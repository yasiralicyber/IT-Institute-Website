<?php /** @var array $rows @var array $kinds */ ?>
<p class="text-sm text-slate-500">Every time a student leaves full-screen, switches tab, or tries to copy during a locked exam, it is logged here.</p>

<?php if ($kinds): ?>
<div class="mt-4 flex flex-wrap gap-2">
    <?php foreach ($kinds as $k): ?>
    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300"><?= e(str_replace('_', ' ', $k['kind'])) ?>: <?= (int) $k['c'] ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Exam</th><th class="px-5 py-3 text-center">Violations</th><th class="px-5 py-3">Last</th></tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">No violations recorded. Locked exams are clean.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($r['student']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['quiz']) ?></td>
                <td class="px-5 py-3 text-center"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= (int) $r['total'] >= 3 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' ?>"><?= (int) $r['total'] ?></span></td>
                <td class="px-5 py-3 text-xs text-slate-400"><?= e(date('d M Y, g:i A', strtotime($r['last_at']))) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
