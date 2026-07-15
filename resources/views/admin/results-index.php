<?php /** @var array $sets */
$badge = ['draft'=>'bg-slate-200 text-slate-600','pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700'];
?>
<div class="flex items-center justify-between">
    <p class="text-sm text-slate-500">Build result sets from online quizzes and offline marks, grade them, then approve to publish.</p>
    <a href="/results/new" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ New Result Set</a>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">Title</th><th class="px-5 py-3">Course / Batch</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody>
        <?php if (empty($sets)): ?>
            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No result sets yet.</td></tr>
        <?php else: foreach ($sets as $s): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($s['title']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($s['batch'] ?: $s['course'] ?: 'All') ?></td>
                <td class="px-5 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge[$s['status']] ?? '' ?>"><?= ucfirst($s['status']) ?></span></td>
                <td class="px-5 py-3 text-right"><a href="/results/<?= (int) $s['id'] ?>" class="font-bold text-brand-600 hover:underline">Open →</a></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
