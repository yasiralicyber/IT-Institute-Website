<?php
namespace App\Models;

use App\Core\Database;

/**
 * Results management: a result set bundles weighted components (online
 * quizzes + offline marks) under a grading scheme, computes weighted
 * percentages, grades and ranks, and runs an approval/lock workflow.
 */
class ResultSet
{
    public static function find(int $id): ?array
    {
        return Database::first(
            "SELECT rs.*, c.title AS course, b.name AS batch, g.name AS scheme_name
             FROM result_sets rs
             LEFT JOIN courses c ON c.id = rs.course_id
             LEFT JOIN batches b ON b.id = rs.batch_id
             LEFT JOIN grading_schemes g ON g.id = rs.scheme_id WHERE rs.id = ?", [$id]);
    }

    public static function components(int $setId): array
    {
        return Database::all("SELECT * FROM result_components WHERE result_set_id = ? ORDER BY sort, id", [$setId]);
    }

    /** Students attached to this set (from batch, else course enrolments). */
    public static function students(array $set): array
    {
        if (!empty($set['batch_id'])) {
            return Database::all(
                "SELECT u.id, u.name, u.reg_no FROM batch_students bs JOIN users u ON u.id = bs.user_id
                 WHERE bs.batch_id = ? AND bs.status='active' ORDER BY u.name", [$set['batch_id']]);
        }
        if (!empty($set['course_id'])) {
            return Database::all(
                "SELECT u.id, u.name, u.reg_no FROM enrollments e JOIN users u ON u.id = e.user_id
                 WHERE e.course_id = ? ORDER BY u.name", [$set['course_id']]);
        }
        return [];
    }

    /** Map component_id => obtained for a student. */
    public static function scoresFor(int $setId, int $userId): array
    {
        $map = [];
        foreach (Database::all("SELECT component_id, obtained FROM result_scores WHERE result_set_id = ? AND user_id = ?",
            [$setId, $userId]) as $r) {
            $map[(int) $r['component_id']] = (float) $r['obtained'];
        }
        return $map;
    }

    /**
     * Pull best online quiz scores into result_scores for every student.
     * Online quiz score is a 0-100 percentage stored against max_marks.
     */
    public static function syncOnline(array $set): void
    {
        $comps = array_filter(self::components((int) $set['id']), fn($c) => $c['source'] === 'online' && $c['quiz_id']);
        if (!$comps) { return; }
        foreach (self::students($set) as $s) {
            foreach ($comps as $c) {
                $best = (float) Database::scalar(
                    "SELECT COALESCE(MAX(score),0) FROM quiz_attempts WHERE user_id = ? AND quiz_id = ?",
                    [$s['id'], $c['quiz_id']]);
                // Scale a 0-100 percent onto the component's max_marks.
                $obtained = round($best / 100 * (int) $c['max_marks'], 2);
                self::setScore((int) $set['id'], (int) $s['id'], (int) $c['id'], $obtained);
            }
        }
    }

    public static function setScore(int $setId, int $userId, int $componentId, float $obtained): void
    {
        $exists = Database::scalar(
            "SELECT id FROM result_scores WHERE result_set_id=? AND user_id=? AND component_id=?",
            [$setId, $userId, $componentId]);
        if ($exists) {
            Database::run("UPDATE result_scores SET obtained=? WHERE id=?", [$obtained, $exists]);
        } else {
            Database::run("INSERT INTO result_scores (result_set_id,user_id,component_id,obtained) VALUES (?,?,?,?)",
                [$setId, $userId, $componentId, $obtained]);
        }
    }

    /** Grading scheme bands, default if none chosen. */
    public static function bands(array $set): array
    {
        $scheme = !empty($set['scheme_id']) ? Database::first("SELECT * FROM grading_schemes WHERE id=?", [(int) $set['scheme_id']]) : null;
        if (!$scheme) { $scheme = Database::first("SELECT * FROM grading_schemes WHERE is_default=1") ?: Database::first("SELECT * FROM grading_schemes ORDER BY id LIMIT 1"); }
        $bands = $scheme ? json_decode($scheme['bands'], true) : [];
        if (!is_array($bands) || !$bands) {
            $bands = [['min'=>80,'grade'=>'A+','gpa'=>4.0],['min'=>70,'grade'=>'A','gpa'=>3.5],['min'=>60,'grade'=>'B','gpa'=>3.0],['min'=>50,'grade'=>'C','gpa'=>2.0],['min'=>40,'grade'=>'D','gpa'=>1.0],['min'=>0,'grade'=>'F','gpa'=>0.0]];
        }
        usort($bands, fn($a, $b) => $b['min'] <=> $a['min']);
        return $bands;
    }

    public static function gradeFor(float $pct, array $bands): array
    {
        foreach ($bands as $b) {
            if ($pct >= (float) $b['min']) { return $b; }
        }
        return end($bands) ?: ['grade' => '-', 'gpa' => 0];
    }

    /**
     * Compute a full result row for a student: per-component, weighted %,
     * grade, pass/fail.
     */
    public static function compute(array $set, array $components, int $userId, array $bands): array
    {
        $scores = self::scoresFor((int) $set['id'], $userId);
        $totalWeight = 0; $weighted = 0; $rawObtained = 0; $rawMax = 0;
        $cells = [];
        foreach ($components as $c) {
            $obt = $scores[(int) $c['id']] ?? 0.0;
            $max = max(1, (int) $c['max_marks']);
            $w = (int) $c['weight'];
            $pct = $obt / $max;
            $weighted += $pct * $w;
            $totalWeight += $w;
            $rawObtained += $obt; $rawMax += $max;
            $cells[(int) $c['id']] = ['obtained' => $obt, 'max' => $max];
        }
        $finalPct = $totalWeight > 0 ? round($weighted / $totalWeight * 100, 2) : ($rawMax > 0 ? round($rawObtained / $rawMax * 100, 2) : 0);
        $band = self::gradeFor($finalPct, $bands);
        return [
            'cells' => $cells,
            'obtained' => $rawObtained,
            'max' => $rawMax,
            'percent' => $finalPct,
            'grade' => $band['grade'] ?? '-',
            'gpa' => $band['gpa'] ?? 0,
            'passed' => $finalPct >= (float) $set['pass_mark'],
        ];
    }

    /** Full ranked result table for a set (merit list / gradebook). */
    public static function table(array $set): array
    {
        $components = self::components((int) $set['id']);
        $bands = self::bands($set);
        $rows = [];
        foreach (self::students($set) as $s) {
            $r = self::compute($set, $components, (int) $s['id'], $bands);
            $rows[] = ['student' => $s] + $r;
        }
        usort($rows, fn($a, $b) => $b['percent'] <=> $a['percent']);
        $rank = 0; $last = null; $i = 0;
        foreach ($rows as &$row) {
            $i++;
            if ($last === null || $row['percent'] < $last) { $rank = $i; $last = $row['percent']; }
            $row['rank'] = $rank;
        }
        return ['components' => $components, 'bands' => $bands, 'rows' => $rows];
    }
}
