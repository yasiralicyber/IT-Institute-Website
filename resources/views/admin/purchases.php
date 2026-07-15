<?php /** @var array $rows @var string $status @var array $counts */ ?>
<!-- Status tabs -->
<div class="mb-5 flex gap-2">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'declined' => 'Declined'] as $s => $label): ?>
    <a href="/purchases?status=<?= $s ?>" class="rounded-xl px-4 py-2 text-sm font-bold <?= $status === $s ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300' ?>">
        <?= $label ?> <span class="ml-1 rounded-full bg-black/10 px-1.5 text-xs"><?= $counts[$s] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No <?= e($status) ?> requests.</div>
<?php else: ?>
<div class="space-y-4">
    <?php foreach ($rows as $r): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="/receipt/<?= (int) $r['id'] ?>" target="_blank" class="group relative block h-20 w-20 flex-none overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-white/10">
                    <img src="/receipt/<?= (int) $r['id'] ?>" alt="Receipt" class="h-full w-full object-cover" onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'flex h-full w-full items-center justify-center text-2xl',innerText:''}))">
                    <span class="absolute inset-0 hidden items-center justify-center bg-black/50 text-xs font-bold text-white group-hover:flex">View</span>
                </a>
                <div>
                    <p class="font-bold text-slate-900 dark:text-white"><?= e($r['student']) ?> <span class="text-sm font-normal text-slate-400">· <?= e($r['email']) ?></span></p>
                    <p class="text-sm text-brand-600"><?= e($r['course']) ?></p>
                    <p class="mt-1 text-xs text-slate-500">Fee: <?= pkr($r['amount']) ?> <?php if ($r['reference_no']): ?>· Ref: <?= e($r['reference_no']) ?><?php endif; ?> · <?= e(date('d M Y, g:i A', strtotime($r['created_at']))) ?></p>
                    <?php if ($r['status'] === 'declined' && $r['admin_note']): ?><p class="mt-1 text-xs font-semibold text-red-500">Reason: <?= e($r['admin_note']) ?></p><?php endif; ?>
                </div>
            </div>

            <?php if ($r['status'] === 'pending'): ?>
            <div class="flex gap-2">
                <form action="/purchases/<?= (int) $r['id'] ?>/approve" method="POST" onsubmit="return confirm('Approve and unlock this course for the student?')">
                    <?= csrf_field() ?>
                    <button class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">✓ Approve & Unlock</button>
                </form>
                <button onclick="document.getElementById('decline-<?= (int) $r['id'] ?>').classList.toggle('hidden')" class="rounded-xl border border-red-300 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:hover:bg-red-500/10">Decline</button>
            </div>
            <?php else: ?>
            <span class="rounded-full px-3 py-1.5 text-xs font-bold <?= $r['status'] === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($r['status']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($r['status'] === 'pending'): ?>
        <form id="decline-<?= (int) $r['id'] ?>" action="/purchases/<?= (int) $r['id'] ?>/decline" method="POST" class="mt-4 hidden">
            <?= csrf_field() ?>
            <div class="flex gap-2">
                <input name="reason" placeholder="Reason for declining (shown to student)" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <button class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Confirm Decline</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
