<?php /** @var array $schemes */
$inp = 'rounded-lg border-slate-300 bg-white px-2 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<p class="text-sm text-slate-500">Define grade bands (minimum %, grade letter, GPA, remark). The default scheme is used when a result set has none chosen.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <?php foreach ($schemes as $s): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="font-black text-slate-900 dark:text-white"><?= e($s['name']) ?> <?php if ($s['is_default']): ?><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Default</span><?php endif; ?></h3>
            <form method="post" action="/grading-schemes/<?= (int) $s['id'] ?>/delete" onsubmit="return confirm('Delete scheme?')"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">Delete</button></form>
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase text-slate-400"><tr><th class="py-1">Min %</th><th class="py-1">Grade</th><th class="py-1">GPA</th><th class="py-1">Remark</th></tr></thead>
            <tbody>
            <?php foreach ($s['bandsArr'] as $b): ?>
                <tr class="border-t border-slate-100 dark:border-white/5"><td class="py-1 font-semibold"><?= e((string) $b['min']) ?></td><td class="py-1 font-bold"><?= e($b['grade']) ?></td><td class="py-1"><?= e((string) ($b['gpa'] ?? '')) ?></td><td class="py-1 text-slate-500"><?= e($b['remark'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- New / edit scheme -->
    <div class="rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
        <h3 class="mb-3 font-black text-brand-800 dark:text-brand-300">New grading scheme</h3>
        <form method="post" action="/grading-schemes">
            <?= csrf_field() ?>
            <input name="name" required placeholder="Scheme name (e.g. Standard %)" class="mb-3 w-full <?= $inp ?>">
            <div id="bands" class="space-y-2">
                <?php foreach ([[80,'A+',4],[70,'A',3.5],[60,'B',3],[50,'C',2],[40,'D',1],[0,'F',0]] as $d): ?>
                <div class="grid grid-cols-4 gap-2">
                    <input name="min[]" type="number" step="0.1" value="<?= $d[0] ?>" placeholder="Min %" class="<?= $inp ?>">
                    <input name="grade[]" value="<?= $d[1] ?>" placeholder="Grade" class="<?= $inp ?>">
                    <input name="gpa[]" type="number" step="0.1" value="<?= $d[2] ?>" placeholder="GPA" class="<?= $inp ?>">
                    <input name="remark[]" placeholder="Remark" class="<?= $inp ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" onclick="addBand()" class="mt-2 text-xs font-bold text-brand-600">+ Add band</button>
            <label class="mt-3 flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300"><input type="checkbox" name="is_default" value="1" class="rounded border-slate-300"> Set as default</label>
            <button class="mt-3 w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Scheme</button>
        </form>
    </div>
</div>

<script>
function addBand() {
    var row = document.querySelector('#bands > div').cloneNode(true);
    row.querySelectorAll('input').forEach(function(i){ i.value=''; });
    document.getElementById('bands').appendChild(row);
}
</script>
