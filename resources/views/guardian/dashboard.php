<?php /** @var array $student @var array $att @var array $fees @var array $results @var array $progress @var int $overallPct @var array $scoreTrend @var array $examCards @var array $tally @var array $notices @var array $timetable @var array $batches */ ?>

<!-- Hero -->
<div class="mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-800 via-brand-900 to-brand-950 p-6 text-white shadow-xl sm:p-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-widest text-gold-300">Guardian Dashboard</p>
            <h1 class="mt-1 text-3xl font-black"><?= e($student['name']) ?></h1>
            <p class="mt-1 text-sm text-brand-200"><?= e($student['reg_no']) ?><?php foreach ($batches as $b): ?> · <?= e($b['name']) ?><?php endforeach; ?></p>
        </div>
        <div class="text-center text-brand-100">
            <?= svg_gauge((int) $overallPct, '#f5b301', 'Course progress', 116) ?>
        </div>
    </div>
</div>

<!-- KPI cards -->
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between"><p class="text-sm text-slate-500">Attendance</p><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"><?= icon('check','h-4 w-4') ?></span></div>
        <p class="mt-1 text-3xl font-black <?= $att['pct'] >= 75 ? 'text-emerald-600' : 'text-amber-600' ?>"><?= $att['pct'] ?>%</p>
        <p class="text-xs text-slate-400"><?= $att['present'] ?> present · <?= $att['late'] ?> late · <?= $att['absent'] ?> absent</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between"><p class="text-sm text-slate-500">Fee Status</p><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-500/10"><?= icon('money','h-4 w-4') ?></span></div>
        <p class="mt-1 text-2xl font-black <?= $fees['balance'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>"><?= $fees['balance'] > 0 ? pkr($fees['balance']) : 'Clear ✓' ?></p>
        <p class="text-xs text-slate-400">Paid <?= pkr($fees['paid']) ?> of <?= pkr($fees['billed']) ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between"><p class="text-sm text-slate-500">Tests Passed</p><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gold-50 text-gold-600 dark:bg-gold-500/10"><?= icon('trophy','h-4 w-4') ?></span></div>
        <p class="mt-1 text-3xl font-black text-brand-700 dark:text-brand-300"><?= (int) $tally['pass'] ?></p>
        <p class="text-xs text-slate-400"><?= count($results) ?> attempts recorded</p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between"><p class="text-sm text-slate-500">Course Progress</p><span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10"><?= icon('chart','h-4 w-4') ?></span></div>
        <p class="mt-1 text-3xl font-black text-sky-600"><?= $overallPct ?>%</p>
        <p class="text-xs text-slate-400">across <?= count($progress) ?> course(s)</p>
    </div>
</div>

<!-- Charts row -->
<div class="mb-6 grid gap-6 lg:grid-cols-3">
    <!-- Score trend -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900 lg:col-span-2">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="font-black text-slate-900 dark:text-white">Test Score Trend</h2>
            <span class="text-xs text-slate-400">last <?= count($scoreTrend) ?> tests · %</span>
        </div>
        <?= svg_line($scoreTrend, '#274a70', 560, 170) ?>
    </div>
    <!-- Pass/fail donut -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-3 font-black text-slate-900 dark:text-white">Pass vs Fail</h2>
        <?php $tot = max(1, $tally['pass'] + $tally['fail']); $passPct = (int) round($tally['pass'] / $tot * 100); ?>
        <div class="flex flex-col items-center">
            <?= svg_gauge($passPct, '#16a34a', 'pass rate', 132) ?>
            <div class="mt-3 flex gap-4 text-sm">
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-emerald-500"></span> Pass <?= (int) $tally['pass'] ?></span>
                <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-full bg-slate-300"></span> Fail <?= (int) $tally['fail'] ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Attendance by month + course progress -->
<div class="mb-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-3 font-black text-slate-900 dark:text-white">Attendance by Month</h2>
        <?= svg_bars($att['monthly'], '#0891b2', 170) ?>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 font-black text-slate-900 dark:text-white">Course Progress</h2>
        <?php if (empty($progress)): ?><p class="text-sm text-slate-500">Not enrolled in any course yet.</p>
        <?php else: foreach ($progress as $p): ?>
        <div class="mb-3.5">
            <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($p['title']) ?></span><span class="font-bold text-brand-600"><?= $p['pct'] ?>%</span></div>
            <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700" style="width: <?= $p['pct'] ?>%"></div></div>
            <p class="mt-1 text-xs text-slate-400"><?= $p['done'] ?> / <?= $p['total'] ?> lessons completed</p>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Published exam results -->
<?php if (!empty($examCards)): ?>
<div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
    <h2 class="mb-4 font-black text-slate-900 dark:text-white">Published Exam Results</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($examCards as $ec): $r = $ec['r']; ?>
        <div class="rounded-2xl border <?= $r['passed'] ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' : 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' ?> p-4">
            <p class="font-bold text-slate-900 dark:text-white"><?= e($ec['title']) ?></p>
            <div class="mt-2 flex items-end justify-between">
                <div><p class="text-3xl font-black <?= $r['passed'] ? 'text-emerald-600' : 'text-red-600' ?>"><?= $r['percent'] ?>%</p><p class="text-xs text-slate-400">Grade <?= e($r['grade']) ?></p></div>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $r['passed'] ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' ?>"><?= $r['passed'] ? 'PASS' : 'FAIL' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="grid gap-6 lg:grid-cols-2">
    <!-- Recent tests -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 font-black text-slate-900 dark:text-white">Recent Test Results</h2>
        <?php if (empty($results)): ?><p class="text-sm text-slate-500">No tests taken yet.</p>
        <?php else: foreach (array_slice($results, 0, 8) as $r): ?>
        <div class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2.5 text-sm dark:border-white/5">
            <div><p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($r['chapter']) ?></p><p class="text-xs text-slate-400"><?= e($r['course']) ?> · <?= e(date('d M Y', strtotime($r['created_at']))) ?></p></div>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $r['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= (int) $r['score'] ?>%</span>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Notices + timetable + attendance heatstrip -->
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h2 class="mb-3 font-black text-slate-900 dark:text-white">Recent Attendance</h2>
            <div class="flex flex-wrap gap-1.5">
                <?php foreach ($att['recent'] as $a): $cls = $a['status']==='present'?'bg-emerald-100 text-emerald-700':($a['status']==='late'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700'); ?>
                <span class="rounded-lg px-2 py-1 text-xs font-bold <?= $cls ?>" title="<?= e($a['date']) ?>"><?= e(date('d M', strtotime($a['date']))) ?></span>
                <?php endforeach; ?>
                <?php if (empty($att['recent'])): ?><span class="text-sm text-slate-500">No attendance records yet.</span><?php endif; ?>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h2 class="mb-3 font-black text-slate-900 dark:text-white">Notices</h2>
            <?php if (empty($notices)): ?><p class="text-sm text-slate-500">No notices.</p>
            <?php else: foreach ($notices as $n): ?>
            <div class="mb-3 border-l-4 border-gold-400 pl-3">
                <p class="font-bold text-slate-800 dark:text-slate-100"><?= e($n['title']) ?></p>
                <?php if ($n['body']): ?><p class="text-sm text-slate-500"><?= e($n['body']) ?></p><?php endif; ?>
                <p class="text-xs text-slate-400"><?= e(date('d M Y', strtotime($n['created_at']))) ?></p>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
