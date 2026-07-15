<?php /** @var array $batches @var array $rooms @var array $events @var array $notices @var array $att @var array $alerts @var array $stats */ ?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="45">
    <title>Operations Center - ITTI</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-brand-900 to-ink p-6 text-white">
    <header class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-xl bg-white p-1"><img src="<?= asset('img/logo.jpg') ?>" alt="" class="h-full w-full object-contain"></span>
            <div><p class="text-xl font-black">Operations Control Center</p><p class="text-xs uppercase tracking-widest text-gold-300">IT Training Institute · Kumber Maidan</p></div>
        </div>
        <div class="text-right"><p id="clock" class="text-2xl font-black tabular-nums"></p><p class="text-xs text-brand-200">Auto-refreshes every 45s · <a href="/" class="underline">Exit</a></p></div>
    </header>

    <!-- top stats -->
    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6">
        <?php
        $cards = [
            ['Students', $stats['students'], 'bg-white/5'],
            ['Active Enrolments', $stats['enrollments'], 'bg-white/5'],
            ['Today Present', $att['present'], 'bg-emerald-500/15'],
            ['Today Absent', $att['absent'], 'bg-red-500/15'],
            ["Today's Fees", pkr($stats['revenue']), 'bg-gold-500/15'],
            ['Open Classes', count($batches), 'bg-white/5'],
        ];
        foreach ($cards as [$l, $v, $bg]): ?>
        <div class="rounded-2xl <?= $bg ?> p-4 ring-1 ring-white/10"><p class="text-3xl font-black"><?= is_int($v) ? $v : e($v) ?></p><p class="text-xs text-brand-200"><?= e($l) ?></p></div>
        <?php endforeach; ?>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <!-- classes -->
        <div class="lg:col-span-2 rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
            <h2 class="mb-3 text-lg font-black">Active Classes</h2>
            <?php if (empty($batches)): ?><p class="text-brand-200">No active batches.</p>
            <?php else: foreach ($batches as $b): ?>
            <div class="mb-2 flex items-center justify-between rounded-xl bg-white/5 px-4 py-2.5">
                <div><p class="font-bold"><?= e($b['name']) ?></p><p class="text-xs text-brand-200"><?= e($b['teacher'] ?: 'No teacher') ?> · <?= e($b['room'] ?: 'No room') ?> · <?= e($b['schedule'] ?: '-') ?></p></div>
                <span class="rounded-full bg-white/10 px-3 py-1 text-sm font-bold"><?= (int) $b['students'] ?> </span>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- alerts -->
        <div class="space-y-4">
            <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                <h2 class="mb-3 text-lg font-black">Needs Attention</h2>
                <?php
                $al = [['Payment approvals', $alerts['approvals'], '/purchases'], ['New admissions', $alerts['admissions'], '/admissions'], ['Reviews to approve', $alerts['reviews'], '/reviews'], ['Projects to review', $alerts['projects'], '/projects']];
                foreach ($al as [$l, $n, $href]): ?>
                <a href="<?= e($href) ?>" class="mb-1.5 flex items-center justify-between rounded-xl px-4 py-2 <?= $n > 0 ? 'bg-amber-500/20' : 'bg-white/5' ?>">
                    <span class="text-sm"><?= e($l) ?></span><span class="rounded-full <?= $n>0?'bg-amber-400 text-brand-950':'bg-white/10' ?> px-2.5 py-0.5 text-sm font-black"><?= $n ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                <h2 class="mb-3 text-lg font-black">Rooms</h2>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($rooms as $r): ?><span class="rounded-lg bg-white/10 px-3 py-1.5 text-sm"><?= e($r['name']) ?> <span class="text-brand-300">(<?= (int) $r['capacity'] ?>)</span></span><?php endforeach; ?>
                    <?php if (empty($rooms)): ?><span class="text-brand-200 text-sm">No rooms added.</span><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
            <h2 class="mb-3 text-lg font-black">Upcoming Events</h2>
            <?php if (empty($events)): ?><p class="text-brand-200">No events.</p><?php else: foreach ($events as $e): ?>
            <p class="mb-1.5 flex justify-between rounded-xl bg-white/5 px-4 py-2"><span class="font-semibold"><?= e($e['title']) ?></span><span class="text-xs text-gold-300"><?= e($e['event_date'] ?: ucfirst($e['type'])) ?></span></p>
            <?php endforeach; endif; ?>
        </div>
        <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
            <h2 class="mb-3 text-lg font-black">Notices</h2>
            <?php if (empty($notices)): ?><p class="text-brand-200">No notices.</p><?php else: foreach ($notices as $n): ?>
            <p class="mb-1.5 rounded-xl bg-white/5 px-4 py-2 font-semibold"><?= e($n['title']) ?></p>
            <?php endforeach; endif; ?>
        </div>
    </div>
<script>function clk(){const n=new Date();document.getElementById('clock').textContent=n.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});}clk();setInterval(clk,1000);</script>
</body>
</html>
