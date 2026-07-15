<?php /** @var array $session @var array $def @var int $validCount @var array $errors @var string $strategy */ ?>
<a href="/imports/<?= (int) $session['id'] ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back to mapping</a>

<div class="mt-4 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10"><p class="text-3xl font-black text-emerald-600"><?= $validCount ?></p><p class="text-sm text-slate-600 dark:text-slate-300">Rows ready to import</p></div>
    <div class="rounded-2xl border <?= $errors ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900' ?> p-5"><p class="text-3xl font-black <?= $errors ? 'text-red-600' : 'text-slate-400' ?>"><?= count($errors) ?></p><p class="text-sm text-slate-600 dark:text-slate-300">Rows with problems</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-3xl font-black text-slate-900 dark:text-white"><?= ucfirst($strategy) ?></p><p class="text-sm text-slate-600 dark:text-slate-300">Duplicate handling</p></div>
</div>

<?php if ($errors): ?>
<div class="mt-6">
    <h3 class="mb-2 font-bold text-slate-900 dark:text-white">Problems found - these rows will be skipped</h3>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5"><tr><th class="px-5 py-3">Row</th><th class="px-5 py-3">Field</th><th class="px-5 py-3">Value</th><th class="px-5 py-3">Problem</th></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach (array_slice($errors, 0, 50) as $e): ?>
                <tr><td class="px-5 py-2.5 text-slate-500"><?= (int) $e['row'] ?></td><td class="px-5 py-2.5 font-semibold text-slate-700 dark:text-slate-200"><?= e($e['field']) ?></td><td class="px-5 py-2.5 text-slate-500"><?= e(mb_substr((string)$e['value'], 0, 40)) ?: '-' ?></td><td class="px-5 py-2.5 text-red-600"><?= e($e['reason']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($errors) > 50): ?><p class="mt-2 text-xs text-slate-400">Showing first 50 of <?= count($errors) ?> problems.</p><?php endif; ?>
</div>
<?php endif; ?>

<form action="/imports/<?= (int) $session['id'] ?>/execute" method="POST" class="mt-6 flex flex-wrap items-center gap-3">
    <?= csrf_field() ?>
    <?php if ($validCount > 0): ?>
        <button class="rounded-xl bg-brand-600 px-8 py-3 font-bold text-white hover:bg-brand-700">Import <?= $validCount ?> rows now</button>
        <a href="/imports/<?= (int) $session['id'] ?>" class="rounded-xl border border-slate-300 px-6 py-3 font-bold text-slate-700 dark:border-white/15 dark:text-white">Adjust mapping</a>
    <?php else: ?>
        <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">No valid rows to import. Fix your file and try again.</p>
        <a href="/imports" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">Start over</a>
    <?php endif; ?>
</form>
