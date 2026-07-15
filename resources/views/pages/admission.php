<?php /** @var array $programs */ ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Admission Form</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Apply to IT Training Institute and College, Kumber Maidan. Fill in your details and our team will contact you to confirm your enrollment.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
    <?php if ($msg = flash('success')): ?>
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-semibold text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">✓ <?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($err = flash('error')): ?>
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"><?= e($err) ?></div>
    <?php endif; ?>

    <form action="<?= url('/admission') ?>" method="POST" enctype="multipart/form-data"
          class="space-y-6 rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-slate-900/60">
        <?= csrf_field() ?>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Full Name *</label>
                <input name="name" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Father's Name *</label>
                <input name="father_name" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Address</label>
                <input name="address" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Contact No *</label>
                <input name="contact" required placeholder="03XX-XXXXXXX" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Email</label>
                <input type="email" name="email" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Date of Birth</label>
                <input type="date" name="dob" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Form B / B-Form Number</label>
                <input name="form_b" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Gender</label>
                <select name="gender" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <option value="">Select…</option>
                    <option>Male</option><option>Female</option><option>Other</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Program *</label>
                <select name="programs" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <option value="">Select a program…</option>
                    <?php foreach ($programs as $p): ?><option><?= e($p['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Your Photo</label>
                <input type="file" name="photo" accept="image/*" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:font-bold file:text-white dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <p class="mt-1 text-xs text-slate-400">JPG or PNG, up to 4MB.</p>
            </div>
        </div>

        <button class="w-full rounded-xl bg-brand-600 py-4 text-lg font-bold text-white shadow-lg shadow-brand-600/30 hover:bg-brand-700">Submit Application</button>
        <p class="text-center text-xs text-slate-400">Your information is stored securely and used only for admission purposes.</p>
    </form>
</section>
