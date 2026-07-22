<?php /** @var array $rows */ ?>
<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/activities" method="POST" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Category</h2>
        <input name="name" required placeholder="e.g. Sports Activities" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ Add Category</button>
    </form>

    <div class="space-y-4">
        <p class="text-sm text-slate-500"><?= count($rows) ?> categories</p>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($rows as $r): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <div class="p-5">
                    <form action="/activities/<?= (int) $r['id'] ?>" method="POST" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <input name="name" value="<?= e($r['name']) ?>" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-900 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                        <button class="flex-none rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700 dark:bg-white/10">Save</button>
                    </form>
                    <p class="mt-3 text-sm text-slate-500"><?= (int) $r['photo_count'] ?> photo<?= (int) $r['photo_count'] === 1 ? '' : 's' ?></p>
                    <?php if (!$r['is_published']): ?><span class="text-xs font-bold text-slate-400">Hidden</span><?php endif; ?>
                </div>
                <div class="flex items-center gap-2 border-t border-slate-100 px-5 py-3 dark:border-white/5">
                    <form action="/activities/<?= (int) $r['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="up"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↑</button></form>
                    <form action="/activities/<?= (int) $r['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="down"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↓</button></form>
                </div>
                <div class="flex border-t border-slate-100 dark:border-white/5">
                    <a href="/activities/<?= (int) $r['id'] ?>" class="flex-1 py-3 text-center text-sm font-bold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-white/5">Manage Photos →</a>
                    <form action="/activities/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this category and all its photos?')" class="flex-1 border-l border-slate-100 dark:border-white/5">
                        <?= csrf_field() ?>
                        <button class="w-full py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
