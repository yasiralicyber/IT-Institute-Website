<?php /** @var array $pay @var int $balance @var array $allocations */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt <?= e($pay['receipt_no']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>@media print{.no-print{display:none}body{background:#fff}}</style>
</head>
<body class="bg-slate-100 p-4 sm:p-8">
<div class="mx-auto max-w-md">
    <div class="no-print mb-4 flex justify-between">
        <a href="/fees/<?= (int) $pay['user_id'] ?>" class="text-sm font-bold text-brand-700">← Back</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white">Print</button>
    </div>
    <div class="rounded-2xl border-2 border-dashed border-slate-300 bg-white p-6">
        <div class="flex items-center gap-3 border-b border-slate-200 pb-4">
            <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-white ring-1 ring-slate-200"><img src="<?= asset('img/logo.jpg') ?>" alt="" class="h-full w-full object-contain"></span>
            <div><p class="font-black text-brand-900">IT Training Institute</p><p class="text-xs text-slate-500">Kumber Maidan · Fee Receipt</p></div>
        </div>
        <div class="mt-4 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Receipt No.</span><span class="font-mono font-bold"><?= e($pay['receipt_no']) ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500">Date</span><span class="font-bold"><?= e(date('d M Y, g:i A', strtotime($pay['paid_at']))) ?></span></div>
            <div class="flex justify-between"><span class="text-slate-500">Student</span><span class="font-bold"><?= e($pay['student']) ?></span></div>
            <?php if ($pay['reg_no']): ?><div class="flex justify-between"><span class="text-slate-500">Reg No.</span><span class="font-bold"><?= e($pay['reg_no']) ?></span></div><?php endif; ?>
            <div class="flex justify-between"><span class="text-slate-500">Method</span><span class="font-bold"><?= ucfirst($pay['method']) ?><?= $pay['reference'] ? ' · ' . e($pay['reference']) : '' ?></span></div>
        </div>
        <?php if (!empty($allocations)): ?>
        <div class="mt-4 rounded-xl border border-slate-200 p-3 text-sm">
            <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-400">Applied to</p>
            <?php foreach ($allocations as $a): ?>
            <div class="flex justify-between py-0.5"><span class="text-slate-600"><?= e($a['title']) ?></span><span class="font-semibold"><?= pkr($a['amount']) ?></span></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="mt-4 flex items-center justify-between rounded-xl bg-brand-700 px-5 py-4 text-white">
            <span class="text-sm">Amount Received</span><span class="text-2xl font-black"><?= pkr($pay['amount']) ?></span>
        </div>
        <div class="mt-3 flex justify-between text-sm">
            <span class="text-slate-500">Remaining Balance</span>
            <span class="font-bold <?= $balance > 0 ? 'text-red-600' : 'text-emerald-600' ?>"><?= $balance > 0 ? pkr($balance) : 'Clear' ?></span>
        </div>
        <p class="mt-5 text-center text-xs text-slate-400">Thank you! Keep this receipt for your records.</p>
    </div>
</div>
</body>
</html>
