<?php
namespace App\Models;

use App\Core\Database;

/**
 * Simple automation engine. Admins build workflows = a trigger + an ordered
 * list of actions. fire() is called from the app at trigger points and runs
 * every matching active workflow's actions against a context (the student).
 */
class Workflow
{
    public const TRIGGERS = [
        'student_registered' => 'When a student registers an account',
        'payment_approved'   => 'When an online course payment is approved',
    ];

    public const ACTIONS = [
        'notify'       => 'Send a notification',
        'create_fee'   => 'Create a fee charge',
        'add_to_batch' => 'Add student to a batch',
        'generate_id'  => 'Generate ID card / registration number',
    ];

    public static function fire(string $trigger, array $ctx): void
    {
        $userId = (int) ($ctx['user_id'] ?? 0);
        if (!$userId) { return; }
        $user = User::find($userId);
        if (!$user) { return; }

        foreach (Database::all("SELECT * FROM workflows WHERE trigger_event=? AND is_active=1", [$trigger]) as $wf) {
            foreach (json_decode($wf['actions'], true) ?: [] as $a) {
                self::run($a, $user, $ctx);
            }
        }
    }

    private static function run(array $a, array $user, array $ctx): void
    {
        $uid = (int) $user['id'];
        $name = $user['name'] ?? 'Student';
        try {
            switch ($a['type'] ?? '') {
                case 'notify':
                    Database::run("INSERT INTO notifications (user_id,title,body) VALUES (?,?,?)",
                        [$uid, $a['title'] ?? 'Notification', str_replace('{name}', $name, (string) ($a['body'] ?? ''))]);
                    break;
                case 'create_fee':
                    Database::run("INSERT INTO fee_invoices (user_id,type,title,amount,discount,status) VALUES (?,?,?,?,?,'unpaid')",
                        [$uid, $a['fee_type'] ?? 'other', $a['title'] ?? 'Fee', (int) ($a['amount'] ?? 0), (int) ($a['discount'] ?? 0)]);
                    break;
                case 'add_to_batch':
                    $bid = (int) ($a['batch_id'] ?? 0);
                    if ($bid && !Database::scalar("SELECT COUNT(*) FROM batch_students WHERE batch_id=? AND user_id=?", [$bid, $uid])) {
                        Database::run("INSERT INTO batch_students (batch_id,user_id) VALUES (?,?)", [$bid, $uid]);
                    }
                    break;
                case 'generate_id':
                    \App\Controllers\Admin\StudentController::ensureIdentity($user);
                    break;
            }
        } catch (\Throwable $e) { /* one bad action shouldn't break the trigger */ }
    }

    public static function all(): array
    {
        return Database::all("SELECT * FROM workflows ORDER BY id DESC");
    }
}
