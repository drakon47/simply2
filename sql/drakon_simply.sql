-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 21:14:45
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
-- Base de datos: `drakon_simply`
--
CREATE DATABASE IF NOT EXISTS `drakon_simply` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `drakon_simply`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros`
--

CREATE TABLE IF NOT EXISTS `centros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `direccion` varchar(500) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `email_referencia` varchar(255) DEFAULT NULL,
  `email_referencia_2` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `centros`
--

INSERT INTO `centros` (`id`, `nombre`, `direccion`, `localidad`, `provincia`, `pais`, `email_referencia`, `email_referencia_2`, `telefono`, `fecha_alta`, `activo`) VALUES
(1, 'Centro M├®dico San Juan', 'Av. Principal 123', 'Madrid', 'Madrid', 'Argentina', 'info@sanjuancm.com', NULL, '+34 91 123 4567', '2025-10-19 12:51:42', 1),
(2, 'Hospital General Barcelona', 'Calle Mayor 456', 'Barcelona', 'Barcelona', 'Argentina', 'contacto@hgb.es', NULL, '+34 93 234 5678', '2025-10-19 12:51:42', 1),
(3, 'Cl├¡nica Valencia Norte', 'Plaza Espa├▒a 789', 'Valencia', 'Valencia', 'Argentina', 'info@clinicanorte.com', NULL, '+34 96 345 6789', '2025-10-19 12:51:42', 1),
(4, 'Centro de Salud Sevilla', 'Av. Andaluc├¡a 321', 'Sevilla', 'Sevilla', 'Argentina', 'centro@sevillasalud.es', NULL, '+34 95 456 7890', '2025-10-19 12:51:42', 1),
(5, 'Hospital Bilbao Central', 'Gran V├¡a 654', 'Bilbao', 'Vizcaya', 'Argentina', 'info@hbilbao.com', NULL, '+34 94 567 8901', '2025-10-19 12:51:42', 1),
(6, 'Cl├¡nica M├ílaga Sur', 'Calle Larios 987', 'M├ílaga', 'M├ílaga', 'Argentina', 'contacto@malagasur.com', NULL, '+34 95 678 9012', '2025-10-19 12:51:55', 1),
(7, 'Centro M├®dico Zaragoza', 'Paseo Independencia 147', 'Zaragoza', 'Zaragoza', 'Argentina', 'info@cmzaragoza.es', NULL, '+34 97 789 0123', '2025-10-19 12:51:55', 1),
(8, 'Hospital Murcia Este', 'Av. Juan Carlos I 258', 'Murcia', 'Murcia', 'Argentina', 'contacto@hmurcia.com', NULL, '+34 96 890 1234', '2025-10-19 12:51:55', 1),
(9, 'Cl├¡nica Palma Mallorca', 'Paseo Mar├¡timo 369', 'Palma', 'Baleares', 'Argentina', 'info@palmahealth.com', NULL, '+34 97 901 2345', '2025-10-19 12:51:55', 1),
(10, 'Centro Sanitario Las Palmas', 'Calle Triana 741', 'Las Palmas', 'Las Palmas', 'Argentina', 'centro@lpalmas.es', NULL, '+34 92 012 3456', '2025-10-19 12:51:55', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `centros_proyectos`
--

CREATE TABLE IF NOT EXISTS `centros_proyectos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centro_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `fecha_asociacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_centro_proyecto` (`centro_id`,`proyecto_id`),
  KEY `proyecto_id` (`proyecto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `centros_proyectos`
--

INSERT INTO `centros_proyectos` (`id`, `centro_id`, `proyecto_id`, `fecha_asociacion`, `activo`) VALUES
(1, 1, 5, '2025-10-24 15:05:11', 1),
(2, 1, 4, '2025-10-24 15:05:15', 1),
(3, 1, 1, '2025-10-31 13:05:14', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_centro_adicional`
--

CREATE TABLE IF NOT EXISTS `documentos_centro_adicional` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `centro_id` int(11) NOT NULL,
  `descripcion` varchar(500) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_alerta_roja` date DEFAULT NULL,
  `fecha_alerta_amarilla` date DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_centro` (`centro_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_fecha_vencimiento` (`fecha_vencimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documentos_centro_adicional`
--

INSERT INTO `documentos_centro_adicional` (`id`, `centro_id`, `descripcion`, `nombre_archivo`, `ruta_archivo`, `fecha_vencimiento`, `fecha_alerta_roja`, `fecha_alerta_amarilla`, `responsable`, `email`, `fecha_subida`, `activo`) VALUES
(1, 1, 'Certificado Salta', 'b0372b42-6c16-4dbc-9703-cba73e050d98.pdf', 'documentos/centro_1/1_adicional_1.pdf', '2025-11-12', '2025-11-28', '2025-11-26', NULL, NULL, '2025-11-21 09:36:04', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_centro_subidos`
--

CREATE TABLE IF NOT EXISTS `documentos_centro_subidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docu_requerida_centro_id` int(11) NOT NULL,
  `centro_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_alerta_roja` date DEFAULT NULL,
  `fecha_alerta_amarilla` date DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_docu_requerida` (`docu_requerida_centro_id`),
  KEY `idx_centro` (`centro_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_fecha_vencimiento` (`fecha_vencimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documentos_centro_subidos`
--

INSERT INTO `documentos_centro_subidos` (`id`, `docu_requerida_centro_id`, `centro_id`, `nombre_archivo`, `ruta_archivo`, `fecha_vencimiento`, `fecha_alerta_roja`, `fecha_alerta_amarilla`, `responsable`, `email`, `fecha_subida`, `activo`) VALUES
(4, 3, 1, 'ESTUDIO_IMAGEN_5005626500_SMG.pdf', 'documentos/centro_1/1_3_1.pdf', NULL, NULL, NULL, NULL, NULL, '2025-11-21 09:30:50', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_proyecto_subidos`
--

CREATE TABLE IF NOT EXISTS `documentos_proyecto_subidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docu_requerida_proyecto_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_alerta_roja` date DEFAULT NULL,
  `fecha_alerta_amarilla` date DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_docu_requerida` (`docu_requerida_proyecto_id`),
  KEY `idx_proyecto` (`proyecto_id`),
  KEY `idx_activo` (`activo`),
  KEY `idx_fecha_vencimiento` (`fecha_vencimiento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documentos_proyecto_subidos`
--

INSERT INTO `documentos_proyecto_subidos` (`id`, `docu_requerida_proyecto_id`, `proyecto_id`, `nombre_archivo`, `ruta_archivo`, `fecha_vencimiento`, `fecha_alerta_roja`, `fecha_alerta_amarilla`, `responsable`, `email`, `fecha_subida`, `activo`) VALUES
(1, 2, 5, 'ESTUDIO_IMAGEN_5005626500_SMG.pdf', 'documentos/proyecto_5/5_2_1.pdf', '2025-11-04', '2025-11-18', '2025-11-10', NULL, NULL, '2025-11-22 08:21:49', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docu_requerida_centro`
--

CREATE TABLE IF NOT EXISTS `docu_requerida_centro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docu_requerida_centro`
--

INSERT INTO `docu_requerida_centro` (`id`, `titulo`, `fecha_creacion`, `activo`) VALUES
(1, 'Certificado de Habilitación del centro', '2025-11-21 11:36:10', 1),
(2, 'Certificado en Buenas Practicas Clinicas Inv. Principal', '2025-11-21 11:36:10', 1),
(3, 'Certificado de Entrenamiento en regulacion local', '2025-11-21 11:36:10', 1),
(4, 'Curriculum Vitae actualizado Investigador Principal', '2025-11-21 11:36:10', 1),
(5, 'Curriculum Vitae actualizado de Staff', '2025-11-21 11:36:10', 1),
(6, 'Matricula Profesional vigente Investigador Principal', '2025-11-21 11:36:10', 1),
(7, 'Matricula Profesional Vigente STAFF del Centro', '2025-11-21 11:36:10', 1),
(8, 'Contrato vigente con Institucion para Internacion', '2025-11-21 11:36:10', 1),
(9, 'Contrato vigente para traslado de paciente', '2025-11-21 11:36:10', 1),
(10, 'Sistema de Control de temperatura diario de medicacion ya establecido', '2025-11-21 11:36:10', 1),
(11, 'chequeo del Procedimiento establecido ante corte de electricidad', '2025-11-21 11:36:10', 1),
(12, 'Miembros y SOPs del Comit?? de Etica Institucional disponible', '2025-11-21 11:36:10', 1),
(13, 'SOPs del Centro de investigacion disponibles', '2025-11-21 11:36:10', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docu_requerida_proyecto`
--

CREATE TABLE IF NOT EXISTS `docu_requerida_proyecto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(500) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docu_requerida_proyecto`
--

INSERT INTO `docu_requerida_proyecto` (`id`, `titulo`, `fecha_creacion`, `activo`) VALUES
(1, 'Protocolo', '2025-11-22 11:21:00', 1),
(2, 'Consentimiento', '2025-11-22 11:21:00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos_tratamiento`
--

CREATE TABLE IF NOT EXISTS `eventos_tratamiento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proyecto_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `dias_desde_inicio` int(11) NOT NULL,
  `tipo_evento` enum('Presencial','Virtual','Llamado','Otro') NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `proyecto_id` (`proyecto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos_tratamiento`
--

INSERT INTO `eventos_tratamiento` (`id`, `proyecto_id`, `titulo`, `descripcion`, `dias_desde_inicio`, `tipo_evento`, `fecha_creacion`, `activo`) VALUES
(1, 1, 'Primera consulta', 'bla bla', 1, 'Presencial', '2025-10-24 14:53:45', 1),
(2, 1, 'Segunda consulta', NULL, 30, 'Presencial', '2025-10-24 14:53:57', 1),
(3, 1, 'otro evento', 'bla bla', 45, 'Virtual', '2025-11-01 11:01:35', 1),
(4, 1, 'oyyyy', NULL, 35, 'Presencial', '2025-11-01 11:01:51', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `laboratorios`
--

CREATE TABLE IF NOT EXISTS `laboratorios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `laboratorios`
--

INSERT INTO `laboratorios` (`id`, `nombre`, `pais`, `fecha_alta`, `activo`) VALUES
(1, 'Laboratorio Roche Argentina', 'Argentina', '2025-10-19 12:52:04', 1),
(2, 'Pfizer Research Center', 'Estados Unidos', '2025-10-19 12:52:04', 1),
(3, 'Novartis Laboratories', 'Suiza', '2025-10-19 12:52:04', 1),
(4, 'Sanofi Research Institute', 'Francia', '2025-10-19 12:52:04', 1),
(5, 'Merck Research Labs', 'Alemania', '2025-10-19 12:52:04', 1),
(6, 'Johnson & Johnson Innovation', 'Estados Unidos', '2025-10-19 12:52:04', 1),
(7, 'GSK Research Center', 'Reino Unido', '2025-10-19 12:52:04', 1),
(8, 'Bayer Healthcare', 'Alemania', '2025-10-19 12:52:04', 1),
(9, 'AstraZeneca Research', 'Reino Unido', '2025-10-19 12:52:04', 1),
(10, 'Boehringer Ingelheim', 'Alemania', '2025-10-19 12:52:04', 1),
(11, 'otro lab', 'ar', '2025-10-31 19:22:22', 1),
(12, 'test', 'ar', '2025-11-01 11:05:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono1` varchar(50) DEFAULT NULL,
  `telefono2` varchar(50) DEFAULT NULL,
  `domicilio_calle` varchar(255) DEFAULT NULL,
  `domicilio_numero` varchar(20) DEFAULT NULL,
  `domicilio_piso` varchar(20) DEFAULT NULL,
  `domicilio_depto` varchar(20) DEFAULT NULL,
  `domicilio_localidad` varchar(100) DEFAULT NULL,
  `domicilio_provincia` varchar(100) DEFAULT NULL,
  `familiar_contacto` varchar(255) DEFAULT NULL,
  `telefono_familiar` varchar(50) DEFAULT NULL,
  `consentimiento_firmado` enum('NO','SI') DEFAULT 'NO',
  `comentarios` text DEFAULT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `centro_id` int(11) DEFAULT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `centro_id` (`centro_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `nombre`, `apellido`, `email`, `telefono1`, `telefono2`, `domicilio_calle`, `domicilio_numero`, `domicilio_piso`, `domicilio_depto`, `domicilio_localidad`, `domicilio_provincia`, `familiar_contacto`, `telefono_familiar`, `consentimiento_firmado`, `comentarios`, `proyecto_id`, `centro_id`, `fecha_alta`, `activo`) VALUES
(1, 'Juan', 'Perez', 'juan.perez@email.com', '+34 91 123 4567', NULL, 'Calle Mayor', '123', NULL, NULL, 'Madrid', 'Madrid', 'Maria Perez', '+34 91 987 6543', 'SI', NULL, 5, 1, '2025-10-24 14:22:35', 1),
(2, 'Ana', 'Garc├¡a', 'ana.garcia@email.com', '+34 91 234 5678', NULL, 'Av. Principal', '456', NULL, NULL, 'Madrid', 'Madrid', 'Carlos Garc├¡a', '+34 91 876 5432', 'NO', NULL, 4, 1, '2025-10-24 14:22:35', 1),
(3, 'Luis', 'Mart├¡n', 'luis.martin@email.com', '+34 91 345 6789', NULL, 'Plaza Espa├▒a', '789', NULL, NULL, 'Madrid', 'Madrid', 'Elena Mart├¡n', '+34 91 765 4321', 'SI', NULL, 4, 1, '2025-10-24 14:22:35', 1),
(4, 'Martin', 'Sgattoni', 'martinsgattoni@gmail.com', '1158801956', NULL, 'C diaz', '1419', '2', '9', 'caba', 'buenos aires', 'Maria Amalia', '1154545457', 'NO', 'Aca van los comentarios.', 5, 1, '2025-10-24 15:08:56', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `patrocinante_id` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `patrocinante_id` (`patrocinante_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id`, `nombre`, `patrocinante_id`, `descripcion`, `fecha_alta`, `activo`) VALUES
(1, 'Estudio Cardiovascular CR-2024', 1, 'Investigacion sobre nuevos tratamientos cardiovasculares', '2025-10-19 12:52:13', 1),
(2, 'Ensayo Oncologico ONC-001', 2, 'Estudio de fase III para tratamiento de cancer de pulmon', '2025-10-19 12:52:13', 1),
(3, 'Investigacion Diabetes DIA-2024', 3, 'Nuevos protocolos para manejo de diabetes tipo 2', '2025-10-19 12:52:13', 1),
(4, 'Estudio Neurologia NEU-001', 4, 'Investigacion sobre enfermedades neurodegenerativas', '2025-10-19 12:52:13', 1),
(5, 'Ensayo Inmunologia IMM-2024', 5, 'Estudio de nuevas terapias inmunologicas', '2025-10-19 12:52:13', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `rol` enum('CENTRO','LABORATORIO','BUDDY','ADMIN') NOT NULL,
  `idEntidad` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `nombre`, `apellido`, `rol`, `idEntidad`, `fecha_registro`, `activo`) VALUES
(1, 'centro@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'Mar├¡a', 'Gonz├ílez', 'CENTRO', 1, '2025-10-19 12:51:34', 1),
(2, 'laboratorio@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'Carlos', 'Rodr├¡guez', 'LABORATORIO', 1, '2025-10-19 12:51:34', 1),
(3, 'buddy@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'Ana', 'Mart├¡n', 'BUDDY', NULL, '2025-10-19 12:51:34', 1),
(4, 'admin@test.com', 'e10adc3949ba59abbe56e057f20f883e', 'Administrador', 'Sistema', 'ADMIN', NULL, '2025-10-24 14:39:45', 1);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `centros_proyectos`
--
ALTER TABLE `centros_proyectos`
  ADD CONSTRAINT `centros_proyectos_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`),
  ADD CONSTRAINT `centros_proyectos_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`);

--
-- Filtros para la tabla `documentos_centro_adicional`
--
ALTER TABLE `documentos_centro_adicional`
  ADD CONSTRAINT `documentos_centro_adicional_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documentos_centro_subidos`
--
ALTER TABLE `documentos_centro_subidos`
  ADD CONSTRAINT `documentos_centro_subidos_ibfk_1` FOREIGN KEY (`docu_requerida_centro_id`) REFERENCES `docu_requerida_centro` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documentos_centro_subidos_ibfk_2` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `documentos_proyecto_subidos`
--
ALTER TABLE `documentos_proyecto_subidos`
  ADD CONSTRAINT `documentos_proyecto_subidos_ibfk_1` FOREIGN KEY (`docu_requerida_proyecto_id`) REFERENCES `docu_requerida_proyecto` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documentos_proyecto_subidos_ibfk_2` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `eventos_tratamiento`
--
ALTER TABLE `eventos_tratamiento`
  ADD CONSTRAINT `eventos_tratamiento_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`);

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`),
  ADD CONSTRAINT `pacientes_ibfk_2` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`);

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`patrocinante_id`) REFERENCES `laboratorios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
