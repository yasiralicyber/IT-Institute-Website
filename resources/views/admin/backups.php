<?php /** @var array $rows */
function bsize($b){ $u=['B','KB','MB','GB'];$i=0;while($b>=1024&&$i<3){$b/=1024;$i++;}return round($b,1).' '.$u[$i]; } ?>
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-slate-500">Backups include the database and all uploaded media, stored privately outside the website.</p>
    <form action="/backups" method="POST" class="flex gap-2">
        <?= csrf_field() ?>
        <input name="note" placeholder="Note (optional)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Create Backup Now</button>
    </form>
</div>

<div class="mb-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-500 dark:border-white/10 dark:bg-white/5">
    <strong>Automatic backups:</strong> on Hostinger, add a Cron Job pointing to <code class="rounded bg-slate-200 px-1 dark:bg-white/10">php /home/USER/itti/database/backup.php</code> (e.g. daily). It keeps the latest 14 automatically.
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">File</th><th class="px-5 py-3">Type</th><th class="px-5 py-3">Size</th><th class="px-5 py-3">Created</th><th class="px-5 py-3 text-right">Actions</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No backups yet. Create your first one above.</td></tr>
            <?php else: foreach ($rows as $r): ?>
            <tr>
                <td class="px-5 py-3 font-mono text-slate-700 dark:text-slate-200"><?= e($r['filename']) ?><?php if ($r['note']): ?><span class="block text-xs text-slate-400"><?= e($r['note']) ?></span><?php endif; ?></td>
                <td class="px-5 py-3 text-slate-500"><?= ucfirst($r['type']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= bsize((int) $r['size']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e(date('d M Y, g:i A', strtotime($r['created_at']))) ?></td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="/backups/<?= (int) $r['id'] ?>/download" class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">Download</a>
                        <form action="/backups/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this backup file?')">
                            <?= csrf_field() ?>
                            <button class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
