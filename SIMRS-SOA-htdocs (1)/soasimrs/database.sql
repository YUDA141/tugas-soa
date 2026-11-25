CREATE DATABASE IF NOT EXISTS `simrssoa`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `simrssoa`;

-- 1. Tabel Pengguna (Autentikasi & RBAC)
CREATE TABLE `pengguna` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `role` ENUM('admin', 'dokter', 'pasien') NOT NULL DEFAULT 'pasien',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel Poliklinik
CREATE TABLE `poliklinik` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode_poli` VARCHAR(20) NOT NULL UNIQUE,
  `nama_poli` VARCHAR(100) NOT NULL,
  `lokasi_lantai` VARCHAR(50) DEFAULT 'Lantai 1',
  `deskripsi` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabel Dokter (Bisa terhubung ke akun Pengguna)
CREATE TABLE `dokter` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pengguna_id` INT UNSIGNED DEFAULT NULL UNIQUE,
  `poliklinik_id` INT UNSIGNED NOT NULL,
  `nip_str` VARCHAR(50) NOT NULL UNIQUE,
  `nama_dokter` VARCHAR(120) NOT NULL,
  `spesialisasi` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(25) DEFAULT NULL,
  `tarif_konsultasi` DECIMAL(12,2) NOT NULL DEFAULT 100000.00,
  `jadwal_praktik` VARCHAR(120) DEFAULT 'Senin - Jumat (08:00 - 14:00)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_dokter_pengguna`
    FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_dokter_poliklinik`
    FOREIGN KEY (`poliklinik_id`) REFERENCES `poliklinik`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Tabel Asuransi
CREATE TABLE `asuransi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode_asuransi` VARCHAR(25) NOT NULL UNIQUE,
  `nama_asuransi` VARCHAR(120) NOT NULL,
  `jenis` ENUM('BPJS', 'Swasta', 'Perusahaan') NOT NULL DEFAULT 'Swasta',
  `persen_tanggungan` TINYINT UNSIGNED NOT NULL DEFAULT 80,
  `kontak` VARCHAR(60) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Tabel Pasien (Bisa terhubung ke akun Pengguna & Asuransi)
CREATE TABLE `pasien` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pengguna_id` INT UNSIGNED DEFAULT NULL UNIQUE,
  `asuransi_id` INT UNSIGNED DEFAULT NULL,
  `no_rm` VARCHAR(30) NOT NULL UNIQUE,
  `nik` VARCHAR(25) NOT NULL UNIQUE,
  `no_polis` VARCHAR(50) DEFAULT NULL,
  `nama_pasien` VARCHAR(120) NOT NULL,
  `jenis_kelamin` ENUM('L', 'P') NOT NULL,
  `tanggal_lahir` DATE NOT NULL,
  `no_hp` VARCHAR(25) DEFAULT NULL,
  `alamat` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pasien_pengguna`
    FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pasien_asuransi`
    FOREIGN KEY (`asuransi_id`) REFERENCES `asuransi`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 6. Tabel Asosiatif / Transaksi Kunjungan & Rekam Medis
CREATE TABLE `kunjungan` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `no_kunjungan` VARCHAR(35) NOT NULL UNIQUE,
  `pasien_id` INT UNSIGNED NOT NULL,
  `dokter_id` INT UNSIGNED NOT NULL,
  `poliklinik_id` INT UNSIGNED NOT NULL,
  `asuransi_id` INT UNSIGNED DEFAULT NULL,
  `tanggal_kunjungan` DATE NOT NULL,
  `keluhan` TEXT NOT NULL,
  `diagnosa` TEXT DEFAULT NULL,
  `biaya_total` DECIMAL(12,2) NOT NULL DEFAULT 100000.00,
  `biaya_ditanggung` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `biaya_pasien` DECIMAL(12,2) NOT NULL DEFAULT 100000.00,
  `status` ENUM('menunggu', 'diperiksa', 'selesai', 'batal') NOT NULL DEFAULT 'menunggu',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_kunjungan_pasien`
    FOREIGN KEY (`pasien_id`) REFERENCES `pasien`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_kunjungan_dokter`
    FOREIGN KEY (`dokter_id`) REFERENCES `dokter`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_kunjungan_poliklinik`
    FOREIGN KEY (`poliklinik_id`) REFERENCES `poliklinik`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_kunjungan_asuransi`
    FOREIGN KEY (`asuransi_id`) REFERENCES `asuransi`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Seed Data Awal (Password semua akun demo: "password")
