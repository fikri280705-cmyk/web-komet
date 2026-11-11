<?php
/**
 * Model Jadwal Pelajaran (KBM Mingguan & Validasi Anti-Bentrok)
 */

namespace App\Models;

use App\Core\Model;

class Jadwal extends Model {
    public function ambilSemua(?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "pg.id_semester = :id_semester" 
            : "pg.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT jp.*, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru, 
                       k.nama_kelas, j.kode_jurusan, j.nama_jurusan
                FROM jadwal_pelajaran jp
                JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                JOIN kelas k ON pg.id_kelas = k.id
                JOIN jurusan j ON k.id_jurusan = j.id
                WHERE {$sqlSemester}
                ORDER BY j.kode_jurusan ASC, k.tingkat ASC, k.nama_kelas ASC, 
                         FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), jp.jam_mulai ASC";

        $params = [];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    public function ambilBerdasarkanKelas(int $idKelas, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "pg.id_semester = :id_semester" 
            : "pg.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT jp.*, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru, k.nama_kelas
                FROM jadwal_pelajaran jp
                JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                JOIN kelas k ON pg.id_kelas = k.id
                WHERE pg.id_kelas = :id_kelas AND {$sqlSemester}
                ORDER BY FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), jp.jam_mulai ASC";

        $params = [':id_kelas' => $idKelas];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    public function ambilBerdasarkanJurusan(int $idJurusan, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "pg.id_semester = :id_semester" 
            : "pg.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT jp.*, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru, k.nama_kelas
                FROM jadwal_pelajaran jp
                JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                JOIN kelas k ON pg.id_kelas = k.id
                WHERE k.id_jurusan = :id_jurusan AND {$sqlSemester}
                ORDER BY k.tingkat ASC, k.nama_kelas ASC, FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), jp.jam_mulai ASC";

