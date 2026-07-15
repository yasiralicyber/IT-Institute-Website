<?php /** @var array $user @var array $courses @var array $pending @var array $certs */ ?>

<div class="mb-6 rounded-2xl bg-gradient-to-r from-brand-700 to-brand-900 p-5 text-white sm:p-8">
    <h2 class="text-xl font-black sm:text-2xl">Welcome back, <?= e(explode(' ', $user['name'])[0]) ?>!</h2>
    <p class="mt-1 text-sm text-brand-100 sm:text-base">Continue learning, track your progress, and earn your certificates.</p>
    <div class="mt-4 flex flex-wrap gap-3">
        <a href="<?= url('/my-id-card.pdf') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-brand-800 hover:bg-brand-50"><?= icon('download','h-4 w-4') ?> Download My ID Card</a>
        <a href="<?= url('/achievements') ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2 text-sm font-bold text-white hover:bg-white/25"><?= icon('trophy','h-4 w-4') ?> My Certificates</a>
    </div>
    <?php if (empty($user['email_verified_at'])): ?>
    <p class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white/15 px-3 py-1.5 text-sm"><?= icon('mail','h-4 w-4') ?> Please verify your email to secure your account.</p>
    <?php endif; ?>
</div>

<?php if (!empty($revisions)): ?>
<div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
    <h3 class="flex items-center gap-2 font-black text-amber-800 dark:text-amber-300"><?= icon('refresh','h-5 w-5') ?> Revision due</h3>
    <p class="text-sm text-slate-600 dark:text-slate-300">Keep your knowledge fresh - these chapter tests are due for a revision:</p>
    <div class="mt-3 flex flex-wrap gap-2">
        <?php foreach ($revisions as $rv): ?>
        <a href="<?= url('/learn/' . $rv['slug'] . '/test/' . (int) $rv['chapter_id']) ?>" class="rounded-xl border border-amber-300 bg-white px-4 py-2 text-sm font-bold text-amber-700 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-slate-900 dark:text-amber-300"><?= e($rv['course']) ?>: <?= e($rv['chapter']) ?> →</a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recommendations -->
