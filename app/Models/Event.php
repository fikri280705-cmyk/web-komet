<?php
/**
 * Model Event Sekolah (Mading Digital, Unggah Poster, & Moderasi OSIS)
 */

namespace App\Models;

use App\Core\Model;

class Event extends Model {
    public function ambilSemua(?string $status = null): array {
        $sqlWhere = $status ? "WHERE es.status = :status" : "";
        $sql = "SELECT es.*, ke.nama_kategori, p.nama_lengkap AS nama_pembuat, 
                       pa.nama_lengkap AS nama_penyetuju
                FROM event_sekolah es
                JOIN kategori_event ke ON es.id_kategori = ke.id
                JOIN pengguna p ON es.dibuat_oleh_id_pengguna = p.id
                LEFT JOIN pengguna pa ON es.disetujui_oleh_id_pengguna = pa.id
                {$sqlWhere}
                ORDER BY es.dibuat_pada DESC";

        $params = [];
        if ($status) {
            $params[':status'] = $status;
        }

        return $this->fetchAll($sql, $params);
    }

    public function ambilKategori(): array {
        return $this->fetchAll("SELECT * FROM kategori_event ORDER BY id ASC");
    }

    /**
     * Ambil event yang sudah dipublikasikan (untuk Siswa & Publik)
     */
    public function ambilEventPublik(?int $idKategori = null): array {
        $sqlKategori = $idKategori ? "AND es.id_kategori = :id_kategori" : "";
        $sql = "SELECT es.*, ke.nama_kategori, p.nama_lengkap AS nama_pembuat
                FROM event_sekolah es
                JOIN kategori_event ke ON es.id_kategori = ke.id
                JOIN pengguna p ON es.dibuat_oleh_id_pengguna = p.id
                WHERE es.status = 'dipublikasikan' {$sqlKategori}
                ORDER BY es.waktu_mulai ASC";

        $params = [];
        if ($idKategori) {
            $params[':id_kategori'] = $idKategori;
        }

        return $this->fetchAll($sql, $params);
    }

    /**
     * Ambil event yang menunggu persetujuan (untuk Dasbor Kesiswaan)
     */
    public function ambilEventMenungguPersetujuan(): array {
        $sql = "SELECT es.*, ke.nama_kategori, p.nama_lengkap AS nama_pengusul
                FROM event_sekolah es
                JOIN kategori_event ke ON es.id_kategori = ke.id
                JOIN pengguna p ON es.dibuat_oleh_id_pengguna = p.id
                WHERE es.status = 'menunggu_persetujuan'
                ORDER BY es.dibuat_pada ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Ambil event milik pengurus OSIS tertentu
     */
    public function ambilEventSaya(int $idPengguna): array {
        $sql = "SELECT es.*, ke.nama_kategori
                FROM event_sekolah es
                JOIN kategori_event ke ON es.id_kategori = ke.id
                WHERE es.dibuat_oleh_id_pengguna = :id_pengguna
                ORDER BY es.dibuat_pada DESC";
        return $this->fetchAll($sql, [':id_pengguna' => $idPengguna]);
    }

    public function cariBerdasarkanId(int $id): ?array {
        $sql = "SELECT es.*, ke.nama_kategori, p.nama_lengkap AS nama_pembuat, 
                       pa.nama_lengkap AS nama_penyetuju
                FROM event_sekolah es
                JOIN kategori_event ke ON es.id_kategori = ke.id
                JOIN pengguna p ON es.dibuat_oleh_id_pengguna = p.id
                LEFT JOIN pengguna pa ON es.disetujui_oleh_id_pengguna = pa.id
                WHERE es.id = :id";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    /**
     * Tambah Event Baru (oleh OSIS / Kesiswaan)
     */
    public function buatEvent(
        string $judul,
        string $slug,
        int $idKategori,
        string $deskripsi,
        string $lokasi,
        string $waktuMulai,
        string $waktuSelesai,
        ?string $fotoPoster,
        string $penyelenggara,
        string $status,
        int $idPenggunaPembuat
    ): int {
        $sql = "INSERT INTO event_sekolah 
                (judul, slug, id_kategori, deskripsi, lokasi, waktu_mulai, waktu_selesai, foto_poster, penyelenggara, status, dibuat_oleh_id_pengguna, dibuat_pada)
                VALUES 
                (:judul, :slug, :id_kategori, :deskripsi, :lokasi, :waktu_mulai, :waktu_selesai, :foto_poster, :penyelenggara, :status, :pembuat, NOW())";

        $this->db->beginTransaction();
        try {
            $this->query($sql, [
                ':judul'        => $judul,
                ':slug'         => $slug,
                ':id_kategori'  => $idKategori,
                ':deskripsi'    => $deskripsi,
                ':lokasi'       => $lokasi,
                ':waktu_mulai'  => $waktuMulai,
                ':waktu_selesai'=> $waktuSelesai,
                ':foto_poster'  => $fotoPoster,
                ':penyelenggara'=> $penyelenggara,
                ':status'       => $status,
                ':pembuat'      => $idPenggunaPembuat,
            ]);

            $idEvent = $this->lastInsertId();
            $aksi = ($status === 'menunggu_persetujuan') ? 'DIAJUKAN' : 'BUAT_DRAF';
            $this->catatLogEvent($idEvent, $idPenggunaPembuat, $aksi, 'Event baru dibuat.');
            $this->db->commit();

            return $idEvent;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    /**
     * Persetujuan / Publikasi Event oleh Kesiswaan
     */
    public function setujuiEvent(int $idEvent, int $idPenggunaPenyetuju): bool {
        $sql = "UPDATE event_sekolah 
                SET status = 'dipublikasikan', disetujui_oleh_id_pengguna = :penyetuju, disetujui_pada = NOW(), alasan_penolakan = NULL 
                WHERE id = :id";
        $this->db->beginTransaction();
        try {
            $sukses = $this->query($sql, [':penyetuju' => $idPenggunaPenyetuju, ':id' => $idEvent])->rowCount() > 0;
            if ($sukses) {
                $this->catatLogEvent($idEvent, $idPenggunaPenyetuju, 'DISETUJUI', 'Event disetujui dan dipublikasikan.');
            }
            $this->db->commit();
            return $sukses;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    /**
     * Penolakan Event oleh Kesiswaan
     */
    public function tolakEvent(int $idEvent, int $idPenggunaPenyetuju, string $alasanRevisi): bool {
        $sql = "UPDATE event_sekolah 
                SET status = 'draf', alasan_penolakan = :alasan, disetujui_oleh_id_pengguna = :penyetuju
                WHERE id = :id";
        $this->db->beginTransaction();
        try {
            $sukses = $this->query($sql, [':alasan' => $alasanRevisi, ':penyetuju' => $idPenggunaPenyetuju, ':id' => $idEvent])->rowCount() > 0;
            if ($sukses) {
                $this->catatLogEvent($idEvent, $idPenggunaPenyetuju, 'DITOLAK', $alasanRevisi);
            }
            $this->db->commit();
            return $sukses;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function catatLogEvent(int $idEvent, int $idPengguna, string $aksi, ?string $catatan = null): void {
        $sql = "INSERT INTO log_event (id_event, id_pengguna_pelaku, aksi, catatan, dibuat_pada)
                VALUES (:id_event, :id_pengguna, :aksi, :catatan, NOW())";
        $this->query($sql, [
            ':id_event'   => $idEvent,
            ':id_pengguna'=> $idPengguna,
            ':aksi'       => $aksi,
            ':catatan'    => $catatan
        ]);
    }
}
