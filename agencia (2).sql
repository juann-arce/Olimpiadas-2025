-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-06-2025 a las 23:06:07
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
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `ID_Carrito` int(11) NOT NULL,
  `ID_Usuario` int(255) DEFAULT NULL,
  `ID_Reserva` int(100) DEFAULT NULL,
  `cantidad` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `ID_Compra` int(11) NOT NULL,
  `ID_Usuario` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` int(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`ID_Compra`, `ID_Usuario`, `fecha`, `total`) VALUES
(1, '09f3da5rdla2cmvmcanrnirdd2', '2025-06-16 20:40:48', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_items`
--

CREATE TABLE `compra_items` (
  `ID_Items` int(11) NOT NULL,
  `ID_Compras` int(11) DEFAULT NULL,
  `ID_Reserva` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `metodo_pago` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `ID_Pedido` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Estado` varchar(20) DEFAULT 'confirmado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`ID_Pedido`, `ID_Usuario`, `Fecha`, `total`, `Estado`) VALUES
(11, 3, '2025-06-22 19:56:42', 45000.00, 'Cancelado'),
(12, 3, '2025-06-22 20:00:02', 45000.00, 'Cancelado'),
(13, 3, '2025-06-22 20:31:21', 45000.00, 'Cancelado'),
(17, 3, '2025-06-23 01:51:52', 45000.00, 'Cancelado'),
(18, 6, '2025-06-23 01:57:13', 180000.00, 'Procesando'),
(19, 3, '2025-06-24 22:51:07', 2000.00, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `ID_Detalle` int(11) NOT NULL,
  `ID_Pedido` int(11) NOT NULL,
  `ID_Reserva` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Precio_Unitario_Al_Comprar` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`ID_Detalle`, `ID_Pedido`, `ID_Reserva`, `Cantidad`, `Precio_Unitario_Al_Comprar`) VALUES
(1, 11, 3, 1, 0.00),
(2, 12, 3, 1, 0.00),
(3, 13, 3, 1, 0.00),
(4, 17, 3, 1, 45000.00),
(5, 18, 3, 4, 45000.00),
(6, 19, 5, 1, 2000.00);

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
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`ID_Carrito`),
  ADD KEY `paquete_id` (`ID_Reserva`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`ID_Compra`);

--
-- Indices de la tabla `compra_items`
--
ALTER TABLE `compra_items`
  ADD PRIMARY KEY (`ID_Items`),
  ADD KEY `compra_id` (`ID_Compras`),
  ADD KEY `paquete_id` (`ID_Reserva`);

--
-- Indices de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  ADD PRIMARY KEY (`ID_Reserva`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`ID_Pedido`),
  ADD KEY `fk_pedidos_usuario` (`ID_Usuario`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`ID_Detalle`),
  ADD KEY `ID_Pedido` (`ID_Pedido`),
  ADD KEY `ID_Reserva` (`ID_Reserva`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_Usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `ID_Carrito` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `ID_Compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `compra_items`
--
ALTER TABLE `compra_items`
  MODIFY `ID_Items` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  MODIFY `ID_Reserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `ID_Pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `ID_Detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_Usuario` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`ID_Reserva`) REFERENCES `paquetes` (`ID_Reserva`);

--
-- Filtros para la tabla `compra_items`
--
ALTER TABLE `compra_items`
  ADD CONSTRAINT `compra_items_ibfk_1` FOREIGN KEY (`ID_Compras`) REFERENCES `compras` (`ID_Compra`),
  ADD CONSTRAINT `compra_items_ibfk_2` FOREIGN KEY (`ID_Reserva`) REFERENCES `paquetes` (`ID_Reserva`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `pedido_detalles_ibfk_1` FOREIGN KEY (`ID_Pedido`) REFERENCES `pedidos` (`ID_Pedido`),
  ADD CONSTRAINT `pedido_detalles_ibfk_2` FOREIGN KEY (`ID_Reserva`) REFERENCES `paquetes` (`ID_Reserva`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
