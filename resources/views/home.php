<?php
/** @var array $courses @var array $stats @var array $reviews */
use App\Content;
$inst = config('institute');
$instStats = Content::stats();
$faculty = array_slice(Content::faculty(), 0, 3);
$values = Content::values();
$gallery = Content::gallery();
?>

<!-- ===== HERO ===== -->
<section id="heroCarousel" class="relative isolate overflow-hidden bg-brand-950">
    <!-- Sliding image layers (cross-fade) -->
    <div class="absolute inset-0">
        <?php foreach ($heroSlides as $i => $sl): ?>
        <img src="<?= url('/hero-image/' . $sl['id']) ?>" alt="<?= e($sl['alt'] ?: 'IT Training Institute') ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
             class="hero-slide absolute inset-0 h-full w-full object-cover transition-opacity duration-[1400ms] ease-in-out <?= $i === 0 ? 'opacity-100 z-[1]' : 'opacity-0' ?>"
             data-slide="<?= $i ?>">
        <?php endforeach; ?>
    </div>
    <div class="absolute inset-0 z-[2] bg-gradient-to-br from-brand-950/95 via-brand-950/85 to-brand-900/70"></div>

    <!-- Content -->
    <div class="relative z-[3] mx-auto grid min-h-[78vh] max-w-7xl items-center gap-12 px-4 py-20 sm:px-6 lg:grid-cols-12 lg:py-28">
        <div class="lg:col-span-8" data-reveal>
            <span class="inline-flex items-center gap-2 rounded-full border border-gold-400/40 bg-gold-400/10 px-4 py-1.5 text-sm font-semibold text-gold-300">
                <span class="flex h-2 w-2 rounded-full bg-gold-400"></span> Admissions Open 2026 · Kumber Maidan
            </span>
            <h1 class="mt-6 text-4xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-7xl">
                Shape Your Future in <span class="text-gold-400">Information Technology</span>
            </h1>
            <p class="mt-6 max-w-2xl text-lg text-brand-100 sm:text-xl">
                A leading institute for <strong class="text-white">Networking, Cyber Security & Programming</strong> in Kumber Maidan. Learn from certified instructors, train in real labs, and earn verifiable certificates - with the first 5 lessons of every course free.
            </p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="<?= url('/admission') ?>" class="rounded-xl bg-gold-500 px-7 py-4 text-base font-bold text-brand-950 shadow-xl shadow-gold-500/30 transition hover:bg-gold-400">Apply for Admission</a>
                <a href="<?= url('/courses') ?>" class="rounded-xl border border-white/25 bg-white/5 px-7 py-4 text-base font-bold text-white backdrop-blur transition hover:bg-white/10">Explore Courses</a>
            </div>
            <div class="mt-10 grid max-w-2xl grid-cols-2 gap-6 sm:grid-cols-4">
                <?php foreach ($instStats as $s): ?>
                <div><p class="text-2xl font-black text-gold-400 sm:text-3xl"><?= e($s[0]) ?></p><p class="text-xs text-brand-200"><?= e($s[1]) ?></p></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Prev / Next -->
    <button type="button" onclick="heroGo(-1)" aria-label="Previous slide" class="group absolute left-3 top-1/2 z-[4] flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/30 text-white backdrop-blur transition hover:bg-black/50 sm:left-6">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button type="button" onclick="heroGo(1)" aria-label="Next slide" class="group absolute right-3 top-1/2 z-[4] flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-black/30 text-white backdrop-blur transition hover:bg-black/50 sm:right-6">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>

    <!-- Dots -->
    <div class="absolute bottom-5 left-1/2 z-[4] flex -translate-x-1/2 gap-2.5">
        <?php foreach ($heroSlides as $i => $sl): ?>
        <button type="button" onclick="heroSet(<?= $i ?>)" aria-label="Go to slide <?= $i + 1 ?>" class="hero-dot h-2.5 rounded-full transition-all <?= $i === 0 ? 'w-7 bg-gold-400' : 'w-2.5 bg-white/50 hover:bg-white/80' ?>" data-dot="<?= $i ?>"></button>
        <?php endforeach; ?>
    </div>
</section>

<script>
(function () {
    var slides = document.querySelectorAll('#heroCarousel .hero-slide');
    var dots   = document.querySelectorAll('#heroCarousel .hero-dot');
    if (!slides.length) return;
    var cur = 0, timer = null, n = slides.length;
    function show(i) {
        cur = (i + n) % n;
        slides.forEach(function (s, k) {
            s.classList.toggle('opacity-100', k === cur);
            s.classList.toggle('opacity-0', k !== cur);
            s.classList.toggle('z-[1]', k === cur);
        });
        dots.forEach(function (d, k) {
            d.classList.toggle('w-7', k === cur);
            d.classList.toggle('bg-gold-400', k === cur);
            d.classList.toggle('w-2.5', k !== cur);
            d.classList.toggle('bg-white/50', k !== cur);
        });
    }
    function start() { stop(); timer = setInterval(function () { show(cur + 1); }, 5000); }
    function stop()  { if (timer) clearInterval(timer); }
    window.heroGo  = function (d) { show(cur + d); start(); };
    window.heroSet = function (i) { show(i); start(); };
    var el = document.getElementById('heroCarousel');
    el.addEventListener('mouseenter', stop);
    el.addEventListener('mouseleave', start);
    start();
})();
</script>

