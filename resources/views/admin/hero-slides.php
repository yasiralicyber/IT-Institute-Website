<?php /** @var array $rows */ ?>
<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/hero-slides" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Hero Slide</h2>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Image *</label>
            <input type="file" name="image" accept="image/*" required class="w-full text-sm"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Alt text</label>
            <input name="alt" placeholder="Describe the photo" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Add Slide</button>
    </form>

    <div class="space-y-3">
        <p class="text-sm text-slate-500"><?= count($rows) ?> slides</p>
        <?php if (empty($rows)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No hero slides yet.</div>
        <?php else: foreach ($rows as $r): ?>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <div class="flex flex-wrap items-center gap-4 p-4">
                <img src="/hero-image/<?= (int) $r['id'] ?>" alt="" class="h-20 w-32 flex-none rounded-xl object-cover ring-1 ring-slate-200">
                <form method="POST" action="/hero-slides/<?= (int) $r['id'] ?>" enctype="multipart/form-data" class="flex min-w-[260px] flex-1 flex-wrap items-center gap-2">
                    <?= csrf_field() ?>
                    <input name="alt" value="<?= e($r['alt']) ?>" placeholder="Alt text" class="min-w-[160px] flex-1 rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Replace image (optional)
                        <input type="file" name="image" accept="image/*" class="mt-1 block w-full text-xs">
                    </label>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <input type="checkbox" name="is_published" value="1" <?= $r['is_published'] ? 'checked' : '' ?> class="rounded text-brand-600"> Show on website
                    </label>
                    <button class="flex-none rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700 dark:bg-white/10">Save</button>
                </form>
            </div>
            <div class="flex items-center gap-2 border-t border-slate-100 px-4 py-2 dark:border-white/5">
                <form action="/hero-slides/<?= (int) $r['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="up"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↑</button></form>
                <form action="/hero-slides/<?= (int) $r['id'] ?>/move" method="POST"><?= csrf_field() ?><input type="hidden" name="dir" value="down"><button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↓</button></form>
                <?php if (!$r['is_published']): ?><span class="text-xs font-bold text-slate-400">Hidden</span><?php endif; ?>
            </div>
            <div class="flex border-t border-slate-100 dark:border-white/5">
                <form action="/hero-slides/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this slide?')" class="flex-1">
                    <?= csrf_field() ?>
                    <button class="w-full py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Delete</button>
                </form>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
