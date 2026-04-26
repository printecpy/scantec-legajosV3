<?php

class SeguridadLegajosModel extends Mysql
{
    public const ACCION_VER_LEGAJOS_OTROS = 'ver_legajos_otros';

    public function __construct()
    {
        parent::__construct();
        $this->ensureRolesDepartamentoColumn();
        $this->ensurePermisosLegajosTiposTable();
    }

    private function ensureRolesDepartamentoColumn(): void
    {
        try {
            $tablaRoles = $this->select_all("SHOW TABLES LIKE 'roles'");
            if (empty($tablaRoles)) {
                return;
            }

            $columnaDepartamento = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");
            if (empty($columnaDepartamento)) {
                $this->update("ALTER TABLE roles ADD COLUMN id_departamento INT NULL DEFAULT NULL AFTER descripcion", []);
            }
        } catch (Throwable $e) {
            // No interrumpimos la carga si la base aun no esta alineada.
        }
    }

    /**
     * Verifica si una tabla existe en la base de datos.
     */
    private function existeTabla(string $tabla): bool
    {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $result = $this->select($sql, [$tabla]);
            return !empty($result);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function existeColumna(string $tabla, string $columna): bool
    {
        try {
            $result = $this->select_all("SHOW COLUMNS FROM `{$tabla}` LIKE ?", [$columna]);
            return !empty($result);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function existeIndice(string $tabla, string $indice): bool
    {
        try {
            $result = $this->select_all("SHOW INDEX FROM `{$tabla}` WHERE Key_name = ?", [$indice]);
            return !empty($result);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function ensurePermisosLegajosTiposTable(): void
    {
        try {
            if (!$this->existeTabla('permisos_legajos_tipos')) {
                $this->update(
                    "CREATE TABLE IF NOT EXISTS `permisos_legajos_tipos` (
                        `id` int NOT NULL AUTO_INCREMENT,
                        `id_rol` int NOT NULL,
                        `id_tipo_legajo` int NOT NULL,
                        `permitido` tinyint(1) NOT NULL DEFAULT 0,
                        `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `uk_rol_tipo_legajo` (`id_rol`, `id_tipo_legajo`),
                        KEY `idx_tipo_legajo` (`id_tipo_legajo`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci",
                    []
                );
                return;
            }

            if (!$this->existeColumna('permisos_legajos_tipos', 'id_tipo_legajo') && $this->existeColumna('permisos_legajos_tipos', 'id_tipoDoc')) {
                $this->update("ALTER TABLE `permisos_legajos_tipos` CHANGE `id_tipoDoc` `id_tipo_legajo` INT NOT NULL", []);
            }

            if (!$this->existeColumna('permisos_legajos_tipos', 'permitido')) {
                $this->update("ALTER TABLE `permisos_legajos_tipos` ADD COLUMN `permitido` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_tipo_legajo`", []);
            }

            if (!$this->existeColumna('permisos_legajos_tipos', 'actualizado_en')) {
                $this->update("ALTER TABLE `permisos_legajos_tipos` ADD COLUMN `actualizado_en` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", []);
            }

            if (!$this->existeIndice('permisos_legajos_tipos', 'uk_rol_tipo_legajo')) {
                $this->update("ALTER TABLE `permisos_legajos_tipos` ADD UNIQUE KEY `uk_rol_tipo_legajo` (`id_rol`, `id_tipo_legajo`)", []);
            }

            if (!$this->existeIndice('permisos_legajos_tipos', 'idx_tipo_legajo')) {
                $this->update("ALTER TABLE `permisos_legajos_tipos` ADD KEY `idx_tipo_legajo` (`id_tipo_legajo`)", []);
            }
        } catch (Throwable $e) {
            error_log("Error asegurando estructura de permisos_legajos_tipos: " . $e->getMessage());
        }
    }

    /**
     * Lista fija de acciones/vistas configurables para legajos y seguridad.
     * Se divide en dos categorías: vistas (sidebar) y acciones internas.
     */
    public static function getAccionesDisponibles(): array
    {
        return [
            // Vistas de Legajos (aparecen en el sidebar)
            'dashboard_legajos' => [
                'etiqueta' => 'Dashboard Legajos',
                'icono' => 'fas fa-chart-line',
                'tipo' => 'vista'
            ],
            'armar_legajo' => [
                'etiqueta' => 'Armar Legajo',
                'icono' => 'fas fa-file-medical',
                'tipo' => 'vista'
            ],
            'buscar_legajos' => [
                'etiqueta' => 'Buscar Legajos',
                'icono' => 'fas fa-search',
                'tipo' => 'vista'
            ],
            'verificar_legajos' => [
                'etiqueta' => 'Verificar Legajos',
                'icono' => 'fas fa-check-double',
                'tipo' => 'vista'
            ],
            'administrar_legajos' => [
                'etiqueta' => 'Administrar Legajos',
                'icono' => 'fas fa-cogs',
                'tipo' => 'vista'
            ],
            'log_legajos' => [
                'etiqueta' => 'Log Legajos',
                'icono' => 'fas fa-history',
                'tipo' => 'vista'
            ],
            // Vistas de Seguridad
            'gestionar_roles' => [
                'etiqueta' => 'Gestionar Roles',
                'icono' => 'fas fa-users-cog',
                'tipo' => 'vista'
            ],
            'permisos_legajos' => [
                'etiqueta' => 'Permisos de Legajos',
                'icono' => 'fas fa-shield-alt',
                'tipo' => 'vista'
            ],
            // Acciones internas de Legajos
            'eliminar_legajo' => [
                'etiqueta' => 'Eliminar Legajo',
                'icono' => 'fas fa-trash-alt',
                'tipo' => 'accion'
            ],
            'generar_pdf' => [
                'etiqueta' => 'Generar PDF',
                'icono' => 'fas fa-file-pdf',
                'tipo' => 'accion'
            ],
            'cargar_documento' => [
                'etiqueta' => 'Cargar Documento',
                'icono' => 'fas fa-upload',
                'tipo' => 'accion'
            ],
            'eliminar_documento' => [
                'etiqueta' => 'Eliminar Documento',
                'icono' => 'fas fa-file-excel',
                'tipo' => 'accion'
            ],
            // Acciones de Seguridad
            'crear_rol' => [
                'etiqueta' => 'Crear Rol',
                'icono' => 'fas fa-plus-circle',
                'tipo' => 'accion'
            ],
            'editar_rol' => [
                'etiqueta' => 'Editar Rol',
                'icono' => 'fas fa-edit',
                'tipo' => 'accion'
            ],
            'eliminar_rol' => [
                'etiqueta' => 'Eliminar Rol',
                'icono' => 'fas fa-trash',
                'tipo' => 'accion'
            ],
            'cambiar_estado_rol' => [
                'etiqueta' => 'Cambiar Estado Rol',
                'icono' => 'fas fa-toggle-on',
                'tipo' => 'accion'
            ],
            'gestionar_permisos' => [
                'etiqueta' => 'Gestionar Permisos',
                'icono' => 'fas fa-key',
                'tipo' => 'accion'
            ],
        ];
    }

    public static function getDashboardCardsDisponibles(): array
    {
        return [
            'dashboard_card_legajos_proceso' => ['etiqueta' => 'Legajos en proceso', 'icono' => 'fas fa-copy'],
            'dashboard_card_legajos_completados' => ['etiqueta' => 'Legajos completados', 'icono' => 'fas fa-folder-open'],
            'dashboard_card_legajos_rechazados' => ['etiqueta' => 'Legajos rechazados', 'icono' => 'fas fa-circle-xmark'],
            'dashboard_card_legajos_verificados' => ['etiqueta' => 'Legajos verificados', 'icono' => 'fas fa-check-double'],
            'dashboard_card_legajos_cerrados' => ['etiqueta' => 'Legajos cerrados', 'icono' => 'fas fa-box-archive'],
            'dashboard_card_docs_vigentes' => ['etiqueta' => 'Documentos vigentes', 'icono' => 'fas fa-circle-check'],
            'dashboard_card_docs_por_vencer' => ['etiqueta' => 'Documentos por vencer', 'icono' => 'fas fa-clock'],
            'dashboard_card_docs_vencidos' => ['etiqueta' => 'Documentos vencidos / faltantes', 'icono' => 'fas fa-circle-exclamation'],
            'dashboard_card_legajos_por_tipo' => ['etiqueta' => 'Legajos completados por tipo', 'icono' => 'fas fa-table-list'],
            'dashboard_card_legajos_por_usuario' => ['etiqueta' => 'Legajos verificados por usuario', 'icono' => 'fas fa-users'],
            'dashboard_card_grafico_productividad' => ['etiqueta' => 'Gráfico de productividad', 'icono' => 'fas fa-chart-line'],
        ];
    }

    /**
     * Obtiene los presets de permisos para nuevos roles.
     */
    public static function getPresetsPermisos(): array
    {
        return [
            'solo_lectura' => [
                'nombre' => 'Solo lectura',
                'descripcion' => 'Puede buscar y ver legajos, sin modificar nada.',
                'icono' => 'fas fa-eye',
                'permisos' => [
                    'buscar_legajos',
                ]
            ],
            'basico' => [
                'nombre' => 'Basico',
                'descripcion' => 'Puede armar, buscar, ver y editar legajos, sin tareas administrativas.',
                'icono' => 'fas fa-tasks',
                'permisos' => [
                    'dashboard_legajos',
                    'armar_legajo',
                    'buscar_legajos',
                    'cargar_documento',
                    'eliminar_documento',
                    'generar_pdf',
                ]
            ],
            'total' => [
                'nombre' => 'Total',
                'descripcion' => 'Puede hacer toda la operativa de legajos, incluyendo verificar, administrar, cerrar y eliminar.',
                'icono' => 'fas fa-shield-alt',
                'permisos' => [
                    'dashboard_legajos',
                    'armar_legajo',
                    'buscar_legajos',
                    'verificar_legajos',
                    'administrar_legajos',
                    'log_legajos',
                    'cargar_documento',
                    'eliminar_documento',
                    'generar_pdf',
                    'eliminar_legajo',
                ]
            ],
            'avanzado' => [
                'nombre' => 'Avanzado',
                'descripcion' => 'Incluye control total de legajos y administracion de seguridad y permisos.',
                'icono' => 'fas fa-star',
                'permisos' => [
                    'dashboard_legajos',
                    'armar_legajo',
                    'buscar_legajos',
                    'verificar_legajos',
                    'administrar_legajos',
                    'log_legajos',
                    'cargar_documento',
                    'eliminar_documento',
                    'generar_pdf',
                    'eliminar_legajo',
                    'permisos_legajos',
                    'gestionar_permisos',
                    'gestionar_roles',
                    'crear_rol',
                    'editar_rol',
                    'eliminar_rol',
                    'cambiar_estado_rol',
                ]
            ],
            'vacio' => [
                'nombre' => 'Sin permisos',
                'descripcion' => 'Sin permisos (se asignaran manualmente)',
                'icono' => 'fas fa-ban',
                'permisos' => []
            ]
        ];
    }

    /**
     * Asigna permisos por defecto a un rol basado en un preset.
     */
    public function asignarPermisosPreset(int $id_rol, string $preset): bool
    {
        $presets = self::getPresetsPermisos();

        if (!isset($presets[$preset])) {
            return false; // Preset no válido
        }

        $permisosPreset = $presets[$preset]['permisos'];

        if ($preset === 'basico') {
            $permisosPreset = [
                'dashboard_legajos',
                'armar_legajo',
                'buscar_legajos',
                'cargar_documento',
                'eliminar_documento',
                'generar_pdf',
            ];
        } elseif ($preset === 'total') {
            $permisosPreset = [
                'dashboard_legajos',
                'armar_legajo',
                'buscar_legajos',
                'verificar_legajos',
                'administrar_legajos',
                'log_legajos',
                'cargar_documento',
                'eliminar_documento',
                'generar_pdf',
                'eliminar_legajo',
            ];
        } elseif ($preset === 'avanzado') {
            $permisosPreset = [
                'dashboard_legajos',
                'armar_legajo',
                'buscar_legajos',
                'verificar_legajos',
                'administrar_legajos',
                'log_legajos',
                'cargar_documento',
                'eliminar_documento',
                'generar_pdf',
                'eliminar_legajo',
                'permisos_legajos',
                'gestionar_permisos',
                'gestionar_roles',
                'crear_rol',
                'editar_rol',
                'eliminar_rol',
                'cambiar_estado_rol',
            ];
        } elseif ($preset === 'solo_lectura') {
            $permisosPreset = [
                'buscar_legajos',
            ];
        }

        return $this->guardarPermisosLegajos($id_rol, array_flip($permisosPreset));
    }

    /**
     * Obtiene todos los roles del sistema.
     */
    public function selectRoles(): array
    {
        try {
            $tablaDepartamentos = $this->select_all("SHOW TABLES LIKE 'departamentos'");
            $columnaDepartamento = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");

            if (!empty($tablaDepartamentos) && !empty($columnaDepartamento)) {
                $sql = "SELECT r.*, d.nombre AS departamento_nombre
                        FROM roles r
                        LEFT JOIN departamentos d ON d.id_departamento = r.id_departamento
                        ORDER BY r.id_rol";
                $res = $this->select_all($sql);
                return is_array($res) ? $res : [];
            }
        } catch (Throwable $e) {
            // Fallback a consulta simple.
        }

        $sql = "SELECT * FROM roles ORDER BY id_rol";
        $res = $this->select_all($sql);
        return is_array($res) ? $res : [];
    }

    public function selectRolesVisiblesPara(int $idRolActual): array
    {
        $roles = $this->selectRoles();
        if ($idRolActual === 1) {
            return $roles;
        }

        return array_values(array_filter($roles, static function ($rol) {
            return intval($rol['id_rol'] ?? 0) !== 1;
        }));
    }

    public function selectRolPorId(int $id_rol): array
    {
        try {
            $tablaDepartamentos = $this->select_all("SHOW TABLES LIKE 'departamentos'");
            $columnaDepartamento = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");

            if (!empty($tablaDepartamentos) && !empty($columnaDepartamento)) {
                $sql = "SELECT r.*, d.nombre AS departamento_nombre
                        FROM roles r
                        LEFT JOIN departamentos d ON d.id_departamento = r.id_departamento
                        WHERE r.id_rol = ? LIMIT 1";
                $res = $this->select_all($sql, [$id_rol]);
                return !empty($res[0]) ? $res[0] : [];
            }
        } catch (Throwable $e) {
            // Fallback a consulta simple.
        }

        $sql = "SELECT * FROM roles WHERE id_rol = ? LIMIT 1";
        $res = $this->select_all($sql, [$id_rol]);
        return !empty($res[0]) ? $res[0] : [];
    }

    public function existeDescripcionRol(string $descripcion, int $idRolExcluir = 0): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM roles WHERE descripcion = ?";
        $params = [$descripcion];

        if ($idRolExcluir > 0) {
            $sql .= " AND id_rol <> ?";
            $params[] = $idRolExcluir;
        }

        $res = $this->select($sql, $params);
        return intval($res['total'] ?? 0) > 0;
    }

    // insertarRol() eliminado — usar insertarRolConPermisos() que también asigna el preset.

    /**
     * Inserta un nuevo rol y asigna permisos por defecto.
     * Retorna el ID del rol creado o null en caso de error.
     */
    public function insertarRolConPermisos(string $descripcion, string $preset = 'basico', int $idDepartamento = 0): ?int
    {
        try {
            // Insertar el rol
            $tablaDepartamentos = $this->select_all("SHOW TABLES LIKE 'departamentos'");
            $columnaDepartamento = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");
            $usaDepartamento = !empty($tablaDepartamentos) && !empty($columnaDepartamento);

            $sql = $usaDepartamento
                ? "INSERT INTO roles (descripcion, id_departamento, estado) VALUES (?, ?, 'activo')"
                : "INSERT INTO roles (descripcion, estado) VALUES (?, 'activo')";
            try {
                $this->insert($sql, $usaDepartamento ? [$descripcion, $idDepartamento > 0 ? $idDepartamento : null] : [$descripcion]);
            } catch (Throwable $e) {
                // Intento sin estado por si no existe la columna
                $sql = $usaDepartamento
                    ? "INSERT INTO roles (descripcion, id_departamento) VALUES (?, ?)"
                    : "INSERT INTO roles (descripcion) VALUES (?)";
                $this->insert($sql, $usaDepartamento ? [$descripcion, $idDepartamento > 0 ? $idDepartamento : null] : [$descripcion]);
            }

            // Obtener el ID del rol insertado
            $sqlGetId = "SELECT id_rol FROM roles WHERE descripcion = ? ORDER BY id_rol DESC LIMIT 1";
            $resultado = $this->select($sqlGetId, [$descripcion]);
            if (is_array($resultado) && !isset($resultado['id_rol']) && !empty($resultado[0])) {
                $resultado = $resultado[0];
            }
            
            if (empty($resultado)) {
                return null;
            }

            $id_rol = intval($resultado['id_rol'] ?? 0);
            
            if ($id_rol <= 0) {
                return null;
            }

            // Asignar permisos por defecto
            if ($this->asignarPermisosPreset($id_rol, $preset)) {
                return $id_rol;
            }

            return null;
        } catch (Throwable $e) {
            error_log("Error insertando rol con permisos: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Elimina un rol.
     */
    public function eliminarRol(int $id_rol): bool
    {
        $sql = "UPDATE roles SET estado = 'inactivo' WHERE id_rol = ?";
        return (bool)$this->update($sql, [$id_rol]);
    }

    /**
     * Cuenta cuántos usuarios tienen asignado este rol.
     */
    public function contarUsuariosPorRol(int $id_rol): int
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = ? AND (estado = 'activo' OR estado = 1)";
        try {
            $res = $this->select($sql, [$id_rol]);
            return intval($res['total'] ?? 0);
        } catch (Throwable $e) {
            // Si la columna estado no existe o es diferente en usuarios
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = ?";
            $res = $this->select($sql, [$id_rol]);
            return intval($res['total'] ?? 0);
        }
    }

    /**
     * Activa o desactiva un rol.
     */
    public function cambiarEstadoRol(int $id_rol, string $nuevoEstado): bool
    {
        try {
            $sql = "UPDATE roles SET estado = ? WHERE id_rol = ?";
            return (bool)$this->update($sql, [$nuevoEstado, $id_rol]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function actualizarRol(int $id_rol, string $descripcion, int $idDepartamento = 0): bool
    {
        try {
            $columnaDepartamento = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");
            if (!empty($columnaDepartamento)) {
                $sql = "UPDATE roles SET descripcion = ?, id_departamento = ? WHERE id_rol = ?";
                return (bool)$this->update($sql, [$descripcion, $idDepartamento > 0 ? $idDepartamento : null, $id_rol]);
            }

            $sql = "UPDATE roles SET descripcion = ? WHERE id_rol = ?";
            return (bool)$this->update($sql, [$descripcion, $id_rol]);
        } catch (Throwable $e) {
            error_log("Error actualizando rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los permisos de legajos de todos los roles.
     * Retorna array indexado: [id_rol][accion] => permitido (0|1)
     */
    public function selectTodosPermisosLegajos(): array
    {
        $sql = "SELECT id_rol, accion, permitido FROM permisos_legajos";
        $rows = $this->select_all($sql);
        $mapa = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $mapa[intval($row['id_rol'])][$row['accion']] = intval($row['permitido']);
            }
        }
        return $mapa;
    }

    /**
     * Obtiene los permisos de legajos de un rol específico.
     * Retorna array: [accion => permitido]
     */
    public function selectPermisosLegajosPorRol(int $id_rol): array
    {
        $sql = "SELECT accion, permitido FROM permisos_legajos WHERE id_rol = ?";
        $rows = $this->select($sql, [$id_rol]);
        $mapa = [];
        if (!empty($rows)) {
            // select() puede retornar un solo array o array de arrays
            if (isset($rows['accion'])) {
                $mapa[$rows['accion']] = intval($rows['permitido']);
            } else {
                foreach ($rows as $row) {
                    $mapa[$row['accion']] = intval($row['permitido']);
                }
            }
        }
        return $mapa;
    }

    /**
     * Verifica si un rol tiene un permiso específico.
     * Retorna true si tiene el permiso, false en caso contrario.
     */
    public function tienePermisoLegajo(int $id_rol, string $accion): bool
    {
        // Bypass para Super Admin (rol 1): siempre tiene todos los permisos
        if ($id_rol === 1) {
            return true;
        }

        try {
            $sql = "SELECT permitido FROM permisos_legajos WHERE id_rol = ? AND accion = ?";
            $resultado = $this->select($sql, [$id_rol, $accion]);
            
            if (empty($resultado)) {
                return false;
            }

            if (is_array($resultado) && isset($resultado[0]) && is_array($resultado[0])) {
                $resultado = $resultado[0];
            }
            
            return intval($resultado['permitido'] ?? 0) === 1;
        } catch (Throwable $e) {
            error_log("Error verificando permiso $accion para rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda los permisos de un rol (INSERT ON DUPLICATE KEY UPDATE).
     */
    public function guardarPermisosLegajos(int $id_rol, array $acciones): bool
    {
        $todasLasAcciones = array_keys(self::getAccionesDisponibles());

        try {
            foreach ($todasLasAcciones as $accion) {
                $permitido = isset($acciones[$accion]) ? 1 : 0;
                $sql = "INSERT INTO permisos_legajos (id_rol, accion, permitido) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE permitido = VALUES(permitido), actualizado_en = NOW()";
                $this->insert($sql, [$id_rol, $accion, $permitido]);
            }
            return true;
        } catch (Throwable $e) {
            error_log("Error guardando permisos legajos para rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la configuración de visibilidad de legajos ajenos por rol.
     * Retorna array indexado: [id_rol] => 0|1
     */
    public function selectVisibilidadLegajosOtrosPorRol(): array
    {
        $sql = "SELECT id_rol, permitido
                FROM permisos_legajos
                WHERE accion = ?";
        $rows = $this->select_all($sql, [self::ACCION_VER_LEGAJOS_OTROS]);
        $mapa = [];
        if (!empty($rows)) {
            foreach ($rows as $row) {
                $mapa[intval($row['id_rol'])] = intval($row['permitido'] ?? 0);
            }
        }
        return $mapa;
    }

    /**
     * Retorna true si el rol puede ver legajos creados por otros usuarios.
     * Admins conservan acceso total.
     */
    public function puedeVerLegajosOtrosUsuarios(int $id_rol): bool
    {
        if ($id_rol === 1) {
            return true;
        }

        try {
            $sql = "SELECT permitido
                    FROM permisos_legajos
                    WHERE id_rol = ? AND accion = ?";
            $resultado = $this->select($sql, [$id_rol, self::ACCION_VER_LEGAJOS_OTROS]);

            if (empty($resultado)) {
                return false;
            }

            if (is_array($resultado) && isset($resultado[0]) && is_array($resultado[0])) {
                $resultado = $resultado[0];
            }

            return intval($resultado['permitido'] ?? 0) === 1;
        } catch (Throwable $e) {
            error_log("Error verificando visibilidad de legajos ajenos para rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda la configuración de visibilidad de legajos ajenos por rol.
     */
    public function guardarVisibilidadLegajosOtros(int $id_rol, bool $permitido): bool
    {
        try {
            $sql = "INSERT INTO permisos_legajos (id_rol, accion, permitido)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE permitido = VALUES(permitido), actualizado_en = NOW()";
            $this->insert($sql, [$id_rol, self::ACCION_VER_LEGAJOS_OTROS, $permitido ? 1 : 0]);
            return true;
        } catch (Throwable $e) {
            error_log("Error guardando visibilidad de legajos ajenos para rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    public function selectPermisosDashboardCardsPorRol(): array
    {
        $acciones = array_keys(self::getDashboardCardsDisponibles());
        if (empty($acciones)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($acciones), '?'));
        $sql = "SELECT id_rol, accion, permitido
                FROM permisos_legajos
                WHERE accion IN ($placeholders)";
        $rows = $this->select_all($sql, $acciones);
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[intval($row['id_rol'])][$row['accion']] = intval($row['permitido'] ?? 0);
        }
        return $mapa;
    }

    public function selectDashboardCardsPorRol(int $id_rol): array
    {
        if ($id_rol === 1) {
            return array_fill_keys(array_keys(self::getDashboardCardsDisponibles()), 1);
        }

        $acciones = array_keys(self::getDashboardCardsDisponibles());
        if (empty($acciones)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($acciones), '?'));
        $params = array_merge([$id_rol], $acciones);
        $sql = "SELECT accion, permitido
                FROM permisos_legajos
                WHERE id_rol = ? AND accion IN ($placeholders)";
        $rows = $this->select_all($sql, $params);
        $mapa = array_fill_keys($acciones, 0);
        foreach ($rows as $row) {
            $mapa[$row['accion']] = intval($row['permitido'] ?? 0);
        }
        return $mapa;
    }

    public function guardarPermisosDashboardCards(int $id_rol, array $cards): bool
    {
        $todasLasCards = array_keys(self::getDashboardCardsDisponibles());

        try {
            foreach ($todasLasCards as $accion) {
                $permitido = isset($cards[$accion]) ? 1 : 0;
                $sql = "INSERT INTO permisos_legajos (id_rol, accion, permitido)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE permitido = VALUES(permitido), actualizado_en = NOW()";
                $this->insert($sql, [$id_rol, $accion, $permitido]);
            }
            return true;
        } catch (Throwable $e) {
            error_log("Error guardando permisos dashboard para rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    public function selectTiposLegajoVisiblesPorRol(): array
    {
        if (!$this->existeTabla('permisos_legajos_tipos')) {
            return [];
        }

        $sql = "SELECT id_rol, id_tipo_legajo, permitido
                FROM permisos_legajos_tipos";
        $rows = $this->select_all($sql);
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[intval($row['id_rol'])][intval($row['id_tipo_legajo'])] = intval($row['permitido'] ?? 0);
        }
        return $mapa;
    }

    public function obtenerTiposLegajoPermitidosPorRol(int $id_rol, array $tiposLegajo = []): array
    {
        $idsDisponibles = array_values(array_filter(array_map(static function ($tipo) {
            return intval($tipo['id_tipo_legajo'] ?? 0);
        }, $tiposLegajo)));

        if ($id_rol === 1) {
            return $idsDisponibles;
        }

        if (!$this->existeTabla('permisos_legajos_tipos')) {
            return $idsDisponibles;
        }

        $sql = "SELECT id_tipo_legajo, permitido
                FROM permisos_legajos_tipos
                WHERE id_rol = ?";
        $rows = $this->select_all($sql, [$id_rol]);
        if (empty($rows)) {
            return [];
        }

        $permitidos = [];
        $mapaEstados = [];
        foreach ($rows as $row) {
            $mapaEstados[intval($row['id_tipo_legajo'] ?? 0)] = intval($row['permitido'] ?? 0);
        }

        foreach ($idsDisponibles as $idTipoLegajo) {
            if (array_key_exists($idTipoLegajo, $mapaEstados) && intval($mapaEstados[$idTipoLegajo]) === 1) {
                $permitidos[] = $idTipoLegajo;
            }
        }

        return array_values(array_unique(array_filter($permitidos)));
    }

    public function guardarTiposLegajoVisiblesPorRol(int $id_rol, array $tiposSeleccionados, array $tiposDisponibles): bool
    {
        if (!$this->existeTabla('permisos_legajos_tipos')) {
            return false;
        }

        $idsTipos = array_values(array_filter(array_map(static function ($tipo) {
            return intval($tipo['id_tipo_legajo'] ?? 0);
        }, $tiposDisponibles)));

        $seleccionados = array_map('intval', array_keys($tiposSeleccionados));
        $seleccionados = array_values(array_unique(array_filter($seleccionados)));

        try {
            foreach ($idsTipos as $idTipoLegajo) {
                $permitido = in_array($idTipoLegajo, $seleccionados, true) ? 1 : 0;
                $sql = "INSERT INTO permisos_legajos_tipos (id_rol, id_tipo_legajo, permitido)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE permitido = VALUES(permitido), actualizado_en = NOW()";
                $this->insert($sql, [$id_rol, $idTipoLegajo, $permitido]);
            }
            return true;
        } catch (Throwable $e) {
            error_log("Error guardando tipos visibles por rol $id_rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un cambio de permiso en el log.
     */
    public function logCambioPermiso(int $id_rol, string $accion, int $estado_anterior, int $estado_nuevo, string $detalle = '', string $observacion = ''): bool
    {
        if (!$this->existeTabla('cfg_permisos_log')) {
            return false; // Tabla no existe, no loguear
        }

        $id_usuario = $_SESSION['id'] ?? null;
        $nombre_host = gethostname();
        $ip_host = $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '127.0.0.1';

        $sql = "INSERT INTO cfg_permisos_log 
                (id_rol, accion, detalle, estado_anterior, estado_nuevo, observacion, id_usuario, nombre_host, ip_host) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $this->insert($sql, [
                $id_rol,
                $accion,
                $detalle,
                $estado_anterior,
                $estado_nuevo,
                $observacion,
                $id_usuario,
                $nombre_host,
                $ip_host
            ]);
            return true;
        } catch (Throwable $e) {
            error_log("Error registrando log de permiso: " . $e->getMessage());
            return false;
        }
    }
}

