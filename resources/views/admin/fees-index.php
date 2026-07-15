<?php /** @var array $rows @var string $q @var array $totals */ ?>
<!-- Totals -->
<div class="mb-5 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-sm text-slate-500">Total Billed</p><p class="mt-1 text-2xl font-black text-slate-900 dark:text-white"><?= pkr($totals['billed']) ?></p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-sm text-slate-500">Collected</p><p class="mt-1 text-2xl font-black text-emerald-600"><?= pkr($totals['collected']) ?></p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-sm text-slate-500">Outstanding</p><p class="mt-1 text-2xl font-black text-red-600"><?= pkr($totals['outstanding']) ?></p></div>
</div>

<!-- Bulk monthly fee -->
<details class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <summary class="cursor-pointer font-bold text-slate-900 dark:text-white">Generate monthly fee for a batch</summary>
    <form action="/fees/generate-monthly" method="POST" class="mt-4 grid gap-3 sm:grid-cols-4">
        <?= csrf_field() ?>
        <select name="batch_id" required class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <option value="">Select batch…</option>
            <?php foreach (\App\Core\Database::all("SELECT id,name FROM batches WHERE status='active' ORDER BY name") as $b): ?>
            <option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input name="amount" type="number" required placeholder="Amount (Rs)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <input name="fee_month" type="month" value="<?= date('Y-m') ?>" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Generate</button>
    </form>
</details>

<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="<?= e($q) ?>" placeholder="Search student…" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Search</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Billed</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Balance</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No students found.</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr class="cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5" onclick="location.href='/fees/<?= (int) $r['id'] ?>'">
                <td class="px-5 py-3"><p class="font-bold text-slate-900 dark:text-white"><?= e($r['name']) ?></p><p class="text-xs text-slate-500"><?= e($r['email']) ?></p></td>
                <td class="px-5 py-3"><?= pkr($r['billed']) ?></td>
                <td class="px-5 py-3 text-emerald-600"><?= pkr($r['paid']) ?></td>
                <td class="px-5 py-3 font-bold <?= $r['balance'] > 0 ? 'text-red-600' : 'text-emerald-600' ?>"><?= $r['balance'] > 0 ? pkr($r['balance']) : 'Clear' ?></td>
                <td class="px-5 py-3 text-right"><span class="font-bold text-brand-600">Open ledger →</span></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
