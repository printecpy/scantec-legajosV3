-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.1.0 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla scantec_2.alerta_destinatarios
CREATE TABLE IF NOT EXISTS `alerta_destinatarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_tarea_programada` int DEFAULT NULL,
  `correo_destino` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.alerta_historial
CREATE TABLE IF NOT EXISTS `alerta_historial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `documento_id` int NOT NULL,
  `correo_destino` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `detalle` text COLLATE utf8mb4_spanish_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.alerta_tipos
CREATE TABLE IF NOT EXISTS `alerta_tipos` (
  `id_tipo` int NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `dias_vencimiento` int DEFAULT NULL,
  PRIMARY KEY (`id_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.api_credenciales
CREATE TABLE IF NOT EXISTS `api_credenciales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `api_name` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `api_key` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `api_secret` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `base_url` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.api_logs
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL,
  `id_usuario` int DEFAULT NULL COMMENT 'ID del usuario autenticado (del JWT)',
  `endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '/Expedientes/subirDocumentoApi',
  `metodo_http` varchar(10) NOT NULL COMMENT 'POST, GET, etc.',
  `codigo_respuesta` smallint NOT NULL COMMENT '201, 403, 500, etc.',
  `mensaje_log` varchar(255) NOT NULL COMMENT 'Mensaje breve de la transacción',
  `ip_origen` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_fecha` (`fecha_hora`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.archivos_fisicos
CREATE TABLE IF NOT EXISTS `archivos_fisicos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_caja` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `ubicacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_almacenamiento` date NOT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=287 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.audits
CREATE TABLE IF NOT EXISTS `audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audits_user_id_user_type_index` (`user_id`,`user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `direccion` text NOT NULL,
  `correo` varchar(100) NOT NULL,
  `total_pag` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.detalle_proceso
