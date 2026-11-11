<?php
/**
 * Singleton Database Connection menggunakan PDO
 */

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require PATH_APP . '/Config/Database.php';

            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                if (defined('APP_ENV') && APP_ENV === 'development') {
                    die("Koneksi Database Gagal: " . $e->getMessage());
                } else {
                    die("Layanan basis data sedang mengalami kendala. Silakan hubungi admin sekolah.");
                }
            }
        }

        return self::$instance;
    }
}

