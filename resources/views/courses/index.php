<?php /** @var array $courses */ ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6">
        <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white sm:text-5xl">Our Courses</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Choose from networking, cyber security, hardware and programming tracks. Every course gives you <strong>5 free lessons</strong> and a <strong>verifiable certificate</strong> on completion.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($courses as $course) { include BASE_PATH . '/resources/views/partials/course-card.php'; } ?>
    </div>
</section>
