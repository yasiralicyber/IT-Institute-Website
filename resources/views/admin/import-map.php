<?php /** @var array $session @var array $def @var array $headers @var array $preview @var array $suggest */ ?>
<a href="/imports" class="text-sm font-semibold text-brand-600 hover:underline">← Cancel</a>

<div class="mt-4 mb-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <p class="text-sm text-slate-500">File: <strong class="text-slate-800 dark:text-slate-200"><?= e($session['filename']) ?></strong> · <?= (int) $session['total_rows'] ?> rows · <?= count($headers) ?> columns detected</p>
</div>

<form action="/imports/<?= (int) $session['id'] ?>/map" method="POST">
    <?= csrf_field() ?>
    <h2 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Step 3 - Match your columns</h2>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">System field</th><th class="px-5 py-3">Your spreadsheet column</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($def['fields'] as $field => $cfg): ?>
                <tr>
                    <td class="px-5 py-3">
                        <span class="font-bold text-slate-900 dark:text-white"><?= e($cfg['label']) ?></span>
                        <?php if (!empty($cfg['required'])): ?><span class="ml-1 text-xs font-bold text-red-500">required</span><?php endif; ?>
                        <?php if (!empty($cfg['relation'])): ?><span class="ml-1 rounded bg-brand-50 px-1.5 text-[10px] font-bold text-brand-700 dark:bg-brand-500/10">linked</span><?php endif; ?>
                    </td>
                    <td class="px-5 py-3">
                        <select name="map_<?= e($field) ?>" class="w-full max-w-xs rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            <option value="">- Ignore -</option>
                            <?php foreach ($headers as $i => $h): ?>
                            <option value="<?= $i ?>" <?= ($suggest[$field] ?? null) === $i ? 'selected' : '' ?>><?= e($h) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">If a record already exists…</h3>
        <div class="mt-3 flex flex-wrap gap-4 text-sm">
            <label class="flex items-center gap-2"><input type="radio" name="dupe_strategy" value="skip" checked class="text-brand-600"> Skip duplicates</label>
            <label class="flex items-center gap-2"><input type="radio" name="dupe_strategy" value="update" class="text-brand-600"> Update existing</label>
            <label class="flex items-center gap-2"><input type="radio" name="dupe_strategy" value="create" class="text-brand-600"> Always create new</label>
        </div>
        <?php if (!empty($def['dupe'])): ?><p class="mt-2 text-xs text-slate-400">Duplicates are matched by: <?= e(implode(', ', $def['dupe'])) ?>.</p><?php endif; ?>
    </div>

    <!-- Data preview -->
    <h3 class="mb-2 mt-6 text-sm font-bold uppercase tracking-wider text-slate-400">Data preview (first <?= count($preview) ?> rows)</h3>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-xs">
            <thead class="bg-slate-50 dark:bg-white/5"><tr><?php foreach ($headers as $h): ?><th class="px-3 py-2 text-left font-semibold text-slate-500"><?= e($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($preview as $row): ?><tr><?php foreach ($headers as $i => $h): ?><td class="px-3 py-2 text-slate-600 dark:text-slate-300"><?= e($row[$i] ?? '') ?></td><?php endforeach; ?></tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <button class="mt-5 w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700">Validate & Review →</button>
</form>
