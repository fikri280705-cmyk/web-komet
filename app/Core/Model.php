<?php
/**
 * Base Model untuk Interaksi Basis Data PDO MySQL
 */

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Eksekusi query dengan prepared statements
     */
    protected function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Mengambil banyak baris hasil query
     */
    protected function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Mengambil satu baris hasil query
     */
    protected function fetchOne(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Mengambil satu nilai kolom tunggal
     */
    protected function fetchColumn(string $sql, array $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Ambil ID hasil auto-increment terakhir
     */
    protected function lastInsertId(): int {
        return (int) $this->db->lastInsertId();
    }
}

