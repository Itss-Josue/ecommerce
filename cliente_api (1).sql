-- phpMyAdmin SQL Dump
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `cliente_api`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `fecha_hora_inicio` datetime NOT NULL,
  `fecha_hora_fin` datetime NOT NULL,
  `token` varchar(30) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`id`, `id_usuario`, `fecha_hora_inicio`, `fecha_hora_fin`, `token`) VALUES
(42, 1, '2025-10-30 11:40:08', '2025-10-30 11:54:35', '&TsDqfHsYbZ]9}/acb#Zb6(FJO${$/'),
(43, 1, '2025-10-30 12:18:36', '2025-10-30 12:19:36', 'idH3U0$8UsJ/RqcqDaIDZMtF//3V0o'),
(44, 1, '2025-11-03 11:12:16', '2025-11-03 11:30:31', 'Nay2rV{/T/dx)Psgm3%&AtBSrLjv36'),
(45, 1, '2025-11-06 08:06:56', '2025-11-06 08:20:06', '6y9o{Uuq{(K[lM*ei)4$mVB5W/9OAO'),
(46, 1, '2025-11-06 08:20:14', '2025-11-06 08:21:37', 'nrLgahvuzlZV2Zlr0jXU6nxic@olPh'),
(47, 1, '2025-11-06 08:23:48', '2025-11-06 08:24:48', '{6FRmP@xUy@poVJ[nG9}U[qQFoId0O'),
(48, 1, '2025-11-06 08:30:16', '2025-11-06 08:31:16', 'hN)nA(s9RsrK64eXz$hiQ[aUKkE(qp'),
(49, 1, '2025-11-06 08:37:07', '2025-11-06 08:38:08', 'UoyGMrB*OFP2Dkwz{9kXhuEN7RR[Gw'),
(50, 1, '2025-11-06 08:39:08', '2025-11-06 08:40:09', 'Mi*rRyDw&OCOUlaf$A)gPAIy]5iB*2'),
(51, 1, '2025-11-06 09:05:16', '2025-11-06 09:06:16', 'B#Oj9jQU7AuI/DAiP(c7Zs7RREZTZX'),
(52, 1, '2025-11-06 09:42:34', '2025-11-06 09:56:12', 'L2qV$UIHlMPXnjCSf](YHL3Ncljr#K'),
(53, 1, '2025-11-06 10:01:55', '2025-11-06 10:27:55', '%1R3%FDqo1h}Qp@j}t7AO8XsbRH8c3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `token_api`
--

CREATE TABLE `token_api` (
  `token` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `token_api`
--

INSERT INTO `token_api` (`token`) VALUES
('d6ba9ab2704f1380-2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `dni` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `nombres_apellidos` varchar(140) COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telefono` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `estado` int NOT NULL DEFAULT '1',
  `password` varchar(1000) COLLATE utf8mb4_general_ci NOT NULL,
  `reset_password` int NOT NULL DEFAULT '0',
  `token_password` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `dni`, `nombres_apellidos`, `correo`, `telefono`, `estado`, `password`, `reset_password`, `token_password`, `fecha_registro`) VALUES
(1, '11112222', 'admin', 'chimaycohnj@gmail.com', '987654321', 1, '$2y$10$IeNRkcso2I60YiFEo8gKmeQEyWhTVq9TETpTgSenx380IaeWOSbv6', 1, 'lF4Ef}p89z$WqZ(z8hMz4pP]kzb)bJ', '2025-04-04 16:20:51'),
(2, '70198965', 'yucra curo ', 'yucrac@gmail.com', '12345611', 1, '$2y$10$eYm6sJB.gf6SWDfad1CDT.ZHcpTBI/3XfL/fA5KT4KXdv3ZgPSW6C', 0, '', '2025-04-04 16:54:14'),
(3, 'ss', 'ss', 'ss', 'ss', 1, '$2y$10$o4roS5UGJWwdbRqzLD7QYexqmtnZli9blSKQGfdAFXL6K7h0Ef1Bq', 0, '', '2025-04-04 21:20:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;