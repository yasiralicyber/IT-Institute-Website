<?php /** @var array $rows */ ?>
<div class="grid gap-6 lg:grid-cols-[340px_1fr]">
    <form action="/classrooms" method="POST" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Add Classroom</h2>
        <input name="name" required placeholder="Room name (e.g. Networking Lab)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <input name="capacity" type="number" placeholder="Capacity" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <input name="location" placeholder="Location (e.g. Ground Floor)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <input name="notes" placeholder="Notes (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Add Room</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">Room</th><th class="px-5 py-3">Capacity</th><th class="px-5 py-3">Location</th><th class="px-5 py-3">Batches</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">No classrooms yet.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td class="px-5 py-3 font-bold text-slate-900 dark:text-white"><?= e($r['name']) ?></td>
                    <td class="px-5 py-3"><?= (int) $r['capacity'] ?></td>
                    <td class="px-5 py-3 text-slate-500"><?= e($r['location'] ?: '-') ?></td>
                    <td class="px-5 py-3"><?= (int) $r['batches'] ?></td>
                    <td class="px-5 py-3 text-right">
                        <form action="/classrooms/<?= (int) $r['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this classroom?')" class="inline">
                            <?= csrf_field() ?>
                            <button class="text-sm font-bold text-red-500 hover:text-red-700">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
