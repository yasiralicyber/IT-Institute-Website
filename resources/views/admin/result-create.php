<?php /** @var array $courses @var array $batches @var array $schemes */
$inp = 'mt-1 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<a href="/results" class="text-sm font-semibold text-brand-600 hover:underline">← All results</a>
<form action="/results" method="POST" class="mt-4 max-w-xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
    <?= csrf_field() ?>
    <div>
        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Title</label>
        <input name="title" required placeholder="e.g. Final Term - CCNA 2026" class="<?= $inp ?>">
    </div>
    <div>
        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Batch (students come from here)</label>
        <select name="batch_id" class="<?= $inp ?>">
            <option value="">— none —</option>
            <?php foreach ($batches as $b): ?><option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?> (<?= e($b['course']) ?>)</option><?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">…or Course (all enrolled students)</label>
        <select name="course_id" class="<?= $inp ?>">
            <option value="">— none —</option>
            <?php foreach ($courses as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['title']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Grading scheme</label>
            <select name="scheme_id" class="<?= $inp ?>">
                <option value="">Default</option>
                <?php foreach ($schemes as $g): ?><option value="<?= (int) $g['id'] ?>"><?= e($g['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="text-sm font-semibold text-slate-600 dark:text-slate-300">Pass mark (%)</label>
            <input name="pass_mark" type="number" min="0" max="100" value="40" class="<?= $inp ?>">
        </div>
    </div>
    <button class="rounded-xl bg-brand-600 px-8 py-3 font-bold text-white hover:bg-brand-700">Create</button>
</form>
