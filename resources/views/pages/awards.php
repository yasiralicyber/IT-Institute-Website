<?php
/** @var array $awards */
$ph_title = 'Awards & Achievements'; $ph_sub = 'Recognition we have earned for our commitment to quality IT education.'; $ph_img = 'awards/award-2.jpg';
include BASE_PATH . '/resources/views/partials/page-header.php';
?>

<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="mx-auto max-w-2xl text-center" data-reveal>
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Our Milestones</span>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">A Track Record of Excellence</h2>
        <p class="mt-4 text-slate-600 dark:text-slate-300">Over the years our work has been recognised by the community and local bodies - a reflection of the results our students achieve.</p>
    </div>

    <div class="mt-14 grid gap-8 lg:grid-cols-2">
        <?php foreach ($awards as $a): ?>
        <div class="group flex flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:shadow-xl dark:border-white/10 dark:bg-slate-900/60 sm:flex-row" data-reveal>
            <div class="relative h-48 overflow-hidden sm:h-auto sm:w-2/5">
                <img src="<?= url('/award-image/' . $a['id']) ?>" alt="<?= e($a['title']) ?>" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                <span class="absolute left-3 top-3 rounded-lg bg-gold-500 px-3 py-1 text-sm font-black text-brand-950"><?= e($a['year']) ?></span>
            </div>
            <div class="flex flex-1 flex-col justify-center p-6">
                <span class="text-xs font-bold uppercase tracking-wider text-gold-600"><?= e($a['org']) ?></span>
                <h3 class="mt-2 text-xl font-black text-slate-900 dark:text-white"><?= e($a['title']) ?></h3>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300"><?= e($a['description']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-14 rounded-3xl bg-gradient-to-r from-brand-700 to-brand-900 p-10 text-center text-white" data-reveal>
        <p class="text-5xl"></p>
        <h2 class="mt-4 text-2xl font-black sm:text-3xl">Be part of our success story</h2>
        <p class="mx-auto mt-3 max-w-xl text-brand-100">Join 1,200+ students who trusted us to launch their IT careers.</p>
        <a href="<?= url('/admission') ?>" class="mt-6 inline-block rounded-xl bg-gold-500 px-7 py-3.5 font-bold text-brand-950 hover:bg-gold-400">Apply Now</a>
    </div>
</section>
