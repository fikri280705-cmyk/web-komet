<?php
/**
 * Model Guru (Master Tenaga Pendidik & Penugasan)
 */

namespace App\Models;

use App\Core\Model;

class Guru extends Model {
    public function ambilSemua(): array {
        $sql = "SELECT g.*, p.nomor_induk, p.foto_profil, p.id_peran, r.kode_peran, r.nama_peran, 
                       j.kode_jurusan, j.nama_jurusan, pwk.nama_kelas_wali
                FROM guru g
                LEFT JOIN pengguna p ON g.id_pengguna = p.id
                LEFT JOIN peran r ON p.id_peran = r.id
                LEFT JOIN jurusan j ON g.id_jurusan = j.id
                LEFT JOIN (
                    SELECT pwk.id_guru,
                           GROUP_CONCAT(
                               DISTINCT k.nama_kelas
                               ORDER BY k.tingkat ASC, k.nama_kelas ASC
                               SEPARATOR ', '
                           ) AS nama_kelas_wali
                    FROM penugasan_wali_kelas pwk
                    JOIN kelas k ON pwk.id_kelas = k.id
                    WHERE pwk.apakah_aktif = 1
                      AND pwk.id_tahun_ajaran = (
                          SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                      )
                    GROUP BY pwk.id_guru
                ) pwk ON pwk.id_guru = g.id
                WHERE g.apakah_aktif = 1
                ORDER BY g.id ASC";
        return $this->fetchAll($sql);
    }

    public function ambilBerdasarkanJurusan(int $idJurusan): array {
        $sql = "SELECT g.*, p.nomor_induk, p.foto_profil, r.kode_peran, r.nama_peran,
                       pwk.nama_kelas_wali
                FROM guru g
                LEFT JOIN pengguna p ON g.id_pengguna = p.id
                LEFT JOIN peran r ON p.id_peran = r.id
                LEFT JOIN (
                    SELECT pwk.id_guru,
                           GROUP_CONCAT(
                               DISTINCT k.nama_kelas
                               ORDER BY k.tingkat ASC, k.nama_kelas ASC
                               SEPARATOR ', '
                           ) AS nama_kelas_wali
                    FROM penugasan_wali_kelas pwk
                    JOIN kelas k ON pwk.id_kelas = k.id
                    WHERE pwk.apakah_aktif = 1
                      AND pwk.id_tahun_ajaran = (
                          SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                      )
                      AND k.id_jurusan = :id_kelas_jurusan
                    GROUP BY pwk.id_guru
                ) pwk ON pwk.id_guru = g.id
                WHERE (
                    g.id_jurusan = :id_jurusan
                    OR g.id_jurusan IS NULL
                    OR EXISTS (
                        SELECT 1
                        FROM penugasan_wali_kelas pwk_guru
                        JOIN kelas kelas_guru ON pwk_guru.id_kelas = kelas_guru.id
                        WHERE pwk_guru.id_guru = g.id
                          AND pwk_guru.apakah_aktif = 1
                          AND pwk_guru.id_tahun_ajaran = (
                              SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1
                          )
                          AND kelas_guru.id_jurusan = :id_jurusan_penugasan
                    )
                )
                  AND g.apakah_aktif = 1
                ORDER BY g.nama_lengkap ASC";
        return $this->fetchAll($sql, [
            ':id_jurusan' => $idJurusan,
            ':id_jurusan_penugasan' => $idJurusan,
            ':id_kelas_jurusan' => $idJurusan
        ]);
    }

    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT g.*, p.nomor_induk, p.foto_profil, p.id_peran, r.kode_peran, r.nama_peran,
                       j.kode_jurusan, j.nama_jurusan
                FROM guru g
                LEFT JOIN pengguna p ON g.id_pengguna = p.id
                LEFT JOIN peran r ON p.id_peran = r.id
                LEFT JOIN jurusan j ON g.id_jurusan = j.id
                WHERE g.id = :id
                LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    public function cariBerdasarkanIdPengguna(int $idPengguna): ?array {
        $sql = "SELECT g.*, p.nomor_induk, p.foto_profil, r.kode_peran, r.nama_peran,
                       j.kode_jurusan, j.nama_jurusan
                FROM guru g
                JOIN pengguna p ON g.id_pengguna = p.id
                JOIN peran r ON p.id_peran = r.id
                LEFT JOIN jurusan j ON g.id_jurusan = j.id
                WHERE g.id_pengguna = :id_pengguna
                LIMIT 1";
        return $this->fetchOne($sql, [':id_pengguna' => $idPengguna]);
    }

