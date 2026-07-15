<?php
/** @var string $content @var ?array $user @var array $course */
$u = $user ?? [];
$wm = trim(($u['name'] ?? 'Guest') . '  ·  ' . ($u['email'] ?? '') . '  ·  ID:' . ($u['id'] ?? '-'));
?>
<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Lesson') ?></title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Anti-casual-copy: block selection/drag on the learning surface. */
        .no-copy{user-select:none;-webkit-user-select:none;-webkit-touch-callout:none}
        .no-copy img{-webkit-user-drag:none;user-drag:none;pointer-events:none}
        .wm-tile{
            position:absolute;inset:0;pointer-events:none;z-index:20;overflow:hidden;
            background-image:repeating-linear-gradient(45deg,transparent 0,transparent 180px);
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased no-copy" data-wm="<?= e($wm) ?>">
<header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-6">
    <div class="flex items-center gap-3">
        <a href="<?= url('/dashboard') ?>" class="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            Dashboard
        </a>
        <span class="hidden text-slate-300 sm:block">/</span>
        <span class="hidden truncate text-sm font-bold text-slate-800 sm:block sm:max-w-xs"><?= e($course['title']) ?></span>
    </div>
    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white"><?= e(strtoupper(substr($u['name'] ?? 'G', 0, 1))) ?></span>
</header>

<?php if ($m = flash('error')): ?>
<div class="bg-red-500/15 px-6 py-2.5 text-center text-sm font-semibold text-red-300"><?= e($m) ?></div>
<?php endif; ?>

<?= $content ?>

<script>
/* ---- Content-protection deterrents (best-effort; documented limits) ---- */
(function(){
    // Disable right-click on the learning surface.
    document.addEventListener('contextmenu', function(e){ e.preventDefault(); });
    // Discourage drag/select.
    document.addEventListener('dragstart', function(e){ e.preventDefault(); });
    // Block common save/print/view-source/devtools shortcuts.
    document.addEventListener('keydown', function(e){
        var k = e.key.toLowerCase();
        if (e.key === 'F12') { e.preventDefault(); }
        if ((e.ctrlKey||e.metaKey) && ['s','u','p'].includes(k)) { e.preventDefault(); warn(); }
        if ((e.ctrlKey||e.metaKey) && e.shiftKey && ['i','j','c'].includes(k)) { e.preventDefault(); warn(); }
        // PrintScreen: try to blank the clipboard.
        if (e.key === 'PrintScreen') { try{ navigator.clipboard.writeText('Screenshots are not allowed - '+document.body.dataset.wm); }catch(_){} warn(); }
    });
    function warn(){
        var el = document.getElementById('protect-warning');
        if(el){ el.classList.remove('hidden'); clearTimeout(window.__wt); window.__wt=setTimeout(function(){el.classList.add('hidden');},2500); }
    }
    // Blur the video when the tab/window loses focus (deters screen-record tools/overlays).
    // Exception: if focus moved to the practice iframe, do NOT blur — student is studying side-by-side.
    var stage = document.getElementById('video-stage');
    function setBlur(on){ if(stage){ stage.style.filter = on ? 'blur(16px)' : 'none'; } var ov=document.getElementById('blur-note'); if(ov){ ov.classList.toggle('hidden', !on);} }
    document.addEventListener('visibilitychange', function(){ setBlur(document.hidden); });
    window.addEventListener('blur', function(){
        setTimeout(function(){
            var pf = document.getElementById('practiceFrame');
            if (pf && document.activeElement === pf) return; // focus in practice pane — keep video clear
            setBlur(true);
        }, 50);
    });
    window.addEventListener('focus', function(){ setBlur(false); });

    // Moving diagonal watermark tied to the logged-in user (traceable leaks).
    var tile = document.getElementById('wm');
    if (tile) {
        var txt = document.body.dataset.wm, c = document.createElement('canvas'), x = c.getContext('2d');
        c.width = 420; c.height = 220;
        x.rotate(-20*Math.PI/180); x.font='600 15px Inter,sans-serif';
        x.fillStyle='rgba(255,255,255,0.10)'; x.fillText(txt, -30, 150);
        tile.style.backgroundImage = 'url('+c.toDataURL()+')';
        var p=0; setInterval(function(){ p=(p+1)%420; tile.style.backgroundPosition = p+'px '+(p/2)+'px'; }, 120);
    }
})();
</script>
</body>
</html>
