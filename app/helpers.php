<?php
/**
 * Global helper functions used throughout the app and views.
 */

/** Inline premium SVG icon (heroicons-style outline). Replaces cheap emojis. */
function icon(string $name, string $class = 'h-6 w-6'): string
{
    static $p = [
        'academic'   => 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.42a12 12 0 01.84 3.92L12 14z M12 14L5.84 10.58A12 12 0 005 14.5L12 14z M6 12v5c3 3 9 3 12 0v-5',
        'clock'      => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'certificate'=> 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'gift'       => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 0v12m0-12a3 3 0 11-3-3 3 3 0 013 3z',
        'beaker'     => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
        'target'     => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'money'      => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'play'       => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'lock'       => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        'teacher'    => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        'shield'     => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'computer'   => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'chart'      => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'chat'       => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'check'      => 'M5 13l4 4L19 7',
        'star'       => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'mail'       => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'refresh'    => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        'trophy'     => 'M5 3h14M5 3v4a7 7 0 007 7 7 7 0 007-7V3M5 3H3v2a4 4 0 004 4m12-6h2v2a4 4 0 01-4 4m-5 4v4m-4 0h8',
        'sparkles'   => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        'bolt'       => 'M13 10V3L4 14h7v7l9-11h-7z',
        'wrench'     => 'M11 6.5a4.5 4.5 0 015.95 4.236l-1.45-1.45a2.5 2.5 0 00-3.536 0l4.95 4.95-2.122 2.121-4.95-4.95a2.5 2.5 0 000 3.536L8.5 17a4.5 4.5 0 01-6.364-6.364L5 13.5l1.5-1.5L3.636 9.136A4.5 4.5 0 0111 6.5z',
        'code'       => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
        'camera'     => 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z',
        'server'     => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01',
        'fire'       => 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.91 17.656 7.474a8 8 0 010 11.183z',
        'book'       => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'note'       => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
        'bookmark'   => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        'download'   => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
    ];
    $d = $p[$name] ?? $p['check'];
    return '<svg class="' . e($class) . '" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="' . $d . '"/></svg>';
}

/** Escape output for safe HTML. */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------------- Lightweight inline SVG charts (no JS library) ---------------- */

/** Circular progress gauge with a centred percentage label. */
function svg_gauge(int $pct, string $color = '#274a70', string $label = '', int $size = 132): string
{
    $pct = max(0, min(100, $pct));
    $r = 54; $c = 2 * M_PI * $r; $off = $c * (1 - $pct / 100);
    $cx = 66;
    ob_start(); ?>
    <svg viewBox="0 0 132 132" style="width:<?= $size ?>px;height:<?= $size ?>px" class="-rotate-90">
        <circle cx="<?= $cx ?>" cy="<?= $cx ?>" r="<?= $r ?>" fill="none" stroke="currentColor" class="text-slate-200 dark:text-white/10" stroke-width="12"/>
        <circle cx="<?= $cx ?>" cy="<?= $cx ?>" r="<?= $r ?>" fill="none" stroke="<?= e($color) ?>" stroke-width="12" stroke-linecap="round"
                stroke-dasharray="<?= round($c, 1) ?>" stroke-dashoffset="<?= round($off, 1) ?>"/>
        <text x="<?= $cx ?>" y="<?= $cx ?>" transform="rotate(90 <?= $cx ?> <?= $cx ?>)" text-anchor="middle" dominant-baseline="central" class="fill-slate-900 dark:fill-white" style="font-size:26px;font-weight:900"><?= $pct ?>%</text>
        <?php if ($label !== ''): ?><text x="<?= $cx ?>" y="<?= $cx + 20 ?>" transform="rotate(90 <?= $cx ?> <?= $cx + 20 ?>)" text-anchor="middle" class="fill-slate-400" style="font-size:10px"><?= e($label) ?></text><?php endif; ?>
    </svg>
    <?php return (string) ob_get_clean();
}

