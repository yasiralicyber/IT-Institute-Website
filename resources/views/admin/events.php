<?php /** @var array $rows */ ?>
<div class="mb-5 flex justify-end">
    <a href="/board" target="_blank" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:text-white">Open Noticeboard (TV mode)</a>
</div>
<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/events" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Post Event / News</h2>
        <input name="title" required placeholder="Title" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <select name="type" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <option value="event">Event</option><option value="news">News</option><option value="competition">Competition</option>
        </select>
        <input name="event_date" type="date" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <textarea name="body" rows="4" placeholder="Details…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Publish</button>
    </form>

    <div class="space-y-3">
        <?php if (empty($rows)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No events or news yet.</div>
        <?php else: foreach ($rows as $r): ?>
        <div class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
            <?php if ($r['image']): ?><img src="/event-image/<?= (int) $r['id'] ?>" alt="" class="h-20 w-28 flex-none rounded-xl object-cover"><?php endif; ?>
            <div class="min-w-0 flex-1">
                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700 dark:bg-brand-500/10"><?= e($r['type']) ?></span>
                <h3 class="mt-1 font-bold text-slate-900 dark:text-white"><?= e($r['title']) ?></h3>
                <p class="text-xs text-slate-400"><?= e($r['event_date'] ?: date('d M Y', strtotime($r['created_at']))) ?></p>
            </div>
            <form action="/events/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button class="text-sm font-bold text-red-500 hover:text-red-700">Delete</button></form>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
