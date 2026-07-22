<?php /** @var array $rows */ ?>
<div class="mb-5 flex items-center justify-between">
    <p class="text-sm text-slate-500"><?= count($rows) ?> awards</p>
    <a href="/awards/create" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ Add Award</a>
</div>

<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($rows as $a): ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center gap-4 p-5">
            <?php if ($a['image']): ?>
                <img src="/award-image/<?= (int) $a['id'] ?>" alt="" class="h-16 w-16 flex-none rounded-xl object-cover ring-1 ring-slate-200">
            <?php else: ?>
                <span class="flex h-16 w-16 flex-none items-center justify-center rounded-xl bg-brand-600 text-xl font-black text-white"><?= e(strtoupper(substr($a['title'], 0, 1))) ?></span>
            <?php endif; ?>
            <div class="min-w-0">
                <?php if ($a['year']): ?><span class="inline-block rounded-lg bg-gold-500 px-2 py-0.5 text-xs font-black text-brand-950"><?= e($a['year']) ?></span><?php endif; ?>
                <p class="truncate font-bold text-slate-900 dark:text-white"><?= e($a['title']) ?></p>
                <p class="truncate text-sm text-brand-600"><?= e($a['org'] ?: '-') ?></p>
                <?php if (!$a['is_published']): ?><span class="text-xs font-bold text-slate-400">Hidden</span><?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-2 border-t border-slate-100 px-5 py-2 dark:border-white/5">
            <form action="/awards/<?= (int) $a['id'] ?>/move" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="dir" value="up">
                <button class="rounded-lg px-2 py-1 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10">↑</button>
            </form>
            <form action="/awards/<?= (int) $a['id'] ?>/move" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="dir" value="down">
                <button class="rounded-lg px-2 py-1 text-sm font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10">↓</button>
            </form>
        </div>
        <div class="flex border-t border-slate-100 dark:border-white/5">
            <a href="/awards/<?= (int) $a['id'] ?>/edit" class="flex-1 py-3 text-center text-sm font-bold text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-white/5">Edit</a>
            <form action="/awards/<?= (int) $a['id'] ?>/delete" method="POST" onsubmit="return confirm('Remove this award?')" class="flex-1 border-l border-slate-100 dark:border-white/5">
                <?= csrf_field() ?>
                <button class="w-full py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
