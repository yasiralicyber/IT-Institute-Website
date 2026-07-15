<?php /** @var array $batch */ ?>
<a href="/attendance/<?= (int) $batch['id'] ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back</a>

<div class="mt-4 grid gap-6 lg:grid-cols-2">
    <form action="/attendance/<?= (int) $batch['id'] ?>/import" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Import from Fingerprint Device</h2>
        <p class="mt-1 text-sm text-slate-500">Upload the CSV exported from your biometric device for <strong><?= e($batch['name']) ?></strong>.</p>
        <input type="file" name="csv" accept=".csv,text/csv" required class="mt-5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:font-bold file:text-white dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button class="mt-5 w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700">Import Attendance</button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">CSV format</h3>
        <p class="mt-2 text-sm text-slate-500">Your file's first row must be column headings. We match each student by their <strong>roll_no</strong> or <strong>email</strong>.</p>
        <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-900 p-4 text-xs text-emerald-300">roll_no,date,status
R-001,2026-07-01,present
R-002,2026-07-01,late
R-003,2026-07-01,absent</pre>
        <ul class="mt-3 space-y-1 text-xs text-slate-500">
            <li>• <strong>date</strong> format: YYYY-MM-DD</li>
            <li>• <strong>status</strong>: present, late, or absent</li>
            <li>• You can use <strong>email</strong> instead of roll_no.</li>
        </ul>
        <a href="/attendance-guide" class="mt-4 inline-block font-bold text-brand-600 hover:underline">How to connect the device →</a>
    </div>
</div>
