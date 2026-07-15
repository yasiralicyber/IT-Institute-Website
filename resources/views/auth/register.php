<?php /** @var ?string $intent @var ?string $course */ ?>
<div class="mb-8 lg:hidden">
    <a href="<?= url('/') ?>" class="flex items-center gap-3">
        <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-white p-1 ring-1 ring-black/5">
            <img src="<?= asset('img/logo.jpg') ?>" alt="ITTI" class="h-full w-full object-contain">
        </span>
        <span class="font-extrabold text-slate-900 dark:text-white">IT Training Institute</span>
    </a>
</div>

<h1 class="text-3xl font-black text-slate-900 dark:text-white">Create your account</h1>
<p class="mt-2 text-slate-500 dark:text-slate-400">Start with 5 free lessons in every course.</p>

<?php if ($err = flash('error')): ?>
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"><?= e($err) ?></div>
<?php endif; ?>

<form action="<?= url('/register') ?>" method="POST" class="mt-8 space-y-5">
    <?= csrf_field() ?>
    <input type="hidden" name="fp" value="">
    <input type="hidden" name="intent" value="<?= e($intent) ?>">
    <input type="hidden" name="course" value="<?= e($course) ?>">
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Full Name</label>
        <input name="name" required autofocus class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Email</label>
        <input type="email" name="email" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Phone / WhatsApp</label>
        <input name="phone" placeholder="03XX-XXXXXXX" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
    </div>
    <div>
        <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Password</label>
        <input type="password" name="password" required minlength="8" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="At least 8 characters">
    </div>
    <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white shadow-lg shadow-brand-900/20 transition hover:bg-brand-700">Create account</button>
</form>

<p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
    Already have an account? <a href="<?= url('/login') ?>" class="font-bold text-brand-600 hover:underline">Log in</a>
</p>
