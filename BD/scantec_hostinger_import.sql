-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- VersiÃ³n del servidor:         8.1.0 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL VersiÃ³n:             12.15.0.7171
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para scantec_basic

-- Volcando estructura para tabla scantec_basic.alerta_destinatarios
CREATE TABLE IF NOT EXISTS `alerta_destinatarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_tarea_programada` int DEFAULT NULL,
  `correo_destino` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.alerta_historial
CREATE TABLE IF NOT EXISTS `alerta_historial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `documento_id` int NOT NULL,
  `correo_destino` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_envio` datetime DEFAULT NULL,
  `estado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.alerta_tipos
CREATE TABLE IF NOT EXISTS `alerta_tipos` (
  `id_tipo` int NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `dias_vencimiento` int DEFAULT NULL,
  PRIMARY KEY (`id_tipo`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.api_credenciales
CREATE TABLE IF NOT EXISTS `api_credenciales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `api_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `api_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `base_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_catalogo_documentos
CREATE TABLE IF NOT EXISTS `cfg_catalogo_documentos` (
  `id_documento_maestro` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_spanish_ci NOT NULL,
  `codigo_interno` varchar(50) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `tiene_vencimiento` tinyint(1) NOT NULL DEFAULT '0',
  `dias_vigencia_base` int DEFAULT NULL,
  `dias_alerta_previa` int DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_documento_maestro`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