/** Smooth line chart with gradient area fill. $values are 0-100. */
function svg_line(array $values, string $color = '#274a70', int $w = 480, int $h = 150): string
{
    $n = count($values);
    if ($n === 0) { return '<p class="py-10 text-center text-sm text-slate-400">No data yet.</p>'; }
    if ($n === 1) { $values = [$values[0], $values[0]]; $n = 2; }
    $pad = 18; $iw = $w - $pad * 2; $ih = $h - $pad * 2;
    $max = 100; $min = 0;
    $pts = [];
    foreach ($values as $i => $v) {
        $x = $pad + ($iw * $i / ($n - 1));
        $y = $pad + $ih * (1 - ($v - $min) / max(1, $max - $min));
        $pts[] = [round($x, 1), round($y, 1)];
    }
    $line = 'M' . implode(' L', array_map(fn($p) => $p[0] . ',' . $p[1], $pts));
    $area = $line . ' L' . $pts[$n - 1][0] . ',' . ($pad + $ih) . ' L' . $pts[0][0] . ',' . ($pad + $ih) . ' Z';
    $gid = 'g' . substr(md5($color . $w . implode(',', $values)), 0, 6);
    ob_start(); ?>
    <svg viewBox="0 0 <?= $w ?> <?= $h ?>" class="w-full" style="height:<?= $h ?>px">
        <defs><linearGradient id="<?= $gid ?>" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="<?= e($color) ?>" stop-opacity="0.35"/><stop offset="100%" stop-color="<?= e($color) ?>" stop-opacity="0"/></linearGradient></defs>
        <?php for ($g = 0; $g <= 4; $g++): $gy = $pad + $ih * $g / 4; ?><line x1="<?= $pad ?>" x2="<?= $w - $pad ?>" y1="<?= $gy ?>" y2="<?= $gy ?>" stroke="currentColor" class="text-slate-100 dark:text-white/5"/><?php endfor; ?>
        <path d="<?= $area ?>" fill="url(#<?= $gid ?>)"/>
        <path d="<?= $line ?>" fill="none" stroke="<?= e($color) ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <?php foreach ($pts as $p): ?><circle cx="<?= $p[0] ?>" cy="<?= $p[1] ?>" r="3" fill="#fff" stroke="<?= e($color) ?>" stroke-width="2"/><?php endforeach; ?>
    </svg>
    <?php return (string) ob_get_clean();
}

/** Vertical bar chart. $items = [['label'=>..,'pct'=>0-100], ...]. */
function svg_bars(array $items, string $color = '#274a70', int $h = 150): string
{
    $n = count($items);
    if ($n === 0) { return '<p class="py-10 text-center text-sm text-slate-400">No data yet.</p>'; }
    $w = max(240, $n * 70); $pad = 22; $iw = $w - $pad * 2; $ih = $h - $pad * 2;
    $bw = $iw / $n * 0.55;
    ob_start(); ?>
    <svg viewBox="0 0 <?= $w ?> <?= $h ?>" class="w-full" style="height:<?= $h ?>px">
        <?php foreach ($items as $i => $it): $bx = $pad + $iw * ($i + 0.5) / $n - $bw / 2; $bh = $ih * ($it['pct'] / 100); $by = $pad + $ih - $bh; ?>
        <rect x="<?= round($bx, 1) ?>" y="<?= round($by, 1) ?>" width="<?= round($bw, 1) ?>" height="<?= round($bh, 1) ?>" rx="5" fill="<?= e($color) ?>"/>
        <text x="<?= round($bx + $bw / 2, 1) ?>" y="<?= $by - 5 ?>" text-anchor="middle" class="fill-slate-500" style="font-size:11px;font-weight:700"><?= (int) $it['pct'] ?></text>
        <text x="<?= round($bx + $bw / 2, 1) ?>" y="<?= $pad + $ih + 14 ?>" text-anchor="middle" class="fill-slate-400" style="font-size:11px"><?= e($it['label']) ?></text>
        <?php endforeach; ?>
    </svg>
    <?php return (string) ob_get_clean();
}

/**
 * Render one double-sided landscape CR80 student ID card (front + back).
 * Uses the institute's real card artwork (idcard-front.jpg / idcard-back.jpg)
 * as the background and overlays the live student data, QR, barcode, the real
 * principal signature (sign.jpg) and stamp (stamp.jpg). The sign/stamp use
 * mix-blend-mode:multiply so the white paper drops out and only the ink shows.
 */
