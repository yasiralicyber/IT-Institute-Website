<?php /** @var array $session @var array $def @var array $errors */ ?>
<a href="/imports" class="text-sm font-semibold text-brand-600 hover:underline">← New import</a>

<div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center dark:border-emerald-500/30 dark:bg-emerald-500/10">
    <p class="text-4xl">✓</p>
    <h2 class="mt-2 text-xl font-black text-slate-900 dark:text-white">Import <?= $session['status'] === 'rolled_back' ? 'rolled back' : 'completed' ?></h2>
    <p class="text-sm text-slate-600 dark:text-slate-300"><?= e($def['label']) ?> · <?= e($session['filename']) ?></p>
</div>

<div class="mt-5 grid gap-4 sm:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-2xl font-black text-emerald-600"><?= (int) $session['imported'] ?></p><p class="text-sm text-slate-500">Imported</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-2xl font-black text-brand-600"><?= (int) $session['updated'] ?></p><p class="text-sm text-slate-500">Updated</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-2xl font-black text-slate-500"><?= (int) $session['skipped'] ?></p><p class="text-sm text-slate-500">Skipped</p></div>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900"><p class="text-2xl font-black text-red-500"><?= (int) $session['failed'] ?></p><p class="text-sm text-slate-500">Failed</p></div>
</div>

<?php if ($session['status'] !== 'rolled_back' && (int) $session['imported'] > 0): ?>
<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <h3 class="font-bold text-slate-900 dark:text-white">Undo this import</h3>
    <p class="mt-1 text-sm text-slate-500">This removes the <?= (int) $session['imported'] ?> records created by this import. Requires your admin password.</p>
    <form action="/imports/<?= (int) $session['id'] ?>/rollback" method="POST" class="mt-3 flex gap-2">
        <?= csrf_field() ?>
        <input type="password" name="password" required placeholder="Admin password" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button onclick="return confirm('Undo this import?')" class="rounded-xl border border-red-300 px-5 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Undo Import</button>
    </form>
</div>
<?php endif; ?>

<?php if ($errors): ?>
<div class="mt-6">
    <h3 class="mb-2 font-bold text-slate-900 dark:text-white">Skipped rows (<?= count($errors) ?>)</h3>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5"><tr><th class="px-5 py-3">Row</th><th class="px-5 py-3">Field</th><th class="px-5 py-3">Problem</th></tr></thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php foreach (array_slice($errors, 0, 50) as $e): ?><tr><td class="px-5 py-2 text-slate-500"><?= (int) $e['row'] ?></td><td class="px-5 py-2 text-slate-700 dark:text-slate-200"><?= e($e['field']) ?></td><td class="px-5 py-2 text-red-600"><?= e($e['reason']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
</div>
<?php endif; ?>
