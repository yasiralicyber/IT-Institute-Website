<?php
/** @var ?array $batch @var array $courses @var array $rooms @var array $staff */
$isEdit = $batch !== null;
$action = $isEdit ? '/batches/' . (int) $batch['id'] : '/batches';
function bf($b, $k) { return $b[$k] ?? ''; }
?>
<a href="/batches" class="text-sm font-semibold text-brand-600 hover:underline">← All batches</a>

<form action="<?= $action ?>" method="POST" class="mt-4 max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
    <?= csrf_field() ?>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Batch name *</label>
        <input name="name" required value="<?= e(bf($batch, 'name')) ?>" placeholder="e.g. CCNA - Morning Batch 2026" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Program / Course *</label>
            <select name="course_id" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Select…</option>
                <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) bf($batch, 'course_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option><?php endforeach; ?>
            </select></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Teacher</label>
            <select name="staff_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Unassigned</option>
                <?php foreach ($staff as $s): ?><option value="<?= (int) $s['id'] ?>" <?= (int) bf($batch, 'staff_id') === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
            </select></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Classroom</label>
            <select name="classroom_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">None</option>
                <?php foreach ($rooms as $r): ?><option value="<?= (int) $r['id'] ?>" <?= (int) bf($batch, 'classroom_id') === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
            </select></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Capacity</label>
            <input name="capacity" type="number" value="<?= (int) (bf($batch, 'capacity') ?: 30) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Start date</label>
            <input name="start_date" type="date" value="<?= e(bf($batch, 'start_date')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">End date</label>
            <input name="end_date" type="date" value="<?= e(bf($batch, 'end_date')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    </div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Schedule</label>
        <input name="schedule" value="<?= e(bf($batch, 'schedule')) ?>" placeholder="e.g. Mon–Fri, 9:00 AM – 12:00 PM" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Status</label>
        <select name="status" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <?php foreach (['active'=>'Active','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= bf($batch, 'status') === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select></div>
    <button class="w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Create batch' ?></button>
</form>
