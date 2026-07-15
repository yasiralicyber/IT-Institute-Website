<?php /** @var array $row */ ?>
<a href="/admissions" class="text-sm font-semibold text-brand-600 hover:underline">← All applications</a>

<div class="mt-4 grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-white/10 dark:bg-slate-900">
        <?php if ($row['photo']): ?>
            <img src="/admission-photo/<?= (int) $row['id'] ?>" alt="Photo" class="mx-auto h-40 w-40 rounded-2xl object-cover ring-1 ring-slate-200">
        <?php else: ?>
            <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-2xl bg-slate-100 text-5xl dark:bg-white/5"></div>
        <?php endif; ?>
        <h2 class="mt-4 text-lg font-black text-slate-900 dark:text-white"><?= e($row['name']) ?></h2>
        <p class="text-sm text-slate-500"><?= e($row['programs']) ?></p>

        <form action="/admissions/<?= (int) $row['id'] ?>/status" method="POST" class="mt-4 flex gap-2">
            <?= csrf_field() ?>
            <select name="status" class="flex-1 rounded-xl border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <?php foreach (['new'=>'New','contacted'=>'Contacted','enrolled'=>'Enrolled','rejected'=>'Rejected'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $row['status']===$k?'selected':'' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">Save</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900 lg:col-span-2">
        <h3 class="mb-4 font-bold text-slate-900 dark:text-white">Full Details</h3>
        <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
            <?php
            $fields = [
                'Father\'s Name' => $row['father_name'], 'Gender' => $row['gender'],
                'Date of Birth' => $row['dob'], 'CNIC / B-Form' => $row['form_b'],
                'Contact No' => $row['contact'], 'Email' => $row['email'],
                'Address' => $row['address'], 'Applied On' => date('d M Y, g:i A', strtotime($row['created_at'])),
            ];
            foreach ($fields as $label => $val): ?>
            <div class="border-b border-slate-100 pb-2 dark:border-white/10">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400"><?= e($label) ?></dt>
                <dd class="mt-0.5 font-semibold text-slate-800 dark:text-slate-200"><?= e($val ?: '-') ?></dd>
            </div>
            <?php endforeach; ?>
        </dl>
        <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $row['contact'])) ?>" target="_blank" class="mt-5 inline-block rounded-xl bg-[#25D366] px-5 py-2.5 text-sm font-bold text-white">WhatsApp applicant</a>
    </div>
</div>