    public function hitungTotal(): int {
        return (int) $this->fetchColumn("SELECT COUNT(*) FROM guru WHERE apakah_aktif = 1");
    }

    public function perbaruiData(int $idGuru, array $data): bool {
        $sql = "UPDATE guru SET 
                    nama_lengkap = :nama,
                    nuptk = :nuptk,
                    nip = :nip,
                    jenis_kelamin = :jk,
                    tempat_lahir = :tempat_lahir,
                    tanggal_lahir = :tanggal_lahir,
                    agama = :agama,
                    nomor_telepon = :telp,
                    email = :email,
                    alamat = :alamat,
                    provinsi = :provinsi,
                    kabupaten = :kabupaten,
                    kode_pos = :kode_pos,
                    pendidikan_terakhir = :pendidikan,
                    npwp = :npwp,
                    id_jurusan = :id_jurusan,
                    mapel_utama = :mapel_utama,
                    jabatan_tugas = :jabatan
                WHERE id = :id";
        
        $params = [
            ':nama'         => $data['nama_lengkap'],
            ':nuptk'        => $data['nuptk'],
            ':nip'          => $data['nip'] ?: null,
            ':jk'           => $data['jenis_kelamin'] ?? 'L',
            ':tempat_lahir' => $data['tempat_lahir'] ?? 'Tangerang',
            ':tanggal_lahir'=> $data['tanggal_lahir'] ?: null,
            ':agama'        => $data['agama'] ?? 'Islam',
            ':telp'         => $data['nomor_telepon'] ?: null,
            ':email'        => $data['email'] ?: null,
            ':alamat'       => $data['alamat'] ?: null,
            ':provinsi'     => $data['provinsi'] ?? 'Banten',
            ':kabupaten'    => $data['kabupaten'] ?? 'Kabupaten Tangerang',
            ':kode_pos'     => $data['kode_pos'] ?? '15710',
            ':pendidikan'   => $data['pendidikan_terakhir'] ?: 'S1 Kependidikan',
            ':npwp'         => $data['npwp'] ?: null,
            ':id_jurusan'   => !empty($data['id_jurusan']) ? (int)$data['id_jurusan'] : null,
            ':mapel_utama'  => $data['mapel_utama'] ?: null,
            ':jabatan'      => $data['jabatan_tugas'] ?? 'Guru Pengajar',
            ':id'           => $idGuru
        ];

        $stmt = $this->query($sql, $params);

        // Sinkronisasi ke tabel pengguna jika ada id_pengguna
        $guru = $this->cariBerdasarkanId($idGuru);
        if ($guru && !empty($guru['id_pengguna'])) {
            $sqlPengguna = "UPDATE pengguna SET 
                                nama_lengkap = :nama,
                                nomor_induk = :nuptk,
                                email = :email,
                                nomor_telepon = :telp
                            WHERE id = :id_pengguna";
            $this->query($sqlPengguna, [
                ':nama'        => $data['nama_lengkap'],
                ':nuptk'       => $data['nuptk'],
                ':email'       => $data['email'] ?: null,
                ':telp'        => $data['nomor_telepon'] ?: null,
                ':id_pengguna' => $guru['id_pengguna']
            ]);
        }

        return true;
    }

    public function tetapkanWaliKelas(int $idGuru, int $idKelas, ?int $idTahunAjaran = null): bool {
        if ($idTahunAjaran === null) {
            $idTahunAjaran = (int)$this->fetchColumn(
                "SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1"
            );
        }
        if ($idTahunAjaran <= 0) {
            return false;
        }

        $penugasanLain = $this->fetchColumn(
            "SELECT id
             FROM penugasan_wali_kelas
             WHERE id_guru = :id_guru
               AND id_tahun_ajaran = :id_ta
               AND apakah_aktif = 1
               AND id_kelas <> :id_kelas
             LIMIT 1",
            [
                ':id_guru' => $idGuru,
                ':id_ta' => $idTahunAjaran,
                ':id_kelas' => $idKelas
            ]
        );
        if ($penugasanLain !== false) {
            return false;
        }

        $sql = "INSERT INTO penugasan_wali_kelas (id_guru, id_kelas, id_tahun_ajaran, apakah_aktif, tanggal_mulai_tugas)
                VALUES (:id_guru, :id_kelas, :id_ta, 1, CURDATE())";
        $sql .= " ON DUPLICATE KEY UPDATE
                    id_guru = VALUES(id_guru),
                    apakah_aktif = 1,
                    tanggal_mulai_tugas = CURDATE()";

        $this->query($sql, [
            ':id_guru' => $idGuru,
            ':id_kelas'=> $idKelas,
            ':id_ta'   => $idTahunAjaran
        ]);
        return true;
    }