<!-- ===== FEATURE STRIP ===== -->
<section class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900/40">
    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-8 sm:px-6 md:grid-cols-4">
        <?php foreach ([['academic','Certified Courses'],['beaker','Real Hands-on Labs'],['certificate','Verifiable Certificates'],['gift','5 Free Lessons Each']] as $f): ?>
        <div class="flex items-center gap-3" data-reveal>
            <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10"><?= icon($f[0],'h-6 w-6') ?></span>
            <p class="font-bold text-slate-900 dark:text-white"><?= e($f[1]) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== ABOUT TEASER ===== -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="grid items-center gap-12 lg:grid-cols-2">
        <div class="relative" data-reveal>
            <img src="<?= asset('img/photos/about.jpg') ?>" alt="ITTI classroom" class="rounded-3xl shadow-xl">
            <div class="absolute -bottom-6 -right-4 hidden rounded-2xl bg-brand-700 p-6 text-white shadow-2xl sm:block">
                <p class="text-3xl font-black text-gold-400">10+</p><p class="text-sm">Years of teaching excellence</p>
            </div>
        </div>
        <div data-reveal>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Welcome to ITTI</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">IT Training Institute and College, Kumber Maidan</h2>
            <p class="mt-5 text-slate-600 dark:text-slate-300">We are a community-focused institute on a mission to make world-class IT education accessible to every student in our region. From networking and cyber security to programming and CCTV, our courses are built around <strong>practical, job-ready skills</strong> - taught by instructors who care about your success.</p>
            <ul class="mt-6 grid gap-3 sm:grid-cols-2">
                <?php foreach (['Certified, experienced faculty','Real equipment & labs','Affordable local fees','Free YouTube learning','Verifiable certificates','Career & admission support'] as $point): ?>
                <li class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200"><span class="text-gold-500">✓</span> <?= e($point) ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= url('/about') ?>" class="mt-8 inline-flex items-center gap-2 rounded-xl bg-brand-700 px-6 py-3 font-bold text-white transition hover:bg-brand-800">More About Us <span>→</span></a>
        </div>
    </div>
</section>

<!-- ===== COURSES ===== -->
<section class="bg-slate-50 py-20 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Our Programs</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Professional IT Courses</h2>
            <p class="mt-4 text-slate-600 dark:text-slate-300">Industry-relevant courses with free previews, chapter tests and certificates.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($courses as $course) { include __DIR__ . '/partials/course-card.php'; } ?>
        </div>
    </div>
</section>

