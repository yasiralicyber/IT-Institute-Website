<?php /** @var array $rows */ ?>
<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No reviews yet.</div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($rows as $r):
        $badge = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','hidden'=>'bg-slate-200 text-slate-500'][$r['status']] ?? ''; ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-900 dark:text-white"><?= e($r['author']) ?></span>
                    <span class="text-amber-400"><?= str_repeat('★', (int) $r['rating']) ?></span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge ?>"><?= ucfirst($r['status']) ?></span>
                </div>
                <p class="text-sm text-brand-600"><?= e($r['course']) ?></p>
                <?php if ($r['body']): ?><p class="mt-2 text-slate-600 dark:text-slate-300">"<?= e($r['body']) ?>"</p><?php endif; ?>
            </div>
            <div class="flex gap-2">
                <?php if ($r['status'] !== 'approved'): ?>
                <form action="/reviews/<?= (int) $r['id'] ?>/status" method="POST"><?= csrf_field() ?><input type="hidden" name="status" value="approved"><button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">Approve</button></form>
                <?php endif; ?>
                <?php if ($r['status'] !== 'hidden'): ?>
                <form action="/reviews/<?= (int) $r['id'] ?>/status" method="POST"><?= csrf_field() ?><input type="hidden" name="status" value="hidden"><button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-white/15 dark:text-slate-300">Hide</button></form>
                <?php endif; ?>
                <form action="/reviews/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this review?')"><?= csrf_field() ?><button class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Delete</button></form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
