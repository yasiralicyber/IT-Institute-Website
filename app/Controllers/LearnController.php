<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Course;
use App\Models\User;
use App\Models\Progress;

class LearnController extends Controller
{
    /** Free preview lecture - open to everyone (no login needed). */
    public function preview(array $params): void
    {
        $lecture = Database::first("SELECT * FROM lectures WHERE id = ? AND is_free = 1", [(int) ($params['id'] ?? 0)]);
        if (!$lecture) { redirect('/courses'); }
        $course = Course::find((int) $lecture['course_id']);

        $this->renderPlayer($course, $lecture, Auth::user(), true);
    }

    /** Course player home - defaults to the first accessible lecture. */
    public function course(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }
        if (!User::hasAccess((int) $user['id'], (int) $course['id'])) {
            redirect('/enroll/' . $course['slug']);
        }
        $first = Database::first(
            "SELECT l.* FROM lectures l JOIN chapters ch ON ch.id = l.chapter_id
             WHERE l.course_id = ? ORDER BY ch.sort, l.sort LIMIT 1", [$course['id']]);
        if (!$first) { redirect('/dashboard'); }
        $this->renderPlayer($course, $first, $user, false);
    }

    /** A specific lecture in a course (access + chapter-gating enforced). */
    public function lecture(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }

        $lecture = Database::first("SELECT * FROM lectures WHERE id = ? AND course_id = ?",
            [(int) ($params['lectureId'] ?? 0), $course['id']]);
        if (!$lecture) { redirect('/learn/' . $course['slug']); }

