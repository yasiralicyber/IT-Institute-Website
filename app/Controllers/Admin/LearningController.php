<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class LearningController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $blocks = Database::all("SELECT * FROM learning_blocks ORDER BY created_at DESC");
        foreach ($blocks as &$b) {
            $b['uses'] = (int) Database::scalar("SELECT COUNT(*) FROM lecture_blocks WHERE block_id=?", [$b['id']]);
        }
        unset($b);
        $courses = Database::all("SELECT id,title FROM courses ORDER BY title");
        $this->view('admin/learning', [
            'title' => 'Learning Tools', 'heading' => 'Learning Enhancements',
            'blocks' => $blocks, 'courses' => $courses,
            'revisionWeeks' => \App\Models\Learning::revisionWeeks(),
        ], 'admin/layouts/admin');
    }

    public function saveBlock(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/learning'); }
        $title = trim((string) input('title', ''));
        $body = trim((string) input('body', ''));
        if ($title === '' || $body === '') { flash('error', 'Enter a title and body.'); redirect('/learning'); }
        $type = in_array(input('type'), ['note', 'tip', 'warning', 'code'], true) ? input('type') : 'note';
        $id = (int) Database::run("INSERT INTO learning_blocks (title,type,body,created_by) VALUES (?,?,?,?)",
            [$title, $type, $body, Auth::id()]);
        audit('create', 'learning_blocks', $id, 'Created learning block "' . $title . '"');
        flash('success', 'Block saved.');
        redirect('/learning');
    }

    public function deleteBlock(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/learning'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM lecture_blocks WHERE block_id=?", [$id]);
        Database::run("DELETE FROM learning_blocks WHERE id=?", [$id]);
        flash('success', 'Block deleted.');
        redirect('/learning');
    }

    /** Attach a reusable block to a lecture (by lecture id). */
    public function attachBlock(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/learning'); }
        $blockId = (int) input('block_id');
        $lectureId = (int) input('lecture_id');
        $lec = Database::first("SELECT id FROM lectures WHERE id=?", [$lectureId]);
        $blk = Database::first("SELECT id FROM learning_blocks WHERE id=?", [$blockId]);
        if (!$lec || !$blk) { flash('error', 'Pick a valid block and lecture id.'); redirect('/learning'); }
        if (!Database::scalar("SELECT id FROM lecture_blocks WHERE lecture_id=? AND block_id=?", [$lectureId, $blockId])) {
            $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0)+1 FROM lecture_blocks WHERE lecture_id=?", [$lectureId]);
            Database::run("INSERT INTO lecture_blocks (lecture_id,block_id,sort) VALUES (?,?,?)", [$lectureId, $blockId, $sort]);
        }
        flash('success', 'Block attached to lecture #' . $lectureId . '.');
        redirect('/learning');
    }

    /** Set per-lecture rules: content expiry + acknowledgment requirement. */
    public function saveLectureRules(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/learning'); }
        $lectureId = (int) input('lecture_id');
        $lec = Database::first("SELECT id FROM lectures WHERE id=?", [$lectureId]);
        if (!$lec) { flash('error', 'Enter a valid lecture id.'); redirect('/learning'); }
        $expires = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) input('expires_at', '')) ? input('expires_at') : null;
        Database::run("UPDATE lectures SET expires_at=?, requires_ack=?, ack_text=? WHERE id=?",
            [$expires, input('requires_ack') ? 1 : 0, trim((string) input('ack_text', '')), $lectureId]);
        flash('success', 'Lesson rules updated for lecture #' . $lectureId . '.');
        redirect('/learning');
    }

    public function saveRevision(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/learning'); }
        $weeks = max(1, (int) input('revision_weeks', 8));
        if (Database::scalar("SELECT COUNT(*) FROM settings WHERE key_name='revision_weeks'")) {
            Database::run("UPDATE settings SET value=? WHERE key_name='revision_weeks'", [(string) $weeks]);
        } else {
            Database::run("INSERT INTO settings (key_name,value) VALUES ('revision_weeks',?)", [(string) $weeks]);
        }
        flash('success', 'Revision interval set to ' . $weeks . ' weeks.');
        redirect('/learning');
    }

    /** Acknowledgment register: who confirmed what. */
    public function acknowledgments(): void
    {
        Auth::requireAdmin();
        $rows = Database::all(
            "SELECT a.*, u.name AS student, l.title AS lecture FROM acknowledgments a
             JOIN users u ON u.id=a.user_id
             LEFT JOIN lectures l ON l.id=a.ref_id AND a.ref_type='lecture'
             ORDER BY a.created_at DESC LIMIT 300");
        $this->view('admin/acknowledgments', [
            'title' => 'Acknowledgments', 'heading' => 'Acknowledgment Register', 'rows' => $rows,
        ], 'admin/layouts/admin');
    }
}
