-- Migrasi non-destruktif untuk fitur tahun ajaran, semester, dan kurikulum.
-- Jalankan sekali pada db_smkn9 yang sudah ada setelah membuat backup.
-- Jangan gunakan 04_DATABASE_DDL_DUMMY_DATA.sql untuk migrasi database berisi data.

ALTER TABLE `kelas`
    ADD COLUMN `nomor_rombel` TINYINT UNSIGNED NULL AFTER `tingkat`;

UPDATE `kelas`
SET `nomor_rombel` = CAST(SUBSTRING_INDEX(`nama_kelas`, ' ', -1) AS UNSIGNED)
WHERE `nama_kelas` REGEXP '^[0-9]+ .+ [0-9]+$';

ALTER TABLE `kelas`
    MODIFY COLUMN `nomor_rombel` TINYINT UNSIGNED NOT NULL,
    ADD UNIQUE KEY `uk_kelas_jurusan_tingkat_rombel` (`id_jurusan`, `tingkat`, `nomor_rombel`);

ALTER TABLE `semester`
    ADD UNIQUE KEY `uk_semester_tahun_jenis` (`id_tahun_ajaran`, `jenis_semester`);

CREATE TABLE IF NOT EXISTS `kurikulum_mapel` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_tahun_ajaran` INT NOT NULL,
    `id_jurusan` INT NOT NULL,
    `tingkat` TINYINT UNSIGNED NOT NULL,
    `jenis_semester` ENUM('ganjil', 'genap') NOT NULL,
    `id_mapel` INT NOT NULL,
    `pertemuan_per_minggu` TINYINT UNSIGNED NOT NULL,
    UNIQUE KEY `uk_kurikulum_mapel_semester` (`id_tahun_ajaran`, `id_jurusan`, `tingkat`, `jenis_semester`, `id_mapel`),
    CONSTRAINT `fk_kurikulum_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kurikulum_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_kurikulum_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Setelah migrasi, Kesiswaan perlu mengisi template mapel wajib dan
-- jumlah pertemuan mingguan pada menu Tahun Ajaran sebelum aktivasi semester.
-- Alur aplikasi: siapkan semester Genap saat Ganjil aktif, aktifkan Genap tanpa
-- menaikkan siswa, lalu siapkan kedua semester tahun baru sebelum finalisasi
-- kenaikan. Finalisasi mempertahankan riwayat nilai, absensi, dan penempatan.
-- Migrasi ini dijalankan satu kali; jangan mengimpor ulang DDL lengkap
-- 04_DATABASE_DDL_DUMMY_DATA.sql ke database yang sudah berisi data.
