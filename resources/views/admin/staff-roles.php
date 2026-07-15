<?php /** @var array $admins @var array $roles @var int $me */ ?>
<p class="text-sm text-slate-500">Staff roles control which sensitive fields each administrator can see (CNIC, guardian contact, fee details). "Super" sees everything; other roles see only what their job needs.</p>

<div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400 dark:bg-white/5">
            <tr><th class="px-5 py-3">Administrator</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">Role</th></tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <tr class="border-t border-slate-100 dark:border-white/5">
                <td class="px-5 py-3 font-semibold text-slate-800 dark:text-slate-200"><?= e($a['name']) ?><?= (int) $a['id'] === $me ? ' (you)' : '' ?></td>
                <td class="px-5 py-3 text-slate-500"><?= e($a['email']) ?></td>
                <td class="px-5 py-3">
                    <form method="post" action="/staff-roles/<?= (int) $a['id'] ?>" class="flex items-center gap-2">
                        <?= csrf_field() ?>
                        <select name="staff_role" class="rounded-xl border-slate-300 bg-white px-3 py-1.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                            <?php foreach ($roles as $k => $v): ?><option value="<?= e($k) ?>" <?= ($a['staff_role'] ?: 'super') === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
                        </select>
                        <button class="rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white dark:bg-white/10">Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
    <h3 class="font-black text-slate-900 dark:text-white">What each role sees</h3>
    <div class="mt-3 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase text-slate-400"><tr><th class="py-2">Role</th><th class="py-2 text-center">CNIC</th><th class="py-2 text-center">Guardian</th><th class="py-2 text-center">Fees</th><th class="py-2 text-center">Contact</th></tr></thead>
            <tbody>
            <?php foreach (['super'=>[1,1,1,1],'finance'=>[0,0,1,1],'academic'=>[0,1,0,1],'front_desk'=>[0,0,0,1]] as $role => $perms): ?>
                <tr class="border-t border-slate-100 dark:border-white/5">
                    <td class="py-2 font-semibold"><?= e($roles[$role] ?? $role) ?></td>
                    <?php foreach ($perms as $p): ?><td class="py-2 text-center"><?= $p ? '<span class="text-emerald-600">✓</span>' : '<span class="text-slate-300">—</span>' ?></td><?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
