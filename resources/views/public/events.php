<?php /** @var array $events */ ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Events & News</h1>
        <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-300">Latest happenings, competitions and announcements from IT Training Institute and College, Kumber Maidan.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <?php if (empty($events)): ?>
        <p class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500 dark:border-white/10">No events or news yet - check back soon!</p>
    <?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($events as $e):
            $color = ['event'=>'bg-brand-600','news'=>'bg-emerald-600','competition'=>'bg-gold-500 text-brand-950'][$e['type']] ?? 'bg-brand-600'; ?>
        <div data-reveal class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900/60">
            <?php if ($e['image']): ?>
                <img src="<?= url('/event-image/' . (int) $e['id']) ?>" alt="" loading="lazy" class="h-44 w-full object-cover">
            <?php else: ?>
                <div class="flex h-44 items-center justify-center bg-gradient-to-br from-brand-700 to-brand-950 text-4xl text-white"></div>
            <?php endif; ?>
            <div class="p-5">
                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase text-white <?= $color ?>"><?= e($e['type']) ?></span>
                <h3 class="mt-2 font-bold text-slate-900 dark:text-white"><?= e($e['title']) ?></h3>
                <p class="text-xs text-slate-400"><?= e($e['event_date'] ?: date('d M Y', strtotime($e['created_at']))) ?></p>
                <?php if ($e['body']): ?><p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= e($e['body']) ?></p><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>
