<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Thin PDO wrapper (singleton). Works with both SQLite (local dev) and
 * MySQL (Hostinger production) using the same query() helpers.
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = config('db');
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            if ($cfg['driver'] === 'sqlite') {
                $path = $cfg['sqlite'];
                if (!is_dir(dirname($path))) {
                    @mkdir(dirname($path), 0775, true);
                }
                self::$pdo = new PDO('sqlite:' . $path, null, null, $options);
                self::$pdo->exec('PRAGMA foreign_keys = ON');
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $cfg['host'], $cfg['port'], $cfg['name']
                );
                self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $options);
            }
        } catch (PDOException $e) {
            if (config('app.debug')) {
                throw $e;
            }
            http_response_code(500);
            exit('Database connection failed.');
        }

        return self::$pdo;
    }

    public static function driver(): string
    {
        return config('db.driver');
    }

    /** Run a query and return all rows. */
    public static function all(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Run a query and return the first row (or null). */
    public static function first(string $sql, array $params = []): ?array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Run an INSERT/UPDATE/DELETE; return last insert id for inserts. */
    public static function run(string $sql, array $params = []): string
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return self::pdo()->lastInsertId();
    }

    /** Scalar helper: returns the first column of the first row. */
    public static function scalar(string $sql, array $params = [])
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
