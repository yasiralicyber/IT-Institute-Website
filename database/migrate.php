<?php
/**
 * Schema migration. Driver-aware so the SAME schema builds on
 * SQLite (local dev) and MySQL (Hostinger production).
 *
 * Run:  php database/migrate.php
 */

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$driver = Database::driver();
$isSqlite = $driver === 'sqlite';

// Driver-specific snippets.
$PK   = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
$INT  = $isSqlite ? 'INTEGER' : 'INT';
$BOOL = $isSqlite ? 'INTEGER' : 'TINYINT(1)';
$REAL = $isSqlite ? 'REAL' : 'DECIMAL(8,2)';
$NOW  = 'CURRENT_TIMESTAMP';
$TS   = "TIMESTAMP DEFAULT {$NOW}";
$SUF  = $isSqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

$tables = [];

$tables['users'] = "
    id $PK,
    role TEXT NOT NULL DEFAULT 'student',
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    phone TEXT,
    photo TEXT,
    cnic TEXT,
    father_name TEXT,
    dob TEXT,
    blood_group TEXT,
    address TEXT,
    email_verified_at TIMESTAMP NULL,
    verify_token TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    device_violations $INT NOT NULL DEFAULT 0,
    reg_no TEXT,
    id_token TEXT,
    guardian_name TEXT,
    guardian_phone TEXT,
    guardian_pin TEXT,
    staff_role TEXT,
    last_login_ip TEXT,
    created_at $TS
";

$tables['courses'] = "
    id $PK,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    subtitle TEXT,
    category TEXT,
    level TEXT DEFAULT 'Beginner',
    description TEXT,
    outcomes TEXT,
    price $INT NOT NULL DEFAULT 0,
    thumbnail TEXT,
    accent TEXT DEFAULT '#2563eb',
    icon TEXT,
    total_minutes $INT DEFAULT 0,
    is_published $BOOL NOT NULL DEFAULT 1,
    sort $INT DEFAULT 0,
    created_at $TS
";

$tables['chapters'] = "
    id $PK,
    course_id $INT NOT NULL,
    title TEXT NOT NULL,
    sort $INT DEFAULT 0
";

$tables['lectures'] = "
    id $PK,
    chapter_id $INT NOT NULL,
    course_id $INT NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    video_url TEXT,
    resource_url TEXT,
    duration_min $INT DEFAULT 0,
    is_free $BOOL NOT NULL DEFAULT 0,
    release_at TEXT,
    expires_at TEXT,
    requires_ack $BOOL NOT NULL DEFAULT 0,
    ack_text TEXT,
    sort $INT DEFAULT 0
";

