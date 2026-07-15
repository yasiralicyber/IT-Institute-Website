<?php
/** @var array $facilities @var array $gallery */
$inst = config('institute');
$ph_title = 'Campus & Facilities'; $ph_sub = 'Modern labs, smart classrooms and the right environment to learn and grow.'; $ph_img = 'photos/campus-2.jpg';
include BASE_PATH . '/resources/views/partials/page-header.php';
?>

<!-- Facilities -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="mx-auto max-w-2xl text-center" data-reveal>
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">What We Offer</span>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Our Facilities</h2>
    </div>
    <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($facilities as $f): ?>
        <div class="group overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <div class="h-48 overflow-hidden">
                <img src="<?= asset('img/' . $f['image']) ?>" alt="<?= e($f['title']) ?>" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            </div>
            <div class="p-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($f['title']) ?></h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($f['desc']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Gallery / Tour -->
<section class="bg-slate-50 py-20 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Photo Tour</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Inside Our Campus</h2>
        </div>
        <div class="mt-12 columns-2 gap-4 sm:columns-3 lg:columns-4 [&>a]:mb-4">
            <?php foreach ($gallery as $i => $g): ?>
            <a href="<?= asset('img/' . $g) ?>" target="_blank" class="group block break-inside-avoid overflow-hidden rounded-2xl" data-reveal>
                <img src="<?= asset('img/' . $g) ?>" alt="Campus photo" loading="lazy" class="w-full object-cover transition duration-500 group-hover:scale-105">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Location -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="grid gap-8 lg:grid-cols-2">
        <div data-reveal>
            <h2 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Visit Us in Kumber Maidan</h2>
            <p class="mt-4 text-slate-600 dark:text-slate-300">We'd love to show you around. Drop by during class hours to see the labs, meet the instructors and discuss the right course for you.</p>
            <ul class="mt-6 space-y-3 text-slate-700 dark:text-slate-200">
                <li class="flex items-center gap-3"><span class="text-xl"></span> Kumber Maidan, Pakistan</li>
                <li class="flex items-center gap-3"><span class="text-xl"></span> <a href="tel:<?= e(preg_replace('/\s+/', '', $inst['phone'])) ?>" class="font-semibold hover:text-brand-700"><?= e($inst['phone']) ?></a></li>
                <li class="flex items-center gap-3"><span class="text-xl"></span> <a href="https://wa.me/<?= e($inst['whatsapp']) ?>" class="font-semibold hover:text-brand-700">WhatsApp us</a></li>
            </ul>
            <a href="<?= url('/admission') ?>" class="mt-7 inline-block rounded-xl bg-gold-500 px-6 py-3 font-bold text-brand-950 hover:bg-gold-400">Apply for Admission</a>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm dark:border-white/10" data-reveal>
            <iframe src="https://www.google.com/maps?q=Kumber+Maidan&output=embed" class="h-full min-h-[360px] w-full" style="border:0" loading="lazy"></iframe>
        </div>
    </div>
</section>
