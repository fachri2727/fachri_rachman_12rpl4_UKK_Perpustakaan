-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 21, 2026 at 11:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `perpustakaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_admin` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `username`, `password`, `nama_admin`) VALUES
(8, 'admin', '123456', 'Fachri Rachman');

-- --------------------------------------------------------

--
-- Table structure for table `tb_buku`
--

CREATE TABLE `tb_buku` (
  `id_buku` int(11) NOT NULL,
  `judul_buku` varchar(100) NOT NULL,
  `pengarang` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `tahun_terbit` year(4) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_buku`
--

INSERT INTO `tb_buku` (`id_buku`, `judul_buku`, `pengarang`, `penerbit`, `tahun_terbit`, `stok`, `status`) VALUES
(5, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', '2005', 1, 'dipinjam'),
(6, 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', '1980', 2, 'tersedia'),
(7, 'Cantik Itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', '2002', 1, 'tersedia'),
(8, 'Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', '2009', 1, 'tersedia'),
(9, 'Pulang', 'Tere Liye', 'Republika Penerbit', '2015', 1, 'tersedia'),
(11, 'Filosofi Teras', 'Henry Manampiring', 'Kompas', '2018', 1, 'tersedia'),
(12, 'Pemrograman Dasar untuk SMK Kelas X', 'Kemendikbud', 'Pusat Perbukuan', '2021', 1, 'tersedia'),
(13, 'Dasar Desain Grafis', 'Kemendikbud', 'Pusat Perbukuan', '2020', 1, 'tersedia'),
(14, 'Produk Kreatif dan Kewirausahaan', 'Kemendikbud', 'Pusat Perbukuan', '2019', 1, 'tersedia'),
(15, 'Pemrograman Web dan Perangkat Bergerak', 'Kemendikbud', 'Pusat Perbukuan', '2021', 1, 'tersedia'),
(16, 'Administrasi Infrastruktur Jaringan', 'Kemendikbud', 'Pusat Perbukuan', '2020', 1, 'dipinjam'),
(17, 'Basis Data', 'Kemendikbud', 'Pusat Perbukuan', '2019', 2, 'tersedia'),
(18, 'Komputer Akuntansi', 'Kemendikbud', 'Pusat Perbukuan', '2020', 1, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `tb_peminjaman`
--

CREATE TABLE `tb_peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tgl_pinjam` datetime NOT NULL,
  `tgl_pengembalian` datetime NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'menunggu',
  `tgl_ajukan_kembali` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_peminjaman`
--

INSERT INTO `tb_peminjaman` (`id_peminjaman`, `id_siswa`, `id_buku`, `tgl_pinjam`, `tgl_pengembalian`, `status`, `tgl_ajukan_kembali`) VALUES
(1, 14, 17, '2026-10-11 00:08:50', '2026-10-08 18:24:40', 'dikembalikan', NULL),
(2, 1, 7, '2026-10-11 00:11:49', '2026-10-18 00:11:49', 'dikembalikan', NULL),
(3, 1, 5, '2026-10-11 00:12:00', '2026-10-18 00:12:00', 'dikembalikan', NULL),
(4, 14, 17, '2026-10-11 00:16:20', '2026-10-18 00:16:20', 'disetujui', NULL),
(5, 14, 16, '2026-10-11 00:16:26', '2026-10-18 00:16:26', 'disetujui', NULL),
(6, 1, 6, '2026-10-11 00:39:38', '2026-10-18 00:39:38', 'dikembalikan', NULL),
(7, 1, 5, '2026-10-14 08:38:54', '2026-10-21 08:38:54', 'disetujui', NULL),
(8, 1, 13, '2026-10-14 08:39:07', '2026-10-21 08:39:07', 'dikembalikan', '2026-10-14 08:42:07');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pengembalian`
--

CREATE TABLE `tb_pengembalian` (
  `id_pengembalian` int(11) NOT NULL,
  `id_peminjaman` int(11) NOT NULL,
  `tgl_pengembalian` datetime NOT NULL,
  `denda` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pengembalian`
--

INSERT INTO `tb_pengembalian` (`id_pengembalian`, `id_peminjaman`, `tgl_pengembalian`, `denda`) VALUES
(1, 1, '2026-10-11 00:10:27', 0),
(2, 3, '2026-10-11 00:40:33', 0),
(3, 2, '2026-10-11 17:49:00', 0),
(4, 6, '2026-10-11 17:49:06', 0),
(5, 8, '2026-10-14 08:42:07', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tb_siswa`
--

CREATE TABLE `tb_siswa` (
  `id_siswa` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama_siswa` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `kelas` varchar(35) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'siswa',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_siswa`
--

INSERT INTO `tb_siswa` (`id_siswa`, `nis`, `nama_siswa`, `username`, `password`, `kelas`, `role`, `status_aktif`) VALUES
(1, '0123456789', 'Fachri Rachman', 'fachri', '123456', '12 RPL 4', 'siswa', 1),
(8, '1234564321', 'Sasa', 'Inoue', 'siswa1234', '12 RPL 4', 'siswa', 1),
(9, '1234567888', 'Lionel Messi', 'Pessi', 'messi punya pildun', '12 RPL 4', 'siswa', 1),
(12, '1231231233', 'Michael Jackson', 'HEHE', 'billie jean', '12 RPL 4', 'siswa', 1),
(14, '1231231414', 'Sukuna', 'Siswa', 'siswa123', '12 RPL 4', 'siswa', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `uq_admin_username` (`username`);

--
-- Indexes for table `tb_buku`
--
ALTER TABLE `tb_buku`
  ADD PRIMARY KEY (`id_buku`);

--
-- Indexes for table `tb_peminjaman`
--
ALTER TABLE `tb_peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `idx_siswa_status` (`id_siswa`,`status`),
  ADD KEY `idx_buku` (`id_buku`);

--
-- Indexes for table `tb_pengembalian`
--
ALTER TABLE `tb_pengembalian`
  ADD PRIMARY KEY (`id_pengembalian`),
  ADD UNIQUE KEY `uq_peminjaman` (`id_peminjaman`);

--
-- Indexes for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `uq_siswa_nis` (`nis`),
  ADD UNIQUE KEY `uq_siswa_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_buku`
--
ALTER TABLE `tb_buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tb_peminjaman`
--
ALTER TABLE `tb_peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_pengembalian`
--
ALTER TABLE `tb_pengembalian`
  MODIFY `id_pengembalian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_siswa`
--
ALTER TABLE `tb_siswa`
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
