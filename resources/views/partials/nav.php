<?php
$nav = [
    '/'           => 'Home',
    '/about'      => 'About',
    '/courses'    => 'Courses',
    '/faculty'    => 'Faculty',
    '/activities' => 'Activities',
    '/awards'     => 'Awards',
    '/result'     => 'Result',
    '/admission'  => 'Admissions',
    '/contact'    => 'Contact',
];
$inst = config('institute');
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!-- Utility bar -->
<div class="hidden bg-brand-900 text-brand-100 lg:block">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-2 text-xs">
        <div class="flex items-center gap-5">
            <a href="tel:<?= e(preg_replace('/\s+/', '', $inst['phone'])) ?>" class="flex items-center gap-1.5 hover:text-white"><span></span><?= e($inst['phone']) ?></a>
            <a href="mailto:<?= e($inst['email']) ?>" class="flex items-center gap-1.5 hover:text-white"><span></span><?= e($inst['email']) ?></a>
            <span class="flex items-center gap-1.5"><span></span>Kumber Maidan, Pakistan</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="flex items-center gap-1.5 font-semibold text-gold-300 hover:text-gold-200">▶ Free YouTube Lessons</a>
            <span class="text-white/20">|</span>
            <a href="<?= url('/guardian') ?>" class="font-semibold text-white hover:text-gold-200">Parents Portal</a>
            <span class="text-white/20">|</span>
            <a href="<?= e($inst['facebook']) ?>" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
            <a href="<?= url('/verify') ?>" class="hover:text-white">Verify Certificate</a>
        </div>
    </div>
</div>

<!-- Main nav -->
<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 glass dark:border-white/10 dark:bg-ink/90">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6">
        <?php include __DIR__ . '/logo.php'; ?>

        <nav class="hidden items-center gap-0.5 xl:flex">
            <?php foreach ($nav as $href => $label): $active = $current === $href; ?>
                <a href="<?= url($href) ?>"
                   class="rounded-lg px-3 py-2 text-sm font-semibold transition <?= $active
                        ? 'text-brand-700 dark:text-brand-300'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-brand-700 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' ?>">
                    <?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="flex items-center gap-2">
            <button onclick="toggleTheme()" aria-label="Toggle theme"
                    class="hidden h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 sm:flex">
                <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </button>
            <a href="<?= url('/login') ?>" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 hover:text-brand-700 dark:text-slate-200 sm:block">Log in</a>
            <a href="<?= url('/admission') ?>" class="rounded-lg bg-gold-500 px-4 py-2 text-sm font-bold text-brand-950 shadow-lg shadow-gold-500/30 transition hover:bg-gold-400">Apply Now</a>
            <button onclick="toggleMenu()" class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 xl:hidden" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="hidden border-t border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-ink xl:hidden">
        <?php foreach ($nav as $href => $label): ?>
            <a href="<?= url($href) ?>" class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/10"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="<?= url('/login') ?>" class="mt-1 block rounded-lg px-3 py-2.5 text-sm font-semibold text-brand-700 dark:text-brand-300">Log in</a>
    </div>
</header>
