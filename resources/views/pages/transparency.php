<?php /** @var array $courses @var array $policies */ ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <span class="text-sm font-bold uppercase tracking-[0.2em] text-gold-600">Clear & Honest</span>
        <h1 class="mt-2 text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Fees, Policies & Transparency</h1>
        <p class="mx-auto mt-4 max-w-2xl text-slate-600 dark:text-slate-300">Everything you need to know before you enrol - prices, durations, certificate rules and policies, all in one place.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
    <h2 class="text-2xl font-black text-slate-900 dark:text-white">Course Fees & Details</h2>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500 dark:bg-white/5">
                <tr><th class="px-5 py-3">Course</th><th class="px-5 py-3">Level</th><th class="px-5 py-3">Lessons</th><th class="px-5 py-3">Duration</th><th class="px-5 py-3">Free Preview</th><th class="px-5 py-3 text-right">Fee</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                <?php foreach ($courses as $c): ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                    <td class="px-5 py-3"><a href="<?= url('/courses/' . $c['slug']) ?>" class="font-bold text-slate-900 hover:text-brand-700 dark:text-white"><?= e($c['title']) ?></a></td>
                    <td class="px-5 py-3 text-slate-500"><?= e($c['level']) ?></td>
                    <td class="px-5 py-3 text-slate-500"><?= (int) $c['lectures'] ?></td>
                    <td class="px-5 py-3 text-slate-500"><?= duration_label((int) $c['total_minutes']) ?></td>
                    <td class="px-5 py-3 text-emerald-600">First 5 free</td>
                    <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-white"><?= pkr($c['price']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-2">
        <?php
        $cards = [
            ['How Payment Works', 'Pay the fee via bank / JazzCash / Easypaisa, then upload your receipt. We verify it and unlock the course - usually within hours.'],
            ['↩Refund Policy', $policies['refund']],
            ['Certificate Conditions', $policies['certificate']],
            ['Support Availability', $policies['support']],
            ['Equipment Required', $policies['equipment']],
            ['Online vs On-Campus', $policies['delivery']],
        ];
        foreach ($cards as [$t, $body]): ?>
        <div data-reveal class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
            <h3 class="font-bold text-slate-900 dark:text-white"><?= e($t) ?></h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300"><?= e($body) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
