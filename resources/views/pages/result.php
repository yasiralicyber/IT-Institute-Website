<?php /** @var string $reg @var string $name @var ?array $rc @var ?string $error */ ?>

<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Check Your Result</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Enter your <strong>Registration / Roll Number</strong> and name to view your test marks and certificates from IT Training Institute and College.</p>
    </div>
</section>

<section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">

    <!-- Search form -->
    <form action="<?= url('/result') ?>" method="GET" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/60 sm:p-8">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">Registration / Roll Number</label>
                <input type="text" name="reg_no" value="<?= e($reg) ?>" required placeholder="e.g. ITTI-2026-0002"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">Student / Father Name</label>
                <input type="text" name="name" value="<?= e($name) ?>" required placeholder="Your name"
                       class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
        </div>
        <button class="mt-5 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700 sm:w-auto sm:px-10">Show My Result</button>
        <p class="mt-3 text-sm text-slate-500">Your registration number is printed on your student ID card.</p>
    </form>

    <?php if ($error !== null): ?>
    <div class="mt-6 rounded-2xl border border-red-300 bg-red-50 p-5 text-center dark:border-red-500/40 dark:bg-red-500/10">
        <p class="font-bold text-red-700 dark:text-red-300"><?= e($error) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($rc !== null): $s = $rc['student']; ?>
    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-white/10 dark:bg-slate-900">
        <!-- Header -->
        <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-brand-700 to-brand-900 p-6 text-white dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-brand-200">Result Card</p>
                <h2 class="mt-1 text-2xl font-black"><?= e($s['name']) ?></h2>
                <p class="text-sm text-brand-100">
                    <?php if ($s['father_name']): ?>S/D of <?= e($s['father_name']) ?> &middot; <?php endif; ?>
                    Reg No: <span class="font-mono font-bold"><?= e($s['reg_no']) ?></span>
                    <?php if ($rc['program']): ?><br><?= e($rc['program']) ?><?php endif; ?>
                </p>
            </div>
            <button onclick="window.print()" class="no-print self-start rounded-xl bg-white/15 px-4 py-2 text-sm font-bold text-white hover:bg-white/25">Print</button>
        </div>

        <div class="p-6">
            <!-- Test marks -->
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Test Marks</h3>
            <?php if (empty($rc['marks'])): ?>
                <p class="rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-500 dark:bg-white/5">No test marks have been published yet.</p>
            <?php else: ?>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                        <tr><th class="px-4 py-3">Test</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Marks</th><th class="px-4 py-3">Result</th><th class="px-4 py-3">Date</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                        <?php
                        $sumObt = 0; $sumTot = 0;
                        foreach ($rc['marks'] as $t):
                            $obt = (float) $t['marks_obtained']; $tot = (float) $t['total_marks'];
                            $sumObt += $obt; $sumTot += $tot;
                            $pct = $tot > 0 ? round($obt / $tot * 100, 1) : 0;
                            $grade = $pct >= 80 ? 'A+' : ($pct >= 70 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : ($pct >= 40 ? 'D' : 'F'))));
                            $pass = $pct >= 50;
                            $fmt = fn($v) => rtrim(rtrim((string) $v, '0'), '.'); ?>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($t['test_name']) ?><?php if ($t['remarks']): ?><p class="text-xs font-normal text-slate-400"><?= e($t['remarks']) ?></p><?php endif; ?></td>
                            <td class="px-4 py-3 text-slate-500"><?= e($t['subject'] ?: '-') ?></td>
                            <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900 dark:text-white"><?= e($fmt($obt)) ?> / <?= e($fmt($tot)) ?></td>
                            <td class="whitespace-nowrap px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $pass ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' ?>"><?= $pct ?>% &middot; <?= $grade ?></span></td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500"><?= e($t['test_date'] ? date('d M Y', strtotime($t['test_date'])) : '-') ?></td>
                        </tr>
                        <?php endforeach;
                        $overall = $sumTot > 0 ? round($sumObt / $sumTot * 100, 1) : 0; ?>
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-white/5">
                        <tr class="font-bold text-slate-900 dark:text-white">
                            <td class="px-4 py-3" colspan="2">Overall</td>
                            <td class="px-4 py-3"><?= e(rtrim(rtrim((string) $sumObt, '0'), '.')) ?> / <?= e(rtrim(rtrim((string) $sumTot, '0'), '.')) ?></td>
                            <td class="px-4 py-3" colspan="2"><span class="rounded-full px-2.5 py-1 text-xs <?= $overall >= 50 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' ?>"><?= $overall ?>% &middot; <?= $overall >= 50 ? 'PASS' : 'FAIL' ?></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            <!-- Certificates -->
            <?php if (!empty($rc['certs'])): ?>
            <h3 class="mb-3 mt-8 text-sm font-bold uppercase tracking-wider text-slate-400">Certificates Earned</h3>
            <div class="grid gap-3 sm:grid-cols-2">
                <?php foreach ($rc['certs'] as $cert): ?>
                <div class="flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white"><?= e($cert['course_title']) ?></p>
                        <p class="text-xs text-slate-500"><?= ucfirst($cert['type']) ?> &middot; <span class="font-mono"><?= e($cert['credential_id']) ?></span></p>
                    </div>
                    <a href="<?= url('/certificate/' . urlencode($cert['credential_id']) . '/pdf') ?>" class="no-print rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">PDF</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
<style>@media print{.no-print{display:none}nav,footer{display:none}}</style>
