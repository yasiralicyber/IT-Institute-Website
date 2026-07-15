<?php
/** @var array $course @var array $chapter @var array $quiz @var array $questions @var int $attemptsUsed @var bool $passed */
$attemptsLeft = (int) $quiz['max_attempts'] - $attemptsUsed;
$secure = !empty($quiz['secure_exam']);
$maxViol = (int) ($quiz['max_violations'] ?? 3);
$submitUrl = url('/learn/' . $course['slug'] . '/test/' . $chapter['id']);
?>
<div class="mx-auto max-w-3xl"<?= $secure ? ' id="examWrap"' : '' ?>>
    <a href="<?= url('/learn/' . $course['slug']) ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back to course</a>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white"><?= e($chapter['title']) ?> - Test</h2>
                <p class="mt-1 text-sm text-slate-500">Pass mark: <strong><?= (int) $quiz['pass_percent'] ?>%</strong> · <?= count($questions) ?> questions</p>
            </div>
            <span class="rounded-full bg-brand-50 px-3 py-1.5 text-sm font-bold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"><?= max(0, $attemptsLeft) ?> attempt<?= $attemptsLeft === 1 ? '' : 's' ?> left</span>
        </div>
        <?php if ($passed): ?>
        <p class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">✓ You already passed this chapter. You may retake it to improve, or continue to the next chapter.</p>
        <?php endif; ?>
        <?php if ($secure): ?>
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            <strong>Secure exam.</strong> This test runs in locked full-screen mode. Leaving full-screen, switching tabs/windows, or attempting to copy is logged. After <strong><?= $maxViol ?></strong> violations the exam auto-submits. Copy, paste and right-click are disabled.
        </div>
        <?php endif; ?>
    </div>

    <?php if ($secure): ?>
    <!-- Start gate -->
    <div id="examGate" class="mt-6 rounded-2xl border border-brand-200 bg-brand-50 p-8 text-center dark:border-brand-500/30 dark:bg-brand-500/10">
        <h3 class="text-lg font-black text-brand-800 dark:text-brand-300">Ready to begin?</h3>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">The exam opens in full screen. Do not leave the window until you submit.</p>
        <button type="button" id="startExam" class="mt-4 rounded-xl bg-brand-600 px-8 py-3 font-bold text-white hover:bg-brand-700">Start Secure Exam</button>
    </div>
    <div id="violBanner" class="mt-4 hidden rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 dark:bg-red-500/10"></div>
    <?php endif; ?>

    <form id="examForm" action="<?= $submitUrl ?>" method="POST" class="mt-6 space-y-5 <?= $secure ? 'hidden' : '' ?>"<?= $secure ? ' style="user-select:none"' : '' ?>>
        <?= csrf_field() ?>
        <?php foreach ($questions as $i => $q): $opts = json_decode($q['options'], true) ?: []; ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <p class="font-bold text-slate-900 dark:text-white"><?= ($i + 1) . '. ' . e($q['question']) ?></p>
            <div class="mt-4 space-y-2">
                <?php foreach ($opts as $oi => $opt): ?>
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 hover:border-brand-400 dark:border-white/10 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:has-[:checked]:bg-brand-500/10">
                    <input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= $oi ?>" required class="text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-slate-700 dark:text-slate-200"><?= e($opt) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white shadow-lg shadow-brand-900/20 hover:bg-brand-700">Submit Test</button>
    </form>
</div>

<?php if ($secure): ?>
<script>
(function () {
    var MAX = <?= $maxViol ?>;
    var violUrl = <?= json_encode($submitUrl . '/violation') ?>;
    var form = document.getElementById('examForm');
    var gate = document.getElementById('examGate');
    var banner = document.getElementById('violBanner');
    var started = false, submitting = false, count = 0;

    function enterFs() { var el = document.documentElement; (el.requestFullscreen || el.webkitRequestFullscreen || function(){}).call(el); }
    function showBanner(msg) { banner.textContent = msg; banner.classList.remove('hidden'); }

    function logViolation(kind) {
        if (!started || submitting) return;
        var body = new URLSearchParams({ kind: kind });
        fetch(violUrl, { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'fetch'}, body: body, keepalive: true })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                count = d.count || count + 1;
                if (count >= (d.max || MAX)) {
                    submitting = true;
                    showBanner('Too many violations (' + count + '). Submitting your exam now.');
                    setTimeout(function () { form.submit(); }, 600);
                } else {
                    showBanner('Warning ' + count + '/' + (d.max || MAX) + ': leaving the exam is recorded. ' + ((d.max||MAX) - count) + ' left before auto-submit.');
                }
            })
            .catch(function () {});
    }

    document.getElementById('startExam').addEventListener('click', function () {
        started = true;
        gate.classList.add('hidden');
        form.classList.remove('hidden');
        enterFs();
    });

    document.addEventListener('visibilitychange', function () { if (document.hidden) logViolation('tab_hidden'); });
    window.addEventListener('blur', function () { logViolation('window_blur'); });
    document.addEventListener('fullscreenchange', function () { if (started && !document.fullscreenElement && !submitting) { logViolation('fullscreen_exit'); } });
    ['copy','paste','cut','contextmenu'].forEach(function (ev) {
        document.addEventListener(ev, function (e) { if (started) { e.preventDefault(); logViolation(ev); } });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C'))) { e.preventDefault(); logViolation('devtools'); }
    });
    form.addEventListener('submit', function () { submitting = true; });
})();
</script>
<?php endif; ?>
