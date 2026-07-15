<?php /** @var string $q @var array $tokens @var array $lessons @var array $courses @var array $threads */ ?>
<form action="<?= url('/search') ?>" method="GET" class="mb-6 flex gap-2">
    <input name="q" value="<?= e($q) ?>" autofocus placeholder="Search a concept, e.g. how routing works…" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-3 dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <button class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700">Search</button>
</form>

<?php $total = count($lessons) + count($courses) + count($threads); ?>
<?php if ($q === ''): ?>
    <p class="text-slate-500">Type a topic or question to find related lessons, courses and discussions.</p>
<?php elseif ($total === 0): ?>
    <p class="text-slate-500">No matches for "<?= e($q) ?>". Try different keywords.</p>
<?php else: ?>

<?php if ($lessons): ?>
<div class="mb-6">
    <h2 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-400">Lessons (<?= count($lessons) ?>)</h2>
    <div class="space-y-2">
        <?php foreach ($lessons as $l): ?>
        <a href="<?= $l['accessible'] ? url('/learn/' . $l['slug'] . '/' . (int) $l['id']) : url('/courses/' . $l['slug']) ?>" class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4 transition hover:shadow-md dark:border-white/10 dark:bg-slate-900">
            <div class="min-w-0">
                <p class="font-bold text-slate-900 dark:text-white"><?= e($l['title']) ?></p>
                <p class="text-xs text-brand-600"><?= e($l['course']) ?><?= $l['is_free'] ? ' · FREE' : '' ?></p>
            </div>
            <span class="flex-none text-sm font-bold text-brand-600"><?= $l['accessible'] ? 'Watch →' : 'Locked' ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($courses): ?>
<div class="mb-6">
    <h2 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-400">Courses (<?= count($courses) ?>)</h2>
    <div class="grid gap-2 sm:grid-cols-2">
        <?php foreach ($courses as $c): ?>
        <a href="<?= url('/courses/' . $c['slug']) ?>" class="rounded-2xl border border-slate-200 bg-white p-4 hover:shadow-md dark:border-white/10 dark:bg-slate-900">
            <p class="font-bold text-slate-900 dark:text-white"><?= e($c['title']) ?></p>
            <p class="text-xs text-slate-500"><?= e($c['category']) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($threads): ?>
<div>
    <h2 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-400">Community (<?= count($threads) ?>)</h2>
    <div class="space-y-2">
        <?php foreach ($threads as $t): ?>
        <a href="<?= url('/community/' . (int) $t['id']) ?>" class="block rounded-2xl border border-slate-200 bg-white p-4 hover:shadow-md dark:border-white/10 dark:bg-slate-900">
            <p class="font-bold text-slate-900 dark:text-white"><?= e($t['title']) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
