-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 11-11-2025 a las 22:14:46
-- Versión del servidor: 5.7.17-log
-- Versión de PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto`
--
CREATE DATABASE IF NOT EXISTS `proyecto` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion`
--

CREATE TABLE `calificacion` (
  `CalificacionID` int(11) NOT NULL,
  `EstudianteID` int(11) NOT NULL,
  `CursoID` int(11) NOT NULL,
  `CicloAcademico` varchar(20) NOT NULL,
  `Nota` decimal(4,2) DEFAULT NULL,
  `FechaRegistro` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrato`
--

CREATE TABLE `contrato` (
  `codcontrato` int(11) NOT NULL,
  `fechac` datetime DEFAULT CURRENT_TIMESTAMP,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `CursoID` int(11) NOT NULL,
  `NombreCurso` varchar(100) NOT NULL,
  `Creditos` decimal(4,2) NOT NULL,
  `ProgramaID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `Estado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `docente`
--

INSERT INTO `docente` (`DocenteID`, `Ci`, `Complemento`, `Exp`, `Nombre`, `Apaterno`, `Amaterno`, `FechaNacimiento`, `CedulaProfesional`, `Especialidad`, `Direccion`, `Correo`, `Tel`, `Cel`, `Estado`) VALUES
(1, 0, '', '', '', '', '', '0000-00-00', '', '', '', '', '', 0, 0),
(4, 12345678, '1B', 'LP', 'JUAN', 'LOPEZ', 'PEREZ', '2002-10-03', '1458', 'ODONTOLOGO', 'av. de la maestro', 'jorge@gmail.com', '', 72341070, 0),
(5, 12345678, '', 'SC', 'JORGE', 'AGUILAR', 'PEREZ', '0000-00-00', '147852', 'ODONTOLOGO', '', 'DIEGO@GMAIL.COM', '', 72341070, 0),
(6, 1234578, '', 'CB', 'CARLOS', 'UGARTE', 'CARRILLO', '2002-10-02', '14785', 'ODONTOLOGO', 'HUAJARA I', 'DIEGO@GMAIL.COM', '', 72341070, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `Profesion` int(11) NOT NULL,
  `Trabajo` varchar(25) NOT NULL,
  `Direccion` varchar(50) NOT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Celular` int(11) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudianteprograma`
--

CREATE TABLE `estudianteprograma` (
  `idInscripcion` int(11) NOT NULL,
  `EstudianteID` int(11) NOT NULL,
  `ProgramaID` int(11) NOT NULL,
  `FechaInscripcion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `idmarca` int(11) NOT NULL,
  `descmarca` varchar(15) DEFAULT NULL,
  `estado` char(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Estructura de tabla para la tabla `profesion`
--

CREATE TABLE `profesion` (
  `idProfesion` int(11) NOT NULL,
  `NombreProfesion` varchar(25) NOT NULL,
  `Detalle` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `profesion`
--

INSERT INTO `profesion` (`idProfesion`, `NombreProfesion`, `Detalle`) VALUES
(1, 'LICENCIADO EN ODONTOLOGIA', ''),
(2, 'CIRUJANO ODONTOLOGO', '');

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
  `Costo` int(11) DEFAULT NULL,
  `Detalle` varchar(255) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `programa`
--

INSERT INTO `programa` (`ProgramaID`, `NombrePrograma`, `GradoAcademico`, `Codigo`, `DuracionMeses`, `Modulos`, `FechaInicio`, `Sede`, `Costo`, `Detalle`, `Estado`) VALUES
(1, 'MESTRIA EN ORTODONCIA', 'MAESTRIA', 'ORU-MAE-1-2025', 24, 12, '2025-11-14', 'ORURO', 25000, 'VIRTUAL', 0),
(2, 'MAESTRIA EN ENDODONCIA', 'MAESTRIA', 'LPZ-MAE-2-2025', 24, 12, '2025-11-14', 'LA PAZ', 25000, 'VIRTUAL', 0),
(3, 'DIPLOMADO EN ENDODONCIA', 'DIPLOMADO', 'ORU-DIP-3-2025', 12, 6, '2025-11-14', 'ORURO', 15000, 'PRESENCIAL', 1),
(4, 'ESPECIALIDAD EN ARMONIZACION OROFACIAL', 'ESPECIALIDAD', 'ORU-ESP-4-2025', 24, 12, '2025-11-27', 'ORURO', 25000, 'PRESENCIAL', 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `fecharecepcioncil` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notadeentrega` varchar(15) NOT NULL,
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `fecharecepcion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `notadeentrega` varchar(20) NOT NULL,
  `idusuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `fechasolicitud` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `idpropvehiculo` int(11) NOT NULL,
  `estado` varchar(15) DEFAULT 'SOLICITADO',
  `idusuario` int(11) DEFAULT NULL,
  `fechaconcluido` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `fechaasignacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `idusuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo`
--

CREATE TABLE `tipo` (
  `idtipo` int(11) NOT NULL,
  `desctipo` varchar(15) DEFAULT NULL,
  `idmarca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `FechaIngreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Estado` char(1) NOT NULL DEFAULT '1',
  `Tipo` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`IdPersonal`, `Usuario`, `Password`, `FechaIngreso`, `Estado`, `Tipo`) VALUES
(1, 'luis123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-07-10 00:00:00', '1', 'GER'),
(2, 'lucero123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-01-10 00:00:00', '1', 'ADM'),
(3, 'matias123', '$2a$07$asxx54ahjppf45sd87a5auGZEtGHuyZwm.Ur.FJvWLCql3nmsMbXy', '2019-04-10 00:00:00', '1', 'TEC'),
(4, 'fer', '$2a$07$asxx54ahjppf45sd87a5auSe82iCLCIV79JFwbhhGZi298Zog/Kxy', '2019-11-17 22:39:39', '0', 'TEC'),
(5, 'carlostec', '$2a$07$asxx54ahjppf45sd87a5auGab9Mr1O7Z/dT/lLnUfNlbR8z12emn6', '2019-11-20 03:09:14', '1', 'TEC');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  ADD UNIQUE KEY `EstudianteID` (`EstudianteID`,`CursoID`,`CicloAcademico`),
  ADD KEY `CursoID` (`CursoID`);

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
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`CursoID`),
  ADD UNIQUE KEY `ProgramaID` (`ProgramaID`,`NombreCurso`);

--
-- Indices de la tabla `detalle`
--
ALTER TABLE `detalle`
  ADD PRIMARY KEY (`dsolicitudt`);

--
-- Indices de la tabla `docente`
--
ALTER TABLE `docente`
  ADD PRIMARY KEY (`DocenteID`),
  ADD UNIQUE KEY `CedulaProfesional` (`CedulaProfesional`);

--
-- Indices de la tabla `dsolicitud`
--
ALTER TABLE `dsolicitud`
  ADD KEY `dsol-sol` (`codsolicitud`),
  ADD KEY `dsol-kit` (`seriekit`),
  ADD KEY `dsol-cil` (`seriecilindro`),
  ADD KEY `usr-detallesol` (`idtecnico`);

--
-- Indices de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD PRIMARY KEY (`EstudianteID`),
  ADD UNIQUE KEY `Matricula` (`Ci`),
  ADD UNIQUE KEY `Profesion` (`Profesion`),
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
-- Indices de la tabla `profesion`
--
ALTER TABLE `profesion`
  ADD PRIMARY KEY (`idProfesion`);

--
-- Indices de la tabla `programa`
--
ALTER TABLE `programa`
  ADD PRIMARY KEY (`ProgramaID`),
  ADD UNIQUE KEY `NombrePrograma` (`NombrePrograma`);
ALTER TABLE `programa` ADD FULLTEXT KEY `NombrePrograma_2` (`NombrePrograma`);

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
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `CursoID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `docente`
--
ALTER TABLE `docente`
  MODIFY `DocenteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT de la tabla `estudiante`
--
ALTER TABLE `estudiante`
  MODIFY `EstudianteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `estudianteprograma`
--
ALTER TABLE `estudianteprograma`
  MODIFY `idInscripcion` int(11) NOT NULL AUTO_INCREMENT;
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
-- AUTO_INCREMENT de la tabla `profesion`
--
ALTER TABLE `profesion`
  MODIFY `idProfesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `programa`
--
ALTER TABLE `programa`
  MODIFY `ProgramaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
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
  ADD CONSTRAINT `calificacion_ibfk_2` FOREIGN KEY (`CursoID`) REFERENCES `curso` (`CursoID`);

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
-- Filtros para la tabla `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`);

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
-- Filtros para la tabla `estudiante`
--
ALTER TABLE `estudiante`
  ADD CONSTRAINT `estudiante_ibfk_1` FOREIGN KEY (`Profesion`) REFERENCES `profesion` (`idProfesion`);

--
-- Filtros para la tabla `estudianteprograma`
--
ALTER TABLE `estudianteprograma`
  ADD CONSTRAINT `Estudiante_inscripcion` FOREIGN KEY (`EstudianteID`) REFERENCES `estudiante` (`EstudianteID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Programa_inscripcion` FOREIGN KEY (`ProgramaID`) REFERENCES `programa` (`ProgramaID`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
