<?php /** @var array $student @var array $events @var array $notes */
$tone = ['brand'=>'bg-brand-100 text-brand-700','emerald'=>'bg-emerald-100 text-emerald-700','amber'=>'bg-amber-100 text-amber-700','red'=>'bg-red-100 text-red-700','gold'=>'bg-gold-100 text-gold-700','slate'=>'bg-slate-100 text-slate-600'];
?>
<a href="/students/<?= (int) $student['id'] ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back to student</a>

<div class="mt-4 grid gap-6 lg:grid-cols-3">
    <!-- Add note -->
    <div>
        <form action="/students/<?= (int) $student['id'] ?>/notes" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <?= csrf_field() ?>
            <h3 class="font-bold text-slate-900 dark:text-white">Add a staff note</h3>
            <textarea name="body" required rows="3" placeholder="e.g. Called parent, will resume next week." class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
            <button class="mt-3 w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Note</button>
        </form>
    </div>

    <!-- Timeline -->
    <div class="lg:col-span-2">
        <h2 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Support Timeline</h2>
        <?php if (empty($events)): ?>
            <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No activity recorded yet.</p>
        <?php else: ?>
        <div class="relative space-y-1 border-l-2 border-slate-200 pl-6 dark:border-white/10">
            <?php foreach (array_slice($events, 0, 80) as $e): ?>
            <div class="relative pb-4">
                <span class="absolute -left-[1.95rem] flex h-7 w-7 items-center justify-center rounded-full text-sm <?= $tone[$e['tone']] ?? $tone['slate'] ?>"><?= $e['icon'] ?></span>
                <p class="text-sm text-slate-700 dark:text-slate-200"><?= e($e['text']) ?></p>
                <p class="text-xs text-slate-400"><?= e(date('d M Y, g:i A', strtotime($e['t']))) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
