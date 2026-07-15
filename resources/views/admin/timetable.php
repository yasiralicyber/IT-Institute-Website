<?php /** @var array $rows */ ?>
<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <!-- Add form -->
    <form action="/timetable" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Timetable</h2>
        <input name="title" required placeholder="Title (e.g. Morning Batches)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <textarea name="body" rows="4" placeholder="Schedule text (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300">Timetable image (optional)</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Publish</button>
    </form>

    <!-- List -->
    <div class="space-y-4">
        <?php if (empty($rows)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No timetable entries yet.</div>
        <?php else: foreach ($rows as $r): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-start justify-between">
                <h3 class="font-bold text-slate-900 dark:text-white"><?= e($r['title']) ?></h3>
                <form action="/timetable/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this entry?')">
                    <?= csrf_field() ?>
                    <button class="text-sm font-bold text-red-500 hover:text-red-700">Delete</button>
                </form>
            </div>
            <?php if ($r['body']): ?><p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300"><?= e($r['body']) ?></p><?php endif; ?>
            <?php if ($r['image_path']): ?><img src="/timetable-image/<?= (int) $r['id'] ?>" alt="Timetable" class="mt-3 w-full rounded-xl border border-slate-200 dark:border-white/10"><?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