CREATE TABLE IF NOT EXISTS `detalle_proceso` (
  `id_detalle_proceso` int NOT NULL AUTO_INCREMENT,
  `id_registro` int NOT NULL,
  `documento` varchar(60) NOT NULL,
  `total_pag` int NOT NULL,
  PRIMARY KEY (`id_detalle_proceso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.det_control
CREATE TABLE IF NOT EXISTS `det_control` (
  `id_det_control` int NOT NULL AUTO_INCREMENT,
  `id_cont` int DEFAULT NULL,
  `nombre_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `num_pag` int DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `ruta_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_det_control`) USING BTREE,
  KEY `FK__op_escaneo` (`id_cont`) USING BTREE,
  CONSTRAINT `FK_op_control` FOREIGN KEY (`id_cont`) REFERENCES `op_control` (`id_cont`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.det_escaneo
CREATE TABLE IF NOT EXISTS `det_escaneo` (
  `id_det_escaneo` int NOT NULL AUTO_INCREMENT,
  `id_esc` int DEFAULT NULL,
  `nombre_archivo` varchar(255) DEFAULT NULL,
  `num_pag` int DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `ruta_archivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_det_escaneo`),
  KEY `FK__op_escaneo` (`id_esc`),
  CONSTRAINT `FK__op_escaneo` FOREIGN KEY (`id_esc`) REFERENCES `op_escaneo` (`id_esc`)
) ENGINE=InnoDB AUTO_INCREMENT=446 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.det_indexado
CREATE TABLE IF NOT EXISTS `det_indexado` (
  `id_det_indexado` int NOT NULL AUTO_INCREMENT,
  `id_index` int DEFAULT NULL,
  `nombre_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `num_pag` int DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `ruta_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_det_indexado`) USING BTREE,
  KEY `FK__op_escaneo` (`id_index`) USING BTREE,
  CONSTRAINT `FK_op_indexado` FOREIGN KEY (`id_index`) REFERENCES `op_indexado` (`id_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.document_views
CREATE TABLE IF NOT EXISTS `document_views` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `usuario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `id_expediente` int DEFAULT NULL,
  `nombre_expediente` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `nombre_pc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `direccion_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.expediente
CREATE TABLE IF NOT EXISTS `expediente` (
  `id_expediente` int NOT NULL AUTO_INCREMENT,
  `id_proceso` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_tipoDoc` int DEFAULT (0),
  `indice_01` varchar(254) NOT NULL,
  `indice_02` varchar(254) NOT NULL,
  `indice_03` varchar(254) NOT NULL,
  `indice_04` varchar(254) NOT NULL,
  `indice_05` varchar(254) NOT NULL,
  `indice_06` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ruta_original` varchar(254) NOT NULL,
  `ubicacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `paginas` int NOT NULL,
  `fecha_indexado` date DEFAULT NULL,
  `fecha_firma` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `firma_digital` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_expediente`),
  KEY `id_tipoDoc_FK` (`id_tipoDoc`),
  KEY `idx_v_expedientes_indice_01` (`indice_01`),
  KEY `idx_v_expedientes_indice_02` (`indice_02`),
  KEY `idx_v_expedientes_indice_03` (`indice_03`),
  KEY `idx_v_expedientes_indice_04` (`indice_04`),
  KEY `idx_v_expedientes_estado` (`estado`),
  CONSTRAINT `id_tipoDoc_FK` FOREIGN KEY (`id_tipoDoc`) REFERENCES `tipo_documento` (`id_tipoDoc`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10317 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.historial_proceso
CREATE TABLE IF NOT EXISTS `historial_proceso` (
  `id_historial` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `id_proceso` int NOT NULL,
  `id_tipo_proceso` int NOT NULL,
  `observacion` text NOT NULL,
  PRIMARY KEY (`id_historial`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.informes_permisos
CREATE TABLE IF NOT EXISTS `informes_permisos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_informe_id` int NOT NULL,
  `id_grupo` int NOT NULL,
  `puede_modificar` tinyint(1) DEFAULT '0',
  `puede_eliminar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tipo_informe_id` (`tipo_informe_id`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `informes_permisos_ibfk_1` FOREIGN KEY (`tipo_informe_id`) REFERENCES `tipo_informe` (`id`),
  CONSTRAINT `informes_permisos_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `usu_grupo` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.intentos_login_fallidos
CREATE TABLE IF NOT EXISTS `intentos_login_fallidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `direccion_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `nombre_pc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `timestamp` timestamp NULL DEFAULT (now()),
  `motivo` varchar(255) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.ldap_datos
CREATE TABLE IF NOT EXISTS `ldap_datos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ldapHost` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapPort` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapUser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapPass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapBaseDn` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT NULL,
  `fecha_sincronizacion` timestamp NULL DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `executedSQL` text NOT NULL,
  `reverseSQL` text NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=14305 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.logs_umango
CREATE TABLE IF NOT EXISTS `logs_umango` (
  `idlog_umango` int NOT NULL AUTO_INCREMENT,
  `id_proceso_umango` varchar(50) NOT NULL,
  `id_lote` varchar(100) NOT NULL,
  `fuente_captura` varchar(100) NOT NULL,
  `archivo_origen` varchar(100) NOT NULL,
  `orden_documento` varchar(100) NOT NULL,
  `paginas_exportadas` int NOT NULL,
  `fecha_inicio` varchar(50) NOT NULL,
  `fecha_finalizacion` varchar(50) NOT NULL,
  `creador` varchar(100) NOT NULL,
  `usuario` varchar(250) NOT NULL,
  `trabajo` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `nombre_host` varchar(250) NOT NULL,
  `ip_host` varchar(150) NOT NULL,
  PRIMARY KEY (`idlog_umango`)
) ENGINE=InnoDB AUTO_INCREMENT=344 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.lote
CREATE TABLE IF NOT EXISTS `lote` (
  `id_registro` int NOT NULL AUTO_INCREMENT,
  `inicio_lote` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `fin_lote` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `cant_expediente` int NOT NULL,
  `fecha_recibido` date NOT NULL,
  `fecha_entregado` date DEFAULT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `total_paginas` int DEFAULT NULL,
  PRIMARY KEY (`id_registro`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_control
CREATE TABLE IF NOT EXISTS `op_control` (
  `id_cont` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `pag_control` int NOT NULL,
  `exp_control` int NOT NULL,
  `solicitado` int NOT NULL,
  `exp_reescaneo` int NOT NULL,
  `id_est` int NOT NULL,
  `id` int NOT NULL,
  `id_operador` int NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_cont`),
  KEY `id_operador` (`id_operador`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_escaneo
CREATE TABLE IF NOT EXISTS `op_escaneo` (
  `id_esc` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `pag_esc` int NOT NULL,
  `cant_exp` int NOT NULL,
  `id_est` int NOT NULL,
  `id` int NOT NULL,
  `id_operador` int NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_esc`),
  KEY `id` (`id`),
  KEY `id_operador` (`id_operador`)
) ENGINE=InnoDB AUTO_INCREMENT=618 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_escaner
CREATE TABLE IF NOT EXISTS `op_escaner` (
  `id_escaner` int NOT NULL AUTO_INCREMENT,
  `marca` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `modelo` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `serie` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_escaner`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_est_trabajo
CREATE TABLE IF NOT EXISTS `op_est_trabajo` (
  `id_est` int NOT NULL AUTO_INCREMENT,
  `nombre_pc` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_est`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_indexado
CREATE TABLE IF NOT EXISTS `op_indexado` (
  `id_index` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `pag_index` int NOT NULL,
  `exp_index` int NOT NULL,
  `id_est` int NOT NULL,
  `id` int NOT NULL,
  `id_operador` int NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_index`),
  KEY `id_operador` (`id_operador`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_operador
CREATE TABLE IF NOT EXISTS `op_operador` (
  `id_operador` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `apellido` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `direccion` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `proyecto` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_operador`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_preparado
CREATE TABLE IF NOT EXISTS `op_preparado` (
  `id_prep` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `cant_expediente` int NOT NULL,
  `cant_cajas` double(10,1) NOT NULL,
  `observaciones` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `id` int NOT NULL,
  `id_operador` int NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_prep`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.op_reagrupado
CREATE TABLE IF NOT EXISTS `op_reagrupado` (
  `id_reagrup` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `solicitado` int NOT NULL,
  `cant_cajas` double(10,1) NOT NULL,
  `observaciones` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `id` int NOT NULL,
  `id_operador` int NOT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_reagrup`),
  KEY `id` (`id`),
  KEY `id_operador` (`id_operador`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.permisos_documentos
CREATE TABLE IF NOT EXISTS `permisos_documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_grupo` int DEFAULT NULL,
  `id_tipoDoc` int DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_grupo` (`id_grupo`),
  KEY `idx_permisos_documentos_tipoDoc` (`id_tipoDoc`),
  CONSTRAINT `permisos_documentos_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `usu_grupo` (`id_grupo`),
  CONSTRAINT `permisos_documentos_ibfk_2` FOREIGN KEY (`id_tipoDoc`) REFERENCES `tipo_documento` (`id_tipoDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.proceso
CREATE TABLE IF NOT EXISTS `proceso` (
  `id_proceso` int NOT NULL AUTO_INCREMENT,
  `id_registro` int NOT NULL,
  `fecha_proceso` date NOT NULL,
  `nro_caja` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `desde` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `hasta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `id` int NOT NULL,
  `id_tipo_proceso` int NOT NULL,
  `total_pag` int DEFAULT NULL,
  `observacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci,
  PRIMARY KEY (`id_proceso`),
  KEY `id` (`id`) USING BTREE,
  KEY `id_tipo_proceso` (`id_tipo_proceso`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.rol_permisos
CREATE TABLE IF NOT EXISTS `rol_permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `controlador` varchar(80) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `permitido` tinyint(1) DEFAULT '1',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_spanish_ci DEFAULT 'activo',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `uk_rol_accion` (`id_rol`,`controlador`,`accion`),
  CONSTRAINT `FK_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.smtp_datos
CREATE TABLE IF NOT EXISTS `smtp_datos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `host` varchar(150) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `password` varchar(150) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `smtpsecure` enum('TLS','SSL') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT 'TLS',
  `remitente` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `nombre_remitente` varchar(100) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `port` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT 'activo',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci ROW_FORMAT=DYNAMIC;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.tarea_programada
CREATE TABLE IF NOT EXISTS `tarea_programada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_tarea` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo_informe` varchar(255) COLLATE utf8mb4_spanish_ci NOT NULL,
  `frecuencia` varchar(100) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `dias_alerta` int DEFAULT NULL,
  `fecha_proxima_ejecucion` datetime DEFAULT NULL,
  `fecha_ultima_ejecucion` datetime DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT (now()),
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.tipo_documento
CREATE TABLE IF NOT EXISTS `tipo_documento` (
  `id_tipoDoc` int NOT NULL AUTO_INCREMENT,
  `nombre_tipoDoc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_1` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_2` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_3` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_4` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_5` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_6` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id_tipoDoc`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.tipo_informe
CREATE TABLE IF NOT EXISTS `tipo_informe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_spanish_ci,
  `creado_por` int DEFAULT NULL,
  `sistema` tinyint(1) DEFAULT '0',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.tipo_proceso
CREATE TABLE IF NOT EXISTS `tipo_proceso` (
  `id_tipo_proceso` int NOT NULL AUTO_INCREMENT,
  `tipo_proceso` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  PRIMARY KEY (`id_tipo_proceso`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.ubicacion
CREATE TABLE IF NOT EXISTS `ubicacion` (
  `id_ubicacion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(200) NOT NULL,
  PRIMARY KEY (`id_ubicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.unirpdf
CREATE TABLE IF NOT EXISTS `unirpdf` (
  `id_unir` int NOT NULL AUTO_INCREMENT,
  `columna_01` varchar(50) NOT NULL,
  `columna_02` varchar(50) NOT NULL,
  `columna_03` varchar(50) NOT NULL,
  `fecha_creacion` datetime NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `ruta_creacion` varchar(150) NOT NULL,
  `nombre_archivo` varchar(80) NOT NULL,
  PRIMARY KEY (`id_unir`)
) ENGINE=InnoDB AUTO_INCREMENT=351 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `usuario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `clave` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `ultimo_acceso` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `cantidad_inicio` int DEFAULT NULL,
  `id_rol` int NOT NULL,
  `estado_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_grupo` int NOT NULL DEFAULT (0),
  `clave_actualizacion` timestamp NULL DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `fuente_registro` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT 'LOCAL',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.usu_grupo
CREATE TABLE IF NOT EXISTS `usu_grupo` (
  `id_grupo` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `estado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id_grupo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_2.visitas
CREATE TABLE IF NOT EXISTS `visitas` (
  `id_visita` int NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `servidor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `id` int NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_visita`),
  KEY `idx_session` (`session_id`),
  KEY `idx_session_estado` (`session_id`,`estado`)
) ENGINE=InnoDB AUTO_INCREMENT=797 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para vista scantec_2.v_control
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_control` (
	`id_cont` INT NOT NULL,
	`fecha` DATE NOT NULL,
	`pag_control` INT NOT NULL,
	`exp_control` INT NOT NULL,
	`solicitado` INT NOT NULL,
	`exp_reescaneo` INT NOT NULL,
	`id_est` INT NOT NULL,
	`nombre_pc` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_operador` INT NOT NULL,
	`operador` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci'
);

-- Volcando estructura para vista scantec_2.v_escaneos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_escaneos` (
	`id_esc` INT NOT NULL,
	`fecha` DATE NOT NULL,
	`pag_esc` INT NOT NULL,
	`cant_exp` INT NOT NULL,
	`id_est` INT NOT NULL,
	`nombre_pc` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_operador` INT NOT NULL,
	`operador` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci'
);

-- Volcando estructura para vista scantec_2.v_expedientes
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_expedientes` (
	`id_expediente` INT NOT NULL,
	`id_proceso` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_tipoDoc` INT NULL,
	`nombre_tipoDoc` VARCHAR(1) NULL COLLATE 'utf8mb4_spanish2_ci',
	`indice_01` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_02` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_03` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_04` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_05` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_06` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`ubicacion` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`ruta_original` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`paginas` INT NOT NULL,
	`fecha_indexado` DATE NULL,
	`fecha_firma` DATE NULL,
	`fecha_vencimiento` DATE NULL,
	`version` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`firma_digital` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci'
);

-- Volcando estructura para vista scantec_2.v_indexado
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_indexado` (
	`id_index` INT NOT NULL,
	`fecha` DATE NOT NULL,
	`pag_index` INT NOT NULL,
	`exp_index` INT NOT NULL,
	`id_est` INT NOT NULL,
	`nombre_pc` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_operador` INT NOT NULL,
	`operador` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci'
);

-- Volcando estructura para vista scantec_2.v_preparado
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_preparado` (
	`id_prep` INT NOT NULL,
	`fecha` DATE NOT NULL,
	`cant_expediente` INT NOT NULL,
	`cant_cajas` DOUBLE(10,1) NOT NULL,
	`observaciones` VARCHAR(1) NULL COLLATE 'utf8mb3_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_operador` INT NOT NULL,
	`operador` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci'
);

-- Volcando estructura para vista scantec_2.v_procesos
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_procesos` (
	`id_proceso` INT NOT NULL,
	`id_registro` INT NOT NULL,
	`fecha_recibido` DATE NOT NULL,
	`fecha_entregado` DATE NULL,
	`cant_expediente` INT NOT NULL,
	`fecha_proceso` DATE NOT NULL,
	`desde` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish2_ci',
	`hasta` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish2_ci',
	`nro_caja` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish2_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_tipo_proceso` INT NOT NULL,
	`tipo_proceso` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish2_ci',
	`observacion` TEXT NULL COLLATE 'utf8mb4_spanish2_ci'
);

-- Volcando estructura para vista scantec_2.v_reagrupado
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_reagrupado` (
	`id_reagrup` INT NOT NULL,
	`fecha` DATE NOT NULL,
	`solicitado` INT NOT NULL,
	`cant_cajas` DOUBLE(10,1) NOT NULL,
	`observaciones` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_operador` INT NOT NULL,
	`operador` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci',
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb3_spanish_ci'
);

-- Volcando estructura para vista scantec_2.v_visitas
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_visitas` (
	`id_visita` INT NOT NULL,
	`fecha` DATETIME NOT NULL,
	`ip` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`servidor` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish_ci',
	`id` INT NOT NULL,
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`usuario` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`fecha_cierre` DATETIME NULL,
	`estado` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_spanish_ci'
);

-- Volcando estructura para disparador scantec_2.after_delete_expedientes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `after_delete_expedientes` AFTER DELETE ON `expediente` FOR EACH ROW BEGIN
    DECLARE reverseSQLText TEXT;

    SET reverseSQLText = CONCAT(
        "INSERT INTO expediente (id_expediente, id_proceso, indice_01, indice_02, indice_03, indice_04, indice_05, indice_06, ruta_original, ubicacion, estado, paginas, fecha_indexado, fecha_actualizacion, version, firma_digital) VALUES (",
        OLD.id_expediente, ", ",
        IFNULL(CONCAT("'", OLD.id_proceso, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_01, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_02, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_03, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_04, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_05, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.indice_06, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.ruta_original, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.ubicacion, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.estado, "'"), 'NULL'), ", ",
        IFNULL(OLD.paginas, 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.fecha_indexado, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.fecha_firma, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.version, "'"), 'NULL'), ", ",
        IFNULL(CONCAT("'", OLD.firma_digital, "'"), 'NULL'), ");"
    );

    INSERT INTO logs (fecha, executedSQL, reverseSQL)
    VALUES (
        NOW(),
        CONCAT(
            "DELETE FROM expediente WHERE id_expediente = ", OLD.id_expediente, ";"
        ),
        reverseSQLText
    );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador scantec_2.after_delete_usuarios
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `after_delete_usuarios` AFTER DELETE ON `usuarios` FOR EACH ROW BEGIN
    DECLARE reverseSQLText TEXT;

    SET reverseSQLText = CONCAT(
        "INSERT INTO usuarios (id, nombre, usuario, clave, ultimo_acceso, cantidad_inicio, id_rol, estado_usuario, id_grupo, clave_actualizacion, email, fuente_registro) VALUES (",
        OLD.id, ", ",
        IFNULL(CONCAT("'", OLD.nombre, "'"), "NULL"), ", ",
        IFNULL(CONCAT("'", OLD.usuario, "'"), "NULL"), ", ",
        IFNULL(CONCAT("'", OLD.clave, "'"), "NULL"), ", ",
        IFNULL(OLD.ultimo_acceso, "NULL"), ", ",
        IFNULL(OLD.cantidad_inicio, "NULL"), ", ",
        OLD.id_rol, ", ",
        IFNULL(CONCAT("'", OLD.estado_usuario, "'"), "NULL"), ", ",
        OLD.id_grupo, ", ",
        IFNULL(OLD.clave_actualizacion, "NULL"), ", ",
        IFNULL(CONCAT("'", OLD.email, "'"), "NULL"), ", ",
        IFNULL(CONCAT("'", OLD.fuente_registro, "'"), "NULL"), ");"
    );

    INSERT INTO logs (fecha, executedSQL, reverseSQL)
    VALUES (
        NOW(),
        CONCAT(
            "DELETE FROM usuarios WHERE id = ", OLD.id, ";"
        ),
        reverseSQLText
    );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador scantec_2.after_update_expedientes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `after_update_expedientes` AFTER UPDATE ON `expediente` FOR EACH ROW BEGIN
    DECLARE executedSQLText TEXT;
    DECLARE reverseSQLText TEXT;

    -- Construir executedSQL
    SET executedSQLText = CONCAT(
        "UPDATE expediente SET ",
        "id_proceso = ", IFNULL(CONCAT("'", NEW.id_proceso, "'"), "NULL"), ", ",
        "indice_01 = ", IFNULL(CONCAT("'", NEW.indice_01, "'"), "NULL"), ", ",
        "indice_02 = ", IFNULL(CONCAT("'", NEW.indice_02, "'"), "NULL"), ", ",
        "indice_03 = ", IFNULL(CONCAT("'", NEW.indice_03, "'"), "NULL"), ", ",
        "indice_04 = ", IFNULL(CONCAT("'", NEW.indice_04, "'"), "NULL"), ", ",
        "indice_05 = ", IFNULL(CONCAT("'", NEW.indice_05, "'"), "NULL"), ", ",
        "indice_06 = ", IFNULL(CONCAT("'", NEW.indice_06, "'"), "NULL"), ", ",
        "ruta_original = ", IFNULL(CONCAT("'", NEW.ruta_original, "'"), "NULL"), ", ",
        "ubicacion = ", IFNULL(CONCAT("'", NEW.ubicacion, "'"), "NULL"), ", ",
        "estado = ", IFNULL(CONCAT("'", NEW.estado, "'"), "NULL"), ", ",
        "paginas = ", IFNULL(CONCAT("", NEW.paginas, ""), "NULL"), ", ",
        "fecha_indexado = ", IFNULL(CONCAT("'", NEW.fecha_indexado, "'"), "NULL"), ", ",
        "fecha_firma = ", IFNULL(CONCAT("'", NEW.fecha_firma, "'"), "NULL"), ", ",
        "version = ", IFNULL(CONCAT("'", NEW.version, "'"), "NULL"), ", ",
        "firma_digital = ", IFNULL(CONCAT("'", NEW.firma_digital, "'"), "NULL"), " ",
        "WHERE id_expediente = ", NEW.id_expediente, ";"
    );

    -- Construir reverseSQL
    SET reverseSQLText = CONCAT(
        "UPDATE expediente SET ",
        "id_proceso = ", IFNULL(CONCAT("'", OLD.id_proceso, "'"), "NULL"), ", ",
        "indice_01 = ", IFNULL(CONCAT("'", OLD.indice_01, "'"), "NULL"), ", ",
        "indice_02 = ", IFNULL(CONCAT("'", OLD.indice_02, "'"), "NULL"), ", ",
        "indice_03 = ", IFNULL(CONCAT("'", OLD.indice_03, "'"), "NULL"), ", ",
        "indice_04 = ", IFNULL(CONCAT("'", OLD.indice_04, "'"), "NULL"), ", ",
        "indice_05 = ", IFNULL(CONCAT("'", OLD.indice_05, "'"), "NULL"), ", ",
        "indice_06 = ", IFNULL(CONCAT("'", OLD.indice_06, "'"), "NULL"), ", ",
        "ruta_original = ", IFNULL(CONCAT("'", OLD.ruta_original, "'"), "NULL"), ", ",
        "ubicacion = ", IFNULL(CONCAT("'", OLD.ubicacion, "'"), "NULL"), ", ",
        "estado = ", IFNULL(CONCAT("'", OLD.estado, "'"), "NULL"), ", ",
        "paginas = ", IFNULL(CONCAT("", OLD.paginas, ""), "NULL"), ", ",
        "fecha_indexado = ", IFNULL(CONCAT("'", OLD.fecha_indexado, "'"), "NULL"), ", ",
        "fecha_firma = ", IFNULL(CONCAT("'", OLD.fecha_firma, "'"), "NULL"), ", ",
        "version = ", IFNULL(CONCAT("'", OLD.version, "'"), "NULL"), ", ",
        "firma_digital = ", IFNULL(CONCAT("'", OLD.firma_digital, "'"), "NULL"), " ",
        "WHERE id_expediente = ", OLD.id_expediente, ";"
    );

    -- Insertar en la tabla de logs
    INSERT INTO logs (fecha, executedSQL, reverseSQL)
    VALUES (NOW(), executedSQLText, reverseSQLText);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador scantec_2.after_update_usuarios
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `after_update_usuarios` AFTER UPDATE ON `usuarios` FOR EACH ROW BEGIN
    DECLARE executedSQLText TEXT;
    DECLARE reverseSQLText TEXT;

    SET executedSQLText = CONCAT(
        "UPDATE usuarios SET ",
        "nombre = ", IFNULL(CONCAT("'", NEW.nombre, "'"), "NULL"), ", ",
        "usuario = ", IFNULL(CONCAT("'", NEW.usuario, "'"), "NULL"), ", ",
        "clave = ", IFNULL(CONCAT("'", NEW.clave, "'"), "NULL"), ", ",
        "ultimo_acceso = ", IFNULL(NEW.ultimo_acceso, "NULL"), ", ",
        "cantidad_inicio = ", IFNULL(NEW.cantidad_inicio, "NULL"), ", ",
        "id_rol = ", NEW.id_rol, ", ",
        "estado_usuario = ", IFNULL(CONCAT("'", NEW.estado_usuario, "'"), "NULL"), ", ",
        "id_grupo = ", NEW.id_grupo, ", ",
        "clave_actualizacion = ", IFNULL(NEW.clave_actualizacion, "NULL"), ", ",
        "email = ", IFNULL(CONCAT("'", NEW.email, "'"), "NULL"), ", ",
        "fuente_registro = ", IFNULL(CONCAT("'", NEW.fuente_registro, "'"), "NULL"), " ",
        "WHERE id = ", NEW.id, ";"
    );

    SET reverseSQLText = CONCAT(
        "UPDATE usuarios SET ",
        "nombre = ", IFNULL(CONCAT("'", OLD.nombre, "'"), "NULL"), ", ",
        "usuario = ", IFNULL(CONCAT("'", OLD.usuario, "'"), "NULL"), ", ",
        "clave = ", IFNULL(CONCAT("'", OLD.clave, "'"), "NULL"), ", ",
        "ultimo_acceso = ", IFNULL(OLD.ultimo_acceso, "NULL"), ", ",
        "cantidad_inicio = ", IFNULL(OLD.cantidad_inicio, "NULL"), ", ",
        "id_rol = ", OLD.id_rol, ", ",
        "estado_usuario = ", IFNULL(CONCAT("'", OLD.estado_usuario, "'"), "NULL"), ", ",
        "id_grupo = ", OLD.id_grupo, ", ",
        "clave_actualizacion = ", IFNULL(OLD.clave_actualizacion, "NULL"), ", ",
        "email = ", IFNULL(CONCAT("'", OLD.email, "'"), "NULL"), ", ",
        "fuente_registro = ", IFNULL(CONCAT("'", OLD.fuente_registro, "'"), "NULL"), " ",
        "WHERE id = ", OLD.id, ";"
    );

    INSERT INTO logs (fecha, executedSQL, reverseSQL)
    VALUES (
        NOW(),
        executedSQLText,
        reverseSQLText
    );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_control`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_control` AS select `a`.`id_cont` AS `id_cont`,`a`.`fecha` AS `fecha`,`a`.`pag_control` AS `pag_control`,`a`.`exp_control` AS `exp_control`,`a`.`solicitado` AS `solicitado`,`a`.`exp_reescaneo` AS `exp_reescaneo`,`a`.`id_est` AS `id_est`,`d`.`nombre_pc` AS `nombre_pc`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`a`.`id_operador` AS `id_operador`,concat_ws(' ',`c`.`nombre`,`c`.`apellido`) AS `operador`,`a`.`estado` AS `estado` from (((`op_control` `a` join `usuarios` `b`) join `op_operador` `c`) join `op_est_trabajo` `d`) where ((`a`.`id` = `b`.`id`) and (`a`.`id_operador` = `c`.`id_operador`) and (`a`.`id_est` = `d`.`id_est`))
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_escaneos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_escaneos` AS select `a`.`id_esc` AS `id_esc`,`a`.`fecha` AS `fecha`,`a`.`pag_esc` AS `pag_esc`,`a`.`cant_exp` AS `cant_exp`,`a`.`id_est` AS `id_est`,`d`.`nombre_pc` AS `nombre_pc`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`a`.`id_operador` AS `id_operador`,concat_ws(' ',`c`.`nombre`,`c`.`apellido`) AS `operador`,`a`.`estado` AS `estado` from (((`op_escaneo` `a` join `usuarios` `b`) join `op_operador` `c`) join `op_est_trabajo` `d`) where ((`a`.`id` = `b`.`id`) and (`a`.`id_operador` = `c`.`id_operador`) and (`a`.`id_est` = `d`.`id_est`)) order by `a`.`id_esc`
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_expedientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_expedientes` AS select `a`.`id_expediente` AS `id_expediente`,`a`.`id_proceso` AS `id_proceso`,`a`.`id_tipoDoc` AS `id_tipoDoc`,`b`.`nombre_tipoDoc` AS `nombre_tipoDoc`,`a`.`indice_01` AS `indice_01`,`a`.`indice_02` AS `indice_02`,`a`.`indice_03` AS `indice_03`,`a`.`indice_04` AS `indice_04`,`a`.`indice_05` AS `indice_05`,`a`.`indice_06` AS `indice_06`,`a`.`ubicacion` AS `ubicacion`,`a`.`ruta_original` AS `ruta_original`,`a`.`estado` AS `estado`,`a`.`paginas` AS `paginas`,`a`.`fecha_indexado` AS `fecha_indexado`,`a`.`fecha_firma` AS `fecha_firma`,`a`.`fecha_vencimiento` AS `fecha_vencimiento`,`a`.`version` AS `version`,`a`.`firma_digital` AS `firma_digital` from (`expediente` `a` join `tipo_documento` `b`) where (`a`.`id_tipoDoc` = `b`.`id_tipoDoc`) order by `a`.`id_expediente`
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_indexado`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_indexado` AS select `a`.`id_index` AS `id_index`,`a`.`fecha` AS `fecha`,`a`.`pag_index` AS `pag_index`,`a`.`exp_index` AS `exp_index`,`a`.`id_est` AS `id_est`,`d`.`nombre_pc` AS `nombre_pc`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`a`.`id_operador` AS `id_operador`,concat_ws(' ',`c`.`nombre`,`c`.`apellido`) AS `operador`,`a`.`estado` AS `estado` from (((`op_indexado` `a` join `usuarios` `b`) join `op_operador` `c`) join `op_est_trabajo` `d`) where ((`a`.`id_est` = `d`.`id_est`) and (`a`.`id` = `b`.`id`) and (`a`.`id_operador` = `c`.`id_operador`))
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_preparado`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_preparado` AS select `a`.`id_prep` AS `id_prep`,`a`.`fecha` AS `fecha`,`a`.`cant_expediente` AS `cant_expediente`,`a`.`cant_cajas` AS `cant_cajas`,`a`.`observaciones` AS `observaciones`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`a`.`id_operador` AS `id_operador`,concat_ws(' ',`c`.`nombre`,`c`.`apellido`) AS `operador`,`a`.`estado` AS `estado` from ((`op_preparado` `a` join `usuarios` `b`) join `op_operador` `c`) where ((`a`.`id` = `b`.`id`) and (`a`.`id_operador` = `c`.`id_operador`)) order by `a`.`id_prep`
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_procesos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_procesos` AS select `a`.`id_proceso` AS `id_proceso`,`a`.`id_registro` AS `id_registro`,`b`.`fecha_recibido` AS `fecha_recibido`,`b`.`fecha_entregado` AS `fecha_entregado`,`b`.`cant_expediente` AS `cant_expediente`,`a`.`fecha_proceso` AS `fecha_proceso`,`a`.`desde` AS `desde`,`a`.`hasta` AS `hasta`,`a`.`nro_caja` AS `nro_caja`,`a`.`id` AS `id`,`c`.`nombre` AS `nombre`,`a`.`id_tipo_proceso` AS `id_tipo_proceso`,`d`.`tipo_proceso` AS `tipo_proceso`,`a`.`observacion` AS `observacion` from (((`proceso` `a` join `lote` `b`) join `usuarios` `c`) join `tipo_proceso` `d` on(((`c`.`id` = `a`.`id`) and (`d`.`id_tipo_proceso` = `a`.`id_tipo_proceso`)))) where (`a`.`id_registro` = `b`.`id_registro`) order by `a`.`id_proceso`
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_reagrupado`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_reagrupado` AS select `a`.`id_reagrup` AS `id_reagrup`,`a`.`fecha` AS `fecha`,`a`.`solicitado` AS `solicitado`,`a`.`cant_cajas` AS `cant_cajas`,`a`.`observaciones` AS `observaciones`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`a`.`id_operador` AS `id_operador`,concat_ws(' ',`c`.`nombre`,`c`.`apellido`) AS `operador`,`a`.`estado` AS `estado` from ((`op_reagrupado` `a` join `usuarios` `b`) join `op_operador` `c`) where ((`a`.`id` = `b`.`id`) and (`a`.`id_operador` = `c`.`id_operador`))
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_visitas`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_visitas` AS select `a`.`id_visita` AS `id_visita`,`a`.`fecha` AS `fecha`,`a`.`ip` AS `ip`,`a`.`servidor` AS `servidor`,`a`.`id` AS `id`,`b`.`nombre` AS `nombre`,`b`.`usuario` AS `usuario`,`a`.`fecha_cierre` AS `fecha_cierre`,`a`.`estado` AS `estado` from (`visitas` `a` join `usuarios` `b`) where (`a`.`id` = `b`.`id`)
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
