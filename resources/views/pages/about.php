<?php
use App\Content;
$stats = Content::stats();
$values = Content::values();
$inst = config('institute');
$ph_title = 'About Us'; $ph_sub = 'Empowering students in Kumber Maidan with world-class, affordable IT education.'; $ph_img = 'photos/about.jpg';
include BASE_PATH . '/resources/views/partials/page-header.php';
?>

<!-- Story -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="grid items-center gap-12 lg:grid-cols-2">
        <div data-reveal>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Our Story</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">A Trusted Name in IT Training</h2>
            <p class="mt-5 text-slate-600 dark:text-slate-300">IT Training Institute was founded in Kumber Maidan with a simple belief: every student deserves access to quality, practical IT education - close to home and at a fair price. What started with a few students and a passion for teaching has grown into a respected institute and a thriving free YouTube channel followed by thousands.</p>
            <p class="mt-4 text-slate-600 dark:text-slate-300">Today we train students across networking, cyber security, programming and hardware - combining real lab equipment, hands-on projects and verifiable certificates to prepare them for jobs, freelancing and their own businesses.</p>
        </div>
        <div class="grid grid-cols-2 gap-4" data-reveal>
            <img src="<?= asset('img/photos/campus-1.jpg') ?>" alt="" class="h-56 w-full rounded-2xl object-cover">
            <img src="<?= asset('img/photos/lab.jpg') ?>" alt="" class="mt-8 h-56 w-full rounded-2xl object-cover">
            <img src="<?= asset('img/photos/campus-3.jpg') ?>" alt="" class="h-56 w-full rounded-2xl object-cover">
            <img src="<?= asset('img/photos/about.jpg') ?>" alt="" class="mt-8 h-56 w-full rounded-2xl object-cover">
        </div>
    </div>
</section>

<!-- Stats band -->
<section class="bg-brand-950 py-14 text-white">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-4 sm:px-6 lg:grid-cols-4">
        <?php foreach ($stats as $s): ?>
        <div class="text-center" data-reveal><p class="text-4xl font-black text-gold-400 sm:text-5xl"><?= e($s[0]) ?></p><p class="mt-1 text-sm text-brand-200"><?= e($s[1]) ?></p></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Mission / Vision -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-3xl dark:bg-brand-500/10"></span>
            <h3 class="mt-5 text-xl font-black text-slate-900 dark:text-white">Our Mission</h3>
            <p class="mt-3 text-slate-600 dark:text-slate-300">To deliver practical, job-ready IT education that is affordable and accessible to every student in our community - and to support each learner all the way from their first lesson to their first job.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-8 dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gold-100 text-3xl dark:bg-gold-500/10"></span>
            <h3 class="mt-5 text-xl font-black text-slate-900 dark:text-white">Our Vision</h3>
            <p class="mt-3 text-slate-600 dark:text-slate-300">To become the leading IT institute in the region - known for producing skilled, confident professionals who power the digital growth of Pakistan.</p>
        </div>
    </div>

    <h2 class="mt-16 text-center text-3xl font-black tracking-tight text-slate-900 dark:text-white" data-reveal>Why Students Choose Us</h2>
    <div class="mt-10 grid gap-6 md:grid-cols-3">
        <?php foreach ($values as $v): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <span class="text-3xl"><?= $v['icon'] ?></span>
            <h3 class="mt-4 font-bold text-slate-900 dark:text-white"><?= e($v['title']) ?></h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($v['desc']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6">
    <div class="flex flex-col items-center justify-between gap-6 rounded-3xl bg-gradient-to-r from-brand-700 to-brand-900 p-10 text-center sm:flex-row sm:text-left" data-reveal>
        <div><h2 class="text-2xl font-black text-white">Come visit our campus in Kumber Maidan</h2><p class="mt-2 text-brand-100">Meet the faculty, see the labs, and find the right course for you.</p></div>
        <div class="flex flex-none gap-3">
            <a href="<?= url('/campus') ?>" class="rounded-xl bg-white px-6 py-3 font-bold text-brand-800 hover:bg-brand-50">Campus Tour</a>
            <a href="<?= url('/contact') ?>" class="rounded-xl bg-gold-500 px-6 py-3 font-bold text-brand-950 hover:bg-gold-400">Contact Us</a>
        </div>
    </div>
</section>
