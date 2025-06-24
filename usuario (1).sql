-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-06-2025 a las 23:08:23
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
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_Usuario` int(200) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `apellido` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `contra` varchar(200) NOT NULL,
  `documento` varchar(200) NOT NULL,
  `telefono` int(50) NOT NULL,
  `rol` enum('usuario','admin','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_Usuario`, `nombre`, `apellido`, `email`, `contra`, `documento`, `telefono`, `rol`) VALUES
(1, 'Juan', 'Arce', 'juan@gmail.com', 'juan', '21345657', 23737372, 'admin'),
(2, 'Coca Cola', 'Cola', 'coca@gmail.com', 'cocacola', '001234', 234332, ''),
(3, 'Agustin', 'Ardenghi', 'gordo@gmail.com', '$2y$10$3m3HAzyv1yICeveSqhP3PuoKImze9544x3IvhZ/LmHxGgmSscHFQ6', '47866542', 2147483647, 'usuario'),
(4, 'Fausto', 'Ferreiro', 'fausto@admin.com', '$2y$10$stKsX1gzXVCp4MXK1JN/PeQy/dHP1KQBh5AXk1WWlP7rxfMGcQoVa', '12345678', 24942494, 'admin'),
(5, 'Grasa', 'Duran', 'grasa@gmail.com', '$2y$10$0DYni4n.qSeR36C8Sj1t6O1/pZEA1gpIpkR8oeNNy/M00J7LtNKhS', '423423423', 24335453, 'usuario'),
(6, 'Moro', 'Basile', 'moro@gmail.com', '$2y$10$wc2aSbdRKRNhEufDcK433.dx4O7I5gUIT5MIUJcdX051wHfj5nCDq', '123456', 123456, 'usuario');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_Usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_Usuario` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
