<?php /** @var array $course @var array $chapter @var int $score @var bool $passed @var int $correct @var int $total */ ?>
<div class="mx-auto max-w-xl text-center">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 dark:border-white/10 dark:bg-slate-900">
        <div class="mx-auto flex h-24 w-24 items-center justify-center rounded-full <?= $passed ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15' : 'bg-red-100 text-red-600 dark:bg-red-500/15' ?>">
            <?= icon($passed ? 'trophy' : 'refresh', 'h-12 w-12') ?>
        </div>
        <h2 class="mt-5 text-2xl font-black text-slate-900 dark:text-white"><?= $passed ? 'Congratulations, you passed!' : 'Not quite there yet' ?></h2>
        <p class="mt-2 text-slate-500"><?= e($chapter['title']) ?> Test</p>

        <div class="mt-6 flex items-center justify-center gap-6">
            <div><p class="text-4xl font-black <?= $passed ? 'text-emerald-600' : 'text-red-600' ?>"><?= $score ?>%</p><p class="text-xs text-slate-400">Your score</p></div>
            <div class="h-12 w-px bg-slate-200 dark:bg-white/10"></div>
            <div><p class="text-4xl font-black text-slate-700 dark:text-slate-200"><?= $correct ?>/<?= $total ?></p><p class="text-xs text-slate-400">Correct</p></div>
        </div>

        <?php if ($passed): ?>
        <div class="mt-6 inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
            <?= icon('certificate','h-4 w-4') ?> A chapter certificate has been issued and the next chapter is now unlocked!
        </div>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="<?= url('/learn/' . $course['slug']) ?>" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700">Continue to next chapter →</a>
            <a href="<?= url('/dashboard') ?>" class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700 dark:border-white/15 dark:text-white">Dashboard</a>
        </div>
        <?php else: ?>
        <p class="mt-6 text-sm text-slate-500">You need <?= (int) $quiz['pass_percent'] ?>% to pass. Review the lessons and try again.</p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="<?= url('/learn/' . $course['slug'] . '/test/' . $chapter['id']) ?>" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700">Try again</a>
            <a href="<?= url('/learn/' . $course['slug']) ?>" class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700 dark:border-white/15 dark:text-white">Review lessons</a>
        </div>
        <?php endif; ?>
    </div>
</div>
