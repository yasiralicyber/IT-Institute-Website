<a href="/attendance" class="text-sm font-semibold text-brand-600 hover:underline">← Back to attendance</a>

<div class="mt-4 max-w-3xl space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h2 class="text-xl font-black text-slate-900 dark:text-white">Connecting a Fingerprint / Biometric Device</h2>
        <p class="mt-2 text-slate-600 dark:text-slate-300">Our website runs on shared hosting and can't talk to a USB device directly. The simplest, most reliable way is the <strong>export &amp; upload</strong> method below - it works with almost any device (ZKTeco, etc.).</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">Method 1 - Export &amp; Upload (recommended, easiest)</h3>
        <ol class="mt-3 space-y-3 text-sm text-slate-600 dark:text-slate-300">
            <li><strong>1.</strong> When you enroll a student on the fingerprint device, use the <strong>same Roll No.</strong> as their roll number in this system (set it on the batch page). This is what links the two.</li>
            <li><strong>2.</strong> At the end of the day/month, open your device's PC software (e.g. ZKTeco Attendance) and <strong>export the report as CSV/Excel</strong>.</li>
            <li><strong>3.</strong> Make sure the file has columns <code class="rounded bg-slate-100 px-1 dark:bg-white/10">roll_no, date, status</code> (rename columns if needed, or use email instead of roll_no).</li>
            <li><strong>4.</strong> Open the batch → <strong>Import CSV</strong> → upload the file. Done - attendance appears instantly in the report.</li>
        </ol>
        <p class="mt-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">No technical setup, no extra cost, works with any device that can export attendance.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">Method 2 - Automatic sync (advanced, optional)</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">If you want attendance to flow in automatically, a small program on the office PC can read the device and post to a secure import URL. This needs a developer to set up once. When you're ready, we can enable a protected <code class="rounded bg-slate-100 px-1 dark:bg-white/10">/api/attendance</code> endpoint (token-secured) and provide a ready-made ZKTeco→website sync script. Tell us your device model and we'll prepare it.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-white/10 dark:bg-slate-900">
        <h3 class="font-bold text-slate-900 dark:text-white">Method 3 - QR self check-in (no device needed)</h3>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Open a batch → <strong>QR Check-in</strong>, then display or print the QR. Students scan it with their phone (while logged in) to mark themselves present. Great as a no-hardware option or a backup.</p>
    </div>
</div>
