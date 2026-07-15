<?php /** @var array $events @var int $threshold */
$oc = ['allowed'=>'bg-emerald-100 text-emerald-700','flagged'=>'bg-amber-100 text-amber-700','failed'=>'bg-red-100 text-red-700','blocked'=>'bg-red-200 text-red-800'];
?>
<p class="text-sm text-slate-500">Every login is scored on device, IP, time-of-day and recent failures. Logins at or above <strong><?= (int) $threshold ?></strong> are flagged and admins are alerted.</p>

<div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">When</th><th class="px-5 py-3">Account</th><th class="px-5 py-3">IP</th><th class="px-5 py-3 text-center">Risk</th><th class="px-5 py-3">Signals</th><th class="px-5 py-3">Outcome</th></tr>
        </thead>
        <tbody>
        <?php if (empty($events)): ?>
            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">No login events yet.</td></tr>
        <?php else: foreach ($events as $e): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3 text-xs text-slate-400"><?= e(date('d M, g:i A', strtotime($e['created_at']))) ?></td>
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($e['student'] ?: $e['email']) ?></td>
                <td class="px-5 py-3 font-mono text-xs text-slate-500"><?= e($e['ip']) ?></td>
                <td class="px-5 py-3 text-center"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= (int) $e['risk'] >= $threshold ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' ?>"><?= (int) $e['risk'] ?></span></td>
                <td class="px-5 py-3 text-xs text-slate-500"><?= e($e['reasons'] ?: '-') ?></td>
                <td class="px-5 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $oc[$e['outcome']] ?? '' ?>"><?= ucfirst($e['outcome']) ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