<!-- ===== WHY CHOOSE US ===== -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="mx-auto max-w-2xl text-center" data-reveal>
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Why Choose ITTI</span>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Built Around Your Success</h2>
    </div>
    <div class="mt-12 grid gap-6 md:grid-cols-3">
        <?php foreach ($values as $v): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-7 transition hover:border-gold-300 hover:shadow-lg dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10"><?= icon($v['icon'],'h-7 w-7') ?></span>
            <h3 class="mt-5 text-lg font-bold text-slate-900 dark:text-white"><?= e($v['title']) ?></h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($v['desc']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== FACULTY ===== -->
<section class="bg-brand-950 py-20 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex flex-wrap items-end justify-between gap-4" data-reveal>
            <div>
                <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-400">Meet Our Team</span>
                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Expert Faculty</h2>
            </div>
            <a href="<?= url('/faculty') ?>" class="rounded-xl border border-white/25 px-5 py-2.5 font-bold text-white hover:bg-white/10">View all faculty →</a>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($faculty as $f): ?>
            <div class="group overflow-hidden rounded-2xl bg-white/5 ring-1 ring-white/10" data-reveal>
                <div class="relative h-64 overflow-hidden">
                    <img src="<?= asset('img/' . $f['photo']) ?>" alt="<?= e($f['name']) ?>" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    <span class="absolute left-3 top-3 rounded-full bg-gold-500 px-3 py-1 text-xs font-bold text-brand-950"><?= e($f['tag']) ?></span>
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-bold"><?= e($f['name']) ?></h3>
                    <p class="text-sm text-gold-300"><?= e($f['role']) ?></p>
                    <p class="mt-3 text-sm text-brand-200"><?= e(mb_substr($f['bio'], 0, 96)) ?>…</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CAMPUS GALLERY ===== -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="flex flex-wrap items-end justify-between gap-4" data-reveal>
        <div>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Take a Look</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Our Campus & Facilities</h2>
        </div>
        <a href="<?= url('/activities') ?>" class="rounded-xl bg-brand-700 px-5 py-2.5 font-bold text-white hover:bg-brand-800">Tour the campus →</a>
    </div>
    <div class="mt-10 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <?php foreach (array_slice($gallery, 0, 8) as $i => $g): ?>
        <a href="<?= url('/activities') ?>" class="group relative overflow-hidden rounded-2xl <?= $i === 0 ? 'col-span-2 row-span-2' : '' ?>" data-reveal>
            <img src="<?= asset('img/' . $g) ?>" alt="Campus" loading="lazy" class="h-full w-full <?= $i === 0 ? 'min-h-[16rem]' : 'h-40 sm:h-44' ?> object-cover transition duration-500 group-hover:scale-110">
            <div class="absolute inset-0 bg-brand-950/0 transition group-hover:bg-brand-950/30"></div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== AWARDS ===== -->
<section class="bg-slate-50 py-20 dark:bg-slate-900/40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Recognition</span>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">Awards & Achievements</h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($awards as $a): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900" data-reveal>
                <div class="relative h-36 overflow-hidden">
                    <img src="<?= url('/award-image/' . $a['id']) ?>" alt="<?= e($a['title']) ?>" loading="lazy" class="h-full w-full object-cover">
                    <span class="absolute left-3 top-3 rounded-lg bg-gold-500 px-2.5 py-1 text-xs font-black text-brand-950"><?= e($a['year']) ?></span>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900 dark:text-white"><?= e($a['title']) ?></h3>
                    <p class="mt-1 text-xs font-semibold text-gold-600"><?= e($a['org']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FREE YOUTUBE ===== -->
<section class="bg-gradient-to-br from-brand-900 to-brand-950 py-20 text-white">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2">
        <div data-reveal>
            <span class="inline-flex items-center gap-2 rounded-full bg-red-600/20 px-4 py-1.5 text-sm font-semibold text-red-300">▶ Free on YouTube</span>
            <h2 class="mt-5 text-3xl font-black sm:text-4xl">Learn for free before you enroll</h2>
            <p class="mt-4 max-w-lg text-brand-100">Our instructor shares free lessons on YouTube so you can experience the teaching style first. Subscribe, learn the basics free, then join a paid course to go pro and earn your certificate.</p>
            <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3.5 font-bold text-white transition hover:bg-red-700">Watch Free Lessons</a>
        </div>
        <div class="overflow-hidden rounded-2xl border border-white/10 shadow-2xl" data-reveal>
            <div class="relative aspect-video" id="ytFacade">
                <img src="<?= asset('img/photos/lab.jpg') ?>" alt="Free IT lessons on YouTube" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-brand-950/40"></div>
                <button type="button" onclick="this.parentElement.innerHTML='<iframe class=&quot;h-full w-full&quot; src=&quot;https://www.youtube.com/embed/videoseries?list=PLOh6YaRDYnhxc0nlxjgUcwTMti2jm-95U&amp;autoplay=1&quot; allow=&quot;autoplay&quot; allowfullscreen></iframe>'"
                        class="group absolute inset-0 flex items-center justify-center" aria-label="Play free lessons">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-red-600 shadow-2xl transition group-hover:scale-110">
                        <svg class="ml-1 h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                </button>
                <span class="absolute bottom-4 left-4 rounded-lg bg-black/50 px-3 py-1.5 text-sm font-bold text-white backdrop-blur">▶ Free IT Lessons Playlist</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== REVIEWS ===== -->
<?php if (!empty($reviews)): ?>
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6">
    <div class="mx-auto max-w-2xl text-center" data-reveal>
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Testimonials</span>
        <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">What Our Students Say</h2>
    </div>
    <div class="mt-12 grid gap-6 md:grid-cols-3">
        <?php foreach ($reviews as $r): ?>
        <figure class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-slate-900/60" data-reveal>
            <div class="flex text-gold-400"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></div>
            <blockquote class="mt-3 text-slate-700 dark:text-slate-300">"<?= e($r['body']) ?>"</blockquote>
            <figcaption class="mt-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-700 font-bold text-white"><?= e(strtoupper(substr($r['author'], 0, 1))) ?></span>
                <span><span class="block text-sm font-bold text-slate-900 dark:text-white"><?= e($r['author']) ?></span><span class="block text-xs text-slate-500"><?= e($r['course_title']) ?></span></span>
            </figcaption>
        </figure>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ===== CTA ===== -->
<section class="relative isolate overflow-hidden">
    <img src="<?= asset('img/photos/cta.jpg') ?>" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-brand-950/85"></div>
    <div class="relative mx-auto max-w-4xl px-4 py-20 text-center sm:px-6" data-reveal>
        <h2 class="text-3xl font-black text-white sm:text-4xl">Ready to start your IT journey?</h2>
        <p class="mx-auto mt-4 max-w-xl text-brand-100">Join IT Training Institute and College, Kumber Maidan today. Apply online or message us on WhatsApp - we'll help you choose the right course.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="<?= url('/admission') ?>" class="rounded-xl bg-gold-500 px-7 py-3.5 font-bold text-brand-950 transition hover:bg-gold-400">Apply for Admission</a>
            <a href="https://wa.me/<?= e($inst['whatsapp']) ?>" target="_blank" rel="noopener" class="rounded-xl border border-white/30 px-7 py-3.5 font-bold text-white transition hover:bg-white/10">WhatsApp: <?= e($inst['phone']) ?></a>
        </div>
    </div>
</section>
