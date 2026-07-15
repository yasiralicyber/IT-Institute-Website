<?php /** @var array $projects */ ?>
<section class="relative isolate overflow-hidden bg-brand-950 text-white">
    <img src="<?= asset('img/photos/lab.jpg') ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
    <div class="absolute inset-0 bg-brand-950/80"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-16 text-center sm:px-6">
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-400">Student Work</span>
        <h1 class="mt-3 text-4xl font-black sm:text-5xl">Project Showcase</h1>
        <p class="mx-auto mt-4 max-w-2xl text-brand-100">Real projects built by our students - websites, apps, network designs, CCTV installations and more.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <?php if (empty($projects)): ?>
        <p class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500 dark:border-white/10">No projects published yet - check back soon!</p>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($projects as $p): ?>
        <div data-reveal class="card-hover overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-xl dark:border-white/10 dark:bg-slate-900/60">
            <?php if ($p['image']): ?>
                <img src="<?= url('/project-image/' . (int) $p['id']) ?>" alt="<?= e($p['title']) ?>" loading="lazy" class="h-44 w-full object-cover">
            <?php else: ?>
                <div class="flex h-44 items-center justify-center bg-gradient-to-br from-brand-700 to-brand-950 text-4xl"></div>
            <?php endif; ?>
            <div class="p-5">
                <?php if ($p['featured']): ?><span class="rounded-full bg-gold-400 px-2 py-0.5 text-[10px] font-bold text-brand-950">★ Featured</span><?php endif; ?>
                <h3 class="mt-2 font-bold text-slate-900 dark:text-white"><?= e($p['title']) ?></h3>
                <p class="text-xs font-semibold text-brand-600"><?= e($p['type']) ?></p>
                <?php if ($p['description']): ?><p class="mt-2 line-clamp-3 text-sm text-slate-500 dark:text-slate-400"><?= e($p['description']) ?></p><?php endif; ?>
                <div class="mt-3 flex items-center justify-between text-sm">
                    <a href="<?= url('/portfolio/' . (int) $p['user_id']) ?>" class="font-semibold text-slate-700 hover:text-brand-700 dark:text-slate-300">by <?= e($p['author']) ?></a>
                    <?php if ($p['link']): ?><a href="<?= e($p['link']) ?>" target="_blank" rel="noopener" class="font-bold text-brand-600 hover:underline">View →</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
