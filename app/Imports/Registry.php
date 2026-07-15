<?php
namespace App\Imports;

/**
 * Registry of importable sections. Each section maps spreadsheet columns to a
 * database table with per-field validation, transforms, relationships and
 * duplicate-matching. New sections are added here only - the import engine,
 * wizard, templates and export all read from this one place.
 */
class Registry
{
    public static function all(): array
    {
        return [
            'students' => [
                'label' => 'Students', 'table' => 'users', 'fixed' => ['role' => 'student', 'status' => 'active'],
                'dupe' => ['email'],
                'fields' => [
                    'name'  => ['label' => 'Name', 'required' => true],
                    'email' => ['label' => 'Email', 'required' => true, 'validate' => 'email', 'transform' => 'lower'],
                    'phone' => ['label' => 'Phone', 'transform' => 'phone'],
                    'password' => ['label' => 'Password', 'transform' => 'password', 'default' => 'itti1234'],
                    'reg_no' => ['label' => 'Registration No.'],
                    'guardian_name' => ['label' => 'Guardian Name'],
                    'guardian_phone' => ['label' => 'Guardian Phone', 'transform' => 'phone'],
                    'guardian_pin' => ['label' => 'Guardian PIN'],
                ],
            ],
            'staff' => [
                'label' => 'Staff / Instructors', 'table' => 'staff', 'fixed' => ['is_published' => 1],
                'dupe' => ['email'],
                'fields' => [
                    'name' => ['label' => 'Name', 'required' => true],
                    'role' => ['label' => 'Role / Designation'],
                    'phone' => ['label' => 'Phone', 'transform' => 'phone'],
                    'email' => ['label' => 'Email', 'validate' => 'email', 'transform' => 'lower'],
                    'expertise' => ['label' => 'Expertise'],
                    'bio' => ['label' => 'Bio'],
                ],
            ],
            'courses' => [
                'label' => 'Courses / Programs', 'table' => 'courses', 'fixed' => ['is_published' => 1, 'accent' => '#274a70'],
                'dupe' => ['slug'], 'auto' => ['slug' => 'title'],
                'fields' => [
                    'title' => ['label' => 'Title', 'required' => true],
                    'slug' => ['label' => 'Slug (auto if blank)', 'transform' => 'slug'],
                    'subtitle' => ['label' => 'Subtitle'],
                    'category' => ['label' => 'Category'],
                    'level' => ['label' => 'Level'],
                    'price' => ['label' => 'Price (PKR)', 'validate' => 'int', 'default' => 0],
                    'description' => ['label' => 'Description'],
                ],
            ],
            'classrooms' => [
                'label' => 'Classrooms', 'table' => 'classrooms', 'dupe' => ['name'],
                'fields' => [
                    'name' => ['label' => 'Name', 'required' => true],
                    'capacity' => ['label' => 'Capacity', 'validate' => 'int', 'default' => 0],
                    'location' => ['label' => 'Location'],
                ],
            ],
            'batches' => [
                'label' => 'Batches', 'table' => 'batches', 'fixed' => ['status' => 'active'], 'dupe' => ['name'],
                'fields' => [
                    'name' => ['label' => 'Batch Name', 'required' => true],
                    'course_id' => ['label' => 'Program / Course', 'required' => true, 'relation' => ['table' => 'courses', 'match' => ['title', 'slug']]],
                    'staff_id' => ['label' => 'Teacher', 'relation' => ['table' => 'staff', 'match' => ['name']]],
                    'classroom_id' => ['label' => 'Classroom', 'relation' => ['table' => 'classrooms', 'match' => ['name']]],
                    'capacity' => ['label' => 'Capacity', 'validate' => 'int', 'default' => 30],
                    'schedule' => ['label' => 'Schedule'],
                    'start_date' => ['label' => 'Start Date', 'transform' => 'date'],
                    'end_date' => ['label' => 'End Date', 'transform' => 'date'],
                ],
            ],
            'admissions' => [
                'label' => 'Admissions', 'table' => 'admissions', 'fixed' => ['status' => 'new'], 'dupe' => [],
                'fields' => [
                    'name' => ['label' => 'Name', 'required' => true],
                    'father_name' => ['label' => "Father's Name", 'required' => true],
                    'contact' => ['label' => 'Contact No', 'transform' => 'phone'],
                    'dob' => ['label' => 'Date of Birth', 'transform' => 'date'],
                    'form_b' => ['label' => 'Form-B / CNIC'],
                    'email' => ['label' => 'Email', 'validate' => 'email', 'transform' => 'lower'],
                    'programs' => ['label' => 'Program'],
                    'gender' => ['label' => 'Gender'],
                    'address' => ['label' => 'Address'],
                ],
            ],
            'attendance' => [
                'label' => 'Attendance', 'table' => 'attendance', 'fixed' => ['method' => 'import'], 'dupe' => ['batch_id', 'user_id', 'date'],
                'fields' => [
                    'batch_id' => ['label' => 'Batch', 'required' => true, 'relation' => ['table' => 'batches', 'match' => ['name']]],
                    'user_id' => ['label' => 'Student (email or reg-no)', 'required' => true, 'relation' => ['table' => 'users', 'match' => ['email', 'reg_no']]],
                    'date' => ['label' => 'Date', 'required' => true, 'transform' => 'date'],
                    'status' => ['label' => 'Status (present/late/absent)', 'default' => 'present'],
                ],
            ],
            'fee_invoices' => [
                'label' => 'Fee Charges', 'table' => 'fee_invoices', 'fixed' => ['status' => 'unpaid', 'type' => 'other'], 'dupe' => [],
                'fields' => [
                    'user_id' => ['label' => 'Student (email or reg-no)', 'required' => true, 'relation' => ['table' => 'users', 'match' => ['email', 'reg_no']]],
                    'title' => ['label' => 'Title', 'required' => true],
                    'amount' => ['label' => 'Amount', 'required' => true, 'validate' => 'int'],
                    'discount' => ['label' => 'Discount', 'validate' => 'int', 'default' => 0],
                    'due_date' => ['label' => 'Due Date', 'transform' => 'date'],
                ],
            ],
            'notices' => [
                'label' => 'Notices', 'table' => 'notices', 'fixed' => ['is_published' => 1], 'dupe' => [],
                'fields' => [
                    'title' => ['label' => 'Title', 'required' => true],
                    'body' => ['label' => 'Body'],
                    'audience' => ['label' => 'Audience (all/students/guardians)', 'default' => 'all'],
                ],
            ],
            'timetable' => [
                'label' => 'Timetable', 'table' => 'timetable', 'fixed' => ['is_published' => 1], 'dupe' => [],
                'fields' => [
                    'title' => ['label' => 'Title', 'required' => true],
                    'body' => ['label' => 'Schedule Text'],
                ],
            ],
        ];
    }

    public static function get(string $section): ?array
    {
        return self::all()[$section] ?? null;
    }
}
