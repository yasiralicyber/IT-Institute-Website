<?php
namespace App\Models;

use App\Core\Database;

class Project
{
    public const TYPES = ['Website', 'Python Application', 'Java Project', 'C++ Program', 'Network Design', 'CCTV Installation', 'Cyber Security Report', 'Other'];

    public static function forUser(int $userId): array
    {
        return Database::all("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
    }

    public static function approvedForUser(int $userId): array
    {
        return Database::all("SELECT * FROM projects WHERE user_id = ? AND status = 'approved' ORDER BY featured DESC, created_at DESC", [$userId]);
    }

    public static function showcase(int $limit = 30): array
    {
        return Database::all(
            "SELECT p.*, u.name AS author, u.reg_no FROM projects p
             JOIN users u ON u.id = p.user_id
             WHERE p.status = 'approved' ORDER BY p.featured DESC, p.created_at DESC LIMIT ?", [$limit]);
    }

    public static function find(int $id): ?array
    {
        return Database::first("SELECT * FROM projects WHERE id = ?", [$id]);
    }

    /** Can this project's media be shown publicly (approved) or to its owner? */
    public static function viewable(array $project, ?int $viewerId): bool
    {
        return $project['status'] === 'approved' || ($viewerId && (int) $project['user_id'] === $viewerId);
    }
}
