-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 04:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk_bibit_padi`
--

-- --------------------------------------------------------

--
-- Table structure for table `alternatif`
--

CREATE TABLE `alternatif` (
  `id_alternatif` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alternatif`
--

INSERT INTO `alternatif` (`id_alternatif`, `kode`, `nama`) VALUES
(1, 'A1', 'Ciherang'),
(2, 'A2', 'Situbagendit'),
(3, 'A3', 'IR64'),
(4, 'A4', 'Sunggal'),
(5, 'A5', 'Inpari32');

-- --------------------------------------------------------

--
-- Table structure for table `hasil_topsis`
--

CREATE TABLE `hasil_topsis` (
  `id_hasil` int(11) NOT NULL,
  `id_alternatif` int(11) DEFAULT NULL,
  `d_plus` decimal(10,5) DEFAULT NULL,
  `d_minus` decimal(10,5) DEFAULT NULL,
  `preferensi` decimal(10,5) DEFAULT NULL,
  `ranking` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hasil_topsis`
--

INSERT INTO `hasil_topsis` (`id_hasil`, `id_alternatif`, `d_plus`, `d_minus`, `preferensi`, `ranking`) VALUES
(1, 1, 0.06119, 0.12143, 0.66494, 3),
(2, 2, 0.17732, 0.01782, 0.09130, 5),
(3, 3, 0.11741, 0.07555, 0.39151, 4),
(4, 4, 0.02871, 0.17434, 0.85861, 1),
(5, 5, 0.05606, 0.16756, 0.74930, 2);

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id_kriteria` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `bobot` decimal(10,3) NOT NULL,
  `sifat` enum('benefit','cost') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id_kriteria`, `kode`, `nama`, `bobot`, `sifat`) VALUES
(1, 'C1', 'Potensi Hasil', 0.495, 'benefit'),
(2, 'C2', 'Bobot 1000 Bulir', 0.243, 'benefit'),
(3, 'C3', 'Umur Tanaman', 0.122, 'benefit'),
(4, 'C4', 'Anakan Tanaman', 0.094, 'benefit'),
(5, 'C5', 'Tinggi Tanaman', 0.046, 'benefit');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_alternatif`
--

CREATE TABLE `nilai_alternatif` (
  `id_nilai` int(11) NOT NULL,
  `id_alternatif` int(11) DEFAULT NULL,
  `id_kriteria` int(11) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_alternatif`
--

INSERT INTO `nilai_alternatif` (`id_nilai`, `id_alternatif`, `id_kriteria`, `nilai`) VALUES
(1, 1, 1, 4),
(2, 1, 2, 5),
(3, 1, 3, 5),
(4, 1, 4, 3),
(5, 1, 5, 5),
(6, 2, 1, 2),
(7, 2, 2, 4),
(8, 2, 3, 4),
(9, 2, 4, 1),
(10, 2, 5, 3),
(11, 3, 1, 3),
(12, 3, 2, 4),
(13, 3, 3, 4),
(14, 3, 4, 5),
(15, 3, 5, 1),
(16, 4, 1, 5),
(17, 4, 2, 4),
(18, 4, 3, 5),
(19, 4, 4, 4),
(20, 4, 5, 5),
(21, 5, 1, 5),
(22, 5, 2, 4),
(23, 5, 3, 4),
(24, 5, 4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`) VALUES
(1, 'admin', 'admin123', 'Admin SPK');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_perankingan`
-- (See below for the actual view)
--
CREATE TABLE `v_perankingan` (
`kode` varchar(10)
,`nama` varchar(50)
,`preferensi` decimal(10,5)
,`ranking` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `v_perankingan`
--
DROP TABLE IF EXISTS `v_perankingan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_perankingan`  AS SELECT `a`.`kode` AS `kode`, `a`.`nama` AS `nama`, `h`.`preferensi` AS `preferensi`, `h`.`ranking` AS `ranking` FROM (`hasil_topsis` `h` join `alternatif` `a` on(`a`.`id_alternatif` = `h`.`id_alternatif`)) ORDER BY `h`.`ranking` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id_alternatif`);

--
-- Indexes for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  ADD PRIMARY KEY (`id_hasil`),
  ADD KEY `id_alternatif` (`id_alternatif`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id_kriteria`);

--
-- Indexes for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `id_alternatif` (`id_alternatif`),
  ADD KEY `id_kriteria` (`id_kriteria`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alternatif`
--
ALTER TABLE `alternatif`
  MODIFY `id_alternatif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  MODIFY `id_hasil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id_kriteria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hasil_topsis`
--
ALTER TABLE `hasil_topsis`
  ADD CONSTRAINT `hasil_topsis_ibfk_1` FOREIGN KEY (`id_alternatif`) REFERENCES `alternatif` (`id_alternatif`);

--
-- Constraints for table `nilai_alternatif`
--
ALTER TABLE `nilai_alternatif`
  ADD CONSTRAINT `nilai_alternatif_ibfk_1` FOREIGN KEY (`id_alternatif`) REFERENCES `alternatif` (`id_alternatif`),
  ADD CONSTRAINT `nilai_alternatif_ibfk_2` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
