<?php
/** @var array $course @var array $curriculum @var array $outcomes @var array $reviews @var array $rating @var int $freeCount @var int $totalCount */
$accent = $course['accent'] ?: '#2563eb';
$inst = config('institute');
?>
<!-- HERO -->
<section class="relative isolate overflow-hidden text-white">
    <img src="<?= asset('img/courses/' . $course['slug'] . '.jpg') ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30">
    <div class="absolute inset-0" style="background:linear-gradient(135deg,<?= e($accent) ?>e6,#091627f2)"></div>
    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <nav class="text-sm text-white/70"><a href="<?= url('/courses') ?>" class="hover:text-white">Courses</a> / <span><?= e($course['category']) ?></span></nav>
            <h1 class="mt-3 text-3xl font-black sm:text-4xl lg:text-5xl"><?= e($course['title']) ?></h1>
            <p class="mt-4 max-w-2xl text-lg text-white/85"><?= e($course['subtitle']) ?></p>
            <div class="mt-6 flex flex-wrap items-center gap-4 text-sm font-semibold">
                <span class="rounded-full bg-white/15 px-3 py-1.5"><?= e($course['level']) ?></span>
                <span class="rounded-full bg-white/15 px-3 py-1.5">▶ <?= $totalCount ?> lessons</span>
                <span class="rounded-full bg-white/15 px-3 py-1.5"><?= duration_label((int)$course['total_minutes']) ?></span>
                <?php if ($rating['count']): ?><span class="rounded-full bg-amber-400/90 px-3 py-1.5 text-amber-950">★ <?= $rating['avg'] ?> (<?= $rating['count'] ?>)</span><?php endif; ?>
                <span class="rounded-full bg-emerald-400/90 px-3 py-1.5 text-emerald-950"><?= $freeCount ?> FREE lessons</span>
            </div>
        </div>

        <!-- Enroll card -->
        <div class="lg:row-span-2">
            <div class="rounded-2xl border border-white/15 bg-white p-6 text-slate-800 shadow-2xl dark:bg-slate-900 dark:text-slate-200">
                <p class="text-sm text-slate-500">Course fee</p>
                <p class="text-4xl font-black text-slate-900 dark:text-white"><?= pkr($course['price']) ?></p>
                <a href="<?= url('/enroll/' . $course['slug']) ?>" class="mt-5 block rounded-xl bg-brand-600 py-3.5 text-center font-bold text-white transition hover:bg-brand-700">Enroll Now</a>
                <a href="#curriculum" class="mt-3 block rounded-xl border border-slate-300 py-3 text-center font-bold text-slate-700 transition hover:border-brand-400 dark:border-white/15 dark:text-white">Watch 5 Free Lessons</a>
                <ul class="mt-6 space-y-3 text-sm">
                    <?php foreach (['Lifetime access to all lessons','Chapter tests & progress tracking','Verifiable completion certificate','Community Q&A support','Secure, leak-protected videos'] as $perk): ?>
                    <li class="flex items-start gap-2"><span class="text-emerald-500">✓</span><?= e($perk) ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs text-slate-500 dark:bg-white/5">
                    Pay via bank / JazzCash / Easypaisa, then upload your receipt. We verify & unlock your access fast.
                </div>
            </div>
        </div>

        <!-- About -->
        <div class="lg:col-span-2">
            <div class="rounded-2xl bg-white/10 p-6 backdrop-blur">
                <h2 class="text-lg font-bold">About this course</h2>
                <p class="mt-3 text-white/85"><?= e($course['description']) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- INTERACTIVE LAB -->
<?php
$labMap = ['ccna-200-301'=>'network','cctv-camera-installation'=>'cctv','cyber-security'=>'cyber','ethical-hacking'=>'cyber'];
if (isset($labMap[$course['slug']])): $labTool = $labMap[$course['slug']]; ?>
<section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6">
    <a href="<?= url('/labs/' . $labTool) ?>" class="flex items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-brand-700 to-brand-950 p-6 text-white transition hover:from-brand-800">
        <div><p class="text-sm font-bold uppercase tracking-wider text-gold-300">Hands-on Practice</p><p class="text-lg font-black">Try the free interactive lab for this course →</p></div>
        <span class="text-3xl"></span>
    </a>
</section>
<?php endif; ?>