    public function tetapkanPeranGuru(int $idGuru, string $kodePeran): bool {
        $guru = $this->cariBerdasarkanId($idGuru);
        if (!$guru || empty($guru['id_pengguna'])) return false;

        $idPeran = (int)$this->fetchColumn("SELECT id FROM peran WHERE kode_peran = :kode", [':kode' => $kodePeran]);
        if (!$idPeran) return false;

        $this->query("UPDATE pengguna SET id_peran = :id_peran WHERE id = :id_pengguna", [
            ':id_peran'    => $idPeran,
            ':id_pengguna' => $guru['id_pengguna']
        ]);
        return true;
    }

    public function nuptkSudahAda(string $nuptk, ?int $kecualiId = null): bool {
        $sql = "SELECT id FROM guru WHERE nuptk = :nuptk";
        $params = [':nuptk' => $nuptk];
        if ($kecualiId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $kecualiId;
        }
        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params) !== null;
    }

    public function buatBaru(array $data, int $idPengguna): int {
        $this->query(
            "INSERT INTO guru (
                id_pengguna, nuptk, nip, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir,
                agama, nomor_telepon, email, alamat, provinsi, kabupaten, kode_pos,
                pendidikan_terakhir, npwp, id_jurusan, mapel_utama, jabatan_tugas, apakah_aktif
             ) VALUES (
                :id_pengguna, :nuptk, :nip, :nama, :jk, :tempat_lahir, :tanggal_lahir,
                :agama, :telp, :email, :alamat, :provinsi, :kabupaten, :kode_pos,
                :pendidikan, :npwp, :id_jurusan, :mapel, :jabatan, 1
             )",
            [
                ':id_pengguna'  => $idPengguna,
                ':nuptk'        => $data['nuptk'],
                ':nip'          => $data['nip'] ?: null,
                ':nama'         => $data['nama_lengkap'],
                ':jk'           => $data['jenis_kelamin'] ?? 'L',
                ':tempat_lahir' => $data['tempat_lahir'] ?: 'Tangerang',
                ':tanggal_lahir'=> $data['tanggal_lahir'] ?: null,
                ':agama'        => $data['agama'] ?: 'Islam',
                ':telp'         => $data['nomor_telepon'] ?: null,
                ':email'        => $data['email'] ?: null,
                ':alamat'       => $data['alamat'] ?: null,
                ':provinsi'     => $data['provinsi'] ?: 'Banten',
                ':kabupaten'    => $data['kabupaten'] ?: 'Kabupaten Tangerang',
                ':kode_pos'     => $data['kode_pos'] ?: '15710',
                ':pendidikan'   => $data['pendidikan_terakhir'] ?: 'S1 Kependidikan',
                ':npwp'         => $data['npwp'] ?: null,
                ':id_jurusan'   => !empty($data['id_jurusan']) ? (int)$data['id_jurusan'] : null,
                ':mapel'        => $data['mapel_utama'] ?: null,
                ':jabatan'      => $data['jabatan_tugas'] ?: 'Guru Pengajar',
            ]
        );

        return $this->lastInsertId();
    }

    public function hitungPenugasanMengajar(int $idGuru): int {
        return (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM penugasan_guru WHERE id_guru = :id",
            [':id' => $idGuru]
        );
    }

    /**
     * Hapus guru. Jika masih punya penugasan mengajar, lakukan soft-delete.
     * @return array{status:string,id_pengguna:?int}
     */
    public function hapus(int $idGuru): array {
        $guru = $this->cariBerdasarkanId($idGuru);
        if (!$guru) {
            return ['status' => 'tidak_ditemukan', 'id_pengguna' => null];
        }

        $idPengguna = !empty($guru['id_pengguna']) ? (int)$guru['id_pengguna'] : null;

        if ($this->hitungPenugasanMengajar($idGuru) > 0) {
            $this->query("UPDATE guru SET apakah_aktif = 0 WHERE id = :id", [':id' => $idGuru]);
            return ['status' => 'dinonaktifkan', 'id_pengguna' => $idPengguna];
        }

        $this->query("DELETE FROM penugasan_wali_kelas WHERE id_guru = :id", [':id' => $idGuru]);
        $this->query("UPDATE jurusan SET id_guru_kaprog = NULL WHERE id_guru_kaprog = :id", [':id' => $idGuru]);
        $this->query("DELETE FROM guru WHERE id = :id", [':id' => $idGuru]);

        return ['status' => 'dihapus', 'id_pengguna' => $idPengguna];
    }
}
