<?php /** @var array $threads @var array $courses */ ?>
<div class="grid gap-6 lg:grid-cols-[1fr_340px]">
    <!-- Threads -->
    <div>
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Community Questions</h2>
        <?php if (empty($threads)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-white/10 dark:bg-slate-900">
                <p class="text-slate-500">No questions yet. Be the first to ask!</p>
            </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($threads as $t): ?>
            <a href="<?= url('/community/' . $t['id']) ?>" class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-md dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-bold text-slate-900 dark:text-white"><?= e($t['title']) ?></h3>
                    <span class="flex-none rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"><?= (int) $t['replies'] ?> </span>
                </div>
                <p class="mt-1 line-clamp-2 text-sm text-slate-500"><?= e(mb_substr($t['body'], 0, 140)) ?></p>
                <p class="mt-3 text-xs text-slate-400">
                    <?= e($t['author']) ?> · <?= e(date('d M Y', strtotime($t['created_at']))) ?>
                    <?php if ($t['course_title']): ?> · <span class="font-semibold text-brand-600"><?= e($t['course_title']) ?></span><?php endif; ?>
                </p>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Ask form -->
    <div>
        <div class="sticky top-20 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="font-bold text-slate-900 dark:text-white">Ask a question</h3>
            <form action="<?= url('/community') ?>" method="POST" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input name="title" required placeholder="Question title" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <textarea name="body" required rows="4" placeholder="Describe your question…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
                <select name="course_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <option value="">General (no course)</option>
                    <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?>
                </select>
                <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Post Question</button>
            </form>
        </div>
    </div>
</div>
