<?php /** @var array $student @var array $enrollments @var array $purchases @var array $devices @var array $certs */
use App\Models\Security;
?>
<a href="/students" class="text-sm font-semibold text-brand-600 hover:underline">← All students</a>
<?php if (!Security::canViewField('cnic') || !Security::canViewField('guardian') || !Security::canViewField('fees')): ?>
<p class="mt-2 inline-block rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10">Some fields are hidden by your staff role. Ask a Super admin for full access.</p>
<?php endif; ?>

<div class="mt-4 grid gap-6 lg:grid-cols-3">
    <!-- Profile + actions -->
    <div class="space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-white/10 dark:bg-slate-900">
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-brand-600 text-2xl font-black text-white"><?= e(strtoupper(substr($student['name'], 0, 1))) ?></span>
            <h2 class="mt-3 text-lg font-black text-slate-900 dark:text-white"><?= e($student['name']) ?></h2>
            <p class="text-sm text-slate-500"><?= e($student['email']) ?></p>
            <p class="text-sm text-slate-500"><?= e(Security::mask('contact', $student['phone'] ?: '-')) ?></p>
            <?php if ($student['cnic'] ?? null): ?><p class="text-xs text-slate-400">CNIC: <?= e(Security::mask('cnic', $student['cnic'])) ?></p><?php endif; ?>
            <span class="mt-3 inline-block rounded-full px-3 py-1 text-xs font-bold <?= $student['status'] === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($student['status']) ?></span>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <a href="/students/<?= (int) $student['id'] ?>/id-card" target="_blank" class="rounded-xl bg-brand-700 px-3 py-2.5 text-center text-sm font-bold text-white hover:bg-brand-800">ID Card</a>
            <a href="/fees/<?= (int) $student['id'] ?>" class="rounded-xl bg-slate-100 px-3 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-200">Fees</a>
            <a href="/students/<?= (int) $student['id'] ?>/timeline" class="rounded-xl bg-slate-100 px-3 py-2.5 text-center text-sm font-bold text-slate-700 hover:bg-slate-200 dark:bg-white/10 dark:text-slate-200">Timeline</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Guardian / Parent Access</h3>
            <?php if (!Security::canViewField('guardian')): ?>
            <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:bg-white/5">Guardian details are hidden for your role (<?= e($student['guardian_name'] ? '••••••' : 'none set') ?>).</p>
            <?php else: ?>
            <form action="/students/<?= (int) $student['id'] ?>/guardian" method="POST" class="space-y-2">
                <?= csrf_field() ?>
                <input name="father_name" value="<?= e($student['father_name'] ?? '') ?>" placeholder="Father's name (shown on ID card)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <div class="grid grid-cols-2 gap-2">
                    <input name="dob" value="<?= e($student['dob'] ?? '') ?>" placeholder="D.O.B (e.g. 12/03/2005)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                    <input name="blood_group" value="<?= e($student['blood_group'] ?? '') ?>" placeholder="Blood group (e.g. B+)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                </div>
                <input name="address" value="<?= e($student['address'] ?? '') ?>" placeholder="Address (shown on ID card back)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <p class="text-xs text-slate-400">D.O.B / Blood group / Address appear on the printed ID card.</p>
                <input name="guardian_name" value="<?= e($student['guardian_name'] ?? '') ?>" placeholder="Guardian name" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <input name="guardian_phone" value="<?= e($student['guardian_phone'] ?? '') ?>" placeholder="Guardian phone" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <input name="guardian_pin" value="<?= e($student['guardian_pin'] ?? '') ?>" placeholder="Guardian PIN (e.g. 1234)" class="w-full rounded-xl border-slate-300 bg-white px-4 py-2.5 text-sm dark:border-white/15 dark:bg-slate-800 dark:text-white">
                <button class="w-full rounded-xl bg-brand-600 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Save Student & Guardian Details</button>
            </form>
            <p class="mt-2 text-xs text-slate-400">Parent logs in at <strong><?= e((parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'ittimaidan.com') . '/guardian') ?></strong> with the student's Reg-No <?= $student['reg_no'] ? '(<strong>' . e($student['reg_no']) . '</strong>)' : '(generated on first ID card)' ?> + this PIN.</p>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Account actions</h3>
            <form action="/students/<?= (int) $student['id'] ?>/status" method="POST" class="mb-2">
                <?= csrf_field() ?>
                <?php if ($student['status'] === 'active'): ?>
                    <input type="hidden" name="action" value="suspend">
                    <button onclick="return confirm('Suspend this student? They will be logged out.')" class="w-full rounded-xl border border-red-300 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 dark:border-red-500/40 dark:hover:bg-red-500/10">Suspend account</button>
                <?php else: ?>
                    <input type="hidden" name="action" value="activate">
                    <button class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Re-activate account</button>
                <?php endif; ?>
            </form>
            <form action="/students/<?= (int) $student['id'] ?>/reset-devices" method="POST">
                <?= csrf_field() ?>
                <button onclick="return confirm('Reset device locks for this student?')" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-white/15 dark:text-white dark:hover:bg-white/5">Reset device locks</button>
            </form>
        </div>

        <!-- Devices -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Registered devices (<?= count($devices) ?>/2)</h3>
            <?php if (empty($devices)): ?><p class="text-sm text-slate-500">No devices registered.</p>
            <?php else: foreach ($devices as $d): ?>
                <div class="mb-2 flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                    <span><?= $d['device_type'] === 'mobile' ? '' : '' ?></span>
                    <span class="flex-1 text-slate-700 dark:text-slate-200"><?= e($d['label']) ?></span>
                </div>
            <?php endforeach; endif; ?>
            <?php if ((int) $student['device_violations'] > 0): ?>
                <p class="mt-2 text-xs font-semibold text-amber-600"><?= (int) $student['device_violations'] ?> sharing violation(s) recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enrollments + purchases + certs -->
    <div class="space-y-4 lg:col-span-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Enrolled Courses</h3>
            <?php if (empty($enrollments)): ?><p class="text-sm text-slate-500">No active enrollments.</p>
            <?php else: foreach ($enrollments as $e): ?>
                <div class="mb-2 flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 dark:border-white/5">
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= e($e['title']) ?></span>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Active</span>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Payment History</h3>
            <?php if (empty($purchases)): ?><p class="text-sm text-slate-500">No payment requests.</p>
            <?php else: foreach ($purchases as $p):
                $badge = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','declined'=>'bg-red-100 text-red-700'][$p['status']] ?? ''; ?>
                <div class="mb-2 flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                    <span class="text-slate-700 dark:text-slate-200"><?= e($p['title']) ?> · <span class="text-slate-400"><?= pkr($p['amount']) ?></span></span>
                    <div class="flex items-center gap-2">
                        <a href="/receipt/<?= (int) $p['id'] ?>" target="_blank" class="text-xs font-bold text-brand-600 hover:underline">Receipt</a>
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold <?= $badge ?>"><?= ucfirst($p['status']) ?></span>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <h3 class="mb-3 font-bold text-slate-900 dark:text-white">Certificates (<?= count($certs) ?>)</h3>
            <?php if (empty($certs)): ?><p class="text-sm text-slate-500">None issued yet.</p>
            <?php else: foreach ($certs as $c): ?>
                <div class="mb-2 flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm dark:border-white/5">
                    <span class="text-slate-700 dark:text-slate-200"><?= e($c['course_title']) ?> · <span class="text-xs text-slate-400"><?= ucfirst($c['type']) ?></span></span>
                    <a href="<?= abs_url('/certificate/' . urlencode($c['credential_id'])) ?>" target="_blank" class="font-mono text-xs text-brand-600 hover:underline"><?= e($c['credential_id']) ?></a>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
