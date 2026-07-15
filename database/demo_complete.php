<?php
/**
 * Demo completion script: marks the HTML course fully completed for the
 * demo student (student@itti.com.pk) so chapter tests and the course
 * certificate are all visible.
 *
 * Run: php database/demo_complete.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Certificate;

$pdo = Database::pdo();

$STUDENT_ID = 2;   // Demo Student
$COURSE_ID  = 7;   // HTML

// ── 1. Verify enrollment is active ──────────────────────────────────────────
$enr = $pdo->prepare("SELECT id FROM enrollments WHERE user_id=? AND course_id=?");
$enr->execute([$STUDENT_ID, $COURSE_ID]);
if (!$enr->fetch()) {
    $pdo->prepare("INSERT INTO enrollments (user_id,course_id,status,approved_at) VALUES (?,?,'active',?)")
        ->execute([$STUDENT_ID, $COURSE_ID, date('Y-m-d H:i:s')]);
    echo "  ✓ enrollment created\n";
} else {
    echo "  ✓ already enrolled\n";
}

// ── 2. Mark all lectures completed ──────────────────────────────────────────
$lectures = $pdo->query(
    "SELECT l.id FROM lectures l
     JOIN chapters ch ON ch.id = l.chapter_id
     WHERE ch.course_id = $COURSE_ID ORDER BY ch.sort, l.sort"
)->fetchAll(PDO::FETCH_COLUMN);

$ins = $pdo->prepare("INSERT OR IGNORE INTO lecture_progress (user_id,lecture_id,completed_at) VALUES (?,?,?)");
foreach ($lectures as $lid) {
    $ins->execute([$STUDENT_ID, $lid, date('Y-m-d H:i:s')]);
}
echo "  ✓ " . count($lectures) . " lectures marked complete\n";

// ── 3. Pass all chapter quizzes ─────────────────────────────────────────────
$chapters = $pdo->query(
    "SELECT ch.id as chapter_id, ch.title, q.id as quiz_id, q.pass_percent
     FROM chapters ch JOIN quizzes q ON q.chapter_id = ch.id
     WHERE ch.course_id = $COURSE_ID ORDER BY ch.sort"
)->fetchAll(PDO::FETCH_ASSOC);

$qins = $pdo->prepare("INSERT INTO quiz_attempts (user_id,quiz_id,score,passed,violations,created_at) VALUES (?,?,?,1,0,?)");
foreach ($chapters as $ch) {
    // Check if already passed
    $passed = $pdo->prepare(
        "SELECT COUNT(*) FROM quiz_attempts WHERE user_id=? AND quiz_id=? AND passed=1"
    );
    $passed->execute([$STUDENT_ID, $ch['quiz_id']]);
    if ($passed->fetchColumn()) {
        echo "  ✓ chapter \"{$ch['title']}\" quiz already passed\n";
        continue;
    }
    // Insert a passing attempt (score = 100%)
    $qins->execute([$STUDENT_ID, $ch['quiz_id'], 100, date('Y-m-d H:i:s')]);
    echo "  ✓ chapter \"{$ch['title']}\" quiz passed (score 100)\n";
}

// ── 4. Issue per-chapter certificates ───────────────────────────────────────
foreach ($chapters as $ch) {
    $cred = Certificate::issue($STUDENT_ID, $COURSE_ID, $ch['chapter_id'], 'chapter');
    echo "  ✓ chapter cert issued: $cred (chapter: {$ch['title']})\n";
}

// ── 5. Issue course completion certificate ───────────────────────────────────
$cred = Certificate::issue($STUDENT_ID, $COURSE_ID, null, 'completion');
echo "  ✓ COURSE COMPLETION cert issued: $cred\n";

echo "\nDone! Log in as student@itti.com.pk / student1234 to see the completed HTML course.\n";
echo "Certificate URL: /verify?id=$cred\n";