        $isFree = (int) $lecture['is_free'] === 1;
        if (!$isFree) {
            if (!User::hasAccess((int) $user['id'], (int) $course['id'])) {
                redirect('/enroll/' . $course['slug']);
            }
            if (!Progress::chapterUnlocked((int) $user['id'], (int) $course['id'], (int) $lecture['chapter_id'])) {
                flash('error', 'Pass the previous chapter test to unlock this lesson.');
                redirect('/learn/' . $course['slug']);
            }
            if (!empty($lecture['release_at']) && $lecture['release_at'] > date('Y-m-d')) {
                flash('error', 'This lesson unlocks on ' . date('d M Y', strtotime($lecture['release_at'])) . '.');
                redirect('/learn/' . $course['slug']);
            }
        }
        $this->renderPlayer($course, $lecture, $user, $isFree && !User::hasAccess((int) $user['id'], (int) $course['id']));
    }

    /** Mark a lecture complete (POST). */
    public function complete(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        $lectureId = (int) ($params['lectureId'] ?? 0);
        if ($course && csrf_verify(input('_csrf')) && User::hasAccess((int) $user['id'], (int) $course['id'])) {
            $lecture = Database::first("SELECT * FROM lectures WHERE id=? AND course_id=?", [$lectureId, $course['id']]);
            // Acknowledgment gate: required content must be acknowledged first.
            if ($lecture && !empty($lecture['requires_ack']) && !\App\Models\Learning::hasAck((int) $user['id'], 'lecture', $lectureId)) {
                flash('error', 'Please tick the acknowledgment for this lesson before marking it complete.');
                redirect('/learn/' . $course['slug'] . '/' . $lectureId);
            }
            Progress::markComplete((int) $user['id'], $lectureId);
            \App\Models\Learning::snapshotMilestone((int) $user['id'], (int) $course['id']);
        }
        redirect('/learn/' . ($course['slug'] ?? '') . '/' . $lectureId);
    }

    /** Record an acknowledgment for a lecture (POST). */
    public function acknowledge(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        $lectureId = (int) ($params['lectureId'] ?? 0);
        if ($course && csrf_verify(input('_csrf'))) {
            \App\Models\Learning::acknowledge((int) $user['id'], 'lecture', $lectureId, (string) input('note', ''));
            flash('success', 'Acknowledgment recorded.');
        }
        redirect('/learn/' . ($course['slug'] ?? '') . '/' . $lectureId);
    }

    /**
     * Stream an uploaded lecture video with HTTP range support.
     * Access is checked every request; the response carries NO download
     * disposition, so the file can't be grabbed via a direct link.
     */
    public function stream(array $params): void
    {
        $lecture = Database::first("SELECT * FROM lectures WHERE id = ?", [(int) ($params['lectureId'] ?? 0)]);
        if (!$lecture || !str_starts_with((string) $lecture['video_url'], 'file:')) {
            http_response_code(404); exit;
        }

        // Free preview is open; paid lessons require enrolment + unlocked chapter.
        if ((int) $lecture['is_free'] !== 1) {
            $user = Auth::user();
            if (!$user || !User::hasAccess((int) $user['id'], (int) $lecture['course_id'])
                || !Progress::chapterUnlocked((int) $user['id'], (int) $lecture['course_id'], (int) $lecture['chapter_id'])) {
                http_response_code(403); exit;
            }
        }

        $file = BASE_PATH . '/storage/uploads/' . substr($lecture['video_url'], 5);
        if (!is_file($file)) { http_response_code(404); exit; }

        $size = filesize($file);
        $start = 0; $end = $size - 1;
        header('Accept-Ranges: bytes');
        header('Content-Type: video/mp4');
        header('Cache-Control: no-store, private');
        header('Content-Disposition: inline');

        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
            $start = (int) $m[1];
            if ($m[2] !== '') { $end = min((int) $m[2], $end); }
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }
        header('Content-Length: ' . ($end - $start + 1));

        $fp = fopen($file, 'rb');
        fseek($fp, $start);
        $remaining = $end - $start + 1;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = fread($fp, min(8192, $remaining));
            echo $chunk;
            $remaining -= strlen($chunk);
            flush();
        }
        fclose($fp);
        exit;
    }

    /** Download the lecture's attached resource (an uploaded PDF, or redirect to an external URL). */
    public function resource(array $params): void
    {
        $lecture = Database::first("SELECT * FROM lectures WHERE id = ?", [(int) ($params['lectureId'] ?? 0)]);
        $resource = (string) ($lecture['resource_url'] ?? '');
        if (!$lecture || $resource === '') { http_response_code(404); exit; }

        if ((int) $lecture['is_free'] !== 1) {
            $user = Auth::user();
            if (!$user || !User::hasAccess((int) $user['id'], (int) $lecture['course_id'])
                || !Progress::chapterUnlocked((int) $user['id'], (int) $lecture['course_id'], (int) $lecture['chapter_id'])) {
                http_response_code(403); exit;
            }
        }

        if (!str_starts_with($resource, 'file:')) {
            header('Location: ' . $resource);
            exit;
        }

        $file = BASE_PATH . '/storage/uploads/' . substr($resource, 5);
        if (!is_file($file)) { http_response_code(404); exit; }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-z0-9-]+/i', '-', $lecture['title']) . '.pdf"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }

    /** Shared player renderer. */
    private function renderPlayer(array $course, array $lecture, ?array $user, bool $previewMode): void
    {
        // Build the curriculum with unlock + completion state.
        $chapters = Progress::chapters((int) $course['id']);
        foreach ($chapters as &$ch) {
            $uid = $user['id'] ?? 0;
            $ch['unlocked'] = $previewMode
                ? false
                : Progress::chapterUnlocked((int) $uid, (int) $course['id'], (int) $ch['id']);
            $ch['passed'] = $uid ? Progress::chapterPassed((int) $uid, (int) $ch['id']) : false;
            $ch['lectures'] = Database::all("SELECT * FROM lectures WHERE chapter_id = ? ORDER BY sort", [$ch['id']]);
            $ch['quiz'] = Progress::quizForChapter((int) $ch['id']);
            foreach ($ch['lectures'] as &$l) {
                $l['completed'] = $uid ? Progress::lectureCompleted((int) $uid, (int) $l['id']) : false;
                $l['released'] = empty($l['release_at']) || $l['release_at'] <= date('Y-m-d');
            }
            unset($l);
        }
        unset($ch);

        $hasAccess = $user ? User::hasAccess((int) $user['id'], (int) $course['id']) : false;

        $note = ''; $bookmarks = [];
        if ($user) {
            $note = (string) Database::scalar("SELECT body FROM lecture_notes WHERE user_id=? AND lecture_id=?", [$user['id'], $lecture['id']]);
            $bookmarks = Database::all("SELECT * FROM lecture_bookmarks WHERE user_id=? AND lecture_id=? ORDER BY seconds", [$user['id'], $lecture['id']]);
        }
        $markers = Database::all("SELECT seconds, label FROM lecture_markers WHERE lecture_id=? ORDER BY seconds", [$lecture['id']]);
        $questions = Database::all("SELECT seconds, question, options, correct_index FROM lecture_questions WHERE lecture_id=? ORDER BY seconds", [$lecture['id']]);

        $uid = (int) ($user['id'] ?? 0);
        $needsAck = $user && !empty($lecture['requires_ack']) && !\App\Models\Learning::hasAck($uid, 'lecture', (int) $lecture['id']);

        $this->view('student/player', [
            'title'      => $lecture['title'] . ' - ' . $course['title'],
            'course'     => $course,
            'lecture'    => $lecture,
            'chapters'   => $chapters,
            'user'       => $user,
            'preview'    => $previewMode || !$hasAccess,
            'hasAccess'  => $hasAccess,
            'completed'  => $user ? Progress::lectureCompleted($uid, (int) $lecture['id']) : false,
            'note'       => $note,
            'bookmarks'  => $bookmarks,
            'markers'    => $markers,
            'questions'  => $questions,
            'blocks'     => \App\Models\Learning::blocksForLecture((int) $lecture['id']),
            'expired'    => \App\Models\Learning::isExpired($lecture),
            'needsAck'   => $needsAck,
            'ackText'    => (string) ($lecture['ack_text'] ?? ''),
        ], 'layouts/player');
    }

    /** Gamified course roadmap. */
    public function roadmap(array $params): void
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }
        if (!User::hasAccess((int) $user['id'], (int) $course['id'])) { redirect('/enroll/' . $course['slug']); }

        $chapters = Progress::chapters((int) $course['id']);
        $prevPassed = true;
        foreach ($chapters as $i => &$ch) {
            $total = (int) Database::scalar("SELECT COUNT(*) FROM lectures WHERE chapter_id=?", [$ch['id']]);
            $done  = (int) Database::scalar("SELECT COUNT(*) FROM lecture_progress lp JOIN lectures l ON l.id=lp.lecture_id WHERE lp.user_id=? AND l.chapter_id=?", [$user['id'], $ch['id']]);
            $passed = Progress::chapterPassed((int) $user['id'], (int) $ch['id']);
            $unlocked = $i === 0 ? true : $prevPassed;
            $ch['total'] = $total; $ch['done'] = $done; $ch['passed'] = $passed; $ch['unlocked'] = $unlocked;
            $ch['state'] = !$unlocked ? 'locked' : ($passed ? 'done' : 'current');
            $ch['first_lecture'] = (int) Database::scalar("SELECT id FROM lectures WHERE chapter_id=? ORDER BY sort LIMIT 1", [$ch['id']]);
            $prevPassed = $passed;
        }
        unset($ch);

        $this->view('student/roadmap', [
            'title' => $course['title'] . ' - Roadmap', 'course' => $course, 'chapters' => $chapters, 'user' => $user,
            'courseDone' => Progress::courseCompleted((int) $user['id'], (int) $course['id']),
        ], 'layouts/dash');
    }

    public function saveNote(array $params): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/dashboard'); }
        $lectureId = (int) ($params['lectureId'] ?? 0);
        $body = trim((string) input('body', ''));
        $exists = Database::scalar("SELECT id FROM lecture_notes WHERE user_id=? AND lecture_id=?", [$user['id'], $lectureId]);
        if ($exists) {
            Database::run("UPDATE lecture_notes SET body=?, updated_at=? WHERE id=?", [$body, date('Y-m-d H:i:s'), $exists]);
        } else {
            Database::run("INSERT INTO lecture_notes (user_id,lecture_id,body) VALUES (?,?,?)", [$user['id'], $lectureId, $body]);
        }
        flash('success', 'Notes saved.');
        redirect('/learn/' . ($params['slug'] ?? '') . '/' . $lectureId);
    }

    public function addBookmark(array $params): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/dashboard'); }
        $lectureId = (int) ($params['lectureId'] ?? 0);
        $secs = max(0, (int) input('seconds'));
        Database::run("INSERT INTO lecture_bookmarks (user_id,lecture_id,seconds,label) VALUES (?,?,?,?)",
            [$user['id'], $lectureId, $secs, substr((string) input('label', ''), 0, 120)]);
        redirect('/learn/' . ($params['slug'] ?? '') . '/' . $lectureId);
    }

    public function deleteBookmark(array $params): void
    {
        $user = Auth::requireStudent();
        if (!csrf_verify(input('_csrf'))) { redirect('/dashboard'); }
        Database::run("DELETE FROM lecture_bookmarks WHERE id=? AND user_id=?", [(int) ($params['id'] ?? 0), $user['id']]);
        redirect('/learn/' . ($params['slug'] ?? '') . '/' . (int) ($params['lectureId'] ?? 0));
    }

    /** Downloadable text notes for low-bandwidth learners. */
    public function notesTxt(array $params): void
    {
        $user = Auth::user();
        $lecture = Database::first("SELECT * FROM lectures WHERE id=?", [(int) ($params['lectureId'] ?? 0)]);
        if (!$lecture) { http_response_code(404); exit; }
        if ((int) $lecture['is_free'] !== 1 && (!$user || !User::hasAccess((int) $user['id'], (int) $lecture['course_id']))) {
            http_response_code(403); exit;
        }
        $course = Course::find((int) $lecture['course_id']);
        $myNote = $user ? (string) Database::scalar("SELECT body FROM lecture_notes WHERE user_id=? AND lecture_id=?", [$user['id'], $lecture['id']]) : '';
        $txt = "IT TRAINING INSTITUTE - Lesson Notes\n";
        $txt .= str_repeat('=', 50) . "\n";
        $txt .= "Course: {$course['title']}\nLesson: {$lecture['title']}\n\n";
        $txt .= ($lecture['description'] ?: 'No lesson notes provided.') . "\n";
        if ($lecture['resource_url']) { $txt .= "\nResource: {$lecture['resource_url']}\n"; }
        if ($myNote) { $txt .= "\n--- My Notes ---\n{$myNote}\n"; }
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="notes-' . $lecture['id'] . '.txt"');
        echo $txt; exit;
    }
}
