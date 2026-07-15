<?php /** @var array $cards @var array $user @var array $appeals */ ?>
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-900 dark:text-white">My Results</h1>
    <p class="text-sm text-slate-500">Your approved examination results and transcript.</p>
</div>

<?php if (empty($cards)): ?>
<div class="rounded-2xl border border-slate-200 bg-white p-10 text-center dark:border-white/10 dark:bg-slate-900">
    <p class="text-slate-500">No results have been published yet. They will appear here once your instructor approves them.</p>
</div>
<?php else: ?>

<!-- Transcript summary -->
<div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <div class="border-b border-slate-100 px-5 py-3 dark:border-white/5"><h2 class="font-black text-slate-900 dark:text-white">Transcript</h2></div>
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-2">Examination</th><th class="px-5 py-2 text-center">%</th><th class="px-5 py-2 text-center">Grade</th><th class="px-5 py-2 text-center">GPA</th><th class="px-5 py-2 text-center">Result</th></tr>
        </thead>
        <tbody>
        <?php foreach ($cards as $card): $r = $card['r']; ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-2 font-semibold text-slate-800 dark:text-slate-200"><?= e($card['set']['title']) ?></td>
                <td class="px-5 py-2 text-center font-bold"><?= $r['percent'] ?></td>
                <td class="px-5 py-2 text-center font-bold"><?= e($r['grade']) ?></td>
                <td class="px-5 py-2 text-center text-slate-500"><?= e((string) $r['gpa']) ?></td>
                <td class="px-5 py-2 text-center"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $r['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $r['passed'] ? 'Pass' : 'Fail' ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Detailed cards -->
<div class="grid gap-6 lg:grid-cols-2">
    <?php foreach ($cards as $card): $set = $card['set']; $r = $card['r']; ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white"><?= e($set['title']) ?></h3>
        <p class="text-xs text-slate-400"><?= e($set['batch'] ?: $set['course'] ?: '') ?></p>
        <table class="mt-3 w-full text-sm">
            <thead class="border-y border-slate-100 text-left text-xs uppercase text-slate-400 dark:border-white/5"><tr><th class="py-1.5">Component</th><th class="py-1.5 text-center">Max</th><th class="py-1.5 text-center">Marks</th></tr></thead>
            <tbody>
            <?php foreach ($card['components'] as $c): $cell = $r['cells'][$c['id']] ?? ['obtained'=>0,'max'=>$c['max_marks']]; ?>
                <tr class="border-b border-slate-50 dark:border-white/5"><td class="py-1.5 font-semibold text-slate-700 dark:text-slate-200"><?= e($c['label']) ?></td><td class="py-1.5 text-center text-slate-400"><?= (int) $cell['max'] ?></td><td class="py-1.5 text-center font-bold"><?= rtrim(rtrim(number_format((float) $cell['obtained'], 2), '0'), '.') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl bg-slate-50 p-2 dark:bg-white/5"><p class="text-xs text-slate-400">%</p><p class="text-lg font-black text-brand-800 dark:text-brand-300"><?= $r['percent'] ?></p></div>
            <div class="rounded-xl bg-slate-50 p-2 dark:bg-white/5"><p class="text-xs text-slate-400">Grade</p><p class="text-lg font-black text-brand-800 dark:text-brand-300"><?= e($r['grade']) ?></p></div>
            <div class="rounded-xl <?= $r['passed'] ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-red-50 dark:bg-red-500/10' ?> p-2"><p class="text-xs text-slate-400">Result</p><p class="text-lg font-black <?= $r['passed'] ? 'text-emerald-600' : 'text-red-600' ?>"><?= $r['passed'] ? 'PASS' : 'FAIL' ?></p></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Score appeal -->
<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/30 dark:bg-amber-500/10">
        <h3 class="font-black text-amber-800 dark:text-amber-300">Think a mark is wrong?</h3>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">File a score appeal. The examinations office will review and respond.</p>
        <form method="post" action="/appeals" class="mt-3 space-y-2">
            <?= csrf_field() ?>
            <select name="ref_id" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Which exam?</option>
                <?php foreach ($cards as $card): ?><option value="<?= (int) $card['set']['id'] ?>"><?= e($card['set']['title']) ?></option><?php endforeach; ?>
            </select>
            <input name="subject" required placeholder="Subject (e.g. Theory marks query)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <textarea name="reason" rows="3" required placeholder="Explain your appeal (min 10 characters)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
            <button class="rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-amber-700">Submit Appeal</button>
        </form>
    </div>

    <?php if (!empty($appeals)): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white">My appeals</h3>
        <?php $b = ['open'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700']; foreach ($appeals as $a): ?>
        <div class="mt-3 border-b border-slate-100 pb-3 last:border-0 dark:border-white/5">
            <div class="flex items-center justify-between">
                <p class="font-semibold text-slate-800 dark:text-slate-200"><?= e($a['subject']) ?></p>
                <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $b[$a['status']] ?? '' ?>"><?= ucfirst($a['status']) ?></span>
            </div>
            <?php if ($a['response']): ?><p class="mt-1 text-xs text-slate-500"><strong>Reply:</strong> <?= e($a['response']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
