<?php

class FuncionalidadesModel extends Mysql
{
    private const MODULOS_ITEMS = [
        'archivos' => 'Archivos',
        'legajos' => 'Legajos',
        'sistema' => 'Sistema',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public static function getModulosItemsDisponibles(): array
    {
        return self::MODULOS_ITEMS;
    }

    public static function getItemsAgrupacionDisponibles(): array
    {
        return [
            'buscador_archivos' => ['etiqueta' => 'Buscador', 'ruta' => 'expedientes/indice_busqueda', 'modulo' => 'archivos'],
            'reporte_expedientes' => ['etiqueta' => 'Reporte de expedientes', 'ruta' => 'expedientes/reporte', 'modulo' => 'archivos'],
            'subir_archivos' => ['etiqueta' => 'Subir archivos', 'ruta' => 'expedientes/upload_files', 'modulo' => 'archivos'],
            'grupos' => ['etiqueta' => 'Grupos', 'ruta' => 'usuarios/grupo', 'modulo' => 'archivos'],
            'log_documentos' => ['etiqueta' => 'Log Documentos', 'ruta' => 'logsumango/views', 'modulo' => 'archivos'],
            'visitas_archivos' => ['etiqueta' => 'Visitas Archivos', 'ruta' => 'logs/registro_views', 'modulo' => 'archivos'],
            'dashboard_legajos' => ['etiqueta' => 'Dashboard Legajos', 'ruta' => 'dashboard/dashboard_legajos', 'modulo' => 'legajos'],
            'armar_legajo' => ['etiqueta' => 'Armar legajo', 'ruta' => 'legajos/armar_legajo', 'modulo' => 'legajos'],
            'buscar_legajos' => ['etiqueta' => 'Buscar legajos', 'ruta' => 'legajos/buscar_legajos', 'modulo' => 'legajos'],
            'verificar_legajos' => ['etiqueta' => 'Verificar legajos', 'ruta' => 'legajos/verificar_legajos', 'modulo' => 'legajos'],
            'administrar_legajos' => ['etiqueta' => 'Administrar legajos', 'ruta' => 'legajos/administrar_legajos', 'modulo' => 'legajos'],
            'permisos_legajos' => ['etiqueta' => 'Permisos Legajos', 'ruta' => 'seguridad/permisos_legajos', 'modulo' => 'legajos'],
            'log_legajos' => ['etiqueta' => 'Log Legajos', 'ruta' => 'legajos/log_legajos', 'modulo' => 'legajos'],
            'empresa' => ['etiqueta' => 'Empresa', 'ruta' => 'configuracion/listar', 'modulo' => 'sistema'],
            'backup' => ['etiqueta' => 'Backup', 'ruta' => 'configuracion/mantenimiento', 'modulo' => 'sistema'],
            'matriz_legajos' => ['etiqueta' => 'Matriz de legajos', 'ruta' => 'configuracion/configuracion_legajos', 'modulo' => 'sistema'],
            'gestion_usuarios' => ['etiqueta' => 'Gestion Usuarios', 'ruta' => 'usuarios/listar', 'modulo' => 'sistema'],
            'conexiones' => ['etiqueta' => 'Monitor de conexiones', 'ruta' => 'usuarios/activos', 'modulo' => 'sistema'],
            'alertas_programadas' => ['etiqueta' => 'Alertas Programadas', 'ruta' => 'alerta/listar', 'modulo' => 'sistema'],
            'roles' => ['etiqueta' => 'Roles', 'ruta' => 'seguridad/roles', 'modulo' => 'sistema'],
            'log_sistema' => ['etiqueta' => 'Log Sistema', 'ruta' => 'logs/views', 'modulo' => 'sistema'],
            'fallos_sesion' => ['etiqueta' => 'Fallos Sesion', 'ruta' => 'logs/registro_session_fail', 'modulo' => 'sistema'],
            'sesiones' => ['etiqueta' => 'Sesiones', 'ruta' => 'logs/registro_sesiones', 'modulo' => 'sistema'],
            'funcionalidades' => ['etiqueta' => 'Gestion de modulos', 'ruta' => 'funcionalidades/listar', 'modulo' => 'sistema'],
        ];
    }

    public static function getSeccionesDisponibles(): array
    {
        return [
            'dashboard' => [
                'etiqueta' => 'Dashboard',
                'descripcion' => 'Controla el acceso al tablero general de legajos.',
                'icono' => 'fas fa-chart-line',
                'grupo' => 'Modulos',
                'rutas' => ['dashboard/*'],
                'ruta_menu' => 'dashboard/dashboard_legajos',
            ],
            'archivos' => [
                'etiqueta' => 'Archivos',
                'descripcion' => 'Incluye buscador, reportes, carga y acciones sobre archivos.',
                'icono' => 'fas fa-folder-open',
                'grupo' => 'Modulos',
                'rutas' => ['expedientes/*', 'logsumango/*', 'logs/registro_views*', 'usuarios/grupo*'],
                'ruta_menu' => 'expedientes/indice_busqueda',
            ],
            'legajos' => [
                'etiqueta' => 'Legajos',
                'descripcion' => 'Abarca armado, busqueda, verificacion, administracion y PDF final.',
                'icono' => 'fas fa-layer-group',
                'grupo' => 'Modulos',
                'rutas' => ['legajos/*', 'seguridad/permisos_legajos*'],
                'ruta_menu' => 'legajos/buscar_legajos',
            ],
            'unir_pdf' => [
                'etiqueta' => "Unir PDF's",
                'descripcion' => 'Habilita la utilidad para unir documentos PDF.',
                'icono' => 'fas fa-file-pdf',
                'grupo' => 'Modulos',
                'rutas' => ['unirpdf/*'],
                'ruta_menu' => 'unirpdf/unir_documentos',
            ],
            'configuracion' => [
                'etiqueta' => 'Configuracion',
                'descripcion' => 'Incluye configuraciones generales, mantenimiento y matriz de legajos.',
                'icono' => 'fas fa-sliders-h',
                'grupo' => 'Sistema',
                'rutas' => ['configuracion/*'],
                'ruta_menu' => 'configuracion/listar',
            ],
            'usuarios' => [
                'etiqueta' => 'Usuarios',
                'descripcion' => 'Gestion de usuarios, grupos, importaciones y reportes de usuarios.',
                'icono' => 'fas fa-users-cog',
                'grupo' => 'Sistema',
                'rutas' => [
                    'usuarios/listar*',
                    'usuarios/asignar_permisos*',
                    'usuarios/eliminar_permiso*',
                    'usuarios/reactivar_permiso*',
                    'usuarios/reporte*',
                    'usuarios/insertar*',
                    'usuarios/registrar_grupo*',
                    'usuarios/registrar_tipodoc*',
                    'usuarios/eliminar*',
                    'usuarios/bloquear*',
                    'usuarios/reingresar*',
                    'usuarios/importar*',
                    'usuarios/sincronizarad*',
                    'usuarios/pdf*',
                    'usuarios/pdf_filtro*',
                    'usuarios/grupo_pdf*',
                    'usuarios/excel*',
                    'usuarios/grupo_excel*',
                    'usuarios/usuario_muestra*',
                ],
                'ruta_menu' => 'usuarios/listar',
            ],
            'alertas' => [
                'etiqueta' => 'Alertas Programadas',
                'descripcion' => 'Gestiona tareas, destinatarios e historial de alertas.',
                'icono' => 'fas fa-bell',
                'grupo' => 'Sistema',
                'rutas' => ['alerta/*'],
                'ruta_menu' => 'alerta/listar',
            ],
            'seguridad' => [
                'etiqueta' => 'Seguridad',
                'descripcion' => 'Agrupa roles y permisos de legajos.',
                'icono' => 'fas fa-shield-alt',
                'grupo' => 'Sistema',
                'rutas' => ['seguridad/roles*'],
                'ruta_menu' => 'seguridad/roles',
            ],
            'auditoria' => [
                'etiqueta' => 'Auditoria',
                'descripcion' => 'Incluye logs documentales, de sistema, monitor de conexiones, visitas y sesiones.',
                'icono' => 'fas fa-history',
                'grupo' => 'Sistema',
                'rutas' => ['logs/views*', 'logs/registro_session_fail*', 'logs/registro_sesiones*', 'legajos/log_legajos*', 'usuarios/activos*'],
                'ruta_menu' => 'logs/views',
            ],
        ];
    }

    public function asegurarTablaFuncionalidades(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS funcionalidades_sistema (
                        id INT NOT NULL AUTO_INCREMENT,
                        clave VARCHAR(60) NOT NULL,
                        habilitado TINYINT(1) NOT NULL DEFAULT 1,
                        actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        actualizado_por INT DEFAULT NULL,
                        PRIMARY KEY (id),
                        UNIQUE KEY uk_funcionalidad_clave (clave)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
            $this->insert($sql, []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla funcionalidades_sistema: ' . $e->getMessage());
        }
    }

    public function asegurarTablaAgrupacionItems(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS funcionalidades_items_modulo (
                        id INT NOT NULL AUTO_INCREMENT,
                        item_key VARCHAR(80) NOT NULL,
                        modulo VARCHAR(30) NOT NULL,
                        actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        actualizado_por INT DEFAULT NULL,
                        PRIMARY KEY (id),
                        UNIQUE KEY uk_item_key (item_key)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
            $this->insert($sql, []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla funcionalidades_items_modulo: ' . $e->getMessage());
        }
    }

    public function selectEstadosSecciones(): array
    {
        $this->asegurarTablaFuncionalidades();

        $definiciones = self::getSeccionesDisponibles();
        $estados = array_fill_keys(array_keys($definiciones), 1);

        try {
            $rows = $this->select_all("SELECT clave, habilitado FROM funcionalidades_sistema");
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $clave = $row['clave'] ?? '';
                    if (isset($estados[$clave])) {
                        $estados[$clave] = intval($row['habilitado'] ?? 0);
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('Error consultando funcionalidades del sistema: ' . $e->getMessage());
        }

        return $estados;
    }

    public function selectModulosItems(): array
    {
        $this->asegurarTablaAgrupacionItems();

        $items = self::getItemsAgrupacionDisponibles();
        $mapa = [];
        foreach ($items as $clave => $info) {
            $mapa[$clave] = $info['modulo'] ?? 'sistema';
        }

        try {
            $rows = $this->select_all("SELECT item_key, modulo FROM funcionalidades_items_modulo");
            foreach ($rows as $row) {
                $clave = $row['item_key'] ?? '';
                if (isset($mapa[$clave]) && isset(self::MODULOS_ITEMS[$row['modulo'] ?? ''])) {
                    $mapa[$clave] = $row['modulo'];
                }
            }
        } catch (Throwable $e) {
            error_log('Error consultando agrupacion de items en funcionalidades: ' . $e->getMessage());
        }

        return $mapa;
    }

    public function guardarModulosItems(array $modulosItems, int $idUsuario): bool
    {
        $this->asegurarTablaAgrupacionItems();
        $items = self::getItemsAgrupacionDisponibles();

        try {
            foreach ($items as $clave => $info) {
                $modulo = $modulosItems[$clave] ?? ($info['modulo'] ?? 'sistema');
                if (!isset(self::MODULOS_ITEMS[$modulo])) {
                    $modulo = $info['modulo'] ?? 'sistema';
                }

                $sql = "INSERT INTO funcionalidades_items_modulo (item_key, modulo, actualizado_por)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            modulo = VALUES(modulo),
                            actualizado_por = VALUES(actualizado_por),
                            actualizado_en = NOW()";
                $this->insert($sql, [$clave, $modulo, $idUsuario]);
            }

            return true;
        } catch (Throwable $e) {
            error_log('Error guardando agrupacion de items en funcionalidades: ' . $e->getMessage());
            return false;
        }
    }

    public function selectItemsAgrupadosPorModulo(): array
    {
        $items = self::getItemsAgrupacionDisponibles();
        $modulosActuales = $this->selectModulosItems();
        $agrupados = [];

        foreach (self::MODULOS_ITEMS as $claveModulo => $etiquetaModulo) {
            $agrupados[$claveModulo] = [
                'etiqueta' => $etiquetaModulo,
                'items' => [],
            ];
        }

        foreach ($items as $clave => $info) {
            $modulo = $modulosActuales[$clave] ?? ($info['modulo'] ?? 'sistema');
            if (!isset($agrupados[$modulo])) {
                $modulo = 'sistema';
            }

            $agrupados[$modulo]['items'][$clave] = $info;
        }

        return $agrupados;
    }

    public function guardarEstadosSecciones(array $estados, int $idUsuario): bool
    {
        $this->asegurarTablaFuncionalidades();
        $definiciones = self::getSeccionesDisponibles();

        try {
            foreach ($definiciones as $clave => $info) {
                $habilitado = isset($estados[$clave]) && strval($estados[$clave]) === '1' ? 1 : 0;
                $sql = "INSERT INTO funcionalidades_sistema (clave, habilitado, actualizado_por)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            habilitado = VALUES(habilitado),
                            actualizado_por = VALUES(actualizado_por),
                            actualizado_en = NOW()";
                $this->insert($sql, [$clave, $habilitado, $idUsuario]);
            }

            return true;
        } catch (Throwable $e) {
            error_log('Error guardando funcionalidades del sistema: ' . $e->getMessage());
            return false;
        }
    }

    public function estaSeccionHabilitada(string $clave): bool
    {
        $estados = $this->selectEstadosSecciones();
        return intval($estados[$clave] ?? 1) === 1;
    }

    public static function resolverSeccionPorRuta(string $ruta): ?string
    {
        $ruta = strtolower(trim($ruta, '/'));
        if ($ruta === '') {
            return null;
        }

        $mejorCoincidencia = null;
        $mejorLongitud = -1;

        foreach (self::getSeccionesDisponibles() as $clave => $info) {
            foreach (($info['rutas'] ?? []) as $patron) {
                if (self::rutaCoincideConPatron($ruta, strtolower($patron))) {
                    $longitud = strlen(rtrim(strtolower($patron), '*'));
                    if ($longitud > $mejorLongitud) {
                        $mejorCoincidencia = $clave;
                        $mejorLongitud = $longitud;
                    }
                }
            }
        }

        return $mejorCoincidencia;
    }

    public static function obtenerRutaRedireccionSegura(int $idRol = 0): string
    {
        $prioridades = [
            'archivos' => 'expedientes/indice_busqueda',
            'legajos' => 'legajos/buscar_legajos',
            'dashboard' => 'dashboard/dashboard_legajos',
            'unir_pdf' => 'unirpdf/unir_documentos',
        ];

        try {
            $model = new self();
            $estados = $model->selectEstadosSecciones();

            foreach ($prioridades as $clave => $ruta) {
                if (intval($estados[$clave] ?? 1) === 1) {
                    return $ruta;
                }
            }
        } catch (Throwable $e) {
            error_log('Error obteniendo redireccion segura de funcionalidades: ' . $e->getMessage());
        }

        if ($idRol === 1) {
            return 'funcionalidades/listar';
        }

        return 'usuarios/cambiar_pass';
    }

    private static function rutaCoincideConPatron(string $ruta, string $patron): bool
    {
        if (substr($patron, -1) === '*') {
            $prefijo = rtrim(substr($patron, 0, -1), '/');
            return $prefijo === '' ? true : str_starts_with($ruta, $prefijo);
        }

        return $ruta === trim($patron, '/');
    }
}
