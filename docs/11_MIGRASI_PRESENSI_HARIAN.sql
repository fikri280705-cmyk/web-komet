-- Migrasi lokal: toggle kehadiran harian siswa kelas binaan
CREATE TABLE IF NOT EXISTS `presensi_harian` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `status` ENUM('hadir','tidak_hadir') NOT NULL DEFAULT 'tidak_hadir',
  `dicatat_oleh_id_pengguna` INT NULL,
  `status_whatsapp` VARCHAR(30) NOT NULL DEFAULT 'belum',
  `respon_whatsapp` TEXT NULL,
  `dikirim_whatsapp_pada` DATETIME NULL,
  `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diperbarui_pada` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_presensi_siswa_tanggal` (`id_siswa`, `tanggal`),
  KEY `idx_presensi_tanggal` (`tanggal`),
  CONSTRAINT `fk_presensi_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_presensi_pengguna` FOREIGN KEY (`dicatat_oleh_id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
