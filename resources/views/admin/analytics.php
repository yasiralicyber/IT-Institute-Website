<?php /** @var array $kpi @var array $topCourses @var int $maxCourse @var array $perStudent */ ?>
<!-- KPI cards -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php
    $cards = [
        ['Students', $kpi['students'], 'border-l-brand-500'],
        ['Active Enrolments', $kpi['enrollments'], 'border-l-sky-500'],
        ['Avg Course Completion', $kpi['avgCompletion'] . '%', 'border-l-emerald-500'],
        ['Test Pass Rate', $kpi['passRate'] . '%', 'border-l-emerald-600'],
        ['Attendance Rate', $kpi['attRate'] . '%', 'border-l-amber-500'],
        ['Certificates Issued', $kpi['certs'], 'border-l-gold-500'],
        ['Fees Collected', pkr($kpi['feeCollected']), 'border-l-emerald-600'],
        ['Online Revenue', pkr($kpi['online']), 'border-l-brand-500'],
    ];
    foreach ($cards as [$l, $v, $accent]): ?>
    <div class="rounded-2xl border border-l-4 border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900 <?= $accent ?>">
        <p class="text-2xl font-black text-slate-900 dark:text-white"><?= is_int($v) ? $v : e($v) ?></p>
        <p class="mt-1 text-sm font-medium text-slate-500"><?= e($l) ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <!-- Top courses -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="mb-4 font-bold text-slate-900 dark:text-white">Top Courses by Enrolment</h3>
        <?php if (empty($topCourses)): ?><p class="text-sm text-slate-500">No enrolments yet.</p>
        <?php else: foreach ($topCourses as $c): ?>
        <div class="mb-3">
            <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($c['title']) ?></span><span class="font-bold text-brand-600"><?= (int) $c['n'] ?></span></div>
            <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-brand-600" style="width: <?= round((int)$c['n']/$maxCourse*100) ?>%"></div></div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Fee collection -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="mb-4 font-bold text-slate-900 dark:text-white">Fee Collection</h3>
        <?php $billed = max(1, $kpi['feeBilled']); $pct = round($kpi['feeCollected'] / $billed * 100); ?>
        <p class="text-3xl font-black text-emerald-600"><?= pkr($kpi['feeCollected']) ?></p>
        <p class="text-sm text-slate-500">collected of <?= pkr($kpi['feeBilled']) ?> billed</p>
        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-emerald-500" style="width: <?= min(100,$pct) ?>%"></div></div>
        <p class="mt-2 text-sm"><span class="font-bold text-red-600"><?= pkr($kpi['feeBilled'] - $kpi['feeCollected']) ?></span> <span class="text-slate-500">outstanding</span></p>
    </div>
</div>

<!-- Per-student performance -->
<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <div class="border-b border-slate-100 px-5 py-4 dark:border-white/10"><h3 class="font-bold text-slate-900 dark:text-white">Student Performance</h3></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Courses</th><th class="px-5 py-3">Avg Test Score</th><th class="px-5 py-3">Attendance</th><th class="px-5 py-3">Certificates</th><th class="px-5 py-3">Fee Balance</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php if (empty($perStudent)): ?><tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">No students yet.</td></tr>
                <?php else: foreach ($perStudent as $s): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e($s['name']) ?></td>
                    <td class="px-5 py-3"><?= $s['enrolls'] ?></td>
                    <td class="px-5 py-3"><span class="font-bold <?= $s['score']>=70?'text-emerald-600':($s['score']>0?'text-amber-600':'text-slate-400') ?>"><?= $s['score'] ? $s['score'].'%' : '-' ?></span></td>
                    <td class="px-5 py-3"><?= $s['att']!==null ? '<span class="font-bold '.($s['att']>=75?'text-emerald-600':'text-red-600').'">'.$s['att'].'%</span>' : '<span class="text-slate-400">-</span>' ?></td>
                    <td class="px-5 py-3"><?= $s['certs'] ?></td>
                    <td class="px-5 py-3"><?= $s['balance']>0 ? '<span class="font-bold text-red-600">'.pkr($s['balance']).'</span>' : '<span class="text-emerald-600">Clear</span>' ?></td>
                    <td class="px-5 py-3 text-right"><a href="/students/<?= $s['id'] ?>/timeline" class="font-bold text-brand-600 hover:underline">View</a></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
