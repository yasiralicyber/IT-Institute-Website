<?php /** @var array $tokens @var array $hits */ ?>
<p class="text-sm text-slate-500">Honeytokens are decoy URLs that no real user should ever open. Any hit is logged and alerts every admin - an early warning of scraping or a breach. Plant the decoy URL where only a snooper would find it.</p>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white">Decoys</h3>
        <form method="post" action="/honeytokens" class="mt-3 flex gap-2">
            <?= csrf_field() ?>
            <input name="label" required placeholder="Label (e.g. Fake student export)" class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Create</button>
        </form>
        <div class="mt-4 space-y-2">
            <?php if (empty($tokens)): ?><p class="text-sm text-slate-500">No honeytokens yet.</p>
            <?php else: foreach ($tokens as $t): ?>
            <div class="rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-800 dark:text-slate-200"><?= e($t['label']) ?></p>
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= (int) $t['hits'] > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500' ?>"><?= (int) $t['hits'] ?> hit(s)</span>
                        <form method="post" action="/honeytokens/<?= (int) $t['id'] ?>/delete" onsubmit="return confirm('Delete?')"><?= csrf_field() ?><button class="text-xs text-red-400 hover:text-red-600">✕</button></form>
                    </div>
                </div>
                <p class="mt-1 break-all font-mono text-xs text-brand-600"><?= e(abs_url('/internal/export/' . $t['token'])) ?></p>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-black text-slate-900 dark:text-white">Recent hits</h3>
        <?php if (empty($hits)): ?><p class="mt-2 text-sm text-slate-500">No hits - all clear.</p>
        <?php else: foreach ($hits as $h): ?>
        <div class="mt-2 border-b border-slate-100 py-1.5 text-sm last:border-0 dark:border-white/5">
            <p class="font-semibold text-red-600"><?= e($h['label']) ?> · <?= e($h['ip']) ?></p>
            <p class="text-xs text-slate-400"><?= e(date('d M Y, g:i A', strtotime($h['created_at']))) ?> · <?= e(substr((string) $h['ua'], 0, 60)) ?></p>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
