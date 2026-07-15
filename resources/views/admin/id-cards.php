<?php /** @var array $cards @var string $batchName @var array $batches @var int $batchId */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk ID Cards<?= $batchName ? ' - ' . e($batchName) : '' ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        @media print {
            .no-print{display:none}
            body{background:#fff;margin:0}
            @page{ size:A4 landscape; margin:6mm; }
            .sheet{ gap:4mm; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6">
<div class="no-print mx-auto mb-5 flex max-w-5xl flex-wrap items-center justify-between gap-3">
    <a href="/students" class="text-sm font-bold text-brand-700">← Students</a>
    <form method="get" action="/students/id-cards" class="flex items-center gap-2">
        <label class="text-sm font-semibold text-slate-600">Batch</label>
        <select name="batch_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 px-3 py-2 text-sm">
            <option value="0">All students</option>
            <?php foreach ($batches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $batchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?> (<?= e($b['course']) ?>)</option><?php endforeach; ?>
        </select>
    </form>
    <div class="flex items-center gap-3">
        <span class="text-sm text-slate-500"><?= count($cards) ?> card(s)</span>
        <a href="/students/id-cards.pdf<?= $batchId ? '?batch_id=' . (int) $batchId : '' ?>" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Download A4 PDF</a>
        <button onclick="window.print()" class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-bold text-slate-700">Print preview</button>
    </div>
</div>
<p class="no-print mx-auto mb-4 max-w-5xl text-center text-xs text-slate-400">The <b>Download A4 PDF</b> packs the official cards (front + back) onto A4 pages with cut guides &mdash; print at 100% scale on 300gsm card stock and cut along the lines. 5 students per sheet.</p>

<?php if (empty($cards)): ?>
    <p class="no-print text-center text-slate-500">No students to print.</p>
<?php else: ?>
<div class="sheet mx-auto flex max-w-5xl flex-wrap justify-center gap-4">
    <?php foreach ($cards as $c): ?>
        <?= student_id_card($c['student'], $c['program'], $c['batch']) ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</body>
</html>
