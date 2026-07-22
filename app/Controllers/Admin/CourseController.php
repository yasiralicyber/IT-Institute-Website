<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class CourseController extends Controller
{
    /* ---------------- Courses ---------------- */

    public function index(): void
    {
        Auth::requireAdmin();
        $courses = Database::all(
            "SELECT c.*,
                (SELECT COUNT(*) FROM lectures l WHERE l.course_id = c.id) AS lectures,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status='active') AS students
             FROM courses c ORDER BY c.sort ASC");
        $this->view('admin/courses', ['title' => 'Courses', 'heading' => 'Courses', 'courses' => $courses], 'admin/layouts/admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('admin/course-form', ['title' => 'New Course', 'heading' => 'New Course', 'course' => null], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $title = trim((string) input('title', ''));
        if ($title === '') { flash('error', 'Title is required.'); redirect('/courses/create'); }

        $slug = $this->uniqueSlug(slugify($title));
        $maxSort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM courses");
        $thumbnail = store_upload('thumbnail', 'courses', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304);
        $id = Database::run(
            "INSERT INTO courses (slug,title,subtitle,category,level,description,outcomes,price,accent,icon,thumbnail,sort,is_published)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [$slug, $title, input('subtitle'), input('category'), input('level'),
             input('description'), json_encode(array_filter(array_map('trim', explode("\n", (string) input('outcomes'))))),
             (int) input('price'), input('accent', '#2f5078') ?: '#2f5078', 'code', $thumbnail,
             $maxSort + 1, input('is_published') ? 1 : 0]);
        flash('success', 'Course created. Now add chapters and lectures.');
        redirect('/courses/' . $id . '/edit');
    }

    public function edit(array $params): void
    {
        Auth::requireAdmin();
        $course = Database::first("SELECT * FROM courses WHERE id = ?", [(int) ($params['id'] ?? 0)]);
        if (!$course) { redirect('/courses'); }
        $chapters = Database::all("SELECT * FROM chapters WHERE course_id = ? ORDER BY sort", [$course['id']]);
        foreach ($chapters as &$ch) {
            $ch['lectures'] = Database::all("SELECT * FROM lectures WHERE chapter_id = ? ORDER BY sort", [$ch['id']]);
            $ch['quiz'] = Database::first("SELECT * FROM quizzes WHERE chapter_id = ?", [$ch['id']]);
            $ch['questions'] = $ch['quiz'] ? (int) Database::scalar("SELECT COUNT(*) FROM questions WHERE quiz_id = ?", [$ch['quiz']['id']]) : 0;
        }
        unset($ch);
        $this->view('admin/course-form', [
            'title' => 'Edit ' . $course['title'], 'heading' => 'Edit Course',
            'course' => $course, 'chapters' => $chapters,
        ], 'admin/layouts/admin');
    }

    public function update(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = Database::first("SELECT thumbnail FROM courses WHERE id = ?", [$id]);
        $thumbnail = store_upload('thumbnail', 'courses', ['jpg', 'jpeg', 'png', 'webp'], 4_194_304) ?: ($cur['thumbnail'] ?? null);
        Database::run(
            "UPDATE courses SET title=?,subtitle=?,category=?,level=?,description=?,outcomes=?,price=?,accent=?,thumbnail=?,is_published=? WHERE id=?",
            [input('title'), input('subtitle'), input('category'), input('level'), input('description'),
             json_encode(array_filter(array_map('trim', explode("\n", (string) input('outcomes'))))),
             (int) input('price'), input('accent', '#2f5078') ?: '#2f5078', $thumbnail, input('is_published') ? 1 : 0, $id]);
        flash('success', 'Course updated.');
        redirect('/courses/' . $id . '/edit');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $id = (int) ($params['id'] ?? 0);
        // Snapshot the whole course tree into the recycle bin before deleting.
        $course = Database::first("SELECT * FROM courses WHERE id = ?", [$id]);
        if ($course) {
            trash_record('courses', $course, 'Course: ' . $course['title'], [
                'chapters'  => Database::all("SELECT * FROM chapters WHERE course_id = ?", [$id]),
                'lectures'  => Database::all("SELECT * FROM lectures WHERE course_id = ?", [$id]),
                'quizzes'   => Database::all("SELECT * FROM quizzes WHERE course_id = ?", [$id]),
                'questions' => Database::all("SELECT q.* FROM questions q JOIN quizzes z ON z.id=q.quiz_id WHERE z.course_id = ?", [$id]),
            ]);
            audit('delete', 'courses', $id, 'Deleted course "' . $course['title'] . '"');
        }
        $quizIds = array_column(Database::all("SELECT id FROM quizzes WHERE course_id = ?", [$id]), 'id');
        foreach ($quizIds as $qid) { Database::run("DELETE FROM questions WHERE quiz_id = ?", [$qid]); }
        Database::run("DELETE FROM quizzes WHERE course_id = ?", [$id]);
        Database::run("DELETE FROM lectures WHERE course_id = ?", [$id]);
        Database::run("DELETE FROM chapters WHERE course_id = ?", [$id]);
        Database::run("DELETE FROM enrollments WHERE course_id = ?", [$id]);
        Database::run("DELETE FROM courses WHERE id = ?", [$id]);
        flash('success', 'Course and all its content were deleted.');
        redirect('/courses');
    }

    /* ---------------- Chapters ---------------- */

    public function storeChapter(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $courseId = (int) ($params['id'] ?? 0);
        $title = trim((string) input('title', ''));
        if ($title !== '') {
            $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM chapters WHERE course_id = ?", [$courseId]) + 1;
            $chId = Database::run("INSERT INTO chapters (course_id,title,sort) VALUES (?,?,?)", [$courseId, $title, $sort]);
            // Every chapter gets a test by default.
            Database::run("INSERT INTO quizzes (chapter_id,course_id,title,pass_percent,max_attempts) VALUES (?,?,?,70,3)",
                [$chId, $courseId, $title . ' - Chapter Test']);
            flash('success', 'Chapter added.');
        }
        redirect('/courses/' . $courseId . '/edit');
    }

    public function deleteChapter(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $chId = (int) ($params['id'] ?? 0);
        $courseId = (int) Database::scalar("SELECT course_id FROM chapters WHERE id = ?", [$chId]);
        $quiz = Database::first("SELECT id FROM quizzes WHERE chapter_id = ?", [$chId]);
        if ($quiz) { Database::run("DELETE FROM questions WHERE quiz_id = ?", [$quiz['id']]); }
        Database::run("DELETE FROM quizzes WHERE chapter_id = ?", [$chId]);
        Database::run("DELETE FROM lectures WHERE chapter_id = ?", [$chId]);
        Database::run("DELETE FROM chapters WHERE id = ?", [$chId]);
        flash('success', 'Chapter deleted.');
        redirect('/courses/' . $courseId . '/edit');
    }

    /* ---------------- Lectures ---------------- */

    public function storeLecture(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $chId = (int) ($params['id'] ?? 0);
        $courseId = (int) Database::scalar("SELECT course_id FROM chapters WHERE id = ?", [$chId]);
        $title = trim((string) input('title', ''));
        if ($title !== '') {
            $video = $this->resolveVideo();
            $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0) FROM lectures WHERE chapter_id = ?", [$chId]) + 1;
            Database::run(
                "INSERT INTO lectures (chapter_id,course_id,title,description,video_url,duration_min,is_free,release_at,sort)
                 VALUES (?,?,?,?,?,?,?,?,?)",
                [$chId, $courseId, $title, input('description'), $video,
                 (int) input('duration_min'), input('is_free') ? 1 : 0, trim((string) input('release_at', '')) ?: null, $sort]);
            $this->recalcMinutes($courseId);
            flash('success', 'Lecture added.');
        }
        redirect('/courses/' . $courseId . '/edit');
    }

    public function updateLecture(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $id = (int) ($params['id'] ?? 0);
        $lec = Database::first("SELECT * FROM lectures WHERE id = ?", [$id]);
        if ($lec) {
            $video = $this->resolveVideo($lec['video_url']);
            Database::run("UPDATE lectures SET title=?,description=?,video_url=?,duration_min=?,is_free=?,release_at=? WHERE id=?",
                [input('title', $lec['title']), input('description'), $video,
                 (int) input('duration_min'), input('is_free') ? 1 : 0, trim((string) input('release_at', '')) ?: null, $id]);
            $this->recalcMinutes((int) $lec['course_id']);
            flash('success', 'Lecture updated.');
        }
        redirect('/courses/' . (int) ($lec['course_id'] ?? 0) . '/edit');
    }

    public function deleteLecture(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $id = (int) ($params['id'] ?? 0);
        $courseId = (int) Database::scalar("SELECT course_id FROM lectures WHERE id = ?", [$id]);
        Database::run("DELETE FROM lectures WHERE id = ?", [$id]);
        $this->recalcMinutes($courseId);
        flash('success', 'Lecture deleted.');
        redirect('/courses/' . $courseId . '/edit');
    }

    /* ---------------- Quiz editor ---------------- */

    public function quiz(array $params): void
    {
        Auth::requireAdmin();
        $chId = (int) ($params['id'] ?? 0);
        $chapter = Database::first("SELECT * FROM chapters WHERE id = ?", [$chId]);
        if (!$chapter) { redirect('/courses'); }
        $quiz = Database::first("SELECT * FROM quizzes WHERE chapter_id = ?", [$chId]);
        if (!$quiz) {
            $qid = Database::run("INSERT INTO quizzes (chapter_id,course_id,title,pass_percent,max_attempts) VALUES (?,?,?,70,3)",
                [$chId, $chapter['course_id'], $chapter['title'] . ' - Chapter Test']);
            $quiz = Database::first("SELECT * FROM quizzes WHERE id = ?", [$qid]);
        }
        $questions = Database::all("SELECT * FROM questions WHERE quiz_id = ? ORDER BY sort", [$quiz['id']]);
        $this->view('admin/quiz-form', [
            'title' => 'Edit Test', 'heading' => 'Chapter Test: ' . $chapter['title'],
            'chapter' => $chapter, 'quiz' => $quiz, 'questions' => $questions,
        ], 'admin/layouts/admin');
    }

    public function saveQuiz(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $chId = (int) ($params['id'] ?? 0);
        $quiz = Database::first("SELECT * FROM quizzes WHERE chapter_id = ?", [$chId]);
        if (!$quiz) { redirect('/courses'); }

        Database::run("UPDATE quizzes SET pass_percent=?, max_attempts=?, secure_exam=?, max_violations=?, is_placement=?, placement_skips=? WHERE id=?",
            [max(1, min(100, (int) input('pass_percent', 70))), max(1, (int) input('max_attempts', 3)),
             input('secure_exam') ? 1 : 0, max(1, (int) input('max_violations', 3)),
             input('is_placement') ? 1 : 0, max(0, (int) input('placement_skips', 0)), $quiz['id']]);

        // Replace questions with the submitted set.
        Database::run("DELETE FROM questions WHERE quiz_id = ?", [$quiz['id']]);
        $questions = (array) input('q', []);
        $sort = 0;
        foreach ($questions as $q) {
            $text = trim((string) ($q['text'] ?? ''));
            $options = array_values(array_filter(array_map('trim', (array) ($q['options'] ?? [])), fn($o) => $o !== ''));
            if ($text === '' || count($options) < 2) { continue; }
            $correct = min((int) ($q['correct'] ?? 0), count($options) - 1);
            $sort++;
            Database::run("INSERT INTO questions (quiz_id,question,options,correct_index,sort) VALUES (?,?,?,?,?)",
                [$quiz['id'], $text, json_encode($options), $correct, $sort]);
        }
        flash('success', 'Test saved.');
        redirect('/courses/' . (int) $quiz['course_id'] . '/edit');
    }

    /* ---------------- Interactive video (markers + in-video questions) ---------------- */

    public function interactive(array $params): void
    {
        Auth::requireAdmin();
        $id = (int) ($params['id'] ?? 0);
        $lecture = Database::first("SELECT * FROM lectures WHERE id = ?", [$id]);
        if (!$lecture) { redirect('/courses'); }
        $this->view('admin/interactive', [
            'title' => 'Interactive: ' . $lecture['title'], 'heading' => 'Interactive Video',
            'lecture' => $lecture,
            'markers' => Database::all("SELECT * FROM lecture_markers WHERE lecture_id=? ORDER BY seconds", [$id]),
            'questions' => Database::all("SELECT * FROM lecture_questions WHERE lecture_id=? ORDER BY seconds", [$id]),
        ], 'admin/layouts/admin');
    }

    public function saveInteractive(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/courses'); }
        $id = (int) ($params['id'] ?? 0);
        $lec = Database::first("SELECT course_id FROM lectures WHERE id=?", [$id]);
        if (!$lec) { redirect('/courses'); }

        Database::run("DELETE FROM lecture_markers WHERE lecture_id=?", [$id]);
        foreach ((array) input('m', []) as $m) {
            $label = trim((string) ($m['label'] ?? ''));
            if ($label === '') { continue; }
            Database::run("INSERT INTO lecture_markers (lecture_id,seconds,label) VALUES (?,?,?)",
                [$id, self::toSeconds($m['time'] ?? '0'), $label]);
        }

        Database::run("DELETE FROM lecture_questions WHERE lecture_id=?", [$id]);
        foreach ((array) input('q', []) as $q) {
            $text = trim((string) ($q['text'] ?? ''));
            $opts = array_values(array_filter(array_map('trim', (array) ($q['options'] ?? [])), fn($o) => $o !== ''));
            if ($text === '' || count($opts) < 2) { continue; }
            Database::run("INSERT INTO lecture_questions (lecture_id,seconds,question,options,correct_index) VALUES (?,?,?,?,?)",
                [$id, self::toSeconds($q['time'] ?? '0'), $text, json_encode($opts), min((int) ($q['correct'] ?? 0), count($opts) - 1)]);
        }
        flash('success', 'Interactive content saved.');
        redirect('/courses/' . (int) $lec['course_id'] . '/edit');
    }

    /** Accepts "90", "1:30" or "1:05:00" → seconds. */
    private static function toSeconds(string $t): int
    {
        $t = trim($t);
        if (str_contains($t, ':')) {
            $p = array_map('intval', explode(':', $t));
            return count($p) === 3 ? $p[0] * 3600 + $p[1] * 60 + $p[2] : $p[0] * 60 + $p[1];
        }
        return max(0, (int) $t);
    }

    /* ---------------- helpers ---------------- */

    private function resolveVideo(string $current = ''): string
    {
        // Prefer an uploaded MP4 (stored + streamed via the protected /media route);
        // otherwise accept a pasted embed/URL.
        $file = store_upload('video', 'videos', ['mp4', 'webm', 'm4v'], 524_288_000);
        if ($file) { return 'file:' . $file; }
        $url = trim((string) input('video_url', ''));
        return $url !== '' ? $url : $current;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base ?: 'course';
        $i = 1;
        while (Database::scalar("SELECT COUNT(*) FROM courses WHERE slug = ?", [$slug])) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    private function recalcMinutes(int $courseId): void
    {
        $total = (int) Database::scalar("SELECT COALESCE(SUM(duration_min),0) FROM lectures WHERE course_id = ?", [$courseId]);
        Database::run("UPDATE courses SET total_minutes = ? WHERE id = ?", [$total, $courseId]);
    }
}
