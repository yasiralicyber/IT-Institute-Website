<?php
/** @var array $faculty */
$inst = config('institute');
$ph_title = 'Our Faculty'; $ph_sub = 'Learn from certified, experienced instructors who are invested in your success.'; $ph_img = 'faculty/1.jpg';
include BASE_PATH . '/resources/views/partials/page-header.php';
?>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="space-y-10">
        <?php foreach ($faculty as $i => $f): ?>
        <?php $skills = array_values(array_filter(array_map('trim', explode(',', (string) ($f['expertise'] ?? ''))))); ?>
        <div class="grid items-center gap-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/60 lg:grid-cols-3 lg:p-8 <?= $i % 2 ? 'lg:[&>div:first-child]:order-2' : '' ?>" data-reveal>
            <div class="relative overflow-hidden rounded-2xl">
                <?php if (!empty($f['photo'])): ?>
                    <img src="<?= url('/staff-photo/' . (int) $f['id']) ?>" alt="<?= e($f['name']) ?>" class="h-72 w-full object-cover lg:h-80">
                <?php else: ?>
                    <span class="flex h-72 w-full items-center justify-center bg-brand-600 text-2xl font-black text-white lg:h-80"><?= e(strtoupper(substr($f['name'], 0, 1))) ?></span>
                <?php endif; ?>
                <?php if (!empty($f['role'])): ?>
                <span class="absolute left-3 top-3 rounded-full bg-gold-500 px-3 py-1 text-xs font-bold text-brand-950"><?= e($f['role']) ?></span>
                <?php endif; ?>
            </div>
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white"><?= e($f['name']) ?></h2>
                </div>
                <?php if (!empty($f['role'])): ?>
                <p class="mt-1 font-semibold text-gold-600"><?= e($f['role']) ?></p>
                <?php endif; ?>
                <?php if (!empty($f['bio'])): ?>
                <p class="mt-4 text-slate-600 dark:text-slate-300"><?= e($f['bio']) ?></p>
                <?php endif; ?>
                <?php if ($skills): ?>
                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Areas of Expertise</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php foreach ($skills as $ex): ?>
                        <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 dark:border-white/10 dark:text-slate-200"><?= e($ex) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-14 rounded-3xl bg-brand-950 p-10 text-center text-white" data-reveal>
        <h2 class="text-2xl font-black sm:text-3xl">Want to learn from our team?</h2>
        <p class="mx-auto mt-3 max-w-xl text-brand-100">Browse our courses or apply for admission and start learning with expert instructors.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="<?= url('/courses') ?>" class="rounded-xl bg-gold-500 px-6 py-3 font-bold text-brand-950 hover:bg-gold-400">View Courses</a>
            <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="rounded-xl border border-white/25 px-6 py-3 font-bold text-white hover:bg-white/10">▶ Free YouTube</a>
        </div>
    </div>
</section>
