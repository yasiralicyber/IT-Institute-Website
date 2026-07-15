<?php /** @var array $set @var array $components @var array $rows @var array $bands @var array $quizzes */
$locked = $set['status'] === 'approved';
$inp = 'rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
$totalWeight = array_sum(array_map(fn($c) => (int) $c['weight'], $components));
$badge = ['draft'=>'bg-slate-200 text-slate-600','pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700'][$set['status']] ?? '';
?>
<a href="/results" class="text-sm font-semibold text-brand-600 hover:underline">← All results</a>

<div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <div>
        <h2 class="text-lg font-black text-slate-900 dark:text-white"><?= e($set['title']) ?> <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge ?>"><?= ucfirst($set['status']) ?></span></h2>
        <p class="text-sm text-slate-500"><?= e($set['batch'] ?: $set['course'] ?: 'All students') ?> · Pass mark <?= (int) $set['pass_mark'] ?>% · Weights total <?= $totalWeight ?>%<?= $totalWeight !== 100 && $totalWeight !== 0 ? ' (normalised)' : '' ?></p>
        <?php if ($set['reopen_reason']): ?><p class="mt-1 text-xs font-semibold text-amber-600">Reopened: <?= e($set['reopen_reason']) ?></p><?php endif; ?>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="/results/<?= (int) $set['id'] ?>/merit" target="_blank" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-white">Merit List</a>
        <?php if ($set['status'] === 'draft'): ?>
            <form method="post" action="/results/<?= (int) $set['id'] ?>/submit"><?= csrf_field() ?><button class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600">Submit for approval</button></form>
        <?php elseif ($set['status'] === 'pending'): ?>
            <form method="post" action="/results/<?= (int) $set['id'] ?>/approve" onsubmit="return confirm('Approve and LOCK this result? It will be published to students.')"><?= csrf_field() ?><button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Approve &amp; Publish</button></form>
        <?php else: ?>
            <span class="rounded-xl bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 dark:bg-emerald-500/10">Locked ✓</span>
        <?php endif; ?>
    </div>
</div>

<?php if ($locked): ?>
<div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-500/30 dark:bg-red-500/10">
    <h3 class="font-bold text-red-800 dark:text-red-300">This result is approved and locked</h3>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Marks cannot be changed. To correct an approved result you must reopen it with a recorded reason (kept in the audit log).</p>
    <form method="post" action="/results/<?= (int) $set['id'] ?>/reopen" class="mt-3 flex flex-wrap gap-2" onsubmit="return confirm('Reopen this approved result for editing?')">
        <?= csrf_field() ?>
        <input name="reason" required minlength="5" placeholder="Reason for reopening (required)" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Reopen</button>
    </form>
</div>
<?php endif; ?>

