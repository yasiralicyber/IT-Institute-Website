<?php /** @var array $rows @var string $month @var int $totalDue @var int $totalPaid */ ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="/payroll" class="flex items-center gap-2">
        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Salary month</label>
        <input type="month" name="month" value="<?= e($month) ?>" onchange="this.form.submit()" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    </form>
    <div class="flex gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center dark:border-white/10 dark:bg-slate-900"><p class="text-xs text-slate-500">Monthly payroll</p><p class="text-xl font-black text-slate-900 dark:text-white"><?= pkr($totalDue) ?></p></div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-center dark:border-emerald-500/30 dark:bg-emerald-500/10"><p class="text-xs text-slate-500">Paid this month</p><p class="text-xl font-black text-emerald-600"><?= pkr($totalPaid) ?></p></div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-center dark:border-amber-500/30 dark:bg-amber-500/10"><p class="text-xs text-slate-500">Remaining</p><p class="text-xl font-black text-amber-600"><?= pkr(max(0, $totalDue - $totalPaid)) ?></p></div>
    </div>
</div>

<p class="mb-3 text-sm text-slate-500">Set each staff member's monthly salary once, then pay it with one click at month-end. Paid salaries automatically appear in Expenses and the Day Book.</p>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">Staff</th><th class="px-5 py-3">Monthly salary</th><th class="px-5 py-3"><?= e(date('F Y', strtotime($month . '-01'))) ?></th></tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">No staff yet. Add staff under Institute → Staff.</td></tr>
        <?php else: foreach ($rows as $r): $s = $r['staff']; $paid = $r['paid']; ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3"><p class="font-bold text-slate-800 dark:text-slate-200"><?= e($s['name']) ?></p><p class="text-xs text-slate-400"><?= e($s['role'] ?: 'Staff') ?></p></td>
                <td class="px-5 py-3">
                    <form method="post" action="/payroll/<?= (int) $s['id'] ?>/salary" class="flex items-center gap-2">
                        <?= csrf_field() ?><input type="hidden" name="month" value="<?= e($month) ?>">
                        <input name="salary" type="number" min="0" value="<?= (int) $s['salary'] ?>" class="w-32 rounded-lg border-slate-300 bg-white px-3 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                        <button class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-white">Save</button>
                    </form>
                </td>
                <td class="px-5 py-3">
                    <?php if ($paid): ?>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Paid <?= pkr($paid['amount']) ?> · <?= ucfirst($paid['method']) ?></span>
                    <?php elseif ((int) $s['salary'] <= 0): ?>
                        <span class="text-xs text-slate-400">Set a salary first</span>
                    <?php else: ?>
                        <form method="post" action="/payroll/<?= (int) $s['id'] ?>/pay" onsubmit="return confirm('Pay <?= e($s['name']) ?> <?= pkr($s['salary']) ?> for <?= e(date('F Y', strtotime($month.'-01'))) ?>?')" class="flex items-center gap-2">
                            <?= csrf_field() ?><input type="hidden" name="month" value="<?= e($month) ?>"><input type="hidden" name="amount" value="<?= (int) $s['salary'] ?>">
                            <select name="method" class="rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"><option value="cash">Cash</option><option value="bank">Bank</option></select>
                            <button class="rounded-lg bg-emerald-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Pay <?= pkr($s['salary']) ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
