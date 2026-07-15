<?php
namespace App\Models;

use App\Core\Database;

/**
 * Daily cash register. Opening cash is set at day start; every cash
 * payment in and expense out is summed to an EXPECTED closing balance.
 * When the drawer is counted, any gap vs expected is flagged.
 */
class DayBook
{
    public static function forDate(string $date): ?array
    {
        return Database::first("SELECT * FROM day_books WHERE date = ?", [$date]);
    }

    /** Cash received (method=cash) on a date. */
    public static function cashIn(string $date): int
    {
        return (int) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE method='cash' AND date(paid_at) = ?", [$date]);
    }

    /** Cash paid out (expenses, method=cash) on a date. */
    public static function cashOut(string $date): int
    {
        return (int) Database::scalar(
            "SELECT COALESCE(SUM(amount),0) FROM expenses WHERE method='cash' AND date = ?", [$date]);
    }

    /** Expected closing = opening + cash in - cash out. */
    public static function expected(string $date, int $opening): int
    {
        return $opening + self::cashIn($date) - self::cashOut($date);
    }
}