        $params = [':id_jurusan' => $idJurusan];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Jadwal Penggunaan Lab / Bengkel Spesifik Jurusan
     */
    public function ambilJadwalLabBerdasarkanJurusan(int $idJurusan, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "pg.id_semester = :id_semester" 
            : "pg.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT jp.*, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru, k.nama_kelas
                FROM jadwal_pelajaran jp
                JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                JOIN kelas k ON pg.id_kelas = k.id
                WHERE k.id_jurusan = :id_jurusan 
                  AND (jp.apakah_lab = 1 OR jp.ruangan LIKE 'Lab%' OR jp.ruangan LIKE 'Bengkel%')
                  AND {$sqlSemester}
                ORDER BY k.tingkat ASC, k.nama_kelas ASC, FIELD(jp.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), jp.jam_mulai ASC";

        $params = [':id_jurusan' => $idJurusan];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Algoritma Validasi Bentrok Jadwal (Guru, Kelas, Ruangan)
     */
    public function cekBentrok(int $idPenugasanGuru, string $hari, string $jamMulai, string $jamSelesai, string $ruangan, ?int $idJadwalKecualikan = null): ?string {
        $penugasan = $this->fetchOne(
            "SELECT id_guru, id_kelas, id_tahun_ajaran, id_semester
             FROM penugasan_guru
             WHERE id = :id",
            [':id' => $idPenugasanGuru]
        );
        if (!$penugasan) return "Penugasan guru tidak ditemukan.";

        $idGuru = $penugasan['id_guru'];
        $idKelas = $penugasan['id_kelas'];
        $idTahun = $penugasan['id_tahun_ajaran'];
        $idSemester = $penugasan['id_semester'];

        $sqlKecualikan = $idJadwalKecualikan ? "AND jp.id != :id_edit" : "";

        // 1. Cek Bentrok Guru
        $sqlGuru = "SELECT g.nama_lengkap, k.nama_kelas, jp.jam_mulai, jp.jam_selesai
                    FROM jadwal_pelajaran jp
                    JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                    JOIN guru g ON pg.id_guru = g.id
                    JOIN kelas k ON pg.id_kelas = k.id
                    WHERE pg.id_tahun_ajaran = :id_tahun
                      AND pg.id_semester = :id_semester
                      AND pg.id_guru = :id_guru
                      AND jp.hari = :hari
                      AND (jp.jam_mulai < :selesai AND jp.jam_selesai > :mulai)
                      {$sqlKecualikan}
                    LIMIT 1";
        $paramsGuru = [
            ':id_tahun' => $idTahun,
            ':id_semester' => $idSemester,
            ':id_guru'  => $idGuru,
            ':hari'     => $hari,
            ':mulai'    => $jamMulai,
            ':selesai'  => $jamSelesai,
        ];
        if ($idJadwalKecualikan) $paramsGuru[':id_edit'] = $idJadwalKecualikan;

        $bentrokGuru = $this->fetchOne($sqlGuru, $paramsGuru);
        if ($bentrokGuru) {
            return "Bentrok Guru: {$bentrokGuru['nama_lengkap']} sudah mengajar di kelas {$bentrokGuru['nama_kelas']} pada jam {$bentrokGuru['jam_mulai']} - {$bentrokGuru['jam_selesai']}.";
        }

        // 2. Cek Bentrok Kelas
        $sqlKelas = "SELECT k.nama_kelas, mp.nama_mapel, jp.jam_mulai, jp.jam_selesai
                     FROM jadwal_pelajaran jp
                     JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                     JOIN kelas k ON pg.id_kelas = k.id
                     JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                     WHERE pg.id_tahun_ajaran = :id_tahun
                       AND pg.id_semester = :id_semester
                       AND pg.id_kelas = :id_kelas
                       AND jp.hari = :hari
                       AND (jp.jam_mulai < :selesai AND jp.jam_selesai > :mulai)
                       {$sqlKecualikan}
                     LIMIT 1";
        $paramsKelas = [
            ':id_tahun' => $idTahun,
            ':id_semester' => $idSemester,
            ':id_kelas' => $idKelas,
            ':hari'     => $hari,
            ':mulai'    => $jamMulai,
            ':selesai'  => $jamSelesai,
        ];
        if ($idJadwalKecualikan) $paramsKelas[':id_edit'] = $idJadwalKecualikan;

        $bentrokKelas = $this->fetchOne($sqlKelas, $paramsKelas);
        if ($bentrokKelas) {
            return "Bentrok Kelas: {$bentrokKelas['nama_kelas']} sudah memiliki jadwal {$bentrokKelas['nama_mapel']} pada jam tersebut.";
        }

        // 3. Cek Bentrok Ruangan/Lab
        $sqlRuang = "SELECT jp.ruangan, k.nama_kelas, jp.jam_mulai, jp.jam_selesai
                     FROM jadwal_pelajaran jp
                     JOIN penugasan_guru pg ON jp.id_penugasan_guru = pg.id
                     JOIN kelas k ON pg.id_kelas = k.id
                     WHERE pg.id_tahun_ajaran = :id_tahun
                       AND pg.id_semester = :id_semester
                       AND jp.ruangan = :ruangan
                       AND jp.hari = :hari
                       AND (jp.jam_mulai < :selesai AND jp.jam_selesai > :mulai)
                       {$sqlKecualikan}
                     LIMIT 1";
        $paramsRuang = [
            ':id_tahun' => $idTahun,
            ':id_semester' => $idSemester,
            ':ruangan'  => $ruangan,
            ':hari'     => $hari,
            ':mulai'    => $jamMulai,
            ':selesai'  => $jamSelesai,
        ];
        if ($idJadwalKecualikan) $paramsRuang[':id_edit'] = $idJadwalKecualikan;

        $bentrokRuang = $this->fetchOne($sqlRuang, $paramsRuang);
        if ($bentrokRuang) {
            return "Bentrok Ruangan: {$bentrokRuang['ruangan']} sedang digunakan oleh kelas {$bentrokRuang['nama_kelas']}.";
        }

        return null;
    }

    /**
     * Simpan jadwal setelah memastikan guru, kelas, dan ruangan bebas pada waktu tersebut.
     * Mengunci tahun ajaran agar dua proses simpan bersamaan tidak lolos validasi sekaligus.
     */
    public function simpanJadwal(
        int $idPenugasanGuru,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        string $ruangan,
        bool $apakahLab = false,
        ?int $idJadwal = null
    ): ?string {
        $hariValid = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        if (!in_array($hari, $hariValid, true)) {
            return 'Hari jadwal harus Senin sampai Jumat.';
        }

        $keDetik = static function (string $waktu): ?int {
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $waktu)) {
                return null;
            }
            $bagian = array_map('intval', explode(':', $waktu));
            return ($bagian[0] * 3600) + ($bagian[1] * 60) + ($bagian[2] ?? 0);
        };
        $mulaiDetik = $keDetik($jamMulai);
        $selesaiDetik = $keDetik($jamSelesai);
        if ($mulaiDetik === null || $selesaiDetik === null || $mulaiDetik >= $selesaiDetik) {
            return 'Rentang waktu jadwal tidak valid.';
        }
        if (trim($ruangan) === '' || strlen($ruangan) > 50) {
            return 'Nama ruangan wajib diisi dan maksimal 50 karakter.';
        }

