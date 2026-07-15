<?php /** @var array $rows @var array $triggers @var array $actionTypes @var array $batches */ ?>
<div class="grid gap-6 lg:grid-cols-[420px_1fr]">
    <!-- Builder -->
    <form action="/automations" method="POST" id="wfForm" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <input type="hidden" name="actions" id="actionsJson">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Build an Automation</h2>
        <p class="mt-1 text-sm text-slate-500">Run a series of actions automatically when something happens.</p>

        <label class="mt-4 block text-sm font-bold text-slate-700 dark:text-slate-200">Name</label>
        <input name="name" required placeholder="e.g. New student welcome" class="mt-1 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">

        <label class="mt-3 block text-sm font-bold text-slate-700 dark:text-slate-200">When this happens (trigger)</label>
        <select name="trigger" required class="mt-1 w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <?php foreach ($triggers as $k=>$label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
        </select>

        <div class="mt-4 flex items-center justify-between">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Do these actions:</label>
            <select id="addAction" onchange="addStep(this.value);this.value=''" class="rounded-lg border-slate-300 bg-white px-2 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <option value="">+ Add action…</option>
                <?php foreach ($actionTypes as $k=>$label): ?><option value="<?= e($k) ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div id="steps" class="mt-3 space-y-2"></div>

        <button class="mt-5 w-full rounded-xl bg-brand-600 py-3 font-bold text-white hover:bg-brand-700">Save Automation</button>
    </form>

    <!-- List -->
    <div>
        <h2 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Your Automations</h2>
        <?php if (empty($rows)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No automations yet. Build one on the left.</div>
        <?php else: foreach ($rows as $w): $acts = json_decode($w['actions'], true) ?: []; ?>
        <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white"><?= e($w['name']) ?></h3>
                    <p class="text-xs text-slate-500"><strong>When:</strong> <?= e($triggers[$w['trigger_event']] ?? $w['trigger_event']) ?></p>
                </div>
                <span class="rounded-full px-2.5 py-1 text-xs font-bold <?= $w['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-500' ?>"><?= $w['is_active'] ? 'Active' : 'Off' ?></span>
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <?php foreach ($acts as $i => $a): ?>
                <span class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"><?= $i+1 ?>. <?= e($actionTypes[$a['type']] ?? $a['type']) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="mt-3 flex gap-2">
                <form action="/automations/<?= (int) $w['id'] ?>/toggle" method="POST"><?= csrf_field() ?><button class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-white/15 dark:text-slate-300"><?= $w['is_active'] ? 'Turn off' : 'Turn on' ?></button></form>
                <form action="/automations/<?= (int) $w['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this automation?')"><?= csrf_field() ?><button class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40">Delete</button></form>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
const BATCHES = <?= json_encode(array_map(fn($b)=>['id'=>(int)$b['id'],'name'=>$b['name']], $batches)) ?>;
let steps=[];
function addStep(type){ if(!type) return; const base={type}; if(type==='notify'){base.title='Welcome!';base.body='Hello {name}, welcome to IT Training Institute.';} if(type==='create_fee'){base.fee_type='admission';base.title='Admission Fee';base.amount=5000;} if(type==='add_to_batch'){base.batch_id=BATCHES[0]?BATCHES[0].id:0;} steps.push(base); render(); }
function del(i){ steps.splice(i,1); render(); }
function upd(i,k,v){ steps[i][k]=v; }
function render(){
    document.getElementById('steps').innerHTML = steps.map((s,i)=>{
        let fields='';
        if(s.type==='notify') fields='<input oninput="upd('+i+',\'title\',this.value)" value="'+esc(s.title)+'" placeholder="Title" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"><textarea oninput="upd('+i+',\'body\',this.value)" placeholder="Message ({name} = student name)" class="mt-1 w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white">'+esc(s.body)+'</textarea>';
        if(s.type==='create_fee') fields='<div class="flex gap-1"><input oninput="upd('+i+',\'title\',this.value)" value="'+esc(s.title)+'" placeholder="Fee title" class="flex-1 rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"><input type="number" oninput="upd('+i+',\'amount\',+this.value)" value="'+s.amount+'" placeholder="Amount" class="w-24 rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white"></div>';
        if(s.type==='add_to_batch') fields='<select oninput="upd('+i+',\'batch_id\',+this.value)" class="w-full rounded-lg border-slate-300 bg-white px-3 py-1.5 text-xs dark:border-white/15 dark:bg-slate-800 dark:text-white">'+BATCHES.map(b=>'<option value="'+b.id+'"'+(b.id===s.batch_id?' selected':'')+'>'+esc(b.name)+'</option>').join('')+'</select>';
        const labels={notify:'Send notification',create_fee:'Create fee charge',add_to_batch:'Add to batch',generate_id:'Generate ID / reg number'};
        return '<div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-white/10 dark:bg-white/5"><div class="flex items-center justify-between"><span class="text-sm font-bold text-slate-700 dark:text-slate-200">'+(i+1)+'. '+labels[s.type]+'</span><button type="button" onclick="del('+i+')" class="text-red-500">✕</button></div>'+(fields?'<div class="mt-2">'+fields+'</div>':'')+'</div>';
    }).join('') || '<p class="text-xs text-slate-400">No actions yet - add one above.</p>';
}
function esc(s){ return (s||'').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
document.getElementById('wfForm').addEventListener('submit',()=>{ document.getElementById('actionsJson').value=JSON.stringify(steps); });
render();
</script>
