<?php
/**
 * Model Nilai Siswa (Pengelolaan Draf & Finalisasi Rapor) serta Rekap Kehadiran
 */

namespace App\Models;

use App\Core\Model;

class Nilai extends Model {
    /**
     * Ambil nilai siswa untuk portal siswa (HANYA status final)
     */
    public function ambilNilaiFinalSiswa(int $idSiswa, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "ns.id_semester = :id_semester" 
            : "ns.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT ns.*, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru_pengajar,
                       sem.jenis_semester, ta.nama_tahun, sem.id AS id_semester
                FROM nilai_siswa ns
                JOIN penugasan_guru pg ON ns.id_penugasan_guru = pg.id
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                JOIN semester sem ON ns.id_semester = sem.id
                JOIN tahun_ajaran ta ON sem.id_tahun_ajaran = ta.id
                WHERE ns.id_siswa = :id_siswa 
                  AND ns.status_nilai = 'final'
                  AND {$sqlSemester}
                ORDER BY mp.kelompok_mapel ASC, mp.nama_mapel ASC";

        $params = [':id_siswa' => $idSiswa];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Ambil data rekap kehadiran siswa (Sakit, Izin, Alfa)
     */
    public function ambilKehadiranSiswa(int $idSiswa, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? ":id_semester" 
            : "(SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT ks.*, sem.jenis_semester, ta.nama_tahun 
                FROM kehadiran_siswa ks
                JOIN semester sem ON ks.id_semester = sem.id
                JOIN tahun_ajaran ta ON sem.id_tahun_ajaran = ta.id
                WHERE ks.id_siswa = :id_siswa AND ks.id_semester = {$sqlSemester}
                LIMIT 1";

        $params = [':id_siswa' => $idSiswa];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        $res = $this->fetchOne($sql, $params);
        return $res ?: ['sakit' => null, 'izin' => null, 'alfa' => null, 'catatan' => null];
    }

    /**
     * Ambil rekap kehadiran seluruh siswa dalam satu kelas (untuk Wali Kelas)
     */
    public function ambilKehadiranKelas(int $idKelas, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? ":id_semester" 
            : "(SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap, s.jenis_kelamin,
                       ks.id AS id_rekap, ks.sakit, ks.izin, ks.alfa, ks.catatan
                FROM riwayat_kelas_siswa rks
                JOIN siswa s ON rks.id_siswa = s.id
                LEFT JOIN kehadiran_siswa ks ON ks.id_siswa = s.id AND ks.id_semester = {$sqlSemester}
                WHERE rks.id_kelas = :id_kelas
                  AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                  AND rks.status_penempatan = 'aktif'
                ORDER BY s.nama_lengkap ASC";

        $params = [':id_kelas' => $idKelas];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    public function ambilIdSemesterAktif(): ?int {
        $idSemester = $this->fetchColumn(
            "SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1"
        );

        return $idSemester === false ? null : (int)$idSemester;
    }

    /**
     * Simpan rekap awal kelas tanpa mengubah data yang sudah ada.
     *
     * @param array<int, array{sakit:int, izin:int, alfa:int, catatan:string}> $absensi
     */
    public function simpanKehadiranAwalKelas(int $idKelas, int $idSemester, array $absensi): int {
        if ($absensi === []) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $jumlahTersimpan = 0;
            foreach ($absensi as $idSiswa => $row) {
                $jumlahTersimpan += $this->query(
                    "INSERT INTO kehadiran_siswa
                        (id_siswa, id_semester, sakit, izin, alfa, catatan, diperbarui_pada)
                     SELECT :id_siswa, :id_semester, :sakit, :izin, :alfa, :catatan, NOW()
                     WHERE NOT EXISTS (
                         SELECT 1
                         FROM kehadiran_siswa
                         WHERE id_siswa = :id_siswa_existing
                           AND id_semester = :id_semester_existing
                     )
                       AND EXISTS (
                         SELECT 1
                         FROM riwayat_kelas_siswa rks
                         JOIN tahun_ajaran ta ON ta.id = rks.id_tahun_ajaran
                         WHERE rks.id_siswa = :id_siswa_kelas
                           AND rks.id_kelas = :id_kelas
                           AND rks.status_penempatan = 'aktif'
                           AND ta.apakah_aktif = 1
                     )",
                    [
                        ':id_siswa' => (int)$idSiswa,
                        ':id_semester' => $idSemester,
                        ':sakit' => $row['sakit'],
                        ':izin' => $row['izin'],
                        ':alfa' => $row['alfa'],
                        ':catatan' => $row['catatan'],
                        ':id_siswa_existing' => (int)$idSiswa,
                        ':id_semester_existing' => $idSemester,
                        ':id_siswa_kelas' => (int)$idSiswa,
                        ':id_kelas' => $idKelas
                    ]
                )->rowCount();
            }

            $this->db->commit();
            return $jumlahTersimpan;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function perbaruiKehadiranSiswa(
        int $idSiswa,
        int $idKelas,
        int $idSemester,
        int $sakit,
        int $izin,
        int $alfa,
        string $catatan
    ): bool {
        $this->db->beginTransaction();
        try {
            $rekapAda = $this->fetchOne(
                "SELECT ks.id
                 FROM kehadiran_siswa ks
                 WHERE ks.id_siswa = :id_siswa
                   AND ks.id_semester = :id_semester
                   AND EXISTS (
                       SELECT 1
                       FROM riwayat_kelas_siswa rks
                       JOIN tahun_ajaran ta ON ta.id = rks.id_tahun_ajaran
                       WHERE rks.id_siswa = ks.id_siswa
                         AND rks.id_kelas = :id_kelas
                         AND rks.status_penempatan = 'aktif'
                         AND ta.apakah_aktif = 1
                   )
                 FOR UPDATE",
                [
                    ':id_siswa' => $idSiswa,
                    ':id_semester' => $idSemester,
                    ':id_kelas' => $idKelas
                ]
            );
            if (!$rekapAda) {
                $this->db->rollBack();
                return false;
            }

            $this->query(
                "UPDATE kehadiran_siswa
                 SET sakit = :sakit,
                     izin = :izin,
                     alfa = :alfa,
                     catatan = :catatan,
                     diperbarui_pada = NOW()
                 WHERE id = :id_rekap",
                [
                    ':sakit' => $sakit,
                    ':izin' => $izin,
                    ':alfa' => $alfa,
                    ':catatan' => $catatan,
                    ':id_rekap' => (int)$rekapAda['id']
                ]
            );

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ambil daftar seluruh semester aktif & lampau untuk dropdown e-Rapor
     */
    public function ambilDaftarSemester(): array {
        $sql = "SELECT s.*, ta.nama_tahun 
                FROM semester s
                JOIN tahun_ajaran ta ON s.id_tahun_ajaran = ta.id
                ORDER BY ta.tanggal_mulai DESC, s.jenis_semester DESC";
        return $this->fetchAll($sql);
    }

    public function ambilSemesterRiwayatSiswa(int $idSiswa, int $idTahunAjaran): array {
        return $this->fetchAll(
            "SELECT sem.id, sem.id_tahun_ajaran, sem.jenis_semester, sem.apakah_aktif,
                    sem.tanggal_mulai, sem.tanggal_selesai, ta.nama_tahun
             FROM semester sem
             JOIN tahun_ajaran ta ON ta.id = sem.id_tahun_ajaran
             JOIN riwayat_kelas_siswa rks
               ON rks.id_siswa = :id_siswa AND rks.id_tahun_ajaran = ta.id
             WHERE sem.id_tahun_ajaran = :id_tahun
             ORDER BY sem.tanggal_mulai",
            [':id_siswa' => $idSiswa, ':id_tahun' => $idTahunAjaran]
        );
    }

    /**
     * Ambil nilai siswa dalam satu kelas per mapel (untuk Wali Kelas / Guru)
     */
    public function ambilNilaiRombel(int $idKelas, int $idPenugasanGuru, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? ":id_semester" 
            : "(SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT s.id AS id_siswa, s.nis, s.nama_lengkap,
                       ns.id AS id_nilai, ns.nilai_tugas, ns.nilai_uts, ns.nilai_uas, ns.nilai_praktik,
                       ns.nilai_akhir, ns.predikat_huruf, ns.status_nilai, ns.disahkan_pada
                FROM riwayat_kelas_siswa rks
                JOIN siswa s ON rks.id_siswa = s.id
                LEFT JOIN nilai_siswa ns ON ns.id_siswa = s.id 
                    AND ns.id_penugasan_guru = :id_penugasan 
                    AND ns.id_semester = {$sqlSemester}
                WHERE rks.id_kelas = :id_kelas
                  AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                  AND rks.status_penempatan = 'aktif'
                ORDER BY s.nama_lengkap ASC";

        $params = [
            ':id_kelas'     => $idKelas,
            ':id_penugasan' => $idPenugasanGuru
        ];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Ambil seluruh mata pelajaran yang diajarkan di kelas tertentu
     */
    public function ambilMapelKelas(int $idKelas, ?int $idSemester = null): array {
        $sqlSemester = $idSemester 
            ? "pg.id_semester = :id_semester" 
            : "pg.id_semester = (SELECT id FROM semester WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT pg.id AS id_penugasan_guru, mp.id AS id_mapel, mp.nama_mapel, mp.kode_mapel, g.nama_lengkap AS nama_guru
                FROM penugasan_guru pg
                JOIN mata_pelajaran mp ON pg.id_mapel = mp.id
                JOIN guru g ON pg.id_guru = g.id
                WHERE pg.id_kelas = :id_kelas AND {$sqlSemester}
                ORDER BY mp.nama_mapel ASC";

        $params = [':id_kelas' => $idKelas];
        if ($idSemester) {
            $params[':id_semester'] = $idSemester;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Simpan nilai awal kelas tanpa mengubah nilai yang sudah tersimpan.
     *
     * @param array<int, array{tugas:float, uts:float, uas:float, praktik:float}> $nilaiSiswa
     */
    public function simpanNilaiAwalKelas(
        int $idKelas,
        int $idPenugasanGuru,
        int $idSemester,
        array $nilaiSiswa,
        int $idGuruPengesah
    ): int {
        if ($nilaiSiswa === []) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $jumlahTersimpan = 0;
            foreach ($nilaiSiswa as $idSiswa => $nilai) {
                [$nilaiAkhir, $predikat] = $this->hitungNilai(
                    $nilai['tugas'],
                    $nilai['uts'],
                    $nilai['uas'],
                    $nilai['praktik']
                );
                $jumlahTersimpan += $this->query(
                    "INSERT INTO nilai_siswa
                        (id_siswa, id_penugasan_guru, id_semester, nilai_tugas, nilai_uts, nilai_uas, nilai_praktik,
                         nilai_akhir, predikat_huruf, status_nilai, disahkan_oleh, disahkan_pada, diperbarui_pada)
                     SELECT :id_siswa, :id_penugasan, :id_semester, :tugas, :uts, :uas, :praktik,
                            :akhir, :predikat, 'final', :pengesah, NOW(), NOW()
                     WHERE NOT EXISTS (
                         SELECT 1
                         FROM nilai_siswa
                         WHERE id_siswa = :id_siswa_existing
                           AND id_penugasan_guru = :id_penugasan_existing
                           AND id_semester = :id_semester_existing
                     )
                       AND EXISTS (
                         SELECT 1
                         FROM penugasan_guru pg
                         JOIN semester sem ON sem.id = pg.id_semester
                         WHERE pg.id = :id_penugasan_valid
                           AND pg.id_kelas = :id_kelas
                           AND pg.id_semester = :id_semester_valid
                           AND sem.apakah_aktif = 1
                       )
                       AND EXISTS (
                         SELECT 1
                         FROM riwayat_kelas_siswa rks
                         JOIN tahun_ajaran ta ON ta.id = rks.id_tahun_ajaran
                         WHERE rks.id_siswa = :id_siswa_kelas
                           AND rks.id_kelas = :id_kelas_roster
                           AND rks.status_penempatan = 'aktif'
                           AND ta.apakah_aktif = 1
                       )
                     ON DUPLICATE KEY UPDATE id = id",
                    [
                        ':id_siswa' => (int)$idSiswa,
                        ':id_penugasan' => $idPenugasanGuru,
                        ':id_semester' => $idSemester,
                        ':tugas' => $nilai['tugas'],
                        ':uts' => $nilai['uts'],
                        ':uas' => $nilai['uas'],
                        ':praktik' => $nilai['praktik'],
                        ':akhir' => $nilaiAkhir,
                        ':predikat' => $predikat,
                        ':pengesah' => $idGuruPengesah,
                        ':id_siswa_existing' => (int)$idSiswa,
                        ':id_penugasan_existing' => $idPenugasanGuru,
                        ':id_semester_existing' => $idSemester,
                        ':id_penugasan_valid' => $idPenugasanGuru,
                        ':id_kelas' => $idKelas,
                        ':id_semester_valid' => $idSemester,
                        ':id_siswa_kelas' => (int)$idSiswa,
                        ':id_kelas_roster' => $idKelas
                    ]
                )->rowCount();
            }

            $this->db->commit();
            return $jumlahTersimpan;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function perbaruiNilaiSiswa(
        int $idSiswa,
        int $idKelas,
        int $idPenugasanGuru,
        int $idSemester,
        float $tugas,
        float $uts,
        float $uas,
        float $praktik,
        int $idGuruPengesah
    ): bool {
        $this->db->beginTransaction();
        try {
            $nilaiAda = $this->fetchOne(
                "SELECT ns.id
                 FROM nilai_siswa ns
                 JOIN penugasan_guru pg ON pg.id = ns.id_penugasan_guru
                 JOIN semester sem ON sem.id = ns.id_semester
                 WHERE ns.id_siswa = :id_siswa
                   AND ns.id_penugasan_guru = :id_penugasan
                   AND ns.id_semester = :id_semester
                   AND pg.id_kelas = :id_kelas
                   AND sem.apakah_aktif = 1
                   AND EXISTS (
                       SELECT 1
                       FROM riwayat_kelas_siswa rks
                       JOIN tahun_ajaran ta ON ta.id = rks.id_tahun_ajaran
                       WHERE rks.id_siswa = ns.id_siswa
                         AND rks.id_kelas = :id_kelas_roster
                         AND rks.status_penempatan = 'aktif'
                         AND ta.apakah_aktif = 1
                   )
                 FOR UPDATE",
                [
                    ':id_siswa' => $idSiswa,
                    ':id_penugasan' => $idPenugasanGuru,
                    ':id_semester' => $idSemester,
                    ':id_kelas' => $idKelas,
                    ':id_kelas_roster' => $idKelas
                ]
            );
            if (!$nilaiAda) {
                $this->db->rollBack();
                return false;
            }

            [$nilaiAkhir, $predikat] = $this->hitungNilai($tugas, $uts, $uas, $praktik);
            $this->query(
                "UPDATE nilai_siswa
                 SET nilai_tugas = :tugas,
                     nilai_uts = :uts,
                     nilai_uas = :uas,
                     nilai_praktik = :praktik,
                     nilai_akhir = :akhir,
                     predikat_huruf = :predikat,
                     status_nilai = 'final',
                     disahkan_oleh = :pengesah,
                     disahkan_pada = NOW(),
                     diperbarui_pada = NOW()
                 WHERE id = :id_nilai",
                [
                    ':tugas' => $tugas,
                    ':uts' => $uts,
                    ':uas' => $uas,
                    ':praktik' => $praktik,
                    ':akhir' => $nilaiAkhir,
                    ':predikat' => $predikat,
                    ':pengesah' => $idGuruPengesah,
                    ':id_nilai' => (int)$nilaiAda['id']
                ]
            );

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return array{0: float, 1: string}
     */
    private function hitungNilai(float $tugas, float $uts, float $uas, float $praktik): array {
        $nilaiAkhir = round(($tugas * 0.20) + ($uts * 0.20) + ($uas * 0.20) + ($praktik * 0.40), 2);
        if ($nilaiAkhir >= 88) $predikat = 'A';
        elseif ($nilaiAkhir >= 78) $predikat = 'B';
        elseif ($nilaiAkhir >= 65) $predikat = 'C';
        elseif ($nilaiAkhir >= 50) $predikat = 'D';
        else $predikat = 'E';

        return [$nilaiAkhir, $predikat];
    }
}
