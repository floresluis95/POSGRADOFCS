-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-12-2025 a las 19:43:50
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
-- Base de datos: `intermag`
--
CREATE DATABASE IF NOT EXISTS `intermag` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `intermag`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cilindro`
--

CREATE TABLE `cilindro` (
  `seriecilindro` varchar(20) NOT NULL,
  `codmarcacil` int(11) DEFAULT NULL,
  `capacidad` varchar(11) DEFAULT NULL,
  `aofab` date DEFAULT NULL,
  `estado` char(1) DEFAULT '1',
  `codrecpecioncil` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `cilindro`
--

INSERT INTO `cilindro` (`seriecilindro`, `codmarcacil`, `capacidad`, `aofab`, `estado`, `codrecpecioncil`) VALUES
('479129', 2, '60 L.', '2017-01-01', '0', 1),
('480841', 2, '60 L.', '2017-01-01', '0', 1),
('480842', 2, '60 L.', '2017-01-01', '0', 1),
('ASWQD4125', 3, '40 L.', '2019-11-01', '0', 4),
('DLT7102', 1, '40 L.', '2019-01-01', '0', 2),
('DLT7153', 1, '40 L.', '2018-01-01', '0', 2),
('DLT8022', 1, '40 L.', '2018-01-01', '0', 2),
('DMF1030', 1, '40 L.', '2018-01-01', '0', 2),
('DMF1039', 1, '40 L.', '2018-01-01', '0', 2),
('DNC9164', 1, '50 L.', '2018-01-01', '0', 1),
('DND2011', 1, '50 L.', '2018-01-01', '0', 1),
('DND7101', 1, '40 L.', '2018-01-01', '0', 1),
('DNE4096', 1, '40 L.', '2017-01-01', '0', 3),
('DNF2185', 1, '40 L.', '2017-01-01', '0', 3),
('DNF2241', 1, '40 L.', '2017-01-01', '0', 3),
('DNF2354', 1, '40 L.', '2017-01-01', '0', 3),
('DNG7142', 1, '40 L.', '2018-01-01', '1', 1),
('DNG7152', 1, '40 L.', '2018-01-01', '1', 1),
('JFYFÑE4123', 3, '40 L.', '2019-11-01', '1', 4),
('JUYGFR74', 2, '80 L.', '2019-11-08', '1', 4),
('QWERT123', 1, '40 L.', '2019-11-01', '1', 4),
('QWESXD41', 1, '40 L.', '2019-11-01', '1', 4),
('VBFBGB125', 3, '50 L.', '2019-11-01', '1', 4),
('ZXCV4125', 1, '50 L.', '2019-11-01', '1', 4),
('ZXXCSS41', 1, '50 L.', '2019-11-01', '1', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cilindrop`
--

