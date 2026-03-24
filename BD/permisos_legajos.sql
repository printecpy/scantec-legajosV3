-- ============================================================
-- Tabla: permisos_legajos
-- Controla qué acciones/vistas de legajos puede usar cada grupo
-- ============================================================

CREATE TABLE IF NOT EXISTS `permisos_legajos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_grupo` int NOT NULL,
  `accion` varchar(50) NOT NULL COMMENT 'Ej: armar_legajo, verificar_legajos, eliminar_legajo',
  `permitido` tinyint(1) NOT NULL DEFAULT 0,
  `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_grupo_accion` (`id_grupo`, `accion`),
  CONSTRAINT `fk_permisos_legajos_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `usu_grupo` (`id_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
