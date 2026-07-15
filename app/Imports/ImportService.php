<?php
namespace App\Imports;

use App\Core\Database;

class ImportService
{
    /** Suggest a column index for each system field by fuzzy header match. */
    public static function suggestMapping(array $def, array $headers): array
    {
        $norm = fn($s) => strtolower(preg_replace('/[^a-z0-9]/i', '', (string) $s));
        $map = [];
        foreach ($def['fields'] as $field => $cfg) {
            $cands = [$field, $cfg['label']];
            $best = null;
            foreach ($headers as $i => $h) {
                foreach ($cands as $cand) {
                    if ($norm($h) === $norm($cand) || str_contains($norm($h), $norm($field))) { $best = $i; break 2; }
                }
            }
            $map[$field] = $best;
        }
        return $map;
    }

    /** Validate every row; returns ['valid'=>[processed...], 'errors'=>[['row'=>n,'field'=>,'value'=>,'reason'=>]]]. */
    public static function validate(array $def, array $rows, array $mapping): array
    {
        $valid = []; $errors = [];
        foreach ($rows as $idx => $row) {
            $assoc = [];
            foreach ($mapping as $field => $col) {
                $assoc[$field] = ($col === null || $col === '') ? '' : trim((string) ($row[(int) $col] ?? ''));
            }
            $res = self::processRow($def, $assoc);
            if ($res['errors']) {
                foreach ($res['errors'] as $e) { $errors[] = ['row' => $idx + 2] + $e; } // +2 = header + 1-based
            } else {
                $valid[] = $res['data'];
            }
        }
        return ['valid' => $valid, 'errors' => $errors];
    }

    private static function processRow(array $def, array $assoc): array
    {
        $data = []; $errors = [];
        foreach ($def['fields'] as $field => $cfg) {
            $val = $assoc[$field] ?? '';
            $val = self::transform($val, $cfg['transform'] ?? 'trim');

            if ($val === '' && isset($cfg['default'])) {
                $val = $cfg['transform'] ?? null ? self::transform((string) $cfg['default'], $cfg['transform']) : $cfg['default'];
            }

            // Relationship resolution (name -> id).
            if (!empty($cfg['relation'])) {
                if ($val === '') {
                    if (!empty($cfg['required'])) { $errors[] = ['field' => $cfg['label'], 'value' => '', 'reason' => 'Required, but empty']; }
                    $data[$field] = null; continue;
                }
                $id = self::resolveRelation($cfg['relation'], $val);
                if (!$id) { $errors[] = ['field' => $cfg['label'], 'value' => $val, 'reason' => 'No matching ' . $cfg['relation']['table'] . ' found']; continue; }
                $data[$field] = $id; continue;
            }

            if ($val === '' && !empty($cfg['required'])) {
                $errors[] = ['field' => $cfg['label'], 'value' => '', 'reason' => 'Required, but empty']; continue;
            }
            if ($val !== '' && !empty($cfg['validate']) && !self::isValid($val, $cfg['validate'])) {
                $errors[] = ['field' => $cfg['label'], 'value' => $val, 'reason' => 'Invalid ' . $cfg['validate']]; continue;
            }
            $data[$field] = $val;
        }
        return ['data' => $data, 'errors' => $errors];
    }

