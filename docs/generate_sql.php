<?php
/**
 * Generator Lengkap untuk docs/04_DATABASE_DDL_DUMMY_DATA.sql
 */

require_once __DIR__ . '/prepare_data.php';

$data = json_decode(file_get_contents(__DIR__ . '/dataset_prepared.json'), true);

$jurusanList = $data['jurusan'];
$kelasList   = $data['kelas'];
$guruList    = $data['guru'];
$siswaList   = $data['siswa'];
$osisList    = $data['osis'];

$sql = "-- ==============================================================================\n";
$sql .= "-- BASIS DATA RESMI SIAKAD SMK NEGERI 9 KABUPATEN TANGERANG\n";
$sql .= "-- NAMA DATABASE : db_smkn9\n";
$sql .= "-- ENGINE        : InnoDB | CHARSET : utf8mb4 | COLLATION : utf8mb4_unicode_ci\n";
$sql .= "-- TARGET        : MySQL 8.0+ / MariaDB 10.4+ / Laragon phpMyAdmin\n";
$sql .= "-- ==============================================================================\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$sql .= "SET time_zone = '+07:00';\n\n";

$sql .= "CREATE DATABASE IF NOT EXISTS `db_smkn9` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$sql .= "USE `db_smkn9`;\n\n";

// Drop tables in reverse order to ensure clean import
$sql .= "DROP TABLE IF EXISTS `log_audit`;\n";
$sql .= "DROP TABLE IF EXISTS `log_event`;\n";
$sql .= "DROP TABLE IF EXISTS `event_sekolah`;\n";
$sql .= "DROP TABLE IF EXISTS `kategori_event`;\n";
$sql .= "DROP TABLE IF EXISTS `kehadiran_siswa`;\n";
$sql .= "DROP TABLE IF EXISTS `nilai_siswa`;\n";
$sql .= "DROP TABLE IF EXISTS `jadwal_pelajaran`;\n";
$sql .= "DROP TABLE IF EXISTS `penugasan_guru`;\n";
$sql .= "DROP TABLE IF EXISTS `kurikulum_mapel`;\n";
$sql .= "DROP TABLE IF EXISTS `mata_pelajaran`;\n";
$sql .= "DROP TABLE IF EXISTS `pengurus_osis`;\n";
$sql .= "DROP TABLE IF EXISTS `riwayat_kelas_siswa`;\n";
$sql .= "DROP TABLE IF EXISTS `penugasan_wali_kelas`;\n";
$sql .= "DROP TABLE IF EXISTS `siswa`;\n";
$sql .= "DROP TABLE IF EXISTS `kelas`;\n";
$sql .= "DROP TABLE IF EXISTS `jurusan`;\n";
$sql .= "DROP TABLE IF EXISTS `guru`;\n";
$sql .= "DROP TABLE IF EXISTS `semester`;\n";
$sql .= "DROP TABLE IF EXISTS `tahun_ajaran`;\n";
$sql .= "DROP TABLE IF EXISTS `pengguna`;\n";
$sql .= "DROP TABLE IF EXISTS `peran`;\n\n";

