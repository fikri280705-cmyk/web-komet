<?php
/**
 * Model Siswa (Data Induk & Riwayat Kelas)
 */

namespace App\Models;

use App\Core\Model;

class Siswa extends Model {
    public function ambilSemua(?int $idJurusan = null, ?int $idKelas = null, int $limit = 500, int $offset = 0): array {
        $sql = "SELECT s.*, j.nama_jurusan, j.kode_jurusan, k.nama_kelas, k.id AS id_kelas_aktif,
                       p.foto_profil, o.jabatan AS jabatan_osis
                FROM siswa s
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                LEFT JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id 
                    AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                LEFT JOIN kelas k ON rks.id_kelas = k.id
                LEFT JOIN pengurus_osis o ON o.id_siswa = s.id AND o.status_aktif = 1
                ";

        $filters = [];
        $params = [];
        if ($idJurusan !== null) {
            $filters[] = 's.id_jurusan = :id_jurusan';
            $params[':id_jurusan'] = $idJurusan;
        }
        if ($idKelas !== null) {
            $filters[] = 'rks.id_kelas = :id_kelas';
            $params[':id_kelas'] = $idKelas;
        }
        if ($filters) {
            $sql .= 'WHERE ' . implode(' AND ', $filters) . ' ';
        }
        $sql .= 'ORDER BY s.nis ASC LIMIT :limit OFFSET :offset';
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function ambilBerdasarkanJurusan(int $idJurusan): array {
        $sql = "SELECT s.*, j.nama_jurusan, j.kode_jurusan, k.nama_kelas, p.foto_profil
                FROM siswa s
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                LEFT JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id 
                    AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                LEFT JOIN kelas k ON rks.id_kelas = k.id
                WHERE s.id_jurusan = :id_jurusan
                ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC";
        return $this->fetchAll($sql, [':id_jurusan' => $idJurusan]);
    }

    public function ambilBerdasarkanKelas(int $idKelas, ?int $idTahunAjaran = null): array {
        $sqlTahun = $idTahunAjaran 
            ? "rks.id_tahun_ajaran = :id_tahun" 
            : "rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)";

        $sql = "SELECT s.*, j.nama_jurusan, k.nama_kelas, rks.status_penempatan, p.foto_profil
                FROM riwayat_kelas_siswa rks
                JOIN siswa s ON rks.id_siswa = s.id
                JOIN kelas k ON rks.id_kelas = k.id
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                WHERE rks.id_kelas = :id_kelas AND {$sqlTahun}
                ORDER BY s.nama_lengkap ASC";

        $params = [':id_kelas' => $idKelas];
        if ($idTahunAjaran) {
            $params[':id_tahun'] = $idTahunAjaran;
        }

        return $this->fetchAll($sql, $params);
    }

    public function ambilRiwayatTahunAjaran(int $idSiswa): array {
        return $this->fetchAll(
            "SELECT ta.id, ta.kode_tahun, ta.nama_tahun, ta.apakah_aktif,
                    ta.tanggal_mulai, ta.tanggal_selesai
             FROM riwayat_kelas_siswa rks
             JOIN tahun_ajaran ta ON ta.id = rks.id_tahun_ajaran
             WHERE rks.id_siswa = :id_siswa
             ORDER BY ta.tanggal_mulai DESC",
            [':id_siswa' => $idSiswa]
        );
    }

    public function ambilKelasPadaTahun(int $idSiswa, int $idTahunAjaran): ?array {
        return $this->fetchOne(
            "SELECT k.id, k.nama_kelas, k.tingkat, j.kode_jurusan, j.nama_jurusan,
                    rks.status_penempatan
             FROM riwayat_kelas_siswa rks
             JOIN kelas k ON k.id = rks.id_kelas
             JOIN jurusan j ON j.id = k.id_jurusan
             WHERE rks.id_siswa = :id_siswa
               AND rks.id_tahun_ajaran = :id_tahun",
            [':id_siswa' => $idSiswa, ':id_tahun' => $idTahunAjaran]
        );
    }

    public function cariBerdasarkanIdPengguna(int $idPengguna): ?array {
        $sql = "SELECT s.*, j.nama_jurusan, j.kode_jurusan, k.nama_kelas, k.id AS id_kelas_aktif,
                       p.foto_profil, p.email AS email_pengguna, o.jabatan AS jabatan_osis
                FROM siswa s
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                LEFT JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id 
                    AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                LEFT JOIN kelas k ON rks.id_kelas = k.id
                LEFT JOIN pengurus_osis o ON o.id_siswa = s.id AND o.status_aktif = 1
                WHERE s.id_pengguna = :id_pengguna
                LIMIT 1";
        return $this->fetchOne($sql, [':id_pengguna' => $idPengguna]);
    }

    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT s.*, j.nama_jurusan, j.kode_jurusan, k.nama_kelas, k.id AS id_kelas_aktif,
                       p.foto_profil, o.jabatan AS jabatan_osis
                FROM siswa s
                JOIN jurusan j ON s.id_jurusan = j.id
                LEFT JOIN pengguna p ON s.id_pengguna = p.id
                LEFT JOIN riwayat_kelas_siswa rks ON rks.id_siswa = s.id 
                    AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                LEFT JOIN kelas k ON rks.id_kelas = k.id
                LEFT JOIN pengurus_osis o ON o.id_siswa = s.id AND o.status_aktif = 1
                WHERE s.id = :id
                LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    public function hitungTotal(): int {
        return (int) $this->fetchColumn("SELECT COUNT(*) FROM siswa WHERE status_siswa = 'aktif'");
    }

    public function perbaruiData(int $idSiswa, array $data): bool {
        $sql = "UPDATE siswa SET 
                    nama_lengkap = :nama,
                    nis = :nis,
                    nisn = :nisn,
                    jenis_kelamin = :jk,
                    tempat_lahir = :tempat_lahir,
                    tanggal_lahir = :tanggal_lahir,
                    agama = :agama,
                    alamat = :alamat,
                    provinsi = :provinsi,
                    kabupaten = :kabupaten,
                    kode_pos = :kode_pos,
                    nomor_telepon = :telp,
                    nama_ayah = :ayah,
                    nama_ibu = :ibu,
                    pekerjaan_ortu = :pekerjaan,
                    telepon_orang_tua = :telp_ortu,
                    id_jurusan = :id_jurusan,
                    status_siswa = :status
                WHERE id = :id";
        
        $this->query($sql, [
            ':nama'          => $data['nama_lengkap'],
            ':nis'           => $data['nis'],
            ':nisn'          => $data['nisn'] ?: null,
            ':jk'            => $data['jenis_kelamin'] ?? 'L',
            ':tempat_lahir'  => $data['tempat_lahir'] ?? 'Tangerang',
            ':tanggal_lahir' => $data['tanggal_lahir'] ?: null,
            ':agama'         => $data['agama'] ?? 'Islam',
            ':alamat'        => $data['alamat'] ?: null,
            ':provinsi'      => $data['provinsi'] ?? 'Banten',
            ':kabupaten'     => $data['kabupaten'] ?? 'Kabupaten Tangerang',
            ':kode_pos'      => $data['kode_pos'] ?? '15710',
            ':telp'          => $data['nomor_telepon'] ?: null,
            ':ayah'          => $data['nama_ayah'] ?: null,
            ':ibu'           => $data['nama_ibu'] ?: null,
            ':pekerjaan'     => $data['pekerjaan_ortu'] ?: null,
            ':telp_ortu'     => $data['telepon_orang_tua'] ?: null,
            ':id_jurusan'    => (int)$data['id_jurusan'],
            ':status'        => $data['status_siswa'] ?? 'aktif',
            ':id'            => $idSiswa
        ]);

        // Sinkronisasi kelas jika ada id_kelas
        if (!empty($data['id_kelas'])) {
            $sqlKelas = "INSERT INTO riwayat_kelas_siswa (id_siswa, id_kelas, id_tahun_ajaran, status_penempatan)
                         VALUES (:id_siswa, :id_kelas, (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1), 'aktif')
                         ON DUPLICATE KEY UPDATE id_kelas = :id_kelas";
            $this->query($sqlKelas, [
                ':id_siswa' => $idSiswa,
                ':id_kelas' => (int)$data['id_kelas']
            ]);
        }

        // Sinkronisasi nama & nomor_induk di pengguna jika terhubung
        $siswa = $this->cariBerdasarkanId($idSiswa);
        if ($siswa && !empty($siswa['id_pengguna'])) {
            $sqlPengguna = "UPDATE pengguna SET 
                                nama_lengkap = :nama,
                                nomor_induk = :nis,
                                nomor_telepon = :telp
                            WHERE id = :id_pengguna";
            $this->query($sqlPengguna, [
                ':nama'        => $data['nama_lengkap'],
                ':nis'         => $data['nis'],
                ':telp'        => $data['nomor_telepon'] ?: null,
                ':id_pengguna' => $siswa['id_pengguna']
            ]);
        }

        return true;
    }

    public function nisSudahAda(string $nis, ?int $kecualiId = null): bool {
        $sql = "SELECT id FROM siswa WHERE nis = :nis";
        $params = [':nis' => $nis];
        if ($kecualiId !== null) {
            $sql .= " AND id <> :id";
            $params[':id'] = $kecualiId;
        }
        $sql .= " LIMIT 1";
        return $this->fetchOne($sql, $params) !== null;
    }

    public function buatBaru(array $data, int $idPengguna): int {
        $this->query(
            "INSERT INTO siswa (
                id_pengguna, nis, nisn, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir,
                agama, alamat, provinsi, kabupaten, kode_pos, nomor_telepon, nama_ayah, nama_ibu,
                pekerjaan_ortu, telepon_orang_tua, id_jurusan, tahun_masuk, foto, status_siswa
             ) VALUES (
                :id_pengguna, :nis, :nisn, :nama, :jk, :tempat_lahir, :tanggal_lahir,
                :agama, :alamat, :provinsi, :kabupaten, :kode_pos, :telp, :ayah, :ibu,
                :pekerjaan, :telp_ortu, :id_jurusan, :tahun_masuk, 'foto_siswa_default.png', :status
             )",
            [
                ':id_pengguna'   => $idPengguna,
                ':nis'           => $data['nis'],
                ':nisn'          => $data['nisn'] ?: null,
                ':nama'          => $data['nama_lengkap'],
                ':jk'            => $data['jenis_kelamin'] ?? 'L',
                ':tempat_lahir'  => $data['tempat_lahir'] ?: 'Tangerang',
                ':tanggal_lahir' => $data['tanggal_lahir'] ?: null,
                ':agama'         => $data['agama'] ?: 'Islam',
                ':alamat'        => $data['alamat'] ?: null,
                ':provinsi'      => $data['provinsi'] ?: 'Banten',
                ':kabupaten'     => $data['kabupaten'] ?: 'Kabupaten Tangerang',
                ':kode_pos'      => $data['kode_pos'] ?: '15710',
                ':telp'          => $data['nomor_telepon'] ?: null,
                ':ayah'          => $data['nama_ayah'] ?: null,
                ':ibu'           => $data['nama_ibu'] ?: null,
                ':pekerjaan'     => $data['pekerjaan_ortu'] ?: null,
                ':telp_ortu'     => $data['telepon_orang_tua'] ?: null,
                ':id_jurusan'    => (int)$data['id_jurusan'],
                ':tahun_masuk'   => (int)$data['tahun_masuk'],
                ':status'        => $data['status_siswa'] ?? 'aktif',
            ]
        );

        $idSiswa = $this->lastInsertId();

        if (!empty($data['id_kelas'])) {
            $this->query(
                "INSERT INTO riwayat_kelas_siswa (id_siswa, id_kelas, id_tahun_ajaran, status_penempatan)
                 VALUES (
                    :id_siswa,
                    :id_kelas,
                    (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1),
                    'aktif'
                 )",
                [
                    ':id_siswa' => $idSiswa,
                    ':id_kelas' => (int)$data['id_kelas'],
                ]
            );
        }

        return $idSiswa;
    }

    public function hapus(int $idSiswa): ?int {
        $siswa = $this->cariBerdasarkanId($idSiswa);
        if (!$siswa) {
            return null;
        }

        $idPengguna = !empty($siswa['id_pengguna']) ? (int)$siswa['id_pengguna'] : null;
        $this->query("DELETE FROM siswa WHERE id = :id", [':id' => $idSiswa]);
        return $idPengguna;
    }
}
