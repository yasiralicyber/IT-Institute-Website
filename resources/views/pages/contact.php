<?php $inst = config('institute'); ?>
<section class="border-b border-slate-200 bg-gradient-to-b from-white to-slate-50 dark:border-white/10 dark:from-ink dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-14 text-center sm:px-6">
        <h1 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">Get in Touch</h1>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-slate-600 dark:text-slate-300">Have a question about a course or admission? We're here to help - call, email, or message us on WhatsApp.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <div class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-4">
            <?php
            $cards = [
                ['','Call / WhatsApp', $inst['phone'], 'https://wa.me/'.$inst['whatsapp']],
                ['','Email', $inst['email'], 'mailto:'.$inst['email']],
                ['','Visit Us', 'Kumber Maidan, Pakistan', $inst['map']],
                ['▶','Free Lessons', 'YouTube Channel', $inst['youtube']],
                ['','Follow Us', 'Facebook Page', $inst['facebook']],
            ];
            foreach ($cards as $c): ?>
            <a href="<?= e($c[3]) ?>" target="_blank" rel="noopener" class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-brand-400 hover:shadow-lg dark:border-white/10 dark:bg-slate-900/60">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-2xl dark:bg-brand-500/10"><?= $c[0] ?></span>
                <div><p class="text-sm text-slate-500"><?= e($c[1]) ?></p><p class="font-bold text-slate-900 dark:text-white"><?= e($c[2]) ?></p></div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm dark:border-white/10">
            <iframe
                src="https://www.google.com/maps?q=Kumber+Maidan&output=embed"
                class="h-full min-h-[420px] w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>