<div class="mt-6 grid gap-6 lg:grid-cols-4">
    <!-- Components -->
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Components</h3>
            <?php if (empty($components)): ?><p class="text-sm text-slate-500">Add at least one component (e.g. Theory, Practical, or an online quiz).</p>
            <?php else: foreach ($components as $c): ?>
            <div class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                <div><p class="font-semibold text-slate-800 dark:text-slate-200"><?= e($c['label']) ?></p><p class="text-xs text-slate-400"><?= ucfirst($c['source']) ?> · wt <?= (int) $c['weight'] ?>% · /<?= (int) $c['max_marks'] ?></p></div>
                <?php if (!$locked): ?><form method="post" action="/results/<?= (int) $set['id'] ?>/component/<?= (int) $c['id'] ?>/delete"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">✕</button></form><?php endif; ?>
            </div>
            <?php endforeach; endif; ?>

            <?php if (!$locked): ?>
            <form method="post" action="/results/<?= (int) $set['id'] ?>/component" class="mt-3 space-y-2 border-t border-slate-100 pt-3 dark:border-white/5">
                <?= csrf_field() ?>
                <input name="label" required placeholder="Component (e.g. Practical)" class="w-full <?= $inp ?>">
                <select name="source" class="w-full <?= $inp ?>" onchange="this.closest('form').querySelector('[name=quiz_id]').classList.toggle('hidden', this.value!=='online')">
                    <option value="offline">Offline (manual / import)</option>
                    <option value="online">Online (quiz)</option>
                </select>
                <select name="quiz_id" class="hidden w-full <?= $inp ?>">
                    <option value="">Choose quiz…</option>
                    <?php foreach ($quizzes as $q): ?><option value="<?= (int) $q['id'] ?>"><?= e($q['title']) ?></option><?php endforeach; ?>
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input name="weight" type="number" min="0" placeholder="Weight %" class="<?= $inp ?>">
                    <input name="max_marks" type="number" min="1" value="100" placeholder="Max marks" class="<?= $inp ?>">
                </div>
                <button class="w-full rounded-lg bg-slate-800 py-2 text-sm font-bold text-white dark:bg-white/10">Add Component</button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (!$locked && $components): ?>
        <form method="post" action="/results/<?= (int) $set['id'] ?>/sync" class="rounded-2xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-500/30 dark:bg-sky-500/10">
            <?= csrf_field() ?>
            <p class="text-sm font-bold text-sky-800 dark:text-sky-300">Online scores</p>
            <p class="mt-1 text-xs text-slate-500">Pull best quiz attempts into online components.</p>
            <button class="mt-2 w-full rounded-lg bg-sky-600 py-2 text-sm font-bold text-white hover:bg-sky-700">Sync online quizzes</button>
        </form>

        <form method="post" action="/results/<?= (int) $set['id'] ?>/import" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <p class="text-sm font-bold text-slate-800 dark:text-white">Import offline marks (CSV)</p>
            <p class="mt-1 text-xs text-slate-500">Columns: <code>reg_no</code> or <code>email</code> + one column per offline component label.</p>
            <input type="file" name="csv" accept=".csv" required class="mt-2 w-full text-xs">
            <button class="mt-2 w-full rounded-lg bg-slate-800 py-2 text-sm font-bold text-white dark:bg-white/10">Import CSV</button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Gradebook grid -->
    <div class="lg:col-span-3">
        <form method="post" action="/results/<?= (int) $set['id'] ?>/scores" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <table class="w-full min-w-[600px] text-sm">
                <thead class="text-left text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-2 py-2">#</th>
                        <th class="px-2 py-2">Student</th>
                        <?php foreach ($components as $c): ?><th class="px-2 py-2 text-center"><?= e($c['label']) ?><br><span class="text-[10px] normal-case text-slate-300">/<?= (int) $c['max_marks'] ?> · <?= $c['source'] === 'online' ? 'auto' : 'wt ' . (int) $c['weight'] ?></span></th><?php endforeach; ?>
                        <th class="px-2 py-2 text-center">%</th>
                        <th class="px-2 py-2 text-center">Grade</th>
                        <th class="px-2 py-2 text-center">Result</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($components) + 6 ?>" class="px-2 py-8 text-center text-slate-500">No students attached. Link a batch or course.</td></tr>
                <?php else: foreach ($rows as $row): $s = $row['student']; ?>
                    <tr class="border-t border-slate-100 dark:border-white/5">
                        <td class="px-2 py-2 text-slate-400"><?= (int) $row['rank'] ?></td>
                        <td class="px-2 py-2 font-semibold text-slate-800 dark:text-slate-200"><?= e($s['name']) ?><?php if ($s['reg_no']): ?><br><span class="text-[10px] text-slate-400"><?= e($s['reg_no']) ?></span><?php endif; ?></td>
                        <?php foreach ($components as $c): $cell = $row['cells'][$c['id']] ?? ['obtained'=>0]; ?>
                        <td class="px-2 py-2 text-center">
                            <?php if ($locked || $c['source'] === 'online'): ?>
                                <span class="<?= $c['source']==='online' ? 'text-sky-600' : '' ?>"><?= rtrim(rtrim(number_format((float) $cell['obtained'], 2), '0'), '.') ?></span>
                            <?php else: ?>
                                <input name="score[<?= (int) $s['id'] ?>][<?= (int) $c['id'] ?>]" type="number" step="0.5" min="0" max="<?= (int) $c['max_marks'] ?>" value="<?= $cell['obtained'] > 0 ? rtrim(rtrim(number_format((float) $cell['obtained'], 2), '0'), '.') : '' ?>" class="w-16 <?= $inp ?> text-center">
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="px-2 py-2 text-center font-bold text-slate-900 dark:text-white"><?= $row['percent'] ?></td>
                        <td class="px-2 py-2 text-center font-bold"><?= e($row['grade']) ?></td>
                        <td class="px-2 py-2 text-center"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $row['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $row['passed'] ? 'Pass' : 'Fail' ?></span></td>
                        <td class="px-2 py-2 text-center"><a href="/results/<?= (int) $set['id'] ?>/card/<?= (int) $s['id'] ?>" target="_blank" class="text-xs font-bold text-brand-600 hover:underline">Card</a></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <?php if (!$locked && $rows): ?>
            <button class="mt-4 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Marks</button>
            <span class="ml-2 text-xs text-slate-400">Online columns are auto-synced and read-only.</span>
            <?php endif; ?>
        </form>
    </div>
</div>
