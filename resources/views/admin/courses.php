<?php /** @var array $courses */ ?>
<div class="mb-5 flex items-center justify-between">
    <p class="text-sm text-slate-500"><?= count($courses) ?> courses</p>
    <a href="/courses/create" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ New Course</a>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($courses as $c): ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="h-20" style="background:linear-gradient(135deg,<?= e($c['accent'] ?: '#2f5078') ?>,#0a121f)"></div>
        <div class="p-5">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-bold text-slate-900 dark:text-white"><?= e($c['title']) ?></h3>
                <span class="flex-none rounded-full px-2 py-0.5 text-[10px] font-bold <?= $c['is_published'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' ?>"><?= $c['is_published'] ? 'Live' : 'Draft' ?></span>
            </div>
            <p class="mt-1 text-xs text-slate-500"><?= e($c['category']) ?> · <?= pkr($c['price']) ?></p>
            <p class="mt-2 text-xs text-slate-500"><?= (int) $c['lectures'] ?> lectures · <?= (int) $c['students'] ?> students</p>
            <div class="mt-4 flex gap-2">
                <a href="/courses/<?= (int) $c['id'] ?>/edit" class="flex-1 rounded-lg bg-brand-50 px-3 py-2 text-center text-sm font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">Edit</a>
                <form action="/courses/<?= (int) $c['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this course and ALL its content? This cannot be undone.')">
                    <?= csrf_field() ?>
                    <button class="rounded-lg border border-red-300 px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:hover:bg-red-500/10">Delete</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
