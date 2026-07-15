<?php /** @var array $rows */ ?>
<a href="/imports" class="text-sm font-semibold text-brand-600 hover:underline">← New import</a>
<div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">When</th><th class="px-5 py-3">Section</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Result</th><th class="px-5 py-3">By</th><th class="px-5 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($rows)): ?>
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No imports yet.</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr class="cursor-pointer hover:bg-slate-50 dark:hover:bg-white/5" onclick="location.href='/imports/<?= (int) $r['id'] ?><?= in_array($r['status'],['completed','rolled_back']) ? '/result' : '' ?>'">
                <td class="px-5 py-3 text-slate-500"><?= e(date('d M, g:i A', strtotime($r['created_at']))) ?></td>
                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e(ucfirst($r['section'])) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['source']) ?></td>
                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= (int) $r['imported'] ?> in · <?= (int) $r['updated'] ?> upd · <?= (int) $r['skipped'] ?> skip · <?= (int) $r['failed'] ?> fail</td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['created_by_name'] ?: '-') ?></td>
                <td class="px-5 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300"><?= e($r['status']) ?></span></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
