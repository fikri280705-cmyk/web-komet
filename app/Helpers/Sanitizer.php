<?php
/**
 * Helper Sanitasi Output XSS & Pemformatan Nilai
 */

namespace App\Helpers;

class Sanitizer {
    /**
     * Escape string untuk mencegah serangan XSS
     */
    public static function e(?string $str): string {
        return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Format Tanggal Bahasa Indonesia (contoh: 15 Oktober 2026)
     */
    public static function formatTanggal(string $tanggal): string {
        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $time = strtotime($tanggal);
        if (!$time) return '-';

        $tgl = date('d', $time);
        $bln = $bulanIndo[(int)date('m', $time)];
        $thn = date('Y', $time);

        return "{$tgl} {$bln} {$thn}";
    }

    /**
     * Format Waktu (contoh: 07:30 WIB)
     */
    public static function formatJam(string $jam): string {
        $time = strtotime($jam);
        return $time ? date('H:i', $time) . ' WIB' : '-';
    }

    /**
     * Helper Badge Status dengan CSS Class Semantik
     */
    public static function badgeStatus(string $status): string {
        $statusClean = strtolower(trim($status));
        $map = [
            'aktif'               => ['class' => 'badge-success', 'label' => 'Aktif'],
            'final'               => ['class' => 'badge-success', 'label' => 'Final (Sah)'],
            'dipublikasikan'      => ['class' => 'badge-success', 'label' => 'Dipublikasikan'],
            'draf'                => ['class' => 'badge-neutral', 'label' => 'Draf'],
            'menunggu_persetujuan'=> ['class' => 'badge-warning', 'label' => 'Menunggu Review'],
            'diarsipkan'          => ['class' => 'badge-secondary', 'label' => 'Diarsipkan'],
            'ditolak'             => ['class' => 'badge-danger',  'label' => 'Ditolak'],
            'naik'                => ['class' => 'badge-info',    'label' => 'Naik Kelas'],
        ];

        $badge = $map[$statusClean] ?? ['class' => 'badge-neutral', 'label' => ucfirst($statusClean)];
        return "<span class=\"badge {$badge['class']}\">{$badge['label']}</span>";
    }

    /**
     * Tahun Ajaran Realtime Dinamis Sesuai Kalender Berjalan
     */
    public static function tahunAjaranRealtime(): string {
        $month = (int)date('n');
        $year = (int)date('Y');
        if ($month >= 7) {
            $nextYear = $year + 1;
            return "TA {$year}/{$nextYear} &bull; Semester Ganjil";
        } else {
            $prevYear = $year - 1;
            return "TA {$prevYear}/{$year} &bull; Semester Genap";
        }
    }
}
