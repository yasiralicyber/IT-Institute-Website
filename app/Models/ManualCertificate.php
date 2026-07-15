<?php
namespace App\Models;

use App\Core\Database;

/**
 * Registry of physical / in-house certificates the institute hands out.
 * Once recorded here (name, number, course, dates) the printed certificate
 * can be verified online by its number — even for people who were never
 * online students.
 */
class ManualCertificate
{
    /** Look up a certificate by its printed number (case-insensitive, trimmed). */
    public static function findByNumber(string $certNo): ?array
    {
        $certNo = trim($certNo);
        if ($certNo === '') { return null; }
        return Database::first(
            "SELECT * FROM manual_certificates WHERE UPPER(cert_no) = UPPER(?)",
            [$certNo]
        );
    }

    public static function all(string $search = ''): array
    {
        if ($search !== '') {
            $like = '%' . $search . '%';
            return Database::all(
                "SELECT * FROM manual_certificates
                 WHERE cert_no LIKE ? OR student_name LIKE ? OR father_name LIKE ? OR course LIKE ?
                 ORDER BY created_at DESC", [$like, $like, $like, $like]);
        }
        return Database::all("SELECT * FROM manual_certificates ORDER BY created_at DESC");
    }

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM manual_certificates WHERE id = ?", [$id]);
    }

    /** Generate the next institute certificate number, e.g. ITTI-CERT-2026-0007. */
    public static function nextNumber(): string
    {
        $year = date('Y');
        $n = (int) Database::scalar(
            "SELECT COUNT(*) FROM manual_certificates WHERE cert_no LIKE ?",
            ['ITTI-CERT-' . $year . '-%']
        ) + 1;
        do {
            $candidate = 'ITTI-CERT-' . $year . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
            $exists = Database::scalar("SELECT 1 FROM manual_certificates WHERE cert_no = ?", [$candidate]);
            $n++;
        } while ($exists);
        return $candidate;
    }

    /** Create a registry record; returns [id, cert_no] or throws on duplicate number. */
    public static function create(array $d, ?array $admin = null): array
    {
        $certNo = trim((string) ($d['cert_no'] ?? ''));
        if ($certNo === '') { $certNo = self::nextNumber(); }

        if (Database::scalar("SELECT 1 FROM manual_certificates WHERE UPPER(cert_no)=UPPER(?)", [$certNo])) {
            throw new \RuntimeException('A certificate with number "' . $certNo . '" already exists.');
        }

        Database::run(
            "INSERT INTO manual_certificates
             (cert_no, student_name, father_name, course, grade, from_date, to_date, issue_date, remarks, status, issued_by, issued_by_name)
             VALUES (?,?,?,?,?,?,?,?,?, 'valid', ?, ?)",
            [
                $certNo,
                trim((string) ($d['student_name'] ?? '')),
                trim((string) ($d['father_name'] ?? '')),
                trim((string) ($d['course'] ?? '')),
                trim((string) ($d['grade'] ?? '')),
                trim((string) ($d['from_date'] ?? '')),
                trim((string) ($d['to_date'] ?? '')),
                trim((string) ($d['issue_date'] ?? '')) ?: date('Y-m-d'),
                trim((string) ($d['remarks'] ?? '')),
                $admin['id'] ?? null,
                $admin['name'] ?? 'admin',
            ]
        );
        return ['id' => (int) Database::pdo()->lastInsertId(), 'cert_no' => $certNo];
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            "UPDATE manual_certificates SET student_name=?, father_name=?, course=?, grade=?,
             from_date=?, to_date=?, issue_date=?, remarks=? WHERE id=?",
            [
                trim((string) ($d['student_name'] ?? '')),
                trim((string) ($d['father_name'] ?? '')),
                trim((string) ($d['course'] ?? '')),
                trim((string) ($d['grade'] ?? '')),
                trim((string) ($d['from_date'] ?? '')),
                trim((string) ($d['to_date'] ?? '')),
                trim((string) ($d['issue_date'] ?? '')),
                trim((string) ($d['remarks'] ?? '')),
                $id,
            ]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        $status = in_array($status, ['valid', 'revoked'], true) ? $status : 'valid';
        Database::run("UPDATE manual_certificates SET status=? WHERE id=?", [$status, $id]);
    }

    public static function delete(int $id): void
    {
        Database::run("DELETE FROM manual_certificates WHERE id=?", [$id]);
    }
}
