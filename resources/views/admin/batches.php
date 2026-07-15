<?php /** @var array $rows */ ?>
<div class="mb-5 flex items-center justify-between">
    <p class="text-sm text-slate-500"><?= count($rows) ?> batches</p>
    <a href="/batches/create" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ New Batch</a>
</div>

<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No batches yet. Create one to start managing classes, attendance and students.</div>
<?php else: ?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($rows as $b): ?>
    <a href="/batches/<?= (int) $b['id'] ?>" class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-lg dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-bold text-slate-900 dark:text-white"><?= e($b['name']) ?></h3>
            <span class="flex-none rounded-full px-2 py-0.5 text-[10px] font-bold <?= $b['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' ?>"><?= ucfirst($b['status']) ?></span>
        </div>
        <p class="mt-1 text-sm text-brand-600"><?= e($b['course']) ?></p>
        <div class="mt-3 space-y-1 text-xs text-slate-500">
            <p>‍<?= e($b['teacher'] ?: 'No teacher assigned') ?></p>
            <p><?= e($b['room'] ?: 'No room') ?> · <?= e($b['schedule'] ?: '-') ?></p>
            <p><?= (int) $b['students'] ?> / <?= (int) $b['capacity'] ?> students</p>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
