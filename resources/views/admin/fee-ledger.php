<?php /** @var array $student @var array $invoices @var array $payments @var array $batches @var int $billed @var int $paid @var int $balance @var int $credit @var array $invMeta @var array $plans @var array $restructures @var array $outstanding */ ?>
<a href="/fees" class="text-sm font-semibold text-brand-600 hover:underline">← All students</a>

<div class="mt-4 grid gap-4 sm:grid-cols-5">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900 sm:col-span-2">
        <p class="text-lg font-black text-slate-900 dark:text-white"><?= e($student['name']) ?></p>
        <p class="text-sm text-slate-500"><?= e($student['email']) ?> · <?= e($student['phone'] ?: '-') ?></p>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-xs text-slate-500">Billed</p><p class="text-xl font-black text-slate-900 dark:text-white"><?= pkr($billed) ?></p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-xs text-slate-500">Paid</p><p class="text-xl font-black text-emerald-600"><?= pkr($paid) ?></p><?php if ($credit > 0): ?><p class="text-xs font-semibold text-sky-600">Credit: <?= pkr($credit) ?></p><?php endif; ?></div>
    <div class="rounded-2xl border <?= $balance > 0 ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10' ?> p-5"><p class="text-xs text-slate-500">Outstanding</p><p class="text-xl font-black <?= $balance > 0 ? 'text-red-600' : 'text-emerald-600' ?>"><?= $balance > 0 ? pkr($balance) : 'Clear ✓' ?></p></div>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <!-- Left column: actions -->
    <div class="space-y-4">
        <!-- Apply a fee plan (rules engine) -->
        <form action="/fees/<?= (int) $student['id'] ?>/apply-plan" method="POST" class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
            <?= csrf_field() ?>
            <h3 class="font-bold text-brand-800 dark:text-brand-300">Apply a Fee Plan</h3>
            <p class="mt-1 text-xs text-slate-500">Auto-generates admission + installment charges from a saved plan.</p>
            <select name="plan_id" required class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Choose plan…</option>
                <?php foreach ($plans as $pl): if (!$pl['is_active']) continue; ?>
                <option value="<?= (int) $pl['id'] ?>"><?= e($pl['name']) ?> (<?= pkr($pl['tuition_fee']) ?> / <?= (int) $pl['installments'] ?>x)</option>
                <?php endforeach; ?>
            </select>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <input name="start_month" type="month" value="<?= date('Y-m') ?>" class="rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <input name="discount_pct" type="number" min="0" max="100" placeholder="Discount %" class="rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <?php if ($batches): ?>
            <select name="batch_id" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">No batch link</option>
                <?php foreach ($batches as $b): ?><option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button class="mt-3 w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Generate Charges</button>
            <p class="mt-2 text-right text-xs"><a href="/fee-plans" class="font-semibold text-brand-600 hover:underline">Manage plans →</a></p>
        </form>

        <!-- Add a manual charge -->
        <form action="/fees/<?= (int) $student['id'] ?>/invoice" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <h3 class="font-bold text-slate-900 dark:text-white">Add a Charge</h3>
            <select name="type" class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <?php foreach (['admission'=>'Admission Fee','monthly'=>'Monthly Fee','installment'=>'Installment','exam'=>'Exam Fee','other'=>'Other'] as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
            <input name="title" required placeholder="Title (e.g. Admission Fee)" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <div class="mt-2 grid grid-cols-2 gap-2">
                <input name="amount" type="number" required placeholder="Amount" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <input name="discount" type="number" placeholder="Discount" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <input name="due_date" type="date" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button class="mt-3 w-full rounded-xl bg-slate-800 py-2.5 text-sm font-bold text-white dark:bg-white/10">Add Charge</button>
        </form>

        <!-- Record a payment (with allocation) -->
        <form action="/fees/<?= (int) $student['id'] ?>/payment" method="POST" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
            <?= csrf_field() ?>
            <h3 class="font-bold text-emerald-800 dark:text-emerald-300">Record a Payment</h3>
            <input name="amount" type="number" required placeholder="Total amount received" class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <select name="method" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <?php foreach (['cash'=>'Cash','bank'=>'Bank','jazzcash'=>'JazzCash','easypaisa'=>'Easypaisa','other'=>'Other'] as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
            <input name="reference" placeholder="Reference / TID (optional)" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <?php if ($outstanding): ?>
            <details class="mt-3 rounded-xl bg-white/70 p-3 text-xs dark:bg-slate-800/60">
                <summary class="cursor-pointer font-bold text-slate-600 dark:text-slate-300">Split across specific charges (optional)</summary>
                <p class="mt-1 text-slate-400">Leave blank to auto-apply oldest-first.</p>
                <?php foreach ($outstanding as $o): ?>
                <label class="mt-2 flex items-center justify-between gap-2">
                    <span class="truncate text-slate-600 dark:text-slate-300"><?= e($o['title']) ?> <span class="text-slate-400">(due <?= pkr($invMeta[$o['id']]['due']) ?>)</span></span>
                    <input name="alloc[<?= (int) $o['id'] ?>]" type="number" min="0" max="<?= $invMeta[$o['id']]['due'] ?>" placeholder="0" class="w-24 rounded-lg border-slate-300 bg-white px-2 py-1 text-right text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white">
                </label>
                <?php endforeach; ?>
            </details>
            <?php endif; ?>
            <button class="mt-3 w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Receive &amp; Print Receipt</button>
        </form>

        <!-- Restructure installments -->
        <form action="/fees/<?= (int) $student['id'] ?>/restructure" method="POST" onsubmit="return confirm('Restructure remaining installments? The original schedule is preserved in history.');" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
            <?= csrf_field() ?>
            <h3 class="font-bold text-amber-800 dark:text-amber-300">Restructure Installments</h3>
            <p class="mt-1 text-xs text-slate-500">Reschedules remaining unpaid installments. Originals are kept &amp; logged.</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                <input name="new_total" type="number" required placeholder="New total" class="rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <input name="new_installments" type="number" min="1" value="3" placeholder="# installments" class="rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <input name="start_month" type="month" value="<?= date('Y-m') ?>" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="reason" placeholder="Reason (recorded)" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button class="mt-3 w-full rounded-xl bg-amber-600 py-2.5 text-sm font-bold text-white hover:bg-amber-700">Restructure</button>
        </form>
    </div>

    <!-- Right: ledger -->
    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Charges</h3>
            <?php if (empty($invoices)): ?><p class="text-sm text-slate-500">No charges yet.</p>
            <?php else: foreach ($invoices as $i): $net = $i['amount'] - $i['discount']; $m = $invMeta[$i['id']] ?? ['paid'=>0,'due'=>$net,'late'=>0];
                $badge = ['unpaid'=>'bg-red-100 text-red-700','partial'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','waived'=>'bg-slate-200 text-slate-500','restructured'=>'bg-purple-100 text-purple-700','cancelled'=>'bg-slate-200 text-slate-500'][$i['status']] ?? ''; ?>
            <div class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2.5 text-sm dark:border-white/5">
                <div>
                    <p class="font-semibold text-slate-800 dark:text-slate-200"><?= e($i['title']) ?></p>
                    <p class="text-xs text-slate-400"><?= pkr($i['amount']) ?><?php if ($i['discount']): ?> − <?= pkr($i['discount']) ?> disc<?php endif; ?> · <?= e($i['due_date'] ?: '') ?><?php if ($m['paid'] > 0 && $i['status'] !== 'paid'): ?> · paid <?= pkr($m['paid']) ?><?php endif; ?></p>
                    <?php if ($m['late'] > 0): ?><p class="text-xs font-bold text-red-500">Late fee due: <?= pkr($m['late']) ?></p><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 dark:text-slate-200"><?= pkr($net) ?></span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge ?>"><?= ucfirst($i['status']) ?></span>
                    <form action="/fees/invoice/<?= (int) $i['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this charge and its payments?')"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">✕</button></form>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Payments</h3>
            <?php if (empty($payments)): ?><p class="text-sm text-slate-500">No payments yet.</p>
            <?php else: foreach ($payments as $p): ?>
            <div class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2.5 text-sm dark:border-white/5">
                <div><p class="font-semibold text-emerald-700 dark:text-emerald-400"><?= pkr($p['amount']) ?> · <?= ucfirst($p['method']) ?></p><p class="text-xs text-slate-400"><?= e($p['receipt_no']) ?> · <?= e(date('d M Y', strtotime($p['paid_at']))) ?></p></div>
                <a href="/fees/receipt/<?= (int) $p['id'] ?>" target="_blank" class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">Receipt</a>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <?php if ($restructures): ?>
        <div class="mt-4 rounded-2xl border border-purple-200 bg-purple-50 p-5 dark:border-purple-500/30 dark:bg-purple-500/10">
            <h3 class="mb-3 font-bold text-purple-800 dark:text-purple-300">Restructure History</h3>
            <?php foreach ($restructures as $r): $old = json_decode($r['old_plan'], true) ?: []; $new = json_decode($r['new_plan'], true) ?: []; ?>
            <div class="mb-2 rounded-xl border border-purple-100 px-4 py-2.5 text-sm dark:border-white/5">
                <p class="font-semibold text-slate-700 dark:text-slate-200"><?= pkr($old['remaining'] ?? 0) ?> → <?= pkr($new['total'] ?? 0) ?> over <?= (int) ($new['count'] ?? 0) ?> installments</p>
                <p class="text-xs text-slate-400"><?= e(date('d M Y', strtotime($r['created_at']))) ?><?php if ($r['reason']): ?> · <?= e($r['reason']) ?><?php endif; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
