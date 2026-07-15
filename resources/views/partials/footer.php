<?php $inst = config('institute'); ?>
<footer class="overflow-x-hidden border-t border-slate-200 bg-white dark:border-white/10 dark:bg-ink">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <?php $variant = 'dark'; include __DIR__ . '/logo.php'; ?>
                <p class="mt-4 max-w-xs text-sm text-slate-500 dark:text-slate-400">
                    Building Pakistan's next generation of IT professionals with affordable, job-ready courses in networking, cyber security and programming.
                </p>
                <div class="mt-5 flex gap-3">
                    <a href="<?= e($inst['facebook']) ?>" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-600 hover:text-white dark:bg-white/10 dark:text-slate-300" aria-label="Facebook">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-2.9h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6v1.9h2.8l-.4 2.9h-2.4v7A10 10 0 0022 12z"/></svg>
                    </a>
                    <a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-red-600 hover:text-white dark:bg-white/10 dark:text-slate-300" aria-label="YouTube">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 00-1.8-1.8C19.3 5 12 5 12 5s-7.3 0-8.8.5A2.5 2.5 0 001.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 001.8 1.8C4.7 19 12 19 12 19s7.3 0 8.8-.5a2.5 2.5 0 001.8-1.8C23 15.2 23 12 23 12zM9.8 15.3V8.7l5.7 3.3z"/></svg>
                    </a>
                    <a href="https://wa.me/<?= e($inst['whatsapp']) ?>" target="_blank" rel="noopener" class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-green-500 hover:text-white dark:bg-white/10 dark:text-slate-300" aria-label="WhatsApp">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 32 32"><path d="M16 .5C7.4.5.5 7.4.5 16c0 2.8.7 5.4 2 7.7L.5 31.5l8-2.1A15.4 15.4 0 0016 31.5c8.6 0 15.5-6.9 15.5-15.5S24.6.5 16 .5z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Courses</h4>
                <ul class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="<?= url('/courses/ccna-200-301') ?>" class="hover:text-brand-600">CCNA 200-301</a></li>
                    <li><a href="<?= url('/courses/ethical-hacking') ?>" class="hover:text-brand-600">Ethical Hacking</a></li>
                    <li><a href="<?= url('/courses/cyber-security') ?>" class="hover:text-brand-600">Cyber Security</a></li>
                    <li><a href="<?= url('/courses/python') ?>" class="hover:text-brand-600">Python</a></li>
                    <li><a href="<?= url('/playground') ?>" class="hover:text-brand-600">Coding Playground</a></li>
                    <li><a href="<?= url('/labs') ?>" class="hover:text-brand-600">Interactive Labs</a></li>
                    <li><a href="<?= url('/courses') ?>" class="font-semibold text-brand-600">View all →</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Institute</h4>
                <ul class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">
                    <li><a href="<?= url('/instructor') ?>" class="hover:text-brand-600">Meet the Instructor</a></li>
                    <li><a href="<?= url('/events') ?>" class="hover:text-brand-600">Events & News</a></li>
                    <li><a href="<?= url('/hall') ?>" class="hover:text-brand-600">Hall of Fame</a></li>
                    <li><a href="<?= url('/transparency') ?>" class="hover:text-brand-600">Fees & Policies</a></li>
                    <li><a href="<?= url('/admission') ?>" class="hover:text-brand-600">Admission Form</a></li>
                    <li><a href="<?= url('/timetable') ?>" class="hover:text-brand-600">Timetable</a></li>
                    <li><a href="<?= url('/verify') ?>" class="hover:text-brand-600">Verify Certificate</a></li>
                    <li><a href="<?= url('/projects') ?>" class="hover:text-brand-600">Student Projects</a></li>
                    <li><a href="<?= url('/guardian') ?>" class="hover:text-brand-600">Guardian / Parent Portal</a></li>
                    <li><a href="<?= e($inst['youtube']) ?>" target="_blank" rel="noopener" class="hover:text-brand-600">Free YouTube Lessons</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Contact</h4>
                <ul class="mt-4 space-y-3 text-sm text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2"><span></span> Kumber Maidan, Pakistan</li>
                    <li class="flex items-center gap-2"><span></span> <a href="tel:<?= e(preg_replace('/\s+/', '', $inst['phone'])) ?>" class="hover:text-brand-600"><?= e($inst['phone']) ?></a></li>
                    <li class="flex items-center gap-2"><span></span> <a href="mailto:<?= e($inst['email']) ?>" class="hover:text-brand-600"><?= e($inst['email']) ?></a></li>
                    <li class="flex items-center gap-2"><span></span> <a href="https://wa.me/<?= e($inst['whatsapp']) ?>" class="hover:text-brand-600">WhatsApp Us</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-200 pt-6 text-sm text-slate-400 dark:border-white/10 sm:flex-row">
            <p>&copy; <?= date('Y') ?> <?= e($inst['name']) ?>. All rights reserved.</p>
            <p>Made for learners in Kumber Maidan </p>
        </div>
    </div>
    <?php // Honeytoken: hidden decoy link. Invisible + nofollow for real users; only a scraper/snooper follows it.
    $ht = \App\Core\Database::scalar("SELECT token FROM honeytokens ORDER BY id LIMIT 1");
    if ($ht): ?>
    <a href="<?= url('/internal/export/' . $ht) ?>" rel="nofollow noindex" aria-hidden="true" tabindex="-1" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">Student data export (staff only)</a>
    <?php endif; ?>
</footer>
