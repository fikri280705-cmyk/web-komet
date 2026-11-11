-- Migrasi non-destruktif untuk instalasi db_smkn9 yang sudah berjalan.
-- Jalankan sekali pada database db_smkn9 melalui phpMyAdmin atau klien MySQL.
CREATE TABLE IF NOT EXISTS `log_event` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_event` INT NOT NULL,
    `id_pengguna_pelaku` INT NOT NULL,
    `aksi` VARCHAR(50) NOT NULL,
    `catatan` TEXT NULL,
    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_log_event_event` FOREIGN KEY (`id_event`) REFERENCES `event_sekolah` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_log_event_pengguna` FOREIGN KEY (`id_pengguna_pelaku`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
