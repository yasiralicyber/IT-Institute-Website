<?php /** @var ?array $plan @var array $courses */
$p = $plan ?: [];
$action = $plan ? '/fee-plans/' . (int) $plan['id'] : '/fee-plans';
function pv($p, $k, $d = '') { return e((string) ($p[$k] ?? $d)); }
$inp = 'w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<a href="/fee-plans" class="text-sm font-semibold text-brand-600 hover:underline">← All plans</a>

<form action="<?= $action ?>" method="POST" class="mt-4 max-w-2xl space-y-5">
    <?= csrf_field() ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="mb-4 font-black text-slate-900 dark:text-white">Plan details</h3>
        <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Plan name</label>
        <input name="name" required value="<?= pv($p,'name') ?>" placeholder="e.g. CCNA Standard 2026" class="mt-1 <?= $inp ?>">

        <label class="mt-4 block text-sm font-semibold text-slate-600 dark:text-slate-300">Course (optional)</label>
        <select name="course_id" class="mt-1 <?= $inp ?>">
            <option value="">All courses</option>
            <?php foreach ($courses as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= ((int) ($p['course_id'] ?? 0) === (int) $c['id']) ? 'selected' : '' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="mb-4 font-black text-slate-900 dark:text-white">Fees (you enter every amount)</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Admission fee (PKR)</label><input name="admission_fee" type="number" min="0" value="<?= pv($p,'admission_fee','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Security deposit (PKR)</label><input name="security_deposit" type="number" min="0" value="<?= pv($p,'security_deposit','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Total tuition (PKR)</label><input name="tuition_fee" type="number" min="0" value="<?= pv($p,'tuition_fee','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300"># of installments</label><input name="installments" type="number" min="1" value="<?= pv($p,'installments','1') ?>" class="mt-1 <?= $inp ?>"></div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="mb-4 font-black text-slate-900 dark:text-white">Late fee &amp; discounts</h3>
        <div class="grid gap-4 sm:grid-cols-3">
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Flat late fee</label><input name="late_fee_flat" type="number" min="0" value="<?= pv($p,'late_fee_flat','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Per-day late fee</label><input name="late_fee_per_day" type="number" min="0" value="<?= pv($p,'late_fee_per_day','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Grace days</label><input name="grace_days" type="number" min="0" value="<?= pv($p,'grace_days','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Early-payment discount %</label><input name="early_discount_pct" type="number" min="0" max="100" value="<?= pv($p,'early_discount_pct','0') ?>" class="mt-1 <?= $inp ?>"></div>
            <div><label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Sibling discount %</label><input name="sibling_discount_pct" type="number" min="0" max="100" value="<?= pv($p,'sibling_discount_pct','0') ?>" class="mt-1 <?= $inp ?>"></div>
        </div>
        <label class="mt-4 block text-sm font-semibold text-slate-600 dark:text-slate-300">Scholarship note (optional)</label>
        <textarea name="scholarship_note" rows="2" class="mt-1 <?= $inp ?>"><?= pv($p,'scholarship_note') ?></textarea>
        <label class="mt-4 flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
            <input type="checkbox" name="is_active" value="1" <?= (!$plan || $p['is_active']) ? 'checked' : '' ?> class="rounded border-slate-300"> Active
        </label>
    </div>

    <button class="rounded-xl bg-brand-600 px-8 py-3 font-bold text-white hover:bg-brand-700">Save Plan</button>
</form>
