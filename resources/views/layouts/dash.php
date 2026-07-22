<?php
/** @var string $content */
$u = $user ?? \App\Core\Auth::user();
$current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$nav = [
    '/dashboard'   => ['Dashboard', 'Home',   'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    '/my-courses'  => ['My Courses','Courses','M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    '/my/projects' => ['Projects', 'Projects','M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    '/my-results'  => ['My Results','Results', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    '/achievements'=> ['Achievements','Awards','M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    '/devices'     => ['My Devices','Devices', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
    '/community'   => ['Community','Chat',    'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l3.586-3.586z'],
];
// 5 primary items shown in the bottom nav; rest accessible via sidebar
$bottomNav = ['/dashboard', '/my-courses', '/my-results', '/achievements', '/community'];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?></title>
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* iOS safe area padding for bottom nav */
        #mobileNav { padding-bottom: max(8px, env(safe-area-inset-bottom)); }
    </style>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 dark:bg-ink dark:text-slate-200 antialiased">

<!-- Mobile sidebar overlay -->
<div id="sidebarOverlay" class="fixed inset-0 z-30 hidden bg-black/40 backdrop-blur-sm lg:hidden" onclick="closeSidebar()"></div>

<div class="flex min-h-screen">
    <!-- Sidebar — slides in on mobile, always visible on lg+ -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out dark:border-white/10 dark:bg-slate-900 lg:translate-x-0 lg:w-64">
        <!-- Logo -->
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-white/10">
            <a href="<?= url('/') ?>" class="flex items-center gap-2.5">
                <span class="flex h-10 w-10 flex-none items-center justify-center overflow-hidden rounded-lg bg-white p-0.5 ring-1 ring-black/5"><img src="<?= url('/site-logo') ?>" alt="ITTI" class="h-full w-full object-contain"></span>
                <span class="text-sm font-extrabold leading-tight text-slate-900 dark:text-white">IT Training<br><span class="text-[10px] font-semibold uppercase tracking-widest text-brand-600">Kumber Maidan</span></span>
            </a>
            <!-- Close button (mobile only) -->
            <button onclick="closeSidebar()" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 lg:hidden">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav links -->
        <nav class="flex-1 space-y-1 overflow-y-auto p-3">
            <?php foreach ($nav as $href => [$label, $short, $icon]): $active = $current === $href; ?>
            <a href="<?= url($href) ?>" onclick="closeSidebar()" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition <?= $active ? 'bg-brand-600 text-white shadow-md shadow-brand-900/20' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' ?>">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
                <?= e($label) ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Logout -->
        <div class="border-t border-slate-200 p-3 dark:border-white/10">
            <a href="<?= url('/logout') ?>" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                <svg class="h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Log out
            </a>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="flex min-w-0 flex-1 flex-col lg:ml-64">

        <!-- Sticky top header -->
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-white/10 dark:bg-slate-900/90">
            <!-- Hamburger (mobile) -->
            <button onclick="openSidebar()" class="flex h-9 w-9 flex-none items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10 lg:hidden" aria-label="Menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <h1 class="min-w-0 flex-1 truncate text-base font-bold text-slate-900 dark:text-white sm:text-lg"><?= e($heading ?? 'Dashboard') ?></h1>

            <div class="flex flex-none items-center gap-2">
                <!-- Search (tablet+) -->
                <form action="<?= url('/search') ?>" method="GET" class="hidden sm:block">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input name="q" placeholder="Search lessons…" class="w-36 rounded-xl border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm focus:w-52 focus:border-brand-400 dark:border-white/10 dark:bg-white/5 dark:text-white transition-all">
                    </div>
                </form>
                <!-- Dark mode toggle -->
                <button onclick="document.documentElement.classList.toggle('dark');localStorage.theme=document.documentElement.classList.contains('dark')?'dark':'light'" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10" aria-label="Theme">
                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36l-1.42-1.42M7.05 7.05L5.64 5.64m12.72 0l-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <!-- Avatar -->
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-brand-600 text-sm font-bold text-white"><?= e(strtoupper(substr($u['name'] ?? 'S', 0, 1))) ?></span>
                    <span class="hidden max-w-[120px] truncate text-sm font-semibold text-slate-700 dark:text-slate-200 md:block"><?= e($u['name'] ?? '') ?></span>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="min-w-0 flex-1 p-4 pb-24 sm:p-6 lg:pb-6">
            <?php if ($m = flash('success')): ?>
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">✓ <?= e($m) ?></div>
            <?php endif; ?>
            <?php if ($m = flash('error')): ?>
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"><?= e($m) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
    </div>
</div>

<!-- Mobile bottom nav (5 primary items, icons + short labels) -->
<nav id="mobileNav" class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900 lg:hidden">
    <div class="flex items-center justify-around px-2 pt-2">
        <?php foreach ($bottomNav as $href):
            [$label, $short, $icon] = $nav[$href];
            $active = $current === $href;
        ?>
        <a href="<?= url($href) ?>" class="flex flex-col items-center gap-0.5 px-1 pb-1 text-[10px] font-semibold <?= $active ? 'text-brand-600' : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' ?>">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl <?= $active ? 'bg-brand-50 dark:bg-brand-500/15' : '' ?>">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/></svg>
            </span>
            <?= e($short) ?>
        </a>
        <?php endforeach; ?>
        <!-- More button → opens sidebar -->
        <button onclick="openSidebar()" class="flex flex-col items-center gap-0.5 px-1 pb-1 text-[10px] font-semibold text-slate-400 hover:text-slate-700 dark:hover:text-slate-200">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </span>
            More
        </button>
    </div>
</nav>

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
</body>
</html>
