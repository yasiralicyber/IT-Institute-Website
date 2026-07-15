<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Course;
use App\Models\User;
use App\Models\Progress;
use App\Models\Certificate;

class QuizController extends Controller
{
    public function show(array $params): void
    {
        [$user, $course, $chapter, $quiz] = $this->resolve($params);

        $attemptsUsed = Progress::attemptsUsed((int) $user['id'], (int) $quiz['id']);
        $questions = Database::all(
            "SELECT id, question, options FROM questions WHERE quiz_id = ? ORDER BY sort", [$quiz['id']]);

        $this->view('student/quiz', [
            'title'        => $quiz['title'],
            'course'       => $course,
            'chapter'      => $chapter,
            'quiz'         => $quiz,
            'questions'    => $questions,
            'attemptsUsed' => $attemptsUsed,
            'user'         => $user,
            'passed'       => Progress::chapterPassed((int) $user['id'], (int) $chapter['id']),
        ], 'layouts/dash');
    }

    public function submit(array $params): void
    {
        [$user, $course, $chapter, $quiz] = $this->resolve($params);

        if (!csrf_verify(input('_csrf'))) {
            redirect('/learn/' . $course['slug'] . '/test/' . $chapter['id']);
        }

        $attemptsUsed = Progress::attemptsUsed((int) $user['id'], (int) $quiz['id']);
        if ($attemptsUsed >= (int) $quiz['max_attempts'] && !Progress::chapterPassed((int) $user['id'], (int) $chapter['id'])) {
            flash('error', 'You have used all attempts for this test. Please contact the institute.');
            redirect('/learn/' . $course['slug']);
        }

        $questions = Database::all("SELECT id, correct_index FROM questions WHERE quiz_id = ?", [$quiz['id']]);
        $answers = (array) input('answers', []);
        $correct = 0;
        foreach ($questions as $q) {
            if (isset($answers[$q['id']]) && (int) $answers[$q['id']] === (int) $q['correct_index']) {
                $correct++;
            }
        }
        $total = max(1, count($questions));
        $score = (int) round($correct / $total * 100);
        $passed = $score >= (int) $quiz['pass_percent'] ? 1 : 0;

        $violations = (int) Database::scalar(
            "SELECT COUNT(*) FROM exam_violations WHERE user_id=? AND quiz_id=? AND date(created_at)=?",
            [$user['id'], $quiz['id'], date('Y-m-d')]);
        Database::run("INSERT INTO quiz_attempts (user_id,quiz_id,score,passed,violations) VALUES (?,?,?,?,?)",
            [$user['id'], $quiz['id'], $score, $passed, $violations]);

        if ($passed) {
            // Chapter certificate on passing the chapter test.
            Certificate::issue((int) $user['id'], (int) $course['id'], (int) $chapter['id'], 'chapter');
            // Learning hooks: revision scheduling, placement skips, milestone snapshots.
            \App\Models\Learning::onChapterPassed((int) $user['id'], (int) $course['id'], (int) $chapter['id'], $quiz);
            // Course certificate if everything is now complete.
            if (Progress::courseCompleted((int) $user['id'], (int) $course['id'])) {
                Certificate::issue((int) $user['id'], (int) $course['id'], null, 'course');
            }
        }

        $this->view('student/quiz-result', [
            'title'   => 'Test Result',
            'course'  => $course,
            'chapter' => $chapter,
            'quiz'    => $quiz,
            'score'   => $score,
            'passed'  => (bool) $passed,
            'correct' => $correct,
            'total'   => $total,
            'user'    => $user,
        ], 'layouts/dash');
    }

    /** Log a locked-exam-browser violation (tab switch, fullscreen exit, copy/paste). */
    public function violation(array $params): void
    {
        $user = Auth::user();
        if (!$user) { http_response_code(401); echo 'no'; return; }
        $quiz = Database::first("SELECT q.* FROM quizzes q WHERE q.chapter_id = ?", [(int) ($params['chapterId'] ?? 0)]);
        if (!$quiz) { http_response_code(404); echo 'no'; return; }
        $kind = preg_replace('/[^a-z_]/', '', strtolower((string) input('kind', 'blur'))) ?: 'blur';
        Database::run("INSERT INTO exam_violations (user_id,quiz_id,kind) VALUES (?,?,?)",
            [$user['id'], $quiz['id'], $kind]);
        $count = (int) Database::scalar(
            "SELECT COUNT(*) FROM exam_violations WHERE user_id=? AND quiz_id=? AND date(created_at)=?",
            [$user['id'], $quiz['id'], date('Y-m-d')]);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count, 'max' => (int) $quiz['max_violations']]);
    }

    /** Resolve + authorise the quiz context. */
    private function resolve(array $params): array
    {
        $user = Auth::requireStudent();
        $course = Course::findBySlug($params['slug'] ?? '');
        if (!$course) { redirect('/courses'); }
        if (!User::hasAccess((int) $user['id'], (int) $course['id'])) {
            redirect('/enroll/' . $course['slug']);
        }
        $chapter = Database::first("SELECT * FROM chapters WHERE id = ? AND course_id = ?",
            [(int) ($params['chapterId'] ?? 0), $course['id']]);
        if (!$chapter) { redirect('/learn/' . $course['slug']); }

        if (!Progress::chapterUnlocked((int) $user['id'], (int) $course['id'], (int) $chapter['id'])) {
            flash('error', 'This chapter is locked. Pass the previous chapter test first.');
            redirect('/learn/' . $course['slug']);
        }
        $quiz = Database::first("SELECT * FROM quizzes WHERE chapter_id = ?", [$chapter['id']]);
        if (!$quiz) { redirect('/learn/' . $course['slug']); }

        return [$user, $course, $chapter, $quiz];
    }
}
