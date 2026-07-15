<?php /** @var array $sections @var array $recent */ ?>
<div class="grid gap-6 lg:grid-cols-3">
    <!-- Import form -->
    <div class="lg:col-span-2">
        <form action="/imports/start" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900" x-data>
            <?= csrf_field() ?>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Step 1 - What are you importing?</h2>
            <select name="section" required class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">Choose a section…</option>
                <?php foreach ($sections as $key => $def): ?><option value="<?= e($key) ?>"><?= e($def['label']) ?></option><?php endforeach; ?>
            </select>

            <h2 class="mt-6 text-lg font-bold text-slate-900 dark:text-white">Step 2 - Provide the data</h2>
            <div class="mt-3 flex gap-2 text-sm" id="srcTabs">
                <button type="button" data-src="file" onclick="srcTab('file')" class="src-tab rounded-lg bg-brand-600 px-4 py-2 font-bold text-white">Upload file</button>
                <button type="button" data-src="paste" onclick="srcTab('paste')" class="src-tab rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Paste</button>
                <button type="button" data-src="google" onclick="srcTab('google')" class="src-tab rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Google Sheet</button>
            </div>
            <input type="hidden" name="source" id="sourceInput" value="file">

            <div data-pane="file" class="mt-4">
                <input type="file" name="file" accept=".csv,.xlsx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:font-bold file:text-white dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <p class="mt-1 text-xs text-slate-400">CSV or Excel (.xlsx). First row must be column headings.</p>
            </div>
            <div data-pane="paste" class="mt-4 hidden">
                <textarea name="paste" rows="6" placeholder="Paste rows from Excel/Sheets (tab or comma separated). First row = headings." class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
            </div>
            <div data-pane="google" class="mt-4 hidden">
                <input name="sheet_url" placeholder="https://docs.google.com/spreadsheets/d/.../edit" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <p class="mt-1 text-xs text-slate-400">Share the sheet as "Anyone with the link" first.</p>
            </div>

            <button class="mt-6 w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700">Continue to Mapping →</button>
        </form>

        <!-- Recent imports -->
        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="mb-3 flex items-center justify-between"><h3 class="font-bold text-slate-900 dark:text-white">Recent Imports</h3><a href="/imports/history" class="text-sm font-bold text-brand-600">View all</a></div>
            <?php if (empty($recent)): ?><p class="text-sm text-slate-500">No imports yet.</p>
            <?php else: foreach ($recent as $r): ?>
            <a href="/imports/<?= (int) $r['id'] ?><?= $r['status'] === 'completed' || $r['status'] === 'rolled_back' ? '/result' : '' ?>" class="mb-2 flex items-center justify-between rounded-xl border border-slate-100 px-4 py-2 text-sm dark:border-white/5">
                <span><span class="font-semibold text-slate-800 dark:text-slate-200"><?= e(ucfirst($r['section'])) ?></span> <span class="text-xs text-slate-400"><?= e($r['filename']) ?></span></span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600 dark:bg-white/10"><?= e($r['status']) ?></span>
            </a>
            <?php endforeach; endif; ?>
        </div>
    </div>

    <!-- Templates & export -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">Templates & Export</h3>
        <p class="mt-1 text-xs text-slate-400">Download a ready-made template, or export current data to edit and re-import.</p>
        <div class="mt-3 space-y-2">
            <?php foreach ($sections as $key => $def): ?>
            <div class="flex items-center justify-between rounded-xl border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                <span class="font-semibold text-slate-700 dark:text-slate-200"><?= e($def['label']) ?></span>
                <span class="flex gap-2">
                    <a href="/imports/template/<?= e($key) ?>" class="text-xs font-bold text-brand-600 hover:underline">Template</a>
                    <a href="/imports/export/<?= e($key) ?>" class="text-xs font-bold text-slate-500 hover:underline">Export</a>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function srcTab(s){
    document.getElementById('sourceInput').value=s;
    document.querySelectorAll('.src-tab').forEach(b=>{ const on=b.dataset.src===s; b.className='src-tab rounded-lg px-4 py-2 font-bold '+(on?'bg-brand-600 text-white':'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300'); });
    document.querySelectorAll('[data-pane]').forEach(p=>p.classList.toggle('hidden', p.dataset.pane!==s));
}
</script>
