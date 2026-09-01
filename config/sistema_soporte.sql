-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-07-2026 a las 06:19:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_soporte`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `asunto` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `tipo` enum('actividad','requerimiento','hallazgos') NOT NULL,
  `solicitante` varchar(100) NOT NULL,
  `medio` enum('correo','presencial','llamada') NOT NULL,
  `vobo_nya` enum('si','no') DEFAULT 'no',
  `status` enum('por asignar','pendiente','proceso','finalizado') DEFAULT 'por asignar',
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_finalizacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_ticket_id` int(11) NOT NULL,
  `extension` char(4) NOT NULL DEFAULT '0000',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_ticket`
--

CREATE TABLE `tipos_ticket` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_ticket`
--

INSERT INTO `tipos_ticket` (`id`, `nombre`) VALUES
(2, 'ITQ ACTIVIDAD'),
(3, 'ITQ CORREO'),
(1, 'ITQ CPU');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_usuario` enum('administrador','intermedio','basico') NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'assets/img/default-avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `nombre`, `tipo_usuario`, `password`, `foto_perfil`) VALUES
(1, 'admin', 'Administrador', 'administrador', '$2y$10$JsleX45lLjmR69lOYcIcF.drfS5KUoPOK9Ioh95bssKoeEhU3CkJ6', 'assets/img/default-avatar.png'),
(2, 'palonso', 'Pedro Jorge Alonso', 'basico', '$2y$10$UeOOjHmGbT7xpaYw92tT5uuKJB5dnqHMfVPBbqlhVAo8MHqyRMBn2', 'assets/img/default-avatar.png'),
(3, 'vflores', 'Vanesa Flores', 'basico', '$2y$10$n5qXeB8PaY.cS1Mtj7hjNOoGZywQ50b5NkwceJx2L6iscxSNIgdxi', 'assets/img/default-avatar.png'),
(4, 'vvazquez', 'Victor Vazquez', 'basico', '$2y$10$isbyZxW7sM7vdFkCZJF9yuR1D2psE//AHhJZH79ozJMjA.M.LlXZ.', 'assets/img/default-avatar.png'),
(5, 'nvargas', 'Netza Vargas', 'administrador', '$2y$10$sEx/UlsXufAJ3gmwXsiE7.jcC6WVGJnBIs3h9Lx6Y.SmEsquVCcIa', 'assets/img/default-avatar.png'),
(6, 'ycastillo', 'Alejandra Castillo', 'basico', '$2y$10$zQdRe.uYeRRvi3crOcRwq.h60wh1xU1uCYqC0.3xZHd.xmnOWeBYS', 'assets/img/default-avatar.png'),
(7, 'dbartolo', 'Diego Bartolo', 'basico', '$2y$10$PFJk6EOjvMr7fFjSUwFSCerzAQWT7Np1hlqK13ZKpg6W5FZWvKThu', 'assets/img/default-avatar.png'),
(8, 'cgomez', 'Cristofer Gomez', 'basico', '$2y$10$oQ.ZkUTZwfOsAn2M0A/uHeRJDY6pbTA7Opvb76lcxGTxpYxpjOJSS', 'assets/img/default-avatar.png'),
(9, 'dperez', 'Diego Perez', 'basico', '$2y$10$etH1q7jLIiVLO7uCzi5ZUOvr4PzAEIH.tfwu6U0NmDJkn3SsBWgSq', 'assets/img/default-avatar.png'),
(10, 'eaguillon', 'Estrella Aguillon', 'basico', '$2y$10$0nCJdAHoTMuWTKrfkX.0vu4pcU6IUnxxYDMc9vHRX0xB5govPopFu', 'assets/img/default-avatar.png'),
(11, 'eastorga', 'Stephany Astorga', 'intermedio', '$2y$10$JYIegod3qVJ9KZY5qRIFFuPu9sL4GChkyASgwWEWnKiBO.HCtcqVy', 'assets/img/default-avatar.png');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `tipo_ticket_id` (`tipo_ticket_id`);

--
-- Indices de la tabla `tipos_ticket`
--
ALTER TABLE `tipos_ticket`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipos_ticket`
--
ALTER TABLE `tipos_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`tipo_ticket_id`) REFERENCES `tipos_ticket` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




U
DROP TABLE IF EXISTS inventario;

-- 2. Creamos la estructura definitiva optimizada para el año 2026
CREATE TABLE inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- El nombre se almacena en mayúsculas. Agregamos codificación UTF-8 para soporte de acentos y Ñ
    nombre VARCHAR(150) NOT NULL,
    
    -- Restricción ENUM para obligar a los registros a pertenecer a categorías del sistema
    categoria ENUM('Computo', 'Redes', 'Perifericos', 'Consumibles', 'Otros') NOT NULL DEFAULT 'Otros',
    
    -- El stock se maneja con enteros. No se admiten valores nulos y por defecto inicia en 0
    stock INT NOT NULL DEFAULT 0,
    
    -- Registra de forma automática la fecha y hora exacta de cualquier alta o descuento físico
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- ÍNDICES DE RENDIMIENTO (INDEX)
    -- Optimiza drásticamente el rendimiento de la barra de filtros al buscar por coincidencia de texto
    UNIQUE KEY idx_nombre_unico (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
