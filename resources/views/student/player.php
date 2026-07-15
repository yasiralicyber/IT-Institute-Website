<?php
/** @var array $course @var array $lecture @var array $chapters @var ?array $user @var bool $preview @var bool $hasAccess @var bool $completed */
$videoUrl = $lecture['video_url'] ?? '';
$canWatch = $videoUrl !== '' && ((int) $lecture['is_free'] === 1 || $hasAccess);
$isFile = str_starts_with($videoUrl, 'file:');
$selfHosted = $isFile || (bool) preg_match('/\.(mp4|webm|m4v|ogg)(\?|#|$)/i', $videoUrl);
$videoSrc = $isFile ? url('/media/' . (int) $lecture['id']) : $videoUrl;
$labMapTop = ['ccna-200-301'=>['network','Network Lab'],'cctv-camera-installation'=>['cctv','CCTV Planner'],'cyber-security'=>['cyber','Cyber Lab'],'ethical-hacking'=>['cyber','Cyber Lab']];
$isProg = in_array($course['category'] ?? '', ['Programming','Web Development'], true) || in_array($course['slug'], ['cpp','oop','html','java','python'], true);
if (isset($labMapTop[$course['slug']])) { $practiceUrl = url('/labs/' . $labMapTop[$course['slug']][0]); $practiceLabel = $labMapTop[$course['slug']][1]; }
elseif ($isProg) { $practiceUrl = url('/playground'); $practiceLabel = 'Coding Playground'; }
else { $practiceUrl = url('/playground'); $practiceLabel = 'Coding Playground'; }
?>

