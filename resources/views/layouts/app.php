<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? config('app.name')) ?></title>
    <meta name="description" content="IT Training Institute and College, Kumber Maidan - job-ready courses in Networking (CCNA), Ethical Hacking, Cyber Security, CCTV, and Programming (C++, Java, Python, OOP, HTML).">
    <link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">

    <!-- Compiled Tailwind (static, no runtime CDN - production-ready for shared hosting) -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>[x-cloak]{display:none}</style>
    <script>
        // Apply saved theme before paint (no flash).
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="overflow-x-hidden bg-slate-50 text-slate-800 dark:bg-ink dark:text-slate-200 antialiased" style="max-width:100vw">

<?php include BASE_PATH . '/resources/views/partials/nav.php'; ?>

<main>
    <?= $content ?>
</main>

<?php include BASE_PATH . '/resources/views/partials/footer.php'; ?>
<?php include BASE_PATH . '/resources/views/partials/whatsapp.php'; ?>

<script>
    // Dark-mode toggle
    function toggleTheme(){
        const html = document.documentElement;
        html.classList.toggle('dark');
        localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
    }
    // Mobile menu
    function toggleMenu(){ document.getElementById('mobileMenu').classList.toggle('hidden'); }
    // Reveal on scroll
    document.addEventListener('DOMContentLoaded',()=>{
        const io = new IntersectionObserver((entries)=>{
            entries.forEach(en=>{ if(en.isIntersecting){ en.target.classList.add('animate-fadeup'); io.unobserve(en.target);} });
        },{threshold:.12});
        document.querySelectorAll('[data-reveal]').forEach(el=>io.observe(el));
    });
</script>
</body>
</html>
