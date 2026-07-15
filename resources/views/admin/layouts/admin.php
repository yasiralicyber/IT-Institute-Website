<?php
/** @var string $content */
use App\Core\Database;
$u = \App\Core\Auth::user();
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pendingCount  = (int) Database::scalar("SELECT COUNT(*) FROM purchase_requests WHERE status = 'pending'");
$newAdmissions = (int) Database::scalar("SELECT COUNT(*) FROM admissions WHERE status = 'new'");
$pendingReviews = (int) Database::scalar("SELECT COUNT(*) FROM reviews WHERE status = 'pending'");
$pendingProjects = (int) Database::scalar("SELECT COUNT(*) FROM projects WHERE status = 'pending'");
$openAppeals = (int) Database::scalar("SELECT COUNT(*) FROM score_appeals WHERE status = 'open'") ?: null;
$pendingApprovals = (int) Database::scalar("SELECT COUNT(*) FROM sensitive_requests WHERE status = 'pending'") ?: null;
$riskCount = null;

// icon set
$I = [
  'dash' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
  'chat' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586M7 8h10M7 12h4m-7 8l3.586-3.586A2 2 0 015 16V6a2 2 0 012-2h10',
  'users'=> 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
  'check'=> 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  'doc'  => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  'grid' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
  'room' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  'cal'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l2 2 4-4',
  'clock'=> 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
  'money'=> 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
  'cog'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
  'audit'=> 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  'trash'=> 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
  'save' => 'M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4',
  'proj' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
  'risk' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
  'chart'=> 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
  'beaker'=> 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
  'shield'=> 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
  'lock' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
  'guard'=> 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-6a3 3 0 11-3 3',
];
$groups = [
  'Main' => [['/','Dashboard',$I['dash'],null],['/analytics','Analytics',$I['chart'],null]],
  'Learning' => [['/courses','Courses',$I['book'],null],['/learning','Learning Tools',$I['beaker'],null],['/reviews','Reviews',$I['star'],$pendingReviews],['/community','Community',$I['chat'],null],['/projects','Projects',$I['proj'],$pendingProjects],['/acknowledgments','Acknowledgments',$I['check'],null]],
  'Students' => [['/students','Students',$I['users'],null],['/admissions','Admissions',$I['doc'],$newAdmissions],['/risk','At-Risk Students',$I['risk'],$riskCount],['/purchases','Online Payments',$I['check'],$pendingCount]],
  'Institute' => [['/batches','Batches',$I['grid'],null],['/staff','Staff',$I['users'],null],['/classrooms','Classrooms',$I['room'],null],['/attendance','Attendance',$I['cal'],null],['/timetable','Timetable',$I['clock'],null]],
  'Finance' => [['/fees','Fees',$I['money'],null],['/expenses','Expenses',$I['doc'],null],['/payroll','Payroll',$I['users'],null],['/daybook','Day Book',$I['save'],null],['/fee-plans','Fee Plans',$I['doc'],null]],
  'Exams & Results' => [['/test-marks','Test Marks',$I['star'],null],['/results','Results',$I['doc'],null],['/certificates','Certificate Registry',$I['check'],null],['/grading-schemes','Grading Schemes',$I['star'],null],['/appeals','Score Appeals',$I['chat'],$openAppeals ?? null],['/exam-violations','Exam Integrity',$I['shield'],null]],
  'Communication' => [['/events','Events & News',$I['cal'],null],['/notices','Notices',$I['bell'],null]],
  'Security' => [['/login-risk','Login Risk',$I['risk'],null],['/approvals','Approvals',$I['check'],$pendingApprovals ?? null],['/honeytokens','Honeytokens',$I['lock'],null],['/staff-roles','Staff Roles',$I['users'],null]],
  'System' => [['/settings','Settings',$I['cog'],null],['/imports','Bulk Import',$I['save'],null],['/automations','Automations',$I['cog'],null],['/audit','Audit Log',$I['audit'],null],['/recycle','Recycle Bin',$I['trash'],null],['/backups','Backups',$I['save'],null]],
];
// breadcrumb
$crumbSection = ''; $crumbPage = $heading ?? ($title ?? 'Dashboard');
foreach ($groups as $gname => $items) { foreach ($items as $it) {
  if ($current === $it[0] || ($it[0] !== '/' && str_starts_with($current, $it[0]))) { $crumbSection = $gname; }
}}
$isActive = fn($href) => $current === $href || ($href !== '/' && str_starts_with($current, $href));
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> - ITTI Admin</title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        #sidebar{width:17rem}
        .main-wrap{margin-left:0}
        @media(min-width:1024px){ .main-wrap{margin-left:17rem} }
        @media(max-width:1023px){ #sidebar{transform:translateX(-100%);transition:transform .25s} body.sb-open #sidebar{transform:translateX(0)} }
        .sb-backdrop{display:none} body.sb-open .sb-backdrop{display:block} @media(min-width:1024px){ .sb-backdrop{display:none!important} }
        .nav-group.is-hidden{display:none}
    </style>
    <script>
        if (localStorage.theme==='dark'||(!('theme' in localStorage)&&matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
    </script>
</head>
<body class="bg-slate-100 text-slate-800 dark:bg-ink dark:text-slate-200 antialiased">
<!-- mobile backdrop -->
<div onclick="document.body.classList.remove('sb-open')" class="sb-backdrop fixed inset-0 z-30 bg-black/40"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <div class="flex items-center gap-2.5 border-b border-slate-200 px-4 py-4 dark:border-white/10">
        <span class="flex h-10 w-10 flex-none items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 ring-1 ring-black/5"><img src="<?= asset('img/logo.jpg') ?>" alt="ITTI" class="h-full w-full object-contain"></span>
        <span class="text-sm font-extrabold leading-tight text-slate-900 dark:text-white">ITTI Admin<br><span class="text-[10px] font-semibold uppercase tracking-widest text-brand-600">Control Panel</span></span>
    </div>
    <!-- Live menu filter: type to find any screen, nothing is hidden by default -->
    <div class="border-b border-slate-200 px-3 py-2.5 dark:border-white/10">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="navFilter" oninput="filterNav(this.value)" placeholder="Find a menu… (Ctrl K)" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm focus:border-brand-400 dark:border-white/10 dark:bg-white/5 dark:text-white">
        </div>
    </div>
    <nav id="navList" class="flex-1 space-y-3 overflow-y-auto p-3">
        <?php foreach ($groups as $gname => $items): ?>
        <div class="nav-group" data-group="<?= e(strtolower($gname)) ?>">
            <p class="nav-sec mb-1 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($gname) ?></p>
            <?php foreach ($items as [$href,$label,$icon,$badge]): $active = $isActive($href); ?>
            <a href="<?= e($href) ?>" data-nav="<?= e(strtolower($label)) ?>" class="nav-item relative flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition <?= $active ? 'bg-brand-700 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' ?>">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
                <span class="flex-1"><?= e($label) ?></span>
                <?php if ($badge): ?><span class="rounded-full bg-red-500 px-1.5 text-[10px] font-bold text-white"><?= $badge ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <p id="navEmpty" class="hidden px-3 py-6 text-center text-sm text-slate-400">No menu matches.</p>
    </nav>
    <div class="nav-foot border-t border-slate-200 p-3 dark:border-white/10">
        <a href="<?= abs_url('/') ?>" target="_blank" class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" d="M3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18"/></svg> View website</a>
        <a href="/logout" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg> Log out</a>
    </div>
</aside>

<div class="main-wrap min-h-screen">
    <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/85 px-4 py-2.5 backdrop-blur dark:border-white/10 dark:bg-slate-900/85 sm:px-6">
        <button onclick="document.body.classList.toggle('sb-open')" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10 lg:hidden" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="min-w-0">
            <?php if ($crumbSection): ?><p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"><?= e($crumbSection) ?></p><?php endif; ?>
            <h1 class="truncate text-base font-bold leading-tight text-slate-900 dark:text-white"><?= e($crumbPage) ?></h1>
        </div>
        <form action="/search" method="GET" class="ml-auto hidden items-center sm:flex">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input name="q" placeholder="Search…" class="w-44 rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm focus:w-64 focus:border-brand-400 dark:border-white/10 dark:bg-white/5 dark:text-white">
            </div>
        </form>
        <button onclick="document.documentElement.classList.toggle('dark');localStorage.theme=document.documentElement.classList.contains('dark')?'dark':'light'" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Theme">
            <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white" title="<?= e($u['name'] ?? 'Admin') ?>"><?= e(strtoupper(substr($u['name'] ?? 'A', 0, 1))) ?></span>
    </header>

    <main class="p-4 sm:p-6">
        <?php if ($m = flash('success')): ?><div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"><?= e($m) ?></div><?php endif; ?>
        <?php if ($m = flash('error')): ?><div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"><?= e($m) ?></div><?php endif; ?>
        <?= $content ?>
    </main>
</div>

<!-- Command palette (Ctrl/Cmd + K) -->
<div id="cmdk" class="fixed inset-0 z-50 hidden items-start justify-center bg-black/40 p-4 pt-24" onclick="if(event.target===this)cmdkToggle(false)">
    <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
        <input id="cmdkInput" placeholder="Jump to… or type to search students, courses…" class="w-full border-0 border-b border-slate-200 bg-transparent px-5 py-4 text-slate-900 focus:ring-0 dark:border-white/10 dark:text-white" oninput="cmdkFilter()" onkeydown="cmdkKey(event)">
        <div id="cmdkList" class="max-h-80 overflow-y-auto p-2"></div>
        <div class="border-t border-slate-100 px-5 py-2 text-xs text-slate-400 dark:border-white/10">↑↓ to move · Enter to open · Esc to close</div>
    </div>
</div>
<script>
const CMDS = <?= json_encode((function() use ($groups) { $o = []; foreach ($groups as $g => $items) { foreach ($items as $it) { $o[] = ['label' => $it[1], 'href' => $it[0], 'section' => $g]; } } return $o; })()) ?>;
let cmdkSel = 0, cmdkItems = [];
function cmdkToggle(show){ const el=document.getElementById('cmdk'); el.classList.toggle('hidden',!show); el.classList.toggle('flex',show); if(show){ document.getElementById('cmdkInput').value=''; cmdkFilter(); document.getElementById('cmdkInput').focus(); } }
function cmdkFilter(){
    const q=document.getElementById('cmdkInput').value.toLowerCase().trim();
    let list=CMDS.filter(c=>!q||c.label.toLowerCase().includes(q)||c.section.toLowerCase().includes(q));
    cmdkItems=list.slice(0,8).map(c=>({label:c.label,sub:c.section,href:c.href}));
    if(q.length>=2) cmdkItems.push({label:'Search "'+q+'"',sub:'Global search',href:'/search?q='+encodeURIComponent(q)});
    cmdkSel=0; cmdkRender();
}
function cmdkRender(){
    document.getElementById('cmdkList').innerHTML=cmdkItems.map((c,i)=>
      '<a href="'+c.href+'" class="flex items-center justify-between rounded-xl px-4 py-2.5 '+(i===cmdkSel?'bg-brand-600 text-white':'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/5')+'"><span class="font-semibold">'+c.label+'</span><span class="text-xs '+(i===cmdkSel?'text-white/70':'text-slate-400')+'">'+c.sub+'</span></a>'
    ).join('')||'<p class="px-4 py-6 text-center text-sm text-slate-400">No matches</p>';
}
function cmdkKey(e){
    if(e.key==='ArrowDown'){e.preventDefault();cmdkSel=Math.min(cmdkSel+1,cmdkItems.length-1);cmdkRender();}
    else if(e.key==='ArrowUp'){e.preventDefault();cmdkSel=Math.max(cmdkSel-1,0);cmdkRender();}
    else if(e.key==='Enter'){e.preventDefault();if(cmdkItems[cmdkSel])location.href=cmdkItems[cmdkSel].href;}
    else if(e.key==='Escape'){cmdkToggle(false);}
}
document.addEventListener('keydown',e=>{ if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();cmdkToggle(true);} });

// Live sidebar filter - highlights matching menu items, hides empty groups. Nothing is hidden until you type.
function filterNav(q){
    q=(q||'').toLowerCase().trim();
    var any=false;
    document.querySelectorAll('#navList .nav-group').forEach(function(g){
        var shown=0;
        g.querySelectorAll('.nav-item').forEach(function(a){
            var match=!q||a.dataset.nav.includes(q)||g.dataset.group.includes(q);
            a.style.display=match?'':'none';
            if(match)shown++;
        });
        g.classList.toggle('is-hidden',shown===0);
        if(shown)any=true;
    });
    document.getElementById('navEmpty').classList.toggle('hidden',any);
}
</script>
</body>
</html>
