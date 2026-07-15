<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ITTI</title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-brand-900 to-ink p-6">
    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-white p-1"><img src="<?= asset('img/logo.jpg') ?>" alt="ITTI" class="h-full w-full object-contain"></span>
            <h1 class="mt-4 text-2xl font-black text-white">ITTI Admin Panel</h1>
            <p class="text-sm text-brand-200">IT Training Institute and College, Kumber Maidan</p>
        </div>

        <?php if ($err = flash('error')): ?>
            <div class="mb-4 rounded-xl border border-red-400/40 bg-red-500/15 px-4 py-3 text-sm font-semibold text-red-200"><?= e($err) ?></div>
        <?php endif; ?>
        <?php if ($m = flash('success')): ?>
            <div class="mb-4 rounded-xl border border-emerald-400/40 bg-emerald-500/15 px-4 py-3 text-sm font-semibold text-emerald-200"><?= e($m) ?></div>
        <?php endif; ?>

        <form action="/login" method="POST" class="space-y-4 rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
            <?= csrf_field() ?>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-200">Email</label>
                <input type="email" name="email" required autofocus class="w-full rounded-xl border-white/15 bg-white/10 px-4 py-3 text-white placeholder-slate-400 focus:border-brand-400 focus:ring-brand-400" placeholder="admin@itti.com.pk">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-200">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border-white/15 bg-white/10 px-4 py-3 text-white placeholder-slate-400 focus:border-brand-400 focus:ring-brand-400" placeholder="••••••••">
            </div>
            <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-500">Sign in to Admin</button>
        </form>
        <p class="mt-6 text-center text-xs text-slate-400">Authorised personnel only.</p>
    </div>
</body>
</html>
