<?php /** @var array $students @var string $q @var string $filter @var array $waNumbers */
$inst = config('institute');
$filters = ['' => 'All', 'enrolled' => 'Enrolled', 'not_enrolled' => 'Not Enrolled', 'fee_due' => 'Fee Due', 'fee_clear' => 'Fee Clear', 'no_payment' => 'No Payment'];
?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <!-- Filter tabs -->
    <div class="flex flex-wrap gap-2">
        <?php foreach ($filters as $k => $label): ?>
        <a href="/students?filter=<?= $k ?><?= $q ? '&q=' . urlencode($q) : '' ?>" class="rounded-xl px-4 py-2 text-sm font-bold <?= $filter === $k ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
    <a href="/students/id-cards" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-brand-700 px-4 py-2 text-sm font-bold text-white hover:bg-brand-800"><?= icon('users','h-4 w-4') ?> Bulk Print ID Cards</a>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input type="hidden" name="filter" value="<?= e($filter) ?>">
    <input name="q" value="<?= e($q) ?>" placeholder="Search by name, email or phone..." class="flex-1 rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
    <button class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Search</button>
</form>

<!-- Bulk WhatsApp -->
<div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300"><strong><?= count($waNumbers) ?></strong> students with a phone in this filter (<?= e($filters[$filter] ?? 'All') ?>). Contact them all on WhatsApp:</p>
        <div class="flex gap-2">
            <button type="button" onclick="copyNumbers()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Copy all numbers</button>
            <button type="button" onclick="document.getElementById('waMsg').classList.toggle('hidden')" class="rounded-xl border border-emerald-300 px-4 py-2 text-sm font-bold text-emerald-700 dark:border-emerald-500/40 dark:text-emerald-300">Open WhatsApp one-by-one</button>
        </div>
    </div>
    <div id="waMsg" class="mt-3 hidden">
        <textarea id="bulkMsg" rows="2" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">Assalam-o-Alaikum, this is IT Training Institute Kumber Maidan. </textarea>
        <p class="mt-2 text-xs text-emerald-700 dark:text-emerald-300">For a true single broadcast, click <strong>Copy all numbers</strong>, then in WhatsApp create a <strong>Broadcast list</strong> and paste them. Or open each chat below with your message pre-filled.</p>
        <button type="button" onclick="openEach()" class="mt-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white">Open chats with this message</button>
    </div>
    <textarea id="numbersData" class="hidden"><?= e(implode("\n", $waNumbers)) ?></textarea>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
            <tr><th class="px-5 py-3">Student</th><th class="px-5 py-3">Courses</th><th class="px-5 py-3">Fee</th><th class="px-5 py-3">Status</th><th class="px-5 py-3"></th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
            <?php if (empty($students)): ?>
                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No students match this filter.</td></tr>
            <?php else: foreach ($students as $s): $digits = preg_replace('/\D/', '', (string) $s['phone']); $wa = $digits ? (str_starts_with($digits,'0') ? '92'.substr($digits,1) : $digits) : ''; ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                <td class="px-5 py-3">
                    <p class="font-bold text-slate-900 dark:text-white"><?= e($s['name']) ?></p>
                    <p class="text-xs text-slate-500"><?= e($s['email']) ?> · <?= e($s['phone'] ?: '-') ?></p>
                </td>
                <td class="px-5 py-3"><?= (int) $s['enrolls'] ?></td>
                <td class="px-5 py-3"><?php if ($s['balance'] > 0): ?><span class="font-bold text-red-600"><?= pkr($s['balance']) ?> due</span><?php elseif ($s['billed'] > 0): ?><span class="text-emerald-600">Clear</span><?php else: ?><span class="text-slate-400">-</span><?php endif; ?></td>
                <td class="px-5 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $s['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($s['status']) ?></span></td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <?php if ($wa): ?><a href="https://wa.me/<?= e($wa) ?>" target="_blank" class="rounded-lg bg-[#25D366] px-3 py-1.5 text-xs font-bold text-white">WhatsApp</a><?php endif; ?>
                        <a href="/students/<?= (int) $s['id'] ?>" class="font-bold text-brand-600 hover:underline">Manage →</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
function copyNumbers(){ const t=document.getElementById('numbersData').value.trim(); navigator.clipboard.writeText(t).then(()=>alert('Copied '+t.split('\n').filter(Boolean).length+' numbers. Paste them into a WhatsApp Broadcast list.')); }
function openEach(){ const nums=document.getElementById('numbersData').value.trim().split('\n').filter(Boolean); const msg=encodeURIComponent(document.getElementById('bulkMsg').value); if(nums.length>15 && !confirm('This will try to open '+nums.length+' WhatsApp tabs. Continue?')) return; nums.forEach((n,i)=>setTimeout(()=>window.open('https://wa.me/'+n+'?text='+msg,'_blank'),i*600)); }
</script>
