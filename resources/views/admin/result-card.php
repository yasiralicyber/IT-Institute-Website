<?php /** @var array $set @var array $student @var array $components @var array $r */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Card - <?= e($student['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>@media print{.no-print{display:none}body{background:#fff}}</style>
</head>
<body class="bg-slate-100 p-4 sm:p-8">
<div class="mx-auto max-w-lg">
    <div class="no-print mb-4 flex justify-between">
        <a href="/results/<?= (int) $set['id'] ?>" class="text-sm font-bold text-brand-700">← Back</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white">Print</button>
    </div>
    <div class="rounded-2xl border-2 border-slate-300 bg-white p-7">
        <div class="flex items-center gap-3 border-b-2 border-brand-700 pb-4">
            <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg ring-1 ring-slate-200"><img src="<?= asset('img/logo.jpg') ?>" alt="" class="h-full w-full object-contain"></span>
            <div><p class="text-lg font-black text-brand-900">IT Training Institute</p><p class="text-xs text-slate-500">Kumber Maidan · Statement of Result</p></div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
            <div><span class="text-slate-400">Student</span><p class="font-bold"><?= e($student['name']) ?></p></div>
            <div><span class="text-slate-400">Reg No.</span><p class="font-bold"><?= e($student['reg_no'] ?: '-') ?></p></div>
            <div class="col-span-2"><span class="text-slate-400">Examination</span><p class="font-bold"><?= e($set['title']) ?></p></div>
        </div>
        <table class="mt-4 w-full text-sm">
            <thead class="border-y border-slate-200 text-left text-xs uppercase text-slate-400">
                <tr><th class="py-2">Component</th><th class="py-2 text-center">Max</th><th class="py-2 text-center">Obtained</th></tr>
            </thead>
            <tbody>
            <?php foreach ($components as $c): $cell = $r['cells'][$c['id']] ?? ['obtained'=>0,'max'=>$c['max_marks']]; ?>
                <tr class="border-b border-slate-100"><td class="py-2 font-semibold text-slate-700"><?= e($c['label']) ?></td><td class="py-2 text-center text-slate-500"><?= (int) $cell['max'] ?></td><td class="py-2 text-center font-bold"><?= rtrim(rtrim(number_format((float) $cell['obtained'], 2), '0'), '.') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Percentage</p><p class="text-xl font-black text-brand-800"><?= $r['percent'] ?>%</p></div>
            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Grade</p><p class="text-xl font-black text-brand-800"><?= e($r['grade']) ?></p></div>
            <div class="rounded-xl <?= $r['passed'] ? 'bg-emerald-50' : 'bg-red-50' ?> p-3"><p class="text-xs text-slate-400">Result</p><p class="text-xl font-black <?= $r['passed'] ? 'text-emerald-600' : 'text-red-600' ?>"><?= $r['passed'] ? 'PASS' : 'FAIL' ?></p></div>
        </div>
        <?php if ($set['status'] !== 'approved'): ?><p class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-center text-xs font-bold text-amber-700">PROVISIONAL - not yet approved</p><?php endif; ?>
        <div class="mt-6 flex justify-between text-xs text-slate-400">
            <span>Issued <?= date('d M Y') ?></span><span>Controller of Examinations</span>
        </div>
    </div>
</div>
</body>
</html>
