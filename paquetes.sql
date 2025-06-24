-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-06-2025 a las 23:07:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `agencia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetes`
--

CREATE TABLE `paquetes` (
  `ID_Reserva` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `precio` int(100) DEFAULT NULL,
  `tipo` enum('estadía','pasaje','auto','completo') DEFAULT NULL,
  `destino` varchar(100) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paquetes`
--

INSERT INTO `paquetes` (`ID_Reserva`, `nombre`, `descripcion`, `precio`, `tipo`, `destino`, `imagen`) VALUES
(3, 'New York', 'Nueva York, también conocida como la Ciudad de Nueva York (NYC), es la ciudad más poblada de los Estados Unidos y un centro global de cultura, finanzas, moda, entretenimiento y comercio. Se encuentra ', 45000, 'estadía', 'EEUU', 'imagenes/newyork.jfif'),
(4, 'Tandil', 'Lugar soñado, lleno de lugares turisticos, disfruta de sus hermosos paisajes serranos, y comete un buen chorizo ', 34000, 'estadía', 'Tandil', 'imagenes/tandil.jfif'),
(5, 'Alquiler de auto', 'Mclaren alquilo x hora', 2000, 'auto', 'EEUU', 'imagenes/mclaren.jfif'),
(6, 'Visita DisneyWorld', 'Disfruta de un viaje en familia en uno de las mejores atracciones turisticas del mundo', 130000, 'pasaje', 'EEUU', 'imagenes/disney.jfif');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  ADD PRIMARY KEY (`ID_Reserva`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  MODIFY `ID_Reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
