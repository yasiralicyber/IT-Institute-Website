<?php /** @var array $batch @var array $students @var array $available */ ?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="/batches" class="text-sm font-semibold text-brand-600 hover:underline">← All batches</a>
    <a href="/batches/<?= (int) $batch['id'] ?>/edit" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:text-white dark:hover:bg-white/5">Edit batch</a>
</div>

<!-- Batch info -->
<div class="mb-6 grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900 sm:grid-cols-2 lg:grid-cols-4">
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Program</p><p class="font-bold text-slate-900 dark:text-white"><?= e($batch['course']) ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Teacher</p><p class="font-bold text-slate-900 dark:text-white"><?= e($batch['teacher'] ?: '-') ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Classroom</p><p class="font-bold text-slate-900 dark:text-white"><?= e($batch['room'] ?: '-') ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Schedule</p><p class="font-bold text-slate-900 dark:text-white"><?= e($batch['schedule'] ?: '-') ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Duration</p><p class="font-bold text-slate-900 dark:text-white"><?= e($batch['start_date'] ?: '?') ?> → <?= e($batch['end_date'] ?: '?') ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Capacity</p><p class="font-bold text-slate-900 dark:text-white"><?= count($students) ?> / <?= (int) $batch['capacity'] ?></p></div>
    <div><p class="text-xs uppercase tracking-wider text-slate-400">Status</p><p class="font-bold text-slate-900 dark:text-white"><?= ucfirst($batch['status']) ?></p></div>
</div>

<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <!-- Enrolled students -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-white/10">
            <h2 class="font-bold text-slate-900 dark:text-white">Enrolled Students (<?= count($students) ?>)</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">Roll</th><th class="px-5 py-3">Student</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php if (empty($students)): ?>
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No students in this batch yet.</td></tr>
                <?php else: foreach ($students as $s): ?>
                <tr>
                    <td class="px-5 py-3 font-mono text-slate-500"><?= e($s['roll_no'] ?: '-') ?></td>
                    <td class="px-5 py-3"><a href="/students/<?= (int) $s['user_id'] ?>" class="font-bold text-slate-900 hover:text-brand-700 dark:text-white"><?= e($s['name']) ?></a></td>
                    <td class="px-5 py-3 text-slate-500"><?= e($s['phone'] ?: $s['email']) ?></td>
                    <td class="px-5 py-3 text-right">
                        <form action="/batches/<?= (int) $batch['id'] ?>/remove" method="POST" onsubmit="return confirm('Remove from batch?')" class="inline">
                            <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $s['user_id'] ?>">
                            <button class="text-xs font-bold text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add student -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">Add a student</h3>
        <?php if (empty($available)): ?>
            <p class="mt-3 text-sm text-slate-500">All students are already in a batch, or none exist yet.</p>
        <?php else: ?>
        <form action="/batches/<?= (int) $batch['id'] ?>/enroll" method="POST" class="mt-3 space-y-3">
            <?= csrf_field() ?>
            <select name="user_id" required class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Select student…</option>
                <?php foreach ($available as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option><?php endforeach; ?>
            </select>
            <input name="roll_no" placeholder="Roll no. (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Add to batch</button>
        </form>
        <?php endif; ?>
        <p class="mt-3 text-xs text-slate-400">Students must have an account. They register on the website or you create them via admissions.</p>
    </div>
</div>
