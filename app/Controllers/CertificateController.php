<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\View;
use App\Models\Certificate;

class CertificateController extends Controller
{
    /** Public printable certificate page. */
    public function show(array $params): void
    {
        $cert = Certificate::findByCredential((string) ($params['credential'] ?? ''));
        if (!$cert) {
            http_response_code(404);
            echo View::render('errors/404', ['title' => 'Certificate not found']);
            return;
        }
        echo View::render('certificate', [
            'title' => 'Certificate ' . $cert['credential_id'],
            'cert'  => $cert,
        ], '');
    }

    /** Download the official certificate filled from the institute PDF template. */
    public function download(array $params): void
    {
        $cert = Certificate::findByCredential((string) ($params['credential'] ?? ''));
        if (!$cert) {
            http_response_code(404);
            echo View::render('errors/404', ['title' => 'Certificate not found']);
            return;
        }
        $pdf  = \App\Services\Pdf::certificate($cert);
        $name = 'Certificate-' . preg_replace('/[^A-Za-z0-9]+/', '-', $cert['credential_id']) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: private, max-age=0, must-revalidate');
        echo $pdf;
        exit;
    }
}
