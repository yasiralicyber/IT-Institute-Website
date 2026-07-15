<?php
/** @var array $student @var string $program @var string $batch */
$verifyUrl = abs_url('/verify-id/' . $student['id_token']);
$qr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($verifyUrl);
$validUntil = date('d M Y', strtotime('+1 year'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card - <?= e($student['name']) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        @media print { .no-print{display:none} body{background:#fff;margin:0} @page{size:A4 landscape;margin:8mm} }
    </style>
</head>
<body class="bg-slate-100 p-6">
<div class="no-print mx-auto mb-5 flex max-w-2xl items-center justify-between">
    <a href="/students/<?= (int) $student['id'] ?>" class="text-sm font-bold text-brand-700">← Back</a>
    <div class="flex gap-2">
        <a href="/students/id-cards" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700">Bulk print</a>
        <a href="/students/<?= (int) $student['id'] ?>/id-card.pdf" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Download PDF (front + back)</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white">Print preview</button>
    </div>
</div>

<div class="mx-auto flex justify-center">
    <?= student_id_card($student, $program, $batch) ?>
</div>
<p class="no-print mx-auto mt-5 max-w-2xl text-center text-xs text-slate-400">Standard CR80 ID-card size (86 x 54 mm, landscape). In the print dialog choose 100% scale and your card stock, or use "Bulk print" to print many cards on one sheet.</p>
</body>
</html>
