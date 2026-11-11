<?php
/**
 * Helper Pencatatan Log Audit Forensik
 */

namespace App\Helpers;

use App\Core\Database;
use PDO;

class Audit {
    public static function catat(string $aksi, string $modul, ?int $idData = null, ?array $nilaiLama = null, ?array $nilaiBaru = null): void {
        try {
            $db = Database::getConnection();
            $idPengguna = $_SESSION['pengguna_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

            $stmt = $db->prepare("
                INSERT INTO log_audit (id_pengguna, aksi, modul, id_data, nilai_lama, nilai_baru, alamat_ip, agen_pengguna, dibuat_pada)
                VALUES (:id_pengguna, :aksi, :modul, :id_data, :nilai_lama, :nilai_baru, :alamat_ip, :agen_pengguna, NOW())
            ");

            $stmt->execute([
                ':id_pengguna'  => $idPengguna,
                ':aksi'         => $aksi,
                ':modul'        => $modul,
                ':id_data'      => $idData,
                ':nilai_lama'   => $nilaiLama ? json_encode($nilaiLama, JSON_UNESCAPED_UNICODE) : null,
                ':nilai_baru'   => $nilaiBaru ? json_encode($nilaiBaru, JSON_UNESCAPED_UNICODE) : null,
                ':alamat_ip'    => $ip,
                ':agen_pengguna'=> $userAgent,
            ]);
        } catch (\Exception $e) {
            error_log("Gagal mencatat log audit: " . $e->getMessage());
        }
    }
}

