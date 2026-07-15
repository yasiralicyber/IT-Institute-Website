<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Learn by Doing</span>
        <h1 class="mt-2 text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Interactive Labs</h1>
        <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-300">Hands-on, browser-based simulators that bring our networking, CCTV and cyber security courses to life - practise the real skills, no setup needed.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid gap-6 md:grid-cols-3">
        <?php
        $labs = [
            ['network', 'server', 'Network Topology Lab', 'Drag routers, switches and PCs, connect them with cables, assign IP addresses and validate your network - perfect for CCNA practice.', '#1d4ed8'],
            ['cctv', 'camera', 'CCTV Coverage Planner', 'Place cameras on a floor plan, adjust angle, field-of-view and range, find blind spots and estimate storage needs.', '#0891b2'],
            ['cyber', 'shield', 'Cyber Investigation', 'Crack a simulated security case - spot the phishing email, find the suspicious login and weak passwords, then submit your findings.', '#dc2626'],
        ];
        foreach ($labs as [$slug, $icon, $title, $desc, $color]): ?>
        <a href="<?= url('/labs/' . $slug) ?>" data-reveal class="card-hover group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-xl dark:border-white/10 dark:bg-slate-900/60">
            <div class="flex h-28 items-center justify-center text-white" style="background:linear-gradient(135deg,<?= $color ?>,#0a121f)"><?= icon($icon, 'h-12 w-12') ?></div>
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white"><?= e($title) ?></h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?= e($desc) ?></p>
                <span class="mt-4 inline-block font-bold text-brand-600 group-hover:underline">Open lab →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