function student_id_card(array $s, string $program, string $batch): string
{
    $issueDate  = date('d/m/Y');
    $expiryDate = date('d/m/Y', strtotime('+1 year'));
    $verify  = abs_url('/verify-id/' . ($s['id_token'] ?? ''));
    $qr      = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=0&data=' . urlencode($verify);
    $regNo   = $s['reg_no'] ?? ('ITTI-' . date('Y') . '-' . str_pad((string)($s['id'] ?? 0), 4, '0', STR_PAD_LEFT));
    $barcode = 'https://barcode.tec-it.com/barcode.ashx?data=' . urlencode($regNo)
             . '&code=Code128&unit=px&dpi=96&imagetype=Png&width=520&height=70&color=%23000000&bgcolor=%23ffffff';
    $frontBg  = asset('img/idcard-front.jpg');
    $backBg   = asset('img/idcard-back.jpg');

    // Common per-side box. Native art ratio (663:400) stretched to CR80 86x54mm.
    $side = 'width:86mm;height:54mm;position:relative;overflow:hidden;border-radius:3mm;'
          . 'background-size:100% 100%;background-repeat:no-repeat;box-shadow:0 3px 12px rgba(0,0,0,0.25);'
          . "font-family:'Times New Roman',Georgia,serif;";

    ob_start(); ?>
<div class="idcard-pair" style="display:inline-flex;flex-wrap:wrap;gap:4mm;vertical-align:top;">

<!-- ════════════════════ FRONT SIDE ════════════════════ -->
<div class="idcard idcard-front" style="<?= $side ?>background-image:url('<?= e($frontBg) ?>');">

    <!-- Name / Father / Roll values sitting on the printed lines -->
    <div style="position:absolute;left:43.5%;top:36.5%;transform:translateY(-50%);font-size:4mm;font-weight:bold;color:#111;white-space:nowrap;letter-spacing:.2px;"><?= e($s['name']) ?></div>
    <div style="position:absolute;left:43.5%;top:50.5%;transform:translateY(-50%);font-size:4mm;font-weight:bold;color:#111;white-space:nowrap;letter-spacing:.2px;"><?= e($s['father_name'] ?? '') ?></div>
    <div style="position:absolute;left:47%;top:64%;transform:translateY(-50%);font-size:3.6mm;font-weight:bold;color:#111;white-space:nowrap;letter-spacing:.2px;"><?= e($regNo) ?></div>

    <!-- (Signature + stamp are already part of the card artwork) -->

    <!-- Real verification QR over the placeholder QR -->
    <img src="<?= e($qr) ?>" alt="QR" style="position:absolute;left:76.5%;top:62.5%;width:15.5%;height:auto;aspect-ratio:1;background:#fff;padding:.5mm;border-radius:.5mm;">

    <!-- Real issue / expiry dates (green covers match the footer #00A54F) -->
    <div style="position:absolute;left:21%;top:90%;width:18.5%;height:8.5%;background:#00A54F;display:flex;align-items:center;justify-content:center;">
        <span style="font-size:3mm;font-weight:bold;color:#111;white-space:nowrap;"><?= e($issueDate) ?></span>
    </div>
    <div style="position:absolute;left:72.5%;top:90%;width:19.5%;height:8.5%;background:#00A54F;display:flex;align-items:center;justify-content:center;">
        <span style="font-size:3mm;font-weight:bold;color:#111;white-space:nowrap;"><?= e($expiryDate) ?></span>
    </div>
</div>

<!-- ════════════════════ BACK SIDE ════════════════════ -->
<div class="idcard idcard-back" style="<?= $side ?>background-image:url('<?= e($backBg) ?>');">

    <!-- Class / D.O.B / Contact values on the printed lines -->
    <div style="position:absolute;left:32%;top:24.5%;transform:translateY(-50%);font-size:3.8mm;font-weight:bold;color:#111;white-space:nowrap;"><?= e($program) ?></div>
    <div style="position:absolute;left:31%;top:33.5%;transform:translateY(-50%);font-size:3.8mm;font-weight:bold;color:#111;white-space:nowrap;"><?= e($s['dob'] ?? '') ?></div>
    <div style="position:absolute;left:33%;top:56%;transform:translateY(-50%);font-size:3.8mm;font-weight:bold;color:#111;white-space:nowrap;"><?= e($s['phone'] ?? '') ?></div>

    <!-- Real Code-128 barcode of the registration number, over the placeholder -->
    <div style="position:absolute;left:13.5%;top:75%;width:79%;height:11.5%;background:#fff;display:flex;align-items:center;justify-content:center;">
        <img src="<?= e($barcode) ?>" alt="<?= e($regNo) ?>" style="height:100%;width:100%;object-fit:fill;display:block;">
    </div>
</div>

</div>
    <?php return (string) ob_get_clean();
}

/**
 * Build a ROOT-RELATIVE URL to a path on the site (e.g. "/courses").
 * Root-relative links work on any host, port or scheme - so the same code
 * runs on localhost:8090 (dev) and https://itti.com.pk (production) without
 * hardcoding the domain. Use abs_url() when a full URL is required (emails,
 * canonical tags, the admin subdomain).
 */
function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

/** Absolute URL using APP_URL (for emails, canonical links, cross-subdomain). */
function abs_url(string $path = ''): string
{
    $base = rtrim((string) config('app.url'), '/');
    return $base . '/' . ltrim($path, '/');
}

/** Public asset URL (root-relative). */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Redirect helper. */
function redirect(string $path): void
{
    $location = str_starts_with($path, 'http') ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

/** Format a price in PKR. */
function pkr($amount): string
{
    if ($amount === null || $amount === '' || (float) $amount == 0.0) {
        return 'Free';
    }
    return 'Rs ' . number_format((float) $amount);
}

/** Human-friendly minutes -> "1h 20m". */
function duration_label(int $minutes): string
{
    if ($minutes <= 0) return '-';
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return trim(($h ? "{$h}h " : '') . ($m ? "{$m}m" : '')) ?: '0m';
}

/** Start the session once with secure-ish defaults. */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // On HTTPS (production ittimaidan.com / app.ittimaidan.com) mark the
        // cookie Secure. Each subdomain keeps its own session (admin login is
        // separate from the student login) — they share the database, not the
        // cookie, so no cross-subdomain cookie domain is set.
        $https = (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off')
              || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
              || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $https,
        ]);
        session_start();
    }
}

