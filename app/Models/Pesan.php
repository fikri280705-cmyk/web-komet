<?php
/**
 * Model Pesan Otomatis ke Siswa (dari Event OSIS yang dipublikasikan)
 * + pengiriman WhatsApp ke nomor HP siswa
 */

namespace App\Models;

use App\Core\Model;
use App\Services\WhatsappService;

class Pesan extends Model {
    /**
     * Kirim pesan otomatis ke semua akun siswa aktif untuk satu event,
     * lalu dorong ke WhatsApp masing-masing siswa.
     *
     * @return array{db:int,wa_ok:int,wa_gagal:int}
     */
    public function kirimOtomatisEventKeSemuaSiswa(array $event): array {
        $idEvent = (int)($event['id'] ?? 0);
        if ($idEvent <= 0) {
            return ['db' => 0, 'wa_ok' => 0, 'wa_gagal' => 0];
        }

        $judulEvent = trim((string)($event['judul'] ?? 'Event Sekolah'));
        $lokasi = trim((string)($event['lokasi'] ?? '-'));
        $penyelenggara = trim((string)($event['penyelenggara'] ?? 'OSIS'));
        $waktuMulai = !empty($event['waktu_mulai'])
            ? date('d M Y H:i', strtotime($event['waktu_mulai']))
            : '-';
        $waktuSelesai = !empty($event['waktu_selesai'])
            ? date('H:i', strtotime($event['waktu_selesai']))
            : '-';

        $judulPesan = 'Event Baru: ' . $judulEvent;
        $isiPesan = "Halo siswa SMKN 9 Kabupaten Tangerang!\n\n"
            . "Ada event OSIS terbaru yang sudah dipublikasikan oleh Kesiswaan.\n\n"
            . "Judul: {$judulEvent}\n"
            . "Penyelenggara: {$penyelenggara}\n"
            . "Lokasi: {$lokasi}\n"
            . "Waktu: {$waktuMulai} - {$waktuSelesai} WIB\n\n"
            . "Silakan cek detail lengkap di portal SIAKAD (menu Event & Mading Sekolah).";

        $sql = "INSERT INTO pesan_siswa (id_pengguna, id_event, judul, isi, sudah_dibaca, status_whatsapp, dibuat_pada)
                SELECT p.id, :id_event, :judul, :isi, 0, 'belum', NOW()
                FROM pengguna p
                JOIN peran r ON r.id = p.id_peran
                WHERE r.kode_peran = 'siswa'
                  AND p.apakah_aktif = 1
                  AND NOT EXISTS (
                      SELECT 1
                      FROM pesan_siswa ps
                      WHERE ps.id_event = :id_event_cek
                        AND ps.id_pengguna = p.id
                  )";

        $stmt = $this->query($sql, [
            ':id_event'     => $idEvent,
            ':id_event_cek' => $idEvent,
            ':judul'        => $judulPesan,
            ':isi'          => $isiPesan,
        ]);

        $jumlahDb = $stmt->rowCount();
        $hasilWa = $this->kirimWhatsappUntukEvent($idEvent);

        return [
            'db' => $jumlahDb,
            'wa_ok' => $hasilWa['ok'],
            'wa_gagal' => $hasilWa['gagal'],
        ];
    }

