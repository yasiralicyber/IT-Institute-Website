<?php /** @var array $appeals */
$badge = ['open'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700'];
?>
<p class="text-sm text-slate-500">Students can challenge a published result. Review each appeal, respond, and approve or reject. Approving lets you reopen the result to correct it.</p>

<div class="mt-6 space-y-4">
    <?php if (empty($appeals)): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No appeals.</div>
    <?php else: foreach ($appeals as $a): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-black text-slate-900 dark:text-white"><?= e($a['subject']) ?></p>
                <p class="text-xs text-slate-400"><?= e($a['student']) ?><?= $a['reg_no'] ? ' · ' . e($a['reg_no']) : '' ?> · <?= e(date('d M Y', strtotime($a['created_at']))) ?></p>
            </div>
            <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge[$a['status']] ?? '' ?>"><?= ucfirst($a['status']) ?></span>
        </div>
        <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300"><?= nl2br(e($a['reason'])) ?></p>
        <?php if ($a['status'] === 'open'): ?>
        <form method="post" action="/appeals/<?= (int) $a['id'] ?>/review" class="mt-3 space-y-2">
            <?= csrf_field() ?>
            <textarea name="response" rows="2" placeholder="Response to the student (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
            <div class="flex gap-2">
                <button name="decision" value="approved" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Approve</button>
                <button name="decision" value="rejected" class="rounded-xl bg-red-600 px-5 py-2 text-sm font-bold text-white hover:bg-red-700">Reject</button>
                <?php if ($a['ref_type'] === 'result' && $a['ref_id']): ?>
                <a href="/results/<?= (int) $a['ref_id'] ?>" class="ml-auto rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-white">Open result →</a>
                <?php endif; ?>
            </div>
        </form>
        <?php elseif ($a['response']): ?>
        <p class="mt-2 text-sm text-slate-500"><strong>Response:</strong> <?= e($a['response']) ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
</div>
