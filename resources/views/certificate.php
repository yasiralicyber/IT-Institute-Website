<?php
/** @var array $cert */
$verifyUrl  = abs_url('/verify?id=' . urlencode($cert['credential_id']));
$qr         = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($verifyUrl);
$issuedDate = date('d/m/Y', strtotime($cert['issued_at']));
$fromDate   = $cert['enrolled_at'] ? date('d/m/Y', strtotime($cert['enrolled_at'])) : date('d/m/Y', strtotime($cert['issued_at'] . ' -3 months'));
$toDate     = $issuedDate;
$signUrl    = asset('img/sign.png');
$stampUrl   = asset('img/stamp.png');
$logoUrl    = asset('img/logo.jpg');
$instUrl    = 'www.ittikumber.com';
$instPhone  = config('institute.phone') ?: '+92-3058382085';
$credId     = $cert['credential_id'];
$studentReg = $cert['student_reg'] ?: $credId;
$fatherName = $cert['father_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=IM+Fell+English:ital@0,1&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4 landscape; margin: 0; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
        }
        body {
            background: #b0bec5;
            padding: 20px;
            font-family: 'IM Fell English', 'Times New Roman', Georgia, serif;
        }
        .cert-wrap {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: white;
            margin: 0 auto;
            overflow: hidden;
        }

        /* Diagonal repeating watermark */
        .cert-wm {
            position: absolute;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .cert-wm-inner {
            position: absolute;
            top: -45%;
            left: -45%;
            width: 190%;
            height: 190%;
            transform: rotate(-28deg);
            display: flex;
            flex-direction: column;
            gap: 5px;
            opacity: 0.10;
        }
        .cert-wm-row {
            white-space: nowrap;
            font-size: 13pt;
            font-weight: bold;
            color: #2e7d32;
            letter-spacing: 2px;
        }

        /* Outer green ornate border */
        .cert-border-outer {
            position: absolute;
            inset: 5mm;
            border: 4px solid #1b5e20;
            z-index: 1;
        }
        .cert-border-inner {
            position: absolute;
            inset: 7mm;
            border: 1.5px solid #2e7d32;
            z-index: 1;
        }
        .cert-border-line {
            position: absolute;
            inset: 8.5mm;
            border: 0.5px solid #388e3c;
            z-index: 1;
        }

        /* Content */
        .cert-content {
            position: absolute;
            inset: 11mm 12mm 10mm 12mm;
            z-index: 2;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .cert-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 2mm;
        }
        .cert-header-left {
            font-size: 7.5pt;
            color: #1a1a1a;
            line-height: 1.5;
            min-width: 55mm;
        }
        .cert-header-left a { color: #1565c0; text-decoration: none; }
        .cert-header-center {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .cert-header-right {
            font-size: 7.5pt;
            color: #1a1a1a;
            text-align: right;
            line-height: 1.8;
            min-width: 55mm;
        }

        /* Certificate title */
        .cert-title {
            text-align: center;
            line-height: 1;
            margin-bottom: 1mm;
        }
        .cert-title-script {
            font-family: 'Dancing Script', cursive;
            font-size: 34pt;
            color: #b71c1c;
            line-height: 1;
        }
        .cert-subtitle {
            font-size: 11pt;
            color: #b71c1c;
            font-style: italic;
            text-align: center;
            margin-bottom: 2.5mm;
        }

        /* Name row */
        .cert-name-row {
            font-size: 10pt;
            display: flex;
            align-items: baseline;
            gap: 1mm;
            border-bottom: 0.5px solid #555;
            padding-bottom: 1.5mm;
            margin-bottom: 2.5mm;
        }
        .cert-name-row .underline-field {
            flex: 1;
            border-bottom: 1px solid #222;
            font-size: 11pt;
            font-weight: bold;
            padding: 0 2mm 1mm;
            min-width: 30mm;
        }

        /* Body text */
        .cert-body {
            font-size: 10pt;
            font-style: italic;
            line-height: 1.55;
            color: #1a1a1a;
            margin-bottom: 2mm;
        }
        .cert-body .underline-field {
            display: inline-block;
            border-bottom: 1px solid #222;
            min-width: 50mm;
            font-weight: bold;
            font-style: normal;
            padding: 0 2mm 0;
        }

        /* Dates */
        .cert-dates {
            font-size: 10pt;
            font-style: italic;
            display: flex;
            align-items: baseline;
            gap: 2mm;
            margin-bottom: 1.5mm;
        }
        .cert-dates .underline-field {
            border-bottom: 1px solid #222;
            min-width: 28mm;
            display: inline-block;
            padding: 0 2mm 0;
            font-style: normal;
            font-weight: bold;
        }

        /* Issue line */
        .cert-issued {
            font-size: 10pt;
            font-style: italic;
            display: flex;
            align-items: baseline;
            gap: 2mm;
            margin-bottom: 3mm;
        }
        .cert-issued .underline-field {
            border-bottom: 1px solid #222;
            min-width: 35mm;
            display: inline-block;
            padding: 0 2mm 0;
            font-style: normal;
            font-weight: bold;
        }

        /* Signatories */
        .cert-signatories {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-top: auto;
            flex: 1;
            padding-top: 1mm;
        }
        .cert-sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 38mm;
        }
        .cert-sig-label {
            font-size: 9pt;
            font-weight: bold;
            color: #1b5e20;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 1.5px solid #1b5e20;
            width: 100%;
            text-align: center;
            padding-top: 1mm;
            margin-top: 1mm;
        }

        /* Gold seal */
        .cert-seal {
            width: 22mm;
            height: 22mm;
            position: relative;
            margin-bottom: 1mm;
        }
    </style>
</head>
<body>
<div class="no-print" style="max-width:297mm;margin:0 auto 16px;display:flex;align-items:center;justify-content:space-between;">
    <a href="<?= url('/') ?>" style="font-size:13px;font-weight:bold;color:#1565c0;text-decoration:none;">← IT Training Institute</a>
    <a href="<?= url('/certificate/' . urlencode($cert['credential_id']) . '/pdf') ?>" style="background:#1b5e20;color:white;border-radius:10px;padding:10px 24px;font-size:13px;font-weight:bold;text-decoration:none;">Download Official PDF</a>
</div>

<div class="cert-wrap">

    <!-- Watermark -->
    <div class="cert-wm">
        <div class="cert-wm-inner">
            <?php for ($i = 0; $i < 60; $i++): ?>
            <div class="cert-wm-row">IT TRAINING INSTITUTE KUMBER MAIDAN &nbsp;&nbsp; IT TRAINING INSTITUTE KUMBER MAIDAN &nbsp;&nbsp; IT TRAINING INSTITUTE KUMBER MAIDAN &nbsp;&nbsp; IT TRAINING INSTITUTE KUMBER MAIDAN &nbsp;&nbsp; IT TRAINING INSTITUTE KUMBER MAIDAN &nbsp;&nbsp; IT TRAINING INSTITUTE KUMBER MAIDAN</div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Borders -->
    <div class="cert-border-outer"></div>
    <div class="cert-border-inner"></div>
    <div class="cert-border-line"></div>

    <!-- Content -->
    <div class="cert-content">

        <!-- Header row -->
        <div class="cert-header">
            <div class="cert-header-left">
                <div><a href="https://<?= e($instUrl) ?>"><?= e($instUrl) ?></a></div>
                <div><?= e($instPhone) ?></div>
            </div>
            <div class="cert-header-center">
                <img src="<?= e($logoUrl) ?>" alt="ITTI" style="height:16mm;width:auto;">
            </div>
            <div class="cert-header-right">
                <div><strong>Certificate No:</strong> <?= e($credId) ?></div>
                <div><strong>Registration No:</strong> <?= e($studentReg) ?></div>
            </div>
        </div>

        <!-- Certificate title -->
        <div class="cert-title">
            <div class="cert-title-script">Certificate</div>
        </div>
        <div class="cert-subtitle">This To Acknowledge That</div>

        <!-- Name + father -->
        <div class="cert-name-row">
            <span>Mr/Mrs/Miss</span>
            <span class="underline-field"><?= e($cert['student']) ?></span>
            <span>Son/Daughter</span>
            <span class="underline-field"><?= e($fatherName) ?></span>
        </div>

        <!-- Body -->
        <div class="cert-body">
            Has successfully completed a special training course organized by <strong>IT Training Institute</strong><br>
            Kumber Maidan Dir Lower in the trade of <span class="underline-field"><?= e($cert['course']) ?></span>
        </div>

        <!-- Date range -->
        <div class="cert-dates">
            <span>From</span>
            <span class="underline-field"><?= e($fromDate) ?></span>
            <span style="margin-left:3mm;">To</span>
            <span class="underline-field"><?= e($toDate) ?></span>
        </div>

        <!-- Issue line -->
        <div class="cert-issued">
            <span>In recognition there of this certificate is issued on</span>
            <span class="underline-field"><?= e($issuedDate) ?></span>
        </div>

        <!-- Signatories row -->
        <div class="cert-signatories">

            <!-- Instructor (left) -->
            <div class="cert-sig-col">
                <!-- Gold seal SVG -->
                <svg class="cert-seal" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                    <!-- Outer ring with notches -->
                    <circle cx="50" cy="50" r="46" fill="none" stroke="#b8860b" stroke-width="2.5"/>
                    <circle cx="50" cy="50" r="40" fill="url(#goldGrad)" stroke="#b8860b" stroke-width="1"/>
                    <defs>
                        <radialGradient id="goldGrad" cx="40%" cy="35%">
                            <stop offset="0%" stop-color="#ffe082"/>
                            <stop offset="60%" stop-color="#c8960c"/>
                            <stop offset="100%" stop-color="#8b6914"/>
                        </radialGradient>
                    </defs>
                    <!-- Star points around edge -->
                    <?php for ($a = 0; $a < 24; $a++): $rad = $a * 15 * M_PI / 180; ?>
                    <line x1="<?= round(50 + 38*cos($rad), 1) ?>" y1="<?= round(50 + 38*sin($rad), 1) ?>"
                          x2="<?= round(50 + 46*cos($rad), 1) ?>" y2="<?= round(50 + 46*sin($rad), 1) ?>"
                          stroke="#b8860b" stroke-width="1.5"/>
                    <?php endfor; ?>
                    <!-- Inner text -->
                    <circle cx="50" cy="50" r="28" fill="none" stroke="#b8860b" stroke-width="1"/>
                    <text x="50" y="44" text-anchor="middle" font-size="9" font-weight="bold" fill="#4a3000" font-family="serif">IT TI</text>
                    <text x="50" y="55" text-anchor="middle" font-size="7" fill="#4a3000" font-family="serif">KUMBER</text>
                    <text x="50" y="63" text-anchor="middle" font-size="7" fill="#4a3000" font-family="serif">MAIDAN</text>
                </svg>
                <div style="height:6mm;"></div>
                <div class="cert-sig-label">Instructor</div>
            </div>

            <!-- Principal (center) -->
            <div class="cert-sig-col" style="min-width:60mm;">
                <!-- Signature image -->
                <div style="text-align:center;margin-bottom:1mm;">
                    <img src="<?= e($signUrl) ?>" alt="Signature" style="height:14mm;width:auto;object-fit:contain;transform:rotate(-3deg);">
                </div>
                <!-- Stamp overlaid -->
                <div style="text-align:center;margin-bottom:1mm;">
                    <img src="<?= e($stampUrl) ?>" alt="Stamp" style="height:12mm;width:auto;object-fit:contain;opacity:0.85;transform:rotate(-5deg);">
                </div>
                <div class="cert-sig-label">Principal</div>
            </div>

            <!-- QR code (digital verification) -->
            <div class="cert-sig-col" style="min-width:28mm;">
                <img src="<?= e($qr) ?>" alt="Verify QR" style="width:20mm;height:20mm;display:block;">
                <div style="font-size:5.5pt;color:#555;text-align:center;margin-top:0.5mm;line-height:1.3;">Scan to<br>verify online</div>
                <div style="height:4mm;"></div>
                <div class="cert-sig-label" style="font-size:7pt;color:#555;border-top-color:#555;">ittikumber.com/verify</div>
            </div>

            <!-- Director (right) -->
            <div class="cert-sig-col">
                <!-- Circular director stamp SVG -->
                <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" style="width:22mm;height:22mm;margin-bottom:1mm;">
                    <circle cx="50" cy="50" r="46" fill="none" stroke="#1a237e" stroke-width="2.5"/>
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#1a237e" stroke-width="1.5"/>
                    <!-- Arc text top -->
                    <path id="topArc" d="M 12 50 A 38 38 0 0 1 88 50" fill="none"/>
                    <text font-size="8" fill="#1a237e" font-weight="bold" font-family="Arial">
                        <textPath href="#topArc" startOffset="5%">IT TRAINING INSTITUTE</textPath>
                    </text>
                    <!-- Arc text bottom -->
                    <path id="botArc" d="M 12 50 A 38 38 0 0 0 88 50" fill="none"/>
                    <text font-size="8" fill="#1a237e" font-weight="bold" font-family="Arial">
                        <textPath href="#botArc" startOffset="5%">KUMBER MAIDAN DIR(L)</textPath>
                    </text>
                    <!-- Center -->
                    <text x="50" y="48" text-anchor="middle" font-size="9" font-weight="bold" fill="#1a237e" font-family="Arial">DIRECTOR</text>
                    <line x1="28" y1="54" x2="72" y2="54" stroke="#1a237e" stroke-width="1"/>
                </svg>
                <div class="cert-sig-label">Director</div>
            </div>

        </div><!-- .cert-signatories -->
    </div><!-- .cert-content -->
</div><!-- .cert-wrap -->

</body>
</html>