CREATE TABLE IF NOT EXISTS `cfg_politicas_actualizacion` (
  `id_politica` INT NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(30) NOT NULL,
  `etiqueta` VARCHAR(60) NOT NULL,
  `descripcion` VARCHAR(150) DEFAULT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `orden` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_politica`),
  UNIQUE KEY `uk_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

INSERT IGNORE INTO `cfg_politicas_actualizacion` (`clave`, `etiqueta`, `descripcion`, `activo`, `orden`) VALUES
  ('REEMPLAZAR', 'Solo reemplazar', 'El archivo nuevo reemplaza al anterior', 1, 1),
  ('UNIR_AL_INICIO', 'Solo agregar al inicio', 'El archivo nuevo se agrega al inicio del existente', 1, 2),
  ('UNIR_AL_FINAL', 'Solo agregar al final', 'El archivo nuevo se agrega al final del existente', 1, 3),
  ('NO_PERMITIR', 'No permitir actualizar', 'Una vez cargado, no se permite modificar', 1, 4),
  ('CONSULTAR', 'Consultar en cada archivo', 'Pregunta al usuario que hacer en cada carga', 1, 5);

-- Volcando estructura para tabla scantec_basic.cfg_legajo
CREATE TABLE IF NOT EXISTS `cfg_legajo` (
  `id_legajo` int NOT NULL AUTO_INCREMENT,
  `id_tipo_legajo` int NOT NULL,
  `ci_socio` varchar(20) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `nro_solicitud` varchar(50) DEFAULT NULL,
  `estado` enum('borrador','activo','finalizado','verificado','verificacion_rechazada','cerrado') NOT NULL DEFAULT 'borrador',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` datetime DEFAULT NULL,
  `observacion` text,
  `id_usuario` int NOT NULL,
  `id_usuario_armado` int DEFAULT NULL,
  PRIMARY KEY (`id_legajo`),
  KEY `id_tipo_legajo` (`id_tipo_legajo`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `cfg_legajo_ibfk_1` FOREIGN KEY (`id_tipo_legajo`) REFERENCES `cfg_tipo_legajo` (`id_tipo_legajo`),
  CONSTRAINT `cfg_legajo_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_legajo_documento
CREATE TABLE IF NOT EXISTS `cfg_legajo_documento` (
  `id_legajo_doc` int NOT NULL AUTO_INCREMENT,
  `id_legajo` int NOT NULL,
  `id_requisito` int NOT NULL,
  `id_documento_maestro` int NOT NULL,
  `rol_vinculado` varchar(20) NOT NULL DEFAULT 'TITULAR',
  `es_obligatorio` tinyint(1) NOT NULL DEFAULT '1',
  `estado` enum('pendiente','cargado','por_vencer','vencido') NOT NULL DEFAULT 'pendiente',
  `ruta_archivo` varchar(255) DEFAULT NULL,
  `fecha_carga` datetime DEFAULT NULL,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `observacion` text,
  `reemplazado_por` int DEFAULT NULL,
  PRIMARY KEY (`id_legajo_doc`),
  KEY `id_legajo` (`id_legajo`),
  KEY `id_requisito` (`id_requisito`),
  KEY `id_documento_maestro` (`id_documento_maestro`),
  KEY `reemplazado_por` (`reemplazado_por`),
  CONSTRAINT `cfg_legajo_documento_ibfk_1` FOREIGN KEY (`id_legajo`) REFERENCES `cfg_legajo` (`id_legajo`),
  CONSTRAINT `cfg_legajo_documento_ibfk_2` FOREIGN KEY (`id_requisito`) REFERENCES `cfg_matriz_requisitos` (`id_requisito`),
  CONSTRAINT `cfg_legajo_documento_ibfk_3` FOREIGN KEY (`id_documento_maestro`) REFERENCES `cfg_catalogo_documentos` (`id_documento_maestro`),
  CONSTRAINT `cfg_legajo_documento_ibfk_4` FOREIGN KEY (`reemplazado_por`) REFERENCES `cfg_legajo_documento` (`id_legajo_doc`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_legajo_documento_log
CREATE TABLE IF NOT EXISTS `cfg_legajo_documento_log` (
  `id_log_legajo_doc` int NOT NULL AUTO_INCREMENT,
  `id_legajo` int NOT NULL,
  `id_legajo_doc` int NOT NULL,
  `id_requisito` int DEFAULT NULL,
  `id_documento_maestro` int DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `detalle` text COLLATE utf8mb4_spanish_ci,
  `ruta_anterior` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ruta_nueva` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado_anterior` varchar(30) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado_nuevo` varchar(30) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_expedicion` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_spanish_ci,
  `id_usuario` int DEFAULT NULL,
  `nombre_host` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ip_host` varchar(45) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_evento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_legajo_doc`),
  KEY `idx_legajo_doc_log_id_legajo` (`id_legajo`),
  KEY `idx_legajo_doc_log_id_legajo_doc` (`id_legajo_doc`),
  KEY `idx_legajo_doc_log_accion` (`accion`),
  KEY `idx_legajo_doc_log_fecha` (`fecha_evento`),
  KEY `idx_legajo_doc_log_usuario` (`id_usuario`),
  KEY `fk_cfg_legajo_documento_log_requisito` (`id_requisito`),
  KEY `fk_cfg_legajo_documento_log_documento` (`id_documento_maestro`),
  CONSTRAINT `fk_cfg_legajo_documento_log_documento` FOREIGN KEY (`id_documento_maestro`) REFERENCES `cfg_catalogo_documentos` (`id_documento_maestro`),
  CONSTRAINT `fk_cfg_legajo_documento_log_legajo` FOREIGN KEY (`id_legajo`) REFERENCES `cfg_legajo` (`id_legajo`),
  CONSTRAINT `fk_cfg_legajo_documento_log_legajo_doc` FOREIGN KEY (`id_legajo_doc`) REFERENCES `cfg_legajo_documento` (`id_legajo_doc`),
  CONSTRAINT `fk_cfg_legajo_documento_log_requisito` FOREIGN KEY (`id_requisito`) REFERENCES `cfg_matriz_requisitos` (`id_requisito`),
  CONSTRAINT `fk_cfg_legajo_documento_log_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_legajo_log
CREATE TABLE IF NOT EXISTS `cfg_legajo_log` (
  `id_log_legajo` int NOT NULL AUTO_INCREMENT,
  `id_legajo` int NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `detalle` text COLLATE utf8mb4_spanish_ci,
  `estado_anterior` varchar(30) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `estado_nuevo` varchar(30) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_spanish_ci,
  `id_usuario` int DEFAULT NULL,
  `nombre_host` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `ip_host` varchar(45) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha_evento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log_legajo`),
  KEY `idx_legajo_log_id_legajo` (`id_legajo`),
  KEY `idx_legajo_log_accion` (`accion`),
  KEY `idx_legajo_log_fecha` (`fecha_evento`),
  KEY `idx_legajo_log_usuario` (`id_usuario`),
  CONSTRAINT `fk_cfg_legajo_log_legajo` FOREIGN KEY (`id_legajo`) REFERENCES `cfg_legajo` (`id_legajo`),
  CONSTRAINT `fk_cfg_legajo_log_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_matriz_requisitos
CREATE TABLE IF NOT EXISTS `cfg_matriz_requisitos` (
  `id_requisito` int NOT NULL AUTO_INCREMENT,
  `id_tipo_legajo` int DEFAULT NULL,
  `id_tipoDoc` int DEFAULT NULL,
  `id_documento_maestro` int NOT NULL COMMENT 'FK a cfg_catalogo_documentos',
  `rol_vinculado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT 'TITULAR' COMMENT 'TITULAR, CONYUGE, CODEUDOR',
  `es_obligatorio` tinyint(1) DEFAULT '1',
  `orden_visual` int DEFAULT '1' COMMENT 'Orden para mostrar en la pantalla de carga',
  `permite_reemplazo` tinyint(1) DEFAULT '1' COMMENT 'Si se puede actualizar/pisar',
  `politica_actualizacion` varchar(20) COLLATE utf8mb4_spanish2_ci NOT NULL DEFAULT 'REEMPLAZAR',
  PRIMARY KEY (`id_requisito`),
  KEY `FK_matriz_tipo` (`id_tipoDoc`),
  KEY `FK_matriz_doc` (`id_documento_maestro`),
  KEY `idx_cfg_matriz_requisitos_tipo_legajo` (`id_tipo_legajo`),
  CONSTRAINT `fk_cfg_matriz_requisitos_tipo_legajo` FOREIGN KEY (`id_tipo_legajo`) REFERENCES `cfg_tipo_legajo` (`id_tipo_legajo`),
  CONSTRAINT `FK_matriz_doc` FOREIGN KEY (`id_documento_maestro`) REFERENCES `cfg_catalogo_documentos` (`id_documento_maestro`),
  CONSTRAINT `chk_cfg_matriz_politica_actualizacion` CHECK ((`politica_actualizacion` in (_utf8mb4'REEMPLAZAR',_utf8mb4'UNIR_AL_INICIO',_utf8mb4'UNIR_AL_FINAL',_utf8mb4'NO_PERMITIR',_utf8mb4'CONSULTAR')))
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_politicas_actualizacion
CREATE TABLE IF NOT EXISTS `cfg_politicas_actualizacion` (
  `id_politica` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(30) NOT NULL,
  `etiqueta` varchar(60) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_politica`),
  UNIQUE KEY `uk_clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_relaciones
CREATE TABLE IF NOT EXISTS `cfg_relaciones` (
  `id_relacion` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `orden` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_relacion`),
  UNIQUE KEY `uk_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT IGNORE INTO `cfg_relaciones` (`nombre`, `activo`, `orden`) VALUES
  ('TITULAR', 1, 1),
  ('CONYUGE', 1, 2),
  ('CODEUDOR', 1, 3);

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.cfg_tipo_legajo
CREATE TABLE IF NOT EXISTS `cfg_tipo_legajo` (
  `id_tipo_legajo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_spanish_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `requiere_nro_solicitud` tinyint(1) NOT NULL DEFAULT '0',
  `sello_caratula_texto` varchar(255) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `sello_caratula_posicion` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'cruzado',
  `sello_anexos_texto` varchar(120) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `sello_anexos_posicion` varchar(20) COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'derecha',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_tipo_legajo`),
  UNIQUE KEY `uk_cfg_tipo_legajo_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `telefono` varchar(30) NOT NULL,
  `direccion` text NOT NULL,
  `correo` varchar(100) NOT NULL,
  `total_pag` int DEFAULT NULL,
  `legajo_marca_agua_texto` varchar(255) DEFAULT NULL,
  `legajo_marca_agua_activa` tinyint(1) NOT NULL DEFAULT '0',
  `legajo_marca_agua_posicion` varchar(20) NOT NULL DEFAULT 'cruzado',
  `legajo_sello_texto` varchar(120) DEFAULT NULL,
  `legajo_sello_activo` tinyint(1) NOT NULL DEFAULT '0',
  `legajo_sello_posicion` varchar(20) NOT NULL DEFAULT 'derecha',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `configuracion` (`id`, `nombre`, `telefono`, `direccion`, `correo`, `total_pag`, `legajo_marca_agua_texto`, `legajo_marca_agua_activa`, `legajo_marca_agua_posicion`, `legajo_sello_texto`, `legajo_sello_activo`, `legajo_sello_posicion`)
VALUES (1, 'Scantec', '', '', '', 0, NULL, 0, 'cruzado', NULL, 0, 'derecha')
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`);

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.detalle_proceso
CREATE TABLE IF NOT EXISTS `detalle_proceso` (
  `id_detalle_proceso` int NOT NULL AUTO_INCREMENT,
  `id_registro` int NOT NULL,
  `documento` varchar(60) NOT NULL,
  `total_pag` int NOT NULL,
  PRIMARY KEY (`id_detalle_proceso`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.document_views
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.expediente
CREATE TABLE IF NOT EXISTS `expediente` (
  `id_expediente` int NOT NULL AUTO_INCREMENT,
  `id_proceso` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_tipoDoc` int DEFAULT (0),
  `indice_01` varchar(254) NOT NULL,
  `indice_02` varchar(254) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `indice_03` varchar(254) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `indice_04` varchar(254) NOT NULL,
  `indice_05` varchar(254) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `indice_06` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `ruta_original` varchar(254) NOT NULL,
  `ubicacion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `paginas` int NOT NULL,
  `fecha_indexado` date DEFAULT NULL,
  `fecha_firma` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `firma_digital` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `id_requisito` int DEFAULT NULL COMMENT 'FK a cfg_matriz_requisitos. Define QUÃ‰ ES este archivo',
  `id_legajo_agrupador` varchar(50) DEFAULT NULL COMMENT 'El Nro de CÃ©dula o Solicitud que agrupa todo el legajo',
  `rol_persona` varchar(20) DEFAULT NULL COMMENT 'Redundancia Ãºtil: TITULAR, CONYUGE',
  `fecha_emision_doc` date DEFAULT NULL COMMENT 'Fecha impresa en el documento',
  `fecha_vencimiento_doc` date DEFAULT NULL COMMENT 'Calculada automÃ¡ticamente: emision + dias_vigencia',
  `estado_validacion` varchar(20) DEFAULT 'PENDIENTE' COMMENT 'VIGENTE, POR_VENCER, VENCIDO, RECHAZADO, HISTORICO',
  PRIMARY KEY (`id_expediente`),
  KEY `id_tipoDoc_FK` (`id_tipoDoc`),
  KEY `idx_v_expedientes_indice_01` (`indice_01`),
  KEY `idx_v_expedientes_indice_02` (`indice_02`),
  KEY `idx_v_expedientes_indice_03` (`indice_03`),
  KEY `idx_v_expedientes_indice_04` (`indice_04`),
  KEY `idx_v_expedientes_estado` (`estado`),
  KEY `FK_expediente_requisito` (`id_requisito`),
  KEY `idx_legajo_vivo_agrupador` (`id_tipoDoc`,`id_legajo_agrupador`),
  KEY `idx_estado_vencimiento` (`estado_validacion`,`fecha_vencimiento_doc`),
  CONSTRAINT `FK_expediente_requisito` FOREIGN KEY (`id_requisito`) REFERENCES `cfg_matriz_requisitos` (`id_requisito`),
  CONSTRAINT `id_tipoDoc_FK` FOREIGN KEY (`id_tipoDoc`) REFERENCES `tipo_documento` (`id_tipoDoc`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.intentos_login_fallidos
CREATE TABLE IF NOT EXISTS `intentos_login_fallidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `direccion_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `nombre_pc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `timestamp` timestamp NULL DEFAULT (now()),
  `motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.ldap_datos
CREATE TABLE IF NOT EXISTS `ldap_datos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ldapHost` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapPort` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapUser` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapPass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `ldapBaseDn` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT NULL,
  `fecha_sincronizacion` timestamp NULL DEFAULT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.logs
CREATE TABLE IF NOT EXISTS `logs` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `fecha` datetime NOT NULL,
  `executedSQL` text NOT NULL,
  `reverseSQL` text NOT NULL,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB AUTO_INCREMENT=4075 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.logs_umango
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.permisos_documentos
CREATE TABLE IF NOT EXISTS `permisos_documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_grupo` int DEFAULT NULL,
  `id_tipoDoc` int DEFAULT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_grupo` (`id_grupo`),
  KEY `idx_permisos_documentos_tipoDoc` (`id_tipoDoc`),
  CONSTRAINT `permisos_documentos_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `usu_grupo` (`id_grupo`),
  CONSTRAINT `permisos_documentos_ibfk_2` FOREIGN KEY (`id_tipoDoc`) REFERENCES `tipo_documento` (`id_tipoDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.permisos_legajos
CREATE TABLE IF NOT EXISTS `permisos_legajos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_spanish_ci NOT NULL,
  `permitido` tinyint(1) NOT NULL DEFAULT '0',
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rol_accion` (`id_rol`,`accion`),
  CONSTRAINT `fk_permisos_legajos_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.rol_permisos
CREATE TABLE IF NOT EXISTS `rol_permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `controlador` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `accion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `permitido` tinyint(1) DEFAULT '1',
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'activo',
  PRIMARY KEY (`id_permiso`) USING BTREE,
  UNIQUE KEY `uk_rol_accion` (`id_rol`,`controlador`,`accion`) USING BTREE,
  CONSTRAINT `FK_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `roles`
  ADD COLUMN IF NOT EXISTS `id_departamento` INT NULL DEFAULT NULL AFTER `descripcion`,
  ADD COLUMN IF NOT EXISTS `estado` VARCHAR(20) NOT NULL DEFAULT 'activo' AFTER `id_departamento`;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.smtp_datos
CREATE TABLE IF NOT EXISTS `smtp_datos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `host` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `smtpsecure` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `port` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci ROW_FORMAT=DYNAMIC;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.tarea_programada
CREATE TABLE IF NOT EXISTS `tarea_programada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre_tarea` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo_informe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `frecuencia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `dias_alerta` int DEFAULT NULL,
  `fecha_proxima_ejecucion` datetime DEFAULT NULL,
  `fecha_ultima_ejecucion` datetime DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT (now()),
  `estado` enum('activo','inactivo') CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci DEFAULT 'activo',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.tipo_documento
CREATE TABLE IF NOT EXISTS `tipo_documento` (
  `id_tipoDoc` int NOT NULL AUTO_INCREMENT,
  `nombre_tipoDoc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `codigo_proceso` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL COMMENT 'Ej: CREDITO, RRHH, PROVEEDORES',
  `indice_1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_3` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_4` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_5` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `indice_6` varchar(50) COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_tipoDoc`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.ubicacion
CREATE TABLE IF NOT EXISTS `ubicacion` (
  `id_ubicacion` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(200) NOT NULL,
  PRIMARY KEY (`id_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.unirpdf
CREATE TABLE IF NOT EXISTS `unirpdf` (
  `id_unir` int NOT NULL AUTO_INCREMENT,
  `columna_01` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `columna_02` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `columna_03` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `ruta_creacion` varchar(150) NOT NULL,
  `nombre_archivo` varchar(80) NOT NULL,
  PRIMARY KEY (`id_unir`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.usu_grupo
CREATE TABLE IF NOT EXISTS `usu_grupo` (
  `id_grupo` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  `estado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci DEFAULT NULL,
  PRIMARY KEY (`id_grupo`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para tabla scantec_basic.usuarios
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
  `email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `fuente_registro` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `FK_usuarios_roles` (`id_rol`),
  KEY `FK_usuarios_usu_grupo` (`id_grupo`),
  CONSTRAINT `FK_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  CONSTRAINT `FK_usuarios_usu_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `usu_grupo` (`id_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para vista scantec_basic.v_expedientes
-- Creando tabla temporal para superar errores de dependencia de VIEW
DROP VIEW IF EXISTS `v_expedientes`;
DROP TABLE IF EXISTS `v_expedientes`;
CREATE TABLE `v_expedientes` (
	`id_expediente` INT NOT NULL,
	`id_proceso` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`id_tipoDoc` INT NULL,
	`nombre_tipoDoc` VARCHAR(1) NULL COLLATE 'utf8mb4_spanish2_ci',
	`indice_01` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_02` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_03` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_04` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_05` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`indice_06` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
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

-- Volcando estructura para vista scantec_basic.v_visitas
-- Creando tabla temporal para superar errores de dependencia de VIEW
DROP VIEW IF EXISTS `v_visitas`;
DROP TABLE IF EXISTS `v_visitas`;
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

-- Volcando estructura para tabla scantec_basic.visitas
CREATE TABLE IF NOT EXISTS `visitas` (
  `id_visita` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) COLLATE utf8mb4_spanish_ci DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `servidor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `id` int NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id_visita`)
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- La exportaciÃ³n de datos fue deseleccionada.

-- Volcando estructura para disparador scantec_basic.after_delete_expedientes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DROP TRIGGER IF EXISTS `after_delete_expedientes`;
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

-- Volcando estructura para disparador scantec_basic.after_update_expedientes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DROP TRIGGER IF EXISTS `after_update_expedientes`;
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

-- Volcando estructura para disparador scantec_basic.after_update_usuarios
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DROP TRIGGER IF EXISTS `after_update_usuarios`;
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
DROP TABLE IF EXISTS `v_expedientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_expedientes` AS select `a`.`id_expediente` AS `id_expediente`,`a`.`id_proceso` AS `id_proceso`,`a`.`id_tipoDoc` AS `id_tipoDoc`,`b`.`nombre_tipoDoc` AS `nombre_tipoDoc`,`a`.`indice_01` AS `indice_01`,`a`.`indice_02` AS `indice_02`,`a`.`indice_03` AS `indice_03`,`a`.`indice_04` AS `indice_04`,`a`.`indice_05` AS `indice_05`,`a`.`indice_06` AS `indice_06`,`a`.`ubicacion` AS `ubicacion`,`a`.`ruta_original` AS `ruta_original`,`a`.`estado` AS `estado`,`a`.`paginas` AS `paginas`,`a`.`fecha_indexado` AS `fecha_indexado`,`a`.`fecha_firma` AS `fecha_firma`,`a`.`fecha_vencimiento` AS `fecha_vencimiento`,`a`.`version` AS `version`,`a`.`firma_digital` AS `firma_digital` from (`expediente` `a` join `tipo_documento` `b`) where (`a`.`id_tipoDoc` = `b`.`id_tipoDoc`) order by `a`.`id_expediente`
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

-- --------------------------------------------------------
-- Ajustes de compatibilidad para instalacion actual
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `departamentos` (
  `id_departamento` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `estado` VARCHAR(20) NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_departamento`),
  UNIQUE KEY `uk_departamentos_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

ALTER TABLE `roles`
  ADD COLUMN IF NOT EXISTS `estado` VARCHAR(20) NOT NULL DEFAULT 'activo';

ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `departamento` VARCHAR(100) NULL DEFAULT NULL AFTER `nombre`,
  ADD COLUMN IF NOT EXISTS `id_departamento` INT NULL DEFAULT NULL AFTER `departamento`,
  ADD COLUMN IF NOT EXISTS `forzar_cambio_clave` TINYINT(1) NOT NULL DEFAULT 0 AFTER `clave_actualizacion`;

INSERT INTO `departamentos` (`id_departamento`, `nombre`, `estado`)
VALUES (1, 'General', 'ACTIVO')
ON DUPLICATE KEY UPDATE
  `nombre` = VALUES(`nombre`),
  `estado` = VALUES(`estado`);

INSERT INTO `roles` (`id_rol`, `descripcion`, `estado`)
VALUES
  (1, 'Administrador Scantec', 'activo'),
  (2, 'Administrador', 'activo')
ON DUPLICATE KEY UPDATE
  `descripcion` = VALUES(`descripcion`),
  `estado` = VALUES(`estado`);

INSERT INTO `usu_grupo` (`id_grupo`, `descripcion`, `estado`)
VALUES (1, 'GENERAL', 'ACTIVO')
ON DUPLICATE KEY UPDATE
  `descripcion` = VALUES(`descripcion`),
  `estado` = VALUES(`estado`);

DELETE FROM `usuarios` WHERE LOWER(TRIM(`usuario`)) IN ('root', 'scantec');

INSERT INTO `usuarios` (
  `id`,
  `nombre`,
  `departamento`,
  `id_departamento`,
  `usuario`,
  `clave`,
  `ultimo_acceso`,
  `cantidad_inicio`,
  `id_rol`,
  `estado_usuario`,
  `id_grupo`,
  `clave_actualizacion`,
  `forzar_cambio_clave`,
  `email`,
  `fuente_registro`
) VALUES (
  1,
  'Administrador Scantec',
  'General',
  1,
  'scantec',
  '$2y$12$tKmykvE9A0jrUsM2XdleduIW5Ch7F9QuYkHzcmL2X.i1hgoxyvk.K',
  NULL,
  0,
  1,
  'ACTIVO',
  1,
  NULL,
  0,
  NULL,
  'seed_sql'
);

ALTER TABLE `usuarios` AUTO_INCREMENT = 2;
