<?php /** @var array $rows */ ?>
<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/notices" method="POST" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Post a Notice</h2>
        <input name="title" required placeholder="Notice title" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <textarea name="body" rows="4" placeholder="Details…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <select name="audience" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <option value="all">Everyone (students & guardians)</option>
            <option value="students">Students only</option>
            <option value="guardians">Guardians only</option>
        </select>
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Publish Notice</button>
    </form>

    <div class="space-y-3">
        <?php if (empty($rows)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No notices yet.</div>
        <?php else: foreach ($rows as $r): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white"><?= e($r['title']) ?></h3>
                    <p class="text-xs text-slate-400"><?= e(date('d M Y', strtotime($r['created_at']))) ?> · <?= ucfirst($r['audience']) ?></p>
                </div>
                <form action="/notices/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete notice?')"><?= csrf_field() ?><button class="text-sm font-bold text-red-500 hover:text-red-700">Delete</button></form>
            </div>
            <?php if ($r['body']): ?><p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300"><?= e($r['body']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
