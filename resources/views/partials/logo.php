<?php
/**
 * Brand logo: real ITTI crest image + wordmark.
 * The crest JPEG has a white background, so on any background we set it in a
 * subtle white rounded tile (looks intentional in both light and dark mode).
 * $variant = 'light' (for dark backgrounds) | 'dark' (default, light backgrounds)
 */
$variant = $variant ?? 'dark';
$word = $variant === 'light' ? 'text-white' : 'text-slate-900 dark:text-white';
$sub  = $variant === 'light' ? 'text-brand-200' : 'text-brand-600 dark:text-brand-300';
?>
<a href="<?= url('/') ?>" class="flex items-center gap-3 group">
    <span class="inline-flex h-12 w-12 flex-none items-center justify-center overflow-hidden rounded-xl bg-white p-0.5 shadow-md ring-1 ring-black/5 transition group-hover:scale-105">
        <img src="<?= url('/site-logo') ?>" alt="IT Training Institute Kumber Maidan" class="h-full w-full object-contain">
    </span>
    <span class="leading-none">
        <span class="block text-base font-extrabold tracking-tight <?= $word ?>">IT Training Institute <span class="whitespace-nowrap">&amp; College</span></span>
        <span class="block text-[11px] font-semibold uppercase tracking-[0.2em] <?= $sub ?>">Kumber Maidan</span>
    </span>
</a>
