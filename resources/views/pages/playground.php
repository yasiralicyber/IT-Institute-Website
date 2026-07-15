<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-12 text-center sm:px-6">
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Practice</span>
        <h1 class="mt-2 text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Coding Playground</h1>
        <p class="mx-auto mt-3 max-w-2xl text-slate-600 dark:text-slate-300">Write and run code right in your browser - no installation needed. Great for practising what you learn in our HTML, Python, C++ and Java courses.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <!-- language tabs -->
    <div class="mb-4 flex flex-wrap gap-2" id="langTabs">
        <?php foreach (['html'=>'HTML','python'=>'Python','cpp'=>'C++','java'=>'Java'] as $k=>$label): ?>
        <button type="button" data-lang="<?= $k ?>" onclick="setLang('<?= $k ?>')" class="lang-tab rounded-xl px-4 py-2 text-sm font-bold <?= $k==='html'?'bg-brand-600 text-white':'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' ?>"><?= $label ?></button>
        <?php endforeach; ?>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-slate-900 p-3 dark:border-white/10">
            <div class="mb-2 flex items-center justify-between px-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400" id="editorLabel">HTML</span>
                <button onclick="runCode()" class="rounded-lg bg-emerald-600 px-4 py-1.5 text-sm font-bold text-white hover:bg-emerald-700">▶ Run</button>
            </div>
            <textarea id="editor" spellcheck="false" class="h-[28rem] w-full resize-none rounded-xl bg-slate-950 p-4 font-mono text-sm text-emerald-200 focus:outline-none"></textarea>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900">
            <span class="px-1 text-xs font-bold uppercase tracking-wider text-slate-400">Output</span>
            <iframe id="htmlOut" class="mt-2 h-[28rem] w-full rounded-xl border border-slate-200 bg-white dark:border-white/10"></iframe>
            <pre id="textOut" class="mt-2 hidden h-[28rem] w-full overflow-auto rounded-xl bg-slate-950 p-4 font-mono text-sm text-emerald-200"></pre>
        </div>
    </div>
    <p class="mt-4 text-center text-xs text-slate-400">HTML & Python run fully in your browser. For C++ / Java, write &amp; check your logic here, then compile on a free tool like <a href="https://onecompiler.com" target="_blank" rel="noopener" class="font-bold text-brand-600">OneCompiler</a>.</p>
</section>

<script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/skulpt@1.2.0/dist/skulpt-stdlib.js"></script>
<script>
const STARTERS = {
    html: '<!DOCTYPE html>\n<html>\n<head><style>body{font-family:sans-serif;text-align:center;padding:2rem}h1{color:#274a70}</style></head>\n<body>\n  <h1>Hello, ITTI!</h1>\n  <p>Edit this code and press Run.</p>\n</body>\n</html>',
    python: '# Python runs in your browser\nname = "ITTI Student"\nfor i in range(1, 4):\n    print(f"{i}. Hello, {name}!")\n\ntotal = sum(range(1, 11))\nprint("Sum 1..10 =", total)',
    cpp: '#include <iostream>\nusing namespace std;\nint main(){\n    cout << "Hello, ITTI!" << endl;\n    for(int i=1;i<=3;i++) cout << "Line " << i << endl;\n    return 0;\n}',
    java: 'public class Main {\n    public static void main(String[] args){\n        System.out.println("Hello, ITTI!");\n        for(int i=1;i<=3;i++) System.out.println("Line " + i);\n    }\n}'
};
const code = {...STARTERS};
let lang = 'html';
const editor = document.getElementById('editor');
function setLang(l){
    code[lang] = editor.value; lang = l; editor.value = code[l];
    document.getElementById('editorLabel').textContent = l.toUpperCase();
    document.querySelectorAll('.lang-tab').forEach(b=>{ const on=b.dataset.lang===l; b.className='lang-tab rounded-xl px-4 py-2 text-sm font-bold '+(on?'bg-brand-600 text-white':'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300'); });
    showHtml(l==='html');
}
function showHtml(yes){ document.getElementById('htmlOut').classList.toggle('hidden',!yes); document.getElementById('textOut').classList.toggle('hidden',yes); }
function out(t){ document.getElementById('textOut').textContent = t; }
function builtinRead(x){ if(Sk.builtinFiles===undefined||Sk.builtinFiles["files"][x]===undefined) throw "File not found: '"+x+"'"; return Sk.builtinFiles["files"][x]; }
function runCode(){
    code[lang]=editor.value;
    if(lang==='html'){ showHtml(true); document.getElementById('htmlOut').srcdoc = editor.value; return; }
    showHtml(false);
    if(lang==='python'){
        out('');
        if(typeof Sk==='undefined'){ out('Python engine still loading - try again in a moment.'); return; }
        Sk.configure({output:t=>{document.getElementById('textOut').textContent+=t;}, read:builtinRead, __future__:Sk.python3});
        Sk.misceval.asyncToPromise(()=>Sk.importMainWithBody("<stdin>",false,editor.value,true))
          .then(()=>{}, e=>{ document.getElementById('textOut').textContent += '\n'+e.toString(); });
        return;
    }
    out('▶ '+lang.toUpperCase()+' is a compiled language and can\'t run in the browser on our hosting.\n\nWrite and check your code here, then compile & run it free at https://onecompiler.com - or in your IDE during class.');
}
editor.value = STARTERS.html;
runCode();
</script>
