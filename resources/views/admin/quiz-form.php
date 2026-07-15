<?php
/** @var array $chapter @var array $quiz @var array $questions */
?>
<a href="/courses/<?= (int) $quiz['course_id'] ?>/edit" class="text-sm font-semibold text-brand-600 hover:underline">← Back to course</a>

<form action="/chapters/<?= (int) $chapter['id'] ?>/quiz" method="POST" class="mt-4">
    <?= csrf_field() ?>
    <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Pass mark (%)</label>
                <input name="pass_percent" type="number" min="1" max="100" value="<?= (int) $quiz['pass_percent'] ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Max attempts</label>
                <input name="max_attempts" type="number" min="1" value="<?= (int) $quiz['max_attempts'] ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 dark:border-white/10">
                <input type="checkbox" name="secure_exam" value="1" <?= !empty($quiz['secure_exam']) ? 'checked' : '' ?> class="rounded border-slate-300 text-brand-600">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Locked exam mode <span class="block text-xs font-normal text-slate-400">Full-screen, tab-switch logging, copy/paste blocked</span></span>
            </label>
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Max violations before auto-submit</label>
                <input name="max_violations" type="number" min="1" value="<?= (int) ($quiz['max_violations'] ?? 3) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 dark:border-white/10">
                <input type="checkbox" name="is_placement" value="1" <?= !empty($quiz['is_placement']) ? 'checked' : '' ?> class="rounded border-slate-300 text-brand-600">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Placement test <span class="block text-xs font-normal text-slate-400">Passing tests the student out of earlier chapters</span></span>
            </label>
            <div><label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Chapters to skip on pass</label>
                <input name="placement_skips" type="number" min="0" value="<?= (int) ($quiz['placement_skips'] ?? 0) ?>" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>
        </div>
        <p class="mt-3 text-sm text-slate-500">Students must score the pass mark to unlock the next chapter and earn a chapter certificate.</p>
    </div>

    <div id="questions" class="space-y-4"></div>

    <button type="button" onclick="addQuestion()" class="mt-4 w-full rounded-xl border-2 border-dashed border-slate-300 py-3 text-sm font-bold text-slate-600 hover:border-brand-400 hover:text-brand-600 dark:border-white/15 dark:text-slate-300">+ Add question</button>
    <button class="mt-4 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700">Save Test</button>
</form>

<template id="q-template">
    <div class="q-card rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center justify-between">
            <p class="text-sm font-bold text-slate-400">Question <span class="q-num"></span></p>
            <button type="button" onclick="this.closest('.q-card').remove();renumber()" class="text-sm font-bold text-red-500 hover:text-red-700">Remove</button>
        </div>
        <input name="QPREFIX[text]" required placeholder="Enter the question" class="mt-3 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <p class="mt-3 text-xs font-semibold text-slate-500">Options (select the correct one):</p>
        <div class="mt-2 space-y-2">
            <?php for ($i = 0; $i < 4; $i++): ?>
            <label class="flex items-center gap-3">
                <input type="radio" name="QPREFIX[correct]" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> class="text-brand-600 focus:ring-brand-500">
                <input name="QPREFIX[options][]" placeholder="Option <?= $i + 1 ?>" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </label>
            <?php endfor; ?>
        </div>
    </div>
</template>

<script>
let qi = 0;
function addQuestion(data){
    const tpl = document.getElementById('q-template').innerHTML.replace(/QPREFIX/g, 'q['+qi+']');
    const wrap = document.createElement('div');
    wrap.innerHTML = tpl;
    const card = wrap.firstElementChild;
    if (data){
        card.querySelector('input[name$="[text]"]').value = data.text;
        const opts = card.querySelectorAll('input[name$="[options][]"]');
        data.options.forEach((o,i)=>{ if(opts[i]) opts[i].value = o; });
        const radios = card.querySelectorAll('input[type=radio]');
        if (radios[data.correct]) radios[data.correct].checked = true;
    }
    document.getElementById('questions').appendChild(card);
    qi++; renumber();
}
function renumber(){ document.querySelectorAll('#questions .q-num').forEach((el,i)=>el.textContent = i+1); }
// Load existing questions.
const existing = <?= json_encode(array_map(fn($q) => [
    'text' => $q['question'],
    'options' => json_decode($q['options'], true) ?: [],
    'correct' => (int) $q['correct_index'],
], $questions)) ?>;
if (existing.length) existing.forEach(addQuestion); else addQuestion();
</script>
