<?php /** @var ?string $intent @var ?string $course */ ?>
<div class="mb-8 lg:hidden">
    <a href="<?= url('/') ?>" class="flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-white p-1 ring-1 ring-black/5">
            <img src="<?= asset('img/logo.jpg') ?>" alt="ITTI" class="h-full w-full object-contain">
        </span>
        <span class="font-extrabold text-slate-900 dark:text-white">IT Training Institute</span>
    </a>
</div>

<h1 class="text-3xl font-black text-slate-900 dark:text-white">Welcome back</h1>
<p class="mt-2 text-slate-500 dark:text-slate-400">Log in to access your courses and certificates.</p>

<?php if ($err = flash('error')): ?>
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"><?= e($err) ?></div>
<?php endif; ?>
<?php if ($msg = flash('success')): ?>
    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"><?= e($msg) ?></div>
<?php endif; ?>

<form action="<?= url('/login') ?>" method="POST" class="mt-8 space-y-5">
    <?= csrf_field() ?>
    <input type="hidden" name="fp" value="">
    <input type="hidden" name="intent" value="<?= e($intent) ?>">
    <input type="hidden" name="course" value="<?= e($course) ?>">
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Email</label>
        <input type="email" name="email" required autofocus class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="you@example.com">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Password</label>
        <input type="password" name="password" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="••••••••">
    </div>
    <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white shadow-lg shadow-brand-900/20 transition hover:bg-brand-700">Log in</button>
</form>

<div class="mt-6 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
    <strong>Device security:</strong> your account works on only <strong>one mobile + one computer</strong>. Sharing your login will lock your account.
</div>

<p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
    New here? <a href="<?= url('/register') ?>" class="font-bold text-brand-600 hover:underline">Create an account</a>
</p>
