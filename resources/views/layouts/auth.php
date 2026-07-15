<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-ink dark:text-slate-200 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">
    <!-- Brand side -->
    <div class="relative hidden overflow-hidden bg-gradient-to-br from-brand-800 to-brand-950 lg:block">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 20%,#fff 1px,transparent 1px);background-size:24px 24px"></div>
        <div class="relative flex h-full flex-col justify-between p-12 text-white">
            <a href="<?= url('/') ?>" class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white p-1">
                    <img src="<?= asset('img/logo.jpg') ?>" alt="ITTI" class="h-full w-full object-contain">
                </span>
                <span class="text-lg font-extrabold">IT Training Institute</span>
            </a>
            <div>
                <h2 class="text-4xl font-black leading-tight">Learn IT skills.<br>Earn certificates.<br>Get hired.</h2>
                <p class="mt-4 max-w-md text-brand-100">Networking, cyber security and programming - taught hands-on at Kumber Maidan. Your learning, secured to your devices.</p>
            </div>
            <p class="text-sm text-brand-200">&copy; <?= date('Y') ?> IT Training Institute and College, Kumber Maidan</p>
        </div>
    </div>
    <!-- Form side -->
    <div class="flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-md">
            <?= $content ?>
        </div>
    </div>
</div>

<!-- Device fingerprint: a stable per-browser id used for the 1-mobile/1-desktop lock. -->
<script>
(function(){
    function fp(){
        try{
            var k='itti_dev_id', id=localStorage.getItem(k);
            if(!id){ id=(crypto.randomUUID?crypto.randomUUID():String(Math.random()).slice(2)); localStorage.setItem(k,id); }
            var raw=[id,navigator.userAgent,navigator.platform,screen.width+'x'+screen.height,Intl.DateTimeFormat().resolvedOptions().timeZone].join('|');
            return raw;
        }catch(e){ return navigator.userAgent; }
    }
    document.querySelectorAll('input[name="fp"]').forEach(function(el){ el.value = fp(); });
})();
</script>
</body>
</html>
