-- Migrasi lokal: pesan otomatis event OSIS ke semua siswa + status WhatsApp
CREATE TABLE IF NOT EXISTS `pesan_siswa` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_pengguna` INT NOT NULL,
  `id_event` INT NULL,
  `judul` VARCHAR(255) NOT NULL,
  `isi` TEXT NOT NULL,
  `nomor_whatsapp` VARCHAR(20) NULL,
  `status_whatsapp` VARCHAR(30) NOT NULL DEFAULT 'belum',
  `tautan_whatsapp` TEXT NULL,
  `respon_whatsapp` TEXT NULL,
  `dikirim_whatsapp_pada` DATETIME NULL,
  `sudah_dibaca` TINYINT(1) NOT NULL DEFAULT 0,
  `dibaca_pada` DATETIME NULL,
  `dibuat_pada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pesan_event_pengguna` (`id_event`, `id_pengguna`),
  KEY `idx_pesan_pengguna_dibaca` (`id_pengguna`, `sudah_dibaca`),
  CONSTRAINT `fk_pesan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pesan_event` FOREIGN KEY (`id_event`) REFERENCES `event_sekolah` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `pesan_siswa`
  ADD COLUMN IF NOT EXISTS `nomor_whatsapp` VARCHAR(20) NULL AFTER `isi`,
  ADD COLUMN IF NOT EXISTS `status_whatsapp` VARCHAR(30) NOT NULL DEFAULT 'belum' AFTER `nomor_whatsapp`,
  ADD COLUMN IF NOT EXISTS `tautan_whatsapp` TEXT NULL AFTER `status_whatsapp`,
  ADD COLUMN IF NOT EXISTS `respon_whatsapp` TEXT NULL AFTER `tautan_whatsapp`,
  ADD COLUMN IF NOT EXISTS `dikirim_whatsapp_pada` DATETIME NULL AFTER `respon_whatsapp`;