<!-- OUTCOMES -->
<?php if ($outcomes): ?>
<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white">What you'll learn</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <?php foreach ($outcomes as $o): ?>
        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900/60">
            <span class="mt-0.5 flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-100 text-emerald-700">✓</span>
            <p class="text-slate-700 dark:text-slate-300"><?= e($o) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- CURRICULUM -->
<section id="curriculum" class="bg-white py-14 dark:bg-slate-900/40">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">Course Curriculum</h2>
            <p class="text-sm text-slate-500"><?= count($curriculum) ?> chapters · <?= $totalCount ?> lessons</p>
        </div>

        <div class="mt-8 space-y-4">
            <?php $globalIdx = 0; foreach ($curriculum as $ci => $chapter): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10" x-data>
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden');this.querySelector('.chev').classList.toggle('rotate-180')"
                        class="flex w-full items-center justify-between bg-slate-50 px-5 py-4 text-left dark:bg-white/5">
                    <span class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-black text-white"><?= $ci + 1 ?></span>
                        <span class="font-bold text-slate-900 dark:text-white"><?= e($chapter['title']) ?></span>
                    </span>
                    <span class="flex items-center gap-3 text-sm text-slate-400">
                        <span><?= count($chapter['lectures']) ?> lessons</span>
                        <span class="chev transition-transform">▾</span>
                    </span>
                </button>
                <div class="<?= $ci === 0 ? '' : 'hidden' ?> divide-y divide-slate-100 dark:divide-white/5">
                    <?php foreach ($chapter['lectures'] as $lec): $globalIdx++; $free = (int)$lec['is_free'] === 1; ?>
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <span class="flex h-8 w-8 flex-none items-center justify-center rounded-lg <?= $free ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400 dark:bg-white/5' ?>">
                            <?= $free ? '▶' : '' ?>
                        </span>
                        <p class="flex-1 text-sm font-medium text-slate-700 dark:text-slate-200"><?= e($lec['title']) ?></p>
                        <span class="text-xs text-slate-400"><?= duration_label((int)$lec['duration_min']) ?></span>
                        <?php if ($free): ?>
                            <a href="<?= url('/learn/preview/' . (int)$lec['id']) ?>" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10">Watch free</a>
                        <?php else: ?>
                            <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-400 dark:bg-white/5">Locked</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <!-- chapter test note -->
                    <div class="flex items-center gap-3 bg-amber-50/60 px-5 py-3 dark:bg-amber-500/5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700"></span>
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Chapter Test - pass to unlock the next chapter</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- REVIEWS -->
<section class="mx-auto max-w-4xl px-4 py-14 sm:px-6">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Student Reviews</h2>
    <?php if ($reviews): ?>
    <div class="mt-6 space-y-4">
        <?php foreach ($reviews as $r): ?>
        <figure class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900/60">
            <div class="flex items-center justify-between">
                <figcaption class="font-bold text-slate-900 dark:text-white"><?= e($r['author']) ?></figcaption>
                <div class="text-amber-400"><?= str_repeat('★', (int)$r['rating']) ?></div>
            </div>
            <blockquote class="mt-2 text-slate-600 dark:text-slate-300">"<?= e($r['body']) ?>"</blockquote>
        </figure>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p class="mt-4 rounded-xl border border-dashed border-slate-300 p-6 text-center text-slate-500 dark:border-white/10">No reviews yet - be the first to review after enrolling.</p>
    <?php endif; ?>
</section>

<!-- CTA -->
<section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6">
    <div class="rounded-3xl bg-gradient-to-r from-brand-700 to-indigo-700 px-8 py-12 text-center text-white">
        <h2 class="text-2xl font-black sm:text-3xl">Start with 5 free lessons today</h2>
        <p class="mx-auto mt-3 max-w-xl text-brand-100">No risk - watch the first 5 lessons free. When you're ready, enroll and unlock the full course plus your certificate.</p>
        <div class="mt-7 flex flex-wrap justify-center gap-4">
            <a href="<?= url('/enroll/' . $course['slug']) ?>" class="rounded-xl bg-white px-6 py-3.5 font-bold text-brand-700 hover:bg-brand-50">Enroll Now</a>
            <a href="https://wa.me/<?= e($inst['whatsapp']) ?>?text=<?= rawurlencode('I want to enroll in '.$course['title']) ?>" target="_blank" rel="noopener" class="rounded-xl border border-white/40 px-6 py-3.5 font-bold text-white hover:bg-white/10">Ask on WhatsApp</a>
        </div>
    </div>
</section>