CREATE TABLE `cilindrop` (
  `idcilindrop` int(11) NOT NULL,
  `seriecilp` varchar(20) NOT NULL,
  `capacidad` varchar(15) DEFAULT NULL,
  `codmarcacil` int(11) DEFAULT NULL,
  `fechafab` date DEFAULT NULL,
  `reca` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrato`
--

CREATE TABLE `contrato` (
  `codcontrato` int(11) NOT NULL,
  `fechac` datetime DEFAULT current_timestamp(),
  `finicio` date DEFAULT NULL,
  `ffinal` date DEFAULT NULL,
  `estado` char(1) DEFAULT '1',
  `idpropvehiculo` int(11) DEFAULT NULL,
  `kitp` varchar(15) DEFAULT NULL,
  `cilindrop` varchar(15) DEFAULT NULL,
  `idusuario` int(11) DEFAULT NULL,
  `tecnico` int(11) NOT NULL,
  `marcak` varchar(15) NOT NULL,
  `marcac` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle`
--

CREATE TABLE `detalle` (
  `dsolicitudt` int(11) NOT NULL,
  `inyecctores` varchar(2) NOT NULL,
  `arranque` varchar(2) NOT NULL,
  `aceleracion` varchar(2) NOT NULL,
  `velocidad` varchar(2) NOT NULL,
  `elctrica` varchar(2) NOT NULL,
  `descripciond` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `detalle`
--

INSERT INTO `detalle` (`dsolicitudt`, `inyecctores`, `arranque`, `aceleracion`, `velocidad`, `elctrica`, `descripciond`) VALUES
(1, 'SI', 'SI', 'SI', 'NO', 'SI', 'inyectores de 3 ch.'),
(2, 'NO', 'SI', 'NO', 'SI', 'NO', 'sin obs.'),
(3, 'NO', 'NO', 'NO', 'NO', 'NO', ''),
(5, 'NO', 'NO', 'NO', 'NO', 'NO', ''),
(14, 'SI', 'NO', 'NO', 'NO', 'SI', 'sin obs.'),
(18, 'NO', 'NO', 'NO', 'NO', 'NO', ''),
(19, 'SI', 'SI', 'NO', 'SI', 'SI', 'sin obs,'),
(21, 'NO', 'NO', 'NO', 'NO', 'NO', 'sin obs.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dsolicitud`
--

CREATE TABLE `dsolicitud` (
  `codsolicitud` int(11) DEFAULT NULL,
  `seriekit` varchar(20) DEFAULT NULL,
  `seriecilindro` varchar(20) DEFAULT NULL,
  `estado` char(1) DEFAULT '1',
  `idtecnico` int(11) NOT NULL,
  `fechatrabajo` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `dsolicitud`
--

INSERT INTO `dsolicitud` (`codsolicitud`, `seriekit`, `seriecilindro`, `estado`, `idtecnico`, `fechatrabajo`) VALUES
(1, '1A880426F24', '479129', '1', 3, '2019-11-22'),
(2, '1A880569F24', '480842', '1', 3, '2019-11-26'),
(3, '1A880570F24', 'DLT8022', '1', 3, '2019-11-27'),
(5, '1A880567F24', 'DMF1030', '1', 4, '2019-11-28'),
(6, '1A880601F24', 'DMF1039', '1', 3, '2019-11-29'),
(9, '1A880608F24', 'DND2011', '1', 3, '2019-11-27'),
(14, '5769', 'DLT7102', '1', 3, '2019-12-02'),
(19, '1A880452F24', 'DNF2185', '1', 3, '2019-11-27'),
(18, '1A880600F24', 'DNF2241', '1', 4, '2019-12-05'),
(12, '1A880627F24', 'ASWQD4125', '1', 5, '2019-12-12'),
(21, '4268', 'DNF2354', '1', 3, '2019-11-29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kit`
--

CREATE TABLE `kit` (
  `seriekit` varchar(20) NOT NULL,
  `tipo` varchar(15) DEFAULT NULL,
  `estado` char(1) DEFAULT NULL,
  `codmarca` int(11) DEFAULT NULL,
  `codrecpecion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `kit`
--

INSERT INTO `kit` (`seriekit`, `tipo`, `estado`, `codmarca`, `codrecpecion`) VALUES
('123', 'CARBURADOR', '1', 1, NULL),
('1236', 'INYECCION', '1', 1, NULL),
('1A880426F24', 'INYECCION', '0', 2, 3),
('1A880436F24', 'INYECCION', '0', 2, 3),
('1A880452F24', 'INYECCION', '0', 2, 3),
('1A880567F24', 'INYECCION', '0', 2, 3),
('1A880569F24', 'INYECCION', '0', 2, 2),
('1A880570F24', 'INYECCION', '0', 2, 2),
('1A880597F24', 'INYECCION', '0', 2, 3),
('1A880600F24', 'INYECCION', '0', 2, 2),
('1A880601F24', 'INYECCION', '0', 2, 2),
('1A880608F24', 'INYECCION', '0', 2, 2),
('1A880610F24', 'INYECCION', '0', 2, 2),
('1A880627F24', 'INYECCION', '0', 2, 2),
('4009', 'CARBURADOR', '0', 1, 4),
('4268', 'CARBURADOR', '0', 1, 4),
('4268DD', 'INYECCION', '1', 2, 8),
('4274', 'CARBURADOR', '0', 1, 4),
('5136', 'INYECCION', '1', 1, NULL),
('54165', 'INYECCION', '1', 2, NULL),
('5769', 'CARBURADOR', '0', 1, 4),
('5788', 'CARBURADOR', '1', 1, 4),
('5872', 'CARBURADOR', '1', 1, 4),
('5891', 'CARBURADOR', '1', 1, 4),
('A096273', 'INYECCION', '1', 4, 1),
('A096276', 'INYECCION', '1', 4, 1),
('A096284', 'INYECCION', '1', 4, 1),
('A096290', 'INYECCION', '1', 4, 1),
('A096296', 'INYECCION', '1', 4, 1),
('A096298', 'INYECCION', '1', 4, 1),
('A096301', 'INYECCION', '1', 4, 1),
('CVCVYYMNH', 'INYECCION', '1', 3, 9),
('GJKKL', 'CARBURADOR', '1', 4, 11),
('JYJMYTN', 'INYECCION', '1', 1, 9),
('R-1600420', 'INYECCION', '1', 2, 7),
('R-1600422', 'CARBURADOR', '1', 2, 7),
('R12345', 'CARBURADOR', '1', 2, 10),
('R15006', 'CARBURADOR', '1', 1, 6),
('R1500700', 'INYECCION', '1', 1, 6),
('R1600241578', 'INYECCION', '1', 2, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kitp`
--

CREATE TABLE `kitp` (
  `idkitp` int(11) NOT NULL,
  `seriekitp` varchar(11) NOT NULL,
  `codmarca` int(11) DEFAULT NULL,
  `tipo` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `idmarca` int(11) NOT NULL,
  `descmarca` varchar(15) DEFAULT NULL,
  `estado` char(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`idmarca`, `descmarca`, `estado`) VALUES
(1, 'TOYOTA', '1'),
(2, 'NISSAN', '1'),
(3, 'SUZUKI', '1'),
(4, 'VOLKSWAGEN', '1'),
(5, 'DAYHATSU', '1'),
(10, 'GOLDEN DRAGON', '1'),
(11, 'VOLKSWAGEN', '1'),
(16, 'DODGE', '1'),
(17, 'BUS', '1'),
(18, 'MAZDA', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcacilindro`
--

CREATE TABLE `marcacilindro` (
  `codmarcacil` int(11) NOT NULL,
  `descripcioncil` varchar(15) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `marcacilindro`
--

INSERT INTO `marcacilindro` (`codmarcacil`, `descripcioncil`, `estado`) VALUES
(1, 'MAT', 1),
(2, 'KIOSHI', 1),
(3, 'INPROCIL', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcakit`
--

CREATE TABLE `marcakit` (
  `codmarca` int(11) NOT NULL,
  `descripcion` varchar(15) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `marcakit`
--

INSERT INTO `marcakit` (`codmarca`, `descripcion`, `estado`) VALUES
(1, 'ROMANO', 1),
(2, 'LANDIRENZO', 1),
(3, 'OMVL', 1),
(4, 'LOVATO', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `IdPersonal` int(11) NOT NULL,
  `CedulaIdentidad` varchar(20) NOT NULL,
  `ApellidoPaterno` varchar(50) NOT NULL,
  `ApellidoMaterno` varchar(50) NOT NULL,
  `Nombres` varchar(50) NOT NULL,
  `Direccion` varchar(200) NOT NULL,
  `Celular` int(11) NOT NULL,
  `Telefono` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`IdPersonal`, `CedulaIdentidad`, `ApellidoPaterno`, `ApellidoMaterno`, `Nombres`, `Direccion`, `Celular`, `Telefono`) VALUES
(1, '12398518', 'UÑO', 'FLORES', 'LUIS', 'URB. HUAJARA III ', 75418370, 410652),
(2, '738207', 'OJEDA', 'FLORES', 'LUCERO', 'C/ LA PAZ #35', 0, 41784),
(3, '123456', 'VARGAS', 'PEREZ', 'MATIAS', 'C/ PAGADOR Y SRGTO. FLORES #12', 74133341, 41524),
(4, '1239851', 'GONZALES', 'SOLIZ', 'FERNANDO', 'AV/ DN', 74133341, 41060),
(5, '74133341', 'OTTO', 'CALIZAYA', 'CARLOS', 'AV. SRGTO, FLORES #12', 73820731, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propietario`
--

CREATE TABLE `propietario` (
  `idpropietaro` int(11) NOT NULL,
  `ci` varchar(15) DEFAULT NULL,
  `nombre` varchar(25) DEFAULT NULL,
  `paterno` varchar(20) DEFAULT NULL,
  `materno` varchar(20) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `estado` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `propietario`
--

INSERT INTO `propietario` (`idpropietaro`, `ci`, `nombre`, `paterno`, `materno`, `telefono`, `estado`) VALUES
(1, '7412589', 'CARLOS VALVERDE', 'ROSALES', 'ROSALES', '75418604', '1'),
(2, '18525256', 'BEIMAR', 'PUMA', 'GUZMAN', '', '1'),
(3, '7412356', 'MIGUEL GONZALO', 'RAMALLO', 'VILLEGAS', '672210890', '1'),
(5, '6', 'FLORENCIO', 'VALDIVIA', 'DAZA', '78608953', '1'),
(6, '3', 'JOSE FERNANDO', 'DORADO', 'AYALA', '74119483', '1'),
(7, '3', 'LUIS ARMANDO', 'QUISPE', 'MAMANI', '73833618', '1'),
(8, '5', 'JHOVANA HILDA', 'FLORES', 'CHAVARRIA', '73820734', '1'),
(9, '1', 'KAREN HAYDE', 'CHUMACERO', 'LLAVE', '74587426', '1'),
(10, '7', 'JOSE ', 'ALCON', 'HUAYANI', '67217747', '1'),
(12, '5', 'JUAN PABLO', 'PEREZ', 'DAZA', '74145842', '1'),
(13, '4', 'ROHELIO GIL', 'CHOQUE', 'NINA', '77423667', '1'),
(15, '1', 'LUIS', 'FLORES', 'FLORES', '75418370', '1'),
(21, '6', 'DAVID', 'FERNANDEZ', 'PEREZ', '', '1'),
(28, '4187856A CBBA.', 'PEDRO CARLOS', 'PAREDES', 'CASAS', '5241352', '1'),
(29, '7355172 OR', 'JHONATAN', 'BORDA', 'FLORES', '37255', '1'),
(30, '52417558A CBBA.', 'JOSE ANGEL', 'MEDRANO', 'CUELLAR', '75418370', '1'),
(31, '296288955 CBBA.', 'CARLOS FERNANDO', 'COLQUE', 'RAMIREZ', '74158962', '1'),
(32, '75418370 OR', 'LUIS', 'FLORES', 'FLORES', '741258741', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `propvehiculo`
--

CREATE TABLE `propvehiculo` (
  `idpropvehiculo` int(11) NOT NULL,
  `Idpropietario` int(11) NOT NULL,
  `idvehiculo` int(11) NOT NULL,
  `estado` char(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `propvehiculo`
--

INSERT INTO `propvehiculo` (`idpropvehiculo`, `Idpropietario`, `idvehiculo`, `estado`) VALUES
(1, 1, 1, '1'),
(2, 2, 2, '1'),
(3, 3, 3, '1'),
(5, 5, 5, '1'),
(6, 5, 6, '1'),
(8, 9, 8, '1'),
(9, 10, 9, '1'),
(12, 12, 12, '1'),
(14, 13, 14, '1'),
(15, 1, 15, '1'),
(16, 1, 16, '1'),
(19, 28, 19, '1'),
(20, 29, 20, '1'),
(21, 31, 21, '1'),
(22, 1, 22, '1'),
(23, 32, 23, '1'),
(24, 1, 24, '1'),
(25, 32, 25, '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recepcioncilindro`
--

CREATE TABLE `recepcioncilindro` (
  `codrecepcioncil` int(11) NOT NULL,
  `fecharecepcioncil` timestamp NULL DEFAULT current_timestamp(),
  `notadeentrega` varchar(15) NOT NULL,
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `recepcioncilindro`
--

INSERT INTO `recepcioncilindro` (`codrecepcioncil`, `fecharecepcioncil`, `notadeentrega`, `idusuario`) VALUES
(1, '2019-11-20 17:27:02', '114/2019', 2),
(2, '2019-11-20 17:33:33', '066/2019', 1),
(3, '2019-11-20 18:20:02', '115/2019', 2),
(4, '2019-11-26 01:12:48', '01/745', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recepcionkitkit`
--

CREATE TABLE `recepcionkitkit` (
  `codrecepcion` int(11) NOT NULL,
  `fecharecepcion` timestamp NULL DEFAULT current_timestamp(),
  `notadeentrega` varchar(20) NOT NULL,
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `recepcionkitkit`
--

INSERT INTO `recepcionkitkit` (`codrecepcion`, `fecharecepcion`, `notadeentrega`, `idusuario`) VALUES
(1, '2019-11-20 17:08:01', 'A-ORU-77/CON/2019', 1),
(2, '2019-11-20 17:13:13', '066/2019', 1),
(3, '2019-11-20 17:20:07', '067/2019', 1),
(4, '2019-11-20 17:23:24', '114/2014', 2),
(5, '2019-11-21 17:56:41', '01/20', 2),
(6, '2019-11-21 18:20:10', '01/2014', 2),
(7, '2019-11-23 21:27:52', '114/524', 1),
(8, '2019-11-23 21:34:38', '151kjh', 1),
(9, '2019-11-24 01:06:37', '01/741', 1),
(10, '2019-11-26 02:11:33', '458/55', 1),
(11, '2019-11-26 02:40:56', '015', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud`
--

CREATE TABLE `solicitud` (
  `codsolicitud` int(11) NOT NULL,
  `fechasolicitud` timestamp NULL DEFAULT current_timestamp(),
  `idpropvehiculo` int(11) NOT NULL,
  `estado` varchar(15) DEFAULT 'SOLICITADO',
  `idusuario` int(11) DEFAULT NULL,
  `fechaconcluido` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `solicitud`
--

INSERT INTO `solicitud` (`codsolicitud`, `fechasolicitud`, `idpropvehiculo`, `estado`, `idusuario`, `fechaconcluido`) VALUES
(1, '2019-11-20 17:39:23', 1, 'TERMINADO', 2, '2019-11-20 13:45:51'),
(2, '2019-11-20 17:40:36', 2, 'TERMINADO', 2, '2019-11-20 14:04:52'),
(3, '2019-11-20 17:42:32', 3, 'TERMINADO', 2, '2019-11-24 11:14:27'),
(5, '2019-11-20 17:52:06', 5, 'TERMINADO', 2, '2019-11-24 11:40:28'),
(6, '2019-11-20 17:54:20', 6, 'PROGRAMADO', 2, NULL),
(9, '2019-11-20 18:03:22', 9, 'PROGRAMADO', 2, NULL),
(12, '2019-11-20 18:11:55', 12, 'PROGRAMADO', 2, NULL),
(13, '2019-11-20 18:16:23', 14, 'SOLICITADO', 2, NULL),
(14, '2019-11-21 18:02:49', 15, 'TERMINADO', 2, '2019-11-21 14:07:26'),
(15, '2019-11-21 20:15:05', 16, 'SOLICITADO', 2, NULL),
(18, '2019-11-23 23:41:50', 19, 'TERMINADO', 2, '2019-11-24 01:10:52'),
(19, '2019-11-24 01:11:24', 20, 'TERMINADO', 2, '2019-11-23 21:13:47'),
(20, '2019-11-27 22:48:32', 21, 'SOLICITADO', 2, NULL),
(21, '2019-11-28 02:38:49', 22, 'TERMINADO', 2, '2019-11-27 22:39:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soltecnico`
--

CREATE TABLE `soltecnico` (
  `codsolicitud` int(11) DEFAULT NULL,
  `fechaasignacion` datetime DEFAULT current_timestamp(),
  `idusuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `soltecnico`
--

INSERT INTO `soltecnico` (`codsolicitud`, `fechaasignacion`, `idusuario`) VALUES
(NULL, '2019-11-13 08:34:19', NULL),
(NULL, '2019-11-13 08:35:34', NULL),
(NULL, '2019-11-13 08:47:36', NULL),
(NULL, '2019-11-13 08:48:01', NULL),
(NULL, '2019-11-13 08:55:03', NULL),
(NULL, '2019-11-13 11:10:38', NULL),
(NULL, '2019-11-13 11:51:30', NULL),
(NULL, '2019-11-13 12:11:14', NULL),
(NULL, '2019-11-13 12:25:21', NULL),
(NULL, '2019-11-13 12:30:12', NULL),
(NULL, '2019-11-13 12:30:15', NULL),
(NULL, '2019-11-13 16:44:59', NULL),
(NULL, '2019-11-13 18:58:02', NULL),
(NULL, '2019-11-13 19:56:45', NULL),
(NULL, '2019-11-13 21:22:21', NULL),
(NULL, '2019-11-14 08:45:34', NULL),
(NULL, '2019-11-14 08:52:27', NULL),
(NULL, '2019-11-14 09:04:34', NULL),
(NULL, '2019-11-14 09:11:04', NULL),
(NULL, '2019-11-14 09:13:22', NULL),
(NULL, '2019-11-14 09:23:42', NULL),
(NULL, '2019-11-14 10:54:34', NULL),
(NULL, '2019-11-15 08:31:36', NULL),
(NULL, '2019-11-15 09:33:02', NULL),
(NULL, '2019-11-16 19:33:55', NULL),
(NULL, '2019-11-16 19:37:37', NULL),
(NULL, '2019-11-16 19:38:37', NULL),
(NULL, '2019-11-16 19:53:40', NULL),
(NULL, '2019-11-16 22:29:32', NULL),
(NULL, '2019-11-16 23:32:20', NULL),
(NULL, '2019-11-17 12:46:46', NULL),
(NULL, '2019-11-17 13:12:42', NULL),
(NULL, '2019-11-17 17:56:38', NULL),
(NULL, '2019-11-17 19:57:57', NULL),
(NULL, '2019-11-17 20:40:02', NULL),
(NULL, '2019-11-17 20:55:52', NULL),
(NULL, '2019-11-17 20:56:10', NULL),
(NULL, '2019-11-17 21:17:01', NULL),
(NULL, '2019-11-17 21:17:15', NULL),
(NULL, '2019-11-17 21:23:29', NULL),
(NULL, '2019-11-17 21:23:46', NULL),
(NULL, '2019-11-17 21:24:10', NULL),
(NULL, '2019-11-17 21:30:29', NULL),
(NULL, '2019-11-17 21:54:47', NULL),
(NULL, '2019-11-17 22:41:49', NULL),
(NULL, '2019-11-18 09:24:37', NULL),
(NULL, '2019-11-18 09:42:06', NULL),
(NULL, '2019-11-18 09:43:41', NULL),
(NULL, '2019-11-18 10:51:51', NULL),
(NULL, '2019-11-18 11:13:55', NULL),
(NULL, '2019-11-18 14:26:23', NULL),
(NULL, '2019-11-18 14:27:39', NULL),
(NULL, '2019-11-19 08:49:05', NULL),
(NULL, '2019-11-19 23:24:15', NULL),
(NULL, '2019-11-20 01:19:36', NULL),
(NULL, '2019-11-20 02:09:46', NULL),
(NULL, '2019-11-20 02:23:29', NULL),
(NULL, '2019-11-20 02:25:59', NULL),
(NULL, '2019-11-20 03:09:40', NULL),
(NULL, '2019-11-20 03:09:59', NULL),
(NULL, '2019-11-20 09:34:28', NULL),
(NULL, '2019-11-20 09:35:01', NULL),
(NULL, '2019-11-20 10:16:32', NULL),
(NULL, '2019-11-20 10:27:53', NULL),
(NULL, '2019-11-20 13:44:21', NULL),
(NULL, '2019-11-20 13:50:00', NULL),
(NULL, '2019-11-20 13:50:23', NULL),
(NULL, '2019-11-20 13:50:51', NULL),
(NULL, '2019-11-20 13:52:32', NULL),
(NULL, '2019-11-20 13:56:22', NULL),
(NULL, '2019-11-20 14:01:08', NULL),
(NULL, '2019-11-20 14:01:46', NULL),
(NULL, '2019-11-20 14:04:03', NULL),
(NULL, '2019-11-21 14:05:38', NULL),
(NULL, '2019-11-23 19:02:51', NULL),
(NULL, '2019-11-23 19:03:58', NULL),
(NULL, '2019-11-23 21:12:55', NULL),
(NULL, '2019-11-23 23:21:50', NULL),
(NULL, '2019-11-27 17:55:52', NULL),
(NULL, '2019-11-27 22:39:09', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnico`
--

CREATE TABLE `tecnico` (
  `idtecnico` int(11) NOT NULL,
  `estado` char(1) DEFAULT NULL,
  `idpersonal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo`
--

CREATE TABLE `tipo` (
  `idtipo` int(11) NOT NULL,
  `desctipo` varchar(15) DEFAULT NULL,
  `idmarca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `tipo`
--

INSERT INTO `tipo` (`idtipo`, `desctipo`, `idmarca`) VALUES
(1, 'COROLLA', 1),
(2, 'AD', 2),
(3, 'HILUX', 1),
(4, 'RAV 4', 1),
(5, 'CAMRY', 1),
(6, 'YARIS', 1),
(7, 'MICRA', 2),
(8, 'LEAF', 2),
(9, 'NAVARA', 2),
(10, 'CELERIO', 3),
(11, 'BALENO', 3),
(12, 'IGNIS', 3),
(13, 'S-CROSS', 3),
(14, 'CARAVELLE', 4),
(15, 'POLO', 4),
(25, 'FOTON', 10),
(27, 'CALDINA', 1),
(28, 'PIZAR', 5),
(29, 'DEMIO', 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `IdPersonal` int(11) NOT NULL,
  `Usuario` varchar(20) NOT NULL,
  `Password` text NOT NULL,
  `FechaIngreso` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` char(1) NOT NULL DEFAULT '1',
  `Tipo` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdPersonal`, `Usuario`, `Password`, `FechaIngreso`, `Estado`, `Tipo`) VALUES
(1, 'luis123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-07-10 00:00:00', '1', 'ADM'),
(2, 'lucero123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-01-10 00:00:00', '1', 'SEC'),
(3, 'matias123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-04-10 00:00:00', '1', 'TEC'),
(4, 'fer', '$2a$07$asxx54ahjppf45sd87a5auSe82iCLCIV79JFwbhhGZi298Zog/Kxy', '2019-11-17 22:39:39', '0', 'TEC'),
(5, 'carlostec', '$2a$07$asxx54ahjppf45sd87a5auGab9Mr1O7Z/dT/lLnUfNlbR8z12emn6', '2019-11-20 03:09:14', '0', 'TEC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculo`
--

CREATE TABLE `vehiculo` (
  `idvehiculo` int(11) NOT NULL,
  `nroplaca` varchar(15) DEFAULT NULL,
  `marca` varchar(15) DEFAULT NULL,
  `tipo` varchar(15) DEFAULT NULL,
  `clase` varchar(15) DEFAULT NULL,
  `modelo` varchar(15) DEFAULT NULL,
  `tipomotor` varchar(15) DEFAULT NULL,
  `cilindrada` int(11) DEFAULT NULL,
  `tipotransporte` varchar(10) DEFAULT NULL,
  `estado` char(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `vehiculo`
--

INSERT INTO `vehiculo` (`idvehiculo`, `nroplaca`, `marca`, `tipo`, `clase`, `modelo`, `tipomotor`, `cilindrada`, `tipotransporte`, `estado`) VALUES
(1, '4049UIL', '10', '25', 'MINIBUS', '2015', 'INYECCION', 2300, 'PUBLICO', '1'),
(2, '1060EDN', '1', '1', 'AUTOMOVIL', '2000', 'CARBURADOR', 1900, 'PUBLICO', '1'),
(3, '443BBU', '2', '8', 'VAGONETA', '2000', 'INYECCION', 1600, 'PUBLICO', '1'),
(4, '653YAH', '4', '14', 'VAGONETA', '2015', 'INYECCION', 2000, 'PARTICULAR', '1'),
(5, '247AHX', '4', '15', 'VAGONETA', '2002', 'CARBURADOR', 1600, 'PARTICULAR', '1'),
(6, '1546IYE', '1', '27', 'VAGONETA', '2005', 'CARBURADOR', 1998, 'PUBLICO', '1'),
(7, '8398BRC', '1', '27', 'VAGONETA', '2014', 'INYECCION', 1500, 'PARTICULAR', '1'),
(8, '741', '3', '10', 'VAGONETA', '2012', 'INYECCION', 2000, 'PARTICULAR', '1'),
(9, '8080KJGU', '1', '1', 'VAGONETA', '2001', 'INYECCION', 1500, 'PARTICULAR', '1'),
(10, '741GPD', '5', '28', 'AUTOMOVIL', '2006', 'INYECCION', 1500, 'PUBLICO', '1'),
(11, '528FRA', '1', '6', 'AUTOMOVIL', '2000', 'INYECCION', 1300, 'PARTICULAR', '1'),
(12, '1840UDI', '2', '2', 'VAGONETA', '2012', 'INYECCION', 1800, 'PARTICULAR', '1'),
(13, '1122HRG', '1', '1', 'AUTOMOVIL', '2011', 'INYECCION', 1500, 'PARTICULAR', '1'),
(14, '1711LRN', '1', '27', 'VAGONETA', '2006', 'INYECCION', 1800, 'PARTICULAR', '1'),
(15, '525YRT', '3', '10', 'VAGONETA', '2006', 'INYECCION', 1300, 'PUBLICO', '1'),
(16, '525JTNH', '1', '1', 'VAGONETA', '2003', 'INYECCION', 1500, 'PUBLICO', '1'),
(17, '525KNH', '2', '2', 'VAGONETA', '2013', 'INYECCION', 1500, 'PARTICULAR', '1'),
(18, '7854SSD', '3', '10', 'MINIBUS', '2012', 'INYECCION', 1500, 'PARTICULAR', '1'),
(19, '424YHG', '4', '14', 'VAGONETA', '2012', 'INYECCION', 2000, 'PARTICULAR', '1'),
(20, '3504RTB', '18', '29', 'VAGONETA', '2011', 'INYECCION', 1600, 'PARTICULAR', '1'),
(21, '4125QAX', '2', '2', 'VAGONETA', '2008', 'INYECCION', 1500, 'PARTICULAR', '1'),
(22, '123SJF', '5', '28', 'VAGONETA', '2009', 'INYECCION', 1500, 'PARTICULAR', '1'),
(23, '525JHTY', '2', '2', 'VAGONETA', '2005', 'CARBURADOR', 1500, 'PARTICULAR', '1'),
(24, '58652KNM', '2', '2', 'VAGONETA', '2001', 'CARBURADOR', 1500, 'PUBLICO', '1'),
(25, '123PRU', '1', '1', 'VAGONETA', '2001', 'CARBURADOR', 1500, 'PUBLICO', '1'),
(26, '42665', '4', '14', 'VAGONETA', '2002', 'CARBURADOR', 1150, 'PUBLICO', '1');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cilindro`
--
ALTER TABLE `cilindro`
  ADD PRIMARY KEY (`seriecilindro`),
  ADD KEY `c-m` (`codmarcacil`),
  ADD KEY `c-recp` (`codrecpecioncil`);

--
-- Indices de la tabla `cilindrop`
--
ALTER TABLE `cilindrop`
  ADD PRIMARY KEY (`idcilindrop`),
  ADD KEY `cilp-m` (`codmarcacil`);

--
-- Indices de la tabla `contrato`
--
ALTER TABLE `contrato`
  ADD PRIMARY KEY (`codcontrato`),
  ADD KEY `con-propp` (`idpropvehiculo`),
  ADD KEY `con-usr` (`idusuario`),
  ADD KEY `kitp-k` (`kitp`),
  ADD KEY `cilp-cp` (`cilindrop`),
  ADD KEY `tec-cont` (`tecnico`);

--
-- Indices de la tabla `detalle`
--
ALTER TABLE `detalle`
  ADD PRIMARY KEY (`dsolicitudt`);

--
-- Indices de la tabla `dsolicitud`
--
ALTER TABLE `dsolicitud`
  ADD KEY `dsol-sol` (`codsolicitud`),
  ADD KEY `dsol-kit` (`seriekit`),
  ADD KEY `dsol-cil` (`seriecilindro`),
  ADD KEY `usr-detallesol` (`idtecnico`);

--
-- Indices de la tabla `kit`
--
ALTER TABLE `kit`
  ADD PRIMARY KEY (`seriekit`),
  ADD KEY `k-m` (`codmarca`),
  ADD KEY `k-recp` (`codrecpecion`);

--
-- Indices de la tabla `kitp`
--
ALTER TABLE `kitp`
  ADD PRIMARY KEY (`idkitp`),
  ADD KEY `kp-mak` (`codmarca`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`idmarca`);

--
-- Indices de la tabla `marcacilindro`
--
ALTER TABLE `marcacilindro`
  ADD PRIMARY KEY (`codmarcacil`);

--
-- Indices de la tabla `marcakit`
--
ALTER TABLE `marcakit`
  ADD PRIMARY KEY (`codmarca`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`IdPersonal`);

--
-- Indices de la tabla `propietario`
--
ALTER TABLE `propietario`
  ADD PRIMARY KEY (`idpropietaro`);

--
-- Indices de la tabla `propvehiculo`
--
ALTER TABLE `propvehiculo`
  ADD PRIMARY KEY (`idpropvehiculo`),
  ADD KEY `pv-prop` (`Idpropietario`),
  ADD KEY `pv-vehi` (`idvehiculo`);

--
-- Indices de la tabla `recepcioncilindro`
--
ALTER TABLE `recepcioncilindro`
  ADD PRIMARY KEY (`codrecepcioncil`),
  ADD KEY `idusr-usr` (`idusuario`);

--
-- Indices de la tabla `recepcionkitkit`
--
ALTER TABLE `recepcionkitkit`
  ADD PRIMARY KEY (`codrecepcion`),
  ADD KEY `recp-usr` (`idusuario`);

--
-- Indices de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`codsolicitud`),
  ADD KEY `sol-usr` (`idusuario`),
  ADD KEY `sol-propvehiculo` (`idpropvehiculo`);

--
-- Indices de la tabla `soltecnico`
--
ALTER TABLE `soltecnico`
  ADD KEY `soltec-tec` (`idusuario`),
  ADD KEY `soltec-sol` (`codsolicitud`);

--
-- Indices de la tabla `tecnico`
--
ALTER TABLE `tecnico`
  ADD PRIMARY KEY (`idtecnico`),
  ADD KEY `tec-per` (`idpersonal`);

--
-- Indices de la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`idtipo`),
  ADD KEY `tipo-marca` (`idmarca`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`IdPersonal`);

--
-- Indices de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  ADD PRIMARY KEY (`idvehiculo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cilindrop`
--
ALTER TABLE `cilindrop`
  MODIFY `idcilindrop` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contrato`
--
ALTER TABLE `contrato`
  MODIFY `codcontrato` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `kitp`
--
ALTER TABLE `kitp`
  MODIFY `idkitp` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `idmarca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `marcacilindro`
--
ALTER TABLE `marcacilindro`
  MODIFY `codmarcacil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `marcakit`
--
ALTER TABLE `marcakit`
  MODIFY `codmarca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `propietario`
--
ALTER TABLE `propietario`
  MODIFY `idpropietaro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `propvehiculo`
--
ALTER TABLE `propvehiculo`
  MODIFY `idpropvehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `recepcioncilindro`
--
ALTER TABLE `recepcioncilindro`
  MODIFY `codrecepcioncil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `recepcionkitkit`
--
ALTER TABLE `recepcionkitkit`
  MODIFY `codrecepcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  MODIFY `codsolicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `tecnico`
--
ALTER TABLE `tecnico`
  MODIFY `idtecnico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo`
--
ALTER TABLE `tipo`
  MODIFY `idtipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `vehiculo`
--
ALTER TABLE `vehiculo`
  MODIFY `idvehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cilindro`
--
ALTER TABLE `cilindro`
  ADD CONSTRAINT `c-m` FOREIGN KEY (`codmarcacil`) REFERENCES `marcacilindro` (`codmarcacil`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `c-recp` FOREIGN KEY (`codrecpecioncil`) REFERENCES `recepcioncilindro` (`codrecepcioncil`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cilindrop`
--
ALTER TABLE `cilindrop`
  ADD CONSTRAINT `cilp-m` FOREIGN KEY (`codmarcacil`) REFERENCES `marcacilindro` (`codmarcacil`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `contrato`
--
ALTER TABLE `contrato`
  ADD CONSTRAINT `prv-cont` FOREIGN KEY (`idpropvehiculo`) REFERENCES `propvehiculo` (`idpropvehiculo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tec-cont` FOREIGN KEY (`tecnico`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usr-cont` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle`
--
ALTER TABLE `detalle`
  ADD CONSTRAINT `sol-det` FOREIGN KEY (`dsolicitudt`) REFERENCES `dsolicitud` (`codsolicitud`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dsolicitud`
--
ALTER TABLE `dsolicitud`
  ADD CONSTRAINT `dsol-cil` FOREIGN KEY (`seriecilindro`) REFERENCES `cilindro` (`seriecilindro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dsol-kit` FOREIGN KEY (`seriekit`) REFERENCES `kit` (`seriekit`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dsol-sol` FOREIGN KEY (`codsolicitud`) REFERENCES `solicitud` (`codsolicitud`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usr-detallesol` FOREIGN KEY (`idtecnico`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `kit`
--
ALTER TABLE `kit`
  ADD CONSTRAINT `k-recp` FOREIGN KEY (`codrecpecion`) REFERENCES `recepcionkitkit` (`codrecepcion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `marca-codmarca` FOREIGN KEY (`codmarca`) REFERENCES `marcakit` (`codmarca`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `kitp`
--
ALTER TABLE `kitp`
  ADD CONSTRAINT `kp-mak` FOREIGN KEY (`codmarca`) REFERENCES `marcakit` (`codmarca`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `propvehiculo`
--
ALTER TABLE `propvehiculo`
  ADD CONSTRAINT `pv-prop` FOREIGN KEY (`Idpropietario`) REFERENCES `propietario` (`idpropietaro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pv-vehi` FOREIGN KEY (`idvehiculo`) REFERENCES `vehiculo` (`idvehiculo`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `recepcioncilindro`
--
ALTER TABLE `recepcioncilindro`
  ADD CONSTRAINT `idusr-usr` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recepcionkitkit`
--
ALTER TABLE `recepcionkitkit`
  ADD CONSTRAINT `recp-usr` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD CONSTRAINT `sol-propvehiculo` FOREIGN KEY (`idpropvehiculo`) REFERENCES `propvehiculo` (`idpropvehiculo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sol-usr` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `soltecnico`
--
ALTER TABLE `soltecnico`
  ADD CONSTRAINT `soltec-sol` FOREIGN KEY (`codsolicitud`) REFERENCES `solicitud` (`codsolicitud`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usr-personal` FOREIGN KEY (`idusuario`) REFERENCES `usuario` (`IdPersonal`);

--
-- Filtros para la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD CONSTRAINT `tipo-marca` FOREIGN KEY (`idmarca`) REFERENCES `marca` (`idmarca`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usr-per` FOREIGN KEY (`IdPersonal`) REFERENCES `personal` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Base de datos: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

--
-- Volcado de datos para la tabla `pma__designer_settings`
--

INSERT INTO `pma__designer_settings` (`username`, `settings_data`) VALUES
('root', '{\"snap_to_grid\":\"off\",\"angular_direct\":\"direct\",\"relation_lines\":\"true\",\"full_screen\":\"off\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Volcado de datos para la tabla `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"proyecto\",\"table\":\"usuario\"},{\"db\":\"proyecto\",\"table\":\"programa\"},{\"db\":\"proyecto\",\"table\":\"profesion\"},{\"db\":\"proyecto\",\"table\":\"personal\"},{\"db\":\"proyecto\",\"table\":\"pagomodulo\"},{\"db\":\"proyecto\",\"table\":\"ordenpago\"},{\"db\":\"proyecto\",\"table\":\"modulos\"},{\"db\":\"proyecto\",\"table\":\"estudianteprograma\"},{\"db\":\"proyecto\",\"table\":\"estudiante\"},{\"db\":\"proyecto\",\"table\":\"docente\"}]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

--
-- Volcado de datos para la tabla `pma__table_info`
--

INSERT INTO `pma__table_info` (`db_name`, `table_name`, `display_field`) VALUES
('proyecto', 'estudiante', 'Complemento'),
('proyecto', 'modulos', 'nombremodulo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Volcado de datos para la tabla `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'INFORMATION_SCHEMA', 'COLUMNS', '{\"sorted_col\":\"`COLUMNS`.`COLUMN_NAME` ASC\"}', '2025-11-15 19:17:04'),
('root', 'proyecto', 'docente', '{\"sorted_col\":\"`Ci` DESC\"}', '2025-12-16 16:04:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Volcado de datos para la tabla `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-12-16 15:53:45', '{\"Console\\/Mode\":\"collapse\",\"lang\":\"es\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indices de la tabla `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indices de la tabla `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indices de la tabla `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indices de la tabla `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indices de la tabla `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indices de la tabla `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indices de la tabla `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indices de la tabla `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indices de la tabla `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indices de la tabla `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indices de la tabla `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indices de la tabla `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indices de la tabla `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indices de la tabla `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indices de la tabla `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indices de la tabla `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indices de la tabla `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- Base de datos: `proyecto`
--
CREATE DATABASE IF NOT EXISTS `proyecto` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `proyecto`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_profesor_curso`
--

CREATE TABLE `asignacion_profesor_curso` (
  `AsignacionID` int(11) NOT NULL,
  `CursoID` int(11) NOT NULL,
  `ProfesorID` int(11) NOT NULL,
  `CicloAcademico` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion`
--

CREATE TABLE `calificacion` (
  `CalificacionID` int(11) NOT NULL,
  `EstudianteID` int(11) NOT NULL,
  `ProgramaId` int(11) NOT NULL,
  `Idmodulo` int(11) NOT NULL,
  `Nota` decimal(4,2) DEFAULT NULL,
  `estado` varchar(20) NOT NULL,
  `FechaRegistro` date DEFAULT NULL,
  `UsuarioRegistroID` int(11) DEFAULT NULL,
  `UsuarioModificacionID` int(11) DEFAULT NULL,
  `FechaModificacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `CursoID` int(11) NOT NULL,
  `NombreCurso` varchar(100) NOT NULL,
  `Creditos` decimal(4,2) NOT NULL,
  `ProgramaID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente`
--

CREATE TABLE `docente` (
  `DocenteID` int(11) NOT NULL,
  `Ci` int(11) NOT NULL,
  `Complemento` varchar(5) NOT NULL,
  `Exp` varchar(5) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apaterno` varchar(25) NOT NULL,
  `Amaterno` varchar(25) NOT NULL,
  `FechaNacimiento` date NOT NULL,
  `CedulaProfesional` varchar(30) DEFAULT NULL,
  `Especialidad` varchar(100) DEFAULT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Correo` varchar(25) NOT NULL,
  `Tel` varchar(12) NOT NULL,
  `Cel` int(15) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiante`
--

CREATE TABLE `estudiante` (
  `EstudianteID` int(11) NOT NULL,
  `Ci` int(20) NOT NULL,
  `Complemento` varchar(5) NOT NULL,
  `Exp` varchar(5) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apaterno` varchar(50) NOT NULL,
  `Amaterno` varchar(50) NOT NULL,
  `FechaNacimiento` date DEFAULT NULL,
  `Edad` int(11) NOT NULL,
  `Lugarn` varchar(20) NOT NULL,
  `Correo` varchar(100) DEFAULT NULL,
  `IdProfesion` int(11) NOT NULL,
  `Trabajo` varchar(25) NOT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Celular` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudianteprograma`
--

CREATE TABLE `estudianteprograma` (
  `idInscripcion` int(11) NOT NULL,
  `EstudianteID` int(11) NOT NULL,
  `ProgramaID` int(11) NOT NULL,
  `costomatricula` int(11) NOT NULL,
  `nvauchermatricula` int(11) NOT NULL,
  `FechaInscripcion` date NOT NULL,
  `foto` longblob DEFAULT NULL,
  `Estado` varchar(20) NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modulos`
--

CREATE TABLE `modulos` (
  `Idmodulo` int(11) NOT NULL,
  `ProgramaId` int(11) NOT NULL,
  `nombremodulo` varchar(100) NOT NULL,
  `codigomodulo` varchar(50) NOT NULL,
  `costomodulo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estadomodulo` varchar(15) NOT NULL DEFAULT 'ACTIVO',
  `ValidadoPor` int(11) DEFAULT NULL,
  `FechaValidacion` datetime DEFAULT NULL,
  `DocenteID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenpago`
--

CREATE TABLE `ordenpago` (
  `IdOrdenPago` int(11) NOT NULL,
  `EstudianteID` int(11) NOT NULL,
  `idinscripcion` int(11) NOT NULL,
  `ProgramaID` int(11) NOT NULL,
  `ListaPagosModulo` text NOT NULL COMMENT 'IDs de pagomodulo separados por comas',
  `MontoTotal` decimal(10,2) NOT NULL,
  `FechaGeneracion` datetime NOT NULL DEFAULT current_timestamp(),
  `ResponsableGeneracion` varchar(200) DEFAULT NULL,
  `NombreFactura` varchar(200) DEFAULT NULL,
  `NitCiFactura` varchar(50) DEFAULT NULL,
  `NumeroOrden` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de órdenes de pago generadas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagomodulo`
--

CREATE TABLE `pagomodulo` (
  `Idpagomodulo` int(11) NOT NULL,
  `idinscripcion` int(11) NOT NULL,
  `IdModulo` int(11) DEFAULT NULL,
  `costomodulo` decimal(10,2) NOT NULL,
  `fechapago` date DEFAULT NULL,
  `nvaucher` varchar(100) DEFAULT NULL,
  `fmodulo` longblob DEFAULT NULL,
  `Estado` enum('PAGADO','PENDIENTE','ANULADO') DEFAULT 'PENDIENTE',
  `FechaRegistro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `IdPersonal` int(11) NOT NULL,
  `CedulaIdentidad` varchar(20) NOT NULL,
  `ApellidoPaterno` varchar(50) NOT NULL,
  `ApellidoMaterno` varchar(50) NOT NULL,
  `Nombres` varchar(50) NOT NULL,
  `Direccion` varchar(200) NOT NULL,
  `Celular` int(11) NOT NULL,
  `Telefono` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`IdPersonal`, `CedulaIdentidad`, `ApellidoPaterno`, `ApellidoMaterno`, `Nombres`, `Direccion`, `Celular`, `Telefono`) VALUES
(1, '12398518', 'UÑO', 'FLORES', 'LUIS', 'URB. HUAJARA III ', 75418370, 410652),
(2, '12345678', 'OJEDA', 'FLORES', 'LUCERO', 'N', 63652014, 0),
(3, '7311599', 'CRUZ', 'CHOQUE', 'LIZBETH', 'ZONA RUMICAMPANA', 72344539, 41784);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesion`
--

CREATE TABLE `profesion` (
  `IdProfesion` int(11) NOT NULL,
  `NombreProfesion` varchar(25) NOT NULL,
  `Detalle` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `profesion`
--

INSERT INTO `profesion` (`IdProfesion`, `NombreProfesion`, `Detalle`) VALUES
(1, 'LICENCIADO EN ODONTOLOGIA', ''),
(2, 'CIRUJANO ODONTOLOGO', ''),
(3, 'MEDICO ORTONCISTA', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programa`
--

CREATE TABLE `programa` (
  `ProgramaID` int(11) NOT NULL,
  `NombrePrograma` varchar(100) NOT NULL,
  `GradoAcademico` varchar(50) NOT NULL,
  `Codigo` varchar(25) NOT NULL,
  `DuracionMeses` int(11) DEFAULT NULL,
  `Modulos` int(11) NOT NULL,
  `FechaInicio` date NOT NULL,
  `Sede` varchar(25) NOT NULL,
  `Costo` decimal(11,0) DEFAULT NULL,
  `CostoMatricula` decimal(10,2) DEFAULT NULL,
  `Detalle` varchar(255) NOT NULL,
  `Estado` varchar(20) NOT NULL DEFAULT '1',
  `Version` varchar(10) DEFAULT 'V-1',
  `NumeroTramite` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID` int(11) NOT NULL,
  `IdPersonal` int(11) DEFAULT NULL,
  `EstudianteID` int(11) DEFAULT NULL,
  `DocenteID` int(11) DEFAULT NULL,
  `Usuario` varchar(20) NOT NULL,
  `Password` text NOT NULL,
  `PasswordTexto` varchar(50) DEFAULT NULL,
  `FechaIngreso` datetime NOT NULL DEFAULT current_timestamp(),
  `Estado` char(1) NOT NULL DEFAULT '1',
  `Tipo` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID`, `IdPersonal`, `EstudianteID`, `DocenteID`, `Usuario`, `Password`, `PasswordTexto`, `FechaIngreso`, `Estado`, `Tipo`) VALUES
(1, 1, NULL, NULL, 'luis123', '$2y$12$RiRuWN5hiMZkT3.0b5Jvkedqe/Wa11t2.Ahq5z3OiST8coVWkmZR6', NULL, '2019-07-10 00:00:00', '1', 'ADM'),
(2, 3, NULL, NULL, '7311599', '$2y$12$ycZgtuiAWYTxPRWEs.M.8uufxjzBULoxsSPTL7b0Mad1Rzo9UN5vu', NULL, '2025-12-16 11:52:58', '1', 'ADM');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_profesor_curso`
--
ALTER TABLE `asignacion_profesor_curso`
  ADD PRIMARY KEY (`AsignacionID`),
  ADD UNIQUE KEY `CursoID` (`CursoID`,`ProfesorID`,`CicloAcademico`),
  ADD KEY `ProfesorID` (`ProfesorID`);

--
-- Indices de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD PRIMARY KEY (`CalificacionID`),
  ADD UNIQUE KEY `EstudianteID` (`EstudianteID`,`ProgramaId`,`Idmodulo`),
  ADD UNIQUE KEY `idx_estudiante_programa_modulo` (`EstudianteID`,`ProgramaId`,`Idmodulo`),
  ADD UNIQUE KEY `uk_calificacion` (`EstudianteID`,`ProgramaId`,`Idmodulo`),
  ADD KEY `CursoID` (`ProgramaId`),
  ADD KEY `modulocalificacion` (`Idmodulo`),
  ADD KEY `idx_usuario_registro` (`UsuarioRegistroID`),
  ADD KEY `idx_usuario_modificacion` (`UsuarioModificacionID`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`CursoID`),
  ADD UNIQUE KEY `ProgramaID` (`ProgramaID`,`NombreCurso`);

--
-- Indices de la tabla `docente`
--
ALTER TABLE `docente`
  ADD PRIMARY KEY (`DocenteID`),
  ADD UNIQUE KEY `CedulaProfesional` (`CedulaProfesional`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`EstudianteID`),
  ADD UNIQUE KEY `Matricula` (`Ci`),
  ADD UNIQUE KEY `EmailPersonal` (`Correo`),
  ADD UNIQUE KEY `Correo` (`Correo`),
  ADD UNIQUE KEY `Correo_2` (`Correo`);

--
-- Indices de la tabla `estudianteprograma`
--
ALTER TABLE `estudianteprograma`
  ADD PRIMARY KEY (`idInscripcion`),
  ADD KEY `Estudiante_inscripcion` (`EstudianteID`),
  ADD KEY `Programa_inscripcion` (`ProgramaID`);

--
-- Indices de la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`Idmodulo`),
  ADD KEY `moduloprograma` (`ProgramaId`),
  ADD KEY `docentemodulo` (`DocenteID`);

--
-- Indices de la tabla `ordenpago`
--
ALTER TABLE `ordenpago`
  ADD PRIMARY KEY (`IdOrdenPago`),
  ADD UNIQUE KEY `NumeroOrden` (`NumeroOrden`),
  ADD KEY `idx_estudiante` (`EstudianteID`),
  ADD KEY `idx_inscripcion` (`idinscripcion`),
  ADD KEY `idx_programa` (`ProgramaID`),
  ADD KEY `idx_fecha` (`FechaGeneracion`);

--
-- Indices de la tabla `pagomodulo`
--
ALTER TABLE `pagomodulo`
  ADD PRIMARY KEY (`Idpagomodulo`),
  ADD KEY `idinscripcion` (`idinscripcion`),
  ADD KEY `idx_pagomodulo_costo` (`costomodulo`),
  ADD KEY `idx_modulo` (`IdModulo`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`IdPersonal`);

--
-- Indices de la tabla `profesion`
--
ALTER TABLE `profesion`
  ADD PRIMARY KEY (`IdProfesion`);

--
-- Indices de la tabla `programa`
--
ALTER TABLE `programa`
  ADD PRIMARY KEY (`ProgramaID`),
  ADD UNIQUE KEY `NombrePrograma` (`NombrePrograma`);
ALTER TABLE `programa` ADD FULLTEXT KEY `NombrePrograma_2` (`NombrePrograma`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `fk_usuario_estudiante` (`EstudianteID`),
  ADD KEY `idx_docente` (`DocenteID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_profesor_curso`
--
ALTER TABLE `asignacion_profesor_curso`
  MODIFY `AsignacionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  MODIFY `CalificacionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `CursoID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docente`
--
ALTER TABLE `docente`
  MODIFY `DocenteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `EstudianteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudianteprograma`
--
ALTER TABLE `estudianteprograma`
  MODIFY `idInscripcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `modulos`
--
ALTER TABLE `modulos`
  MODIFY `Idmodulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenpago`
--
ALTER TABLE `ordenpago`
  MODIFY `IdOrdenPago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagomodulo`
--
ALTER TABLE `pagomodulo`
  MODIFY `Idpagomodulo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `profesion`
--
ALTER TABLE `profesion`
  MODIFY `IdProfesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `programa`
--
ALTER TABLE `programa`
  MODIFY `ProgramaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_profesor_curso`
--
ALTER TABLE `asignacion_profesor_curso`
  ADD CONSTRAINT `asignacion_profesor_curso_ibfk_1` FOREIGN KEY (`CursoID`) REFERENCES `curso` (`CursoID`),
  ADD CONSTRAINT `asignacion_profesor_curso_ibfk_2` FOREIGN KEY (`ProfesorID`) REFERENCES `docente` (`DocenteID`);

--
-- Filtros para la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD CONSTRAINT `calificacion_ibfk_1` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`),
  ADD CONSTRAINT `calificacion_ibfk_2` FOREIGN KEY (`ProgramaId`) REFERENCES `programa` (`ProgramaID`),
  ADD CONSTRAINT `modulocalificacion` FOREIGN KEY (`Idmodulo`) REFERENCES `modulos` (`Idmodulo`);

--
-- Filtros para la tabla `estudianteprograma`
--
ALTER TABLE `estudianteprograma`
  ADD CONSTRAINT `Estudiante_inscripcion` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Programa_inscripcion` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`);

--
-- Filtros para la tabla `modulos`
--
ALTER TABLE `modulos`
  ADD CONSTRAINT `docentemodulo` FOREIGN KEY (`DocenteID`) REFERENCES `docente` (`DocenteID`),
  ADD CONSTRAINT `moduloprograma` FOREIGN KEY (`ProgramaId`) REFERENCES `programa` (`ProgramaID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ordenpago`
--
ALTER TABLE `ordenpago`
  ADD CONSTRAINT `ordenpago_ibfk_1` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`),
  ADD CONSTRAINT `ordenpago_ibfk_2` FOREIGN KEY (`idinscripcion`) REFERENCES `estudianteprograma` (`idInscripcion`),
  ADD CONSTRAINT `ordenpago_ibfk_3` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`);

--
-- Filtros para la tabla `pagomodulo`
--
ALTER TABLE `pagomodulo`
  ADD CONSTRAINT `fk_pagomodulo_modulo` FOREIGN KEY (`IdModulo`) REFERENCES `modulos` (`Idmodulo`) ON DELETE SET NULL,
  ADD CONSTRAINT `pagomodulo_ibfk_1` FOREIGN KEY (`idinscripcion`) REFERENCES `estudianteprograma` (`idInscripcion`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_estudiante` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usr-per` FOREIGN KEY (`IdPersonal`) REFERENCES `personal` (`IdPersonal`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Base de datos: `test`
--
CREATE DATABASE IF NOT EXISTS `test` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `test`;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
