<?php /** @var array $requests @var array $students @var int $me
 * @var array $actions */
use App\Controllers\Admin\SecurityController;
$actions = SecurityController::actions();
$badge = ['pending'=>'bg-amber-100 text-amber-700','executed'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700'];
?>
<p class="text-sm text-slate-500">High-impact actions need a second administrator to approve before they run. The approver must be a different admin than the requester.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <!-- Request a sensitive action -->
    <form method="post" action="/approvals/request" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h3 class="font-black text-slate-900 dark:text-white">Request an action</h3>
        <select name="action" class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <?php foreach ($actions as $k => $v): ?><option value="<?= e($k) ?>"><?= e($v) ?></option><?php endforeach; ?>
        </select>
        <select name="target_id" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <option value="">Target student…</option>
            <?php foreach ($students as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?><?= $s['reg_no'] ? ' (' . e($s['reg_no']) . ')' : '' ?></option><?php endforeach; ?>
        </select>
        <button class="mt-3 w-full rounded-xl bg-amber-600 py-2.5 text-sm font-bold text-white hover:bg-amber-700">Submit for approval</button>
    </form>

    <!-- Queue -->
    <div class="lg:col-span-2 space-y-3">
        <?php if (empty($requests)): ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No requests.</div>
        <?php else: foreach ($requests as $r): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-black text-slate-900 dark:text-white"><?= e($r['summary']) ?></p>
                    <p class="text-xs text-slate-400">Requested by <?= e($r['requester'] ?: $r['requested_name']) ?> · <?= e(date('d M Y, g:i A', strtotime($r['created_at']))) ?><?php if ($r['approver']): ?> · decided by <?= e($r['approver']) ?><?php endif; ?></p>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge[$r['status']] ?? '' ?>"><?= ucfirst($r['status']) ?></span>
            </div>
            <?php if ($r['status'] === 'pending'): ?>
                <?php if ((int) $r['requested_by'] === $me): ?>
                    <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/5">You requested this. A different administrator must approve it.</p>
                <?php else: ?>
                <form method="post" action="/approvals/<?= (int) $r['id'] ?>/decide" class="mt-3 flex flex-wrap gap-2" onsubmit="return confirm('Confirm this decision?')">
                    <?= csrf_field() ?>
                    <input name="note" placeholder="Note (optional)" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <button name="decision" value="approve" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">Approve &amp; Execute</button>
                    <button name="decision" value="reject" class="rounded-xl bg-red-600 px-5 py-2 text-sm font-bold text-white hover:bg-red-700">Reject</button>
                </form>
                <?php endif; ?>
            <?php elseif ($r['decision_note']): ?>
                <p class="mt-2 text-sm text-slate-500"><strong>Note:</strong> <?= e($r['decision_note']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