    private static function transform(string $v, string $t): string
    {
        $v = trim($v);
        switch ($t) {
            case 'lower': return mb_strtolower($v);
            case 'upper': return mb_strtoupper($v);
            case 'title': return ucwords(mb_strtolower($v));
            case 'slug':  return $v === '' ? '' : slugify($v);
            case 'password': return $v === '' ? '' : password_hash($v, PASSWORD_DEFAULT);
            case 'phone':
                $d = preg_replace('/[^\d+]/', '', $v);
                if (str_starts_with($d, '+92')) { $d = '0' . substr($d, 3); }
                elseif (str_starts_with($d, '92') && strlen($d) === 12) { $d = '0' . substr($d, 2); }
                return $d;
            case 'cnic':
                $d = preg_replace('/\D/', '', $v);
                return strlen($d) === 13 ? substr($d, 0, 5) . '-' . substr($d, 5, 7) . '-' . substr($d, 12) : $v;
            case 'date':
                if ($v === '') { return ''; }
                foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'j/n/Y', 'd.m.Y'] as $fmt) {
                    $dt = \DateTime::createFromFormat($fmt, $v);
                    if ($dt && $dt->format($fmt) === $v) { return $dt->format('Y-m-d'); }
                }
                $ts = strtotime($v);
                return $ts ? date('Y-m-d', $ts) : $v;
            default: return $v;
        }
    }

    private static function isValid(string $v, string $rule): bool
    {
        return match ($rule) {
            'email' => (bool) filter_var($v, FILTER_VALIDATE_EMAIL),
            'int'   => is_numeric($v),
            'date'  => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v),
            default => true,
        };
    }

    private static function resolveRelation(array $rel, string $value): ?int
    {
        foreach ($rel['match'] as $col) {
            $id = Database::scalar("SELECT id FROM {$rel['table']} WHERE LOWER({$col}) = LOWER(?) LIMIT 1", [$value]);
            if ($id) { return (int) $id; }
        }
        return null;
    }

    /** Execute the import for a session row; returns updated stats. */
    public static function execute(array $session): array
    {
        $def = Registry::get($session['section']);
        $path = $session['storage_path'];
        $data = Reader::read($path, pathinfo($path, PATHINFO_EXTENSION) ?: 'csv');
        $mapping = json_decode($session['mapping'], true) ?: [];
        $result = self::validate($def, $data['rows'], $mapping);

        $strategy = $session['dupe_strategy'];
        $imported = $updated = $skipped = 0; $createdIds = [];

        foreach ($result['valid'] as $row) {
            $row = array_merge($row, $def['fixed'] ?? []);
            // auto fields (e.g. slug from title)
            foreach ($def['auto'] ?? [] as $target => $source) {
                if (empty($row[$target]) && !empty($row[$source])) { $row[$target] = slugify((string) $row[$source]); }
            }
            $existingId = self::findDuplicate($def, $row);
            if ($existingId) {
                if ($strategy === 'update') {
                    self::updateRow($def['table'], $existingId, $row); $updated++;
                } elseif ($strategy === 'create') {
                    $createdIds[] = self::insertRow($def['table'], $row); $imported++;
                } else { $skipped++; }
            } else {
                $createdIds[] = self::insertRow($def['table'], $row); $imported++;
            }
        }

        Database::run(
            "UPDATE import_sessions SET imported=?,updated=?,skipped=?,failed=?,created_ids=?,errors=?,status='completed' WHERE id=?",
            [$imported, $updated, $skipped, count($result['errors']), json_encode($createdIds),
             json_encode(array_slice($result['errors'], 0, 500)), $session['id']]
        );
        audit('import', $def['table'], null, "Imported {$imported}, updated {$updated}, skipped {$skipped} ({$def['label']})");
        return compact('imported', 'updated', 'skipped') + ['failed' => count($result['errors'])];
    }

    public static function rollback(array $session): int
    {
        $ids = json_decode($session['created_ids'] ?? '[]', true) ?: [];
        $def = Registry::get($session['section']);
        $n = 0;
        foreach ($ids as $id) {
            Database::run("DELETE FROM {$def['table']} WHERE id = ?", [(int) $id]); $n++;
        }
        Database::run("UPDATE import_sessions SET status='rolled_back', created_ids='[]' WHERE id=?", [$session['id']]);
        audit('import_rollback', $def['table'], null, "Rolled back {$n} imported {$def['label']} records");
        return $n;
    }

    private static function findDuplicate(array $def, array $row): ?int
    {
        if (empty($def['dupe'])) { return null; }
        $where = []; $params = [];
        foreach ($def['dupe'] as $key) {
            if (!isset($row[$key]) || $row[$key] === '' || $row[$key] === null) { return null; }
            $where[] = "{$key} = ?"; $params[] = $row[$key];
        }
        $id = Database::scalar("SELECT id FROM {$def['table']} WHERE " . implode(' AND ', $where) . " LIMIT 1", $params);
        return $id ? (int) $id : null;
    }

    private static function insertRow(string $table, array $row): int
    {
        $cols = array_keys($row);
        $place = implode(',', array_fill(0, count($cols), '?'));
        return (int) Database::run("INSERT INTO {$table} (" . implode(',', $cols) . ") VALUES ({$place})", array_values($row));
    }

    private static function updateRow(string $table, int $id, array $row): void
    {
        unset($row['id']);
        $sets = implode(',', array_map(fn($c) => "{$c} = ?", array_keys($row)));
        Database::run("UPDATE {$table} SET {$sets} WHERE id = ?", array_merge(array_values($row), [$id]));
    }

    /** CSV template (headers + one example row) for a section. */
    public static function template(array $def): string
    {
        $headers = array_map(fn($c) => $c['label'], $def['fields']);
        $example = array_map(function ($c) {
            if (!empty($c['relation'])) { return 'existing ' . $c['relation']['table'] . ' name'; }
            return match ($c['transform'] ?? ($c['validate'] ?? '')) {
                'date' => '2026-07-01', 'email' => 'name@example.com', 'phone' => '03001234567',
                'int' => '1000', default => 'example',
            };
        }, $def['fields']);
        return self::csvLine($headers) . self::csvLine($example);
    }

    private static function csvLine(array $cells): string
    {
        return implode(',', array_map(fn($c) => '"' . str_replace('"', '""', (string) $c) . '"', $cells)) . "\n";
    }
}
