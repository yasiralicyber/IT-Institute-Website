<?php /** @var string $q @var array $results */
$groups = [
    'students' => ['Students', fn($r) => ['/students/' . $r['id'], $r['name'], $r['email']]],
    'courses' => ['Courses', fn($r) => ['/courses/' . $r['id'] . '/edit', $r['title'], $r['slug']]],
    'batches' => ['Batches', fn($r) => ['/batches/' . $r['id'], $r['name'], '']],
    'staff' => ['Staff', fn($r) => ['/staff/' . $r['id'] . '/edit', $r['name'], $r['role'] ?? '']],
    'admissions' => ['Admissions', fn($r) => ['/admissions/' . $r['id'], $r['name'], $r['programs'] ?? '']],
];
$total = array_sum(array_map('count', $results));
?>
<form method="GET" action="/search" class="mb-6 flex gap-2">
    <input name="q" value="<?= e($q) ?>" autofocus placeholder="Search students, courses, batches, staff…" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-3 dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <button class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700">Search</button>
</form>

<?php if ($q === ''): ?>
    <p class="text-slate-500">Type at least 2 characters to search across the system.</p>
<?php elseif ($total === 0): ?>
    <p class="text-slate-500">No results for "<?= e($q) ?>".</p>
<?php else: ?>
<div class="space-y-6">
    <?php foreach ($groups as $key => [$label, $fmt]): if (empty($results[$key])) continue; ?>
    <div>
        <h2 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-400"><?= $label ?> (<?= count($results[$key]) ?>)</h2>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <?php foreach ($results[$key] as $r): [$href, $title, $sub] = $fmt($r); ?>
            <a href="<?= e($href) ?>" class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50 dark:border-white/5 dark:hover:bg-white/5">
                <span><span class="font-bold text-slate-900 dark:text-white"><?= e($title) ?></span> <span class="text-sm text-slate-400"><?= e($sub) ?></span></span>
                <span class="text-brand-600">→</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