<?php if (!empty($recs)): $rt = ['brand'=>'border-brand-300 bg-brand-50 dark:border-brand-500/30 dark:bg-brand-500/10','amber'=>'border-amber-300 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10','gold'=>'border-gold-300 bg-gold-50 dark:border-gold-500/30 dark:bg-gold-500/10']; ?>
<div class="mb-6">
    <h3 class="mb-3 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"><span class="text-gold-500"><?= icon('sparkles','h-5 w-5') ?></span> Recommended for you</h3>
    <div class="grid gap-3 sm:grid-cols-2">
        <?php foreach ($recs as $r): ?>
        <a href="<?= url($r['href']) ?>" class="flex items-center gap-3 rounded-2xl border p-4 transition hover:shadow-md <?= $rt[$r['tone']] ?? $rt['brand'] ?>">
            <span class="inline-flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-white/70 text-brand-600 dark:bg-white/10 dark:text-brand-300"><?= icon($r['icon'], 'h-5 w-5') ?></span>
            <span class="flex-1 text-sm font-semibold text-slate-700 dark:text-slate-200"><?= e($r['text']) ?></span>
            <svg class="h-4 w-4 flex-none text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <?php
    $cards = [
        ['Enrolled Courses', count($courses), 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300'],
        ['Certificates', count($certs), 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300'],
        ['Pending Requests', count(array_filter($pending, fn($p) => $p['status'] === 'pending')), 'bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300'],
    ];
    foreach ($cards as $c): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <p class="text-sm text-slate-500"><?= e($c[0]) ?></p>
        <p class="mt-1 inline-flex rounded-lg px-3 py-1 text-3xl font-black <?= $c[2] ?>"><?= $c[1] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<!-- Enrolled courses -->
<div class="mb-8">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Continue Learning</h3>
        <a href="<?= url('/courses') ?>" class="text-sm font-bold text-brand-600 hover:underline">Browse more →</a>
    </div>
    <?php if (empty($courses)): ?>
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-white/10 dark:bg-slate-900">
            <p class="text-slate-500">You're not enrolled in any course yet.</p>
            <a href="<?= url('/courses') ?>" class="mt-4 inline-block rounded-xl bg-brand-600 px-5 py-2.5 font-bold text-white hover:bg-brand-700">Explore Courses</a>
        </div>
    <?php else: ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($courses as $c): ?>
            <a href="<?= url('/learn/' . $c['slug']) ?>" class="group rounded-2xl border border-slate-200 bg-white p-5 transition hover:shadow-lg dark:border-white/10 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-slate-900 dark:text-white"><?= e($c['title']) ?></h4>
                    <span class="text-sm font-bold text-brand-600"><?= $c['progress'] ?>%</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10">
                    <div class="h-full rounded-full bg-brand-600" style="width: <?= $c['progress'] ?>%"></div>
                </div>
                <p class="mt-2 text-xs text-slate-500"><?= $c['done'] ?> / <?= $c['total'] ?> lessons completed · <span class="font-semibold text-brand-600 group-hover:underline">Resume →</span></p>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Purchase requests -->
<?php if (!empty($pending)): ?>
<div class="mb-8">
    <h3 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Enrollment Requests</h3>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-4 py-3 sm:px-5">Course</th><th class="px-4 py-3 sm:px-5">Date</th><th class="px-4 py-3 sm:px-5">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($pending as $p):
                    $badge = ['pending' => 'bg-amber-100 text-amber-700','approved' => 'bg-emerald-100 text-emerald-700','declined' => 'bg-red-100 text-red-700'][$p['status']] ?? 'bg-slate-100 text-slate-600'; ?>
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200 sm:px-5"><?= e($p['title']) ?></td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500 sm:px-5"><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
                    <td class="px-4 py-3 sm:px-5"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $badge ?>"><?= ucfirst($p['status']) ?></span>
                        <?php if ($p['status'] === 'declined' && $p['admin_note']): ?><p class="mt-1 text-xs text-red-500"><?= e($p['admin_note']) ?></p><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Physical / in-class test marks -->
<?php if (!empty($testMarks)): ?>
<div class="mb-8">
    <h3 class="mb-3 flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"><span class="text-gold-500"><?= icon('star','h-5 w-5') ?></span> Test Marks &amp; Results</h3>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-4 py-3 sm:px-5">Test</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Marks</th><th class="px-4 py-3">Result</th><th class="px-4 py-3">Date</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($testMarks as $t):
                    $total = (float) $t['total_marks']; $obt = (float) $t['marks_obtained'];
                    $pct = $total > 0 ? round($obt / $total * 100, 1) : 0;
                    $grade = $pct >= 80 ? 'A+' : ($pct >= 70 ? 'A' : ($pct >= 60 ? 'B' : ($pct >= 50 ? 'C' : ($pct >= 40 ? 'D' : 'F'))));
                    $pass = $pct >= 50;
                    $fmt = fn($v) => rtrim(rtrim((string) $v, '0'), '.'); ?>
                <tr>
                    <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200 sm:px-5">
                        <?= e($t['test_name']) ?>
                        <?php if ($t['remarks']): ?><p class="text-xs font-normal text-slate-400"><?= e($t['remarks']) ?></p><?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-slate-500"><?= e($t['subject'] ?: '-') ?></td>
                    <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900 dark:text-white"><?= e($fmt($obt)) ?> / <?= e($fmt($total)) ?></td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $pass ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300' ?>"><?= $pct ?>% · <?= $grade ?></span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500"><?= e($t['test_date'] ? date('d M Y', strtotime($t['test_date'])) : '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Certificates -->
<?php if (!empty($certs)): ?>
<div>
    <h3 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">My Certificates</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($certs as $cert): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gold-100 text-gold-600 dark:bg-gold-500/15"><?= icon('trophy','h-6 w-6') ?></span>
            <p class="mt-2 font-bold text-slate-900 dark:text-white"><?= e($cert['course_title']) ?></p>
            <p class="text-xs text-slate-500"><?= ucfirst($cert['type']) ?> certificate</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <a href="<?= url('/certificate/' . urlencode($cert['credential_id']) . '/pdf') ?>" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700"><?= icon('download','h-4 w-4') ?> Download PDF</a>
                <a href="<?= url('/verify?id=' . urlencode($cert['credential_id'])) ?>" class="text-sm font-bold text-brand-600 hover:underline">View / Verify →</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
