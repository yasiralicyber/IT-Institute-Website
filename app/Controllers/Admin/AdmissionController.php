<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

/**
 * Admissions module with a SECURE VERIFICATION GATE: full personal details
 * and the photo of an applicant are only revealed after the admin enters the
 * matching Name + Father Name + CNIC/B-Form No. + Date of Birth - so sensitive
 * data can't be opened without these four identifiers.
 */
class AdmissionController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $rows = Database::all("SELECT id,name,programs,contact,status,created_at FROM admissions ORDER BY created_at DESC");
        $this->view('admin/admissions', [
            'title' => 'Admissions', 'heading' => 'Admission Applications',
            'rows' => $rows,
        ], 'admin/layouts/admin');
    }

    /** The 4-identifier security gate. */
    public function verify(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/admissions'); }

        $name   = trim((string) input('name', ''));
        $father = trim((string) input('father_name', ''));
        $cnic   = trim((string) input('cnic', ''));
        $dob    = trim((string) input('dob', ''));

        if ($name === '' || $father === '' || $cnic === '' || $dob === '') {
            flash('error', 'Enter all four identifiers (Name, Father Name, CNIC/B-Form, Date of Birth) to verify.');
            redirect('/admissions');
        }

        // Match against name + father + DOB + (form_b serves as CNIC/B-Form).
        $norm = fn($s) => strtolower(preg_replace('/[\s\-]/', '', $s));
        $candidates = Database::all(
            "SELECT * FROM admissions WHERE LOWER(name) = LOWER(?) AND LOWER(father_name) = LOWER(?)",
            [$name, $father]);
        $match = null;
        foreach ($candidates as $c) {
            if ($norm($c['form_b'] ?? '') === $norm($cnic) && $norm($c['dob'] ?? '') === $norm($dob)) {
                $match = $c; break;
            }
        }

        if (!$match) {
            flash('error', 'No record matches all four identifiers. Access denied for data security.');
            redirect('/admissions');
        }

        // Authorise viewing this specific record for this session.
        ensure_session();
        $_SESSION['verified_admission'][(int) $match['id']] = true;
        flash('success', 'Identity verified - record unlocked.');
        redirect('/admissions/' . (int) $match['id']);
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT * FROM admissions WHERE id = ?", [$id]);
        if (!$row) { redirect('/admissions'); }
        $this->view('admin/admission-detail', [
            'title' => $row['name'], 'heading' => 'Applicant: ' . $row['name'], 'row' => $row,
        ], 'admin/layouts/admin');
    }

    public function photo(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $row = Database::first("SELECT photo FROM admissions WHERE id = ?", [$id]);
        $path = $row && $row['photo'] ? BASE_PATH . '/storage/uploads/' . $row['photo'] : '';
        if (!$path || !is_file($path)) { http_response_code(404); exit; }
        $mime = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : 'image/jpeg';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function status(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/admissions'); }
        $id = (int) ($params['id'] ?? 0);
        $status = in_array(input('status'), ['new', 'contacted', 'enrolled', 'rejected'], true) ? input('status') : 'new';
        Database::run("UPDATE admissions SET status = ? WHERE id = ?", [$status, $id]);
        flash('success', 'Application status updated.');
        redirect('/admissions/' . $id);
    }
}