        $penugasan = $this->fetchOne(
            "SELECT id_tahun_ajaran, id_semester
             FROM penugasan_guru
             WHERE id = :id",
            [':id' => $idPenugasanGuru]
        );
        if (!$penugasan) {
            return 'Penugasan guru tidak ditemukan.';
        }

        $this->db->beginTransaction();
        try {
            $idTahunAjaran = (int)$penugasan['id_tahun_ajaran'];
            $tahunTerkunci = $this->fetchColumn(
                "SELECT id FROM tahun_ajaran WHERE id = :id FOR UPDATE",
                [':id' => $idTahunAjaran]
            );
            if (!$tahunTerkunci) {
                $this->db->rollBack();
                return 'Tahun ajaran penugasan tidak ditemukan.';
            }
            $semesterTerkunci = $this->fetchOne(
                "SELECT sem.id, sem.apakah_aktif
                 FROM semester sem
                 JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
                 WHERE sem.id = :id_semester AND sem.id_tahun_ajaran = :id_tahun
                   AND sem.apakah_aktif = 0
                   AND (
                       (ta.apakah_aktif = 0 AND ta.tanggal_mulai > (
                           SELECT tanggal_selesai FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                       ))
                       OR (
                           ta.apakah_aktif = 1
                           AND sem.jenis_semester = 'genap'
                           AND EXISTS (
                               SELECT 1 FROM semester aktif
                               WHERE aktif.id_tahun_ajaran = ta.id
                                 AND aktif.jenis_semester = 'ganjil'
                                 AND aktif.apakah_aktif = 1
                           )
                       )
                   )
                 FOR UPDATE",
                [
                    ':id_semester' => (int)$penugasan['id_semester'],
                    ':id_tahun' => $idTahunAjaran
                ]
            );
            if (!$semesterTerkunci) {
                $this->db->rollBack();
                return 'Jadwal hanya dapat disiapkan pada semester mendatang yang belum aktif.';
            }

            if ($idJadwal !== null) {
                $jadwalAda = $this->fetchOne(
                    "SELECT id
                     FROM jadwal_pelajaran
                     WHERE id = :id_jadwal
                       AND id_penugasan_guru = :id_penugasan",
                    [
                        ':id_jadwal' => $idJadwal,
                        ':id_penugasan' => $idPenugasanGuru
                    ]
                );
                if (!$jadwalAda) {
                    $this->db->rollBack();
                    return 'Jadwal yang akan diperbarui tidak ditemukan.';
                }
            }

            $pesanBentrok = $this->cekBentrok(
                $idPenugasanGuru,
                $hari,
                $jamMulai,
                $jamSelesai,
                $ruangan,
                $idJadwal
            );
            if ($pesanBentrok !== null) {
                $this->db->rollBack();
                return $pesanBentrok;
            }

            $params = [
                ':id_penugasan' => $idPenugasanGuru,
                ':hari' => $hari,
                ':mulai' => $jamMulai,
                ':selesai' => $jamSelesai,
                ':ruangan' => trim($ruangan),
                ':apakah_lab' => $apakahLab ? 1 : 0
            ];
            if ($idJadwal === null) {
                $this->query(
                    "INSERT INTO jadwal_pelajaran
                        (id_penugasan_guru, hari, jam_mulai, jam_selesai, ruangan, apakah_lab)
                     VALUES
                        (:id_penugasan, :hari, :mulai, :selesai, :ruangan, :apakah_lab)",
                    $params
                );
            } else {
                $params[':id_jadwal'] = $idJadwal;
                $this->query(
                    "UPDATE jadwal_pelajaran
                     SET hari = :hari,
                         jam_mulai = :mulai,
                         jam_selesai = :selesai,
                         ruangan = :ruangan,
                         apakah_lab = :apakah_lab
                     WHERE id = :id_jadwal
                       AND id_penugasan_guru = :id_penugasan",
                    $params
                );
            }

            $this->db->commit();
            return null;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
