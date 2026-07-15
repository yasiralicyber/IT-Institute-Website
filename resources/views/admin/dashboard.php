<?php /** @var array $stats @var array $admissions @var array $payments @var array $expenses */
use App\Controllers\Admin\ExpenseController;
$expCats = ExpenseController::categories();
$h = (int) date('G'); $greet = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
$netMonth = $stats['fee_month'] - $stats['exp_month'];
?>
<div class="mb-5">
    <h2 class="text-xl font-black text-slate-900 dark:text-white"><?= $greet ?>! Here is today at a glance.</h2>
    <p class="text-sm text-slate-500"><?= e(date('l, d F Y')) ?></p>
</div>

<!-- Things that need action today (only show if there is something) -->
<?php $todo = [];
if ($stats['pending_pay']) $todo[] = ['/purchases', $stats['pending_pay'] . ' online payment' . ($stats['pending_pay']==1?'':'s') . ' to approve', 'amber'];
if ($stats['adm_new']) $todo[] = ['/admissions', $stats['adm_new'] . ' new admission' . ($stats['adm_new']==1?'':'s') . ' to contact', 'brand'];
if ($stats['fee_due_count']) $todo[] = ['/students?filter=fee_due', $stats['fee_due_count'] . ' student' . ($stats['fee_due_count']==1?'':'s') . ' owe fees', 'red'];
if ($todo): ?>
<div class="mb-5 flex flex-wrap gap-2">
    <?php foreach ($todo as [$href,$txt,$tone]): $c = ['amber'=>'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300','brand'=>'border-brand-300 bg-brand-50 text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300','red'=>'border-red-300 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'][$tone]; ?>
    <a href="<?= e($href) ?>" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-bold <?= $c ?>"><?= e($txt) ?> →</a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Key numbers -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    <?php
    $cards = [
        ['Active Students', $stats['students'], '/students', 'text-slate-900 dark:text-white'],
        ['Present Today', $stats['present'], '/attendance', 'text-sky-600'],
        ["Today's Collection", pkr($stats['fee_today']), '/fees', 'text-emerald-600'],
        ["Today's Expenses", pkr($stats['exp_today']), '/expenses', 'text-red-600'],
        ['Outstanding Fees', pkr($stats['outstanding']), '/students?filter=fee_due', 'text-amber-600'],
        ['Students Owing', $stats['fee_due_count'], '/students?filter=fee_due', 'text-amber-600'],
    ];
    foreach ($cards as [$label,$val,$href,$cls]): ?>
    <a href="<?= e($href) ?>" class="rounded-2xl border border-slate-200 bg-white p-5 hover:shadow-md dark:border-white/10 dark:bg-slate-900">
        <p class="text-2xl font-black <?= $cls ?>"><?= $val ?></p>
        <p class="mt-1 text-sm font-medium text-slate-500"><?= e($label) ?></p>
    </a>
    <?php endforeach; ?>
</div>

<!-- This month money summary -->
<div class="mt-4 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10"><p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Collected this month</p><p class="mt-1 text-2xl font-black text-emerald-600"><?= pkr($stats['fee_month']) ?></p></div>
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-500/30 dark:bg-red-500/10"><p class="text-xs font-bold uppercase tracking-wider text-red-700">Spent this month</p><p class="mt-1 text-2xl font-black text-red-600"><?= pkr($stats['exp_month']) ?></p></div>
    <div class="rounded-2xl border <?= $netMonth >= 0 ? 'border-brand-200 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10' : 'border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' ?> p-5"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">Net this month</p><p class="mt-1 text-2xl font-black <?= $netMonth >= 0 ? 'text-brand-700 dark:text-brand-300' : 'text-amber-600' ?>"><?= ($netMonth < 0 ? '-' : '') . pkr(abs($netMonth)) ?></p></div>
</div>

<!-- Quick actions -->
<div class="mt-6">
    <h3 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-400">Quick actions</h3>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <?php
        $actions = [
            ['/fees', 'Record Fee', 'money'], ['/expenses', 'Add Expense', 'doc'], ['/attendance', 'Attendance', 'cal'],
            ['/payroll', 'Pay Salary', 'users'], ['/students', 'Students', 'users'], ['/analytics', 'Reports', 'chart'],
        ];
        $I = ['money'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','doc'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','cal'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','users'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z','chart'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'];
        foreach ($actions as [$href,$label,$ic]): ?>
        <a href="<?= e($href) ?>" class="flex flex-col items-center gap-2 rounded-2xl border border-slate-200 bg-white py-4 text-center text-sm font-bold text-slate-700 hover:border-brand-400 hover:shadow-md dark:border-white/10 dark:bg-slate-900 dark:text-white">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $I[$ic] ?>"/></svg></span>
            <?= e($label) ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Recent money in / out -->
<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="mb-3 flex items-center justify-between"><h3 class="font-black text-slate-900 dark:text-white">Recent fee payments</h3><a href="/fees" class="text-sm font-bold text-brand-600 hover:underline">All fees →</a></div>
        <?php if (empty($payments)): ?><p class="text-sm text-slate-500">No payments yet.</p>
        <?php else: foreach ($payments as $p): ?>
        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-white/5">
            <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($p['name']) ?> <span class="text-xs text-slate-400"><?= e(date('d M', strtotime($p['paid_at']))) ?></span></span>
            <span class="font-bold text-emerald-600">+<?= pkr($p['amount']) ?></span>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="mb-3 flex items-center justify-between"><h3 class="font-black text-slate-900 dark:text-white">Recent expenses</h3><a href="/expenses" class="text-sm font-bold text-brand-600 hover:underline">All expenses →</a></div>
        <?php if (empty($expenses)): ?><p class="text-sm text-slate-500">No expenses yet.</p>
        <?php else: foreach ($expenses as $x): ?>
        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-white/5">
            <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($expCats[$x['category']][0] ?? ucfirst($x['category'])) ?> <span class="text-xs text-slate-400"><?= e($x['payee'] ?: '') ?> · <?= e(date('d M', strtotime($x['date']))) ?></span></span>
            <span class="font-bold text-red-600">-<?= pkr($x['amount']) ?></span>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- New admissions to contact -->
<?php if (!empty($admissions)): ?>
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <div class="mb-3 flex items-center justify-between"><h3 class="font-black text-slate-900 dark:text-white">New admissions to contact</h3><a href="/admissions" class="text-sm font-bold text-brand-600 hover:underline">All admissions →</a></div>
    <div class="grid gap-2 sm:grid-cols-2">
        <?php foreach ($admissions as $a): ?>
        <a href="/admissions" class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2 text-sm dark:border-white/5">
            <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($a['name']) ?></span>
            <span class="text-xs text-slate-400"><?= e($a['phone'] ?? '') ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
