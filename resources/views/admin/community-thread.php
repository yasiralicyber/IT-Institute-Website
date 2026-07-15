<?php /** @var array $thread @var array $replies */ ?>
<a href="/community" class="text-sm font-semibold text-brand-600 hover:underline">← All questions</a>

<div class="mx-auto mt-4 max-w-3xl">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h1 class="text-xl font-black text-slate-900 dark:text-white"><?= e($thread['title']) ?></h1>
        <p class="mt-1 text-xs text-slate-400"><?= e($thread['author']) ?> · <?= e(date('d M Y, g:i A', strtotime($thread['created_at']))) ?><?php if ($thread['course_title']): ?> · <span class="font-semibold text-brand-600"><?= e($thread['course_title']) ?></span><?php endif; ?></p>
        <p class="mt-4 whitespace-pre-line text-slate-700 dark:text-slate-300"><?= e($thread['body']) ?></p>
    </div>

    <div class="mt-6 space-y-3">
        <?php foreach ($replies as $r): $admin = (int) $r['is_admin'] === 1; ?>
        <div class="rounded-2xl border p-5 <?= $admin ? 'border-brand-300 bg-brand-50 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900' ?>">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 dark:text-white"><?= e($r['author']) ?></span>
                <?php if ($admin): ?><span class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-bold text-white">INSTRUCTOR</span><?php endif; ?>
                <span class="ml-auto text-xs text-slate-400"><?= e(date('d M, g:i A', strtotime($r['created_at']))) ?></span>
            </div>
            <p class="mt-2 whitespace-pre-line text-slate-700 dark:text-slate-300"><?= e($r['body']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <form action="/community/<?= (int) $thread['id'] ?>/reply" method="POST" class="mt-5 rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
        <?= csrf_field() ?>
        <label class="mb-2 block text-sm font-bold text-brand-800 dark:text-brand-200">Reply as Instructor</label>
        <textarea name="body" required rows="3" placeholder="Type your answer to the student…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <button class="mt-3 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Post Reply</button>
    </form>
</div>
