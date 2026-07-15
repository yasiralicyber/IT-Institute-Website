<?php /** @var array $batch @var string $month @var array $students @var array $grid @var int $days */ ?>
<a href="/attendance" class="text-sm font-semibold text-brand-600 hover:underline">← All batches</a>

<form method="GET" action="/attendance/<?= (int) $batch['id'] ?>/report" class="mt-4 flex flex-wrap items-end gap-3">
    <div><label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Month</label>
        <input type="month" name="month" value="<?= e($month) ?>" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    <button class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white dark:bg-white/10">View</button>
    <a href="/attendance/<?= (int) $batch['id'] ?>" class="ml-auto rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Mark Attendance</a>
    <button type="button" onclick="window.print()" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 dark:border-white/15 dark:text-white">Print</button>
</form>

<h2 class="mt-5 font-bold text-slate-900 dark:text-white"><?= e($batch['name']) ?> · <?= e(date('F Y', strtotime($month . '-01'))) ?></h2>

<div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-xs">
        <thead class="bg-slate-50 text-slate-500 dark:bg-white/5">
            <tr>
                <th class="sticky left-0 bg-slate-50 px-3 py-2 text-left dark:bg-white/5">Student</th>
                <?php for ($d = 1; $d <= $days; $d++): ?><th class="px-1.5 py-2 text-center font-semibold"><?= $d ?></th><?php endfor; ?>
                <th class="px-2 py-2 text-center text-emerald-600">P</th><th class="px-2 py-2 text-center text-amber-600">L</th><th class="px-2 py-2 text-center text-red-600">A</th><th class="px-2 py-2 text-center">%</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($students)): ?>
                <tr><td colspan="<?= $days + 5 ?>" class="px-4 py-8 text-center text-slate-500">No students in this batch.</td></tr>
            <?php else: foreach ($students as $s):
                $row = $grid[$s['user_id']] ?? [];
                $p = $l = $a = 0;
                foreach ($row as $st) { $st === 'present' ? $p++ : ($st === 'late' ? $l++ : $a++); }
                $total = $p + $l + $a; $pct = $total ? round(($p + $l) / $total * 100) : 0; ?>
            <tr>
                <td class="sticky left-0 bg-white px-3 py-2 font-bold text-slate-800 dark:bg-slate-900 dark:text-white"><?= e($s['name']) ?></td>
                <?php for ($d = 1; $d <= $days; $d++): $st = $row[$d] ?? null;
                    $cell = $st === 'present' ? ['P','bg-emerald-100 text-emerald-700'] : ($st === 'late' ? ['L','bg-amber-100 text-amber-700'] : ($st === 'absent' ? ['A','bg-red-100 text-red-700'] : ['·','text-slate-300'])); ?>
                <td class="px-1 py-1 text-center"><span class="inline-flex h-5 w-5 items-center justify-center rounded text-[10px] font-bold <?= $cell[1] ?>"><?= $cell[0] ?></span></td>
                <?php endfor; ?>
                <td class="px-2 py-2 text-center font-bold text-emerald-600"><?= $p ?></td>
                <td class="px-2 py-2 text-center font-bold text-amber-600"><?= $l ?></td>
                <td class="px-2 py-2 text-center font-bold text-red-600"><?= $a ?></td>
                <td class="px-2 py-2 text-center font-black <?= $pct >= 75 ? 'text-emerald-600' : 'text-red-600' ?>"><?= $pct ?>%</td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<p class="mt-2 text-xs text-slate-400">P = Present · L = Late · A = Absent · % counts present + late as attended.</p>
