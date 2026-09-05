-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 05 Sep 2026 pada 16.40
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pengadaan_aset`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_permintaan`
--

CREATE TABLE `detail_permintaan` (
  `id` int(11) NOT NULL,
  `permintaan_id` int(11) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `jumlah_minta` int(11) NOT NULL,
  `jumlah_approved` int(11) DEFAULT 0,
  `jumlah_vendor` int(11) DEFAULT 0,
  `satuan` varchar(50) NOT NULL,
  `sifat_barang` enum('Habis_Pakai','Tidak_Habis_Pakai') NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_permintaan`
--

INSERT INTO `detail_permintaan` (`id`, `permintaan_id`, `nama_barang`, `spesifikasi`, `jumlah_minta`, `jumlah_approved`, `jumlah_vendor`, `satuan`, `sifat_barang`, `keterangan`) VALUES
(2, 2, 'pena', 'warna hijau ', 3, 1, 1, 'pcs', 'Habis_Pakai', 'bos lake'),
(3, 1, 'pulpen', 'warna hijau ', 100, 100, 0, 'pcs', 'Habis_Pakai', 'pulpen tanda tangan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventaris`
--

CREATE TABLE `inventaris` (
  `id` int(11) NOT NULL,
  `kode_inventaris` varchar(100) NOT NULL,
  `nama_barang` varchar(200) NOT NULL,
  `spesifikasi` text DEFAULT NULL,
  `nomor_seri` varchar(100) DEFAULT NULL,
  `bidang_pemegang_id` int(11) NOT NULL,
  `tanggal_pembelian` date NOT NULL,
  `sumber_dana` varchar(100) NOT NULL,
  `status_kelayakan` enum('Baik','Rusak Ringan','Rusak Berat','Dalam Perbaikan','Dihapuskan') DEFAULT 'Baik',
  `vendor_pengadaan_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permintaan_barang`
--

CREATE TABLE `permintaan_barang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tanggal_pengajuan` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Approved_Biro_Umum','Sent_to_Vendor','Confirmed_by_Vendor','Retur','Completed','Rejected') DEFAULT 'Pending',
  `catatan_biro_umum` text DEFAULT NULL,
  `sumber_dana` varchar(100) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `permintaan_barang`
--

INSERT INTO `permintaan_barang` (`id`, `user_id`, `tanggal_pengajuan`, `status`, `catatan_biro_umum`, `sumber_dana`, `vendor_id`) VALUES
(1, 1, '2026-09-05 18:08:00', 'Pending', NULL, NULL, NULL),
(2, 1, '2026-09-05 18:13:16', '', 'jai that', '', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_pemeliharaan`
--

CREATE TABLE `riwayat_pemeliharaan` (
  `id` int(11) NOT NULL,
  `inventaris_id` int(11) NOT NULL,
  `tanggal_servis` date NOT NULL,
  `jenis_pemeliharaan` enum('Rutin','Perbaikan Kerusakan','Penggantian Part') NOT NULL,
  `deskripsi_pekerjaan` text NOT NULL,
  `part_yang_diganti` text DEFAULT NULL,
  `vendor_pemeliharaan_id` int(11) DEFAULT NULL,
  `biaya_servis` decimal(15,2) DEFAULT 0.00,
  `status_pemeliharaan` enum('Pending','Sedang Dikerjakan','Selesai') DEFAULT 'Selesai',
  `jadwal_servis_berikutnya` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_perubahan`
--

CREATE TABLE `riwayat_perubahan` (
  `id` int(11) NOT NULL,
  `permintaan_id` int(11) NOT NULL,
  `aktor` varchar(50) NOT NULL,
  `aktor_id` int(11) NOT NULL,
  `deskripsi_perubahan` text NOT NULL,
  `waktu_perubahan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pemohon','biro_umum','pengadaan','vendor','perlengkapan','super_admin') NOT NULL,
  `bidang` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `bidang`, `created_at`) VALUES
(1, 'Pemohon Bidang', 'pemohon@app.com', '$2y$10$SaPJyrS1qhH0eP0FS/HMQOX5d4r69bLwrNMsiYlf/dCHl/GmQ9lg2', 'pemohon', 'Bidang Akademik', '2026-09-05 10:38:17'),
(2, 'Staff Biro Umum', 'biro@app.com', '$2y$10$SaPJyrS1qhH0eP0FS/HMQOX5d4r69bLwrNMsiYlf/dCHl/GmQ9lg2', 'biro_umum', 'Biro Umum', '2026-09-05 10:38:17'),
(3, 'Staff Pengadaan', 'pengadaan@app.com', '$2y$10$SaPJyrS1qhH0eP0FS/HMQOX5d4r69bLwrNMsiYlf/dCHl/GmQ9lg2', 'pengadaan', NULL, '2026-09-05 10:38:17'),
(4, 'Mitra Vendor', 'vendor@app.com', '$2y$10$SaPJyrS1qhH0eP0FS/HMQOX5d4r69bLwrNMsiYlf/dCHl/GmQ9lg2', 'vendor', NULL, '2026-09-05 10:38:17'),
(5, 'Staff Biro Perlengkapan', 'perlengkapan@app.com', '$2y$10$SaPJyrS1qhH0eP0FS/HMQOX5d4r69bLwrNMsiYlf/dCHl/GmQ9lg2', 'perlengkapan', 'Perlengkapan', '2026-09-05 10:38:17'),
(6, 'Super Administrator', 'admin@app.com', '$2y$10$MBjdDRVeoJS4DsfDiNKUpuG9ehNKrf41Kj3/77IH5axtjnTYlwYyy', 'super_admin', NULL, '2026-09-05 11:25:48'),
(7, 'Arif Maula', 'arif@app.com', '$2y$10$pKlwRejTq1uEYEjHsM9mJOGNF1lptWIoisp0Zu/d9eiHDD7QVb7lm', 'pemohon', 'Dosen S2 Fikom', '2026-09-05 11:42:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `vendor`
--

CREATE TABLE `vendor` (
  `id` int(11) NOT NULL,
  `nama_vendor` varchar(150) NOT NULL,
  `kontak` varchar(50) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `jenis_vendor` enum('pengadaan_barang','pemeliharaan_aset','keduanya') DEFAULT 'pengadaan_barang',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_permintaan`
--
ALTER TABLE `detail_permintaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permintaan_id` (`permintaan_id`);

--
-- Indeks untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_inventaris` (`kode_inventaris`),
  ADD KEY `bidang_pemegang_id` (`bidang_pemegang_id`),
  ADD KEY `vendor_pengadaan_id` (`vendor_pengadaan_id`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `permintaan_barang_ibfk_2` (`vendor_id`);

--
-- Indeks untuk tabel `riwayat_pemeliharaan`
--
ALTER TABLE `riwayat_pemeliharaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventaris_id` (`inventaris_id`),
  ADD KEY `vendor_pemeliharaan_id` (`vendor_pemeliharaan_id`);

--
-- Indeks untuk tabel `riwayat_perubahan`
--
ALTER TABLE `riwayat_perubahan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permintaan_id` (`permintaan_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_permintaan`
--
ALTER TABLE `detail_permintaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `riwayat_pemeliharaan`
--
ALTER TABLE `riwayat_pemeliharaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `riwayat_perubahan`
--
ALTER TABLE `riwayat_perubahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `vendor`
--
ALTER TABLE `vendor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_permintaan`
--
ALTER TABLE `detail_permintaan`
  ADD CONSTRAINT `detail_permintaan_ibfk_1` FOREIGN KEY (`permintaan_id`) REFERENCES `permintaan_barang` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `inventaris_ibfk_1` FOREIGN KEY (`bidang_pemegang_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventaris_ibfk_2` FOREIGN KEY (`vendor_pengadaan_id`) REFERENCES `vendor` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `permintaan_barang`
--
ALTER TABLE `permintaan_barang`
  ADD CONSTRAINT `permintaan_barang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permintaan_barang_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `riwayat_pemeliharaan`
--
ALTER TABLE `riwayat_pemeliharaan`
  ADD CONSTRAINT `riwayat_pemeliharaan_ibfk_1` FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_pemeliharaan_ibfk_2` FOREIGN KEY (`vendor_pemeliharaan_id`) REFERENCES `vendor` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `riwayat_perubahan`
--
ALTER TABLE `riwayat_perubahan`
  ADD CONSTRAINT `riwayat_perubahan_ibfk_1` FOREIGN KEY (`permintaan_id`) REFERENCES `permintaan_barang` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
