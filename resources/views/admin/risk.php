<?php /** @var array $rows */ ?>
<div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
    These students may need a friendly check-in. This list <strong>recommends follow-up</strong> - it never restricts the student automatically.
</div>

<?php if (empty($rows)): ?>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-10 text-center text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10">Everyone's on track - no students flagged.</div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($rows as $r): ?>
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <a href="/students/<?= (int) $r['id'] ?>" class="font-bold text-slate-900 hover:text-brand-700 dark:text-white"><?= e($r['name']) ?></a>
                <?php if ($r['severity'] >= 3): ?><span class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">High</span>
                <?php elseif ($r['severity'] == 2): ?><span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">Medium</span>
                <?php endif; ?>
            </div>
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                <?php foreach ($r['reasons'] as $reason): ?><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300"><?= e($reason) ?></span><?php endforeach; ?>
            </div>
        </div>
        <div class="flex gap-2">
            <?php if ($r['phone']): ?><a href="https://wa.me/<?= e(preg_replace('/\D/', '', $r['phone'])) ?>" target="_blank" class="rounded-lg bg-[#25D366] px-3 py-2 text-xs font-bold text-white">WhatsApp</a><?php endif; ?>
            <a href="/students/<?= (int) $r['id'] ?>/timeline" class="rounded-lg bg-brand-50 px-3 py-2 text-xs font-bold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">Timeline</a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
