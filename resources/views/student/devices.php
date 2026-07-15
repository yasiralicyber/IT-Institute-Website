<?php /** @var array $devices */ ?>
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
    For content security, your account is limited to <strong>one mobile + one computer/laptop</strong>. Remove an old device here to free a slot before logging in on a new one. Sharing your login will suspend your account.
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <?php foreach (['mobile' => 'Mobile', 'desktop' => 'Computer / Laptop'] as $type => $label):
        $d = array_values(array_filter($devices, fn($x) => $x['device_type'] === $type))[0] ?? null; ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <p class="text-sm font-bold uppercase tracking-wider text-slate-400"><?= $label ?> slot</p>
        <?php if ($d): ?>
            <p class="mt-3 text-lg font-bold text-slate-900 dark:text-white"><?= e($d['label']) ?></p>
            <p class="text-xs text-slate-500">Last active <?= e(date('d M Y, g:i A', strtotime($d['last_seen']))) ?></p>
            <form action="<?= url('/devices/remove') ?>" method="POST" onsubmit="return confirm('Remove this device? You will be logged out on it.')" class="mt-4">
                <?= csrf_field() ?>
                <input type="hidden" name="device_id" value="<?= (int) $d['id'] ?>">
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:hover:bg-red-500/10">Remove device</button>
            </form>
        <?php else: ?>
            <p class="mt-3 text-slate-400">No device registered - this slot is free.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
