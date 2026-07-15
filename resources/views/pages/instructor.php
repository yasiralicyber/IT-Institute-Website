<?php $inst = config('institute'); ?>
<section class="relative overflow-hidden bg-gradient-to-b from-white to-slate-50 dark:from-ink dark:to-slate-950">
    <div class="hero-grid absolute inset-0 opacity-50 dark:opacity-20"></div>
    <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-3">
        <div class="flex justify-center">
            <div class="relative">
                <div class="flex h-56 w-56 items-center justify-center rounded-[2rem] bg-gradient-to-br from-brand-500 to-indigo-600 text-8xl text-white shadow-2xl">‍</div>
                <span class="absolute -bottom-3 -right-3 rounded-2xl bg-white px-4 py-2 text-sm font-bold text-brand-700 shadow-lg dark:bg-slate-800 dark:text-brand-300">10+ yrs exp.</span>
            </div>
        </div>
        <div class="lg:col-span-2">
            <span class="text-sm font-bold uppercase tracking-wider text-brand-600">Meet Your Instructor</span>
            <h1 class="mt-2 text-4xl font-black text-slate-900 dark:text-white">Industry-Experienced IT Trainer</h1>
            <p class="mt-4 text-lg text-slate-600 dark:text-slate-300">
                Founder of <strong>IT Training Institute and College, Kumber Maidan</strong>, your instructor combines real-world experience in networking, cyber security and software development with a passion for teaching. Thousands of learners have followed along on the free YouTube channel - and many have gone on to certifications, jobs and their own IT businesses.
            </p>
            <div class="mt-6 flex flex-wrap gap-4">
                <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 font-bold text-white hover:bg-red-700">▶ Free YouTube Channel</a>
                <a href="https://wa.me/<?= e($inst['whatsapp']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-5 py-3 font-bold text-slate-700 hover:border-brand-400 dark:border-white/15 dark:text-white">Message on WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid gap-6 md:grid-cols-3">
        <?php
        $exp = [
            ['','Networking & Cisco','Hands-on CCNA-level routing, switching and real lab experience.'],
            ['','Cyber Security & Ethical Hacking','Practical, authorised security training and defensive best-practices.'],
            ['','Programming','C++, Java, Python, OOP and web fundamentals taught from scratch.'],
        ];
        foreach ($exp as $e): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <span class="text-4xl"><?= $e[0] ?></span>
            <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white"><?= e($e[1]) ?></h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($e[2]) ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-10 rounded-3xl bg-gradient-to-r from-slate-900 to-brand-950 p-10 text-center text-white">
        <h2 class="text-2xl font-black sm:text-3xl">Want to see the teaching style first?</h2>
        <p class="mx-auto mt-3 max-w-xl text-slate-300">Watch free lessons on YouTube, then enroll for the full structured course with tests and a certificate.</p>
        <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="mt-6 inline-block rounded-xl bg-red-600 px-6 py-3.5 font-bold hover:bg-red-700">Watch on YouTube</a>
    </div>
</section>
