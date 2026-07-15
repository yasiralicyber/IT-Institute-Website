<?php /** @var array $course @var array $chapters @var bool $courseDone */ ?>
<a href="/learn/<?= e($course['slug']) ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back to lessons</a>

<div class="mt-4 mb-8 rounded-2xl bg-gradient-to-r from-brand-800 to-brand-950 p-6 text-white">
    <h1 class="text-2xl font-black"><?= e($course['title']) ?> - Learning Path</h1>
    <p class="mt-1 text-sm text-brand-100">Complete each stage and pass its test to unlock the next. Reach the end to earn your certificate. </p>
</div>

<div class="relative mx-auto max-w-2xl">
    <?php foreach ($chapters as $i => $ch):
        $cfg = [
            'done'    => ['bg-emerald-500 text-white', 'ring-emerald-200', '✓', 'Completed'],
            'current' => ['bg-brand-600 text-white animate-pulse', 'ring-brand-200', (string) ($i + 1), 'In progress'],
            'locked'  => ['bg-slate-200 text-slate-400 dark:bg-white/10', 'ring-transparent', '', 'Locked'],
        ][$ch['state']];
    ?>
    <div class="relative flex gap-5 pb-2">
        <!-- node + connector -->
        <div class="flex flex-col items-center">
            <span class="z-10 flex h-12 w-12 flex-none items-center justify-center rounded-full text-lg font-black ring-4 <?= $cfg[0] ?> <?= $cfg[1] ?>"><?= $cfg[2] ?></span>
            <?php if ($i < count($chapters) - 1): ?><span class="w-1 flex-1 <?= $ch['state'] === 'done' ? 'bg-emerald-400' : 'bg-slate-200 dark:bg-white/10' ?>"></span><?php endif; ?>
        </div>
        <!-- card -->
        <div class="mb-6 flex-1 rounded-2xl border bg-white p-5 dark:bg-slate-900 <?= $ch['state'] === 'current' ? 'border-brand-300 shadow-lg dark:border-brand-500/40' : 'border-slate-200 dark:border-white/10' ?>">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white"><?= e($ch['title']) ?></h3>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $ch['state']==='done'?'bg-emerald-100 text-emerald-700':($ch['state']==='current'?'bg-brand-100 text-brand-700':'bg-slate-100 text-slate-400') ?>"><?= $cfg[3] ?></span>
            </div>
            <p class="mt-1 text-sm text-slate-500"><?= (int) $ch['done'] ?> / <?= (int) $ch['total'] ?> lessons · Test: <?= $ch['passed'] ? 'passed' : 'pending' ?></p>
            <?php if ($ch['state'] !== 'locked'): ?>
            <div class="mt-3 flex gap-2">
                <a href="/learn/<?= e($course['slug']) ?>/<?= (int) $ch['first_lecture'] ?>" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700"><?= $ch['done'] ? 'Review' : 'Start' ?> →</a>
                <a href="/learn/<?= e($course['slug']) ?>/test/<?= (int) $ch['id'] ?>" class="rounded-lg bg-amber-100 px-4 py-2 text-sm font-bold text-amber-700 hover:bg-amber-200">Take Test</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- finish -->
    <div class="relative flex gap-5">
        <div class="flex flex-col items-center"><span class="z-10 flex h-12 w-12 items-center justify-center rounded-full text-2xl ring-4 <?= $courseDone ? 'bg-gold-400 ring-gold-200' : 'bg-slate-200 ring-transparent dark:bg-white/10' ?>"></span></div>
        <div class="flex-1 rounded-2xl border border-gold-200 bg-gold-50 p-5 dark:border-gold-500/30 dark:bg-gold-500/10">
            <h3 class="font-bold text-slate-900 dark:text-white">Course Certificate</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300"><?= $courseDone ? 'Earned! Check your dashboard to view it.' : 'Finish all chapters to earn your verifiable certificate.' ?></p>
        </div>
    </div>
</div>
