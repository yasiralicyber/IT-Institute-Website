<?php
/** @var ?array $course @var array $chapters */
$isEdit = $course !== null;
$action = $isEdit ? '/courses/' . (int) $course['id'] : '/courses';
$outcomes = $isEdit ? implode("\n", json_decode($course['outcomes'] ?? '[]', true) ?: []) : '';
function fld($c, $k) { return $c[$k] ?? ''; }
?>
<a href="/courses" class="text-sm font-semibold text-brand-600 hover:underline">← All courses</a>

<div class="mt-4 grid gap-6 lg:grid-cols-<?= $isEdit ? '5' : '1' ?>">
    <!-- Course details -->
    <form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="<?= $isEdit ? 'lg:col-span-2' : '' ?> space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Course details</h2>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Title *</label>
            <input name="title" required value="<?= e(fld($course, 'title')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Thumbnail</label>
            <?php if ($isEdit && !empty($course['thumbnail'])): ?>
                <img src="/course-thumbnail/<?= (int) $course['id'] ?>" alt="" class="mb-2 aspect-video w-40 rounded-xl object-cover ring-1 ring-slate-200">
            <?php endif; ?>
            <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
            <p class="mt-1 text-xs text-slate-400">Leave blank to keep the current course image. Recommended size: 1200 &times; 675px (16:9) so the image fills the card with no cropping.</p>
        </div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Subtitle</label>
            <input name="subtitle" value="<?= e(fld($course, 'subtitle')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Category</label>
                <input name="category" value="<?= e(fld($course, 'category')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Level</label>
                <input name="level" value="<?= e(fld($course, 'level')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Price (PKR)</label>
                <input name="price" type="number" value="<?= (int) fld($course, 'price') ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Accent colour</label>
                <input name="accent" type="color" value="<?= e(fld($course, 'accent') ?: '#2f5078') ?>" class="h-11 w-full rounded-xl border-slate-300 bg-white dark:border-white/15 dark:bg-slate-800"></div>
        </div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e(fld($course, 'description')) ?></textarea></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">What you'll learn (one per line)</label>
            <textarea name="outcomes" rows="4" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e($outcomes) ?></textarea></div>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
            <input type="checkbox" name="is_published" value="1" <?= (!$isEdit || $course['is_published']) ? 'checked' : '' ?> class="rounded text-brand-600 focus:ring-brand-500"> Published (visible on site)
        </label>
        <button class="w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Create course' ?></button>
    </form>

    <?php if ($isEdit): ?>
    <!-- Curriculum builder -->
    <div class="space-y-4 lg:col-span-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Curriculum</h2>
            <p class="text-sm text-slate-500">Add chapters, then lectures inside each chapter. The first 5 free lectures rule is set per-lecture below.</p>

            <?php foreach ($chapters as $ci => $ch): ?>
            <div class="mt-5 rounded-xl border border-slate-200 dark:border-white/10">
                <div class="flex items-center justify-between gap-2 rounded-t-xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                    <p class="font-bold text-slate-900 dark:text-white"><?= ($ci + 1) . '. ' . e($ch['title']) ?></p>
                    <div class="flex items-center gap-2">
                        <a href="/chapters/<?= (int) $ch['id'] ?>/quiz" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-200">Test (<?= (int) $ch['questions'] ?> Qs)</a>
                        <form action="/chapters/<?= (int) $ch['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this chapter and its lectures?')">
                            <?= csrf_field() ?>
                            <button class="rounded-lg border border-red-300 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">✕</button>
                        </form>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-white/5">
                    <?php foreach ($ch['lectures'] as $l): ?>
                    <details class="px-4 py-2.5 text-sm">
                        <summary class="flex cursor-pointer items-center gap-3 list-none [&::-webkit-details-marker]:hidden">
                            <span class="text-slate-400">▶</span>
                            <span class="flex-1 text-slate-700 dark:text-slate-200"><?= e($l['title']) ?> <span class="text-xs text-slate-400">(<?= (int) $l['duration_min'] ?>m)</span></span>
                            <?php if ((int) $l['is_free'] === 1): ?><span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">FREE</span><?php endif; ?>
                            <?php if (!empty($l['description'])): ?><span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300" title="Has lesson notes">NOTES</span><?php endif; ?>
                            <?php if (str_starts_with((string) $l['video_url'], 'file:')): ?><span title="Uploaded video"></span><?php elseif ($l['video_url']): ?><span title="External video"></span><?php else: ?><span title="No video" class="text-amber-500"></span><?php endif; ?>
                            <a href="/lectures/<?= (int) $l['id'] ?>/interactive" class="rounded bg-brand-50 px-2 py-0.5 text-[10px] font-bold text-brand-700 hover:bg-brand-100 dark:bg-brand-500/10 dark:text-brand-300" title="Markers & in-video questions">Interactive</a>
                            <form action="/lectures/<?= (int) $l['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this lecture?')">
                                <?= csrf_field() ?>
                                <button class="text-red-500 hover:text-red-700">✕</button>
                            </form>
                        </summary>
                        <form action="/lectures/<?= (int) $l['id'] ?>/update" method="POST" enctype="multipart/form-data" class="mt-3 space-y-2 rounded-xl bg-slate-50/50 p-3 dark:bg-white/5">
                            <?= csrf_field() ?>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <input name="title" required value="<?= e($l['title']) ?>" placeholder="Lecture title" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                                <input name="duration_min" type="number" value="<?= (int) $l['duration_min'] ?>" placeholder="Duration (min)" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            </div>
                            <textarea name="description" rows="3" placeholder="Lesson notes shown to students on this lecture" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e($l['description'] ?? '') ?></textarea>
                            <input name="video_url" value="<?= str_starts_with((string) $l['video_url'], 'file:') ? '' : e($l['video_url']) ?>" placeholder="Video URL (YouTube/Vimeo embed) - or upload a file below" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            <div class="flex flex-wrap items-center gap-3">
                                <input type="file" name="video" accept="video/mp4,video/webm" class="text-xs">
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300" title="Drip: leave blank to release immediately"><input type="date" name="release_at" value="<?= e($l['release_at'] ?? '') ?>" class="rounded-lg border-slate-300 bg-white px-2 py-1 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"></label>
                                <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300"><input type="checkbox" name="is_free" value="1" <?= (int) $l['is_free'] === 1 ? 'checked' : '' ?> class="rounded text-brand-600"> Free preview</label>
                                <button class="ml-auto rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-brand-700">Save changes</button>
                            </div>
                        </form>
                    </details>
                    <?php endforeach; ?>
                    <!-- Add lecture -->
                    <form action="/chapters/<?= (int) $ch['id'] ?>/lectures" method="POST" enctype="multipart/form-data" class="space-y-2 bg-slate-50/50 px-4 py-3 dark:bg-white/5">
                        <?= csrf_field() ?>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <input name="title" required placeholder="Lecture title" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            <input name="duration_min" type="number" placeholder="Duration (min)" class="rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                        </div>
                        <textarea name="description" rows="2" placeholder="Lesson notes shown to students on this lecture (optional)" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
                        <input name="video_url" placeholder="Video URL (YouTube/Vimeo embed) - or upload a file below" class="w-full rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="file" name="video" accept="video/mp4,video/webm" class="text-xs">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300" title="Drip: leave blank to release immediately"><input type="date" name="release_at" class="rounded-lg border-slate-300 bg-white px-2 py-1 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"></label>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300"><input type="checkbox" name="is_free" value="1" class="rounded text-brand-600"> Free preview</label>
                            <button class="ml-auto rounded-lg bg-brand-600 px-4 py-1.5 text-xs font-bold text-white hover:bg-brand-700">+ Add lecture</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Add chapter -->
            <form action="/courses/<?= (int) $course['id'] ?>/chapters" method="POST" class="mt-5 flex gap-2">
                <?= csrf_field() ?>
                <input name="title" required placeholder="New chapter title" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <button class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700 dark:bg-white/10">+ Chapter</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
