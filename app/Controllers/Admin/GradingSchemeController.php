<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class GradingSchemeController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $schemes = Database::all("SELECT * FROM grading_schemes ORDER BY is_default DESC, name");
        foreach ($schemes as &$s) { $s['bandsArr'] = json_decode($s['bands'], true) ?: []; }
        $this->view('admin/grading-schemes', [
            'title' => 'Grading Schemes', 'heading' => 'Grading Schemes', 'schemes' => $schemes,
        ], 'admin/layouts/admin');
    }

    public function save(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/grading-schemes'); }
        $name = trim((string) input('name', ''));
        if ($name === '') { flash('error', 'Enter a scheme name.'); redirect('/grading-schemes'); }

        // Parse band rows: min[], grade[], gpa[], remark[].
        $mins = (array) input('min', []); $grades = (array) input('grade', []);
        $gpas = (array) input('gpa', []); $remarks = (array) input('remark', []);
        $bands = [];
        foreach ($mins as $i => $m) {
            if (($grades[$i] ?? '') === '') { continue; }
            $bands[] = ['min' => (float) $m, 'grade' => trim((string) $grades[$i]),
                        'gpa' => (float) ($gpas[$i] ?? 0), 'remark' => trim((string) ($remarks[$i] ?? ''))];
        }
        usort($bands, fn($a, $b) => $b['min'] <=> $a['min']);
        $json = json_encode($bands);
        $isDefault = input('is_default') ? 1 : 0;

        $id = (int) ($params['id'] ?? input('id') ?? 0);
        if ($isDefault) { Database::run("UPDATE grading_schemes SET is_default=0"); }
        if ($id && Database::scalar("SELECT id FROM grading_schemes WHERE id=?", [$id])) {
            Database::run("UPDATE grading_schemes SET name=?, bands=?, is_default=? WHERE id=?", [$name, $json, $isDefault, $id]);
        } else {
            $id = (int) Database::run("INSERT INTO grading_schemes (name,bands,is_default) VALUES (?,?,?)", [$name, $json, $isDefault]);
        }
        audit('grading_scheme', 'grading_schemes', $id, 'Saved grading scheme "' . $name . '"');
        flash('success', 'Grading scheme saved.');
        redirect('/grading-schemes');
    }

    public function delete(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/grading-schemes'); }
        $id = (int) ($params['id'] ?? 0);
        Database::run("DELETE FROM grading_schemes WHERE id=?", [$id]);
        flash('success', 'Scheme deleted.');
        redirect('/grading-schemes');
    }
}
