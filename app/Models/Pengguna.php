<?php
/**
 * Model Pengguna (Otentikasi & Akun)
 */

namespace App\Models;

use App\Core\Model;

class Pengguna extends Model {
    /**
     * Cari pengguna berdasarkan nomor induk (NIS untuk siswa, NUPTK untuk guru) atau email
     */
    public function cariBerdasarkanKredensial(string $nomorIndukAtauEmail): ?array {
        $sql = "SELECT p.*, r.kode_peran, r.nama_peran 
                FROM pengguna p
                JOIN peran r ON p.id_peran = r.id
                WHERE (p.nomor_induk = :nomor OR p.email = :email) 
                  AND p.apakah_aktif = 1
                LIMIT 1";
        return $this->fetchOne($sql, [
            ':nomor' => $nomorIndukAtauEmail,
            ':email' => $nomorIndukAtauEmail
        ]);
    }

    /**
     * Cari berdasarkan ID Pengguna
     */
    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT p.*, r.kode_peran, r.nama_peran 
                FROM pengguna p
                JOIN peran r ON p.id_peran = r.id
                WHERE p.id = :id
                LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Verifikasi kata sandi
     */
    public function verifikasiKataSandi(string $kataSandiInput, string $hashDatabase): bool {
        return password_verify($kataSandiInput, $hashDatabase);
    }

    /**
     * Perbarui nomor telepon pengguna
     */
    public function perbaruiTelepon(int $idPengguna, string $nomorTelepon): bool {
        $sql = "UPDATE pengguna SET nomor_telepon = :telp, diperbarui_pada = NOW() WHERE id = :id";
        return $this->query($sql, [':telp' => $nomorTelepon, ':id' => $idPengguna])->rowCount() > 0;
    }

    /**
     * Perbarui foto profil pengguna
     */
    public function perbaruiFotoProfil(int $idPengguna, string $namaFoto): bool {
        $sql = "UPDATE pengguna SET foto_profil = :foto, diperbarui_pada = NOW() WHERE id = :id";
        return $this->query($sql, [':foto' => $namaFoto, ':id' => $idPengguna])->rowCount() > 0;
    }

    /**
     * Perbarui kata sandi
     */
    public function perbaruiKataSandi(int $idPengguna, string $kataSandiBaru): bool {
        $hashBaru = password_hash($kataSandiBaru, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = "UPDATE pengguna SET kata_sandi = :hash, diperbarui_pada = NOW() WHERE id = :id";
        return $this->query($sql, [':hash' => $hashBaru, ':id' => $idPengguna])->rowCount() > 0;
    }

    public function nomorIndukSudahAda(string $nomorInduk, ?int $kecualiId = null): bool {
        $sql = "SELECT id FROM pengguna WHERE nomor_induk = :nomor";
        $params = [':nomor' => $nomorInduk];
        if ($kecualiId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $kecualiId;
        }
        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params) !== null;
    }

    public function buatAkun(array $data): int {
        $hash = password_hash($data['kata_sandi'], PASSWORD_BCRYPT, ['cost' => 12]);
        $this->query(
            "INSERT INTO pengguna (nomor_induk, email, kata_sandi, id_peran, nama_lengkap, nomor_telepon, foto_profil, apakah_aktif)
             VALUES (:nomor, :email, :hash, :id_peran, :nama, :telp, 'avatar_default.png', 1)",
            [
                ':nomor'    => $data['nomor_induk'],
                ':email'    => $data['email'] ?: null,
                ':hash'     => $hash,
                ':id_peran' => (int)$data['id_peran'],
                ':nama'     => $data['nama_lengkap'],
                ':telp'     => $data['nomor_telepon'] ?: null,
            ]
        );
        return $this->lastInsertId();
    }

    public function idPeranDariKode(string $kodePeran): ?int {
        $id = $this->fetchColumn(
            "SELECT id FROM peran WHERE kode_peran = :kode LIMIT 1",
            [':kode' => $kodePeran]
        );
        return $id !== false ? (int)$id : null;
    }

    /**
     * Hapus akun bila aman; jika masih direferensi (event/log), nonaktifkan saja.
     */
    public function hapusAtauNonaktifkan(int $idPengguna): string {
        try {
            $this->query("DELETE FROM pengguna WHERE id = :id", [':id' => $idPengguna]);
            return 'dihapus';
        } catch (\PDOException $e) {
            $this->query(
                "UPDATE pengguna SET apakah_aktif = 0, diperbarui_pada = NOW() WHERE id = :id",
                [':id' => $idPengguna]
            );
            return 'dinonaktifkan';
        }
    }
}
