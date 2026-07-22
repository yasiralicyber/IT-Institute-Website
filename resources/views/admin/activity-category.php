<?php /** @var array $category @var array $photos */ ?>
<a href="/activities" class="text-sm font-semibold text-brand-600 hover:underline">← All categories</a>

<div class="mt-4 grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/activities/<?= (int) $category['id'] ?>/photos" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Photo</h2>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required class="w-full text-sm">
        <input name="caption" placeholder="Caption (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ Add Photo</button>
    </form>

    <div class="space-y-4">
        <p class="text-sm text-slate-500"><?= count($photos) ?> photos in "<?= e($category['name']) ?>"</p>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($photos as $p): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <div class="h-40 overflow-hidden">
                    <img src="/activity-image/<?= (int) $p['id'] ?>" alt="<?= e($p['caption'] ?: $category['name']) ?>" class="h-full w-full object-cover">
                </div>
                <div class="p-4">
                    <p class="truncate text-sm text-slate-600 dark:text-slate-300"><?= e($p['caption'] ?: '-') ?></p>
                    <?php if (!$p['is_published']): ?><span class="text-xs font-bold text-slate-400">Hidden</span><?php endif; ?>
                </div>
                <div class="flex items-center gap-2 border-t border-slate-100 px-4 py-2.5 dark:border-white/5">
                    <form action="/activity-photos/<?= (int) $p['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="up"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↑</button></form>
                    <form action="/activity-photos/<?= (int) $p['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="down"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↓</button></form>
                    <form action="/activity-photos/<?= (int) $p['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this photo?')" class="ml-auto">
                        <?= csrf_field() ?>
                        <button class="text-sm font-bold text-red-600 hover:text-red-700">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
