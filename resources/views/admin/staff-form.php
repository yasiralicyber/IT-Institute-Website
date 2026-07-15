<?php
/** @var ?array $staff */
$isEdit = $staff !== null;
$action = $isEdit ? '/staff/' . (int) $staff['id'] : '/staff';
function sf($s, $k) { return $s[$k] ?? ''; }
?>
<a href="/staff" class="text-sm font-semibold text-brand-600 hover:underline">← All staff</a>

<form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="mt-4 max-w-2xl space-y-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
    <?= csrf_field() ?>
    <?php if ($isEdit && $staff['photo']): ?>
        <img src="/staff-photo/<?= (int) $staff['id'] ?>" alt="" class="h-24 w-24 rounded-xl object-cover ring-1 ring-slate-200">
    <?php endif; ?>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Name *</label>
            <input name="name" required value="<?= e(sf($staff, 'name')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Role / Designation</label>
            <input name="role" value="<?= e(sf($staff, 'role')) ?>" placeholder="e.g. Networking Instructor" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Phone</label>
            <input name="phone" value="<?= e(sf($staff, 'phone')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Email</label>
            <input name="email" value="<?= e(sf($staff, 'email')) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    </div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Expertise (comma separated)</label>
        <input name="expertise" value="<?= e(sf($staff, 'expertise')) ?>" placeholder="CCNA, Routing, Network Security" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Bio</label>
        <textarea name="bio" rows="3" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e(sf($staff, 'bio')) ?></textarea></div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Photo</label>
        <input type="file" name="photo" accept="image/*" class="w-full text-sm"></div>
    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
        <input type="checkbox" name="is_published" value="1" <?= (!$isEdit || $staff['is_published']) ? 'checked' : '' ?> class="rounded text-brand-600"> Show on website faculty page
    </label>
    <button class="w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700"><?= $isEdit ? 'Save changes' : 'Add staff member' ?></button>
</form>
