-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-06-2024 a las 22:45:28
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `procedimiento`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesional`
--

CREATE TABLE `profesional` (
  `id_profesional` int(11) NOT NULL,
  `nombre_profesional` varchar(100) NOT NULL,
  `apellido_profesional` varchar(100) NOT NULL,
  `rut_profesional` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `profesional`
--

INSERT INTO `profesional` (`id_profesional`, `nombre_profesional`, `apellido_profesional`, `rut_profesional`) VALUES
(8, 'nombre', 'y apellido no especificado', NULL),
(9, 'ARISTONY DE ARMAS', 'VICTORES', NULL),
(10, 'SANDRA', 'GARCIA SANCHEZ', NULL),
(11, 'LIXAIDA', 'CABANES', NULL),
(12, 'EDUARDO', 'BLANCO FARAMIÑAN', NULL),
(13, 'MARIELYS', 'LAREZ PEREIRA', NULL),
(14, 'JORGE CRISTIAN', 'SAAVEDRA READY', NULL),
(15, 'RICARDO', 'GONZALEZ', NULL),
(16, 'ALEJANDRO', 'ARGUELLO AREVALO', NULL),
(17, 'JANETH', 'RODRIGUEZ BETANCOURT', NULL),
(18, 'ELIOMAR', 'PINTO CORDOVA', NULL),
(19, 'CARMONA', 'ROJAS MARIO MISAEL', NULL),
(20, 'NAVAS', 'PINEDA DESIREE', NULL),
(21, 'CONTRERAS', 'DANIEL', NULL),
(22, 'JOSE', 'OLVERA SANCHEZ', NULL),
(23, 'IVAN', 'CAMPUSANO GRANDA', NULL),
(24, 'CAROLINA', 'LLANA BARZANA', NULL),
(25, 'FRANCISCO', 'ECHEVERRIA MANZO', NULL),
(26, 'SILVA', 'GARATE MARCELO FERNANDO', NULL),
(27, 'CHRISTIAN', 'SAHMKOW PAEZ', NULL),
(28, 'MARIA EUGENIA', 'BARRIONUEVO SCHILLER', NULL),
(29, 'TAHIA', 'ALVAREZ DROGUETT', NULL),
(30, 'DANIELA', 'ALLENDE ROJO', NULL),
(31, 'ODEMARIS', 'MACHADO', NULL),
(32, 'CECILIA', 'SANCHEZ', NULL),
(33, 'DIEGO', 'PEÑA', NULL),
(34, 'OSCAR', 'CONTRERAS RODRIGUEZ', NULL),
(35, 'DOUGLAS', 'GUACARAN ROMERO', NULL),
(36, 'RODRIGO', 'VILLALOBOS GARCIA', NULL),
(37, 'PAOLA', 'HERRERA DE FUENTES', NULL),
(38, 'RUBEN', 'IBARRA SANCHEZ', NULL),
(39, 'CAROLINA', 'EMACK', NULL),
(40, 'CARLOS', 'ROMAN ZAMORANO', NULL),
(41, 'INES', 'SOMAROO LUNA', NULL),
(42, 'FELIPE ANDRES', 'SALINAS SALAMANCA', NULL),
(44, 'CHRISTIAN', 'CARRILLO SARANGO', NULL),
(45, 'CAVERO CARDENAS', 'MARCO ANTONIO', NULL),
(46, 'ANDREA', 'TAPIA VENEGAS', NULL),
(47, 'MASSIMO', 'FORTE FILIPPI', NULL),
(48, 'RODRIGO', 'VILLALOBOS GARCIA', NULL),
(49, 'JUANA', 'HERNANDEZ', NULL),
(50, 'MARIA FERNANDA', 'SCRIMINI TOSCANO', NULL),
(51, 'NOEL', 'VALERY', NULL),
(52, 'GRECIA JOSEFINA', 'RIVERO SIERRALTA', NULL),
(53, 'TAMARA', 'MUÑOZ ZAVALA', NULL),
(54, 'FANNY', 'MEJIAS', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `profesional`
--
ALTER TABLE `profesional`
  ADD PRIMARY KEY (`id_profesional`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `profesional`
--
ALTER TABLE `profesional`
  MODIFY `id_profesional` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
