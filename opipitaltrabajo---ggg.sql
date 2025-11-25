-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-11-2025 a las 07:12:08
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
-- Base de datos: `opipitaltrabajo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anamnesis`
--

CREATE TABLE `anamnesis` (
  `anamnesis_id` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `alergias` varchar(255) DEFAULT NULL,
  `enfermedades_pulmonares` varchar(255) DEFAULT NULL,
  `enfermedades_cardiacas` varchar(255) DEFAULT NULL,
  `enfermedades_neurologicas` varchar(255) DEFAULT NULL,
  `enfermedades_hepaticas` varchar(255) DEFAULT NULL,
  `enfermedades_renales` varchar(255) DEFAULT NULL,
  `enfermedades_endocrinas` varchar(255) DEFAULT NULL,
  `otras_enfermedades` text DEFAULT NULL,
  `medicacion` varchar(255) DEFAULT NULL,
  `ha_sido_operado` varchar(255) DEFAULT NULL,
  `ha_tenido_tumor` tinyint(1) DEFAULT NULL,
  `ha_tenido_hemorragia` tinyint(1) DEFAULT NULL,
  `fuma` tinyint(1) DEFAULT NULL,
  `frecuencia_fuma` varchar(100) DEFAULT NULL,
  `toma_anticonceptivos` tinyint(1) DEFAULT NULL,
  `esta_embarazada` tinyint(1) DEFAULT NULL,
  `semanas_embarazo` int(2) DEFAULT NULL,
  `periodo_lactancia` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `anamnesis`
--

INSERT INTO `anamnesis` (`anamnesis_id`, `historia_clinica_id`, `alergias`, `enfermedades_pulmonares`, `enfermedades_cardiacas`, `enfermedades_neurologicas`, `enfermedades_hepaticas`, `enfermedades_renales`, `enfermedades_endocrinas`, `otras_enfermedades`, `medicacion`, `ha_sido_operado`, `ha_tenido_tumor`, `ha_tenido_hemorragia`, `fuma`, `frecuencia_fuma`, `toma_anticonceptivos`, `esta_embarazada`, `semanas_embarazo`, `periodo_lactancia`) VALUES
(1, 4, 'penecilina', 'Asmaa', 'arritmia', 'Migraña', 'cirrosis', 'insuficiencia renal', 'diabetes', NULL, 'metformina', NULL, 0, 0, 1, NULL, 0, 0, NULL, 0),
(3, 17, 'aspirina', 'Asma', 'Hipertencion', 'Epilepcia', 'Hepatites', 'Ensuficiencia renal', 'Diab', 'pulmionar', 'metarmofina', '0', 0, 0, 1, NULL, 0, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas`
--

CREATE TABLE `boletas` (
  `id_boleta` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `numero_boleta` varchar(20) NOT NULL,
  `tipo` enum('Boleta','Factura') DEFAULT 'Boleta',
  `monto_total` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia') NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `boletas`
--

INSERT INTO `boletas` (`id_boleta`, `id_orden`, `numero_boleta`, `tipo`, `monto_total`, `metodo_pago`, `fecha_emision`) VALUES
(2, 8, '7477', 'Boleta', 23.00, 'Efectivo', '2025-11-04 11:42:20'),
(3, 3, '4117', 'Boleta', 103.00, 'Tarjeta', '2025-11-04 11:42:59'),
(13, 10, '12', 'Boleta', 34.00, 'Efectivo', '2025-11-05 15:03:58'),
(14, 9, '123', 'Boleta', 34.00, 'Efectivo', '2025-11-05 15:04:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id_cita` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_tratamiento` int(11) NOT NULL,
  `id_medico` int(11) DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `duracion` int(11) DEFAULT 30 COMMENT 'Duración en minutos',
  `estado` enum('Pendiente','Confirmada','Completada','Cancelada','No asistió') DEFAULT 'Pendiente',
  `notas` text DEFAULT NULL,
  `recordatorio_enviado` tinyint(1) DEFAULT 0,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que creó la cita'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id_cita`, `id_paciente`, `id_tratamiento`, `id_medico`, `fecha_hora`, `duracion`, `estado`, `notas`, `recordatorio_enviado`, `creado_en`, `creado_por`) VALUES
(26, 2, 1, 8, '2025-09-28 01:09:00', 220, 'Completada', 'hollgg', 0, '2025-09-27 06:30:04', NULL),
(27, 6, 1, 8, '2025-10-26 21:59:00', 125, 'Confirmada', 'dfgdf', 0, '2025-10-20 03:04:22', 1),
(28, 6, 1, 8, '2025-10-30 12:24:00', 30, 'Completada', 'ASDASd', 0, '2025-10-20 17:25:06', 1),
(29, 6, 2, 8, '2025-10-31 15:57:00', 20, 'Completada', '5434', 0, '2025-10-20 20:58:04', 1),
(30, 5, 1, 8, '2025-10-22 15:58:00', 30, 'Confirmada', 'er', 0, '2025-10-20 20:58:43', 1),
(31, 6, 1, 8, '2025-10-24 15:59:00', 30, 'Pendiente', 'err', 0, '2025-10-20 20:59:33', 1),
(32, 7, 1, 8, '2025-11-12 07:24:00', 30, 'Confirmada', 'se le recomnida venir en ayunas', 0, '2025-11-10 12:26:42', 9),
(33, 6, 1, 8, '2025-11-10 08:21:00', 30, 'Confirmada', 'venir en ayunas', 0, '2025-11-10 13:21:23', 9),
(34, 7, 1, 8, '2025-11-20 08:23:00', 30, 'Confirmada', 'En ayunas', 0, '2025-11-10 13:23:33', 2),
(35, 6, 1, 9, '2025-11-10 09:28:00', 30, 'Confirmada', 'en ayunas', 0, '2025-11-10 13:28:05', 2),
(36, 7, 1, 8, '2025-11-10 10:32:00', 30, 'Confirmada', 'hola', 0, '2025-11-10 13:30:33', 2),
(37, 6, 1, 9, '2025-11-20 21:29:00', 30, 'Pendiente', 'bbbb', 0, '2025-11-20 02:29:45', 9),
(38, 6, 4, 8, '2025-11-20 21:31:00', 15, 'Pendiente', 'nnnnnnnnnnnn', 0, '2025-11-20 02:31:25', 9),
(39, 6, 1, 9, '2025-11-21 13:06:00', 30, 'Pendiente', 'hola', 0, '2025-11-21 17:06:26', 2),
(40, 6, 1, 9, '2025-11-22 22:18:00', 30, 'Confirmada', '', 0, '2025-11-23 03:15:34', 2),
(41, 6, 1, 8, '2025-11-22 22:20:00', 30, 'Confirmada', 'hola', 0, '2025-11-23 03:17:38', 2),
(42, 6, 3, 8, '2025-11-22 22:59:00', 60, 'Confirmada', '', 0, '2025-11-23 03:56:48', 2),
(43, 6, 2, 9, '2025-11-23 01:07:00', 20, 'Completada', '', 0, '2025-11-23 04:05:09', 2),
(44, 6, 2, 9, '2025-11-23 03:03:00', 20, 'Pendiente', '', 1, '2025-11-23 05:00:59', 2),
(45, 6, 2, 8, '2025-11-23 04:17:00', 20, 'Pendiente', '', 1, '2025-11-23 05:12:11', 2),
(46, 6, 2, 9, '2025-11-23 06:26:00', 20, 'Pendiente', '', 1, '2025-11-23 05:20:59', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_verificacion`
--

CREATE TABLE `codigos_verificacion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `utilizado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `codigos_verificacion`
--

INSERT INTO `codigos_verificacion` (`id`, `id_usuario`, `codigo`, `fecha_creacion`, `fecha_expiracion`, `utilizado`) VALUES
(1, 20, '282116', '2025-11-21 21:43:42', '2025-11-21 22:58:42', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consentimiento_informado`
--

CREATE TABLE `consentimiento_informado` (
  `consentimiento_id` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `dr_tratante_id` int(11) NOT NULL,
  `diagnostico_descripcion` text DEFAULT NULL,
  `tratamiento_descripcion` text DEFAULT NULL,
  `documento_pdf` mediumblob DEFAULT NULL,
  `fecha_firma` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `consentimiento_informado`
--

INSERT INTO `consentimiento_informado` (`consentimiento_id`, `historia_clinica_id`, `id_paciente`, `dr_tratante_id`, `diagnostico_descripcion`, `tratamiento_descripcion`, `documento_pdf`, `fecha_firma`) VALUES
(14, 3, 3, 2, 'aSDAd', 'ASD', NULL, '2025-10-29 20:12:03'),
(15, 3, 3, 2, 'SDADF', 'ADSAsd', NULL, '2025-10-31 16:55:36'),
(16, 3, 3, 2, 'fgbvcSDF', 'cvbcvbc', NULL, '2025-10-31 16:58:41'),
(17, 3, 3, 2, 'XCVB', 'CVBCXVB', NULL, '2025-10-31 17:47:21'),
(18, 3, 3, 2, 'SDFS', 'ASDF', NULL, '2025-10-31 17:49:30'),
(19, 3, 3, 2, 'CXVZXCV', 'ZXCV', NULL, '2025-10-31 17:49:47'),
(20, 4, 5, 2, 'asd', 'asd', NULL, '2025-11-06 18:05:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `tipo` enum('Radiografía','Consentimiento','Historial','Otro') NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `notas` text DEFAULT NULL,
  `subido_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `subido_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que subió el documento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `documentos`
--

INSERT INTO `documentos` (`id_documento`, `id_paciente`, `tipo`, `nombre`, `ruta_archivo`, `notas`, `subido_en`, `subido_por`) VALUES
(2, 3, 'Radiografía', 'Radiografía Panorámica Inicial', 'documentos_pacientes/101/pano_20240315.pdf', 'Documento requerido para evaluación de implantes.yy', '2025-10-20 04:02:25', 1),
(4, 5, 'Historial', 'Historial Médico de Alergias y Medicamentos', 'documentos_pacientes/101/historial_medico_base.doc', 'Paciente con alergia a la penicilina, verificar antes de recetar.', '2025-10-20 04:02:25', 1),
(5, 5, 'Consentimiento', 'Ginerertert', '../../../archivos_documentos/doc_68f5cd0e6ae1c_1760939278.pdf', 'gfdhgfhg', '2025-10-20 05:47:58', 1),
(6, 6, 'Consentimiento', 'Fresas de diamante', '../../../archivos_documentos/doc_68f5cd54632a2_1760939348.pdf', 'sdfsadf', '2025-10-20 05:49:08', 1),
(7, 5, 'Radiografía', 'Fresas de diamante', '../../../archivos_documentos/doc_68f5d03a4ab71_1760940090.pdf', 'asdasd', '2025-10-20 06:01:30', 1),
(8, 5, 'Consentimiento', 'Fresas de diamantesasd', '../../../../archivos_documentos/doc_68f5d11f3b61c_1760940319.pdf', 'sfsdfsdf', '2025-10-20 06:05:19', 1),
(9, 6, 'Radiografía', 'Fresas de diamante', '../../../../archivos_documentos/doc_68f5d61126873_1760941585.pdf', 'asdfdfs', '2025-10-20 06:26:25', 1),
(10, 5, 'Radiografía', 'Fresas de diamante', '../../../TRABAJOFINALARQUITECTURA/archivos_documentos/doc_68f6592cb7501_1760975148.pdf', 'dsfsdf', '2025-10-20 15:45:48', 1),
(11, 2, 'Radiografía', 'Fresas de diamante', '/TRABAJOFINALARQUITECTURA/archivos_documentos/doc_68f65c3866f2c_1760975928.pdf', 'dasf', '2025-10-20 15:58:48', 1),
(12, 5, 'Radiografía', 'Fresas de diamante', 'TRABAJOFINALARQUITECTURA/archivos_documentos/doc_68f6636288a53_1760977762.pdf', 'dasdas', '2025-10-20 16:29:22', 1),
(13, 5, 'Radiografía', 'Fresas de diamantesdsda', 'TRABAJOFINALARQUITECTURA/archivos_documentos/doc_68f666f96191a_1760978681.pdf', 'sadfwe', '2025-10-20 16:44:41', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades_medicas`
--

CREATE TABLE `especialidades_medicas` (
  `id_especialidad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(30) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `especialidades_medicas`
--

INSERT INTO `especialidades_medicas` (`id_especialidad`, `nombre`, `descripcion`, `icono`, `creado_en`) VALUES
(1, 'Medicina General', 'Diagnóstico y tratamiento de enfermedades comunes y atención primaria.', 'stethoscope', '2025-04-14 05:33:15'),
(2, 'Pediatría', 'Atención médica especializada para niños, adolescentes y lactantes.', 'child', '2025-04-14 05:33:15'),
(3, 'Cardiología', 'Diagnóstico y tratamiento de enfermedades del corazón y del sistema circulatorio.', 'heartbeat', '2025-04-14 05:33:15'),
(4, 'Cirugía General', 'Procedimientos quirúrgicos para tratar enfermedades y lesiones.', 'scalpel', '2025-04-14 05:33:15'),
(5, 'Oftalmología', 'Diagnóstico y tratamiento de enfermedades de los ojos.', 'eye', '2025-04-14 05:33:15'),
(6, 'Dermatología', 'Tratamiento de enfermedades de la piel, cabello y uñas.', 'skin', '2025-04-14 05:33:15'),
(7, 'Ginecología', 'Cuidado de la salud de la mujer, incluyendo el sistema reproductivo.', 'female', '2025-04-14 05:33:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evolucion_medica_paciente`
--

CREATE TABLE `evolucion_medica_paciente` (
  `id_evolucion` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL COMMENT 'Médico que registra la evolución',
  `fecha_evolucion` datetime NOT NULL DEFAULT current_timestamp(),
  `nota_subjetiva` text DEFAULT NULL COMMENT 'S: Síntomas o quejas referidas por el paciente',
  `nota_objetiva` text DEFAULT NULL COMMENT 'O: Hallazgos del examen físico o resultados de pruebas',
  `analisis` text DEFAULT NULL COMMENT 'A: Evaluación y diagnóstico del médico (Plan A o P)',
  `plan_de_accion` text DEFAULT NULL COMMENT 'P: Tratamiento, medicamentos, interconsultas solicitadas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci COMMENT='Registro detallado de la evolución médica del paciente';

--
-- Volcado de datos para la tabla `evolucion_medica_paciente`
--

INSERT INTO `evolucion_medica_paciente` (`id_evolucion`, `historia_clinica_id`, `id_medico`, `fecha_evolucion`, `nota_subjetiva`, `nota_objetiva`, `analisis`, `plan_de_accion`) VALUES
(3, 17, 8, '2025-11-07 00:46:25', 'Verificar que EvolucionPacienteDAO.php esté correcto', 'Verificar que EvolucionPacienteDAO.php esté correcto', '0', 'Verificar que EvolucionPacienteDAO.php esté correcto'),
(4, 17, 8, '2025-11-07 00:58:03', 'sdfsgfasdASD', 'cxvcxv', '0', 'sdfsdf'),
(6, 3, 8, '2025-11-07 01:21:40', '// controlEvolucionPaciente.php - VERSIÓN CORREGIDA\r\n\r\n// ELIMINAR esta línea: session_start(); // Ya se inició en getEvolucionPaciente.php', '// controlEvolucionPaciente.php - VERSIÓN CORREGIDA\r\n\r\n// ELIMINAR esta línea: session_start(); // Ya se inició en getEvolucionPaciente.php', '// controlEvolucionPaciente.php - VERSIÓN CORREGIDA\r\n\r\n// ELIMINAR esta línea: session_start(); // Ya se inició en getEvolucionPaciente.php', '// controlEvolucionPaciente.php - VERSIÓN CORREGIDA\r\n\r\n// ELIMINAR esta línea: session_start(); // Ya se inició en getEvolucionPaciente.php'),
(7, 17, 8, '2025-11-07 22:04:50', 'volucion, emp.historia_clinica_id, CONCAT(u.nombre, &#039; &#039;, u.apellido_paterno) as paciente, CONCAT(um.nombre, &#039; &#039;, um.apellido_paterno) as medico, emp.fecha_evolucion, SUBSTRING(emp.nota_subjetiva, 1, 50) as resdf', 'volucion, emp.historia_clinica_id, CONCAT(u.nombre, &#039; &#039;, u.apellido_paterno) as paciente, CONCAT(um.nombre, &#039; &#039;, um.apellido_paterno) as medico, emp.fecha_evolucion, SUBSTRING(emp.nota_subjetiva, 1, 50) as re', 'volucion, emp.historia_clinica_id, CONCAT(u.nombre, &#039; &#039;, u.apellido_paterno) as paciente, CONCAT(um.nombre, &#039; &#039;, um.apellido_paterno) as medico, emp.fecha_evolucion, SUBSTRING(emp.nota_subjetiva, 1, 50) as re', 'volucion, emp.historia_clinica_id, CONCAT(u.nombre, &#039; &#039;, u.apellido_paterno) as paciente, CONCAT(um.nombre, &#039; &#039;, um.apellido_paterno) as medico, emp.fecha_evolucion, SUBSTRING(emp.nota_subjetiva, 1, 50) as re'),
(8, 20, 8, '2025-11-17 00:26:15', 'dfsdgdsfgz&lt;xcxz', 'dsfgsdfg', 'gfhdsfg', 'fdgdfg'),
(9, 20, 8, '2025-11-19 21:44:19', 'bbbbbbb', 'bbbbbbbbbb', 'bbbbbbbbbbb', 'bbbbbbbbbbbbb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen_clinico`
--

CREATE TABLE `examen_clinico` (
  `examen_id` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `talla` decimal(5,2) DEFAULT NULL,
  `pulso` varchar(20) DEFAULT NULL,
  `id_enfermero` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `examen_clinico`
--

INSERT INTO `examen_clinico` (`examen_id`, `historia_clinica_id`, `peso`, `talla`, `pulso`, `id_enfermero`) VALUES
(2, 4, 23.00, 1.50, '34', 21),
(3, 20, 23.00, 1.07, '34', 21),
(6, 17, 56.00, 1.25, '44', 21);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturacion_internado`
--

CREATE TABLE `facturacion_internado` (
  `id_factura` int(11) NOT NULL,
  `id_internado` int(11) NOT NULL,
  `fecha_emision` date NOT NULL,
  `dias_internado` int(11) NOT NULL,
  `costo_habitacion` decimal(10,2) NOT NULL,
  `costo_tratamientos` decimal(10,2) DEFAULT 0.00,
  `costo_medicamentos` decimal(10,2) DEFAULT 0.00,
  `costo_otros` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Pagado','Anulado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `facturacion_internado`
--

INSERT INTO `facturacion_internado` (`id_factura`, `id_internado`, `fecha_emision`, `dias_internado`, `costo_habitacion`, `costo_tratamientos`, `costo_medicamentos`, `costo_otros`, `total`, `estado`) VALUES
(1, 18, '2025-11-04', 6, 45.00, 114.00, 17.00, 0.00, 176.00, 'Pagado'),
(2, 22, '2025-11-04', 4, 12.00, 45.00, 0.00, 0.00, 57.00, 'Pendiente'),
(3, 29, '2025-11-20', -1, 0.06, 0.07, 45.00, 41.00, 86.13, 'Pagado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id_habitacion` int(11) NOT NULL,
  `numero_puerta` varchar(10) NOT NULL,
  `piso` int(11) NOT NULL,
  `tipo` enum('Individual','Compartida','UCI') NOT NULL,
  `estado` enum('Disponible','Ocupada','Mantenimiento') DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id_habitacion`, `numero_puerta`, `piso`, `tipo`, `estado`) VALUES
(1, '101', 1, 'Individual', 'Ocupada'),
(2, '102', 1, 'Individual', 'Ocupada'),
(3, '103', 1, 'Compartida', 'Ocupada'),
(4, '104', 1, 'UCI', 'Disponible'),
(5, '201', 2, 'Individual', 'Disponible'),
(6, '202', 2, 'Compartida', 'Disponible'),
(7, '203', 2, 'Compartida', 'Disponible'),
(8, '301', 3, 'Individual', 'Disponible'),
(9, '302', 3, 'UCI', 'Disponible'),
(10, '303', 3, 'Compartida', 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historia_clinica`
--

CREATE TABLE `historia_clinica` (
  `historia_clinica_id` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `dr_tratante_id` int(11) NOT NULL,
  `fecha_creacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `historia_clinica`
--

INSERT INTO `historia_clinica` (`historia_clinica_id`, `id_paciente`, `dr_tratante_id`, `fecha_creacion`) VALUES
(3, 3, 21, '2025-10-28'),
(4, 5, 21, '2025-11-06'),
(17, 7, 21, '2025-11-06'),
(20, 6, 21, '2025-11-10'),
(21, 8, 2, '2025-11-22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `internados`
--

CREATE TABLE `internados` (
  `id_internado` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `fecha_alta` datetime DEFAULT NULL,
  `diagnostico_egreso` text DEFAULT NULL,
  `diagnostico_ingreso` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('Activo','Alta','Derivado','Fallecido') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `internados`
--

INSERT INTO `internados` (`id_internado`, `id_paciente`, `id_habitacion`, `id_medico`, `fecha_ingreso`, `fecha_alta`, `diagnostico_egreso`, `diagnostico_ingreso`, `observaciones`, `estado`) VALUES
(3, 2, 3, 8, '2025-09-28 00:38:00', '2025-09-28 00:38:00', NULL, NULL, 'AS', ''),
(4, 3, 10, 8, '2025-10-08 14:14:00', '2025-10-08 14:14:00', 'sdfsf', '2025-10-29T13:44', '0', ''),
(18, 6, 7, 8, '2025-10-29 00:00:00', '2025-11-10 15:00:00', NULL, 'SAD', '0', 'Derivado'),
(22, 5, 10, 9, '2025-10-29 00:00:00', '2025-11-10 14:58:00', NULL, 'SDFSDAF', '0', 'Alta'),
(25, 7, 2, 8, '2025-11-12 12:40:00', '2025-11-17 06:37:15', NULL, 'mmdmsdmmdsmd', '0', 'Alta'),
(26, 2, 1, 8, '2025-11-16 06:36:00', '2025-11-17 11:27:00', NULL, 'sfdgfsdg', '0', 'Alta'),
(27, 7, 2, 9, '2025-11-17 17:22:00', NULL, NULL, 'ggggggggggggggggggggggggggg', 'gggggggggggggggggg', 'Activo'),
(28, 3, 3, 8, '2025-11-17 17:29:00', NULL, NULL, 'dddddddddddddd', '0', 'Activo'),
(29, 6, 1, 9, '2025-11-20 03:49:00', NULL, NULL, 'bbbbbbb', 'bbbbbbbbbb', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `internados_seguimiento`
--

CREATE TABLE `internados_seguimiento` (
  `id_seguimiento` int(11) NOT NULL,
  `id_internado` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `id_medico` int(11) DEFAULT NULL,
  `id_enfermera` int(11) DEFAULT NULL,
  `evolucion` text DEFAULT NULL,
  `tratamiento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `internados_seguimiento`
--

INSERT INTO `internados_seguimiento` (`id_seguimiento`, `id_internado`, `fecha`, `id_medico`, `id_enfermera`, `evolucion`, `tratamiento`) VALUES
(17, 28, '2025-10-29 12:43:18', 8, 21, 'weqesdadasdadddsd', 'qweasd'),
(18, 28, '2025-11-17 15:07:49', 8, 21, 'dsadfsf', 'dsfsdf'),
(20, 29, '2025-11-19 21:50:26', 8, 21, 'bbbbbbbbbbbb', 'bbbbbbbbbbbbbb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicos`
--

CREATE TABLE `medicos` (
  `id_medico` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `cedula_profesional` varchar(20) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `experiencia` int(11) DEFAULT NULL COMMENT 'Años de experiencia',
  `horario` text DEFAULT NULL COMMENT 'Horario de trabajo en JSON',
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `medicos`
--

INSERT INTO `medicos` (`id_medico`, `id_usuario`, `id_especialidad`, `cedula_profesional`, `biografia`, `experiencia`, `horario`, `foto`, `activo`, `creado_en`) VALUES
(8, 2, 1, 'MED12345', 'Médico general con amplia experiencia en atención primaria.', 10, '{\"lunes\":\"08:00-14:00\",\"martes\":\"08:00-14:00\"}', 'fotos/medico1.jpg', 1, '2025-09-27 06:09:22'),
(9, 23, 5, 'CP123456', 'Médico con experiencia en cardiología.', 10, '{\"lunes\":\"8-12\",\"martes\":\"14-18\"}', 'foto.jpg', 1, '2025-11-10 12:33:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_examen`
--

CREATE TABLE `orden_examen` (
  `id_orden` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_examen` varchar(100) NOT NULL COMMENT 'Ejemplo: Hemograma, Radiografía, Ecografía',
  `indicaciones` text DEFAULT NULL,
  `estado` enum('Pendiente','Realizado','Entregado') DEFAULT 'Pendiente',
  `resultados` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `orden_examen`
--

INSERT INTO `orden_examen` (`id_orden`, `historia_clinica_id`, `id_medico`, `fecha`, `tipo_examen`, `indicaciones`, `estado`, `resultados`) VALUES
(7, 4, 8, '2025-11-09', 'hemograma, radiografia', 'sdsf', 'Pendiente', NULL),
(8, 17, 8, '2025-11-09', 'hemograma, radiografia', 'sddsf', 'Pendiente', NULL),
(9, 17, 8, '2025-11-09', 'hemograma, radiografiasd', 'asdf', 'Pendiente', NULL),
(10, 4, 8, '2025-11-09', 'hemograma, radiografiasd', 'sadasd', 'Pendiente', NULL),
(11, 4, 8, '2025-11-20', 'hemograma, radiografia', 'bbbbbbbbbbb', 'Pendiente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_pago`
--

CREATE TABLE `orden_pago` (
  `id_orden` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_cita` int(11) DEFAULT NULL,
  `id_internado` int(11) DEFAULT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto_estimado` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Facturada','Anulada') DEFAULT 'Pendiente',
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `orden_pago`
--

INSERT INTO `orden_pago` (`id_orden`, `id_paciente`, `id_cita`, `id_internado`, `concepto`, `monto_estimado`, `estado`, `fecha_emision`) VALUES
(3, 2, 26, NULL, 'Consulta médica general', 103.00, 'Facturada', '2025-09-28 00:41:37'),
(7, 2, 26, NULL, 'hola', 123.00, 'Pendiente', '2025-09-28 05:44:09'),
(8, 2, 26, 3, 'hola', 23.00, 'Facturada', '2025-09-28 05:49:34'),
(9, 2, NULL, 3, 'holasd', 34.00, 'Facturada', '2025-09-28 05:50:10'),
(10, 2, 26, 3, 'sdffdfsdf', 34.00, 'Facturada', '2025-10-20 20:51:45'),
(11, 6, 29, 18, 'bbbbbbbb', 45.00, 'Pendiente', '2025-11-20 02:32:25'),
(12, 6, 29, NULL, 'gggk', 45.00, 'Pendiente', '2025-11-20 07:28:23'),
(13, 6, 29, NULL, 'rrrr', 455.00, 'Pendiente', '2025-11-20 07:28:45'),
(14, 6, 28, NULL, 'hola', 34.00, 'Pendiente', '2025-11-21 17:07:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id_paciente` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `lugar_nacimiento` varchar(100) DEFAULT NULL,
  `ocupacion` varchar(100) DEFAULT NULL,
  `dni` varchar(20) NOT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `distrito` varchar(100) DEFAULT NULL,
  `edad` int(3) DEFAULT NULL,
  `sexo` varchar(10) DEFAULT NULL,
  `estado_civil` varchar(20) DEFAULT NULL,
  `nombre_apoderado` varchar(100) DEFAULT NULL,
  `apellido_paterno_apoderado` varchar(50) DEFAULT NULL,
  `apellido_materno_apoderado` varchar(50) DEFAULT NULL,
  `parentesco_apoderado` varchar(50) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id_paciente`, `id_usuario`, `fecha_nacimiento`, `lugar_nacimiento`, `ocupacion`, `dni`, `domicilio`, `distrito`, `edad`, `sexo`, `estado_civil`, `nombre_apoderado`, `apellido_paterno_apoderado`, `apellido_materno_apoderado`, `parentesco_apoderado`, `creado_en`) VALUES
(2, 4, '2001-07-24', 'jirron san luissfdfEQWE', 'data center', '47160428', 'leonso pradoeeHF', 'san martin sdd', 37, 'Masculino', 'solteroCVX', 'carlos', 'sifuenter', 'lozano', 'familiar', '2025-09-24 19:02:30'),
(3, 11, '2025-10-02', 'jirron san luis', 'data centerr', '47160420', 'leonso pradoeer', 'san martin ', 34, 'Masculino', 'solteroCVX', 'Giner', 'sifuente', 'Gonzalez', 'werf', '2025-10-05 06:50:30'),
(5, 18, '2025-10-18', 'san luiesew', 'ingerrwe', '4716027', 'masnsan nasaswe', 'weee', 34, 'M', 'Soltero', 'Giner', 'qw', 'Gonzalez', 'qw', '2025-10-19 22:30:32'),
(6, 20, '2025-10-09', 'san luies', 'ingerr', '47160424', 'masnsan nasares', 'acochascaqw', 120, '', 'Soltero', 'Giner', 'ewrewr', 'Gonzalez', 'dsfsadfhfg', '2025-10-19 22:39:59'),
(7, 17, '2025-11-07', 'san luiesewsdf', 'ingerrwe', '47160422', 'sdfdsff', 'sdfsadf', 47, 'M', 'Soltero', '', '', '', '', '2025-11-06 21:16:24'),
(8, 27, '2025-11-14', 'san luiesesds', 'ingerr', '47160454', 'masnsan nasasdd', 'acochascasd', 32, 'M', 'Soltero', 'Giner', 'ewrewr', 'Gonzalez', 'dsfsadf', '2025-11-21 19:53:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_telegram`
--

CREATE TABLE `paciente_telegram` (
  `id` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `chat_id` bigint(20) NOT NULL,
  `username_telegram` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `paciente_telegram`
--

INSERT INTO `paciente_telegram` (`id`, `id_paciente`, `chat_id`, `username_telegram`, `first_name`, `last_name`, `activo`, `fecha_registro`, `fecha_actualizacion`) VALUES
(1, 6, 8492891837, 'ginerBush', 'Giner', 'Figueroa', 1, '2025-11-20 13:42:23', '2025-11-21 01:57:22'),
(2, 2, 32342444, '@admin', 'Ginere', 'Figueroae', 0, '2025-11-20 14:33:49', '2025-11-21 00:56:22'),
(3, 3, 3443531, 'drCarolay', 'rrrrr', 're', 1, '2025-11-20 15:33:32', '2025-11-21 13:19:00'),
(4, 7, 4343453, 'adminbld', 'Ginerefd', 'ertrefedd', 1, '2025-11-20 18:48:35', '2025-11-20 19:55:04'),
(5, 5, 111112, 'jmartinezE', 'Gineref', 'ertrefed', 0, '2025-11-20 19:59:54', '2025-11-21 14:10:00'),
(6, 2, 345345, 'adminre', 'Ginerefe', 'ertreerr', 0, '2025-11-20 23:00:54', '2025-11-21 02:40:01'),
(7, 5, 455555, 'recepci4on01', 'Ginereu0', 'Figueroap', 0, '2025-11-23 00:55:31', '2025-11-23 01:03:38'),
(8, 5, 45645, 'admin6', 'Ginerefdgh', 'ertreetrt', 1, '2025-11-23 01:04:11', '2025-11-23 01:04:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta_detalle`
--

CREATE TABLE `receta_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_receta` int(11) NOT NULL,
  `medicamento` varchar(100) NOT NULL,
  `dosis` varchar(50) NOT NULL,
  `frecuencia` varchar(50) NOT NULL,
  `duracion` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `receta_detalle`
--

INSERT INTO `receta_detalle` (`id_detalle`, `id_receta`, `medicamento`, `dosis`, `frecuencia`, `duracion`, `notas`) VALUES
(1, 7, 'paracetamol', '500mg', 'Cada 8 horas', '7 dias', 'no tomar hacohol'),
(2, 6, 'paracetamol', '1000mg', 'Cada 24 horas', '7 dias', 'no tomar cerveza, no como aji'),
(3, 8, 'paracetamol', '500mg', 'Tres veces al día', '7 dias', 'bbbbbbbbbb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta_medica`
--

CREATE TABLE `receta_medica` (
  `id_receta` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `indicaciones_generales` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `receta_medica`
--

INSERT INTO `receta_medica` (`id_receta`, `historia_clinica_id`, `id_medico`, `fecha`, `indicaciones_generales`, `creado_en`) VALUES
(3, 4, 8, '2025-11-05', 'asdf', '2025-11-05 20:00:59'),
(4, 4, 8, '2025-11-05', 'Gestionar roles y permisos\r\nVer reportes generales del hospital\r\nAdministrar especialidades médicas\r\nGestionar tratamientos disponibles\r\nSupervisar todas', '2025-11-05 20:03:48'),
(5, 4, 8, '2025-11-10', 'Gestionar roles y permisos\r\nVer reportes generales del hospital\r\nAdministrar especialidades médicas\r\nGestionar tratamientos disponibles\r\nSupervisar todas', '2025-11-10 01:16:04'),
(6, 4, 8, '2025-11-10', 'Gestionar roles y permisos\r\nVer reportes generales del hospital', '2025-11-10 01:47:01'),
(7, 17, 8, '2025-11-10', 'Gestionar roles y permisos\r\nVer reportes generales del hospital\r\nAdministrar especialidades médicas\r\nGestionar tratamientos disponibles\r\nSupervisar todas', '2025-11-10 01:48:50'),
(8, 4, 8, '2025-11-20', 'bbbbbbbbbbbbbbbb', '2025-11-20 02:46:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_medico`
--

CREATE TABLE `registro_medico` (
  `registro_medico_id` int(11) NOT NULL,
  `historia_clinica_id` int(11) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `riesgos` text DEFAULT NULL,
  `motivo_consulta` text DEFAULT NULL,
  `enfermedad_actual` text DEFAULT NULL,
  `tiempo_enfermedad` varchar(100) DEFAULT NULL,
  `signos_sintomas` text DEFAULT NULL,
  `motivo_ultima_visita` text DEFAULT NULL,
  `ultima_visita_medica` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `registro_medico`
--

INSERT INTO `registro_medico` (`registro_medico_id`, `historia_clinica_id`, `fecha_registro`, `riesgos`, `motivo_consulta`, `enfermedad_actual`, `tiempo_enfermedad`, `signos_sintomas`, `motivo_ultima_visita`, `ultima_visita_medica`) VALUES
(2, 4, '2025-11-08 20:15:47', 'sgfdsfs', 'gdfgd', 'dfsg', '3 dias', 'sdfgdfg', 'sdgfg', '2025-11-08'),
(3, 4, '2025-11-08 20:16:11', 'dfsgdfg', 'fgdg', 'dfgdsfs', '3 dias', 'sfgdsgs', 'fgdg', '2025-11-08'),
(5, 3, '2025-11-08 20:17:30', 'nvbn', 'vcbnxcvgienrr', 'bcxvbc', 'cvbn', 'vbnvbnasc', 'bnv', '2025-11-09'),
(6, 4, '2025-11-17 14:03:55', 'muerte', 'bbbbbbbbbbbbb', 'bbbbbbbbbbbbb', '4  dias', 'signos', 'otilo dolor de garganta', '2025-11-17'),
(7, 4, '2025-11-19 21:41:44', 'gggggggggggg', 'bbbbbbbbbb', 'bbbbbbbbbbbbbbb', '3 dias', 'gggggggggg', 'ggggggggggggggg', '2025-11-19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `creado_en`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', '2025-04-14 10:33:14'),
(2, 'Médico', 'Personal médico de la clínica', '2025-04-14 10:33:14'),
(3, 'Recepcionista', 'Personal administrativo', '2025-04-14 10:33:14'),
(4, 'Paciente', 'Pacientes de la clínica', '2025-04-14 10:33:14'),
(5, 'Cajero', ' Emitir Boleta', '2025-09-22 06:55:35'),
(6, 'Enfermera', 'Apoyo clínico, signos vitales, seguimiento', '2025-09-23 16:30:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tratamientos`
--

CREATE TABLE `tratamientos` (
  `id_tratamiento` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `id_especialidad` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `duracion_estimada` int(11) DEFAULT NULL COMMENT 'Duración en minutos',
  `costo` decimal(10,2) NOT NULL,
  `requisitos` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `tratamientos`
--

INSERT INTO `tratamientos` (`id_tratamiento`, `nombre`, `id_especialidad`, `descripcion`, `duracion_estimada`, `costo`, `requisitos`, `activo`, `creado_en`) VALUES
(1, 'Consulta General', 1, 'Evaluación médica general del paciente', 30, 50.00, 'Ayuno no requerido', 1, '2025-09-27 05:51:50'),
(2, 'Electrocardiograma', 2, 'Registro de la actividad eléctrica del corazón', 20, 80.00, 'No haber ingerido cafeína 2h antes', 1, '2025-09-27 05:51:50'),
(3, 'Resonancia Magnética', 3, 'Imagen por resonancia para diagnóstico interno', 60, 300.00, 'Retirar objetos metálicos', 1, '2025-09-27 05:51:50'),
(4, 'Radiografía de Tórax', 3, 'Radiografía de tórax para evaluar pulmones y corazón', 15, 70.00, 'Evitar joyas metálicas', 1, '2025-09-27 05:51:50'),
(5, 'Cirugía Ambulatoria', 4, 'Procedimiento quirúrgico menor que no requiere hospitalización', 120, 1000.00, 'Ayuno de 8 horas', 1, '2025-09-27 05:51:50'),
(6, 'Quimioterapia', 5, 'Tratamiento oncológico con medicamentos específicos', 90, 500.00, 'Traer resultados de laboratorio recientes', 1, '2025-09-27 05:51:50'),
(7, 'Fisioterapia', 6, 'Sesión de terapia física para recuperación motriz', 45, 60.00, 'Ropa cómoda', 1, '2025-09-27 05:51:50'),
(8, 'Endoscopia', 7, 'Examen visual del interior del tracto digestivo', 40, 200.00, 'Ayuno de 6 horas', 1, '2025-09-27 05:51:50'),
(9, 'Terapia Respiratoria', 2, 'Tratamiento para mejorar la función pulmonar', 30, 70.00, 'Traer historial médico', 1, '2025-09-27 05:51:50'),
(10, 'Control de Diabetes', 1, 'Evaluación y ajuste de tratamiento para pacientes diabéticos', 25, 50.00, 'Ayuno 8 horas para análisis de glucosa', 1, '2025-09-27 05:51:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `usuario_clave` varchar(255) NOT NULL,
  `usuario_usuario` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) NOT NULL,
  `apellido_materno` varchar(50) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_login` datetime DEFAULT NULL,
  `estatus` int(11) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `email`, `usuario_clave`, `usuario_usuario`, `nombre`, `apellido_paterno`, `apellido_materno`, `telefono`, `activo`, `ultimo_login`, `estatus`, `creado_en`, `actualizado_en`) VALUES
(2, 3, '12333@hotmail.com', '$2y$10$ZIatSDpOogs9aBI/hkWBA.lvOjcxlPVfsDWUvwIyofzTeHfYWwu1G', 'mariaMedico', 'maria', 'Velarde', 'Zhan', '124321', 1, '2025-10-17 14:44:50', 0, '2025-09-18 18:21:36', '2025-11-23 02:35:48'),
(3, 3, 'guuunene@hotmail.com', '$2y$10$JvvR3ESEPOpTh5sz2vsXTOcwW27NHSBdtiCja5.0JXcD5BiWJ6ape', 'rececepcionAr', 'recepcionPeres', 'gunnntienens', 'gunetes', '934735478', 1, NULL, 0, '2025-09-18 18:29:45', '2025-10-22 18:13:32'),
(4, 4, 'jose@hotmail.com', '$2y$10$wYnKVeAL2UbdqrL9Y3IOWeGsLw4/LNGs6CMsI6.gOy.yO5OhAi.bu', 'josePaciente', 'jose', 'zhan', 'shacrell', '983848632', 1, NULL, 0, '2025-09-18 18:32:51', '2025-11-19 21:16:17'),
(5, 1, 'patron@hotmail.com', '$2y$10$gnFXmUXJp2K9uzYxJOnkwOXL4EoLacc7PrwLDbhbWJHw2KMekK8zS', 'patronAdministrador', 'patron', 'franBusman', 'florezqw', '314872346', 1, NULL, 0, '2025-09-18 18:34:49', '2025-09-21 18:43:23'),
(9, 3, 'arlen@hotmail.com', '$2y$10$XBKpMLX2Kx3YD.gSNYlwjegmf/.SMIz5Yh9MsYtioH.6jwh3OKOiy', 'ionistaArleniDDDF', 'arleni', 'flores', 'Melgarejo', '27983489', 1, '2025-10-17 14:48:36', 0, '2025-09-22 03:07:16', '2025-10-17 21:42:05'),
(11, 4, 'mariapac@untels.edu.pe', '$2y$10$V.kx7l8KpvzI.A/0L9wvI.l1kKhrPgkRIgDxDo1OCJkQUPhQJ8kuK', 'mariaPacienteewedee', 'maria', 'filoessn', 'hdkajhd', '29834723', 1, NULL, 0, '2025-10-05 05:25:10', '2025-10-18 04:18:08'),
(16, 4, '2113110108@duntels.edu.pe', '$2y$10$9fb0U2H4DuvAiMm40NU0vefWSUz7NMePg6w6SqoC7Bzic3r1MygR.', 'ginerasdQWDASDFDSF', 'GinersdfSDFS', 'asdaSD', 'Gonzalezfds', '925667407', 1, NULL, 0, '2025-10-18 08:30:08', '2025-11-21 21:40:17'),
(17, 4, '2113110108@unt4els.edu.pe', '$2y$10$CxAyipC/QhejOjCFZYJtyuNqPHNZwFxlqc2kEN0RnfyHIjaSq0vPi', 'ginerewqe', 'Ginewer', 'Figoueroa', 'Gonzalewez', '925667404', 1, NULL, 0, '2025-10-19 22:02:17', '2025-10-19 22:02:17'),
(18, 4, '2113dsa110108@untels.edu.pe', '$2y$10$xayr38DcHjB1/I.R6gRche2NBpRSz.Ue7PfCSlRcVNag6w6oPiC56', 'gineradsde', 'Ginasder', 'Figruerewoa e', 'Gonzalasdez', '925767408', 1, NULL, 0, '2025-10-19 22:29:36', '2025-10-19 22:29:36'),
(19, 4, '2113110148@untels.edu.pe', '$2y$10$bav06GUF6B5fT1cwKj4rHOeX5ZoTfLCAcSluvsxERxAFCxDqnPHCu', 'gineresdfsdsfsdgffsdg', 'sosowqssdf', 'Velardedfds', 'Gonzalezdsf', '925667608', 1, NULL, 0, '2025-10-19 22:32:05', '2025-10-19 23:42:06'),
(20, 4, '2113110sd108@untels.edu.pe', '$2y$10$Qq250V6xKkweHsDz1pB9U.dypJsGLnUEjLOD77rqI5/uKio2.Uzt6', 'ginsderDD', 'Gisdner', 'dasdsd', 'Gonzalez', '925675408', 1, NULL, 0, '2025-10-19 22:39:10', '2025-11-21 21:45:02'),
(21, 6, 'rocio@Untels.edu.pe', '$2y$10$9M5bKaIMdn2Uv3l.ZrL6TeeS4P6vVwVctlhwehQngKAUt4FkCbzQ6', 'EnfermeraRocio', 'RosiEnfermera', 'flores', 'margarita', '925667487', 1, NULL, 0, '2025-10-22 18:10:31', '2025-10-22 18:10:31'),
(22, 5, 'juan@hotmail.com.edu.pe', '$2y$10$clTocCLVjWZIxFGWvKEAl.LV/78nDvnKgss3E5kbG8b44xkL1343O', 'cajeroJuan', 'juan', 'tafur', 'zerda', '925667456', 1, NULL, 0, '2025-11-04 09:14:40', '2025-11-04 09:14:40'),
(23, 1, 'jose@hotmail.pe.com', '$2y$10$sIL2dQ5.UxcCNoTW0wS.NekuePC0zYrmJHgZpFvMak8VvKMLswHfC', 'JoseMedicos', 'Jose', 'Torres', 'ma', '925667441', 1, NULL, 0, '2025-11-10 12:22:30', '2025-11-18 18:58:33'),
(25, 1, '211311sd0108@untels.edu.pe', '$2y$10$VqUj9jaH3/zaKOSKuI.jSeZ4caexSJQxkeV74qyK8tI/G70/Vl43y', 'ginderdd', 'Ginedr', 'eeeeeeeeee', 'Gonzadez', '925661408', 1, NULL, 0, '2025-11-20 05:08:40', '2025-11-20 05:09:04'),
(26, 4, '21131re10108@untels.edu.pe', '$2y$10$oEhhTUlH8.8NswnYXfJF1elnE3WVFSfnULr7r6UrmtYc2MAaVT1nG', 'gineree', 'Giners', 'sifuentesd', 'Gonzaldez', '924567408', 1, NULL, 0, '2025-11-20 07:18:13', '2025-11-20 07:18:13'),
(27, 4, 'whwh@paciente', '$2y$10$9QVVHA9oa7yU1QXOhwoteOsMSWMKwxl1clymstG1cfHHPzBoEiMWO', 'pacitente', 'Crlos', 'perssss', 'frores', '95566455', 1, NULL, 0, '2025-11-21 19:49:24', '2025-11-21 19:49:24');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anamnesis`
--
ALTER TABLE `anamnesis`
  ADD PRIMARY KEY (`anamnesis_id`),
  ADD UNIQUE KEY `historia_clinica_id` (`historia_clinica_id`);

--
-- Indices de la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD PRIMARY KEY (`id_boleta`),
  ADD UNIQUE KEY `numero_boleta` (`numero_boleta`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id_cita`),
  ADD KEY `id_tratamiento` (`id_tratamiento`),
  ADD KEY `idx_citas_fecha` (`fecha_hora`),
  ADD KEY `idx_citas_paciente` (`id_paciente`),
  ADD KEY `idx_citas_medico` (`id_medico`),
  ADD KEY `idx_citas_estado` (`estado`);

--
-- Indices de la tabla `codigos_verificacion`
--
ALTER TABLE `codigos_verificacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `consentimiento_informado`
--
ALTER TABLE `consentimiento_informado`
  ADD PRIMARY KEY (`consentimiento_id`),
  ADD UNIQUE KEY `idx_unique_hc_fecha` (`historia_clinica_id`,`fecha_firma`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `dr_tratante_id` (`dr_tratante_id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indices de la tabla `especialidades_medicas`
--
ALTER TABLE `especialidades_medicas`
  ADD PRIMARY KEY (`id_especialidad`);

--
-- Indices de la tabla `evolucion_medica_paciente`
--
ALTER TABLE `evolucion_medica_paciente`
  ADD PRIMARY KEY (`id_evolucion`),
  ADD KEY `historia_clinica_id` (`historia_clinica_id`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `examen_clinico`
--
ALTER TABLE `examen_clinico`
  ADD PRIMARY KEY (`examen_id`),
  ADD UNIQUE KEY `historia_clinica_id` (`historia_clinica_id`),
  ADD KEY `fk_examen_enfermero` (`id_enfermero`);

--
-- Indices de la tabla `facturacion_internado`
--
ALTER TABLE `facturacion_internado`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id_internado` (`id_internado`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id_habitacion`),
  ADD UNIQUE KEY `numero_puerta` (`numero_puerta`);

--
-- Indices de la tabla `historia_clinica`
--
ALTER TABLE `historia_clinica`
  ADD PRIMARY KEY (`historia_clinica_id`),
  ADD UNIQUE KEY `id_paciente` (`id_paciente`),
  ADD KEY `dr_tratante_id` (`dr_tratante_id`);

--
-- Indices de la tabla `internados`
--
ALTER TABLE `internados`
  ADD PRIMARY KEY (`id_internado`),
  ADD KEY `fk_internado_paciente` (`id_paciente`),
  ADD KEY `fk_internado_habitacion` (`id_habitacion`),
  ADD KEY `fk_internado_medico` (`id_medico`);

--
-- Indices de la tabla `internados_seguimiento`
--
ALTER TABLE `internados_seguimiento`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD KEY `fk_seg_internado` (`id_internado`),
  ADD KEY `fk_seg_medico` (`id_medico`),
  ADD KEY `fk_seg_enfermera` (`id_enfermera`);

--
-- Indices de la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD PRIMARY KEY (`id_medico`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `cedula_profesional` (`cedula_profesional`),
  ADD KEY `id_especialidad` (`id_especialidad`),
  ADD KEY `idx_medicos_usuario` (`id_usuario`);

--
-- Indices de la tabla `orden_examen`
--
ALTER TABLE `orden_examen`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `historia_clinica_id` (`historia_clinica_id`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `orden_pago`
--
ALTER TABLE `orden_pago`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_cita` (`id_cita`),
  ADD KEY `id_internado` (`id_internado`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id_paciente`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `paciente_telegram`
--
ALTER TABLE `paciente_telegram`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_chat_paciente` (`id_paciente`,`chat_id`);

--
-- Indices de la tabla `receta_detalle`
--
ALTER TABLE `receta_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_receta` (`id_receta`);

--
-- Indices de la tabla `receta_medica`
--
ALTER TABLE `receta_medica`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `historia_clinica_id` (`historia_clinica_id`),
  ADD KEY `id_medico` (`id_medico`);

--
-- Indices de la tabla `registro_medico`
--
ALTER TABLE `registro_medico`
  ADD PRIMARY KEY (`registro_medico_id`),
  ADD KEY `fk_registro_historia` (`historia_clinica_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD PRIMARY KEY (`id_tratamiento`),
  ADD KEY `id_especialidad` (`id_especialidad`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anamnesis`
--
ALTER TABLE `anamnesis`
  MODIFY `anamnesis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `boletas`
--
ALTER TABLE `boletas`
  MODIFY `id_boleta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `codigos_verificacion`
--
ALTER TABLE `codigos_verificacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `consentimiento_informado`
--
ALTER TABLE `consentimiento_informado`
  MODIFY `consentimiento_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `especialidades_medicas`
--
ALTER TABLE `especialidades_medicas`
  MODIFY `id_especialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `evolucion_medica_paciente`
--
ALTER TABLE `evolucion_medica_paciente`
  MODIFY `id_evolucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `examen_clinico`
--
ALTER TABLE `examen_clinico`
  MODIFY `examen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `facturacion_internado`
--
ALTER TABLE `facturacion_internado`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id_habitacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `historia_clinica`
--
ALTER TABLE `historia_clinica`
  MODIFY `historia_clinica_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `internados`
--
ALTER TABLE `internados`
  MODIFY `id_internado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `internados_seguimiento`
--
ALTER TABLE `internados_seguimiento`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `medicos`
--
ALTER TABLE `medicos`
  MODIFY `id_medico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `orden_examen`
--
ALTER TABLE `orden_examen`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `orden_pago`
--
ALTER TABLE `orden_pago`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id_paciente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `paciente_telegram`
--
ALTER TABLE `paciente_telegram`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `receta_detalle`
--
ALTER TABLE `receta_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `receta_medica`
--
ALTER TABLE `receta_medica`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `registro_medico`
--
ALTER TABLE `registro_medico`
  MODIFY `registro_medico_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  MODIFY `id_tratamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anamnesis`
--
ALTER TABLE `anamnesis`
  ADD CONSTRAINT `anamnesis_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`);

--
-- Filtros para la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD CONSTRAINT `boletas_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `orden_pago` (`id_orden`);

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`id_tratamiento`) REFERENCES `tratamientos` (`id_tratamiento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `codigos_verificacion`
--
ALTER TABLE `codigos_verificacion`
  ADD CONSTRAINT `codigos_verificacion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `consentimiento_informado`
--
ALTER TABLE `consentimiento_informado`
  ADD CONSTRAINT `consentimiento_informado_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`),
  ADD CONSTRAINT `consentimiento_informado_ibfk_2` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `consentimiento_informado_ibfk_3` FOREIGN KEY (`dr_tratante_id`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_consentimiento_historia` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`);

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `evolucion_medica_paciente`
--
ALTER TABLE `evolucion_medica_paciente`
  ADD CONSTRAINT `evolucion_medica_paciente_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`),
  ADD CONSTRAINT `evolucion_medica_paciente_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`);

--
-- Filtros para la tabla `examen_clinico`
--
ALTER TABLE `examen_clinico`
  ADD CONSTRAINT `examen_clinico_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`),
  ADD CONSTRAINT `fk_examen_enfermero` FOREIGN KEY (`id_enfermero`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `facturacion_internado`
--
ALTER TABLE `facturacion_internado`
  ADD CONSTRAINT `facturacion_internado_ibfk_1` FOREIGN KEY (`id_internado`) REFERENCES `internados` (`id_internado`);

--
-- Filtros para la tabla `historia_clinica`
--
ALTER TABLE `historia_clinica`
  ADD CONSTRAINT `historia_clinica_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `historia_clinica_ibfk_2` FOREIGN KEY (`dr_tratante_id`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `internados`
--
ALTER TABLE `internados`
  ADD CONSTRAINT `fk_internado_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id_habitacion`),
  ADD CONSTRAINT `fk_internado_medico` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`),
  ADD CONSTRAINT `fk_internado_paciente` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`);

--
-- Filtros para la tabla `internados_seguimiento`
--
ALTER TABLE `internados_seguimiento`
  ADD CONSTRAINT `fk_seg_enfermera` FOREIGN KEY (`id_enfermera`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_seg_internado` FOREIGN KEY (`id_internado`) REFERENCES `internados` (`id_internado`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_seg_medico` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`);

--
-- Filtros para la tabla `medicos`
--
ALTER TABLE `medicos`
  ADD CONSTRAINT `medicos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `medicos_ibfk_2` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades_medicas` (`id_especialidad`);

--
-- Filtros para la tabla `orden_examen`
--
ALTER TABLE `orden_examen`
  ADD CONSTRAINT `orden_examen_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`),
  ADD CONSTRAINT `orden_examen_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`);

--
-- Filtros para la tabla `orden_pago`
--
ALTER TABLE `orden_pago`
  ADD CONSTRAINT `orden_pago_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`),
  ADD CONSTRAINT `orden_pago_ibfk_2` FOREIGN KEY (`id_cita`) REFERENCES `citas` (`id_cita`),
  ADD CONSTRAINT `orden_pago_ibfk_3` FOREIGN KEY (`id_internado`) REFERENCES `internados` (`id_internado`);

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `paciente_telegram`
--
ALTER TABLE `paciente_telegram`
  ADD CONSTRAINT `paciente_telegram_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id_paciente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `receta_detalle`
--
ALTER TABLE `receta_detalle`
  ADD CONSTRAINT `receta_detalle_ibfk_1` FOREIGN KEY (`id_receta`) REFERENCES `receta_medica` (`id_receta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `receta_medica`
--
ALTER TABLE `receta_medica`
  ADD CONSTRAINT `receta_medica_ibfk_1` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`),
  ADD CONSTRAINT `receta_medica_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `medicos` (`id_medico`);

--
-- Filtros para la tabla `registro_medico`
--
ALTER TABLE `registro_medico`
  ADD CONSTRAINT `fk_registro_historia` FOREIGN KEY (`historia_clinica_id`) REFERENCES `historia_clinica` (`historia_clinica_id`);

--
-- Filtros para la tabla `tratamientos`
--
ALTER TABLE `tratamientos`
  ADD CONSTRAINT `tratamientos_ibfk_1` FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades_medicas` (`id_especialidad`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
