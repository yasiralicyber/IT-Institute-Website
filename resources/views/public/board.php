<?php /** @var array $slides @var array $stats */ ?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticeboard - IT Training Institute</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:Inter,sans-serif}.slide{animation:fade .8s ease}@keyframes fade{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:none}}</style>
</head>
<body class="flex h-screen flex-col overflow-hidden bg-gradient-to-br from-brand-900 to-ink text-white">
    <!-- header -->
    <header class="flex items-center justify-between border-b border-white/10 px-10 py-5">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl bg-white p-1"><img src="<?= asset('img/logo.jpg') ?>" alt="" class="h-full w-full object-contain"></span>
            <div><p class="text-2xl font-black">IT Training Institute</p><p class="text-sm uppercase tracking-[0.3em] text-gold-300">Kumber Maidan</p></div>
        </div>
        <div class="text-right"><p id="clock" class="text-3xl font-black tabular-nums"></p><p id="date" class="text-sm text-brand-200"></p></div>
    </header>

    <!-- slide -->
    <main class="flex flex-1 items-center justify-center p-12">
        <div id="slide" class="slide max-w-5xl text-center"></div>
    </main>

    <!-- footer stats -->
    <footer class="grid grid-cols-3 gap-6 border-t border-white/10 px-10 py-5 text-center">
        <div><p class="text-3xl font-black text-gold-400"><?= number_format($stats['students']) ?>+</p><p class="text-xs text-brand-200">Students Trained</p></div>
        <div><p class="text-3xl font-black text-gold-400"><?= $stats['courses'] ?></p><p class="text-xs text-brand-200">Courses</p></div>
        <div><p class="text-3xl font-black text-gold-400"><?= $stats['certs'] ?>+</p><p class="text-xs text-brand-200">Certificates Issued</p></div>
    </footer>

<script>
const SLIDES = <?= json_encode(array_values($slides)) ?>;
const fallback = [{kind:'Welcome',title:'Welcome to IT Training Institute',body:'Networking · Cyber Security · Programming · CCTV - Admissions open now.'}];
const data = SLIDES.length ? SLIDES : fallback;
let idx = 0;
const kindColor = {Notice:'text-gold-300',Event:'text-emerald-300',News:'text-sky-300',Competition:'text-gold-300',Timetable:'text-brand-200',Welcome:'text-gold-300'};
function render(){
    const s = data[idx % data.length];
    document.getElementById('slide').innerHTML =
        '<p class="text-lg font-bold uppercase tracking-[0.4em] '+(kindColor[s.kind]||'text-gold-300')+'">'+s.kind+(s.date?' · '+s.date:'')+'</p>'+
        '<h1 class="mt-6 text-6xl font-black leading-tight">'+escapeHtml(s.title)+'</h1>'+
        (s.body?'<p class="mx-auto mt-6 max-w-3xl text-2xl text-brand-100">'+escapeHtml(s.body)+'</p>':'');
    document.getElementById('slide').classList.remove('slide'); void document.getElementById('slide').offsetWidth; document.getElementById('slide').classList.add('slide');
    idx++;
}
function escapeHtml(t){ const d=document.createElement('div'); d.textContent=t||''; return d.innerHTML; }
function clock(){ const n=new Date(); document.getElementById('clock').textContent=n.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}); document.getElementById('date').textContent=n.toLocaleDateString([], {weekday:'long',day:'numeric',month:'long'}); }
render(); setInterval(render, 8000); clock(); setInterval(clock, 1000);
</script>
</body>
</html>
