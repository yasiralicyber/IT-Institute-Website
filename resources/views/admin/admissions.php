<?php /** @var array $rows */ ?>
<form method="GET" class="mb-5 flex gap-2">
    <input name="q" value="<?= e(input('q', '')) ?>" placeholder="Search applicant by name…" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Search</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">Applicant</th><th class="px-5 py-3">Program</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">Applied</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php
            $q = strtolower(trim((string) input('q', '')));
            $rows = $q === '' ? $rows : array_filter($rows, fn($r) => str_contains(strtolower($r['name']), $q));
            if (empty($rows)): ?>
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No applications found.</td></tr>
            <?php else: foreach ($rows as $r):
                $badge = ['new'=>'bg-blue-100 text-blue-700','contacted'=>'bg-amber-100 text-amber-700','enrolled'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700'][$r['status']] ?? ''; ?>
            <tr class="cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5" onclick="location.href='/admissions/<?= (int) $r['id'] ?>'">
                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e($r['name']) ?></td>
                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['programs']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['contact']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
                <td class="px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $badge ?>"><?= ucfirst($r['status']) ?></span></td>
                <td class="px-5 py-3 text-right"><span class="font-bold text-brand-600">Open →</span></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
