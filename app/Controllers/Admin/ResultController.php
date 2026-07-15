<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\ResultSet;

class ResultController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $sets = Database::all(
            "SELECT rs.*, c.title AS course, b.name AS batch FROM result_sets rs
             LEFT JOIN courses c ON c.id=rs.course_id LEFT JOIN batches b ON b.id=rs.batch_id
             ORDER BY rs.created_at DESC");
        $this->view('admin/results-index', [
            'title' => 'Results', 'heading' => 'Results Management', 'sets' => $sets,
        ], 'admin/layouts/admin');
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('admin/result-create', [
            'title' => 'New Result Set', 'heading' => 'New Result Set',
            'courses' => Database::all("SELECT id,title FROM courses ORDER BY title"),
            'batches' => Database::all("SELECT b.id,b.name,c.title course FROM batches b JOIN courses c ON c.id=b.course_id ORDER BY b.name"),
            'schemes' => Database::all("SELECT * FROM grading_schemes ORDER BY is_default DESC, name"),
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/results'); }
        $title = trim((string) input('title', ''));
        if ($title === '') { flash('error', 'Enter a title.'); redirect('/results/new'); }
        $id = (int) Database::run(
            "INSERT INTO result_sets (title,course_id,batch_id,scheme_id,pass_mark,status,created_by)
             VALUES (?,?,?,?,?,'draft',?)",
            [$title, (int) input('course_id') ?: null, (int) input('batch_id') ?: null,
             (int) input('scheme_id') ?: null, (int) input('pass_mark', 40) ?: 40, Auth::id()]);
        audit('create', 'result_sets', $id, 'Created result set "' . $title . '"');
        flash('success', 'Result set created. Add components and enter marks.');
        redirect('/results/' . $id);
    }

    public function show(array $params): void
    {
        Auth::requireAdmin();
        $set = ResultSet::find((int) ($params['id'] ?? 0));
        if (!$set) { redirect('/results'); }
        $data = ResultSet::table($set);
        $this->view('admin/result-build', [
            'title' => $set['title'], 'heading' => 'Result: ' . $set['title'],
            'set' => $set, 'components' => $data['components'], 'rows' => $data['rows'], 'bands' => $data['bands'],
            'quizzes' => Database::all("SELECT id,title FROM quizzes" . ($set['course_id'] ? " WHERE course_id=" . (int) $set['course_id'] : '') . " ORDER BY title"),
        ], 'admin/layouts/admin');
    }

    private function set(int $id): array
    {
        $set = ResultSet::find($id);
        if (!$set) { redirect('/results'); }
        return $set;
    }

    /** Block edits once approved unless explicitly reopened. */
    private function guardEditable(array $set): void
    {
        if ($set['status'] === 'approved') {
            flash('error', 'This result is approved and locked. Reopen it (with a reason) before editing.');
            redirect('/results/' . $set['id']);
        }
    }

    public function addComponent(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $this->guardEditable($set);
        $label = trim((string) input('label', ''));
        if ($label === '') { flash('error', 'Enter a component label.'); redirect('/results/' . $set['id']); }
        $source = input('source') === 'online' ? 'online' : 'offline';
        $sort = (int) Database::scalar("SELECT COALESCE(MAX(sort),0)+1 FROM result_components WHERE result_set_id=?", [$set['id']]);
        Database::run(
            "INSERT INTO result_components (result_set_id,ckey,label,source,weight,max_marks,quiz_id,sort)
             VALUES (?,?,?,?,?,?,?,?)",
            [$set['id'], strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label)), $label, $source,
             (int) input('weight'), max(1, (int) input('max_marks', 100)),
             $source === 'online' ? ((int) input('quiz_id') ?: null) : null, $sort]);
        if ($source === 'online') { ResultSet::syncOnline(ResultSet::find((int) $set['id'])); }
        flash('success', 'Component added.');
        redirect('/results/' . $set['id']);
    }

    public function deleteComponent(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $this->guardEditable($set);
        $cid = (int) ($params['cid'] ?? 0);
        Database::run("DELETE FROM result_scores WHERE component_id=?", [$cid]);
        Database::run("DELETE FROM result_components WHERE id=? AND result_set_id=?", [$cid, $set['id']]);
        flash('success', 'Component removed.');
        redirect('/results/' . $set['id']);
    }

    /** Save the whole offline marks grid: score[userId][componentId] = marks. */
    public function saveScores(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $this->guardEditable($set);
        $grid = (array) input('score', []);
        $online = [];
        foreach (ResultSet::components((int) $set['id']) as $c) {
            if ($c['source'] === 'online') { $online[(int) $c['id']] = true; }
        }
        $n = 0;
        foreach ($grid as $uid => $cells) {
            foreach ((array) $cells as $cid => $val) {
                if (isset($online[(int) $cid])) { continue; } // online cells are synced, not hand-edited
                if ($val === '' || $val === null) { continue; }
                ResultSet::setScore((int) $set['id'], (int) $uid, (int) $cid, (float) $val);
                $n++;
            }
        }
        flash('success', "Saved {$n} mark(s).");
        redirect('/results/' . $set['id']);
    }

    public function syncOnline(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $this->guardEditable($set);
        ResultSet::syncOnline($set);
        flash('success', 'Online quiz scores pulled in.');
        redirect('/results/' . $set['id']);
    }

    /** Import offline marks from a CSV (reg_no/email + component columns). */
    public function import(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $this->guardEditable($set);
        if (empty($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Choose a CSV file.'); redirect('/results/' . $set['id']);
        }
        // Build lookup: component label (lowercased) => component id (offline only).
        $byLabel = [];
        foreach (ResultSet::components((int) $set['id']) as $c) {
            if ($c['source'] === 'offline') { $byLabel[strtolower(trim($c['label']))] = (int) $c['id']; }
        }
        // Student lookup.
        $byReg = []; $byEmail = [];
        foreach (Database::all("SELECT id,reg_no,email FROM users WHERE role='student'") as $u) {
            if ($u['reg_no']) { $byReg[strtolower(trim($u['reg_no']))] = (int) $u['id']; }
            if ($u['email']) { $byEmail[strtolower(trim($u['email']))] = (int) $u['id']; }
        }
        $fh = fopen($_FILES['csv']['tmp_name'], 'r');
        $header = null; $rows = 0; $cells = 0;
        while (($line = fgetcsv($fh)) !== false) {
            if ($header === null) { $header = array_map(fn($h) => strtolower(trim($h)), $line); continue; }
            $row = @array_combine($header, $line);
            if (!$row) { continue; }
            $key = strtolower(trim($row['reg_no'] ?? $row['reg'] ?? $row['email'] ?? ''));
            $uid = $byReg[$key] ?? $byEmail[$key] ?? null;
            if (!$uid) { continue; }
            $rows++;
            foreach ($byLabel as $label => $cid) {
                if (array_key_exists($label, $row) && $row[$label] !== '') {
                    ResultSet::setScore((int) $set['id'], $uid, $cid, (float) $row[$label]);
                    $cells++;
                }
            }
        }
        fclose($fh);
        flash('success', "Imported {$cells} mark(s) for {$rows} student(s).");
        redirect('/results/' . $set['id']);
    }

    public function submit(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        Database::run("UPDATE result_sets SET status='pending' WHERE id=?", [$set['id']]);
        audit('result_submit', 'result_sets', (int) $set['id'], 'Submitted "' . $set['title'] . '" for approval');
        flash('success', 'Submitted for approval.');
        redirect('/results/' . $set['id']);
    }

    public function approve(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        Database::run("UPDATE result_sets SET status='approved', approved_by=?, approved_at=? WHERE id=?",
            [Auth::id(), date('Y-m-d H:i:s'), $set['id']]);
        // Notify each student their result is published.
        foreach (ResultSet::students($set) as $s) {
            Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                [$s['id'], 'Result published', 'Your result for "' . $set['title'] . '" is now available.']);
        }
        audit('result_approve', 'result_sets', (int) $set['id'], 'Approved & locked "' . $set['title'] . '"');
        flash('success', 'Result approved, locked and published to students.');
        redirect('/results/' . $set['id']);
    }

    /** Reopen an approved result - REQUIRES a recorded reason (authorisation trail). */
    public function reopen(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        if (!csrf_verify(input('_csrf'))) { redirect('/results/' . $set['id']); }
        $reason = trim((string) input('reason', ''));
        if (mb_strlen($reason) < 5) {
            flash('error', 'A reason (min 5 chars) is required to reopen an approved result.');
            redirect('/results/' . $set['id']);
        }
        Database::run("UPDATE result_sets SET status='draft', reopen_reason=? WHERE id=?", [$reason, $set['id']]);
        audit('result_reopen', 'result_sets', (int) $set['id'],
            'Reopened approved result "' . $set['title'] . '"', ['reason' => $reason]);
        flash('success', 'Result reopened for editing. The reason has been recorded in the audit log.');
        redirect('/results/' . $set['id']);
    }

    /** Printable single-student result card. */
    public function card(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        $student = Database::first("SELECT * FROM users WHERE id=?", [(int) ($params['uid'] ?? 0)]);
        if (!$student) { redirect('/results/' . $set['id']); }
        $components = ResultSet::components((int) $set['id']);
        $bands = ResultSet::bands($set);
        $r = ResultSet::compute($set, $components, (int) $student['id'], $bands);
        echo \App\Core\View::render('admin/result-card', [
            'title' => 'Result Card', 'set' => $set, 'student' => $student,
            'components' => $components, 'r' => $r,
        ], '');
    }

    /** Printable merit list (ranked). */
    public function merit(array $params): void
    {
        Auth::requireAdmin();
        $set = $this->set((int) ($params['id'] ?? 0));
        $data = ResultSet::table($set);
        echo \App\Core\View::render('admin/result-merit', [
            'title' => 'Merit List', 'set' => $set, 'rows' => $data['rows'], 'components' => $data['components'],
        ], '');
    }
}
