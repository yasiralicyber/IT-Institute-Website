<div class="mx-auto max-w-md">
    <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-4xl"></span>
            <h1 class="mt-4 text-2xl font-black text-slate-900">Guardian / Parent Login</h1>
            <p class="mt-2 text-sm text-slate-500">Track your child's attendance, fees, results and progress.</p>
        </div>
        <form action="<?= url('/guardian/login') ?>" method="POST" class="mt-6 space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700">Student Registration No.</label>
                <input name="reg_no" required placeholder="ITTI-2026-0001" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-700">Guardian PIN</label>
                <input name="pin" required type="password" placeholder="••••" class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 focus:border-brand-500 focus:ring-brand-500">
            </div>
            <button class="w-full rounded-xl bg-brand-700 py-3.5 font-bold text-white hover:bg-brand-800">View My Child's Progress</button>
        </form>
        <p class="mt-5 text-center text-xs text-slate-400">Don't have your Reg-No and PIN? Ask the institute office - they'll set up your guardian access.</p>
    </div>
</div>
