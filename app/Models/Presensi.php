<?php
/**
 * Model Presensi Harian Siswa (hadir / izin / sakit / tidak hadir)
 */

namespace App\Models;

use App\Core\Model;
use App\Services\WhatsappService;

class Presensi extends Model {
    public const STATUS_VALID = ['hadir', 'izin', 'sakit', 'tidak_hadir'];

    public static function labelStatus(string $status): string {
        return match ($status) {
            'hadir' => 'HADIR',
            'izin' => 'IZIN',
            'sakit' => 'SAKIT',
            'tidak_hadir' => 'TIDAK HADIR',
            default => strtoupper($status),
        };
    }

    public function ambilMapStatusKelasHariIni(int $idKelas, ?string $tanggal = null): array {
        $tanggal = $tanggal ?: date('Y-m-d');

        $sql = "SELECT ph.id_siswa, ph.status
                FROM presensi_harian ph
                JOIN riwayat_kelas_siswa rks ON rks.id_siswa = ph.id_siswa
                WHERE rks.id_kelas = :id_kelas
                  AND rks.id_tahun_ajaran = (SELECT id FROM tahun_ajaran WHERE apakah_aktif = 1 LIMIT 1)
                  AND ph.tanggal = :tanggal";

        $rows = $this->fetchAll($sql, [
            ':id_kelas' => $idKelas,
            ':tanggal'  => $tanggal,
        ]);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id_siswa']] = $row['status'];
        }

        return $map;
    }

    public function setStatusSaja(int $idSiswa, string $status, int $idPenggunaPencatat): void {
        if (!in_array($status, self::STATUS_VALID, true)) {
            return;
        }

        $tanggal = date('Y-m-d');
        $this->query(
            "INSERT INTO presensi_harian
                (id_siswa, tanggal, status, dicatat_oleh_id_pengguna, dibuat_pada, diperbarui_pada)
             VALUES
                (:id_siswa, :tanggal, :status, :pencatat, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                dicatat_oleh_id_pengguna = VALUES(dicatat_oleh_id_pengguna),
                diperbarui_pada = NOW()",
            [
                ':id_siswa' => $idSiswa,
                ':tanggal'  => $tanggal,
                ':status'   => $status,
                ':pencatat' => $idPenggunaPencatat,
            ]
        );
    }

    /**
     * Simpan status kehadiran hari ini + kirim WA ke orang tua.
     *
     * @return array{ok:bool,status:string,wa_ok:bool,wa_status:string,wa_message:string,pesan_ortu:string}
     */
    public function setStatusDanKirimWa(
        int $idSiswa,
        string $status,
        int $idPenggunaPencatat,
        string $namaSiswa,
        string $namaKelas,
        ?string $teleponOrtu,
        ?string $teleponSiswa = null
    ): array {
        if (!in_array($status, self::STATUS_VALID, true)) {
            return [
                'ok' => false,
                'status' => $status,
                'wa_ok' => false,
                'wa_status' => 'gagal',
                'wa_message' => 'Status kehadiran tidak valid.',
                'pesan_ortu' => '',
            ];
        }

        $tanggal = date('Y-m-d');
        $sql = "INSERT INTO presensi_harian
                    (id_siswa, tanggal, status, dicatat_oleh_id_pengguna, dibuat_pada, diperbarui_pada)
                VALUES
                    (:id_siswa, :tanggal, :status, :pencatat, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    dicatat_oleh_id_pengguna = VALUES(dicatat_oleh_id_pengguna),
                    diperbarui_pada = NOW()";

        $this->query($sql, [
            ':id_siswa' => $idSiswa,
            ':tanggal'  => $tanggal,
            ':status'   => $status,
            ':pencatat' => $idPenggunaPencatat,
        ]);

        $tanggalLabel = date('d M Y');
        $statusLabel = self::labelStatus($status);
        $pesanOrtu = "Yth. Bapak/Ibu Orang Tua/Wali dari {$namaSiswa}.\n\n"
            . "Kami informasikan bahwa putra/putri Anda dinyatakan {$statusLabel} "
            . "di sekolah pada {$tanggalLabel}.\n"
            . "Kelas: {$namaKelas}\n\n"
            . "Pesan otomatis dari Wali Kelas SMKN 9 Kabupaten Tangerang.";

        $nomorTujuan = trim((string)($teleponOrtu ?: ''));
        if ($nomorTujuan === '') {
            $nomorTujuan = trim((string)($teleponSiswa ?: ''));
        }

        $wa = new WhatsappService();
        $hasilWa = $wa->kirim($nomorTujuan !== '' ? $nomorTujuan : null, $pesanOrtu);

        $this->query(
            "UPDATE presensi_harian
             SET status_whatsapp = :wa_status,
                 respon_whatsapp = :wa_respon,
                 dikirim_whatsapp_pada = NOW()
             WHERE id_siswa = :id_siswa AND tanggal = :tanggal",
            [
                ':wa_status' => $hasilWa['status'],
                ':wa_respon' => $hasilWa['response'],
                ':id_siswa'  => $idSiswa,
                ':tanggal'   => $tanggal,
            ]
        );

        return [
            'ok' => true,
            'status' => $status,
            'wa_ok' => (bool)$hasilWa['ok'],
            'wa_status' => (string)$hasilWa['status'],
            'wa_message' => (string)($hasilWa['response'] ?? ''),
            'pesan_ortu' => $pesanOrtu,
            'nomor_ortu' => $hasilWa['nomor'],
        ];
    }
}
