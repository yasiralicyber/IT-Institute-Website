<?php /** @var ?array $student @var ?string $program */ ?>
<section class="mx-auto max-w-xl px-4 py-20 sm:px-6">
    <?php if ($student): ?>
    <div class="overflow-hidden rounded-2xl border border-emerald-300 bg-white shadow-lg dark:border-emerald-500/40 dark:bg-slate-900">
        <div class="flex items-center gap-3 bg-emerald-500 px-6 py-4 text-white"><span class="text-2xl">✓</span><p class="font-black">Verified Student of IT Training Institute</p></div>
        <div class="space-y-3 p-6 text-sm">
            <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Name</span><span class="font-bold text-slate-900 dark:text-white"><?= e($student['name']) ?></span></div>
            <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Registration No.</span><span class="font-mono font-bold text-brand-700 dark:text-brand-300"><?= e($student['reg_no']) ?></span></div>
            <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Program</span><span class="font-bold text-slate-900 dark:text-white"><?= e($program ?: 'General') ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500">Status</span><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $student['status']==='active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($student['status']) ?></span></div>
        </div>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-red-300 bg-red-50 p-6 text-center dark:border-red-500/40 dark:bg-red-500/10">
        <span class="text-3xl">✕</span>
        <p class="mt-2 font-black text-red-700 dark:text-red-300">Invalid ID</p>
        <p class="mt-1 text-sm text-red-600 dark:text-red-300/80">This ID card could not be verified. It may be fake or expired.</p>
    </div>
    <?php endif; ?>
    <p class="mt-6 text-center text-sm text-slate-500">IT Training Institute and College, Kumber Maidan · <a href="<?= url('/') ?>" class="font-bold text-brand-600">Visit website</a></p>
</section>
