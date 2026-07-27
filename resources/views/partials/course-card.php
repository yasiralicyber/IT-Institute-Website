<?php
/** Reusable course card with photo. Expects $course (array). */
$accent = $course['accent'] ?: '#274a70';
$mins = (int) $course['total_minutes'];
$imgFile = '/courses/' . $course['slug'] . '.jpg';
$img = !empty($course['thumbnail'])
    ? url('/course-thumbnail/' . $course['id'])
    : asset('img' . (is_file(BASE_PATH . '/public/assets/img' . $imgFile) ? $imgFile : '/courses/_default.jpg'));
?>
<a href="<?= url('/courses/' . $course['slug']) ?>"
   data-reveal
   class="card-hover group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-2xl dark:border-white/10 dark:bg-slate-900/60">
    <div class="relative aspect-video overflow-hidden">
        <img src="<?= e($img) ?>" alt="<?= e($course['title']) ?>" loading="lazy"
             class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-brand-950/90 via-brand-950/30 to-transparent"></div>
        <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-800 backdrop-blur"><?= e($course['category']) ?></span>
        <span class="absolute right-3 top-3 rounded-full bg-gold-400 px-2.5 py-1 text-[11px] font-bold text-brand-950">5 lessons FREE</span>
        <h3 class="absolute bottom-3 left-4 right-4 text-lg font-extrabold leading-tight text-white drop-shadow-lg"><?= e($course['title']) ?></h3>
    </div>
    <div class="flex flex-1 flex-col p-5">
        <p class="text-sm text-slate-500 dark:text-slate-400"><?= e($course['subtitle']) ?></p>
        <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1.5"><?= icon('academic','h-4 w-4 text-brand-500') ?> <?= e($course['level']) ?></span>
            <?php if ($mins): ?><span class="inline-flex items-center gap-1.5"><?= icon('clock','h-4 w-4 text-brand-500') ?> <?= duration_label($mins) ?></span><?php endif; ?>
        </div>
        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-white/10">
            <div>
                <span class="text-xs text-slate-400">Course fee</span>
                <p class="text-lg font-extrabold text-slate-900 dark:text-white"><?= pkr($course['price']) ?></p>
            </div>
            <span class="inline-flex items-center gap-1 rounded-lg bg-brand-50 px-3 py-2 text-sm font-bold text-brand-700 transition group-hover:bg-brand-700 group-hover:text-white dark:bg-brand-500/10 dark:text-brand-300">
                Explore <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 12h14m-6-6l6 6-6 6"/></svg>
            </span>
        </div>
    </div>
</a>
