<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Verify a Certificate</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Enter the <strong>certificate number</strong> or credential ID printed on the certificate (or scan its QR code) to confirm it was issued by IT Training Institute — works for both online and physical certificates.</p>
    </div>
</section>

<section class="mx-auto max-w-xl px-4 py-16 sm:px-6">
    <?php if (($result ?? null) !== null): ?>
        <?php if (!empty($result['valid']) && ($result['kind'] ?? '') === 'online'): $c = $result['cert']; ?>
        <div class="mb-6 overflow-hidden rounded-2xl border border-emerald-300 bg-white shadow-lg dark:border-emerald-500/40 dark:bg-slate-900">
            <div class="flex items-center gap-3 bg-emerald-500 px-6 py-4 text-white">
                <?= icon('check','h-6 w-6') ?><p class="font-black">Valid Certificate</p>
            </div>
            <div class="space-y-3 p-6 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Student</span><span class="font-bold text-slate-900 dark:text-white"><?= e($c['student']) ?></span></div>
                <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Course</span><span class="font-bold text-slate-900 dark:text-white"><?= e($c['course']) ?></span></div>
                <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Type</span><span class="font-bold text-slate-900 dark:text-white"><?= ucfirst($c['type']) ?> completion</span></div>
                <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Issued</span><span class="font-bold text-slate-900 dark:text-white"><?= e(date('d M Y', strtotime($c['issued_at']))) ?></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Credential</span><span class="font-mono font-bold text-brand-700 dark:text-brand-300"><?= e($c['credential_id']) ?></span></div>
                <a href="<?= url('/certificate/' . urlencode($c['credential_id'])) ?>" class="mt-3 block rounded-xl bg-brand-600 py-3 text-center font-bold text-white hover:bg-brand-700">View Certificate</a>
            </div>
        </div>

        <?php elseif (!empty($result['valid']) && ($result['kind'] ?? '') === 'physical'): $m = $result['manual']; ?>
        <div class="mb-6 overflow-hidden rounded-2xl border border-emerald-300 bg-white shadow-lg dark:border-emerald-500/40 dark:bg-slate-900">
            <div class="flex items-center gap-3 bg-emerald-500 px-6 py-4 text-white">
                <?= icon('shield','h-6 w-6') ?><p class="font-black">Genuine — Issued by the Institute</p>
            </div>
            <div class="space-y-3 p-6 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Name</span><span class="font-bold text-slate-900 dark:text-white"><?= e($m['student_name']) ?></span></div>
                <?php if ($m['father_name']): ?><div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Son / Daughter of</span><span class="font-bold text-slate-900 dark:text-white"><?= e($m['father_name']) ?></span></div><?php endif; ?>
                <?php if ($m['course']): ?><div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Course / Trade</span><span class="font-bold text-slate-900 dark:text-white"><?= e($m['course']) ?></span></div><?php endif; ?>
                <?php if ($m['grade']): ?><div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Grade</span><span class="font-bold text-slate-900 dark:text-white"><?= e($m['grade']) ?></span></div><?php endif; ?>
                <?php if ($m['from_date'] || $m['to_date']): ?><div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Duration</span><span class="font-bold text-slate-900 dark:text-white"><?= e($m['from_date'] ? date('d M Y', strtotime($m['from_date'])) : '?') ?> — <?= e($m['to_date'] ? date('d M Y', strtotime($m['to_date'])) : '?') ?></span></div><?php endif; ?>
                <?php if ($m['issue_date']): ?><div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><span class="text-slate-500">Issued</span><span class="font-bold text-slate-900 dark:text-white"><?= e(date('d M Y', strtotime($m['issue_date']))) ?></span></div><?php endif; ?>
                <div class="flex justify-between"><span class="text-slate-500">Certificate No.</span><span class="font-mono font-bold text-brand-700 dark:text-brand-300"><?= e($m['cert_no']) ?></span></div>
            </div>
        </div>

        <?php elseif (!empty($result['revoked'])): $m = $result['manual']; ?>
        <div class="mb-6 rounded-2xl border border-red-300 bg-red-50 p-6 text-center dark:border-red-500/40 dark:bg-red-500/10">
            <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-500 text-white"><?= icon('lock','h-6 w-6') ?></span>
            <p class="mt-3 font-black text-red-700 dark:text-red-300">This certificate has been REVOKED</p>
            <p class="mt-1 text-sm text-red-600 dark:text-red-300/80">Certificate <span class="font-mono font-bold"><?= e($m['cert_no']) ?></span> (<?= e($m['student_name']) ?>) was recorded but has since been revoked by the institute. It should not be accepted as valid.</p>
        </div>

        <?php else: ?>
        <div class="mb-6 rounded-2xl border border-red-300 bg-red-50 p-6 text-center dark:border-red-500/40 dark:bg-red-500/10">
            <span class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-500 text-white text-2xl font-black">!</span>
            <p class="mt-3 font-black text-red-700 dark:text-red-300">No certificate found</p>
            <p class="mt-1 text-sm text-red-600 dark:text-red-300/80">We couldn't find any certificate with the number "<?= e($query) ?>". Please check the number and try again.</p>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <form action="<?= url('/verify') ?>" method="GET" class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-white/10 dark:bg-slate-900/60">
        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">Credential ID</label>
        <input type="text" name="id" value="<?= e($query ?? '') ?>" placeholder="e.g. ITTI-2026-AB12CD34"
               class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="mt-5 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700">Verify Certificate</button>
        <p class="mt-4 text-center text-sm text-slate-500">Enter the credential ID printed on the certificate, or scan its QR code.</p>
    </form>
</section>
