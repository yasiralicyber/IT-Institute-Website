<?php /** @var array $set @var array $rows @var array $components */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit List - <?= e($set['title']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>@media print{.no-print{display:none}body{background:#fff}}</style>
</head>
<body class="bg-slate-100 p-4 sm:p-8">
<div class="mx-auto max-w-3xl">
    <div class="no-print mb-4 flex justify-between">
        <a href="/results/<?= (int) $set['id'] ?>" class="text-sm font-bold text-brand-700">← Back</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white">Print</button>
    </div>
    <div class="rounded-2xl border-2 border-slate-300 bg-white p-7">
        <div class="flex items-center gap-3 border-b-2 border-brand-700 pb-4">
            <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg ring-1 ring-slate-200"><img src="<?= asset('img/logo.jpg') ?>" alt="" class="h-full w-full object-contain"></span>
            <div><p class="text-lg font-black text-brand-900">IT Training Institute · Merit List</p><p class="text-xs text-slate-500"><?= e($set['title']) ?> · <?= e($set['batch'] ?: $set['course'] ?: '') ?></p></div>
        </div>
        <table class="mt-4 w-full text-sm">
            <thead class="border-y border-slate-200 text-left text-xs uppercase text-slate-400">
                <tr><th class="py-2">Rank</th><th class="py-2">Student</th><th class="py-2">Reg No.</th><th class="py-2 text-center">%</th><th class="py-2 text-center">Grade</th><th class="py-2 text-center">Result</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): $s = $row['student']; ?>
                <tr class="border-b border-slate-100">
                    <td class="py-2 font-black <?= $row['rank'] <= 3 ? 'text-brand-700' : 'text-slate-400' ?>"><?= (int) $row['rank'] ?></td>
                    <td class="py-2 font-semibold text-slate-700"><?= e($s['name']) ?></td>
                    <td class="py-2 text-slate-400"><?= e($s['reg_no'] ?: '-') ?></td>
                    <td class="py-2 text-center font-bold"><?= $row['percent'] ?></td>
                    <td class="py-2 text-center font-bold"><?= e($row['grade']) ?></td>
                    <td class="py-2 text-center"><span class="<?= $row['passed'] ? 'text-emerald-600' : 'text-red-600' ?> font-bold"><?= $row['passed'] ? 'Pass' : 'Fail' ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="mt-4 text-xs text-slate-400">Generated <?= date('d M Y') ?> · <?= count($rows) ?> students</p>
    </div>
</div>
</body>
</html>
