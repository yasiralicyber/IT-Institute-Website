<?php /** @var array $batch @var string $date @var array $students @var array $existing */ ?>
<a href="/attendance" class="text-sm font-semibold text-brand-600 hover:underline">← All batches</a>

<form method="GET" action="/attendance/<?= (int) $batch['id'] ?>" class="mt-4 flex flex-wrap items-end gap-3">
    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Date</label>
        <input type="date" name="date" value="<?= e($date) ?>" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    <button class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white dark:bg-white/10">Load</button>
    <div class="ml-auto flex gap-2">
        <a href="/attendance/<?= (int) $batch['id'] ?>/qr" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-white/15 dark:text-white">QR Check-in</a>
        <a href="/attendance/<?= (int) $batch['id'] ?>/report" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-white/15 dark:text-white">Report</a>
    </div>
</form>

<form action="/attendance/<?= (int) $batch['id'] ?>/save" method="POST" class="mt-5">
    <?= csrf_field() ?>
    <input type="hidden" name="date" value="<?= e($date) ?>">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-bold text-slate-900 dark:text-white"><?= e($batch['name']) ?> · <?= e(date('l, d M Y', strtotime($date))) ?></h2>
        <div class="flex gap-2 text-xs">
            <button type="button" onclick="setAll('present')" class="rounded-lg bg-emerald-100 px-3 py-1.5 font-bold text-emerald-700">All Present</button>
            <button type="button" onclick="setAll('absent')" class="rounded-lg bg-red-100 px-3 py-1.5 font-bold text-red-700">All Absent</button>
        </div>
    </div>

    <?php if (empty($students)): ?>
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No students in this batch. Add students from the batch page.</div>
    <?php else: ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">Roll</th><th class="px-5 py-3">Student</th><th class="px-5 py-3 text-right">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($students as $s): $cur = $existing[$s['user_id']] ?? 'present'; ?>
                <tr>
                    <td class="px-5 py-3 font-mono text-slate-500"><?= e($s['roll_no'] ?: '-') ?></td>
                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e($s['name']) ?></td>
                    <td class="px-5 py-3">
                        <?php $opts = [
                            'present' => ['Present', 'peer-checked:border-emerald-500 peer-checked:bg-emerald-100 peer-checked:text-emerald-700'],
                            'late'    => ['Late',    'peer-checked:border-amber-500 peer-checked:bg-amber-100 peer-checked:text-amber-700'],
                            'absent'  => ['Absent',  'peer-checked:border-red-500 peer-checked:bg-red-100 peer-checked:text-red-700'],
                        ]; ?>
                        <div class="flex justify-end gap-1.5" data-row>
                            <?php foreach ($opts as $val => $meta): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="status[<?= (int) $s['user_id'] ?>]" value="<?= $val ?>" <?= $cur===$val?'checked':'' ?> class="peer hidden">
                                <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 dark:border-white/10 <?= $meta[1] ?>"><?= $meta[0] ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="mt-5 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700 sm:w-auto sm:px-10">Save Attendance</button>
    <?php endif; ?>
</form>

<script>
function setAll(val){ document.querySelectorAll('[data-row]').forEach(r=>{ const i=r.querySelector('input[value="'+val+'"]'); if(i) i.checked=true; }); }
</script>