// 1. peran
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 1. TABEL: peran\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `peran` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `kode_peran` VARCHAR(30) NOT NULL UNIQUE COMMENT 'kesiswaan, kaprog, wali_kelas, siswa, osis',\n";
$sql .= "    `nama_peran` VARCHAR(50) NOT NULL,\n";
$sql .= "    `deskripsi` VARCHAR(255) NULL\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 2. pengguna
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 2. TABEL: pengguna (Login dengan NIS untuk Siswa, NUPTK untuk Guru)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `pengguna` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `nomor_induk` VARCHAR(30) NOT NULL UNIQUE COMMENT 'NIS untuk siswa, NUPTK untuk guru/staff',\n";
$sql .= "    `email` VARCHAR(100) NULL,\n";
$sql .= "    `kata_sandi` VARCHAR(255) NOT NULL,\n";
$sql .= "    `id_peran` INT NOT NULL,\n";
$sql .= "    `nama_lengkap` VARCHAR(100) NOT NULL,\n";
$sql .= "    `nomor_telepon` VARCHAR(20) NULL,\n";
$sql .= "    `foto_profil` VARCHAR(255) NULL DEFAULT 'avatar_default.png',\n";
$sql .= "    `apakah_aktif` TINYINT(1) NOT NULL DEFAULT 1,\n";
$sql .= "    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    `diperbarui_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "    CONSTRAINT `fk_pengguna_peran` FOREIGN KEY (`id_peran`) REFERENCES `peran` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 3. tahun_ajaran & semester
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 3. TABEL: tahun_ajaran & semester\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `tahun_ajaran` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `kode_tahun` VARCHAR(15) NOT NULL UNIQUE COMMENT 'Contoh: 2026-2027',\n";
$sql .= "    `nama_tahun` VARCHAR(30) NOT NULL COMMENT 'Tahun Ajaran 2026/2027',\n";
$sql .= "    `apakah_aktif` TINYINT(1) NOT NULL DEFAULT 0,\n";
$sql .= "    `tanggal_mulai` DATE NOT NULL,\n";
$sql .= "    `tanggal_selesai` DATE NOT NULL\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "CREATE TABLE `semester` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_tahun_ajaran` INT NOT NULL,\n";
$sql .= "    `jenis_semester` ENUM('ganjil', 'genap') NOT NULL,\n";
$sql .= "    `apakah_aktif` TINYINT(1) NOT NULL DEFAULT 0,\n";
$sql .= "    `tanggal_mulai` DATE NOT NULL,\n";
$sql .= "    `tanggal_selesai` DATE NOT NULL,\n";
$sql .= "    UNIQUE KEY `uk_semester_tahun_jenis` (`id_tahun_ajaran`, `jenis_semester`),\n";
$sql .= "    CONSTRAINT `fk_semester_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 4. jurusan (Relasi ke guru dibuat di akhir file agar tidak ada circular foreign key error)
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 4. TABEL: jurusan (6 Konsentrasi Keahlian Resmi)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `jurusan` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `kode_jurusan` VARCHAR(20) NOT NULL UNIQUE COMMENT 'TKJ, DKV, AKC, FKK, MPLB, TBSM',\n";
$sql .= "    `nama_jurusan` VARCHAR(100) NOT NULL,\n";
$sql .= "    `id_guru_kaprog` INT NULL COMMENT 'Kepala Program Keahlian',\n";
$sql .= "    `kode_lab` VARCHAR(50) NULL COMMENT 'Kode Lab/Bengkel Utama (Labkom 001, Bengkel 001, dll)',\n";
$sql .= "    `deskripsi` TEXT NULL\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 5. guru
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 5. TABEL: guru (Data Lengkap 98 Guru/Tenaga Pendidik)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `guru` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_pengguna` INT NULL UNIQUE COMMENT 'Terhubung ke akun login pengguna',\n";
$sql .= "    `nuptk` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Nomor Unik Pendidik dan Tenaga Kependidikan',\n";
$sql .= "    `nip` VARCHAR(30) NULL COMMENT 'NIP ASN (jika ada)',\n";
$sql .= "    `nama_lengkap` VARCHAR(100) NOT NULL,\n";
$sql .= "    `jenis_kelamin` ENUM('L', 'P') NOT NULL DEFAULT 'L',\n";
$sql .= "    `tempat_lahir` VARCHAR(50) NULL DEFAULT 'Tangerang',\n";
$sql .= "    `tanggal_lahir` DATE NULL DEFAULT '1985-05-15',\n";
$sql .= "    `agama` VARCHAR(20) NULL DEFAULT 'Islam',\n";
$sql .= "    `nomor_telepon` VARCHAR(20) NULL,\n";
$sql .= "    `email` VARCHAR(100) NULL,\n";
$sql .= "    `alamat` TEXT NULL,\n";
$sql .= "    `provinsi` VARCHAR(50) NULL DEFAULT 'Banten',\n";
$sql .= "    `kabupaten` VARCHAR(50) NULL DEFAULT 'Kabupaten Tangerang',\n";
$sql .= "    `kode_pos` VARCHAR(10) NULL DEFAULT '15710',\n";
$sql .= "    `pendidikan_terakhir` VARCHAR(50) NULL DEFAULT 'S1 Kependidikan',\n";
$sql .= "    `npwp` VARCHAR(30) NULL,\n";
$sql .= "    `id_jurusan` INT NULL COMMENT 'Jurusan homebase guru kejuruan',\n";
$sql .= "    `mapel_utama` VARCHAR(100) NULL,\n";
$sql .= "    `jabatan_tugas` VARCHAR(50) NULL DEFAULT 'Guru Pengajar',\n";
$sql .= "    `apakah_aktif` TINYINT(1) NOT NULL DEFAULT 1,\n";
$sql .= "    CONSTRAINT `fk_guru_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,\n";
$sql .= "    CONSTRAINT `fk_guru_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 6. kelas
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 6. TABEL: kelas (36 Kelas: 2 rombel per tingkat di 6 jurusan)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `kelas` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_jurusan` INT NOT NULL,\n";
$sql .= "    `tingkat` TINYINT NOT NULL COMMENT '10, 11, atau 12',\n";
$sql .= "    `nomor_rombel` TINYINT UNSIGNED NOT NULL,\n";
$sql .= "    `nama_kelas` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Contoh: 10 TKJ 1, 11 DKV 1',\n";
$sql .= "    UNIQUE KEY `uk_kelas_jurusan_tingkat_rombel` (`id_jurusan`, `tingkat`, `nomor_rombel`),\n";
$sql .= "    CONSTRAINT `fk_kelas_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 7. penugasan_wali_kelas
$sql .= "CREATE TABLE `penugasan_wali_kelas` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_guru` INT NOT NULL,\n";
$sql .= "    `id_kelas` INT NOT NULL,\n";
$sql .= "    `id_tahun_ajaran` INT NOT NULL,\n";
$sql .= "    `apakah_aktif` TINYINT(1) NOT NULL DEFAULT 1,\n";
$sql .= "    `tanggal_mulai_tugas` DATE NOT NULL DEFAULT (CURRENT_DATE),\n";
$sql .= "    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    UNIQUE KEY `uk_wali_kelas_periode` (`id_kelas`, `id_tahun_ajaran`),\n";
$sql .= "    CONSTRAINT `fk_pwk_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_pwk_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_pwk_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 8. siswa
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- 8. TABEL: siswa (Data Lengkap 1.080 Siswa)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "CREATE TABLE `siswa` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_pengguna` INT NULL UNIQUE COMMENT 'Akun login portal siswa',\n";
$sql .= "    `nis` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Nomor Induk Siswa Sekolah',\n";
$sql .= "    `nisn` VARCHAR(20) NULL UNIQUE COMMENT 'Nomor Induk Siswa Nasional',\n";
$sql .= "    `nama_lengkap` VARCHAR(100) NOT NULL,\n";
$sql .= "    `jenis_kelamin` ENUM('L', 'P') NOT NULL DEFAULT 'L',\n";
$sql .= "    `tempat_lahir` VARCHAR(50) NULL DEFAULT 'Tangerang',\n";
$sql .= "    `tanggal_lahir` DATE NULL,\n";
$sql .= "    `agama` VARCHAR(20) NULL DEFAULT 'Islam',\n";
$sql .= "    `alamat` TEXT NULL,\n";
$sql .= "    `provinsi` VARCHAR(50) NULL DEFAULT 'Banten',\n";
$sql .= "    `kabupaten` VARCHAR(50) NULL DEFAULT 'Kabupaten Tangerang',\n";
$sql .= "    `kode_pos` VARCHAR(10) NULL DEFAULT '15710',\n";
$sql .= "    `nomor_telepon` VARCHAR(20) NULL,\n";
$sql .= "    `nama_ayah` VARCHAR(100) NULL,\n";
$sql .= "    `nama_ibu` VARCHAR(100) NULL,\n";
$sql .= "    `pekerjaan_ortu` VARCHAR(100) NULL DEFAULT 'Wiraswasta',\n";
$sql .= "    `telepon_orang_tua` VARCHAR(20) NULL,\n";
$sql .= "    `id_jurusan` INT NOT NULL,\n";
$sql .= "    `tahun_masuk` YEAR NOT NULL,\n";
$sql .= "    `foto` VARCHAR(255) NULL DEFAULT 'foto_siswa_default.png',\n";
$sql .= "    `status_siswa` ENUM('aktif', 'lulus', 'mutasi', 'keluar') NOT NULL DEFAULT 'aktif',\n";
$sql .= "    CONSTRAINT `fk_siswa_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,\n";
$sql .= "    CONSTRAINT `fk_siswa_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 9. riwayat_kelas_siswa
$sql .= "CREATE TABLE `riwayat_kelas_siswa` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_siswa` INT NOT NULL,\n";
$sql .= "    `id_kelas` INT NOT NULL,\n";
$sql .= "    `id_tahun_ajaran` INT NOT NULL,\n";
$sql .= "    `status_penempatan` ENUM('aktif', 'naik', 'tinggal', 'lulus', 'pindah') NOT NULL DEFAULT 'aktif',\n";
$sql .= "    `didaftarkan_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    UNIQUE KEY `uk_siswa_tahun_ajaran` (`id_siswa`, `id_tahun_ajaran`),\n";
$sql .= "    CONSTRAINT `fk_rks_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_rks_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_rks_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 10. pengurus_osis
$sql .= "CREATE TABLE `pengurus_osis` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_siswa` INT NOT NULL,\n";
$sql .= "    `jabatan` VARCHAR(100) NOT NULL COMMENT 'Ketua OSIS, Wakil Ketua, Sekretaris, Bendahara, Seksi Bidang',\n";
$sql .= "    `periode` VARCHAR(30) NOT NULL DEFAULT '2026/2027',\n";
$sql .= "    `status_aktif` TINYINT(1) NOT NULL DEFAULT 1,\n";
$sql .= "    CONSTRAINT `fk_osis_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 11. mata_pelajaran
$sql .= "CREATE TABLE `mata_pelajaran` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `kode_mapel` VARCHAR(20) NOT NULL UNIQUE,\n";
$sql .= "    `nama_mapel` VARCHAR(100) NOT NULL,\n";
$sql .= "    `kelompok_mapel` ENUM('umum', 'kejuruan', 'muatan_lokal') NOT NULL DEFAULT 'umum',\n";
$sql .= "    `id_jurusan` INT NULL,\n";
$sql .= "    CONSTRAINT `fk_mapel_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "CREATE TABLE `kurikulum_mapel` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_tahun_ajaran` INT NOT NULL,\n";
$sql .= "    `id_jurusan` INT NOT NULL,\n";
$sql .= "    `tingkat` TINYINT UNSIGNED NOT NULL,\n";
$sql .= "    `jenis_semester` ENUM('ganjil', 'genap') NOT NULL,\n";
$sql .= "    `id_mapel` INT NOT NULL,\n";
$sql .= "    `pertemuan_per_minggu` TINYINT UNSIGNED NOT NULL,\n";
$sql .= "    UNIQUE KEY `uk_kurikulum_mapel_semester` (`id_tahun_ajaran`, `id_jurusan`, `tingkat`, `jenis_semester`, `id_mapel`),\n";
$sql .= "    CONSTRAINT `fk_kurikulum_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_kurikulum_jurusan` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_kurikulum_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 12. penugasan_guru
$sql .= "CREATE TABLE `penugasan_guru` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_tahun_ajaran` INT NOT NULL,\n";
$sql .= "    `id_semester` INT NOT NULL,\n";
$sql .= "    `id_guru` INT NOT NULL,\n";
$sql .= "    `id_mapel` INT NOT NULL,\n";
$sql .= "    `id_kelas` INT NOT NULL,\n";
$sql .= "    UNIQUE KEY `uk_tugas_mengajar` (`id_tahun_ajaran`, `id_semester`, `id_guru`, `id_mapel`, `id_kelas`),\n";
$sql .= "    CONSTRAINT `fk_pg_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_pg_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_pg_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_pg_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_pg_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 13. jadwal_pelajaran
$sql .= "CREATE TABLE `jadwal_pelajaran` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_penugasan_guru` INT NOT NULL,\n";
$sql .= "    `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat') NOT NULL,\n";
$sql .= "    `jam_mulai` TIME NOT NULL,\n";
$sql .= "    `jam_selesai` TIME NOT NULL,\n";
$sql .= "    `ruangan` VARCHAR(50) NOT NULL DEFAULT 'Ruang Teori 101',\n";
$sql .= "    `apakah_lab` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika menggunakan lab/bengkel',\n";
$sql .= "    CONSTRAINT `fk_jp_penugasan_guru` FOREIGN KEY (`id_penugasan_guru`) REFERENCES `penugasan_guru` (`id`) ON DELETE CASCADE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 14. nilai_siswa
$sql .= "CREATE TABLE `nilai_siswa` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_siswa` INT NOT NULL,\n";
$sql .= "    `id_penugasan_guru` INT NOT NULL,\n";
$sql .= "    `id_semester` INT NOT NULL,\n";
$sql .= "    `nilai_tugas` DECIMAL(5, 2) NULL DEFAULT 0.00,\n";
$sql .= "    `nilai_uts` DECIMAL(5, 2) NULL DEFAULT 0.00,\n";
$sql .= "    `nilai_uas` DECIMAL(5, 2) NULL DEFAULT 0.00,\n";
$sql .= "    `nilai_praktik` DECIMAL(5, 2) NULL DEFAULT 0.00,\n";
$sql .= "    `nilai_akhir` DECIMAL(5, 2) NULL DEFAULT 0.00,\n";
$sql .= "    `predikat_huruf` ENUM('A', 'B', 'C', 'D', 'E') NULL,\n";
$sql .= "    `status_nilai` ENUM('draf', 'final') NOT NULL DEFAULT 'final',\n";
$sql .= "    `disahkan_oleh` INT NULL,\n";
$sql .= "    `disahkan_pada` TIMESTAMP NULL,\n";
$sql .= "    `diperbarui_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "    UNIQUE KEY `uk_nilai_siswa_mapel_semester` (`id_siswa`, `id_penugasan_guru`, `id_semester`),\n";
$sql .= "    CONSTRAINT `fk_ns_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_ns_penugasan` FOREIGN KEY (`id_penugasan_guru`) REFERENCES `penugasan_guru` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_ns_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 15. kehadiran_siswa
$sql .= "CREATE TABLE `kehadiran_siswa` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_siswa` INT NOT NULL,\n";
$sql .= "    `id_semester` INT NOT NULL,\n";
$sql .= "    `sakit` INT NOT NULL DEFAULT 0,\n";
$sql .= "    `izin` INT NOT NULL DEFAULT 0,\n";
$sql .= "    `alfa` INT NOT NULL DEFAULT 0,\n";
$sql .= "    `catatan` VARCHAR(255) NULL,\n";
$sql .= "    `diperbarui_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "    UNIQUE KEY `uk_kehadiran_siswa_semester` (`id_siswa`, `id_semester`),\n";
$sql .= "    CONSTRAINT `fk_kehadiran_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_kehadiran_semester` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// 16. event & mading
$sql .= "CREATE TABLE `kategori_event` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `nama_kategori` VARCHAR(50) NOT NULL,\n";
$sql .= "    `slug` VARCHAR(50) NOT NULL UNIQUE\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "CREATE TABLE `event_sekolah` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `judul` VARCHAR(150) NOT NULL,\n";
$sql .= "    `slug` VARCHAR(180) NOT NULL UNIQUE,\n";
$sql .= "    `id_kategori` INT NOT NULL,\n";
$sql .= "    `deskripsi` LONGTEXT NOT NULL,\n";
$sql .= "    `lokasi` VARCHAR(100) NOT NULL DEFAULT 'SMKN 9 Tangerang',\n";
$sql .= "    `waktu_mulai` DATETIME NOT NULL,\n";
$sql .= "    `waktu_selesai` DATETIME NOT NULL,\n";
$sql .= "    `foto_poster` VARCHAR(255) NULL,\n";
$sql .= "    `penyelenggara` VARCHAR(100) NOT NULL DEFAULT 'OSIS SMKN 9 Tangerang',\n";
$sql .= "    `status` ENUM('draf', 'menunggu_persetujuan', 'dipublikasikan', 'diarsipkan') NOT NULL DEFAULT 'dipublikasikan',\n";
$sql .= "    `dibuat_oleh_id_pengguna` INT NOT NULL,\n";
$sql .= "    `disetujui_oleh_id_pengguna` INT NULL,\n";
$sql .= "    `disetujui_pada` TIMESTAMP NULL,\n";
$sql .= "    `alasan_penolakan` TEXT NULL,\n";
$sql .= "    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    `diperbarui_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n";
$sql .= "    CONSTRAINT `fk_es_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_event` (`id`) ON DELETE RESTRICT,\n";
$sql .= "    CONSTRAINT `fk_es_pembuat` FOREIGN KEY (`dibuat_oleh_id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "CREATE TABLE `log_event` (\n";
$sql .= "    `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_event` INT NOT NULL,\n";
$sql .= "    `id_pengguna_pelaku` INT NOT NULL,\n";
$sql .= "    `aksi` VARCHAR(50) NOT NULL,\n";
$sql .= "    `catatan` TEXT NULL,\n";
$sql .= "    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
$sql .= "    CONSTRAINT `fk_log_event_event` FOREIGN KEY (`id_event`) REFERENCES `event_sekolah` (`id`) ON DELETE CASCADE,\n";
$sql .= "    CONSTRAINT `fk_log_event_pengguna` FOREIGN KEY (`id_pengguna_pelaku`) REFERENCES `pengguna` (`id`) ON DELETE RESTRICT\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

$sql .= "CREATE TABLE `log_audit` (\n";
$sql .= "    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,\n";
$sql .= "    `id_pengguna` INT NULL,\n";
$sql .= "    `aksi` VARCHAR(50) NOT NULL,\n";
$sql .= "    `modul` VARCHAR(50) NOT NULL,\n";
$sql .= "    `id_data` INT NULL,\n";
$sql .= "    `nilai_lama` JSON NULL,\n";
$sql .= "    `nilai_baru` JSON NULL,\n";
$sql .= "    `alamat_ip` VARCHAR(45) NOT NULL DEFAULT '0.0.0.0',\n";
$sql .= "    `agen_pengguna` VARCHAR(255) NULL,\n";
$sql .= "    `dibuat_pada` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP\n";
$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

// ==============================================================================
// DATA SEEDING (Disusun secara hierarkis tanpa pelanggaran Foreign Key)
// ==============================================================================
$sql .= "-- ==============================================================================\n";
$sql .= "-- SEEDING DATA DUMMY\n";
$sql .= "-- ==============================================================================\n\n";

// 1. Peran
$sql .= "INSERT INTO `peran` (`id`, `kode_peran`, `nama_peran`, `deskripsi`) VALUES\n";
$sql .= "(1, 'kesiswaan', 'Kesiswaan & Kurikulum', 'Administrator utama akademik, kesiswaan lintas jurusan, dan moderasi publikasi.'),\n";
$sql .= "(2, 'kaprog', 'Kepala Program Keahlian', 'Pengawas akademik dan jadwal terisolasi khusus jurusannya.'),\n";
$sql .= "(3, 'wali_kelas', 'Wali Kelas', 'Pembina rombel, pengelola nilai rapor kelas binaan, dan pemantau jadwal kelas.'),\n";
$sql .= "(4, 'siswa', 'Siswa / Murid', 'Akses transkrip nilai final, jadwal harian kelas, dan event resmi.'),\n";
$sql .= "(5, 'osis', 'Pengurus OSIS', 'Pengelola usulan event, pengunggah foto poster, dan mading digital sekolah.');\n\n";

// 2. Tahun Ajaran & Semester
$sql .= "INSERT INTO `tahun_ajaran` (`id`, `kode_tahun`, `nama_tahun`, `apakah_aktif`, `tanggal_mulai`, `tanggal_selesai`) VALUES\n";
$sql .= "(1, '2025-2026', 'Tahun Ajaran 2025/2026', 0, '2025-07-14', '2026-06-19'),\n";
$sql .= "(2, '2026-2027', 'Tahun Ajaran 2026/2027', 1, '2026-07-13', '2027-06-18');\n\n";

$sql .= "INSERT INTO `semester` (`id`, `id_tahun_ajaran`, `jenis_semester`, `apakah_aktif`, `tanggal_mulai`, `tanggal_selesai`) VALUES\n";
$sql .= "(1, 1, 'ganjil', 0, '2025-07-14', '2025-12-19'),\n";
$sql .= "(2, 1, 'genap',  0, '2026-01-05', '2026-06-19'),\n";
$sql .= "(3, 2, 'ganjil', 1, '2026-07-13', '2026-12-18'),\n";
$sql .= "(4, 2, 'genap',  0, '2027-01-04', '2027-06-18');\n\n";

// 3. Persiapan Data Pengguna Guru & Siswa
$userId = 1;
$userRows = [];
$guruRows = [];

$bcryptCache = [];
function getHash($pass, &$cache) {
    if (!isset($cache[$pass])) {
        $cache[$pass] = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
    }
    return $cache[$pass];
}

foreach ($guruList as $g) {
    $last6 = substr($g['nuptk'], -6);
    $plainPass = "gurusmkn9#{$last6}";
    $passHash = getHash($plainPass, $bcryptCache);
    $email = strtolower(str_replace([' ', '.', ',', "'"], '', explode(',', $g['nama'])[0])) . "@smkn9kabtangerang.sch.id";
    
    $userRows[] = "({$userId}, '{$g['nuptk']}', '{$email}', '{$passHash}', {$g['peran_id']}, '" . addslashes($g['nama']) . "', '08129000" . sprintf('%04d', $g['id']) . "', 'avatar_default.png', 1)";
    
    $jurusanIdVal = $g['jurusan_id'] ? $g['jurusan_id'] : "NULL";
    $jabatan = $g['kode_peran'] === 'kesiswaan' ? 'Kesiswaan & Kurikulum' : ($g['kode_peran'] === 'kaprog' ? 'Kepala Program Keahlian' : ($g['kode_peran'] === 'wali_kelas' ? 'Wali Kelas' : 'Guru Pengajar'));
    
    $guruRows[] = "({$g['id']}, {$userId}, '{$g['nuptk']}', '{$g['nip']}', '" . addslashes($g['nama']) . "', '{$g['jk']}', 'Tangerang', '1985-05-15', 'Islam', '08129000" . sprintf('%04d', $g['id']) . "', '{$email}', 'Jl. Raya Pemda No. " . (10 + $g['id']) . ", Tigaraksa', 'Banten', 'Kabupaten Tangerang', '15710', 'S1 Kependidikan', '09." . rand(100, 999) . "." . rand(100, 999) . ".1-418.000', {$jurusanIdVal}, '" . addslashes($g['mapel']) . "', '{$jabatan}', 1)";
    
    $userId++;
}

// Persiapan Data Siswa
$siswaRows = [];
$riwayatRows = [];
$rwId = 1;

foreach ($siswaList as $s) {
    $last4 = substr($s['nis'], -4);
    $plainPass = "smkn9#{$last4}";
    $passHash = getHash($plainPass, $bcryptCache);
    
    // Nayla Zahra (Ketua OSIS, id_siswa 12) menggunakan peran OSIS (id_peran 5)
    $roleId = ($s['id'] === 12) ? 5 : 4;
    
    $email = "siswa.{$s['nis']}@smkn9kabtangerang.sch.id";
    $userRows[] = "({$userId}, '{$s['nis']}', '{$email}', '{$passHash}', {$roleId}, '" . addslashes($s['nama']) . "', '{$s['telepon']}', 'avatar_default.png', 1)";
    
    $siswaRows[] = "({$s['id']}, {$userId}, '{$s['nis']}', '{$s['nisn']}', '" . addslashes($s['nama']) . "', '{$s['jk']}', '{$s['tempat_lahir']}', '{$s['tanggal_lahir']}', '{$s['agama']}', '" . addslashes($s['alamat']) . "', '{$s['provinsi']}', '{$s['kabupaten']}', '{$s['kode_pos']}', '{$s['telepon']}', '" . addslashes($s['nama_ayah']) . "', '" . addslashes($s['nama_ibu']) . "', '{$s['pekerjaan_ortu']}', '{$s['telepon_ortu']}', {$s['id_jurusan']}, {$s['tahun_masuk']}, 'foto_siswa_default.png', 'aktif')";
    
    $riwayatRows[] = "({$rwId}, {$s['id']}, {$s['id_kelas']}, 2, 'aktif')";
    
    $userId++;
    $rwId++;
}

// 4. Seeding Pengguna Terlebih Dahulu (Wajib sebelum guru dan siswa)
$sql .= "INSERT INTO `pengguna` (`id`, `nomor_induk`, `email`, `kata_sandi`, `id_peran`, `nama_lengkap`, `nomor_telepon`, `foto_profil`, `apakah_aktif`) VALUES\n";
$sql .= implode(",\n", $userRows) . ";\n\n";

// 5. Seeding Jurusan
$sql .= "INSERT INTO `jurusan` (`id`, `kode_jurusan`, `nama_jurusan`, `id_guru_kaprog`, `kode_lab`, `deskripsi`) VALUES\n";
$jurRows = [];
foreach ($jurusanList as $j) {
    $labVal = $j['lab'] ? "'{$j['lab']}'" : "NULL";
    $jurRows[] = "({$j['id']}, '{$j['kode']}', '" . addslashes($j['nama']) . "', {$j['kaprog_guru_id']}, {$labVal}, '" . addslashes($j['deskripsi']) . "')";
}
$sql .= implode(",\n", $jurRows) . ";\n\n";

// 6. Seeding Guru (Sudah ada pengguna dan jurusan)
$sql .= "INSERT INTO `guru` (`id`, `id_pengguna`, `nuptk`, `nip`, `nama_lengkap`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `agama`, `nomor_telepon`, `email`, `alamat`, `provinsi`, `kabupaten`, `kode_pos`, `pendidikan_terakhir`, `npwp`, `id_jurusan`, `mapel_utama`, `jabatan_tugas`, `apakah_aktif`) VALUES\n";
$sql .= implode(",\n", $guruRows) . ";\n\n";

// 7. Seeding Kelas (36 kelas dari 6 jurusan)
$sql .= "INSERT INTO `kelas` (`id`, `id_jurusan`, `tingkat`, `nomor_rombel`, `nama_kelas`) VALUES\n";
$klsRows = [];
foreach ($kelasList as $k) {
    $nomorRombel = (int)($k['nomor_rombel'] ?? substr($k['nama_kelas'], strrpos($k['nama_kelas'], ' ') + 1));
    $klsRows[] = "({$k['id']}, {$k['id_jurusan']}, {$k['tingkat']}, {$nomorRombel}, '{$k['nama_kelas']}')";
}
$sql .= implode(",\n", $klsRows) . ";\n\n";

// 8. Seeding Penugasan Wali Kelas (36 wali kelas berbeda untuk 36 kelas)
$sql .= "INSERT INTO `penugasan_wali_kelas` (`id`, `id_guru`, `id_kelas`, `id_tahun_ajaran`, `apakah_aktif`, `tanggal_mulai_tugas`) VALUES\n";
$pwkRows = [];
$pwkId = 1;
foreach ($kelasList as $k) {
    $pwkRows[] = "({$pwkId}, {$k['wali_guru_id']}, {$k['id']}, 2, 1, '2026-07-13')";
    $pwkId++;
}
$sql .= implode(",\n", $pwkRows) . ";\n\n";

// 9. Seeding Siswa
$sql .= "INSERT INTO `siswa` (`id`, `id_pengguna`, `nis`, `nisn`, `nama_lengkap`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `agama`, `alamat`, `provinsi`, `kabupaten`, `kode_pos`, `nomor_telepon`, `nama_ayah`, `nama_ibu`, `pekerjaan_ortu`, `telepon_orang_tua`, `id_jurusan`, `tahun_masuk`, `foto`, `status_siswa`) VALUES\n";
$sql .= implode(",\n", $siswaRows) . ";\n\n";

// 10. Seeding Riwayat Kelas Siswa
$sql .= "INSERT INTO `riwayat_kelas_siswa` (`id`, `id_siswa`, `id_kelas`, `id_tahun_ajaran`, `status_penempatan`) VALUES\n";
$sql .= implode(",\n", $riwayatRows) . ";\n\n";

// 11. Seeding Pengurus OSIS
$sql .= "INSERT INTO `pengurus_osis` (`id`, `id_siswa`, `jabatan`, `periode`, `status_aktif`) VALUES\n";
$osisRows = [];
$osId = 1;
foreach ($osisList as $o) {
    $osisRows[] = "({$osId}, {$o['id_siswa']}, '" . addslashes($o['jabatan']) . "', '{$o['periode']}', 1)";
    $osId++;
}
$sql .= implode(",\n", $osisRows) . ";\n\n";

// 12. Seeding Mata Pelajaran (Mapel Umum & Mapel Kejuruan per Jurusan)
$sql .= "INSERT INTO `mata_pelajaran` (`id`, `kode_mapel`, `nama_mapel`, `kelompok_mapel`, `id_jurusan`) VALUES\n";
$mapelRows = [
    // Mapel Umum
    "(1, 'MP-PAI-01', 'Pendidikan Agama dan Budi Pekerti', 'umum', NULL)",
    "(2, 'MP-PPKN-01', 'Pendidikan Pancasila', 'umum', NULL)",
    "(3, 'MP-BIND-01', 'Bahasa Indonesia', 'umum', NULL)",
    "(4, 'MP-BING-01', 'Bahasa Inggris', 'umum', NULL)",
    "(5, 'MP-MTK-01', 'Matematika Terapan', 'umum', NULL)",
    "(6, 'MP-SEJ-01', 'Sejarah Indonesia', 'umum', NULL)",
    "(7, 'MP-PJOK-01', 'Pendidikan Jasmani & Olahraga', 'umum', NULL)",
    "(8, 'MP-INF-01', 'Informatika & Literasi Digital', 'umum', NULL)",
    "(9, 'MP-IPAS-01', 'Projek IPAS Terapan', 'umum', NULL)",
    "(10, 'MP-SENI-01', 'Seni Budaya & Kreativitas', 'umum', NULL)",
    // Mapel Kejuruan TKJ (id_jurusan = 1)
    "(11, 'TKJ-DASAR-01', 'Dasar-dasar Teknik Jaringan Komputer', 'kejuruan', 1)",
    "(12, 'TKJ-JAR-02', 'Administrasi Infrastruktur Jaringan', 'kejuruan', 1)",
    "(13, 'TKJ-SEC-03', 'Cybersecurity & Keamanan Jaringan', 'kejuruan', 1)",
    // Mapel Kejuruan DKV (id_jurusan = 2)
    "(14, 'DKV-NIR-01', 'Dasar Desain Grafis & Nirmana', 'kejuruan', 2)",
    "(15, 'DKV-TIP-02', 'Tipografi & Ilustrasi Digital', 'kejuruan', 2)",
    "(16, 'DKV-FOTO-03', 'Fotografi & Videografi Kejuruan', 'kejuruan', 2)",
    // Mapel Kejuruan AKC (id_jurusan = 3)
    "(17, 'AKC-DASAR-01', 'Konsep Dasar Keperawatan & Anatomi', 'kejuruan', 3)",
    "(18, 'AKC-KDM-02', 'Kebutuhan Dasar Manusia & Caregiver', 'kejuruan', 3)",
    "(19, 'AKC-KLIN-03', 'Keterampilan Dasar Tindakan Keperawatan', 'kejuruan', 3)",
    // Mapel Kejuruan FKK (id_jurusan = 4)
    "(20, 'FKK-DASAR-01', 'Dasar Farmakologi & Peracikan Resep', 'kejuruan', 4)",
    "(21, 'FKK-ALAM-02', 'Farmakognosi & Bahan Alam Medis', 'kejuruan', 4)",
    "(22, 'FKK-DISP-03', 'Pelayanan Farmasi & Manajemen Obat', 'kejuruan', 4)",
    // Mapel Kejuruan MPLB (id_jurusan = 5)
    "(23, 'MPLB-KOR-01', 'Korespondensi Bisnis & Kearsipan', 'kejuruan', 5)",
    "(24, 'MPLB-HUM-02', 'Komunikasi Humas & Protokoler', 'kejuruan', 5)",
    "(25, 'MPLB-ADM-03', 'Otomasi Tata Kelola Perkantoran', 'kejuruan', 5)",
    // Mapel Kejuruan TBSM (id_jurusan = 6)
    "(26, 'TBSM-MES-01', 'Pemeliharaan Mesin Sepeda Motor', 'kejuruan', 6)",
    "(27, 'TBSM-SAS-02', 'Pemeliharaan Sasis Sepeda Motor', 'kejuruan', 6)",
    "(28, 'TBSM-LIS-03', 'Kelistrikan Sepeda Motor & Sistem Injeksi', 'kejuruan', 6)"
];
$sql .= implode(",\n", $mapelRows) . ";\n\n";

// 13. Seeding 900 Jadwal KBM (5 JP per hari, Senin sampai Jumat)
$penugasanRows = [];
$jadwalRows = [];
$pgId = 1;
$jpId = 1;

$hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$jamSlots = [
    ['mulai' => '07:00:00', 'selesai' => '08:30:00'],
    ['mulai' => '08:30:00', 'selesai' => '10:00:00'],
    ['mulai' => '10:30:00', 'selesai' => '12:00:00'],
    ['mulai' => '12:30:00', 'selesai' => '14:00:00'],
    ['mulai' => '14:00:00', 'selesai' => '15:30:00']
];

$mapelUmum = [
    1 => 'Pendidikan Agama dan Budi Pekerti',
    2 => 'Pendidikan Pancasila',
    3 => 'Bahasa Indonesia',
    4 => 'Bahasa Inggris',
    5 => 'Matematika Terapan',
    6 => 'Sejarah Indonesia',
    7 => 'Pendidikan Jasmani & Olahraga',
    8 => 'Informatika & Literasi Digital',
    9 => 'Projek IPAS Terapan',
    10 => 'Seni Budaya & Kreativitas'
];
$guruUmumPerMapel = [
    1 => [27],
    2 => [38],
    3 => [29],
    4 => [30],
    5 => [31, 32],
    6 => [34],
    7 => [33],
    8 => [36],
    9 => [37],
    10 => [35]
];
foreach ($guruList as $guru) {
    if ($guru['kode_peran'] !== 'guru_pengajar') {
        continue;
    }

    $mapelId = array_search($guru['mapel'], $mapelUmum, true);
    if ($mapelId !== false) {
        $guruUmumPerMapel[$mapelId][] = $guru['id'];
    }
}

$guruPerMapel = $guruUmumPerMapel;
$mapelKejuruanPerJurusan = [];
foreach ($jurusanList as $jurusan) {
    $mapelKejuruanPerJurusan[$jurusan['id']] = [];
    for ($index = 0; $index < 3; $index++) {
        $mapelId = 11 + (($jurusan['id'] - 1) * 3) + $index;
        $guruLamaId = 9 + (($jurusan['id'] - 1) * 3) + $index;
        $guruBaruId = 41 + (($jurusan['id'] - 1) * 3) + $index;
        $mapelKejuruanPerJurusan[$jurusan['id']][] = $mapelId;
        $guruPerMapel[$mapelId] = [$guruLamaId, $guruBaruId];
    }
}

foreach ($guruPerMapel as $mapelId => $guruIds) {
    $guruPerMapel[$mapelId] = array_values(array_unique($guruIds));
}

mt_srand(20261102);
$kelasPerJurusan = [];
foreach ($kelasList as $kelas) {
    $kelasPerJurusan[$kelas['id_jurusan']][] = $kelas;
}

$penugasanIdByKunci = [];
$penugasanPertamaPerKelas = [];
foreach ($hariList as $hariIndex => $hari) {
    $jadwalHarian = [];

    foreach ($kelasPerJurusan as $jurusanId => $kelasJurusan) {
        shuffle($kelasJurusan);
        $mapelKejuruan = $mapelKejuruanPerJurusan[$jurusanId];
        shuffle($mapelKejuruan);

        foreach ($kelasJurusan as $indexKelas => $kelas) {
            $slotMapel = [];
            $slotPraktik = ($indexKelas + $hariIndex) % count($mapelKejuruan);

            foreach ($mapelKejuruan as $indexMapel => $mapelId) {
                $slot = ($indexKelas + (2 * $indexMapel) + $hariIndex) % count($jamSlots);
                $slotMapel[$slot] = [
                    'id_mapel' => $mapelId,
                    'apakah_lab' => !empty($jurusanList[$jurusanId - 1]['labs'])
                        && $indexMapel === $slotPraktik
                ];
            }

            $slotUmum = array_values(array_diff(array_keys($jamSlots), array_keys($slotMapel)));
            foreach ($slotUmum as $slot) {
                $slotMapel[$slot] = [
                    'id_mapel' => null,
                    'apakah_lab' => false
                ];
            }

            $jadwalHarian[$kelas['id']] = [
                'kelas' => $kelas,
                'slot_mapel' => $slotMapel
            ];
        }
    }

    $mapelUmumPerKelasPerHari = [];
    foreach (array_keys($jamSlots) as $slot) {
        $kelasMapelUmum = [];
        foreach ($jadwalHarian as $idKelas => $jadwalKelas) {
            if ($jadwalKelas['slot_mapel'][$slot]['id_mapel'] === null) {
                $kelasMapelUmum[] = $idKelas;
            }
        }
        shuffle($kelasMapelUmum);
        $jumlahPerMapel = array_fill_keys(array_keys($mapelUmum), 0);

        foreach ($kelasMapelUmum as $idKelas) {
            $kandidatMapel = array_values(array_diff(array_keys($mapelUmum), $mapelUmumPerKelasPerHari[$idKelas] ?? []));
            shuffle($kandidatMapel);
            usort($kandidatMapel, static function (int $a, int $b) use (&$jumlahPerMapel): int {
                return $jumlahPerMapel[$a] <=> $jumlahPerMapel[$b];
            });
            $mapelId = $kandidatMapel[0] ?? null;
            if ($mapelId === null) {
                throw new RuntimeException("Tidak ada mapel umum unik untuk kelas {$idKelas}, {$hari}, JP " . ($slot + 1) . '.');
            }

            $jadwalHarian[$idKelas]['slot_mapel'][$slot]['id_mapel'] = $mapelId;
            $mapelUmumPerKelasPerHari[$idKelas][] = $mapelId;
            $jumlahPerMapel[$mapelId]++;
        }
    }

    foreach (array_keys($jamSlots) as $slot) {
        $kelasDiSlot = array_values($jadwalHarian);
        shuffle($kelasDiSlot);
        $guruTerpakai = [];
        $ruanganTerpakai = [];

        foreach ($kelasDiSlot as $jadwalKelas) {
            $kelas = $jadwalKelas['kelas'];
            $slotJadwal = $jadwalKelas['slot_mapel'][$slot];
            $mapelId = $slotJadwal['id_mapel'];
            $kandidatGuru = $guruPerMapel[$mapelId] ?? [];
            shuffle($kandidatGuru);

            $guruId = null;
            foreach ($kandidatGuru as $kandidat) {
                if (!isset($guruTerpakai[$kandidat])) {
                    $guruId = $kandidat;
                    break;
                }
            }
            if ($guruId === null) {
                throw new RuntimeException("Tidak ada guru bebas untuk mapel {$mapelId}, {$hari}, JP " . ($slot + 1) . '.');
            }

            $isLab = $slotJadwal['apakah_lab'];
            if ($isLab) {
                $kandidatRuangan = $jurusanList[$kelas['id_jurusan'] - 1]['labs'];
                shuffle($kandidatRuangan);
                $ruangan = null;
                foreach ($kandidatRuangan as $kandidat) {
                    if (!isset($ruanganTerpakai[$kandidat])) {
                        $ruangan = $kandidat;
                        break;
                    }
                }
                if ($ruangan === null) {
                    throw new RuntimeException("Tidak ada ruangan lab bebas untuk {$kelas['nama_kelas']}, {$hari}, JP " . ($slot + 1) . '.');
                }
            } else {
                $ruangan = 'Ruang Teori ' . $kelas['nama_kelas'];
            }

            $guruTerpakai[$guruId] = true;
            $ruanganTerpakai[$ruangan] = true;
            $kunciPenugasan = "2:3:{$guruId}:{$mapelId}:{$kelas['id']}";
            if (!isset($penugasanIdByKunci[$kunciPenugasan])) {
                $penugasanIdByKunci[$kunciPenugasan] = $pgId;
                $penugasanRows[] = "({$pgId}, 2, 3, {$guruId}, {$mapelId}, {$kelas['id']})";
                $pgId++;
            }

            $idPenugasan = $penugasanIdByKunci[$kunciPenugasan];
            $penugasanPertamaPerKelas[$kelas['id']] ??= $idPenugasan;
            $waktu = $jamSlots[$slot];
            $jadwalRows[] = "({$jpId}, {$idPenugasan}, '{$hari}', '{$waktu['mulai']}', '{$waktu['selesai']}', '" . addslashes($ruangan) . "', " . (int)$isLab . ')';
            $jpId++;
        }
    }
}

$sql .= "-- Jadwal KBM: 5 JP x 90 menit per hari, Senin-Jumat; istirahat 10:00-10:30 dan 12:00-12:30.\n";
$sql .= "-- Penugasan guru dipakai ulang untuk sesi mapel yang sama pada kelas dan semester yang sama.\n";
$sql .= "INSERT INTO `penugasan_guru` (`id`, `id_tahun_ajaran`, `id_semester`, `id_guru`, `id_mapel`, `id_kelas`) VALUES\n";
$sql .= implode(",\n", $penugasanRows) . ";\n\n";

$sql .= "INSERT INTO `jadwal_pelajaran` (`id`, `id_penugasan_guru`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`, `apakah_lab`) VALUES\n";
$sql .= implode(",\n", $jadwalRows) . ";\n\n";

// 14. Seeding Nilai Siswa & Kehadiran Siswa
$nilaiRows = [];
$kehadiranRows = [];
$nilId = 1;
$kehId = 1;

foreach ($siswaList as $s) {
    $sakit = rand(0, 3);
    $izin  = rand(0, 2);
    $alfa  = rand(0, 1);
    $kehadiranRows[] = "({$kehId}, {$s['id']}, 3, {$sakit}, {$izin}, {$alfa}, 'Kehadiran Aktif Semester Ganjil')";
    $kehId++;
    
    $kId = $s['id_kelas'];
    $tugas1 = rand(80, 95);
    $uts1   = rand(78, 92);
    $uas1   = rand(80, 95);
    $prak1  = rand(85, 96);
    $akhir1 = round(($tugas1 * 0.2) + ($uts1 * 0.2) + ($uas1 * 0.2) + ($prak1 * 0.4), 2);
    $mutu1  = $akhir1 >= 90 ? 'A' : ($akhir1 >= 80 ? 'B' : 'C');
    
    $targetPgId = $penugasanPertamaPerKelas[$kId];
    $nilaiRows[] = "({$nilId}, {$s['id']}, {$targetPgId}, 3, {$tugas1}, {$uts1}, {$uas1}, {$prak1}, {$akhir1}, '{$mutu1}', 'final', 1, NOW())";
    $nilId++;
}

$sql .= "INSERT INTO `kehadiran_siswa` (`id`, `id_siswa`, `id_semester`, `sakit`, `izin`, `alfa`, `catatan`) VALUES\n";
$sql .= implode(",\n", $kehadiranRows) . ";\n\n";

$sql .= "INSERT INTO `nilai_siswa` (`id`, `id_siswa`, `id_penugasan_guru`, `id_semester`, `nilai_tugas`, `nilai_uts`, `nilai_uas`, `nilai_praktik`, `nilai_akhir`, `predikat_huruf`, `status_nilai`, `disahkan_oleh`, `disahkan_pada`) VALUES\n";
$sql .= implode(",\n", $nilaiRows) . ";\n\n";

// 15. Seeding Kategori Event & Event Sekolah
$sql .= "INSERT INTO `kategori_event` (`id`, `nama_kategori`, `slug`) VALUES\n";
$sql .= "(1, 'Akademik & Ujian', 'akademik-ujian'),\n";
$sql .= "(2, 'Lomba & Kejuaraan', 'lomba-kejuaraan'),\n";
$sql .= "(3, 'Organisasi & OSIS', 'organisasi-osis'),\n";
$sql .= "(4, 'Pentas Seni & Budaya', 'pentas-seni-budaya'),\n";
$sql .= "(5, 'Pengumuman Resmi', 'pengumuman-resmi');\n\n";

$sql .= "INSERT INTO `event_sekolah` (`id`, `judul`, `slug`, `id_kategori`, `deskripsi`, `lokasi`, `waktu_mulai`, `waktu_selesai`, `foto_poster`, `penyelenggara`, `status`, `dibuat_oleh_id_pengguna`, `disetujui_oleh_id_pengguna`, `disetujui_pada`, `alasan_penolakan`, `dibuat_pada`) VALUES\n";
$sql .= "(1, 'Gebyar Dies Natalis & Gelar Karya Vokasi SMKN 9 Kabupaten Tangerang', 'gebyar-dies-natalis-smkn9-2026', 4, 'Pameran inovasi kejuruan TKJ, DKV, AKC, FKK, MPLB, dan TBSM menampilkan hasil karya siswa, bursa kerja khusus, dan pentas seni pelajar se-Kabupaten Tangerang.', 'Graha Utama SMKN 9 Kabupaten Tangerang', '2026-11-15 08:00:00', '2026-11-17 16:00:00', 'poster_dies_natalis_2026.jpg', 'OSIS SMKN 9 Kabupaten Tangerang', 'dipublikasikan', 5, 1, '2026-10-10 09:00:00', NULL, '2026-10-05 10:00:00'),\n";
$sql .= "(2, 'Turnamen Futsal & E-Sport Cup Pelajar Kejuruan 2026', 'turnamen-futsal-esport-cup-2026', 3, 'Kompetisi persahabatan antar rombel dan sekolah mitra untuk menumbuhkan sportivitas, kepemimpinan, dan kerja sama tim.', 'Lapangan Olahraga Serbaguna', '2026-12-10 07:30:00', '2026-12-14 15:30:00', 'poster_futsal_esport_2026.jpg', 'Pengurus OSIS SMKN 9', 'dipublikasikan', 5, 1, '2026-10-15 14:20:00', NULL, '2026-10-12 11:30:00'),\n";
$sql .= "(3, 'Lomba Inovasi Teknologi Tepat Guna & Desain Kreatif Vokasi', 'lomba-inovasi-teknologi-vokasi', 2, 'Ajang adu gagasan kreatif siswa bidang IoT Jaringan, Animasi Digital, Formulasi Herbal, dan Modifikasi Ramah Lingkungan.', 'Aula Multimedia SMKN 9', '2026-11-28 08:30:00', '2026-11-28 15:00:00', 'poster_lomba_inovasi_2026.jpg', 'Divisi IPTEK OSIS', 'menunggu_persetujuan', 5, NULL, NULL, NULL, '2026-10-20 13:00:00'),\n";
$sql .= "(4, 'Bakti Sosial & Layanan Cek Kesehatan Gratis Caregiver & Farmasi', 'baksos-kesehatan-caregiver-farmasi', 5, 'Penyuluhan pola hidup bersih dan sehat, pemeriksaan tensi/gula darah gratis, serta pembagian suplemen oleh siswa AKC dan FKK.', 'Desa Binaan Tigaraksa, Kab. Tangerang', '2026-12-05 08:00:00', '2026-12-05 13:00:00', 'poster_baksos_kesehatan.jpg', 'Pokja Kesiswaan & OSIS', 'draf', 5, NULL, NULL, NULL, '2026-10-25 09:15:00');\n\n";

// ==============================================================================
// 16. PENAMBAHAN FOREIGN KEY KAPROG (Dijalankan setelah seluruh data guru masuk)
// ==============================================================================
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "-- Relasi Kaprog Jurusan ke Guru (Setelah data guru terisi lengkap)\n";
$sql .= "-- ------------------------------------------------------------------------------\n";
$sql .= "ALTER TABLE `jurusan` ADD CONSTRAINT `fk_jurusan_guru_kaprog` FOREIGN KEY (`id_guru_kaprog`) REFERENCES `guru` (`id`) ON DELETE SET NULL;\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$sql .= "-- ======================== SELESAI ========================\n";

file_put_contents(__DIR__ . '/04_DATABASE_DDL_DUMMY_DATA.sql', $sql);
echo "SQL file docs/04_DATABASE_DDL_DUMMY_DATA.sql generated successfully! Bytes: " . strlen($sql) . "\n";
