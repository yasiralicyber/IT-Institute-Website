<?php /** @var array $rows */ ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Class Timetable</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Current batches and class schedule. Managed and updated by the institute from the admin panel.</p>
    </div>
</section>

<section class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
    <?php if (empty($rows)): ?>
        <p class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 dark:border-white/10">The timetable will be published here soon. Message us on WhatsApp for current timings.</p>
    <?php else: foreach ($rows as $row): ?>
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/60">
            <div class="border-b border-slate-100 bg-slate-50 px-6 py-4 dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($row['title']) ?></h2>
            </div>
            <?php if (!empty($row['image_path'])): ?>
                <img src="<?= url('/timetable-image/' . (int) $row['id']) ?>" alt="Timetable" class="w-full">
            <?php endif; ?>
            <?php if (!empty($row['body'])): ?>
                <div class="whitespace-pre-line px-6 py-5 text-slate-700 dark:text-slate-300"><?= e($row['body']) ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; endif; ?>
</section>