/** CSRF token getter (creates one if missing). */
function csrf_token(): string
{
    ensure_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Hidden CSRF input for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Verify a submitted CSRF token. */
function csrf_verify(?string $token): bool
{
    ensure_session();
    return !empty($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
}

/** Flash a message for the next request. */
function flash(string $key, ?string $message = null)
{
    ensure_session();
    if ($message === null) {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }
    $_SESSION['_flash'][$key] = $message;
    return null;
}

/** Slug helper. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Convenience to read GET/POST input. */
function input(string $key, $default = null)
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

/**
 * Write an entry to the audit log. NEVER logs passwords/tokens/PINs and
 * masks CNIC/Form-B - safe to keep long-term.
 */
function audit(string $action, ?string $entity = null, ?int $entityId = null, string $summary = '', array $meta = []): void
{
    try {
        $user = \App\Core\Auth::user();
        $clean = [];
        foreach ($meta as $k => $v) {
            if (preg_match('/pass|token|pin|secret|csrf/i', $k)) { $clean[$k] = '***'; continue; }
            if (preg_match('/cnic|form_b/i', $k) && is_string($v) && strlen($v) > 4) {
                $clean[$k] = str_repeat('*', strlen($v) - 4) . substr($v, -4); continue;
            }
            $clean[$k] = is_scalar($v) ? mb_substr((string) $v, 0, 200) : json_encode($v);
        }
        \App\Core\Database::run(
            "INSERT INTO audit_log (user_id,user_name,action,entity,entity_id,summary,meta,ip) VALUES (?,?,?,?,?,?,?,?)",
            [$user['id'] ?? null, $user['name'] ?? 'system', $action, $entity, $entityId, $summary,
             $clean ? json_encode($clean) : null, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    } catch (\Throwable $e) { /* never let auditing break the app */ }
}

/**
 * Snapshot a record into the Recycle Bin before a hard delete, so it can be
 * restored later. $children is an optional map of related rows to restore too.
 */
function trash_record(string $table, array $row, string $label = '', array $children = []): void
{
    try {
        $user = \App\Core\Auth::user();
        \App\Core\Database::run(
            "INSERT INTO trash (table_name,record_id,label,data,children,deleted_by,deleted_by_name) VALUES (?,?,?,?,?,?,?)",
            [$table, (int) ($row['id'] ?? 0), $label ?: ($row['name'] ?? $row['title'] ?? ('#' . ($row['id'] ?? '?'))),
             json_encode($row), $children ? json_encode($children) : null, $user['id'] ?? null, $user['name'] ?? 'system']
        );
    } catch (\Throwable $e) { /* don't block deletion if trashing fails */ }
}

/**
 * Securely store an uploaded file under /storage/uploads/{subdir}.
 * Returns the stored relative filename (subdir/name.ext) or null on failure.
 * Files outside subdir cannot be guessed; extension + size are validated.
 */
function store_upload(string $field, string $subdir, array $allowedExt, int $maxBytes = 5_242_880): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['size'] > $maxBytes || $file['size'] <= 0) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }
    // Verify real MIME for images.
    $okMime = ['jpg' => 'image/jpeg','jpeg' => 'image/jpeg','png' => 'image/png','webp' => 'image/webp','pdf' => 'application/pdf'];
    $finfo = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']) : ($okMime[$ext] ?? '');
    if (isset($okMime[$ext]) && $finfo && $finfo !== $okMime[$ext]) {
        return null;
    }
    $dir = BASE_PATH . '/storage/uploads/' . $subdir;
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        // Fallback for CLI/dev where move_uploaded_file may not apply.
        if (!@rename($file['tmp_name'], $dir . '/' . $name)) {
            return null;
        }
    }
    return $subdir . '/' . $name;
}
