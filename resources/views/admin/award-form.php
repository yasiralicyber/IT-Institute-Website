<?php
/** @var ?array $award */
$isEdit = $award !== null;
$action = $isEdit ? '/awards/' . (int) $award['id'] : '/awards';
function fld($a, $k) { return $a[$k] ?? ''; }
?>
<a href="/awards" class="text-sm font-semibold text-brand-600 hover:underline">← All awards</a>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="mt-4 max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
    <?= csrf_field() ?>
    <?php if ($isEdit && $award['image']): ?>
        <img src="/award-image/<?= (int) $award['id'] ?>" alt="" class="h-24 w-24 rounded-xl object-cover ring-1 ring-slate-200">
    <?php endif; ?>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Title *</label>
            <input name="title" required value="<?= e(fld($award, 'title')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Year</label>
            <input name="year" value="<?= e(fld($award, 'year')) ?>" placeholder="e.g. 2023" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Organization</label>
            <input name="org" value="<?= e(fld($award, 'org')) ?>" placeholder="e.g. Chamber of Commerce" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    </div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Description</label>
        <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e(fld($award, 'description')) ?></textarea></div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Image</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm"></div>
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
        <input type="checkbox" name="is_published" value="1" <?= (!$isEdit || $award['is_published']) ? 'checked' : '' ?> class="rounded text-brand-600"> Show on website
    </label>
    <button class="w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Add award' ?></button>
</form>
