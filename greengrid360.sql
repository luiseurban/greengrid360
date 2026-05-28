-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 28-05-2026 a las 21:14:15
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
-- Base de datos: `greengrid360`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `esp32`
--

CREATE TABLE `esp32` (
  `id_dispositivo` int(11) NOT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `direccion_ip` varchar(50) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT 'Zona General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `esp32`
--

INSERT INTO `esp32` (`id_dispositivo`, `estado`, `direccion_ip`, `ubicacion`) VALUES
(1, 'Activo', '192.168.1.10', 'Zona General'),
(2, 'Activo', '192.168.1.11', 'Huerto de Tomates'),
(3, 'Activo', '192.168.1.12', 'Jardín Frontal (Exterior)'),
(4, 'Inactivo', '192.168.1.13', 'Zona de Compostaje');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicion_ambiental`
--

CREATE TABLE `medicion_ambiental` (
  `id_medicion` int(11) NOT NULL,
  `temperatura` float DEFAULT NULL,
  `humedad` float DEFAULT NULL,
  `humedad_suelo` float DEFAULT NULL,
  `calidad_aire` float DEFAULT NULL,
  `lluvia` float DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `id_dispositivo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `medicion_ambiental`
--

INSERT INTO `medicion_ambiental` (`id_medicion`, `temperatura`, `humedad`, `humedad_suelo`, `calidad_aire`, `lluvia`, `fecha_hora`, `id_dispositivo`) VALUES
(1, 23.8, 62.1, 58, 410.2, 0, '2026-05-28 07:00:00', 1),
(2, 25.4, 59.5, 55.3, 405.1, 0, '2026-05-28 13:00:00', 1),
(3, 24.1, 61, 54.8, 408.7, 0, '2026-05-28 19:30:00', 1),
(4, 26.5, 50.2, 32.1, 415.4, 0, '2026-05-28 08:30:00', 2),
(5, 31.2, 41.8, 22.5, 422.9, 0, '2026-05-28 14:00:00', 2),
(6, 28, 46.5, 18, 419.2, 0, '2026-05-28 18:00:00', 2),
(7, 22, 68.4, 45.2, 398.1, 0, '2026-05-28 10:00:00', 3),
(8, 20.5, 82.1, 50.7, 390.5, 35.5, '2026-05-28 15:15:00', 3),
(9, 18.3, 94.8, 85.1, 372.3, 90, '2026-05-28 16:45:00', 3),
(10, 19, 88.2, 82.4, 380, 12, '2026-05-28 21:00:00', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `contraseña`, `fecha_registro`) VALUES
(1, 'Luise', 'luise@gmail.com', '$2y$10$zBcAkVHA1DXjNxVY5BEgIOrGnX6po775eiHYcO6salgA0pVzqPLRi', '2026-05-12 08:52:53'),
(2, 'Luise', 'luises@gmail.com', '$2y$10$dojZXEPzoM9F6Hwe.IM8ZeTYt17Tqfic.iaMJ3LkEUv6RZp7yKur6', '2026-05-12 09:13:20'),
(3, 'hola', 'hola@gmail.com', '$2y$10$sBMyt6VIZvTWGlFoB4zcf.iG0E6/OUiRKdyN0kzArVfz2qTW.xWM2', '2026-05-28 08:54:24');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `esp32`
--
ALTER TABLE `esp32`
  ADD PRIMARY KEY (`id_dispositivo`);

--
-- Indices de la tabla `medicion_ambiental`
--
ALTER TABLE `medicion_ambiental`
  ADD PRIMARY KEY (`id_medicion`),
  ADD KEY `id_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `esp32`
--
ALTER TABLE `esp32`
  MODIFY `id_dispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `medicion_ambiental`
--
ALTER TABLE `medicion_ambiental`
  MODIFY `id_medicion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `medicion_ambiental`
--
ALTER TABLE `medicion_ambiental`
  ADD CONSTRAINT `medicion_ambiental_ibfk_1` FOREIGN KEY (`id_dispositivo`) REFERENCES `esp32` (`id_dispositivo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
