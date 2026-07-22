<?php /** @var array $v */ function sv($v,$k){return $v[$k]??'';} ?>
<form action="/settings" method="POST" enctype="multipart/form-data" class="max-w-3xl space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Homepage Hero</h2>
        <div class="space-y-3">
            <input name="hero_title" value="<?= e(sv($v,'hero_title')) ?>" placeholder="Hero title" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <textarea name="hero_subtitle" rows="2" placeholder="Hero subtitle" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e(sv($v,'hero_subtitle')) ?></textarea>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Hero Stats (the 4 numbers on the homepage hero)</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            <input name="stat1_value" value="<?= e(sv($v,'stat1_value')) ?>" placeholder="Stat 1 – value (e.g. 10+)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat1_label" value="<?= e(sv($v,'stat1_label')) ?>" placeholder="Stat 1 – label (e.g. Years of Excellence)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat2_value" value="<?= e(sv($v,'stat2_value')) ?>" placeholder="Stat 2 – value (e.g. 1,200+)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat2_label" value="<?= e(sv($v,'stat2_label')) ?>" placeholder="Stat 2 – label (e.g. Students Trained)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat3_value" value="<?= e(sv($v,'stat3_value')) ?>" placeholder="Stat 3 – value (e.g. 9)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat3_label" value="<?= e(sv($v,'stat3_label')) ?>" placeholder="Stat 3 – label (e.g. Professional Courses)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat4_value" value="<?= e(sv($v,'stat4_value')) ?>" placeholder="Stat 4 – value (e.g. 95%)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="stat4_label" value="<?= e(sv($v,'stat4_label')) ?>" placeholder="Stat 4 – label (e.g. Course Satisfaction)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Branding</h2>
        <div class="flex items-center gap-4">
            <img src="<?= url('/site-logo') ?>" alt="Current site logo" class="h-16 w-16 rounded-xl object-contain ring-1 ring-slate-200">
            <div class="min-w-0 flex-1">
                <label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200">Site logo (JPG/PNG/WebP, max 2 MB)</label>
                <input type="file" name="site_logo" accept="image/*" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Payment Details (shown at checkout)</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            <input name="pay_bank" value="<?= e(sv($v,'pay_bank')) ?>" placeholder="Method (Bank/JazzCash/Easypaisa)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="pay_title" value="<?= e(sv($v,'pay_title')) ?>" placeholder="Account title" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="pay_number" value="<?= e(sv($v,'pay_number')) ?>" placeholder="Account number" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="pay_note" value="<?= e(sv($v,'pay_note')) ?>" placeholder="Instructions" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Contact & Social Links</h2>
        <div class="grid gap-3 sm:grid-cols-2">
            <input name="inst_phone" value="<?= e(sv($v,'inst_phone')) ?>" placeholder="Phone" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="inst_whatsapp" value="<?= e(sv($v,'inst_whatsapp')) ?>" placeholder="WhatsApp number (e.g. 923058382085)" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="inst_email" value="<?= e(sv($v,'inst_email')) ?>" placeholder="Email" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="inst_facebook" value="<?= e(sv($v,'inst_facebook')) ?>" placeholder="Facebook URL" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="inst_youtube" value="<?= e(sv($v,'inst_youtube')) ?>" placeholder="YouTube playlist URL" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <input name="inst_map" value="<?= e(sv($v,'inst_map')) ?>" placeholder="Google Maps URL" class="rounded-xl border-slate-300 bg-white px-4 py-2.5 dark:border-white/15 dark:bg-slate-800 dark:text-white">
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="mb-4 text-lg font-bold text-slate-900 dark:text-white">Policies (shown on the public Transparency page)</h2>
        <div class="space-y-3">
            <?php foreach (['policy_refund'=>'Refund Policy','policy_certificate'=>'Certificate Conditions','policy_support'=>'Support Availability','policy_equipment'=>'Equipment Required','policy_delivery'=>'Online vs On-Campus'] as $k=>$label): ?>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700 dark:text-slate-200"><?= e($label) ?></label>
                <textarea name="<?= $k ?>" rows="2" placeholder="Leave blank to use the default text" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"><?= e(sv($v,$k)) ?></textarea>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <button class="w-full rounded-xl bg-brand-600 py-3.5 font-bold text-white hover:bg-brand-700 sm:w-auto sm:px-10">Save Settings</button>
</form>
