<?php /** @var array $threads */ ?>
<?php if (empty($threads)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No questions yet.</div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($threads as $t): $needsReply = (int) $t['admin_replies'] === 0; ?>
    <div class="flex items-center justify-between rounded-2xl border bg-white p-5 dark:bg-slate-900 <?= $needsReply ? 'border-amber-300 dark:border-amber-500/40' : 'border-slate-200 dark:border-white/10' ?>">
        <a href="/community/<?= (int) $t['id'] ?>" class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <h3 class="truncate font-bold text-slate-900 dark:text-white"><?= e($t['title']) ?></h3>
                <?php if ($needsReply): ?><span class="flex-none rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">Needs reply</span><?php endif; ?>
            </div>
            <p class="mt-1 truncate text-sm text-slate-500"><?= e($t['author']) ?><?php if ($t['course_title']): ?> · <?= e($t['course_title']) ?><?php endif; ?> · <?= (int) $t['replies'] ?> replies</p>
        </a>
        <div class="ml-4 flex items-center gap-3">
            <a href="/community/<?= (int) $t['id'] ?>" class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300">Open</a>
            <form action="/community/<?= (int) $t['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this thread?')"><?= csrf_field() ?><button class="text-xs font-bold text-red-500 hover:text-red-700">Delete</button></form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
