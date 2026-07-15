<?php /** @var array $user @var array $projects @var array $types */ ?>
<div class="mb-6 rounded-2xl bg-gradient-to-r from-brand-800 to-brand-950 p-6 text-white">
    <h2 class="text-xl font-black">My Project Portfolio</h2>
    <p class="mt-1 text-sm text-brand-100">Showcase your work. Approved projects appear on your public portfolio you can share with employers.</p>
    <a href="<?= abs_url('/portfolio/' . (int) $user['id']) ?>" target="_blank" class="mt-3 inline-block rounded-lg bg-white/15 px-4 py-2 text-sm font-bold hover:bg-white/25">View my public portfolio →</a>
</div>

<div class="grid gap-6 lg:grid-cols-[360px_1fr]">
    <form action="/my/projects" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <?= csrf_field() ?>
        <h3 class="font-bold text-slate-900 dark:text-white">Submit a Project</h3>
        <input name="title" required placeholder="Project title" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <select name="type" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
            <?php foreach ($types as $t): ?><option><?= e($t) ?></option><?php endforeach; ?>
        </select>
        <textarea name="description" rows="3" placeholder="Describe what you built…" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white"></textarea>
        <input name="link" placeholder="Live link / GitHub (optional)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
        <label class="block text-xs font-semibold text-slate-500">Screenshot (optional)</label>
        <input type="file" name="image" accept="image/*" class="w-full text-xs">
        <label class="block text-xs font-semibold text-slate-500">File / evidence - zip, pdf, image (optional)</label>
        <input type="file" name="file" accept=".zip,.pdf,image/*,.txt" class="w-full text-xs">
        <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Submit for Review</button>
    </form>

    <div>
        <?php if (empty($projects)): ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-slate-900">No projects yet. Submit your first one!</div>
        <?php else: ?>
        <div class="grid gap-4 sm:grid-cols-2">
            <?php foreach ($projects as $p):
                $badge = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700'][$p['status']] ?? ''; ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                <?php if ($p['image']): ?><img src="/project-image/<?= (int) $p['id'] ?>" alt="" class="h-32 w-full object-cover"><?php endif; ?>
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <h4 class="font-bold text-slate-900 dark:text-white"><?= e($p['title']) ?></h4>
                        <span class="flex-none rounded-full px-2 py-0.5 text-[10px] font-bold <?= $badge ?>"><?= ucfirst($p['status']) ?></span>
                    </div>
                    <p class="text-xs text-slate-500"><?= e($p['type']) ?></p>
                    <?php if ($p['status'] === 'rejected' && $p['admin_note']): ?><p class="mt-1 text-xs text-red-500"><?= e($p['admin_note']) ?></p><?php endif; ?>
                    <form action="/my/projects/<?= (int) $p['id'] ?>/delete" method="POST" onsubmit="return confirm('Delete this project?')" class="mt-3">
                        <?= csrf_field() ?><button class="text-xs font-bold text-red-500 hover:text-red-700">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
