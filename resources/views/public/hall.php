<?php /** @var array $awards @var array $projects @var array $stats @var array $topCourses @var array $leaders */
use App\Models\Gamification; ?>
<section class="relative isolate overflow-hidden bg-brand-950 text-white">
    <img src="<?= asset('img/awards/award-1.jpg') ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
    <div class="absolute inset-0 bg-brand-950/85"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6">
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-400">Celebrating Excellence</span>
        <h1 class="mt-3 text-4xl font-black sm:text-5xl">Hall of Fame</h1>
        <div class="mx-auto mt-8 grid max-w-2xl grid-cols-3 gap-6">
            <div><p class="text-3xl font-black text-gold-400"><?= number_format($stats['students']) ?>+</p><p class="text-xs text-brand-200">Students</p></div>
            <div><p class="text-3xl font-black text-gold-400"><?= $stats['certs'] ?>+</p><p class="text-xs text-brand-200">Certificates</p></div>
            <div><p class="text-3xl font-black text-gold-400"><?= $stats['projects'] ?>+</p><p class="text-xs text-brand-200">Projects</p></div>
        </div>
    </div>
</section>

<!-- Awards -->
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Institute Awards</h2>
    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($awards as $a): ?>
        <div data-reveal class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <div class="relative h-32"><img src="<?= asset('img/' . $a['image']) ?>" alt="" class="h-full w-full object-cover"><span class="absolute left-3 top-3 rounded-lg bg-gold-500 px-2.5 py-1 text-xs font-black text-brand-950"><?= e($a['year']) ?></span></div>
            <div class="p-4"><h3 class="font-bold text-slate-900 dark:text-white"><?= e($a['title']) ?></h3><p class="text-xs font-semibold text-gold-600"><?= e($a['org']) ?></p></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Top learners + featured projects -->
<section class="bg-slate-50 py-16 dark:bg-slate-900/40">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2">
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Top Learners</h2>
            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <?php if (empty($leaders)): ?><p class="px-5 py-8 text-center text-slate-500">Leaderboard fills up as students learn.</p>
                <?php else: foreach ($leaders as $i => $r): ?>
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-3 last:border-0 dark:border-white/5">
                    <span class="w-7 text-center font-black <?= $i<3?'text-gold-500':'text-slate-400' ?>"><?= $i+1 ?></span>
                    <span class="flex-1 font-semibold text-slate-800 dark:text-slate-200"><?= e(Gamification::publicName($r['name'])) ?></span>
                    <span class="font-black text-brand-700 dark:text-brand-300"><?= number_format($r['xp']) ?> XP</span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
        <div>
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Featured Projects</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <?php if (empty($projects)): ?><p class="text-slate-500">No featured projects yet. <a href="<?= url('/projects') ?>" class="font-bold text-brand-600">See all projects →</a></p>
                <?php else: foreach ($projects as $p): ?>
                <a href="<?= url('/portfolio/' . (int) $p['user_id']) ?>" class="rounded-2xl border border-slate-200 bg-white p-4 hover:shadow-md dark:border-white/10 dark:bg-slate-900">
                    <h3 class="font-bold text-slate-900 dark:text-white"><?= e($p['title']) ?></h3>
                    <p class="text-xs text-brand-600"><?= e($p['type']) ?> · by <?= e($p['author']) ?></p>
                </a>
                <?php endforeach; endif; ?>
            </div>
            <a href="<?= url('/projects') ?>" class="mt-4 inline-block font-bold text-brand-600 hover:underline">View all student projects →</a>
        </div>
    </div>
</section>