INSERT INTO `pengguna` (`username`, `password_hash`, `nama_lengkap`, `email`, `role`, `status`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator SIMRS', 'admin@rsharapansehat.local', 'admin', 'aktif'),
('dr.budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr. Budi Santoso, Sp.PD', 'budi.dokter@rsharapansehat.local', 'dokter', 'aktif'),
('dr.siti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr. Siti Rahmawati, Sp.A', 'siti.dokter@rsharapansehat.local', 'dokter', 'aktif'),
('pasien.andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Pratama', 'andi.pasien@rsharapansehat.local', 'pasien', 'aktif');

INSERT INTO `poliklinik` (`kode_poli`, `nama_poli`, `lokasi_lantai`, `deskripsi`) VALUES
('POL-01', 'Poli Penyakit Dalam', 'Lantai 1 Gedung A', 'Pelayanan kesehatan organ dalam dan metabolik dewasa'),
('POL-02', 'Poli Anak', 'Lantai 1 Gedung B', 'Pelayanan kesehatan bayi, anak, dan tumbuh kembang'),
('POL-03', 'Poli Gigi & Mulut', 'Lantai 2 Gedung A', 'Konservasi gigi, bedah mulut ringan, dan kesehatan gigi');

INSERT INTO `dokter` (`pengguna_id`, `poliklinik_id`, `nip_str`, `nama_dokter`, `spesialisasi`, `no_hp`, `tarif_konsultasi`, `jadwal_praktik`) VALUES
(2, 1, 'STR-100201', 'dr. Budi Santoso, Sp.PD', 'Penyakit Dalam', '081234567801', 150000.00, 'Senin - Kamis (08:00 - 13:00)'),
(3, 2, 'STR-100202', 'dr. Siti Rahmawati, Sp.A', 'Kesehatan Anak', '081234567802', 125000.00, 'Selasa - Jumat (09:00 - 14:00)');

INSERT INTO `asuransi` (`kode_asuransi`, `nama_asuransi`, `jenis`, `persen_tanggungan`, `kontak`) VALUES
('ASR-BPJS', 'BPJS Kesehatan', 'BPJS', 100, '165'),
('ASR-PRU', 'Prudential Sehat', 'Swasta', 80, '1500085'),
('ASR-PLN', 'Jaminan Kesehatan Pegawai', 'Perusahaan', 90, '021-5550199');

INSERT INTO `pasien` (`pengguna_id`, `asuransi_id`, `no_rm`, `nik`, `no_polis`, `nama_pasien`, `jenis_kelamin`, `tanggal_lahir`, `no_hp`, `alamat`) VALUES
(4, 1, 'RM-2026-0001', '1771011205980001', '0001234567890', 'Andi Pratama', 'L', '1998-05-12', '085266778801', 'Jl. Sudirman No. 12, Bengkulu'),
(NULL, 2, 'RM-2026-0002', '1771012308950002', 'PRU-882910', 'Maya Kusuma', 'P', '1995-08-23', '085266778802', 'Jl. Suprapto No. 45, Bengkulu'),
(NULL, NULL, 'RM-2026-0003', '1771010411020003', NULL, 'Rizky Ramadhan', 'L', '2002-11-04', '085266778803', 'Jl. Ahmad Yani No. 8, Bengkulu');

INSERT INTO `kunjungan` (`no_kunjungan`, `pasien_id`, `dokter_id`, `poliklinik_id`, `asuransi_id`, `tanggal_kunjungan`, `keluhan`, `diagnosa`, `biaya_total`, `biaya_ditanggung`, `biaya_pasien`, `status`) VALUES
('KNJ-20261125-01', 1, 1, 1, 1, '2026-11-25', 'Nyeri ulu hati dan pusing sejak 2 hari', 'Gastritis akut', 150000.00, 150000.00, 0.00, 'selesai'),
('KNJ-20261125-02', 2, 2, 2, 2, '2026-11-25', 'Demam naik turun dan batuk ringan', 'ISPA ringan', 125000.00, 100000.00, 25000.00, 'diperiksa'),
('KNJ-20261125-03', 3, 1, 1, NULL, '2026-11-25', 'Kontrol tekanan darah rutin', 'Menunggu pemeriksaan', 150000.00, 0.00, 150000.00, 'menunggu');
