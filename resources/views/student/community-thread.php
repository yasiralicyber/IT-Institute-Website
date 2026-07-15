<?php /** @var array $thread @var array $replies */ ?>
<div class="mx-auto max-w-3xl">
    <a href="<?= url('/community') ?>" class="text-sm font-semibold text-brand-600 hover:underline">← All questions</a>

    <!-- Question -->
    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h1 class="text-xl font-black text-slate-900 dark:text-white"><?= e($thread['title']) ?></h1>
        <p class="mt-1 text-xs text-slate-400"><?= e($thread['author']) ?> · <?= e(date('d M Y, g:i A', strtotime($thread['created_at']))) ?><?php if ($thread['course_title']): ?> · <span class="font-semibold text-brand-600"><?= e($thread['course_title']) ?></span><?php endif; ?></p>
        <p class="mt-4 whitespace-pre-line text-slate-700 dark:text-slate-300"><?= e($thread['body']) ?></p>
    </div>

    <!-- Replies -->
    <h2 class="mb-3 mt-6 text-sm font-bold uppercase tracking-wider text-slate-400"><?= count($replies) ?> Repl<?= count($replies) === 1 ? 'y' : 'ies' ?></h2>
    <div class="space-y-3">
        <?php foreach ($replies as $r): $admin = (int) $r['is_admin'] === 1; ?>
        <div class="rounded-2xl border p-5 <?= $admin ? 'border-brand-300 bg-brand-50 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900' ?>">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full <?= $admin ? 'bg-brand-600 text-white' : 'bg-slate-200 text-slate-600 dark:bg-white/10 dark:text-slate-300' ?> text-xs font-bold"><?= e(strtoupper(substr($r['author'], 0, 1))) ?></span>
                <span class="text-sm font-bold text-slate-900 dark:text-white"><?= e($r['author']) ?></span>
                <?php if ($admin): ?><span class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">INSTRUCTOR</span><?php endif; ?>
                <span class="ml-auto text-xs text-slate-400"><?= e(date('d M, g:i A', strtotime($r['created_at']))) ?></span>
            </div>
            <p class="mt-3 whitespace-pre-line text-slate-700 dark:text-slate-300"><?= e($r['body']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Reply form -->
    <form action="<?= url('/community/' . $thread['id'] . '/reply') ?>" method="POST" class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <textarea name="body" required rows="3" placeholder="Write a reply…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <button class="mt-3 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Post Reply</button>
    </form>
</div>
