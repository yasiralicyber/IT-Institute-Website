<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Workflow;

class WorkflowController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->view('admin/workflows', [
            'title' => 'Automations', 'heading' => 'Automations',
            'rows' => Workflow::all(),
            'triggers' => Workflow::TRIGGERS, 'actionTypes' => Workflow::ACTIONS,
            'batches' => Database::all("SELECT id,name FROM batches WHERE status='active' ORDER BY name"),
        ], 'admin/layouts/admin');
    }

    public function store(): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/automations'); }
        $name = trim((string) input('name', ''));
        $trigger = (string) input('trigger', '');
        $actions = json_decode((string) input('actions', '[]'), true);
        if ($name === '' || !isset(Workflow::TRIGGERS[$trigger]) || !is_array($actions) || !$actions) {
            flash('error', 'Give it a name, a trigger and at least one action.');
            redirect('/automations');
        }
        Database::run("INSERT INTO workflows (name,trigger_event,actions,is_active) VALUES (?,?,?,1)",
            [$name, $trigger, json_encode($actions)]);
        audit('create', 'workflows', null, 'Created automation "' . $name . '"');
        flash('success', 'Automation created.');
        redirect('/automations');
    }

    public function toggle(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/automations'); }
        $id = (int) ($params['id'] ?? 0);
        $cur = (int) Database::scalar("SELECT is_active FROM workflows WHERE id=?", [$id]);
        Database::run("UPDATE workflows SET is_active=? WHERE id=?", [$cur ? 0 : 1, $id]);
        redirect('/automations');
    }

    public function destroy(array $params): void
    {
        Auth::requireAdmin();
        if (!csrf_verify(input('_csrf'))) { redirect('/automations'); }
        $id = (int) ($params['id'] ?? 0);
        if ($w = Database::first("SELECT * FROM workflows WHERE id=?", [$id])) {
            trash_record('workflows', $w, 'Automation: ' . $w['name']);
            audit('delete', 'workflows', $id, 'Deleted automation "' . $w['name'] . '"');
        }
        Database::run("DELETE FROM workflows WHERE id=?", [$id]);
        flash('success', 'Automation deleted.');
        redirect('/automations');
    }
}
