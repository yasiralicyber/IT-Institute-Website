<?php /** @var array $rows @var array $actions @var string $action @var string $q */ ?>
<form method="GET" class="mb-5 flex flex-wrap gap-2">
    <input name="q" value="<?= e($q) ?>" placeholder="Search action, user or item…" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <select name="action" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <option value="">All actions</option>
        <?php foreach ($actions as $a): ?><option value="<?= e($a['action']) ?>" <?= $action === $a['action'] ? 'selected' : '' ?>><?= e($a['action']) ?></option><?php endforeach; ?>
    </select>
    <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">When</th><th class="px-5 py-3">User</th><th class="px-5 py-3">Action</th><th class="px-5 py-3">Details</th><th class="px-5 py-3">IP</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No activity recorded yet.</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                <td class="px-5 py-3 whitespace-nowrap text-slate-500"><?= e(date('d M, g:i A', strtotime($r['created_at']))) ?></td>
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($r['user_name']) ?></td>
                <td class="px-5 py-3"><span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"><?= e($r['action']) ?></span></td>
                <td class="px-5 py-3 text-slate-600 dark:text-slate-300"><?= e($r['summary']) ?><?php if ($r['entity']): ?> <span class="text-xs text-slate-400">(<?= e($r['entity']) ?><?= $r['entity_id'] ? ' #' . (int) $r['entity_id'] : '' ?>)</span><?php endif; ?></td>
                <td class="px-5 py-3 text-xs text-slate-400"><?= e($r['ip'] ?: '-') ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<p class="mt-2 text-xs text-slate-400">Showing the most recent 300 events. Passwords, tokens and full CNIC numbers are never logged.</p>
