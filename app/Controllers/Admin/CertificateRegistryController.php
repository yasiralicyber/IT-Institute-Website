<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\ManualCertificate;
use App\Services\Pdf;

/**
 * Admin: register the institute's physical / in-house certificates so they can
 * be verified online by their number.
 */
class CertificateRegistryController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $search = trim((string) input('q', ''));
        $this->view('admin/certificate-registry', [
            'title'    => 'Certificate Registry',
            'heading'  => 'Physical Certificate Registry',
            'rows'     => ManualCertificate::all($search),
            'search'   => $search,
            'nextNo'   => ManualCertificate::nextNumber(),
            'verifyBase' => rtrim((string) config('app.url'), '/') . '/verify?id=',
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/certificates'); }

        $name = trim((string) input('student_name', ''));
        if ($name === '') {
            flash('error', 'Student name is required.');
            redirect('/certificates');
        }
        try {
            $res = ManualCertificate::create([
                'cert_no'      => input('cert_no'),
                'student_name' => $name,
                'father_name'  => input('father_name'),
                'course'       => input('course'),
                'grade'        => input('grade'),
                'from_date'    => input('from_date'),
                'to_date'      => input('to_date'),
                'issue_date'   => input('issue_date'),
                'remarks'      => input('remarks'),
            ], Auth::user());
            audit('certificate.register', 'manual_certificates', $res['id'], 'Registered physical certificate ' . $res['cert_no'] . ' for ' . $name);
            flash('success', 'Certificate ' . $res['cert_no'] . ' registered. It can now be verified online.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('/certificates');
    }

    public function revoke(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/certificates'); }
        $id = (int) ($params['id'] ?? 0);
        $row = ManualCertificate::find($id);
        if ($row) {
            $new = $row['status'] === 'revoked' ? 'valid' : 'revoked';
            ManualCertificate::setStatus($id, $new);
            audit('certificate.' . $new, 'manual_certificates', $id, ($new === 'revoked' ? 'Revoked' : 'Restored') . ' certificate ' . $row['cert_no']);
            flash('success', 'Certificate ' . $row['cert_no'] . ' is now ' . ($new === 'revoked' ? 'REVOKED' : 'valid') . '.');
        }
        redirect('/certificates');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/certificates'); }
        $id = (int) ($params['id'] ?? 0);
        if ($row = ManualCertificate::find($id)) {
            trash_record('manual_certificates', $row, 'Certificate ' . $row['cert_no']);
            ManualCertificate::delete($id);
            audit('certificate.delete', 'manual_certificates', $id, 'Deleted registry record ' . $row['cert_no']);
            flash('success', 'Registry record deleted.');
        }
        redirect('/certificates');
    }

    /** Download a filled certificate PDF for a registry record (institute template). */
    public function pdf(array $params): void
    {
        Auth::requireAdmin();
        $row = ManualCertificate::find((int) ($params['id'] ?? 0));
        if (!$row) { redirect('/certificates'); }

        // Map the registry record onto the certificate PDF fields.
        $cert = [
            'credential_id' => $row['cert_no'],
            'student'       => $row['student_name'],
            'father_name'   => $row['father_name'],
            'course'        => $row['course'],
            'issued_at'     => $row['issue_date'] ?: date('Y-m-d'),
            'enrolled_at'   => $row['from_date'] ?: null,
        ];
        $pdf  = Pdf::certificate($cert);
        $name = 'Certificate-' . preg_replace('/[^A-Za-z0-9]+/', '-', (string) $row['cert_no']) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
        exit;
    }
}