    /**
     * Kirim/antrikan WhatsApp untuk pesan event yang belum terkirim.
     *
     * @return array{ok:int,gagal:int}
     */
    public function kirimWhatsappUntukEvent(int $idEvent, int $batas = 0): array {
        $sql = "SELECT ps.id, ps.isi,
                       COALESCE(NULLIF(s.nomor_telepon, ''), NULLIF(p.nomor_telepon, '')) AS nomor_telepon
                FROM pesan_siswa ps
                JOIN pengguna p ON p.id = ps.id_pengguna
                LEFT JOIN siswa s ON s.id_pengguna = p.id
                WHERE ps.id_event = :id_event
                  AND ps.status_whatsapp IN ('belum', 'gagal')
                ORDER BY ps.id ASC";

        if ($batas > 0) {
            $sql .= ' LIMIT ' . (int)$batas;
        }

        $baris = $this->fetchAll($sql, [':id_event' => $idEvent]);
        if (empty($baris)) {
            return ['ok' => 0, 'gagal' => 0];
        }

        $wa = new WhatsappService();
        $configPath = dirname(__DIR__) . '/Config/Whatsapp.php';
        $config = is_file($configPath) ? require $configPath : [];
        $delayMs = (int)($config['delay_ms'] ?? 0);

        $ok = 0;
        $gagal = 0;

        foreach ($baris as $item) {
            $hasil = $wa->kirim($item['nomor_telepon'] ?? null, (string)$item['isi']);

            $this->query(
                "UPDATE pesan_siswa
                 SET nomor_whatsapp = :nomor,
                     status_whatsapp = :status,
                     tautan_whatsapp = :tautan,
                     respon_whatsapp = :respon,
                     dikirim_whatsapp_pada = NOW()
                 WHERE id = :id",
                [
                    ':nomor'  => $hasil['nomor'],
                    ':status' => $hasil['status'],
                    ':tautan' => $hasil['tautan'],
                    ':respon' => $hasil['response'],
                    ':id'     => (int)$item['id'],
                ]
            );

            if ($hasil['ok']) {
                $ok++;
            } else {
                $gagal++;
            }

            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        return ['ok' => $ok, 'gagal' => $gagal];
    }

    /**
     * Sinkronkan pesan + WhatsApp untuk semua event yang sudah dipublikasikan.
     *
     * @return array{db:int,wa_ok:int,wa_gagal:int}
     */
    public function sinkronkanEventPublik(): array {
        $events = $this->fetchAll(
            "SELECT es.*
             FROM event_sekolah es
             WHERE es.status = 'dipublikasikan'
             ORDER BY es.disetujui_pada DESC, es.id DESC"
        );

        $total = ['db' => 0, 'wa_ok' => 0, 'wa_gagal' => 0];
        foreach ($events as $event) {
            $hasil = $this->kirimOtomatisEventKeSemuaSiswa($event);
            $total['db'] += $hasil['db'];
            $total['wa_ok'] += $hasil['wa_ok'];
            $total['wa_gagal'] += $hasil['wa_gagal'];
        }

        return $total;
    }

    public function ambilUntukPengguna(int $idPengguna, int $batas = 50): array {
        $batas = max(1, (int)$batas);
        $sql = "SELECT ps.*, es.judul AS judul_event, es.lokasi, es.waktu_mulai, es.foto_poster, ke.nama_kategori
                FROM pesan_siswa ps
                LEFT JOIN event_sekolah es ON es.id = ps.id_event
                LEFT JOIN kategori_event ke ON ke.id = es.id_kategori
                WHERE ps.id_pengguna = :id_pengguna
                ORDER BY ps.sudah_dibaca ASC, ps.dibuat_pada DESC
                LIMIT {$batas}";

        return $this->fetchAll($sql, [':id_pengguna' => $idPengguna]);
    }

    public function hitungBelumDibaca(int $idPengguna): int {
        return (int)$this->fetchColumn(
            "SELECT COUNT(*) FROM pesan_siswa WHERE id_pengguna = :id AND sudah_dibaca = 0",
            [':id' => $idPengguna]
        );
    }

    public function tandaiDibaca(int $idPesan, int $idPengguna): bool {
        $sql = "UPDATE pesan_siswa
                SET sudah_dibaca = 1, dibaca_pada = NOW()
                WHERE id = :id AND id_pengguna = :id_pengguna AND sudah_dibaca = 0";
        return $this->query($sql, [
            ':id'           => $idPesan,
            ':id_pengguna'  => $idPengguna,
        ])->rowCount() > 0;
    }

    public function tandaiSemuaDibaca(int $idPengguna): int {
        $sql = "UPDATE pesan_siswa
                SET sudah_dibaca = 1, dibaca_pada = NOW()
                WHERE id_pengguna = :id_pengguna AND sudah_dibaca = 0";
        return $this->query($sql, [':id_pengguna' => $idPengguna])->rowCount();
    }

}
