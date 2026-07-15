<?php /** @var array $courses */ ?>
<?php if (empty($courses)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-white/10 dark:bg-slate-900">
        <p class="text-slate-500">You haven't enrolled in any courses yet.</p>
        <a href="<?= url('/courses') ?>" class="mt-4 inline-block rounded-xl bg-brand-600 px-5 py-2.5 font-bold text-white hover:bg-brand-700">Explore Courses</a>
    </div>
<?php else: ?>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($courses as $c): ?>
        <a href="<?= url('/learn/' . $c['slug']) ?>" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-lg dark:border-white/10 dark:bg-slate-900">
            <div class="h-24" style="background:linear-gradient(135deg,<?= e($c['accent'] ?: '#2f5078') ?>,#0a121f)"></div>
            <div class="p-5">
                <h4 class="font-bold text-slate-900 dark:text-white"><?= e($c['title']) ?></h4>
                <p class="mt-1 text-sm text-slate-500"><?= e($c['category']) ?></p>
                <span class="mt-3 inline-block text-sm font-bold text-brand-600 group-hover:underline">Open course →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
