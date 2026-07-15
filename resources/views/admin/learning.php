<?php /** @var array $blocks @var array $courses @var int $revisionWeeks */
$inp = 'w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white';
?>
<p class="text-sm text-slate-500">Reusable content blocks, per-lesson expiry &amp; acknowledgment rules, and the knowledge-decay revision interval. Multi-path placement is set on a chapter test (Courses → test → "Placement test").</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <!-- Reusable blocks library -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white">Reusable Learning Blocks</h3>
        <p class="text-xs text-slate-400">Create once, attach to many lessons. Render inside the player.</p>
        <form method="post" action="/learning/block" class="mt-3 space-y-2">
            <?= csrf_field() ?>
            <div class="grid grid-cols-3 gap-2">
                <input name="title" required placeholder="Title" class="col-span-2 <?= $inp ?>">
                <select name="type" class="<?= $inp ?>"><?php foreach (['note','tip','warning','code'] as $t): ?><option value="<?= $t ?>"><?= ucfirst($t) ?></option><?php endforeach; ?></select>
            </div>
            <textarea name="body" rows="3" required placeholder="Block content" class="<?= $inp ?>"></textarea>
            <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Add Block</button>
        </form>
        <div class="mt-4 space-y-2">
            <?php if (empty($blocks)): ?><p class="text-sm text-slate-500">No blocks yet.</p>
            <?php else: foreach ($blocks as $b): ?>
            <div class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                <div><p class="font-semibold text-slate-800 dark:text-slate-200">#<?= (int) $b['id'] ?> · <?= e($b['title']) ?></p><p class="text-xs text-slate-400"><?= ucfirst($b['type']) ?> · used in <?= (int) $b['uses'] ?> lesson(s)</p></div>
                <form method="post" action="/learning/block/<?= (int) $b['id'] ?>/delete" onsubmit="return confirm('Delete block?')"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">Delete</button></form>
            </div>
            <?php endforeach; endif; ?>
        </div>
        <form method="post" action="/learning/attach" class="mt-4 border-t border-slate-100 pt-3 dark:border-white/5">
            <?= csrf_field() ?>
            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Attach a block to a lesson</p>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <select name="block_id" class="<?= $inp ?>"><option value="">Block…</option><?php foreach ($blocks as $b): ?><option value="<?= (int) $b['id'] ?>"><?= e($b['title']) ?></option><?php endforeach; ?></select>
                <input name="lecture_id" type="number" placeholder="Lecture ID" class="<?= $inp ?>">
            </div>
            <button class="mt-2 rounded-xl bg-slate-800 px-5 py-2 text-sm font-bold text-white dark:bg-white/10">Attach</button>
        </form>
    </div>

    <div class="space-y-6">
        <!-- Per-lesson rules -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="font-black text-slate-900 dark:text-white">Lesson Rules (expiry &amp; acknowledgment)</h3>
            <form method="post" action="/learning/lecture-rules" class="mt-3 space-y-2">
                <?= csrf_field() ?>
                <input name="lecture_id" type="number" required placeholder="Lecture ID" class="<?= $inp ?>">
                <label class="block text-xs font-semibold text-slate-500">Content expires on (optional)</label>
                <input name="expires_at" type="date" class="<?= $inp ?>">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300"><input type="checkbox" name="requires_ack" value="1" class="rounded border-slate-300"> Require acknowledgment to complete</label>
                <textarea name="ack_text" rows="2" placeholder="Acknowledgment text (e.g. safety/policy statement)" class="<?= $inp ?>"></textarea>
                <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Save Lesson Rules</button>
            </form>
        </div>

        <!-- Knowledge-decay revision interval -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="font-black text-slate-900 dark:text-white">Knowledge-Decay Revision</h3>
            <p class="text-xs text-slate-400">After a student passes a chapter test, a revision test becomes due this many weeks later.</p>
            <form method="post" action="/learning/revision" class="mt-3 flex items-center gap-2">
                <?= csrf_field() ?>
                <input name="revision_weeks" type="number" min="1" value="<?= (int) $revisionWeeks ?>" class="w-28 <?= $inp ?>">
                <span class="text-sm text-slate-500">weeks</span>
                <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Save</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="font-black text-slate-900 dark:text-white">Acknowledgment Register</h3>
            <p class="text-xs text-slate-400">See who has confirmed required lessons.</p>
            <a href="/acknowledgments" class="mt-2 inline-block rounded-xl bg-slate-100 px-5 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-white">Open register →</a>
        </div>
    </div>
</div>
