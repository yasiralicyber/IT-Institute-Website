<?php /** @var array $user @var array $stats @var array $badges @var array $board @var ?int $rank */
use App\Models\Gamification; ?>
<div class="mb-6 grid gap-4 sm:grid-cols-3">
    <div class="rounded-2xl bg-gradient-to-br from-brand-700 to-brand-950 p-6 text-white sm:col-span-2">
        <div class="flex items-center justify-between">
            <div><p class="text-sm text-brand-200">Level</p><p class="text-4xl font-black"><?= $stats['level'] ?></p></div>
            <div class="text-right"><p class="text-sm text-brand-200">Total XP</p><p class="text-3xl font-black text-gold-400"><?= number_format($stats['xp']) ?></p></div>
        </div>
        <div class="mt-4">
            <div class="h-3 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-gold-400" style="width: <?= $stats['progress'] ?>%"></div></div>
            <p class="mt-1 text-xs text-brand-200"><?= $stats['into_level'] ?> / <?= $stats['next_level_xp'] ?> XP to level <?= $stats['level'] + 1 ?></p>
        </div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-white/10 dark:bg-slate-900">
        <p class="text-sm text-slate-500">Leaderboard Rank</p>
        <p class="mt-1 text-4xl font-black text-brand-700 dark:text-brand-300"><?= $rank ? '#' . $rank : '-' ?></p>
        <p class="text-xs text-slate-400"><?= count(array_filter($badges, fn($b)=>$b['earned'])) ?> / <?= count($badges) ?> badges earned</p>
    </div>
</div>

<h2 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Badges</h2>
<div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <?php foreach ($badges as $b): ?>
    <div class="rounded-2xl border p-5 text-center <?= $b['earned'] ? 'border-gold-300 bg-gold-50 dark:border-gold-500/30 dark:bg-gold-500/10' : 'border-slate-200 bg-white opacity-60 dark:border-white/10 dark:bg-slate-900' ?>">
        <span class="text-4xl <?= $b['earned'] ? '' : 'grayscale' ?>"><?= $b['icon'] ?></span>
        <p class="mt-2 font-bold text-slate-900 dark:text-white"><?= e($b['label']) ?></p>
        <p class="text-xs text-slate-500"><?= $b['earned'] ? 'Earned ✓' : $b['hint'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<h2 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">Leaderboard</h2>
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
    <?php foreach ($board as $i => $r): $me = $r['id'] === (int) $user['id']; ?>
    <div class="flex items-center gap-4 border-b border-slate-100 px-5 py-3 last:border-0 dark:border-white/5 <?= $me ? 'bg-brand-50 dark:bg-brand-500/10' : '' ?>">
        <span class="w-8 text-center text-lg font-black <?= $i < 3 ? 'text-gold-500' : 'text-slate-400' ?>"><?= $i + 1 ?></span>
        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-700 text-sm font-bold text-white"><?= e(strtoupper(substr($r['name'],0,1))) ?></span>
        <span class="flex-1 font-semibold text-slate-800 dark:text-slate-200"><?= e($me ? 'You' : Gamification::publicName($r['name'])) ?></span>
        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Lvl <?= $r['level'] ?></span>
        <span class="w-20 text-right font-black text-brand-700 dark:text-brand-300"><?= number_format($r['xp']) ?> XP</span>
    </div>
    <?php endforeach; ?>
    <?php if (empty($board)): ?><p class="px-5 py-8 text-center text-slate-500">Start learning to climb the leaderboard!</p><?php endif; ?>
</div>
