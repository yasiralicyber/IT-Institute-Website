<?php /** @var array $rows */ ?>
<div class="mb-5 flex items-center justify-between">
    <p class="text-sm text-slate-500">Select a batch to take or view attendance.</p>
    <a href="/attendance-guide" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:text-white dark:hover:bg-white/5">Connect Fingerprint Device</a>
</div>

<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No active batches. <a href="/batches/create" class="font-bold text-brand-600">Create a batch</a> first.</div>
<?php else: ?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($rows as $b): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white"><?= e($b['name']) ?></h3>
        <p class="text-sm text-brand-600"><?= e($b['course']) ?></p>
        <p class="mt-1 text-xs text-slate-500"><?= (int) $b['students'] ?> students · <?= e($b['teacher'] ?: 'No teacher') ?></p>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <a href="/attendance/<?= (int) $b['id'] ?>" class="rounded-lg bg-brand-600 py-2 text-center text-sm font-bold text-white hover:bg-brand-700">Mark Today</a>
            <a href="/attendance/<?= (int) $b['id'] ?>/report" class="rounded-lg bg-slate-100 py-2 text-center text-sm font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-200">Report</a>
            <a href="/attendance/<?= (int) $b['id'] ?>/qr" class="rounded-lg border border-slate-200 py-2 text-center text-sm font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300">QR Check-in</a>
            <a href="/attendance/<?= (int) $b['id'] ?>/import" class="rounded-lg border border-slate-200 py-2 text-center text-sm font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300">Import CSV</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
