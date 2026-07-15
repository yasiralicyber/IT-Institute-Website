<?php
/** @var array $batch @var string $token @var string $date @var string $expires @var int $ttl */
$checkinUrl = abs_url('/checkin/' . $token);
$qrBase = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=';
$qr = $qrBase . urlencode($checkinUrl);
?>
<a href="/attendance/<?= (int) $batch['id'] ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back</a>

<div class="mx-auto mt-6 max-w-md rounded-3xl border border-slate-200 bg-white p-8 text-center dark:border-white/10 dark:bg-slate-900">
    <h2 class="text-xl font-black text-slate-900 dark:text-white"><?= e($batch['name']) ?></h2>
    <p class="text-sm text-slate-500"><?= e(date('l, d M Y', strtotime($date))) ?></p>

    <div class="mt-6 inline-block rounded-2xl bg-white p-4 ring-1 ring-slate-200">
        <img id="qrImg" src="<?= e($qr) ?>" alt="Attendance QR" class="h-64 w-64">
    </div>

    <div class="mt-3 flex items-center justify-center gap-2 text-xs font-bold">
        <span class="inline-flex h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500"></span>
        <span class="text-emerald-600">Live - code refreshes every <?= (int) $ttl ?>s.</span>
        <span id="countdown" class="rounded bg-slate-100 px-2 py-0.5 text-slate-600 dark:bg-white/10 dark:text-slate-300"><?= (int) $ttl ?></span>
    </div>

    <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">
        Students scan this with their phone <strong>while logged in on their own registered mobile</strong> to mark themselves <strong>present</strong>. A forwarded screenshot will not work - each code dies in <?= (int) $ttl ?> seconds.
    </p>

    <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-left text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
        <strong>Anti-cheat is on:</strong> one phone = one account. If two accounts are used on the same phone, or one phone scans for two students, <strong>both accounts are suspended automatically</strong>. Desktops cannot self-check-in.
    </div>

    <div class="mt-5 flex flex-wrap justify-center gap-3">
        <button onclick="window.print()" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:text-white">Print QR</button>
        <form method="post" action="/attendance/<?= (int) $batch['id'] ?>/finalize" onsubmit="return confirm('Close attendance for today? Every enrolled student who did NOT scan will be marked ABSENT.');">
            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
            <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Close session &amp; mark absentees</button>
        </form>
    </div>
</div>

<script>
(function () {
    var batchId = <?= (int) $batch['id'] ?>;
    var ttl = <?= (int) $ttl ?>;
    var qrBase = <?= json_encode($qrBase) ?>;
    var origin = location.protocol + '//' + location.host;
    var img = document.getElementById('qrImg');
    var cd = document.getElementById('countdown');
    var left = ttl;

    function rotate() {
        fetch('/attendance/' + batchId + '/qr/rotate', { headers: { 'X-Requested-With': 'fetch' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.token) return;
                var url = origin + '/checkin/' + d.token;
                img.src = qrBase + encodeURIComponent(url);
                left = d.ttl || ttl;
            })
            .catch(function () { /* keep last QR on transient error */ });
    }
    setInterval(function () {
        left -= 1;
        if (cd) cd.textContent = left > 0 ? left : 0;
        if (left <= 0) { rotate(); left = ttl; }
    }, 1000);
})();
</script>
