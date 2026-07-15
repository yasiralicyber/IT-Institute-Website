<?php /** @var array $rows */ ?>
<p class="mb-4 text-sm text-slate-500">Deleted records are kept here so you can restore them. Permanent deletion needs your admin password.</p>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">Item</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Deleted by</th><th class="px-5 py-3">When</th><th class="px-5 py-3 text-right">Actions</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Recycle bin is empty.</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr>
                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e($r['label']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e(str_replace('_', ' ', $r['table_name'])) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['deleted_by_name'] ?: '-') ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e(date('d M Y, g:i A', strtotime($r['deleted_at']))) ?></td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <form action="/recycle/<?= (int) $r['id'] ?>/restore" method="POST">
                            <?= csrf_field() ?>
                            <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Restore</button>
                        </form>
                        <button onclick="document.getElementById('purge-<?= (int) $r['id'] ?>').classList.toggle('hidden')" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Delete forever</button>
                    </div>
                    <form id="purge-<?= (int) $r['id'] ?>" action="/recycle/<?= (int) $r['id'] ?>/purge" method="POST" class="mt-2 hidden">
                        <?= csrf_field() ?>
                        <div class="flex justify-end gap-2">
                            <input type="password" name="password" required placeholder="Admin password" class="rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            <button class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700">Confirm</button>
                        </div>
                    </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
