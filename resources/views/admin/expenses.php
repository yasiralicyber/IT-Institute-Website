<?php /** @var array $rows @var array $cats @var string $month @var int $monthTotal @var int $todayTotal @var array $byCat */
$inp = 'w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<!-- Summary -->
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <form method="get" action="/expenses" class="flex items-center gap-2">
        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Month</label>
        <input type="month" name="month" value="<?= e($month) ?>" onchange="this.form.submit()" class="rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    </form>
    <div class="flex gap-3">
        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center dark:border-white/10 dark:bg-slate-900"><p class="text-xs text-slate-500">Today</p><p class="text-xl font-black text-slate-900 dark:text-white"><?= pkr($todayTotal) ?></p></div>
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-center dark:border-red-500/30 dark:bg-red-500/10"><p class="text-xs text-slate-500">This month</p><p class="text-xl font-black text-red-600"><?= pkr($monthTotal) ?></p></div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <!-- Quick add -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white">Add an expense</h3>
        <p class="text-xs text-slate-400">Tap a category, type the amount, save. That's it.</p>
        <form method="post" action="/expenses" class="mt-3">
            <?= csrf_field() ?>
            <input type="hidden" name="category" id="catField" value="tea">
            <div id="catGrid" class="grid grid-cols-3 gap-2">
                <?php foreach ($cats as $k => $c): ?>
                <button type="button" data-cat="<?= e($k) ?>" onclick="pickCat('<?= e($k) ?>')" class="catbtn flex flex-col items-center gap-1 rounded-xl border border-slate-200 px-1 py-2 text-[11px] font-bold text-slate-600 hover:border-brand-400 dark:border-white/10 dark:text-slate-300">
                    <span class="text-brand-600"><?= icon($c[1], 'h-5 w-5') ?></span><?= e($c[0]) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <input name="amount" type="number" min="1" required placeholder="Amount (Rs)" class="<?= $inp ?>">
                <input name="date" type="date" value="<?= date('Y-m-d') ?>" class="<?= $inp ?>">
            </div>
            <input name="payee" placeholder="Paid to (optional)" class="mt-2 <?= $inp ?>">
            <input name="note" placeholder="Note (optional)" class="mt-2 <?= $inp ?>">
            <select name="method" class="mt-2 <?= $inp ?>">
                <?php foreach (['cash'=>'Cash','bank'=>'Bank','jazzcash'=>'JazzCash','easypaisa'=>'Easypaisa'] as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
            <button class="mt-3 w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Expense</button>
        </form>

        <?php if ($byCat): ?>
        <div class="mt-5 border-t border-slate-100 pt-4 dark:border-white/10">
            <h4 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">This month by category</h4>
            <?php $maxCat = max(array_map(fn($c) => $c['total'], $byCat)); foreach ($byCat as $c): ?>
            <div class="mb-2">
                <div class="flex justify-between text-sm"><span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($c['label']) ?> <span class="text-xs text-slate-400">×<?= $c['n'] ?></span></span><span class="font-bold text-slate-900 dark:text-white"><?= pkr($c['total']) ?></span></div>
                <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10"><div class="h-full rounded-full bg-brand-500" style="width: <?= $maxCat ? round($c['total']/$maxCat*100) : 0 ?>%"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- List -->
    <div class="rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="border-b border-slate-100 px-5 py-3 dark:border-white/10"><h3 class="font-black text-slate-900 dark:text-white"><?= e(date('F Y', strtotime($month . '-01'))) ?> expenses</h3></div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
                <tr><th class="px-5 py-2">Date</th><th class="px-5 py-2">Category</th><th class="px-5 py-2">Paid to</th><th class="px-5 py-2 text-right">Amount</th><th class="px-5 py-2"></th></tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No expenses this month. Add your first one on the left.</td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr class="border-t border-slate-100 dark:border-white/5">
                    <td class="px-5 py-2.5 text-slate-500"><?= e(date('d M', strtotime($r['date']))) ?></td>
                    <td class="px-5 py-2.5 font-semibold text-slate-800 dark:text-slate-200"><?= e($cats[$r['category']][0] ?? ucfirst($r['category'])) ?></td>
                    <td class="px-5 py-2.5 text-slate-500"><?= e($r['payee'] ?: $r['note'] ?: '-') ?> <span class="text-xs text-slate-400">· <?= ucfirst($r['method']) ?></span></td>
                    <td class="px-5 py-2.5 text-right font-bold text-red-600"><?= pkr($r['amount']) ?></td>
                    <td class="px-5 py-2.5 text-right"><form method="post" action="/expenses/<?= (int) $r['id'] ?>/delete" onsubmit="return confirm('Delete this expense?')"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">✕</button></form></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function pickCat(k){
    document.getElementById('catField').value = k;
    document.querySelectorAll('.catbtn').forEach(b => b.classList.toggle('border-brand-500', b.dataset.cat===k));
    document.querySelectorAll('.catbtn').forEach(b => b.classList.toggle('bg-brand-50', b.dataset.cat===k));
}
pickCat('tea');
</script>
