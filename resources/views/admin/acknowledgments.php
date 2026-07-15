<?php /** @var array $rows */ ?>
<p class="text-sm text-slate-500">Every time a student confirms a required lesson, it is recorded here with a timestamp.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Lesson</th><th class="px-5 py-3">When</th></tr>
        </thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-500">No acknowledgments yet.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($r['student']) ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($r['lecture'] ?: ($r['ref_type'] . ' #' . $r['ref_id'])) ?></td>
                <td class="px-5 py-3 text-xs text-slate-400"><?= e(date('d M Y, g:i A', strtotime($r['created_at']))) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
