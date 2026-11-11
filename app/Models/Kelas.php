<?php
/**
 * Model Kelas (Rombongan Belajar)
 */

namespace App\Models;

use App\Core\Model;

class Kelas extends Model {
    public function ambilSemua(): array {
        $sql = "SELECT k.*, j.nama_jurusan, j.kode_jurusan 
                FROM kelas k
                JOIN jurusan j ON k.id_jurusan = j.id
                ORDER BY k.tingkat ASC, k.nama_kelas ASC";
        return $this->fetchAll($sql);
    }

    public function ambilBerdasarkanJurusan(int $idJurusan): array {
        $sql = "SELECT k.*, j.nama_jurusan, j.kode_jurusan,
                       COALESCE(sk.jumlah_siswa_aktif, 0) AS jumlah_siswa_aktif
                FROM kelas k
                JOIN jurusan j ON k.id_jurusan = j.id
                LEFT JOIN (
                    SELECT rks.id_kelas, COUNT(DISTINCT s.id) AS jumlah_siswa_aktif
                    FROM riwayat_kelas_siswa rks
                    JOIN siswa s ON rks.id_siswa = s.id
                    WHERE rks.id_tahun_ajaran = (
                              SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                          )
                      AND rks.status_penempatan = 'aktif'
                      AND s.status_siswa = 'aktif'
                    GROUP BY rks.id_kelas
                ) sk ON sk.id_kelas = k.id
                WHERE k.id_jurusan = :id_jurusan
                ORDER BY k.tingkat ASC, k.nama_kelas ASC";
        return $this->fetchAll($sql, [':id_jurusan' => $idJurusan]);
    }

    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT k.*, j.nama_jurusan, j.kode_jurusan 
                FROM kelas k
                JOIN jurusan j ON k.id_jurusan = j.id
                WHERE k.id = :id";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    public function cariKelasWaliAktif(int $idPengguna): ?array {
        $sql = "SELECT k.*, j.nama_jurusan, j.kode_jurusan, ta.nama_tahun 
                FROM penugasan_wali_kelas pwk
                JOIN guru g ON pwk.id_guru = g.id
                JOIN kelas k ON pwk.id_kelas = k.id
                JOIN jurusan j ON k.id_jurusan = j.id
                JOIN tahun_ajaran ta ON pwk.id_tahun_ajaran = ta.id
                WHERE g.id_pengguna = :id_pengguna 
                  AND pwk.apakah_aktif = 1 
                  AND ta.apakah_aktif = 1
                LIMIT 1";
        return $this->fetchOne($sql, [':id_pengguna' => $idPengguna]);
    }
}
