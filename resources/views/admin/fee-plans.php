<?php /** @var array $plans */ ?>
<div class="flex items-center justify-between">
    <p class="text-sm text-slate-500">Define reusable fee structures. Apply a plan to a student to auto-generate their admission + installment charges.</p>
    <a href="/fee-plans/new" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">+ New Plan</a>
</div>

<div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <?php if (empty($plans)): ?>
        <p class="text-sm text-slate-500">No fee plans yet. Create one to get started.</p>
    <?php else: foreach ($plans as $p): ?>
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="font-black text-slate-900 dark:text-white"><?= e($p['name']) ?></h3>
                <p class="text-xs text-slate-400"><?= e($p['course'] ?: 'All courses') ?></p>
            </div>
            <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $p['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' ?>"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
        </div>
        <dl class="mt-4 space-y-1.5 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">Admission</dt><dd class="font-semibold"><?= pkr($p['admission_fee']) ?></dd></div>
            <?php if ($p['security_deposit']): ?><div class="flex justify-between"><dt class="text-slate-500">Security</dt><dd class="font-semibold"><?= pkr($p['security_deposit']) ?></dd></div><?php endif; ?>
            <div class="flex justify-between"><dt class="text-slate-500">Tuition</dt><dd class="font-semibold"><?= pkr($p['tuition_fee']) ?> / <?= (int) $p['installments'] ?>x</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">Late fee</dt><dd class="font-semibold"><?= $p['late_fee_flat'] ? pkr($p['late_fee_flat']) : '' ?><?= $p['late_fee_per_day'] ? ' +' . pkr($p['late_fee_per_day']) . '/day' : '' ?><?= (!$p['late_fee_flat'] && !$p['late_fee_per_day']) ? 'None' : '' ?> <span class="text-xs text-slate-400">(<?= (int) $p['grace_days'] ?>d grace)</span></dd></div>
            <?php if ($p['early_discount_pct'] || $p['sibling_discount_pct']): ?><div class="flex justify-between"><dt class="text-slate-500">Discounts</dt><dd class="font-semibold"><?= $p['early_discount_pct'] ? $p['early_discount_pct'] . '% early' : '' ?><?= $p['sibling_discount_pct'] ? ' · ' . $p['sibling_discount_pct'] . '% sibling' : '' ?></dd></div><?php endif; ?>
        </dl>
        <?php if ($p['scholarship_note']): ?><p class="mt-3 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/5"><?= e($p['scholarship_note']) ?></p><?php endif; ?>
        <div class="mt-4 flex gap-2">
            <a href="/fee-plans/<?= (int) $p['id'] ?>/edit" class="flex-1 rounded-lg bg-slate-100 py-2 text-center text-xs font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-white">Edit</a>
            <form action="/fee-plans/<?= (int) $p['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this plan?')" class="flex-1"><?= csrf_field() ?><button class="w-full rounded-lg bg-red-50 py-2 text-xs font-bold text-red-600 hover:bg-red-100 dark:bg-red-500/10">Delete</button></form>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
