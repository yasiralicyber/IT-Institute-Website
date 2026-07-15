<?php /** @var array $student @var ?string $program @var array $projects @var array $certs */ ?>
<section class="bg-gradient-to-br from-brand-800 to-brand-950 py-16 text-white">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6">
        <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-white/10 text-4xl font-black ring-2 ring-gold-400"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span>
        <h1 class="mt-4 text-3xl font-black sm:text-4xl"><?= e($student['name']) ?></h1>
        <p class="mt-1 text-brand-100"><?= e($program ?: 'Student') ?> · IT Training Institute and College, Kumber Maidan</p>
        <div class="mt-4 flex justify-center gap-6 text-sm">
            <span><strong class="text-gold-400 text-lg"><?= count($projects) ?></strong> projects</span>
            <span><strong class="text-gold-400 text-lg"><?= count($certs) ?></strong> certificates</span>
        </div>
    </div>
</section>

<section class="mx-auto max-w-5xl px-4 py-14 sm:px-6">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Projects</h2>
    <?php if (empty($projects)): ?>
        <p class="mt-4 rounded-xl border border-dashed border-slate-300 p-8 text-center text-slate-500 dark:border-white/10">No published projects yet.</p>
    <?php else: ?>
    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <?php foreach ($projects as $p): ?>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
            <?php if ($p['image']): ?><img src="<?= url('/project-image/' . (int) $p['id']) ?>" alt="" class="h-48 w-full object-cover"><?php endif; ?>
            <div class="p-5">
                <h3 class="font-bold text-slate-900 dark:text-white"><?= e($p['title']) ?></h3>
                <p class="text-xs font-semibold text-brand-600"><?= e($p['type']) ?></p>
                <?php if ($p['description']): ?><p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= e($p['description']) ?></p><?php endif; ?>
                <div class="mt-3 flex gap-4 text-sm font-bold">
                    <?php if ($p['link']): ?><a href="<?= e($p['link']) ?>" target="_blank" rel="noopener" class="text-brand-600 hover:underline">Live / Code →</a><?php endif; ?>
                    <?php if ($p['file']): ?><a href="<?= url('/project-file/' . (int) $p['id']) ?>" class="text-slate-500 hover:underline">Download</a><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($certs)): ?>
    <h2 class="mt-12 text-2xl font-black text-slate-900 dark:text-white">Certificates</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <?php foreach ($certs as $c): ?>
        <a href="<?= url('/certificate/' . urlencode($c['credential_id'])) ?>" target="_blank" class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
            <p class="text-2xl"></p><p class="mt-1 font-bold text-slate-900 dark:text-white"><?= e($c['course_title']) ?></p><p class="text-xs text-slate-500"><?= ucfirst($c['type']) ?> · verify →</p>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
