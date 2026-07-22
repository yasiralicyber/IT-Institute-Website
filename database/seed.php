<?php
/**
 * Seed the database with courses, chapters, lectures, a demo admin,
 * a demo student, settings, timetable and a couple of reviews.
 *
 * Run:  php database/seed.php   (run AFTER migrate.php)
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$pdo = Database::pdo();

// ---- Admin + demo student -------------------------------------------------
$pdo->prepare("INSERT INTO users (role,name,email,password,phone,status,email_verified_at)
               VALUES ('admin', ?, ?, ?, ?, 'active', ?)")
    ->execute(['Institute Admin', 'admin@itti.com.pk',
               password_hash('admin1234', PASSWORD_DEFAULT), '+92 305 8382085', date('Y-m-d H:i:s')]);

$pdo->prepare("INSERT INTO users (role,name,email,password,phone,status,email_verified_at)
               VALUES ('student', ?, ?, ?, ?, 'active', ?)")
    ->execute(['Demo Student', 'student@itti.com.pk',
               password_hash('student1234', PASSWORD_DEFAULT), '+92 300 0000000', date('Y-m-d H:i:s')]);

echo "  ✓ users seeded (admin@itti.com.pk / admin1234, student@itti.com.pk / student1234)\n";

// ---- Courses --------------------------------------------------------------
$courses = require __DIR__ . '/data/courses.php';
// Rotating dummy lesson videos so every lecture is playable for demos.
$sampleVideos = [
    'https://www.youtube.com/embed/rfscVS0vtbw',
    'https://www.youtube.com/embed/UB1O30fR-EE',
    'https://www.youtube.com/embed/zOjov-2OZ0E',
    'https://www.youtube.com/embed/_uQrJ0TkZlc',
    'https://www.youtube.com/embed/8jLOx1hD3_o',
];

$courseSort = 0;
foreach ($courses as $c) {
    $courseSort++;
    $courseId = Database::run(
        "INSERT INTO courses (slug,title,subtitle,category,level,description,outcomes,price,accent,icon,sort,is_published)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,1)",
        [$c['slug'], $c['title'], $c['subtitle'], $c['category'], $c['level'],
         $c['description'], json_encode($c['outcomes']), $c['price'], $c['accent'], $c['icon'], $courseSort]
    );

    $lectureCounter = 0;   // first 5 lectures of each course are free
    $totalMinutes = 0;
    $chapterSort = 0;

    foreach ($c['chapters'] as $chapterTitle => $lectures) {
        $chapterSort++;
        $chapterId = Database::run(
            "INSERT INTO chapters (course_id,title,sort) VALUES (?,?,?)",
            [$courseId, $chapterTitle, $chapterSort]
        );

        $lecSort = 0;
        foreach ($lectures as $lectureTitle) {
            $lecSort++;
            $lectureCounter++;
            $isFree = $lectureCounter <= 5 ? 1 : 0;
            $minutes = random_int_safe(8, 22);
            $totalMinutes += $minutes;

            // Every lecture gets a playable demo video (rotated).
            $video = $sampleVideos[$lectureCounter % count($sampleVideos)];
            Database::run(
                "INSERT INTO lectures (chapter_id,course_id,title,description,video_url,duration_min,is_free,sort)
                 VALUES (?,?,?,?,?,?,?,?)",
                [$chapterId, $courseId, $lectureTitle,
                 'In this lesson, "' . $lectureTitle . '", you will learn the key concepts step by step with practical examples. Watch the video, take notes, and try the practice exercises.',
                 $video, $minutes, $isFree, $lecSort]
            );
        }

        // A chapter test (quiz) with 3 sample questions per chapter.
        $quizId = Database::run(
            "INSERT INTO quizzes (chapter_id,course_id,title,pass_percent,max_attempts)
             VALUES (?,?,?,?,?)",
            [$chapterId, $courseId, $chapterTitle . ' - Chapter Test', 70, 3]
        );
        for ($q = 1; $q <= 3; $q++) {
            Database::run(
                "INSERT INTO questions (quiz_id,question,options,correct_index,sort)
                 VALUES (?,?,?,?,?)",
                [$quizId,
                 "Sample question {$q} for \"{$chapterTitle}\" - replace from the admin panel.",
                 json_encode(['Correct answer', 'Option B', 'Option C', 'Option D']),
                 0, $q]
            );
        }
    }

    Database::run("UPDATE courses SET total_minutes=? WHERE id=?", [$totalMinutes, $courseId]);
    echo "  ✓ course: {$c['title']}  ({$lectureCounter} lectures)\n";
}

// ---- Settings -------------------------------------------------------------
// (Hero text is left unset so the designed default shows; the admin can
//  override hero/payment/contact from the Settings page at any time.)

// ---- Timetable sample -----------------------------------------------------
Database::run("INSERT INTO timetable (title,body,sort,is_published) VALUES (?,?,?,1)",
    ['Current Class Timetable',
     "Morning Batch: 9:00 AM – 12:00 PM\nAfternoon Batch: 1:00 PM – 4:00 PM\nEvening Batch: 5:00 PM – 8:00 PM", 1]);

// ---- A few approved reviews ----------------------------------------------
$pythonId = Database::scalar("SELECT id FROM courses WHERE slug='python'");
$studentId = Database::scalar("SELECT id FROM users WHERE email='student@itti.com.pk'");
Database::run("INSERT INTO reviews (user_id,course_id,rating,body,status) VALUES (?,?,?,?,'approved')",
    [$studentId, $pythonId, 5, 'Best Python course in our area. The instructor explains everything clearly with real examples.']);

// Give the demo student a father name + reg number for the ID card.
Database::run("UPDATE users SET father_name=?, reg_no=? WHERE id=?", ['Abdul Rahman', 'ITTI-2026-0002', $studentId]);

// ---- Enrol the demo student in several courses (so the LMS is demoable) ----
foreach (['python','ccna-200-301','cctv-camera-installation','cyber-security','html'] as $demoSlug) {
    $cid = Database::scalar("SELECT id FROM courses WHERE slug=?", [$demoSlug]);
    if ($cid && !Database::scalar("SELECT id FROM enrollments WHERE user_id=? AND course_id=?", [$studentId, $cid])) {
        Database::run("INSERT INTO enrollments (user_id,course_id,status) VALUES (?,?,'active')", [$studentId, $cid]);
    }
}

// ---- Institute: classrooms, staff, a demo batch -------------------------
$rooms = [
    ['Room A - Networking Lab', 20, 'Ground Floor'],
    ['Room B - Computer Lab', 25, 'Ground Floor'],
    ['Room C - Classroom', 30, 'First Floor'],
];
foreach ($rooms as $r) {
    Database::run("INSERT INTO classrooms (name,capacity,location) VALUES (?,?,?)", $r);
}

// Staff from the faculty content (photos copied into storage for streaming).
$staffDir = BASE_PATH . '/storage/uploads/staff';
if (!is_dir($staffDir)) { @mkdir($staffDir, 0775, true); }
$sort = 0;
foreach (\App\Content::faculty() as $f) {
    $sort++;
    $photoRel = null;
    $srcImg = BASE_PATH . '/public/assets/img/' . $f['photo'];
    if (is_file($srcImg)) {
        $dest = 'staff/seed-' . $sort . '.jpg';
        @copy($srcImg, BASE_PATH . '/storage/uploads/' . $dest);
        $photoRel = $dest;
    }
    Database::run(
        "INSERT INTO staff (name,role,expertise,bio,photo,sort,is_published) VALUES (?,?,?,?,?,?,1)",
        [$f['name'], $f['role'], implode(', ', $f['expertise']), $f['bio'], $photoRel, $sort]
    );
}

// ---- Website content (CMS: hero slides, awards, activity gallery) ---------
// Same copy+insert shape as database/seed_website_content_from_static.php,
// inlined here for fresh local/dev installs (this file is documented as
// fresh-install-only, so no "already populated" guards are needed).
function seedCopyStatic(string $relSrc, string $subdir, string $destName): ?string
{
    $src = BASE_PATH . '/public/assets/img/' . $relSrc;
    if (!is_file($src)) { return null; }
    $dir = BASE_PATH . '/storage/uploads/' . $subdir;
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $dest = $subdir . '/' . $destName;
    @copy($src, BASE_PATH . '/storage/uploads/' . $dest);
    return $dest;
}

$heroSource = [
    ['img/photos/hero.jpg','Students learning at IT Training Institute'],
    ['img/photos/lab.jpg','Hands-on computer lab'],
    ['img/photos/campus-1.jpg','Our campus'],
    ['img/photos/campus-2.jpg','Classrooms'],
    ['img/photos/campus-3.jpg','Training in progress'],
    ['img/photos/campus-4.jpg','Practical labs'],
    ['img/photos/campus-5.jpg','Student activities'],
    ['img/photos/campus-6.jpg','Institute facilities'],
];
$heroSort = 0;
foreach ($heroSource as [$rel, $alt]) {
    $heroSort++;
    $stored = seedCopyStatic(ltrim(str_replace('img/', '', $rel), '/'), 'hero', 'seed-' . $heroSort . '.jpg');
    Database::run("INSERT INTO hero_slides (image,alt,sort,is_published) VALUES (?,?,?,1)", [$stored, $alt, $heroSort]);
}

$awardSort = 0;
foreach (\App\Content::awards() as $a) {
    $awardSort++;
    $stored = seedCopyStatic($a['image'], 'awards', 'seed-' . $awardSort . '.jpg');
    Database::run("INSERT INTO awards (year,title,org,description,image,sort,is_published) VALUES (?,?,?,?,?,?,1)",
        [$a['year'], $a['title'], $a['org'], $a['desc'], $stored, $awardSort]);
}

$campusTourCatId = (int) Database::run("INSERT INTO activity_categories (name,sort,is_published) VALUES (?,?,1)", ['Campus Tour', 1]);
$activitySort = 0;
foreach (\App\Content::gallery() as $rel) {
    $activitySort++;
    $stored = seedCopyStatic($rel, 'activities', 'seed-' . $activitySort . '.jpg');
    Database::run("INSERT INTO activity_photos (category_id,image,caption,sort,is_published) VALUES (?,?,?,?,1)",
        [$campusTourCatId, $stored, null, $activitySort]);
}

$facilitySort = 0;
foreach (\App\Content::facilities() as $f) {
    $facilitySort++;
    $stored = seedCopyStatic($f['image'], 'facilities', 'seed-' . $facilitySort . '.jpg');
    Database::run("INSERT INTO facilities (title,description,image,sort,is_published) VALUES (?,?,?,?,1)",
        [$f['title'], $f['desc'], $stored, $facilitySort]);
}

echo "  ✓ website content seeded (" . count($heroSource) . " hero slides, " . count(\App\Content::awards()) . " awards, " . count(\App\Content::gallery()) . " activity photos, " . count(\App\Content::facilities()) . " facilities)\n";

// A demo batch for CCNA assigned to room A + lead instructor.
$ccnaId = Database::scalar("SELECT id FROM courses WHERE slug='ccna-200-301'");
$roomA  = Database::scalar("SELECT id FROM classrooms ORDER BY id LIMIT 1");
$lead   = Database::scalar("SELECT id FROM staff ORDER BY id LIMIT 1");
$batchId = (int) Database::run(
    "INSERT INTO batches (course_id,name,classroom_id,staff_id,capacity,schedule,start_date,end_date,status)
     VALUES (?,?,?,?,?,?,?,?,'active')",
    [$ccnaId, 'CCNA - Morning Batch 2026', $roomA, $lead, 20, 'Mon-Fri, 9:00 AM - 12:00 PM', '2026-07-01', '2026-12-31']
);

// Enrol the demo student so QR attendance is demoable out of the box.
$demoStudent = Database::scalar("SELECT id FROM users WHERE email='student@itti.com.pk'");
if ($demoStudent) {
    Database::run("INSERT INTO batch_students (batch_id,user_id,roll_no,status) VALUES (?,?,?,'active')",
        [$batchId, $demoStudent, 'CCNA-01']);
}

// ---- Demo fee plans (configurable rules engine) ----
Database::run(
    "INSERT INTO fee_plans (name,course_id,admission_fee,security_deposit,tuition_fee,installments,late_fee_flat,late_fee_per_day,grace_days,early_discount_pct,sibling_discount_pct,scholarship_note,is_active,created_by)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?)",
    ['CCNA Standard 2026', $ccnaId, 5000, 2000, 36000, 6, 500, 50, 5, 10, 5, 'Need-based scholarships up to 25% on request.', $adminId ?? null]);
Database::run(
    "INSERT INTO fee_plans (name,course_id,admission_fee,security_deposit,tuition_fee,installments,late_fee_flat,late_fee_per_day,grace_days,early_discount_pct,sibling_discount_pct,scholarship_note,is_active,created_by)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,?)",
    ['Diploma Easy Installments', null, 3000, 0, 60000, 12, 0, 100, 7, 0, 10, '', $adminId ?? null]);

// ---- Default grading scheme + a demo approved result ----
$bands = json_encode([
    ['min'=>80,'grade'=>'A+','gpa'=>4.0,'remark'=>'Outstanding'],
    ['min'=>70,'grade'=>'A','gpa'=>3.5,'remark'=>'Excellent'],
    ['min'=>60,'grade'=>'B','gpa'=>3.0,'remark'=>'Very Good'],
    ['min'=>50,'grade'=>'C','gpa'=>2.0,'remark'=>'Good'],
    ['min'=>40,'grade'=>'D','gpa'=>1.0,'remark'=>'Pass'],
    ['min'=>0,'grade'=>'F','gpa'=>0.0,'remark'=>'Fail'],
]);
$schemeId = (int) Database::run("INSERT INTO grading_schemes (name,bands,is_default) VALUES (?,?,1)", ['Standard %', $bands]);

if (!empty($demoStudent) && !empty($batchId)) {
    $rsId = (int) Database::run(
        "INSERT INTO result_sets (title,batch_id,scheme_id,pass_mark,status,created_by,approved_by,approved_at)
         VALUES (?,?,?,?,'approved',?,?,?)",
        ['Mid Term - CCNA 2026', $batchId, $schemeId, 40, $adminId ?? null, $adminId ?? null, date('Y-m-d H:i:s')]);
    $c1 = (int) Database::run("INSERT INTO result_components (result_set_id,ckey,label,source,weight,max_marks,sort) VALUES (?,?,?,?,?,?,1)",
        [$rsId, 'theory', 'Theory', 'offline', 60, 100]);
    $c2 = (int) Database::run("INSERT INTO result_components (result_set_id,ckey,label,source,weight,max_marks,sort) VALUES (?,?,?,?,?,?,2)",
        [$rsId, 'practical', 'Practical', 'offline', 40, 50]);
    Database::run("INSERT INTO result_scores (result_set_id,user_id,component_id,obtained) VALUES (?,?,?,?)", [$rsId, $demoStudent, $c1, 78]);
    Database::run("INSERT INTO result_scores (result_set_id,user_id,component_id,obtained) VALUES (?,?,?,?)", [$rsId, $demoStudent, $c2, 41]);
}

// Mark the first CCNA chapter test as a locked secure exam (demo).
$firstQuiz = Database::scalar("SELECT MIN(id) FROM quizzes WHERE course_id=?", [$ccnaId]);
if ($firstQuiz) { Database::run("UPDATE quizzes SET secure_exam=1, max_violations=3 WHERE id=?", [$firstQuiz]); }

// ---- Learning enhancements demo (task #35) ----
$cctvId = Database::scalar("SELECT id FROM courses WHERE slug='cctv-camera-installation'");
$blockId = (int) Database::run("INSERT INTO learning_blocks (title,type,body,created_by) VALUES (?,?,?,?)",
    ['Electrical Safety', 'warning', "Always switch off mains power before wiring a camera. Use insulated tools and verify with a tester.", $adminId ?? null]);
// Attach the safety block to the first CCTV lecture + require acknowledgment + set an expiry.
$cctvLec = Database::scalar("SELECT id FROM lectures WHERE course_id=? ORDER BY sort LIMIT 1", [$cctvId]);
if ($cctvLec) {
    Database::run("INSERT INTO lecture_blocks (lecture_id,block_id,sort) VALUES (?,?,1)", [$cctvLec, $blockId]);
    Database::run("UPDATE lectures SET requires_ack=1, ack_text=? WHERE id=?",
        ['I confirm I understand the electrical-safety rules and will switch off mains power before wiring.', $cctvLec]);
}
// An expired lecture (to show the outdated badge) - a later lecture in the FIRST (unlocked) CCTV chapter.
$cctvFirstChapter = Database::scalar("SELECT id FROM chapters WHERE course_id=? ORDER BY sort, id LIMIT 1", [$cctvId]);
$cctvLec2 = Database::scalar("SELECT id FROM lectures WHERE chapter_id=? ORDER BY sort LIMIT 1 OFFSET 1", [$cctvFirstChapter]);
if ($cctvLec2) { Database::run("UPDATE lectures SET expires_at=? WHERE id=?", [date('Y-m-d', strtotime('-30 days')), $cctvLec2]); }
// A placement test: mark the HTML course's first chapter test as placement (skips 1 chapter).
$htmlId = Database::scalar("SELECT id FROM courses WHERE slug='html'");
$htmlQuiz = Database::scalar("SELECT MIN(id) FROM quizzes WHERE course_id=?", [$htmlId]);
if ($htmlQuiz) { Database::run("UPDATE quizzes SET is_placement=1, placement_skips=1 WHERE id=?", [$htmlQuiz]); }
// A revision already due for the demo student (python chapter 1).
$pyChapter = Database::scalar("SELECT id FROM chapters WHERE course_id=? ORDER BY sort LIMIT 1", [$pythonId]);
if (!empty($studentId) && $pyChapter) {
    Database::run("INSERT INTO revision_schedule (user_id,course_id,chapter_id,due_at) VALUES (?,?,?,?)",
        [$studentId, $pythonId, $pyChapter, date('Y-m-d', strtotime('-2 days'))]);
}
Database::run("INSERT INTO settings (key_name,value) VALUES ('revision_weeks','8')");

// ---- Finance demo: staff salaries + a few daily expenses ----
$salaries = [40000, 35000, 30000, 28000, 25000, 22000];
$staffIds = array_column(Database::all("SELECT id FROM staff ORDER BY id"), 'id');
foreach ($staffIds as $i => $sid) { Database::run("UPDATE staff SET salary=? WHERE id=?", [$salaries[$i] ?? 20000, $sid]); }
foreach ([
    ['tea', 350, 'Chai wala'], ['breakfast', 600, 'Nashta'], ['guest', 1200, 'Guest lunch'],
    ['stationery', 850, 'Register & pens'], ['utilities', 4500, 'Electricity bill'], ['internet', 3000, 'PTCL'],
    ['tea', 300, 'Evening tea'], ['transport', 500, 'Rickshaw'],
] as $i => $x) {
    $d = date('Y-m-d', strtotime('-' . ($i) . ' days'));
    Database::run("INSERT INTO expenses (date,category,amount,method,payee,created_by) VALUES (?,?,?,'cash',?,?)",
        [$d, $x[0], $x[1], $x[2], $adminId ?? null]);
}

// ---- Security & governance demo (task #36) ----
// A second admin so two-approver sensitive actions can be tested.
if (!Database::scalar("SELECT id FROM users WHERE email='admin2@itti.com.pk'")) {
    Database::run("INSERT INTO users (role,name,email,password,status,staff_role,email_verified_at) VALUES ('admin','Second Admin','admin2@itti.com.pk',?,'active','super',?)",
        [password_hash('admin1234', PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
}
// A demo honeytoken (decoy student-export URL).
Database::run("INSERT INTO honeytokens (label,token) VALUES (?, 'ht_demo000decoy01')", ['Fake student data export']);

// ---- Guardian portal demo data (so the analytics charts are populated) ----
Database::run("UPDATE users SET guardian_pin='4321', guardian_name='Abdul Rahman', guardian_phone='+92 300 1112233' WHERE id=?", [$studentId]);
// Attendance spread across the last ~5 months in batch 1.
$attStatuses = ['present','present','present','late','present','absent','present','present','late','present','present','present','absent','present'];
$ai = 0;
for ($d = 130; $d >= 2; $d -= 9) {
    $date = date('Y-m-d', strtotime("-{$d} days"));
    $st = $attStatuses[$ai % count($attStatuses)]; $ai++;
    Database::run("INSERT INTO attendance (batch_id,user_id,date,status,method) VALUES (1,?,?,?,'qr')", [$studentId, $date, $st]);
}
// Quiz attempts with a rising-then-steady score trend (for the line chart).
$firstCcnaQuiz = (int) Database::scalar("SELECT MIN(id) FROM quizzes WHERE course_id=?", [$ccnaId]);
if ($firstCcnaQuiz) {
    $scores = [55, 62, 48, 70, 78, 66, 85, 90, 72, 88];
    foreach ($scores as $k => $sc) {
        $when = date('Y-m-d H:i:s', strtotime('-' . (110 - $k * 11) . ' days'));
        Database::run("INSERT INTO quiz_attempts (user_id,quiz_id,score,passed,created_at) VALUES (?,?,?,?,?)",
            [$studentId, $firstCcnaQuiz, $sc, $sc >= 70 ? 1 : 0, $when]);
    }
}

echo "  ✓ institute seeded (3 classrooms, 6 staff, 1 demo batch + demo student enrolled, 2 fee plans, grading + demo result, 1 secure exam, learning demos, security demos)\n";
echo "Seeding complete.\n";

/** Deterministic-ish small random helper (kept simple). */
function random_int_safe(int $min, int $max): int
{
    return random_int($min, $max);
}
