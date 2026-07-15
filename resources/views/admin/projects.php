<?php /** @var array $rows */ ?>
<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No projects submitted yet.</div>
<?php else: ?>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($rows as $p):
        $badge = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700'][$p['status']] ?? ''; ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <?php if ($p['image']): ?><img src="/project-image/<?= (int) $p['id'] ?>" alt="" class="h-36 w-full object-cover"><?php endif; ?>
        <div class="p-4">
            <div class="flex items-start justify-between gap-2">
                <div><h3 class="font-bold text-slate-900 dark:text-white"><?= e($p['title']) ?></h3><p class="text-xs text-slate-500">by <?= e($p['author']) ?> · <?= e($p['type']) ?></p></div>
                <span class="flex-none rounded-full px-2 py-0.5 text-[10px] font-bold <?= $badge ?>"><?= ucfirst($p['status']) ?><?= $p['featured'] ? ' ★' : '' ?></span>
            </div>
            <?php if ($p['description']): ?><p class="mt-2 line-clamp-2 text-sm text-slate-500"><?= e($p['description']) ?></p><?php endif; ?>
            <?php if ($p['link']): ?><a href="<?= e($p['link']) ?>" target="_blank" class="mt-1 inline-block text-xs font-bold text-brand-600">Open link →</a><?php endif; ?>
            <div class="mt-3 flex flex-wrap gap-2">
                <?php if ($p['status'] !== 'approved'): ?>
                <form action="/projects/<?= (int) $p['id'] ?>/status" method="POST"><?= csrf_field() ?><input type="hidden" name="status" value="approved"><button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve</button></form>
                <?php endif; ?>
                <?php if ($p['status'] === 'approved'): ?>
                <form action="/projects/<?= (int) $p['id'] ?>/feature" method="POST"><?= csrf_field() ?><button class="rounded-lg bg-gold-500 px-3 py-1.5 text-xs font-bold text-brand-950 hover:bg-gold-400"><?= $p['featured'] ? 'Unfeature' : 'Feature' ?></button></form>
                <?php endif; ?>
                <button onclick="document.getElementById('rej-<?= (int) $p['id'] ?>').classList.toggle('hidden')" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Reject</button>
                <form action="/projects/<?= (int) $p['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete project?')"><?= csrf_field() ?><button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-500 dark:border-white/15">Delete</button></form>
            </div>
            <form id="rej-<?= (int) $p['id'] ?>" action="/projects/<?= (int) $p['id'] ?>/status" method="POST" class="mt-2 hidden">
                <?= csrf_field() ?><input type="hidden" name="status" value="rejected">
                <div class="flex gap-2"><input name="note" placeholder="Reason (shown to student)" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"><button class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white">Confirm</button></div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
