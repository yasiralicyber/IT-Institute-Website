<?php /** @var string $state @var string $batch */
$map = [
    'ok'           => ['check', 'You are marked present!', 'Your attendance for today has been recorded for ' . $batch . '.', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15'],
    'already'      => ['check', 'Already marked present', 'You have already checked in for today. No need to scan again.', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15'],
    'expired'      => ['clock', 'This code has expired', 'Check-in codes refresh every few seconds. Scan the live code currently on the class screen.', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15'],
    'not_in_batch' => ['lock', 'You are not in this batch', 'This attendance code is for a batch you are not enrolled in.', 'bg-red-100 text-red-600 dark:bg-red-500/15'],
    'invalid'      => ['shield', 'Invalid check-in code', 'This QR code is not valid. Please scan the current code shown in class.', 'bg-red-100 text-red-600 dark:bg-red-500/15'],
    'no_device'    => ['lock', 'No registered device', 'We could not identify your phone. Log out and log back in on your own mobile, then scan again.', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15'],
    'desktop'      => ['computer', 'Use your phone, not a computer', 'Self check-in must be done from your own registered mobile phone. Computers cannot mark attendance.', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15'],
    'shared'       => ['shield', 'Both accounts suspended', 'This phone is linked to more than one student account. Sharing a device is not allowed - every account on this phone has been suspended. Contact the institute.', 'bg-red-100 text-red-600 dark:bg-red-500/15'],
    'proxy'        => ['shield', 'Proxy attendance detected', 'This phone already marked another student present. Marking attendance for someone else is cheating - both accounts have been suspended. Contact the institute.', 'bg-red-100 text-red-600 dark:bg-red-500/15'],
];
[$icon, $title, $msg, $color] = $map[$state] ?? $map['invalid'];
$bad = in_array($state, ['shared', 'proxy'], true);
?>
<div class="mx-auto max-w-md text-center">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 dark:border-white/10 dark:bg-slate-900">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full <?= $color ?>"><?= icon($icon, 'h-10 w-10') ?></div>
        <h2 class="mt-5 text-2xl font-black text-slate-900 dark:text-white"><?= e($title) ?></h2>
        <p class="mt-2 text-slate-500"><?= e($msg) ?></p>
        <?php if ($bad): ?>
            <a href="/login" class="mt-6 inline-block rounded-xl bg-red-600 px-6 py-3 font-bold text-white hover:bg-red-700">Back to login</a>
        <?php else: ?>
            <a href="/dashboard" class="mt-6 inline-block rounded-xl bg-brand-600 px-6 py-3 font-bold text-white hover:bg-brand-700">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>
