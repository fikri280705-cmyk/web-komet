<?php
/**
 * Model Pengurus OSIS SMKN 9 Kabupaten Tangerang
 */

namespace App\Models;

use App\Core\Model;

class Osis extends Model {
    public function ambilSemuaPengurus(): array {
        $sql = "SELECT po.*, s.nis, s.nama_lengkap, s.jenis_kelamin, k.nama_kelas, j.kode_jurusan, j.nama_jurusan, p.foto_profil
                FROM pengurus_osis po
                JOIN siswa s ON po.id_siswa = s.id
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                LEFT JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id 
                    AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                LEFT JOIN kelas k ON rks.id_kelas = k.id
                ORDER BY 
                    CASE 
                        WHEN po.jabatan LIKE '%Ketua OSIS%' AND po.jabatan NOT LIKE '%Wakil%' THEN 1
                        WHEN po.jabatan LIKE '%Wakil%' THEN 2
                        WHEN po.jabatan LIKE '%Sekretaris 1%' THEN 3
                        WHEN po.jabatan LIKE '%Sekretaris 2%' THEN 4
                        WHEN po.jabatan LIKE '%Bendahara 1%' THEN 5
                        WHEN po.jabatan LIKE '%Bendahara 2%' THEN 6
                        WHEN po.jabatan LIKE '%Sekbid%' THEN 7
                        ELSE 8
                    END ASC, po.id ASC";
        return $this->fetchAll($sql);
    }

    public function tambahPengurus(int $idSiswa, string $jabatan, string $periode = '2026/2027'): bool {
        $sql = "INSERT INTO pengurus_osis (id_siswa, jabatan, periode, status_aktif) 
                VALUES (:id_siswa, :jabatan, :periode, 1)";
        return $this->query($sql, [
            ':id_siswa' => $idSiswa,
            ':jabatan'  => $jabatan,
            ':periode'  => $periode
        ])->rowCount() > 0;
    }

    public function perbaruiJabatan(int $id, string $jabatan): bool {
        $sql = "UPDATE pengurus_osis SET jabatan = :jabatan WHERE id = :id";
        return $this->query($sql, [':jabatan' => $jabatan, ':id' => $id])->rowCount() > 0;
    }

    public function hapusPengurus(int $id): bool {
        $sql = "DELETE FROM pengurus_osis WHERE id = :id";
        return $this->query($sql, [':id' => $id])->rowCount() > 0;
    }
}

