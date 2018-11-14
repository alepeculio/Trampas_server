-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2018 at 06:57 PM
-- Server version: 10.1.31-MariaDB
-- PHP Version: 7.2.3

use u520566866_tramp;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trampas_bd`
--

-- --------------------------------------------------------

--
-- Table structure for table `colocacion`
--

CREATE TABLE `colocacion` (
  `idColocacion` int(11) NOT NULL,
  `lat` double NOT NULL,
  `lon` double NOT NULL,
  `tempMin` float DEFAULT NULL,
  `tempMax` float DEFAULT NULL,
  `humMin` float DEFAULT NULL,
  `humMax` float DEFAULT NULL,
  `tempProm` float DEFAULT NULL,
  `humProm` float DEFAULT NULL,
  `fechaInicio` datetime NOT NULL,
  `fechaFin` datetime DEFAULT NULL,
  `leishmaniasis` tinyint(1) NOT NULL DEFAULT '0',
  `trampa` int(11) NOT NULL,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trampa`
--

CREATE TABLE `trampa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(250) NOT NULL,
  `mac` varchar(30) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `trampa`
--

INSERT INTO `trampa` (`id`, `nombre`, `mac`, `activa`) VALUES
(99, 'Trampa7', '20:16:10:27:80:42', 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `correo` varchar(250) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `contrasenia` varchar(250) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`id`, `correo`, `nombre`, `apellido`, `activo`, `contrasenia`, `admin`) VALUES
(29, 'alejandropeculio@gmail.com', 'Alejandro', 'Peculio', 1, '1', 1),
(30, 'visitante@trampas.com', 'Usuario', 'visitante', 1, 'visitante', 3),
(31, 'prueba@gmail.com', 'prueba', 'prueba', 1, '1', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `colocacion`
--
ALTER TABLE `colocacion`
  ADD PRIMARY KEY (`idColocacion`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `trampa` (`trampa`);

--
-- Indexes for table `trampa`
--
ALTER TABLE `trampa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `colocacion`
--
ALTER TABLE `colocacion`
  MODIFY `idColocacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `trampa`
--
ALTER TABLE `trampa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `colocacion`
--
ALTER TABLE `colocacion`
  ADD CONSTRAINT `colocacion_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `colocacion_ibfk_2` FOREIGN KEY (`trampa`) REFERENCES `trampa` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
