<?php /** @var string $date @var ?array $book @var int $opening @var array $payments @var array $expenses @var int $cashIn @var int $cashOut @var int $expected @var array $recent */
$closed = $book && $book['status'] === 'closed';
$inp = 'rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<form method="get" action="/daybook" class="mb-5 flex items-center gap-3">
    <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Date</label>
    <input type="date" name="date" value="<?= e($date) ?>" onchange="this.form.submit()" class="<?= $inp ?>">
    <?php if ($closed): ?><span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-600">Closed</span><?php else: ?><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Open</span><?php endif; ?>
</form>

<!-- Summary cards -->
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-xs text-slate-500">Opening cash</p><p class="text-2xl font-black text-slate-900 dark:text-white"><?= pkr($opening) ?></p></div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10"><p class="text-xs text-slate-500">Cash in (today)</p><p class="text-2xl font-black text-emerald-600">+<?= pkr($cashIn) ?></p></div>
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-500/30 dark:bg-red-500/10"><p class="text-xs text-slate-500">Cash out (today)</p><p class="text-2xl font-black text-red-600">−<?= pkr($cashOut) ?></p></div>
    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10"><p class="text-xs text-slate-500">Expected in drawer</p><p class="text-2xl font-black text-brand-700 dark:text-brand-300"><?= pkr($expected) ?></p></div>
</div>

<?php if ($closed): ?>
<div class="mt-4 rounded-2xl border <?= (int) $book['discrepancy'] === 0 ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' : 'border-red-300 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' ?> p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><p class="text-xs text-slate-500">Counted (actual)</p><p class="text-xl font-black"><?= pkr($book['actual_close']) ?></p></div>
        <div><p class="text-xs text-slate-500">Expected</p><p class="text-xl font-black"><?= pkr($book['expected_close']) ?></p></div>
        <div><p class="text-xs text-slate-500">Discrepancy</p><p class="text-xl font-black <?= (int) $book['discrepancy'] === 0 ? 'text-emerald-600' : 'text-red-600' ?>"><?= (int) $book['discrepancy'] === 0 ? 'Balanced ✓' : pkr($book['discrepancy']) . ((int) $book['discrepancy'] > 0 ? ' surplus' : ' short') ?></p></div>
    </div>
    <?php if ($book['note']): ?><p class="mt-2 text-sm text-slate-500">Note: <?= e($book['note']) ?></p><?php endif; ?>
</div>
<?php endif; ?>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <!-- Actions -->
    <div class="space-y-4">
        <?php if (!$closed): ?>
        <form action="/daybook/open" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?><input type="hidden" name="date" value="<?= e($date) ?>">
            <h3 class="font-bold text-slate-900 dark:text-white">Set opening cash</h3>
            <input name="opening" type="number" min="0" value="<?= $opening ?>" class="mt-3 w-full <?= $inp ?>">
            <button class="mt-3 w-full rounded-xl bg-slate-800 py-2.5 text-sm font-bold text-white dark:bg-white/10">Save Opening</button>
        </form>

        <form action="/daybook/expense" method="POST" class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-500/30 dark:bg-red-500/10">
            <?= csrf_field() ?><input type="hidden" name="date" value="<?= e($date) ?>">
            <h3 class="font-bold text-red-800 dark:text-red-300">Record expense (cash out)</h3>
            <input name="amount" type="number" min="1" required placeholder="Amount" class="mt-3 w-full <?= $inp ?>">
            <select name="category" class="mt-2 w-full <?= $inp ?>">
                <?php foreach (['general'=>'General','salary'=>'Salary','rent'=>'Rent','utilities'=>'Utilities','supplies'=>'Supplies','maintenance'=>'Maintenance'] as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
            <select name="method" class="mt-2 w-full <?= $inp ?>">
                <?php foreach (['cash'=>'Cash','bank'=>'Bank'] as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
            <input name="payee" placeholder="Payee (optional)" class="mt-2 w-full <?= $inp ?>">
            <input name="note" placeholder="Note (optional)" class="mt-2 w-full <?= $inp ?>">
            <button class="mt-3 w-full rounded-xl bg-red-600 py-2.5 text-sm font-bold text-white hover:bg-red-700">Add Expense</button>
        </form>

        <form action="/daybook/close" method="POST" onsubmit="return confirm('Close and reconcile this day?')" class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
            <?= csrf_field() ?><input type="hidden" name="date" value="<?= e($date) ?>">
            <h3 class="font-bold text-brand-800 dark:text-brand-300">Close &amp; reconcile</h3>
            <p class="mt-1 text-xs text-slate-500">Count the drawer and enter the actual cash. Any gap vs <?= pkr($expected) ?> is flagged.</p>
            <input name="actual_close" type="number" min="0" required placeholder="Actual counted cash" class="mt-3 w-full <?= $inp ?>">
            <input name="note" placeholder="Note (optional)" class="mt-2 w-full <?= $inp ?>">
            <button class="mt-3 w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Close Day</button>
        </form>
        <?php endif; ?>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-2 font-bold text-slate-900 dark:text-white">Recent days</h3>
            <?php if (empty($recent)): ?><p class="text-sm text-slate-500">None.</p>
            <?php else: foreach ($recent as $r): ?>
            <a href="/daybook?date=<?= e($r['date']) ?>" class="flex items-center justify-between border-b border-slate-100 py-1.5 text-sm last:border-0 dark:border-white/5">
                <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($r['date']) ?></span>
                <span class="text-xs <?= (int) $r['discrepancy'] === 0 ? 'text-emerald-600' : 'text-red-600' ?>"><?= $r['status'] === 'closed' ? ((int) $r['discrepancy'] === 0 ? 'Balanced' : pkr($r['discrepancy'])) : 'Open' ?></span>
            </a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Ledgers -->
    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Cash received today</h3>
            <?php if (empty($payments)): ?><p class="text-sm text-slate-500">No payments today.</p>
            <?php else: foreach ($payments as $p): ?>
            <div class="flex items-center justify-between border-b border-slate-100 py-1.5 text-sm last:border-0 dark:border-white/5">
                <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($p['student']) ?> <span class="text-xs text-slate-400"><?= ucfirst($p['method']) ?> · <?= e($p['receipt_no']) ?></span></span>
                <span class="font-bold text-emerald-600">+<?= pkr($p['amount']) ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Expenses today</h3>
            <?php if (empty($expenses)): ?><p class="text-sm text-slate-500">No expenses today.</p>
            <?php else: foreach ($expenses as $x): ?>
            <div class="flex items-center justify-between border-b border-slate-100 py-1.5 text-sm last:border-0 dark:border-white/5">
                <span class="font-semibold text-slate-700 dark:text-slate-200"><?= ucfirst($x['category']) ?> <span class="text-xs text-slate-400"><?= e($x['payee'] ?: $x['note'] ?: '') ?> · <?= ucfirst($x['method']) ?></span></span>
                <span class="font-bold text-red-600">−<?= pkr($x['amount']) ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