<div class="mx-auto max-w-screen-2xl p-3 sm:p-4 lg:p-5">
<div class="flex flex-col gap-4 lg:flex-row lg:items-start">

  <!-- ── LEFT: video card (always full width of left column) ── -->
  <div class="min-w-0 flex-1">
    <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">

      <!-- Video stage (full width of left column, always) -->
      <div id="video-stage" class="relative aspect-video w-full bg-black">

        <?php if ($canWatch && $selfHosted): ?>
        <div id="player" class="absolute inset-0 h-full w-full">
            <video id="v" class="h-full w-full bg-black" src="<?= e($videoSrc) ?>" playsinline preload="metadata"
                   controlslist="nodownload noremoteplayback" disablepictureinpicture oncontextmenu="return false"></video>
            <button id="bigPlay" class="absolute inset-0 z-20 flex items-center justify-center bg-black/20">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-brand-600/90 shadow-2xl"><svg class="h-9 w-9 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
            </button>
            <div id="qOverlay" class="absolute inset-0 z-40 hidden flex-col items-center justify-center bg-black/85 p-6 text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-gold-300">Quick check — answer to continue</p>
                <p id="qText" class="mt-3 max-w-xl text-xl font-bold text-white"></p>
                <div id="qOpts" class="mt-5 grid w-full max-w-md gap-2"></div>
                <p id="qFeedback" class="mt-4 hidden text-sm"></p>
            </div>
            <div id="controls" class="absolute inset-x-0 bottom-0 z-30 bg-gradient-to-t from-black/80 to-transparent px-3 pb-2 pt-8 opacity-0 transition-opacity">
                <div id="bar" class="group relative h-3 cursor-pointer">
                    <div class="absolute top-1/2 h-1.5 w-full -translate-y-1/2 rounded-full bg-white/25"></div>
                    <div id="played" class="absolute top-1/2 h-1.5 -translate-y-1/2 rounded-full bg-brand-500" style="width:0"></div>
                    <div id="markersBar" class="absolute inset-0"></div>
                    <div id="scrub" class="absolute top-1/2 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white opacity-0 group-hover:opacity-100" style="left:0"></div>
                </div>
                <div class="mt-1 flex items-center gap-3 text-white">
                    <button id="pp" aria-label="Play/Pause"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></button>
                    <button id="mute" aria-label="Mute"><svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9v6h4l5 5V4L9 9H5zm11.5 3a4.5 4.5 0 00-2.5-4v8a4.5 4.5 0 002.5-4z"/></svg></button>
                    <span class="text-xs tabular-nums"><span id="cur">0:00</span> / <span id="dur">0:00</span></span>
                    <span class="ml-auto"></span>
                    <button id="speed" class="rounded bg-white/15 px-2 py-0.5 text-xs font-bold">1x</button>
                    <button id="fs" aria-label="Fullscreen"><svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4"/></svg></button>
                </div>
            </div>
        </div>
        <div id="wm" class="wm-tile"></div>
        <div id="blur-note" class="absolute inset-0 z-30 hidden items-center justify-center bg-black/70 text-center">
            <p class="px-6 text-sm font-semibold text-white">Paused — return to this tab to keep watching.<br><span class="text-xs text-slate-300">Recording &amp; screenshots are tracked to your account.</span></p>
        </div>

        <?php elseif ($canWatch): ?>
        <button type="button" id="ytFacade" data-embed="<?= e($videoUrl) ?>?rel=0&modestbranding=1&autoplay=1" class="group absolute inset-0 flex h-full w-full items-center justify-center bg-brand-950">
            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-red-600 shadow-2xl transition group-hover:scale-110"><svg class="h-9 w-9 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
            <span class="absolute bottom-4 text-xs text-white/70">Tap to load the lesson video</span>
        </button>
        <div id="wm" class="wm-tile"></div>
        <div id="blur-note" class="absolute inset-0 z-30 hidden items-center justify-center bg-black/70 text-center">
            <p class="px-6 text-sm font-semibold text-white">Paused — return to this tab to keep watching.</p>
        </div>

        <?php else: ?>
        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-center">
            <svg class="h-14 w-14 text-white/80" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <p class="font-bold text-white">This lesson is locked</p>
            <a href="<?= url('/enroll/' . $course['slug']) ?>" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Enroll to unlock</a>
        </div>
        <?php endif; ?>

      </div><!-- /video-stage -->

      <!-- Lecture meta + toolbar -->
      <div class="border-t border-slate-100 bg-white px-5 py-5 sm:px-6">

        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 class="text-xl font-black text-slate-900 sm:text-2xl"><?= e($lecture['title']) ?></h1>
            <p class="mt-1 text-sm text-slate-500"><?= e($course['title']) ?> · <?= duration_label((int) $lecture['duration_min']) ?></p>
          </div>
          <?php if ($hasAccess && !$preview): ?>
          <form action="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/complete') ?>" method="POST">
              <?= csrf_field() ?>
              <button class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold <?= $completed ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                  <?php if ($completed): ?><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg><?php endif; ?>
                  <?= $completed ? 'Completed' : 'Mark as complete' ?>
              </button>
          </form>
          <?php endif; ?>
        </div>

        <?php if ($lecture['description']): ?>
        <p class="mt-4 text-slate-600"><?= e($lecture['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($expired)): ?>
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
            This lesson's content expired on <?= e(date('d M Y', strtotime($lecture['expires_at']))) ?> and may be outdated.
        </div>
        <?php endif; ?>

        <?php if (!empty($blocks)): ?>
        <div class="mt-5 space-y-3">
            <?php foreach ($blocks as $b):
                $style = ['note'=>'border-sky-200 bg-sky-50','tip'=>'border-emerald-200 bg-emerald-50','warning'=>'border-amber-200 bg-amber-50','code'=>'border-slate-800 bg-slate-900'][$b['type']] ?? 'border-slate-200 bg-slate-50'; ?>
            <div class="rounded-2xl border <?= $style ?> p-4">
                <p class="text-xs font-bold uppercase tracking-wide <?= $b['type']==='code' ? 'text-slate-400' : 'text-slate-500' ?>"><?= e($b['type']) ?> · <?= e($b['title']) ?></p>
                <?php if ($b['type'] === 'code'): ?>
                    <pre class="mt-2 overflow-x-auto text-sm text-emerald-300"><code><?= e($b['body']) ?></code></pre>
                <?php else: ?>
                    <p class="mt-1 text-sm text-slate-700"><?= nl2br(e($b['body'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($needsAck)): ?>
        <form action="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/ack') ?>" method="POST" class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <?= csrf_field() ?>
            <p class="font-bold text-amber-900">Acknowledgment required</p>
            <p class="mt-1 text-sm text-amber-800"><?= e($ackText ?: 'I confirm I have read and understood this lesson.') ?></p>
            <label class="mt-3 flex items-center gap-2 text-sm text-amber-900"><input type="checkbox" name="confirm" required class="rounded border-amber-300"> I acknowledge the above.</label>
            <button class="mt-3 rounded-xl bg-amber-500 px-5 py-2 text-sm font-bold text-white hover:bg-amber-600">Confirm</button>
        </form>
        <?php endif; ?>

        <?php if ($preview): ?>
        <div class="mt-5 rounded-2xl border border-brand-100 bg-brand-50 p-5">
            <p class="font-bold text-brand-900">You're watching a free preview</p>
            <p class="mt-1 text-sm text-brand-700">Enroll to unlock all lessons, chapter tests and your certificate.</p>
            <a href="<?= url('/enroll/' . $course['slug']) ?>" class="mt-3 inline-block rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Enroll in <?= e($course['title']) ?></a>
        </div>
        <?php endif; ?>

        <!-- Toolbar -->
        <?php
        $labMap = ['ccna-200-301'=>['network','Network Topology Lab'],'cctv-camera-installation'=>['cctv','CCTV Coverage Planner'],'cyber-security'=>['cyber','Cyber Investigation'],'ethical-hacking'=>['cyber','Cyber Investigation']];
        $isProgramming = in_array($course['category'] ?? '', ['Programming','Web Development'], true) || in_array($course['slug'], ['cpp','oop','html','java','python'], true);
        $tbtn = 'inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200';
        ?>
        <div class="mt-5 flex flex-wrap gap-2">
            <button type="button" onclick="togglePractice()" id="practiceBtn" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700"><?= icon('beaker','h-4 w-4') ?> Practice while watching</button>
            <button type="button" onclick="togglePanel('panel-labs',this)" class="<?= $tbtn ?>"><?= icon('rocket','h-4 w-4') ?> Lab &amp; Practice</button>
            <?php if ($hasAccess && !$preview): ?><button type="button" onclick="togglePanel('panel-notes',this)" class="<?= $tbtn ?>"><?= icon('note','h-4 w-4') ?> Notes &amp; Bookmarks</button><?php endif; ?>
            <a href="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/notes.txt') ?>" class="<?= $tbtn ?>"><?= icon('save','h-4 w-4') ?> Download</a>
            <?php if ($hasAccess): ?><a href="<?= url('/learn/' . $course['slug'] . '/roadmap') ?>" class="<?= $tbtn ?>"><?= icon('chart','h-4 w-4') ?> Learning Path</a><?php endif; ?>
            <button type="button" onclick="toggleSaver()" id="saverBtn" class="<?= $tbtn ?>">Data Saver: <span id="saverState">Off</span></button>
        </div>

        <!-- Lab & Practice (expandable) -->
        <div id="panel-labs" class="mt-4 rounded-2xl border border-brand-100 bg-brand-50 p-5" style="display:none">
            <p class="flex items-center gap-2 font-bold text-brand-900"><span class="text-brand-600"><?= icon('beaker','h-5 w-5') ?></span> Practice &amp; Labs</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" onclick="togglePractice(true)" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white hover:bg-brand-700">Practice beside the video</button>
                <?php if (isset($labMap[$course['slug']])): ?>
                    <a href="<?= url('/labs/' . $labMap[$course['slug']][0]) ?>" target="_blank" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">Open <?= e($labMap[$course['slug']][1]) ?> ↗</a>
                <?php endif; ?>
                <?php if ($isProgramming): ?>
                    <a href="<?= url('/playground') ?>" target="_blank" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">Coding Playground ↗</a>
                <?php endif; ?>
                <a href="<?= url('/achievements') ?>" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50">My Achievements</a>
            </div>
            <p class="mt-2 text-xs text-brand-700">Click <strong>Practice while watching</strong> — course content hides and the IDE opens beside your video.</p>
        </div>

        <?php if ($hasAccess && !$preview): ?>
        <!-- Notes & bookmarks (expandable) -->
        <div id="panel-notes" class="mt-5 grid gap-4 sm:grid-cols-2" style="display:none">
            <form action="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/note') ?>" method="POST" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <?= csrf_field() ?>
                <p class="flex items-center gap-2 font-bold text-slate-800"><?= icon('note','h-5 w-5') ?> My Notes</p>
                <textarea name="body" rows="4" placeholder="Write your notes for this lesson…" class="mt-3 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-brand-400 focus:ring-brand-400"><?= e($note) ?></textarea>
                <button class="mt-3 rounded-xl bg-brand-600 px-5 py-2 text-sm font-bold text-white hover:bg-brand-700">Save Notes</button>
            </form>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="flex items-center gap-2 font-bold text-slate-800"><?= icon('bookmark','h-5 w-5') ?> Bookmarks</p>
                <form action="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/bookmark') ?>" method="POST" class="mt-3 flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="seconds" id="bmSeconds" value="0">
                    <input name="label" placeholder="Bookmark this moment…" class="flex-1 rounded-xl border-slate-200 bg-white px-3 py-2 text-sm text-slate-800">
                    <button onclick="document.getElementById('bmSeconds').value=Math.floor((document.getElementById('v')||{}).currentTime||0)" class="rounded-xl bg-brand-600 px-3 py-2 text-sm font-bold text-white">Add</button>
                </form>
                <div class="mt-3 space-y-1.5">
                    <?php foreach ($bookmarks as $bm): $mm = floor($bm['seconds'] / 60); $ss = $bm['seconds'] % 60; ?>
                    <div class="flex items-center justify-between rounded-lg bg-white px-3 py-1.5 text-sm ring-1 ring-slate-100">
                        <button type="button" onclick="seekTo(<?= (int) $bm['seconds'] ?>)" class="font-mono text-brand-600 hover:underline"><?= sprintf('%d:%02d', $mm, $ss) ?></button>
                        <span class="flex-1 px-2 text-slate-600"><?= e($bm['label'] ?: 'Bookmark') ?></span>
                        <form action="<?= url('/learn/' . $course['slug'] . '/' . (int) $lecture['id'] . '/bookmark/' . (int) $bm['id'] . '/delete') ?>" method="POST"><?= csrf_field() ?><button class="text-red-400 hover:text-red-600">✕</button></form>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($bookmarks)): ?><p class="text-xs text-slate-400">No bookmarks yet. Play the video and add one.</p><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course review -->
        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <p class="font-bold text-slate-800">Rate this course</p>
            <?php if ($m = flash('success')): ?><p class="mt-2 rounded-lg bg-emerald-100 px-3 py-2 text-sm text-emerald-800"><?= e($m) ?></p><?php endif; ?>
            <form action="<?= url('/courses/' . $course['slug'] . '/review') ?>" method="POST" class="mt-3">
                <?= csrf_field() ?>
                <div class="flex flex-row-reverse justify-end gap-1">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <input type="radio" name="rating" id="st<?= $s ?>" value="<?= $s ?>" class="peer hidden" <?= $s === 5 ? 'checked' : '' ?>>
                    <label for="st<?= $s ?>" class="cursor-pointer text-2xl text-slate-300 peer-checked:text-amber-400 hover:text-amber-400">★</label>
                    <?php endfor; ?>
                </div>
                <textarea name="body" rows="2" placeholder="Share your experience (optional)…" class="mt-3 w-full rounded-xl border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 focus:border-brand-400 focus:ring-brand-400"></textarea>
                <button class="mt-3 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700">Submit Review</button>
            </form>
        </div>
        <?php endif; ?>

      </div><!-- /lecture meta -->
    </div><!-- /main player card -->
  </div><!-- /left col -->

  <!-- ── RIGHT: Curriculum sidebar (hidden when practice is open) ── -->
  <aside id="curriculumCol" class="w-full flex-none lg:w-80 xl:w-96">
    <div class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 lg:sticky lg:top-4">
      <div class="border-b border-slate-100 px-5 py-4">
          <h2 class="font-bold text-slate-900">Course content</h2>
      </div>
      <div class="max-h-[72vh] overflow-y-auto">
          <?php foreach ($chapters as $ci => $ch): ?>
          <div class="border-b border-slate-100">
              <div class="flex items-center justify-between bg-slate-50 px-5 py-3">
                  <p class="text-sm font-bold text-slate-800"><?= ($ci + 1) . '. ' . e($ch['title']) ?></p>
                  <?php if ($ch['passed']): ?><span class="text-xs font-bold text-emerald-600">Passed ✓</span>
                  <?php elseif (!$ch['unlocked'] && !$preview): ?><svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><?php endif; ?>
              </div>
              <div>
                  <?php foreach ($ch['lectures'] as $l):
                      $free = (int) $l['is_free'] === 1;
                      $released = $l['released'] ?? true;
                      $open = $free || ($hasAccess && $ch['unlocked'] && $released);
                      $isCurrent = (int) $l['id'] === (int) $lecture['id'];
                      $href = $free && !$hasAccess ? url('/learn/preview/' . $l['id']) : url('/learn/' . $course['slug'] . '/' . $l['id']);
                  ?>
                  <a href="<?= $open ? $href : '#' ?>" class="flex items-center gap-3 px-5 py-2.5 text-sm <?= $isCurrent ? 'border-l-2 border-brand-500 bg-brand-50' : ($open ? 'hover:bg-slate-50' : 'cursor-default opacity-40') ?>">
                      <span class="flex h-6 w-6 flex-none items-center justify-center rounded-md <?= $l['completed'] ? 'bg-emerald-100 text-emerald-600' : ($open ? 'bg-slate-100 text-slate-500' : 'bg-slate-50 text-slate-300') ?>">
                          <?php if ($l['completed']): ?><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                          <?php elseif ($open): ?><svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                          <?php else: ?><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg><?php endif; ?>
                      </span>
                      <span class="flex-1 <?= $isCurrent ? 'font-bold text-brand-700' : 'text-slate-700' ?>"><?= e($l['title']) ?></span>
                      <?php if (!$released && $hasAccess): ?><span class="text-[10px] font-bold text-amber-500"><?= e(date('d M', strtotime($l['release_at']))) ?></span>
                      <?php elseif ($free): ?><span class="text-[10px] font-bold text-emerald-600">FREE</span><?php endif; ?>
                  </a>
                  <?php endforeach; ?>

                  <?php if ($ch['quiz'] && $hasAccess && $ch['unlocked']): ?>
                  <a href="<?= url('/learn/' . $course['slug'] . '/test/' . $ch['id']) ?>" class="flex items-center gap-3 bg-amber-50 px-5 py-2.5 text-sm hover:bg-amber-100">
                      <span class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-100 text-amber-600"><svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                      <span class="flex-1 font-semibold text-amber-700"><?= $ch['passed'] ? 'Retake chapter test' : 'Take chapter test to continue' ?></span>
                  </a>
                  <?php endif; ?>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
    </div>
  </aside>

  <!-- ── RIGHT: Practice IDE (replaces curriculum when open) ── -->
  <div id="practiceCol" class="w-full flex-none lg:w-80 xl:w-96" style="display:none">
    <div class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200 lg:sticky lg:top-4" style="height:calc(100vh - 90px);min-height:520px">
      <!-- IDE header -->
      <div class="flex flex-none items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
          <p class="flex items-center gap-2 text-sm font-bold text-slate-800"><?= icon('beaker','h-4 w-4 text-brand-500') ?> <?= e($practiceLabel) ?></p>
          <div class="flex items-center gap-2">
              <a href="<?= e($practiceUrl) ?>" target="_blank" rel="noopener" class="text-xs font-semibold text-brand-600 hover:underline">Full screen ↗</a>
              <button type="button" onclick="togglePractice(false)" class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 hover:bg-slate-200">
                  <svg class="inline h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                  Close &amp; show content
              </button>
          </div>
      </div>
      <!-- IDE iframe fills all remaining height -->
      <iframe id="practiceFrame" class="flex-1 w-full bg-white" style="border:none;display:block" src="about:blank" title="Practice IDE" sandbox="allow-scripts allow-same-origin allow-forms allow-modals allow-popups"></iframe>
    </div>
  </div>

</div><!-- /flex row -->
</div><!-- /page wrapper -->

<div id="protect-warning" class="fixed bottom-5 left-1/2 z-50 hidden -translate-x-1/2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-2xl">
    Downloading, copying or recording lessons is not allowed and is traced to your account.
</div>

<script>
var PRACTICE_URL = <?= json_encode($practiceUrl) ?>;

function togglePractice(on) {
    var practice = document.getElementById('practiceCol');
    var curriculum = document.getElementById('curriculumCol');
    var btn = document.getElementById('practiceBtn');
    if (!practice || !curriculum) return;
    if (on === undefined) on = practice.style.display === 'none';

    if (on) {
        curriculum.style.display = 'none';
        practice.style.display = '';
        var f = document.getElementById('practiceFrame');
        if (f && (f.src === 'about:blank' || !f.dataset.loaded)) {
            f.src = PRACTICE_URL;
            f.dataset.loaded = '1';
        }
        if (btn) { btn.classList.add('bg-brand-700'); }
    } else {
        practice.style.display = 'none';
        curriculum.style.display = '';
        if (btn) { btn.classList.remove('bg-brand-700'); }
    }
}

function togglePanel(id, btn) {
    var p = document.getElementById(id);
    if (!p) return;
    var show = getComputedStyle(p).display === 'none';
    p.style.display = show ? '' : 'none';
    if (btn) {
        btn.classList.toggle('bg-brand-600', show);
        btn.classList.toggle('text-white', show);
        btn.classList.toggle('bg-slate-100', !show);
        btn.classList.toggle('text-slate-700', !show);
    }
    if (show) p.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

(function () {
    var lid = <?= (int) $lecture['id'] ?>;
    var v = document.getElementById('v');
    var facade = document.getElementById('ytFacade');

    function saverOn() { return localStorage.dataSaver === '1'; }
    function applySaver() { var st = document.getElementById('saverState'); if (st) st.textContent = saverOn() ? 'On' : 'Off'; if (v) v.preload = saverOn() ? 'none' : 'metadata'; }
    window.toggleSaver = function () { localStorage.dataSaver = saverOn() ? '0' : '1'; applySaver(); };
    applySaver();

    if (facade) {
        facade.addEventListener('click', function () {
            var ifr = document.createElement('iframe');
            ifr.className = 'absolute inset-0 h-full w-full';
            ifr.src = facade.dataset.embed;
            ifr.allow = 'accelerometer; encrypted-media; gyroscope; picture-in-picture';
            ifr.allowFullscreen = true;
            ifr.referrerPolicy = 'strict-origin';
            facade.replaceWith(ifr);
        });
    }

    window.seekTo = function (s) {
        if (v) { v.currentTime = s; v.play && v.play().catch(function () {}); window.scrollTo(0, 0); }
    };

    if (!v) return;

    var MARKERS = <?= json_encode(array_map(fn($m) => ['t' => (int) $m['seconds'], 'label' => $m['label']], $markers)) ?>;
    var QUESTIONS = <?= json_encode(array_map(fn($q) => ['t' => (int) $q['seconds'], 'q' => $q['question'], 'o' => (json_decode($q['options'], true) ?: []), 'c' => (int) $q['correct_index']], $questions)) ?>;
    var answered = new Set();
    var fmt = function (s) { s = Math.floor(s || 0); return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0'); };
    var $ = function (id) { return document.getElementById(id); };
    var player = $('player'), controls = $('controls'), bigPlay = $('bigPlay'), pp = $('pp');
    var bar = $('bar'), played = $('played'), scrub = $('scrub'), ov = $('qOverlay');

    function toggle() { if (v.paused) v.play(); else v.pause(); }
    bigPlay.onclick = toggle;
    v.addEventListener('click', toggle);
    pp.onclick = toggle;

    var SVG_PLAY = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>';
    var SVG_PAUSE = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 4h4v16H6zM14 4h4v16h-4z"/></svg>';
    var SVG_VOL = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9v6h4l5 5V4L9 9H5zm11.5 3a4.5 4.5 0 00-2.5-4v8a4.5 4.5 0 002.5-4z"/></svg>';
    var SVG_MUTE = '<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9v6h4l5 5V4L9 9H5z"/><path stroke="currentColor" stroke-width="2" d="M16 9l5 6m0-6l-5 6"/></svg>';

    v.addEventListener('play', function () { pp.innerHTML = SVG_PAUSE; bigPlay.style.display = 'none'; });
    v.addEventListener('pause', function () { pp.innerHTML = SVG_PLAY; if (ov.classList.contains('hidden')) bigPlay.style.display = ''; });
    player.addEventListener('mousemove', function () {
        controls.style.opacity = 1;
        clearTimeout(window.__ct);
        window.__ct = setTimeout(function () { if (!v.paused) controls.style.opacity = 0; }, 2500);
    });
    controls.style.opacity = 1;

    v.addEventListener('loadedmetadata', function () {
        $('dur').textContent = fmt(v.duration);
        renderMarkers();
        var t = parseFloat(localStorage.getItem('resume_' + lid) || '0');
        if (t > 5 && t < v.duration - 5) v.currentTime = t;
    });
    var lastSave = 0;
    v.addEventListener('timeupdate', function () {
        var p = (v.currentTime / v.duration * 100) || 0;
        played.style.width = p + '%';
        scrub.style.left = p + '%';
        $('cur').textContent = fmt(v.currentTime);
        if (Date.now() - lastSave > 4000) { lastSave = Date.now(); localStorage.setItem('resume_' + lid, v.currentTime); }
        checkQ(v.currentTime);
    });
    bar.addEventListener('click', function (e) { var r = bar.getBoundingClientRect(); v.currentTime = (e.clientX - r.left) / r.width * v.duration; });

    function renderMarkers() {
        var mb = $('markersBar');
        if (!mb || !v.duration) return;
        mb.innerHTML = MARKERS.map(function (m) {
            return '<span title="' + (m.label || '').replace(/"/g, '&quot;') + '" data-t="' + m.t + '" class="mk absolute top-1/2 h-3 w-1 -translate-y-1/2 cursor-pointer rounded bg-gold-400" style="left:' + (m.t / v.duration * 100) + '%"></span>';
        }).join('');
        mb.querySelectorAll('.mk').forEach(function (s) {
            s.addEventListener('click', function (ev) { ev.stopPropagation(); v.currentTime = +s.dataset.t; v.play(); });
        });
    }

    var speeds = [1, 1.25, 1.5, 2]; var si = 0;
    $('speed').onclick = function () { si = (si + 1) % speeds.length; v.playbackRate = speeds[si]; $('speed').textContent = speeds[si] + 'x'; };
    $('mute').onclick = function () { v.muted = !v.muted; this.innerHTML = v.muted ? SVG_MUTE : SVG_VOL; };
    $('fs').onclick = function () { if (document.fullscreenElement) document.exitFullscreen(); else player.requestFullscreen && player.requestFullscreen(); };

    function checkQ(t) {
        if (!ov.classList.contains('hidden')) return;
        for (var i = 0; i < QUESTIONS.length; i++) {
            if (answered.has(i)) continue;
            if (t >= QUESTIONS[i].t && t < QUESTIONS[i].t + 1.5) { showQ(i); break; }
        }
    }
    window.__checkQ = checkQ;

    function showQ(i) {
        var q = QUESTIONS[i];
        v.pause();
        ov.classList.remove('hidden'); ov.classList.add('flex'); bigPlay.style.display = 'none';
        $('qText').textContent = q.q;
        var fb = $('qFeedback'); fb.classList.add('hidden');
        var box = $('qOpts'); box.innerHTML = '';
        q.o.forEach(function (opt, oi) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'rounded-xl bg-white/10 px-4 py-2.5 text-left text-white hover:bg-white/20';
            b.textContent = opt;
            b.onclick = function () { answer(i, oi, b); };
            box.appendChild(b);
        });
    }

    function answer(i, oi, btn) {
        var q = QUESTIONS[i]; var fb = $('qFeedback');
        if (oi === q.c) {
            btn.className = 'rounded-xl bg-emerald-600 px-4 py-2.5 text-left text-white';
            fb.textContent = '✓ Correct!'; fb.className = 'mt-4 text-sm font-bold text-emerald-300';
            answered.add(i); setTimeout(closeQ, 700);
        } else {
            btn.className = 'rounded-xl bg-red-600 px-4 py-2.5 text-left text-white';
            fb.textContent = 'Not quite — try again.'; fb.className = 'mt-4 text-sm font-bold text-amber-300';
        }
        fb.classList.remove('hidden');
    }

    function closeQ() { ov.classList.add('hidden'); ov.classList.remove('flex'); v.play(); }
})();
</script>
