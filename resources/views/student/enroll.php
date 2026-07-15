<?php /** @var array $course @var ?array $pending @var array $pay */ ?>
<div class="mx-auto max-w-3xl">
    <a href="<?= url('/courses/' . $course['slug']) ?>" class="text-sm font-semibold text-brand-600 hover:underline">← Back to course</a>

    <?php if ($pending): ?>
    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-500/30 dark:bg-amber-500/10">
        <h2 class="text-lg font-bold text-amber-800 dark:text-amber-300">Your request is being reviewed</h2>
        <p class="mt-2 text-sm text-amber-700 dark:text-amber-300/90">You already submitted a receipt for this course on <?= e(date('d M Y', strtotime($pending['created_at']))) ?>. We'll unlock your access as soon as the payment is verified. Need help? <a href="https://wa.me/<?= e(config('institute.whatsapp')) ?>" class="font-bold underline">WhatsApp us</a>.</p>
    </div>
    <?php else: ?>

    <div class="mt-4 grid gap-6 lg:grid-cols-5">
        <!-- Payment instructions -->
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Step 1 · Pay the fee</h2>
                <p class="mt-2 text-3xl font-black text-brand-700 dark:text-brand-300"><?= pkr($course['price']) ?></p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><dt class="text-slate-500">Method</dt><dd class="font-bold text-slate-800 dark:text-slate-200"><?= e($pay['bank']) ?></dd></div>
                    <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><dt class="text-slate-500">Account title</dt><dd class="font-bold text-slate-800 dark:text-slate-200"><?= e($pay['title']) ?></dd></div>
                    <div class="flex justify-between border-b border-slate-100 pb-2 dark:border-white/10"><dt class="text-slate-500">Account no.</dt><dd class="font-bold text-slate-800 dark:text-slate-200"><?= e($pay['number']) ?></dd></div>
                </dl>
                <p class="mt-4 rounded-lg bg-slate-50 p-3 text-xs text-slate-500 dark:bg-white/5"><?= e($pay['note']) ?></p>
            </div>
        </div>

        <!-- Receipt upload -->
        <div class="lg:col-span-3">
            <form action="<?= url('/enroll/' . $course['slug']) ?>" method="POST" enctype="multipart/form-data"
                  class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
                <?= csrf_field() ?>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Step 2 · Upload your receipt</h2>
                <p class="mt-1 text-sm text-slate-500">We verify it and unlock the course for your account.</p>

                <div class="mt-5">
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Transaction / Reference No. (optional)</label>
                    <input name="reference_no" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500 dark:border-white/15 dark:bg-slate-800 dark:text-white" placeholder="e.g. TID 123456789">
                </div>
                <div class="mt-4">
                    <label class="mb-1.5 block text-sm font-bold text-slate-700 dark:text-slate-200">Payment receipt *</label>
                    <input type="file" name="receipt" required accept="image/*,application/pdf" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:font-bold file:text-white dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <p class="mt-1 text-xs text-slate-400">Screenshot or photo of your payment. JPG, PNG or PDF, up to 5MB.</p>
                </div>
                <button class="mt-6 w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white shadow-lg shadow-brand-900/20 hover:bg-brand-700">Submit for Approval</button>
                <p class="mt-3 text-center text-xs text-slate-400">You'll be notified once your access is unlocked.</p>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
