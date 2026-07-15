<?php
/** @var array $lecture @var array $markers @var array $questions */
function hms($s){ $s=(int)$s; return $s>=3600?sprintf('%d:%02d:%02d',intdiv($s,3600),intdiv($s%3600,60),$s%60):sprintf('%d:%02d',intdiv($s,60),$s%60); }
?>
<a href="/courses/<?= (int) $lecture['course_id'] ?>/edit" class="text-sm font-semibold text-brand-600 hover:underline">← Back to course</a>
<p class="mt-1 text-sm text-slate-500">Lesson: <strong class="text-slate-800 dark:text-slate-200"><?= e($lecture['title']) ?></strong>. Timestamps accept <code>90</code>, <code>1:30</code> or <code>1:05:00</code>. Works on your uploaded / self-hosted videos.</p>

<form action="/lectures/<?= (int) $lecture['id'] ?>/interactive" method="POST" class="mt-5 space-y-8">
    <?= csrf_field() ?>

    <!-- Chapter markers -->
    <div>
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Chapter Markers</h2>
            <button type="button" onclick="addMarker()" class="rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-bold text-white dark:bg-white/10">+ Marker</button>
        </div>
        <div id="markers" class="space-y-2"></div>
    </div>

    <!-- In-video questions -->
    <div>
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">In-Video Questions</h2>
            <button type="button" onclick="addQ()" class="rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-bold text-white dark:bg-white/10">+ Question</button>
        </div>
        <p class="mb-2 text-xs text-slate-400">The video pauses at the timestamp and the student must answer before continuing.</p>
        <div id="questions" class="space-y-3"></div>
    </div>

    <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700">Save Interactive Content</button>
</form>

<template id="markerTpl">
    <div class="mrow flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
        <input name="m[IDX][time]" placeholder="1:30" class="w-24 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <input name="m[IDX][label]" placeholder="Chapter title" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <button type="button" onclick="this.closest('.mrow').remove()" class="text-red-500">✕</button>
    </div>
</template>
<template id="qTpl">
    <div class="qrow rounded-xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
        <div class="flex items-center gap-2">
            <input name="q[IDX][time]" placeholder="2:00" class="w-24 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="q[IDX][text]" placeholder="Question shown at this time" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <button type="button" onclick="this.closest('.qrow').remove()" class="text-red-500">✕</button>
        </div>
        <div class="mt-2 grid gap-2 sm:grid-cols-2">
            <?php for ($i=0;$i<4;$i++): ?>
            <label class="flex items-center gap-2">
                <input type="radio" name="q[IDX][correct]" value="<?= $i ?>" <?= $i===0?'checked':'' ?> class="text-brand-600">
                <input name="q[IDX][options][]" placeholder="Option <?= $i+1 ?>" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </label>
            <?php endfor; ?>
        </div>
    </div>
</template>

<script>
let mi=0,qi=0;
function addMarker(d){ const h=document.getElementById('markerTpl').innerHTML.replace(/IDX/g,mi++); const w=document.createElement('div'); w.innerHTML=h; const el=w.firstElementChild; if(d){ el.querySelector('[name$="[time]"]').value=d.time; el.querySelector('[name$="[label]"]').value=d.label; } document.getElementById('markers').appendChild(el); }
function addQ(d){ const h=document.getElementById('qTpl').innerHTML.replace(/IDX/g,qi++); const w=document.createElement('div'); w.innerHTML=h; const el=w.firstElementChild; if(d){ el.querySelector('[name$="[time]"]').value=d.time; el.querySelector('[name$="[text]"]').value=d.text; const opts=el.querySelectorAll('[name$="[options][]"]'); d.options.forEach((o,i)=>{ if(opts[i]) opts[i].value=o; }); const r=el.querySelectorAll('input[type=radio]'); if(r[d.correct]) r[d.correct].checked=true; } document.getElementById('questions').appendChild(el); }
<?php foreach ($markers as $m): ?>addMarker({time:'<?= hms($m['seconds']) ?>',label:<?= json_encode($m['label']) ?>});<?php endforeach; ?>
<?php foreach ($questions as $q): ?>addQ({time:'<?= hms($q['seconds']) ?>',text:<?= json_encode($q['question']) ?>,options:<?= $q['options'] ?>,correct:<?= (int)$q['correct_index'] ?>});<?php endforeach; ?>
</script>
