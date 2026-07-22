<?php /** @var string $content */ $loggedIn = !empty($_SESSION['guardian_student_id']); ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Guardian Portal') ?></title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
<header class="bg-brand-900 text-white">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="<?= url('/') ?>" class="flex items-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-white p-0.5"><img src="<?= url('/site-logo') ?>" alt="ITTI" class="h-full w-full object-contain"></span>
            <span class="leading-tight"><span class="block text-sm font-extrabold">IT Training Institute</span><span class="block text-[10px] uppercase tracking-widest text-gold-300">Guardian Portal</span></span>
        </a>
        <?php if ($loggedIn): ?>
            <a href="<?= url('/guardian/logout') ?>" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-bold hover:bg-white/20">Log out</a>
        <?php endif; ?>
    </div>
</header>
<main class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
    <?php if ($m = flash('error')): ?><div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"><?= e($m) ?></div><?php endif; ?>
    <?= $content ?>
</main>
<footer class="py-8 text-center text-xs text-slate-400">&copy; <?= date('Y') ?> IT Training Institute and College, Kumber Maidan · Guardian Portal</footer>
</body>
</html>
