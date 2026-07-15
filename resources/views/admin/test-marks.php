<?php
/** @var array $students @var array $rows */
use App\Models\TestMark;
?>

<div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
    <h2 class="flex items-center gap-2 text-lg font-black text-brand-800 dark:text-brand-200"><?= icon('star','h-5 w-5') ?> Physical Test Marks</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Enter a student's marks for any in-class / physical test. The student sees the result instantly on their dashboard (Marks &amp; Results). Use this for quick tests; for formal weighted exams use <a href="/results" class="font-semibold text-brand-700 underline dark:text-brand-300">Results</a>.</p>
</div>

<?php if ($msg = flash('success')): ?><div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err = flash('error')): ?><div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300"><?= e($err) ?></div><?php endif; ?>

<div class="grid gap-6 lg:grid-cols-3">

    <!-- Entry form -->
    <div class="lg:col-span-1">
        <form action="/test-marks" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Record marks</h3>

            <label class="block text-xs font-semibold text-slate-500">Student *</label>
            <select name="user_id" required class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Select a student…</option>
                <?php foreach ($students as $s): ?>
                <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?><?= $s['reg_no'] ? ' (' . e($s['reg_no']) . ')' : '' ?></option>
                <?php endforeach; ?>
            </select>

            <label class="block text-xs font-semibold text-slate-500">Test name *</label>
            <input name="test_name" required placeholder="e.g. Mid-Term Test, Chapter 3 Quiz" class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <label class="block text-xs font-semibold text-slate-500">Subject / Course</label>
            <input name="subject" placeholder="e.g. Networking" class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <div class="mb-3 grid grid-cols-2 gap-2">
                <div><label class="block text-xs font-semibold text-slate-500">Marks obtained *</label><input name="marks_obtained" type="number" step="0.5" min="0" required class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="block text-xs font-semibold text-slate-500">Out of *</label><input name="total_marks" type="number" step="0.5" min="1" value="100" required class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            </div>

            <div class="mb-3 grid grid-cols-2 gap-2">
                <div><label class="block text-xs font-semibold text-slate-500">Test date</label><input name="test_date" type="date" value="<?= e(date('Y-m-d')) ?>" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="block text-xs font-semibold text-slate-500">Visibility</label>
                    <select name="status" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                        <option value="published">Published (student sees it)</option>
                        <option value="draft">Draft (hidden)</option>
                    </select>
                </div>
            </div>

            <label class="block text-xs font-semibold text-slate-500">Remarks</label>
            <input name="remarks" placeholder="e.g. Good improvement" class="mb-4 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Marks</button>
        </form>
    </div>

    <!-- Recent entries -->
    <div class="lg:col-span-2">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Recent entries</h3>
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Test</th>
                        <th class="px-4 py-3">Marks</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3"></th>
                        <th class="px-4 py-3 text-right">Del</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No marks recorded yet. Add one on the left.</td></tr>
                    <?php else: foreach ($rows as $r):
                        $pct = TestMark::pct($r); $grade = TestMark::grade($pct);
                        $tone = $pct >= 50 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-300'; ?>
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 dark:text-slate-100"><?= e($r['student_name']) ?></p>
                            <?php if ($r['reg_no']): ?><p class="text-xs text-slate-400"><?= e($r['reg_no']) ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-700 dark:text-slate-200"><?= e($r['test_name']) ?></p>
                            <?php if ($r['subject']): ?><p class="text-xs text-slate-400"><?= e($r['subject']) ?></p><?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-bold text-slate-900 dark:text-white"><?= e(rtrim(rtrim((string) $r['marks_obtained'], '0'), '.')) ?> / <?= e(rtrim(rtrim((string) $r['total_marks'], '0'), '.')) ?></span>
                            <span class="ml-1 text-xs font-bold <?= $tone ?>"><?= $pct ?>% · <?= $grade ?></span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-500"><?= e($r['test_date'] ? date('d M Y', strtotime($r['test_date'])) : '-') ?></td>
                        <td class="px-4 py-3"><?php if ($r['status'] === 'draft'): ?><span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Draft</span><?php endif; ?></td>
                        <td class="px-4 py-3 text-right">
                            <form action="/test-marks/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this mark?')">
                                <?= csrf_field() ?>
                                <button class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/20 dark:text-red-300">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
