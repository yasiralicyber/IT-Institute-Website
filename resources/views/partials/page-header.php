<?php
/** Reusable page hero. Expects $ph_title, $ph_sub, $ph_img (asset path under img/). */
$ph_img = $ph_img ?? 'photos/about.jpg';
?>
<section class="relative isolate overflow-hidden bg-brand-950">
    <img src="<?= asset('img/' . $ph_img) ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
    <div class="absolute inset-0 bg-gradient-to-r from-brand-950 via-brand-950/90 to-brand-900/70"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:py-20">
        <nav class="text-sm text-brand-300"><a href="<?= url('/') ?>" class="hover:text-white">Home</a> <span class="text-white/30">/</span> <span class="text-gold-400"><?= e($ph_title) ?></span></nav>
        <h1 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-5xl"><?= e($ph_title) ?></h1>
        <?php if (!empty($ph_sub)): ?><p class="mt-4 max-w-2xl text-lg text-brand-100"><?= e($ph_sub) ?></p><?php endif; ?>
    </div>
</section>