$tables['enrollments'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    status TEXT NOT NULL DEFAULT 'active',
    approved_at $TS
";

$tables['purchase_requests'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    amount $INT DEFAULT 0,
    reference_no TEXT,
    receipt_path TEXT,
    status TEXT NOT NULL DEFAULT 'pending',
    admin_note TEXT,
    created_at $TS,
    reviewed_at TIMESTAMP NULL
";

$tables['quizzes'] = "
    id $PK,
    chapter_id $INT NOT NULL,
    course_id $INT NOT NULL,
    title TEXT NOT NULL DEFAULT 'Chapter Test',
    pass_percent $INT NOT NULL DEFAULT 70,
    max_attempts $INT NOT NULL DEFAULT 3,
    secure_exam $BOOL NOT NULL DEFAULT 0,
    max_violations $INT NOT NULL DEFAULT 3,
    is_placement $BOOL NOT NULL DEFAULT 0,
    placement_skips $INT NOT NULL DEFAULT 0
";

$tables['questions'] = "
    id $PK,
    quiz_id $INT NOT NULL,
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_index $INT NOT NULL DEFAULT 0,
    sort $INT DEFAULT 0
";

$tables['quiz_attempts'] = "
    id $PK,
    user_id $INT NOT NULL,
    quiz_id $INT NOT NULL,
    score $INT DEFAULT 0,
    passed $BOOL DEFAULT 0,
    violations $INT NOT NULL DEFAULT 0,
    created_at $TS
";

// Locked-exam-browser violation log (tab switches, fullscreen exits, copy/paste).
$tables['exam_violations'] = "
    id $PK,
    user_id $INT NOT NULL,
    quiz_id $INT NOT NULL,
    kind TEXT NOT NULL,
    created_at $TS
";

// Score appeals (student challenges a published result / quiz score).
$tables['score_appeals'] = "
    id $PK,
    user_id $INT NOT NULL,
    ref_type TEXT NOT NULL DEFAULT 'result',
    ref_id $INT NULL,
    subject TEXT NOT NULL,
    reason TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'open',
    response TEXT,
    reviewed_by $INT NULL,
    reviewed_at TIMESTAMP NULL,
    created_at $TS
";

// ---- Learning enhancements (task #35) ----
// Reusable content blocks embeddable into many lectures.
$tables['learning_blocks'] = "
    id $PK,
    title TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'note',
    body TEXT NOT NULL,
    created_by $INT NULL,
    created_at $TS
";
$tables['lecture_blocks'] = "
    id $PK,
    lecture_id $INT NOT NULL,
    block_id $INT NOT NULL,
    sort $INT NOT NULL DEFAULT 0
";

// Acknowledgment tracking (student confirms they read/understood content).
$tables['acknowledgments'] = "
    id $PK,
    user_id $INT NOT NULL,
    ref_type TEXT NOT NULL DEFAULT 'lecture',
    ref_id $INT NOT NULL,
    note TEXT,
    created_at $TS,
    UNIQUE(user_id, ref_type, ref_id)
";

// Progress milestone snapshots (history of completion crossings).
$tables['progress_snapshots'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    milestone $INT NOT NULL,
    percent $INT NOT NULL,
    note TEXT,
    created_at $TS,
    UNIQUE(user_id, course_id, milestone)
";

// Multi-path: chapters a student tested out of via a placement test.
$tables['placement_skips'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    chapter_id $INT NOT NULL,
    created_at $TS,
    UNIQUE(user_id, chapter_id)
";

// Knowledge-decay revision schedule (one row per passed chapter).
$tables['revision_schedule'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    chapter_id $INT NOT NULL,
    due_at TEXT NOT NULL,
    done_at TIMESTAMP NULL,
    UNIQUE(user_id, chapter_id)
";

// ---- Security & governance (task #36) ----
// Risk-based login evaluation log.
$tables['login_events'] = "
    id $PK,
    user_id $INT NULL,
    email TEXT,
    ip TEXT,
    fingerprint TEXT,
    risk $INT NOT NULL DEFAULT 0,
    reasons TEXT,
    outcome TEXT NOT NULL DEFAULT 'allowed',
    created_at $TS
";

// Honeytokens: decoy links/content that should never be accessed legitimately.
$tables['honeytokens'] = "
    id $PK,
    label TEXT NOT NULL,
    token TEXT NOT NULL,
    hits $INT NOT NULL DEFAULT 0,
    last_ip TEXT,
    last_at TIMESTAMP NULL,
    created_at $TS
";
$tables['honeytoken_hits'] = "
    id $PK,
    token_id $INT NOT NULL,
    ip TEXT,
    ua TEXT,
    user_id $INT NULL,
    created_at $TS
";

// Two-approver sensitive-action queue.
$tables['sensitive_requests'] = "
    id $PK,
    action TEXT NOT NULL,
    summary TEXT NOT NULL,
    payload TEXT,
    requested_by $INT NULL,
    requested_name TEXT,
    approved_by $INT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    decision_note TEXT,
    created_at $TS,
    decided_at TIMESTAMP NULL
";

$tables['lecture_progress'] = "
    id $PK,
    user_id $INT NOT NULL,
    lecture_id $INT NOT NULL,
    completed_at $TS
";

$tables['certificates'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    chapter_id $INT NULL,
    type TEXT NOT NULL DEFAULT 'course',
    credential_id TEXT NOT NULL UNIQUE,
    student_name TEXT,
    course_title TEXT,
    issued_at $TS
";

// Physical / in-class test marks — quick per-student marks the student can
// check online (separate from the heavier weighted result_sets system).
$tables['test_marks'] = "
    id $PK,
    user_id $INT NOT NULL,
    test_name TEXT NOT NULL,
    subject TEXT,
    marks_obtained REAL NOT NULL DEFAULT 0,
    total_marks REAL NOT NULL DEFAULT 100,
    test_date TEXT,
    remarks TEXT,
    status TEXT NOT NULL DEFAULT 'published',
    entered_by $INT NULL,
    entered_by_name TEXT,
    created_at $TS
";

// In-house / physical certificate registry (verifiable by number, even for
// people who were never online students).
$tables['manual_certificates'] = "
    id $PK,
    cert_no TEXT UNIQUE,
    student_name TEXT NOT NULL,
    father_name TEXT,
    course TEXT,
    grade TEXT,
    from_date TEXT,
    to_date TEXT,
    issue_date TEXT,
    remarks TEXT,
    status TEXT NOT NULL DEFAULT 'valid',
    issued_by $INT NULL,
    issued_by_name TEXT,
    created_at $TS
";

$tables['community_threads'] = "
    id $PK,
    course_id $INT NULL,
    user_id $INT NOT NULL,
    title TEXT NOT NULL,
    body TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'open',
    created_at $TS
";

$tables['community_replies'] = "
    id $PK,
    thread_id $INT NOT NULL,
    user_id $INT NOT NULL,
    is_admin $BOOL NOT NULL DEFAULT 0,
    body TEXT NOT NULL,
    created_at $TS
";

$tables['reviews'] = "
    id $PK,
    user_id $INT NOT NULL,
    course_id $INT NOT NULL,
    rating $INT NOT NULL DEFAULT 5,
    body TEXT,
    status TEXT NOT NULL DEFAULT 'pending',
    created_at $TS
";

$tables['admissions'] = "
    id $PK,
    name TEXT NOT NULL,
    father_name TEXT NOT NULL,
    address TEXT,
    contact TEXT,
    dob TEXT,
    form_b TEXT,
    email TEXT,
    programs TEXT,
    gender TEXT,
    cnic TEXT,
    photo TEXT,
    status TEXT NOT NULL DEFAULT 'new',
    created_at $TS
";

$tables['timetable'] = "
    id $PK,
    title TEXT NOT NULL,
    body TEXT,
    image_path TEXT,
    sort $INT DEFAULT 0,
    is_published $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

$tables['devices'] = "
    id $PK,
    user_id $INT NOT NULL,
    device_type TEXT NOT NULL,
    fingerprint TEXT NOT NULL,
    label TEXT,
    last_seen $TS,
    created_at $TS
";

$tables['settings'] = "
    key_name TEXT NOT NULL UNIQUE,
    value TEXT
";

$tables['notifications'] = "
    id $PK,
    user_id $INT NOT NULL,
    title TEXT NOT NULL,
    body TEXT,
    is_read $BOOL NOT NULL DEFAULT 0,
    created_at $TS
";

// ---- Institute management (physical campus) ----

$tables['classrooms'] = "
    id $PK,
    name TEXT NOT NULL,
    capacity $INT DEFAULT 0,
    location TEXT,
    notes TEXT,
    created_at $TS
";

$tables['staff'] = "
    id $PK,
    name TEXT NOT NULL,
    role TEXT,
    phone TEXT,
    email TEXT,
    photo TEXT,
    expertise TEXT,
    bio TEXT,
    salary $INT NOT NULL DEFAULT 0,
    user_id $INT NULL,
    sort $INT DEFAULT 0,
    is_published $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

// Monthly staff salary payments (payroll).
$tables['salary_payments'] = "
    id $PK,
    staff_id $INT NOT NULL,
    fee_month TEXT NOT NULL,
    amount $INT NOT NULL DEFAULT 0,
    method TEXT NOT NULL DEFAULT 'cash',
    note TEXT,
    paid_by $INT NULL,
    paid_at $TS,
    UNIQUE(staff_id, fee_month)
";

$tables['batches'] = "
    id $PK,
    course_id $INT NOT NULL,
    name TEXT NOT NULL,
    classroom_id $INT NULL,
    staff_id $INT NULL,
    capacity $INT DEFAULT 30,
    schedule TEXT,
    start_date TEXT,
    end_date TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    created_at $TS
";

$tables['batch_students'] = "
    id $PK,
    batch_id $INT NOT NULL,
    user_id $INT NOT NULL,
    roll_no TEXT,
    status TEXT NOT NULL DEFAULT 'active',
    joined_at $TS
";

$tables['attendance'] = "
    id $PK,
    batch_id $INT NOT NULL,
    user_id $INT NOT NULL,
    date TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'present',
    method TEXT NOT NULL DEFAULT 'manual',
    marked_by $INT NULL,
    device_id $INT NULL,
    fingerprint TEXT NULL,
    created_at $TS
";

$tables['batch_checkin'] = "
    id $PK,
    batch_id $INT NOT NULL,
    token TEXT NOT NULL,
    date TEXT NOT NULL,
    expires_at TEXT NULL,
    is_open $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

// ---- Institute finance ----

$tables['fee_invoices'] = "
    id $PK,
    user_id $INT NOT NULL,
    batch_id $INT NULL,
    plan_id $INT NULL,
    installment_no $INT NULL,
    type TEXT NOT NULL DEFAULT 'monthly',
    title TEXT NOT NULL,
    amount $INT NOT NULL DEFAULT 0,
    discount $INT NOT NULL DEFAULT 0,
    fee_month TEXT,
    due_date TEXT,
    status TEXT NOT NULL DEFAULT 'unpaid',
    notes TEXT,
    created_by $INT NULL,
    created_at $TS
";

$tables['fee_payments'] = "
    id $PK,
    user_id $INT NOT NULL,
    invoice_id $INT NULL,
    amount $INT NOT NULL DEFAULT 0,
    method TEXT NOT NULL DEFAULT 'cash',
    reference TEXT,
    receipt_no TEXT,
    note TEXT,
    received_by $INT NULL,
    paid_at $TS
";

// ---- Configurable fee rules engine ----
$tables['fee_plans'] = "
    id $PK,
    name TEXT NOT NULL,
    course_id $INT NULL,
    admission_fee $INT NOT NULL DEFAULT 0,
    security_deposit $INT NOT NULL DEFAULT 0,
    tuition_fee $INT NOT NULL DEFAULT 0,
    installments $INT NOT NULL DEFAULT 1,
    late_fee_flat $INT NOT NULL DEFAULT 0,
    late_fee_per_day $INT NOT NULL DEFAULT 0,
    grace_days $INT NOT NULL DEFAULT 0,
    sibling_discount_pct $INT NOT NULL DEFAULT 0,
    early_discount_pct $INT NOT NULL DEFAULT 0,
    scholarship_note TEXT,
    is_active $BOOL NOT NULL DEFAULT 1,
    created_by $INT NULL,
    created_at $TS
";

// One payment can be split across many invoices (payment allocation).
$tables['fee_allocations'] = "
    id $PK,
    payment_id $INT NOT NULL,
    invoice_id $INT NOT NULL,
    amount $INT NOT NULL DEFAULT 0,
    created_at $TS
";

// Daily cash register (day book) with discrepancy detection.
$tables['day_books'] = "
    id $PK,
    date TEXT NOT NULL,
    opening $INT NOT NULL DEFAULT 0,
    expected_close $INT NOT NULL DEFAULT 0,
    actual_close $INT NULL,
    discrepancy $INT NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'open',
    opened_by $INT NULL,
    closed_by $INT NULL,
    note TEXT,
    closed_at TIMESTAMP NULL,
    created_at $TS
";

// Cash-out / expenses (affect the day book).
$tables['expenses'] = "
    id $PK,
    date TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT 'general',
    amount $INT NOT NULL DEFAULT 0,
    method TEXT NOT NULL DEFAULT 'cash',
    payee TEXT,
    note TEXT,
    created_by $INT NULL,
    created_at $TS
";

// Installment restructuring history (originals preserved, never overwritten).
$tables['fee_restructures'] = "
    id $PK,
    user_id $INT NOT NULL,
    reason TEXT,
    old_plan TEXT,
    new_plan TEXT,
    created_by $INT NULL,
    created_at $TS
";

// ---- Results management ----
$tables['grading_schemes'] = "
    id $PK,
    name TEXT NOT NULL,
    bands TEXT NOT NULL,
    is_default $BOOL NOT NULL DEFAULT 0,
    created_at $TS
";

$tables['result_sets'] = "
    id $PK,
    title TEXT NOT NULL,
    course_id $INT NULL,
    batch_id $INT NULL,
    scheme_id $INT NULL,
    pass_mark $INT NOT NULL DEFAULT 40,
    status TEXT NOT NULL DEFAULT 'draft',
    created_by $INT NULL,
    approved_by $INT NULL,
    approved_at TIMESTAMP NULL,
    reopen_reason TEXT,
    created_at $TS
";

$tables['result_components'] = "
    id $PK,
    result_set_id $INT NOT NULL,
    ckey TEXT NOT NULL,
    label TEXT NOT NULL,
    source TEXT NOT NULL DEFAULT 'offline',
    weight $INT NOT NULL DEFAULT 0,
    max_marks $INT NOT NULL DEFAULT 100,
    quiz_id $INT NULL,
    sort $INT NOT NULL DEFAULT 0
";

$tables['result_scores'] = "
    id $PK,
    result_set_id $INT NOT NULL,
    user_id $INT NOT NULL,
    component_id $INT NOT NULL,
    obtained $REAL NOT NULL DEFAULT 0,
    UNIQUE(result_set_id, user_id, component_id)
";

// ---- Notices / announcements ----

$tables['notices'] = "
    id $PK,
    title TEXT NOT NULL,
    body TEXT,
    audience TEXT NOT NULL DEFAULT 'all',
    is_published $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

// ---- System: audit log, recycle bin, backups ----

$tables['audit_log'] = "
    id $PK,
    user_id $INT NULL,
    user_name TEXT,
    action TEXT NOT NULL,
    entity TEXT,
    entity_id $INT NULL,
    summary TEXT,
    meta TEXT,
    ip TEXT,
    created_at $TS
";

$tables['trash'] = "
    id $PK,
    table_name TEXT NOT NULL,
    record_id $INT NOT NULL,
    label TEXT,
    data TEXT NOT NULL,
    children TEXT,
    deleted_by $INT NULL,
    deleted_by_name TEXT,
    deleted_at $TS
";

$tables['backups'] = "
    id $PK,
    filename TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'full',
    size $INT DEFAULT 0,
    note TEXT,
    created_by $INT NULL,
    created_at $TS
";

$tables['projects'] = "
    id $PK,
    user_id $INT NOT NULL,
    title TEXT NOT NULL,
    type TEXT,
    description TEXT,
    link TEXT,
    image TEXT,
    file TEXT,
    status TEXT NOT NULL DEFAULT 'pending',
    featured $BOOL NOT NULL DEFAULT 0,
    admin_note TEXT,
    created_at $TS
";

$tables['events'] = "
    id $PK,
    title TEXT NOT NULL,
    type TEXT NOT NULL DEFAULT 'event',
    body TEXT,
    event_date TEXT,
    image TEXT,
    is_published $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

$tables['lecture_markers'] = "
    id $PK,
    lecture_id $INT NOT NULL,
    seconds $INT NOT NULL DEFAULT 0,
    label TEXT NOT NULL
";

$tables['lecture_questions'] = "
    id $PK,
    lecture_id $INT NOT NULL,
    seconds $INT NOT NULL DEFAULT 0,
    question TEXT NOT NULL,
    options TEXT NOT NULL,
    correct_index $INT NOT NULL DEFAULT 0
";

$tables['lecture_notes'] = "
    id $PK,
    user_id $INT NOT NULL,
    lecture_id $INT NOT NULL,
    body TEXT,
    updated_at $TS
";

$tables['lecture_bookmarks'] = "
    id $PK,
    user_id $INT NOT NULL,
    lecture_id $INT NOT NULL,
    seconds $INT NOT NULL DEFAULT 0,
    label TEXT,
    created_at $TS
";

$tables['student_notes'] = "
    id $PK,
    user_id $INT NOT NULL,
    author_id $INT NULL,
    author_name TEXT,
    body TEXT NOT NULL,
    created_at $TS
";

$tables['workflows'] = "
    id $PK,
    name TEXT NOT NULL,
    trigger_event TEXT NOT NULL,
    actions TEXT NOT NULL,
    is_active $BOOL NOT NULL DEFAULT 1,
    created_at $TS
";

$tables['import_sessions'] = "
    id $PK,
    section TEXT NOT NULL,
    source TEXT NOT NULL DEFAULT 'file',
    filename TEXT,
    storage_path TEXT,
    columns TEXT,
    mapping TEXT,
    dupe_strategy TEXT NOT NULL DEFAULT 'skip',
    total_rows $INT DEFAULT 0,
    imported $INT DEFAULT 0,
    updated $INT DEFAULT 0,
    skipped $INT DEFAULT 0,
    failed $INT DEFAULT 0,
    created_ids TEXT,
    errors TEXT,
    status TEXT NOT NULL DEFAULT 'mapping',
    created_by $INT NULL,
    created_by_name TEXT,
    created_at $TS
";

// ---- Website content (CMS: hero slides, awards, activity gallery) ----
$tables += require __DIR__ . '/schema/website_content.php';

// MySQL cannot index a TEXT/BLOB column without a key length. Convert every
// TEXT column that is UNIQUE (inline) or referenced by a UNIQUE(...) constraint
// into VARCHAR(191) — long-content TEXT columns (bodies, JSON, notes) stay TEXT.
// SQLite happily indexes TEXT, so it is left untouched.
if (!$isSqlite) {
    foreach ($tables as $name => $cols) {
        // a) inline "<type> TEXT [NOT NULL] UNIQUE"
        $cols = preg_replace('/\bTEXT(\s+NOT\s+NULL)?\s+UNIQUE\b/i', 'VARCHAR(191)$1 UNIQUE', $cols);
        // b) columns named inside a composite UNIQUE(...) that are declared TEXT
        if (preg_match_all('/UNIQUE\s*\(([^)]+)\)/i', $cols, $mm)) {
            foreach ($mm[1] as $list) {
                foreach (explode(',', $list) as $col) {
                    $col = trim($col);
                    if ($col === '') { continue; }
                    $cols = preg_replace('/(\b' . preg_quote($col, '/') . '\s+)TEXT\b/i', '$1VARCHAR(191)', $cols);
                }
            }
        }
        $tables[$name] = $cols;
    }
}

$pdo = Database::pdo();
foreach ($tables as $name => $cols) {
    $pdo->exec("DROP TABLE IF EXISTS {$name}");
    $pdo->exec("CREATE TABLE {$name} ({$cols}){$SUF}");
    echo "  ✓ created table {$name}\n";
}

echo "Migration complete ({$driver}).\n";
