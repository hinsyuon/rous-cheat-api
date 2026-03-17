<?php

namespace App\Helpers;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $pdo = null;

    public static function init(): void
    {
        if (self::$pdo) return;

        $driver = Env::get('DB_DRIVER', 'sqlite');

        try {
            if ($driver === 'sqlite') {
                $path = Env::get('SQLITE_PATH', './database/rous_cheat.sqlite');
                // Ensure directory exists
                $dir = dirname($path);
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                self::$pdo = new PDO("sqlite:$path");
            } else {
                $host = Env::get('DB_HOST', '127.0.0.1');
                $port = Env::get('DB_PORT', '3306');
                $db   = Env::get('DB_DATABASE', 'rous_cheat');
                $user = Env::get('DB_USERNAME', 'root');
                $pass = Env::get('DB_PASSWORD', '');
                self::$pdo = new PDO(
                    "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                    $user, $pass
                );
            }

            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            if ($driver === 'sqlite') {
                self::$pdo->exec('PRAGMA foreign_keys = ON');
                self::$pdo->exec('PRAGMA journal_mode = WAL');
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => ['code' => 500, 'message' => 'Database connection failed: ' . $e->getMessage()]]);
            exit;
        }
    }

    public static function pdo(): PDO
    {
        if (!self::$pdo) self::init();
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string
    {
        return self::pdo()->lastInsertId();
    }

    public static function count(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
