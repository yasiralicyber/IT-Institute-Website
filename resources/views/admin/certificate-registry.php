<?php /** @var array $rows @var string $search @var string $nextNo @var string $verifyBase */ ?>

<div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 p-5 dark:border-brand-500/30 dark:bg-brand-500/10">
    <h2 class="flex items-center gap-2 text-lg font-black text-brand-800 dark:text-brand-200"><?= icon('certificate','h-5 w-5') ?> Physical Certificate Registry</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Record any certificate the institute hands out in person (offline courses, exams, workshops). Once saved here, the printed certificate can be <strong>verified online</strong> by its number at <span class="font-mono"><?= e(parse_url($verifyBase, PHP_URL_HOST)) ?>/verify</span>. Print the number (or a QR of it) on the certificate.</p>
</div>

<?php if ($msg = flash('success')): ?><div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err = flash('error')): ?><div class="mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-300"><?= e($err) ?></div><?php endif; ?>

<div class="grid gap-6 lg:grid-cols-3">

    <!-- Add form -->
    <div class="lg:col-span-1">
        <form action="/certificates" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Register a certificate</h3>

            <label class="block text-xs font-semibold text-slate-500">Certificate No. <span class="text-slate-400">(leave blank to auto-generate)</span></label>
            <input name="cert_no" placeholder="<?= e($nextNo) ?>" class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <label class="block text-xs font-semibold text-slate-500">Student name *</label>
            <input name="student_name" required class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <label class="block text-xs font-semibold text-slate-500">Father's name</label>
            <input name="father_name" class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <label class="block text-xs font-semibold text-slate-500">Course / Trade</label>
            <input name="course" placeholder="e.g. Micro Soft Office" class="mb-3 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <div class="mb-3 grid grid-cols-2 gap-2">
                <div><label class="block text-xs font-semibold text-slate-500">From</label><input name="from_date" type="date" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="block text-xs font-semibold text-slate-500">To</label><input name="to_date" type="date" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            </div>
            <div class="mb-3 grid grid-cols-2 gap-2">
                <div><label class="block text-xs font-semibold text-slate-500">Issue date</label><input name="issue_date" type="date" value="<?= e(date('Y-m-d')) ?>" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="block text-xs font-semibold text-slate-500">Grade</label><input name="grade" placeholder="A / Pass" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            </div>

            <label class="block text-xs font-semibold text-slate-500">Remarks</label>
            <input name="remarks" class="mb-4 mt-1 w-full rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

            <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Register Certificate</button>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2">
        <form method="get" action="/certificates" class="mb-3 flex items-center gap-2">
            <input name="q" value="<?= e($search) ?>" placeholder="Search by number, name, course…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white dark:bg-white/10">Search</button>
        </form>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3">Certificate No.</th>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3">Issued</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No certificates registered yet. Add one on the left.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="font-mono font-bold text-brand-700 dark:text-brand-300"><?= e($r['cert_no']) ?></span>
                            <a href="<?= e($verifyBase . urlencode($r['cert_no'])) ?>" target="_blank" rel="noopener" class="ml-1 text-xs text-slate-400 hover:text-brand-600">verify ↗</a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800 dark:text-slate-100"><?= e($r['student_name']) ?></p>
                            <?php if ($r['father_name']): ?><p class="text-xs text-slate-400">S/D of <?= e($r['father_name']) ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($r['course'] ?: '-') ?></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-500"><?= e($r['issue_date'] ? date('d M Y', strtotime($r['issue_date'])) : '-') ?></td>
                        <td class="px-4 py-3">
                            <?php if ($r['status'] === 'revoked'): ?>
                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-500/15 dark:text-red-300">Revoked</span>
                            <?php else: ?>
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Valid</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="/certificates/<?= (int) $r['id'] ?>/pdf" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300">PDF</a>
                                <form action="/certificates/<?= (int) $r['id'] ?>/revoke" method="POST" onsubmit="return confirm('Toggle valid/revoked for <?= e($r['cert_no']) ?>?')">
                                    <?= csrf_field() ?>
                                    <button class="rounded-lg border border-amber-200 px-2.5 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-50 dark:border-amber-500/20 dark:text-amber-300"><?= $r['status'] === 'revoked' ? 'Restore' : 'Revoke' ?></button>
                                </form>
                                <form action="/certificates/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete registry record <?= e($r['cert_no']) ?>? It will move to Recycle Bin.')">
                                    <?= csrf_field() ?>
                                    <button class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/20 dark:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
