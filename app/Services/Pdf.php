<?php
namespace App\Services;

use setasign\Fpdi\Fpdi;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Common\EccLevel;

/**
 * FPDI-based PDF generator. Imports the institute's official blank PDF templates
 * (storage/templates/id-card.pdf, certificate.pdf) and stamps the live student
 * data, a freshly generated verification QR, a Code-128 barcode and the student
 * photo onto them. Pure PHP (FPDI + FPDF + chillerlan/php-qrcode) so it runs on
 * shared hosting without any binary dependency.
 */
class Pdf extends Fpdi
{
    /** Code-128 bar/space width patterns (values 0..106). */
    private const C128 = [
        '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
        '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
        '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
        '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
        '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
        '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
        '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
        '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
        '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
        '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
        '114131','311141','411131','211412','211214','211232','2331112',
    ];

    private static function tplDir(): string { return BASE_PATH . '/storage/templates/'; }
    private static function tmpDir(): string
    {
        $d = BASE_PATH . '/storage/tmp';
        if (!is_dir($d)) { @mkdir($d, 0775, true); }
        return $d;
    }

    /** Generate a verification QR PNG, return its temp file path (caller cleans up via cleanup()). */
    private static function qrPng(string $data, int $scale = 14): ?string
    {
        try {
            $opt = new QROptions([
                'version'         => 7,
                'outputInterface' => QRGdImagePNG::class,
                'eccLevel'        => EccLevel::M,
                'scale'           => $scale,
                'outputBase64'    => false,
                'quietzoneSize'   => 1,
                'drawLightModules'=> true,
            ]);
            $png  = (new QRCode($opt))->render($data);
            $path = self::tmpDir() . '/qr_' . md5($data . microtime()) . '.png';
            file_put_contents($path, $png);
            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private array $tmpFiles = [];
    private function track(string $path): string { $this->tmpFiles[] = $path; return $path; }
    private function clearTmp(): void { foreach ($this->tmpFiles as $f) { @unlink($f); } $this->tmpFiles = []; }

    /** Place a QR for $data inside a square of side $size at (x,y), on a white pad. */
    private function putQr(string $data, float $x, float $y, float $size): void
    {
        $png = self::qrPng($data);
        if (!$png) { return; }
        $this->track($png);
        // white backing so it scans cleanly over any artwork
        $this->SetFillColor(255, 255, 255);
        $this->Rect($x - 0.6, $y - 0.6, $size + 1.2, $size + 1.2, 'F');
        $this->Image($png, $x, $y, $size, $size, 'PNG');
    }

    /** Place a square QR so it exactly covers the template's placeholder box
     *  (no surrounding white area — just the QR with its own tight quiet zone). */
    private function qrInBox(string $data, float $bx, float $by, float $bw, float $bh): void
    {
        $png = self::qrPng($data);
        if (!$png) { return; }
        $this->track($png);
        $qs = max($bw, $bh);
        $this->Image($png, $bx + ($bw - $qs) / 2, $by + ($bh - $qs) / 2, $qs, $qs, 'PNG');
    }

    /** Draw a Code-128 (auto B) barcode as crisp vector bars filling (x,y,w,h). */
    private function code128(string $code, float $x, float $y, float $w, float $h): void
    {
        $code = preg_replace('/[^\x20-\x7E]/', '', $code);
        if ($code === '') { return; }
        // Build value sequence (Code B).
        $vals = [104]; // Start B
        $sum  = 104;
        $pos  = 1;
        for ($i = 0, $n = strlen($code); $i < $n; $i++) {
            $v = ord($code[$i]) - 32;
            $vals[] = $v;
            $sum   += $v * $pos++;
        }
        $vals[] = $sum % 103; // checksum
        $vals[] = 106;        // stop

        // Total modules.
        $modules = 0;
        foreach ($vals as $v) { $modules += array_sum(str_split(self::C128[$v])); }
        $mw = $w / $modules; // module width
        $this->SetFillColor(0, 0, 0);
        $cx = $x;
        foreach ($vals as $v) {
            $pattern = self::C128[$v];
            $bar = true;
            for ($k = 0, $m = strlen($pattern); $k < $m; $k++) {
                $ww = (int) $pattern[$k] * $mw;
                if ($bar) { $this->Rect($cx, $y, $ww, $h, 'F'); }
                $cx  += $ww;
                $bar = !$bar;
            }
        }
    }

    /** Helper: write text at a baseline position with a given font size/style/color. */
    private function put(string $text, float $x, float $y, float $pt, string $style = '', array $rgb = [0, 0, 0], string $font = 'Helvetica'): void
    {
        if ($text === '') { return; }
        $this->SetFont($font, $style, $pt);
        $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
        $this->Text($x, $y, $text);
    }

    private function photoPath(array $s): ?string
    {
        if (empty($s['photo'])) { return null; }
        $p = BASE_PATH . '/public/assets/img/' . $s['photo'];
        return is_file($p) ? $p : null;
    }

    private static function verifyUrlId(array $s): string
    {
        return rtrim((string) config('app.url'), '/') . '/verify-id/' . ($s['id_token'] ?? '');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ID CARD
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Field geometry on the id-card.pdf template (A4 portrait, card pair at top).
     * All values in millimetres. Tuned against the official template.
     */
    private const CARD = [
        // front (value x positions include a small gap after the ":")
        'photo'   => ['x' => 16.4, 'y' => 16.3, 'w' => 13.3, 'h' => 15.6],
        'name'    => ['x' => 52.5, 'y' => 24.4],
        'fname'   => ['x' => 52.5, 'y' => 31.2],
        'roll'    => ['x' => 53.5, 'y' => 38.0],
        'qrbox'   => ['x' => 75.0, 'y' => 32.0, 'w' => 18.0, 'h' => 19.0],
        'issue'   => ['x' => 37.0, 'y' => 54.6],
        'expiry'  => ['x' => 85.0, 'y' => 54.6],
        // back
        'class'   => ['x' => 133.5, 'y' => 18.0],
        'dob'     => ['x' => 131.5, 'y' => 23.4],
        'blood'   => ['x' => 143.5, 'y' => 28.9],
        'contact' => ['x' => 135.5, 'y' => 34.8],
        'address' => ['x' => 135.5, 'y' => 40.2],
        'bcwhite' => ['x' => 111.0, 'y' => 41.0, 'w' => 80.0, 'h' => 9.2],
        'barcode' => ['x' => 127.0, 'y' => 43.8, 'w' => 48.0, 'h' => 4.6],
        'cardno'  => ['x' => 151.0, 'y' => 43.0],
    ];

    /** Height (mm) of the card band cropped from the top of the A4 template. */
    private const BAND_H = 57.0;

    /** Build (and cache) a tight template that contains only the front+back card band. */
    private static function bandTemplate(): string
    {
        $band = self::tplDir() . 'id-card-band.pdf';
        $src  = self::tplDir() . 'id-card.pdf';
        if (is_file($band) && filemtime($band) >= filemtime($src)) { return $band; }
        $b = new Fpdi();
        $b->SetAutoPageBreak(false);
        $b->setSourceFile($src);
        $t = $b->importPage(1);
        $b->AddPage('L', [210.0, self::BAND_H]);
        // Draw the full A4 template at native size; everything below BAND_H is
        // outside the MediaBox and is clipped, leaving only the card band.
        $b->useTemplate($t, 0, 0, 210.0, 297.0);
        $b->Output('F', $band);
        return $band;
    }

    /** Draw one student's card data over an already-placed band, offset by (ox,oy) mm. */
    private function drawCardData(array $s, string $program, float $ox = 0, float $oy = 0): void
    {
        $C   = self::CARD;
        $reg = $s['reg_no'] ?? ('ITTI-' . date('Y') . '-' . str_pad((string) ($s['id'] ?? 0), 4, '0', STR_PAD_LEFT));
        $issue  = date('d/m/Y');
        $expiry = date('d/m/Y', strtotime('+1 year'));

        // Photo (behind the stamp).
        $photo = $this->photoPath($s);
        if ($photo) {
            $type = strtoupper(pathinfo($photo, PATHINFO_EXTENSION)) === 'PNG' ? 'PNG' : 'JPEG';
            $this->Image($photo, $ox + $C['photo']['x'], $oy + $C['photo']['y'], $C['photo']['w'], $C['photo']['h'], $type);
        }
        // (No stamp/signature overlay on the front — photo only, per request.)

        // Front text — kept compact so the values clear the QR box.
        $this->put($s['name'] ?? '',            $ox + $C['name']['x'],  $oy + $C['name']['y'],  8,   'B');
        $this->put($s['father_name'] ?? '',     $ox + $C['fname']['x'], $oy + $C['fname']['y'], 8,   'B');
        $this->put($reg,                        $ox + $C['roll']['x'],  $oy + $C['roll']['y'],  7.5, 'B');
        $this->put($issue,  $ox + $C['issue']['x'],  $oy + $C['issue']['y'],  7.5, 'B');
        $this->put($expiry, $ox + $C['expiry']['x'], $oy + $C['expiry']['y'], 7.5, 'B');
        // Front QR (verification) fitted neatly inside the template's QR box.
        $qb = $C['qrbox'];
        $this->qrInBox(self::verifyUrlId($s), $ox + $qb['x'], $oy + $qb['y'], $qb['w'], $qb['h']);

        // Back text.
        $this->put($program,                    $ox + $C['class']['x'],   $oy + $C['class']['y'],   10, 'B');
        $this->put($s['dob'] ?? '',             $ox + $C['dob']['x'],     $oy + $C['dob']['y'],     10, 'B');
        $this->put($s['blood_group'] ?? 'Nill', $ox + $C['blood']['x'],   $oy + $C['blood']['y'],   10, 'B');
        $this->put($s['phone'] ?? '',           $ox + $C['contact']['x'], $oy + $C['contact']['y'], 10, 'B');
        $this->put($s['address'] ?? 'Teh.Lal Qilla Dir (L)', $ox + $C['address']['x'], $oy + $C['address']['y'], 8.5, 'B');
        // Back barcode (verification = card / registration number).
        // Cover the template's full placeholder strip with white, then draw a
        // smaller real barcode centred in it.
        $this->SetFillColor(255, 255, 255);
        $this->Rect($ox + $C['bcwhite']['x'], $oy + $C['bcwhite']['y'], $C['bcwhite']['w'], $C['bcwhite']['h'], 'F');
        $this->code128($reg, $ox + $C['barcode']['x'], $oy + $C['barcode']['y'], $C['barcode']['w'], $C['barcode']['h']);
        // Human-readable card number centred above the bars.
        $this->SetFont('Helvetica', 'B', 5.5);
        $this->SetTextColor(0, 0, 0);
        $w = $this->GetStringWidth($reg);
        $this->Text($ox + $C['cardno']['x'] - $w / 2, $oy + $C['cardno']['y'], $reg);
    }

    /** Single student: front+back card on one tight page using the official template. */
    public static function idCard(array $s, string $program): string
    {
        $band = self::bandTemplate();
        $pdf  = new self();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($band);
        $tpl = $pdf->importPage(1);
        $pdf->AddPage('L', [210.0, self::BAND_H]);
        $pdf->useTemplate($tpl, 0, 0, 210.0, self::BAND_H);
        $pdf->drawCardData($s, $program, 0, 0);
        $out = $pdf->Output('S');
        $pdf->clearTmp();
        return $out;
    }

    /**
     * Bulk: many students' cards packed onto A4 pages at full CR80 size with no
     * wasted space. Each band (front+back) is tiled down an A4 portrait page.
     */
    public static function idCardsBulk(array $cards): string
    {
        $band = self::bandTemplate();
        $pageW = 210.0; $pageH = 297.0;
        $marginTop = 3.0; $gap = 1.5;
        $perPage = (int) floor(($pageH - 2 * $marginTop + $gap) / (self::BAND_H + $gap));
        if ($perPage < 1) { $perPage = 1; }

        $pdf = new self();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($band);
        $tpl = $pdf->importPage(1);

        $i = 0;
        foreach ($cards as $card) {
            $slot = $i % $perPage;
            if ($slot === 0) { $pdf->AddPage('P', [$pageW, $pageH]); }
            $oy = $marginTop + $slot * (self::BAND_H + $gap);
            $pdf->useTemplate($tpl, 0, $oy, $pageW, self::BAND_H);
            // light cut guide between bands
            $pdf->SetDrawColor(210, 210, 210); $pdf->SetLineWidth(0.1);
            $pdf->Line(0, $oy + self::BAND_H + $gap / 2, $pageW, $oy + self::BAND_H + $gap / 2);
            $pdf->drawCardData($card['student'], $card['program'], 0, $oy);
            $i++;
        }
        if ($i === 0) { $pdf->AddPage('P', [$pageW, $pageH]); }
        $out = $pdf->Output('S');
        $pdf->clearTmp();
        return $out;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CERTIFICATE
    // ─────────────────────────────────────────────────────────────────────────

    /** Field geometry on certificate.pdf (A4 landscape 297x210mm), millimetres, baselines. */
    private const CERT = [
        'certno'  => ['x' => 250.0, 'y' => 58.8],
        'name'    => ['x' => 58.0,  'y' => 91.6],
        'father'  => ['x' => 188.0, 'y' => 91.6],
        'course'  => ['x' => 180.0, 'y' => 117.6],
        'from'    => ['x' => 92.0,  'y' => 132.6],
        'to'      => ['x' => 182.0, 'y' => 132.6],
        'issued'  => ['x' => 152.0, 'y' => 147.6],
        'qr'      => ['x' => 49.5, 'y' => 56.5, 'size' => 26.0],
    ];

    /** Fill the certificate template for a credential row. Returns PDF bytes. */
    public static function certificate(array $cert): string
    {
        $C = self::CERT;
        $credId  = $cert['credential_id'] ?? '';
        $student = $cert['student'] ?? ($cert['student_name'] ?? '');
        $father  = $cert['father_name'] ?? '';
        $course  = $cert['course'] ?? ($cert['course_title'] ?? '');
        $issued  = !empty($cert['issued_at']) ? date('d/m/Y', strtotime($cert['issued_at'])) : date('d/m/Y');
        $from    = !empty($cert['enrolled_at']) ? date('d/m/Y', strtotime($cert['enrolled_at']))
                 : date('d/m/Y', strtotime(($cert['issued_at'] ?? 'now') . ' -3 months'));
        $to      = $issued;
        $verify  = rtrim((string) config('app.url'), '/') . '/verify?id=' . urlencode($credId);

        $pdf = new self();
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile(self::tplDir() . 'certificate.pdf');
        $tpl  = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage('L', [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height']);

        $ink = [20, 20, 20];
        $pdf->put($credId,  $C['certno']['x'], $C['certno']['y'], 8.5, 'B', [120, 20, 20]);
        $pdf->put($student, $C['name']['x'],   $C['name']['y'],   15, 'B', $ink, 'Times');
        $pdf->put($father,  $C['father']['x'], $C['father']['y'], 14, 'B', $ink, 'Times');
        $pdf->put($course,  $C['course']['x'], $C['course']['y'], 13, 'BI', $ink, 'Times');
        $pdf->put($from,    $C['from']['x'],   $C['from']['y'],   12, 'B', $ink);
        $pdf->put($to,      $C['to']['x'],     $C['to']['y'],     12, 'B', $ink);
        $pdf->put($issued,  $C['issued']['x'], $C['issued']['y'], 12, 'B', $ink);
        $pdf->putQr($verify, $C['qr']['x'], $C['qr']['y'], $C['qr']['size']);
        // "Scan to verify" centred under the QR.
        $pdf->SetFont('Helvetica', 'B', 6.5);
        $pdf->SetTextColor(90, 90, 90);
        $label = 'Scan to verify';
        $lw = $pdf->GetStringWidth($label);
        $pdf->Text($C['qr']['x'] + $C['qr']['size'] / 2 - $lw / 2, $C['qr']['y'] + $C['qr']['size'] + 3.5, $label);

        $out = $pdf->Output('S');
        $pdf->clearTmp();
        return $out;
    }
}
