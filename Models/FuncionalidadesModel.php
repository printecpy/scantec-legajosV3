<?php

class FuncionalidadesModel extends Mysql
{
    private const MODULOS_ITEMS = [
        'archivos' => 'Archivos',
        'legajos' => 'Legajos',
        'personas' => 'Personas',
        'sistema' => 'Sistema',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->asegurarTablaParametros();
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
            'grupos' => ['etiqueta' => 'Grupos', 'ruta' => 'usuarios/grupo', 'modulo' => 'sistema'],
            'log_documentos' => ['etiqueta' => 'Log Documentos', 'ruta' => 'logsumango/views', 'modulo' => 'archivos'],
            'visitas_archivos' => ['etiqueta' => 'Visitas Archivos', 'ruta' => 'logs/registro_views', 'modulo' => 'archivos'],
            'reporte_paginas_legajos' => ['etiqueta' => 'Páginas de Legajos', 'ruta' => 'logs/reporte_paginas_legajos', 'modulo' => 'legajos'],
            'dashboard_legajos' => ['etiqueta' => 'Dashboard', 'ruta' => 'dashboard/dashboard', 'modulo' => 'legajos'],
            'armar_legajo' => ['etiqueta' => 'Armar legajo', 'ruta' => 'legajos/armar_legajo', 'modulo' => 'legajos'],
            'buscar_legajos' => ['etiqueta' => 'Buscar legajos', 'ruta' => 'legajos/buscar_legajos', 'modulo' => 'legajos'],
            'verificar_legajos' => ['etiqueta' => 'Verificar legajos', 'ruta' => 'legajos/verificar_legajos', 'modulo' => 'legajos'],
            'administrar_legajos' => ['etiqueta' => 'Administrar legajos', 'ruta' => 'legajos/administrar_legajos', 'modulo' => 'legajos'],
            'permisos_legajos' => ['etiqueta' => 'Permisos de Vistas', 'ruta' => 'seguridad/permisos_legajos', 'modulo' => 'legajos'],
            'log_legajos' => ['etiqueta' => 'Log Legajos', 'ruta' => 'legajos/log_legajos', 'modulo' => 'legajos'],
            'personas' => ['etiqueta' => 'Personas', 'ruta' => 'personas/listar', 'modulo' => 'personas'],
            'empresa' => ['etiqueta' => 'Empresa', 'ruta' => 'configuracion/listar', 'modulo' => 'sistema'],
            'facturacion' => ['etiqueta' => 'Facturación', 'ruta' => 'legajos/facturacion', 'modulo' => 'sistema'],
            'base_datos_externa' => ['etiqueta' => 'Base de datos externa', 'ruta' => 'configuracion/base_datos_externa', 'modulo' => 'sistema'],
            'backup' => ['etiqueta' => 'Backup', 'ruta' => 'configuracion/mantenimiento', 'modulo' => 'sistema'],
            'matriz_legajos' => ['etiqueta' => 'Matriz de legajos', 'ruta' => 'configuracion/configuracion_legajos', 'modulo' => 'sistema'],
            'gestion_usuarios' => ['etiqueta' => 'Gestión de usuarios', 'ruta' => 'usuarios/listar', 'modulo' => 'sistema'],
            'conexiones' => ['etiqueta' => 'Monitor de conexiones', 'ruta' => 'usuarios/activos', 'modulo' => 'sistema'],
            'alertas_programadas' => ['etiqueta' => 'Alertas Programadas', 'ruta' => 'alerta/listar', 'modulo' => 'sistema'],
            'roles' => ['etiqueta' => 'Roles', 'ruta' => 'seguridad/roles', 'modulo' => 'sistema'],
            'log_sistema' => ['etiqueta' => 'Log Sistema', 'ruta' => 'logs/views', 'modulo' => 'sistema'],
            'fallos_sesion' => ['etiqueta' => 'Fallos de sesión', 'ruta' => 'logs/registro_session_fail', 'modulo' => 'sistema'],
            'sesiones' => ['etiqueta' => 'Sesiones', 'ruta' => 'logs/registro_sesiones', 'modulo' => 'sistema'],
            'funcionalidades' => ['etiqueta' => 'Gestión de módulos', 'ruta' => 'funcionalidades/listar', 'modulo' => 'sistema'],
            'funcionalidades_accesos' => [
                'etiqueta' => 'Accesos por rol y departamento',
                'ruta' => 'funcionalidades/accesos',
                'descripcion' => 'Permite definir las ventanas y funciones activas por rol y departamento.',
                'grupo' => 'Funcionalidades',
                'rutas' => ['funcionalidades/accesos*', 'funcionalidades/guardar_accesos*'],
            ],
        ];
    }

    /**
     * Catalogo de ventanas y funciones que se pueden habilitar por rol y departamento.
     *  - 'defecto' => 1  : el item nace habilitado para roles no configurados.
     *                      Sin 'defecto', el item nace cerrado (solo roles 1 y 2 lo tienen hasta que se habilite en la pantalla de accesos).
     *  - 'inicio'  => N  : el item puede ser pantalla de inicio al iniciar sesion; N es la prioridad (menor = primero).
     */
    public static function getAccesosDisponibles(): array
    {
        return [
            'buscador_archivos' => [
                'etiqueta' => 'Buscador de archivos',
                'ruta' => 'expedientes/indice_busqueda',
                'descripcion' => 'Permite entrar al buscador principal de archivos.',
                'grupo' => 'Archivos',
                'rutas' => ['expedientes/indice_busqueda*'],
                'inicio' => 20,
                'defecto' => 1,
            ],
            'reporte_expedientes' => [
                'etiqueta' => 'Reporte de expedientes',
                'ruta' => 'expedientes/reporte',
                'descripcion' => 'Permite consultar el reporte general de expedientes.',
                'grupo' => 'Archivos',
                'rutas' => ['expedientes/reporte*'],
            ],
            'subir_archivos' => [
                'etiqueta' => 'Subir archivos',
                'ruta' => 'expedientes/upload_files',
                'descripcion' => 'Habilita la carga de archivos al sistema.',
                'grupo' => 'Archivos',
                'rutas' => ['expedientes/upload_files*'],
            ],
            'gestionar_expedientes' => [
                'etiqueta' => 'Editar y anular documentos',
                'ruta' => 'expedientes/editar',
                'descripcion' => 'Permite modificar índices, anular y reactivar documentos de los tipos de su grupo.',
                'grupo' => 'Archivos',
                'rutas' => ['expedientes/editar*', 'expedientes/modificar*', 'expedientes/eliminar*', 'expedientes/reingresar*'],
            ],
            'consulta_indice1' => [
                'etiqueta' => 'Consulta por índice 1 (enlace externo)',
                'ruta' => 'expedientes/buscarporindice01',
                'descripcion' => 'Permite abrir la consulta de documentos por índice 1 desde un enlace de otro sistema.',
                'grupo' => 'Archivos',
                'rutas' => ['expedientes/buscarporindice01', 'expedientes/mostrar_resultados'],
                'defecto' => 0,
            ],
            'grupos' => [
                'etiqueta' => 'Grupos',
                'ruta' => 'usuarios/grupo',
                'descripcion' => 'Gestiona los grupos documentales asociados a archivos.',
                'grupo' => 'Administración',
                'rutas' => ['usuarios/grupo*', 'usuarios/asignar_permisos*', 'usuarios/eliminar_permiso*', 'usuarios/reactivar_permiso*', 'usuarios/registrar_grupo*', 'usuarios/registrar_tipodoc*', 'usuarios/grupo_pdf*', 'usuarios/grupo_excel*'],
            ],
            'log_documentos' => [
                'etiqueta' => 'Log Documentos',
                'ruta' => 'logsumango/views',
                'descripcion' => 'Muestra el log de documentos.',
                'grupo' => 'Auditoría',
                'rutas' => ['logsumango/*'],
            ],
            'visitas_archivos' => [
                'etiqueta' => 'Visitas Archivos',
                'ruta' => 'logs/registro_views',
                'descripcion' => 'Muestra las visitas a archivos.',
                'grupo' => 'Auditoría',
                'rutas' => ['logs/registro_views*', 'logs/registro_viewexcel*'],
            ],
            'reporte_paginas_legajos' => [
                'etiqueta' => 'Páginas de legajos',
                'ruta' => 'logs/reporte_paginas_legajos',
                'descripcion' => 'Muestra el reporte de páginas procesadas de legajos por período.',
                'grupo' => 'Auditoría',
                'rutas' => ['logs/reporte_paginas_legajos*', 'logs/reporte_paginas_legajosPdf*', 'logs/reporte_paginas_legajosExcel*'],
            ],
            'unir_pdf' => [
                'etiqueta' => "Unir PDF's",
                'ruta' => 'unirpdf/unir_documentos',
                'descripcion' => 'Permite usar la utilidad para unir documentos PDF.',
                'grupo' => 'Archivos',
                'rutas' => ['unirpdf/*'],
            ],
            'dashboard_legajos' => [
                'etiqueta' => 'Dashboard',
                'ruta' => 'dashboard/dashboard',
                'descripcion' => 'Muestra el tablero general unificado.',
                'grupo' => 'Dashboard',
                'rutas' => ['dashboard/dashboard*', 'dashboard/dashboard_legajos*', 'dashboard/listar*'],
                'inicio' => 10,
                'defecto' => 1,
            ],
            'armar_legajo' => [
                'etiqueta' => 'Armar legajo',
                'ruta' => 'legajos/armar_legajo',
                'descripcion' => 'Permite crear y cargar un legajo.',
                'grupo' => 'Legajos',
                'rutas' => ['legajos/armar_legajo*', 'legajos/cargar_documento*', 'legajos/ver_archivo_checklist*', 'legajos/ver_documento_checklist*', 'legajos/validar_solicitud_duplicada*', 'legajos/generar_pdf_texto*'],
            ],
            'buscar_legajos' => [
                'etiqueta' => 'Buscar legajos',
                'ruta' => 'legajos/buscar_legajos',
                'descripcion' => 'Permite consultar legajos existentes.',
                'grupo' => 'Legajos',
                'rutas' => ['legajos/buscar_legajos*', 'legajos/documentos_procesados*', 'legajos/documentos_procesadosPdf*', 'legajos/documentos_procesadosExcel*', 'legajos/estado_legajo*', 'legajos/ver_pdf_legajo*', 'legajos/descargar_pdf_legajo*'],
                'inicio' => 30,
            ],
            'verificar_legajos' => [
                'etiqueta' => 'Verificar legajos',
                'ruta' => 'legajos/verificar_legajos',
                'descripcion' => 'Permite revisar y verificar legajos.',
                'grupo' => 'Legajos',
                'rutas' => ['legajos/verificar_legajos*', 'legajos/aprobar_legajo*', 'legajos/rechazar_legajo*'],
            ],
            'administrar_legajos' => [
                'etiqueta' => 'Administrar legajos',
                'ruta' => 'legajos/administrar_legajos',
                'descripcion' => 'Permite administrar y cerrar legajos.',
                'grupo' => 'Legajos',
                'rutas' => ['legajos/administrar_legajos*', 'legajos/cerrar_legajo*', 'legajos/eliminar_legajo*'],
            ],
            'permisos_legajos' => [
                'etiqueta' => 'Permisos de Vistas',
                'ruta' => 'seguridad/permisos_legajos',
                'descripcion' => 'Administra permisos y visibilidad de legajos.',
                'grupo' => 'Seguridad',
                'rutas' => ['seguridad/permisos_legajos*', 'seguridad/guardar_permisos_legajos*'],
            ],
            'log_legajos' => [
                'etiqueta' => 'Log Legajos',
                'ruta' => 'legajos/log_legajos',
                'descripcion' => 'Muestra el log de legajos.',
                'grupo' => 'Auditoría',
                'rutas' => ['legajos/log_legajos*'],
            ],
            'personas' => [
                'etiqueta' => 'Personas',
                'ruta' => 'personas/listar',
                'descripcion' => 'Permite gestionar personas vinculables a legajos.',
                'grupo' => 'Personas',
                'rutas' => ['personas/*'],
            ],
            'empresa' => [
                'etiqueta' => 'Empresa',
                'ruta' => 'configuracion/listar',
                'descripcion' => 'Permite gestionar la configuración general de la empresa y sus departamentos.',
                'grupo' => 'Administración',
                'rutas' => ['configuracion/listar*', 'configuracion/guardar_departamento*', 'configuracion/actualizar_departamento*', 'configuracion/eliminar_departamento*'],
            ],
            'facturacion' => [
                'etiqueta' => 'Facturación',
                'ruta' => 'legajos/facturacion',
                'descripcion' => 'Permite consultar el contador facturable de páginas procesadas por fecha.',
                'grupo' => 'Facturación',
                'rutas' => ['legajos/facturacion*', 'legajos/facturacionPdf*', 'legajos/facturacionExcel*'],
            ],
            'base_datos_externa' => [
                'etiqueta' => 'Base de datos externa',
                'ruta' => 'configuracion/base_datos_externa',
                'descripcion' => 'Permite configurar la fuente externa de datos para Legajos.',
                'grupo' => 'Configuración',
                'rutas' => ['configuracion/base_datos_externa*', 'configuracion/probar_base_datos_externa*', 'configuracion/guardar_base_datos_externa*'],
            ],
            'servidor_ad' => [
                'etiqueta' => 'Servidor Active Directory',
                'ruta' => 'configuracion/servidor_AD',
                'descripcion' => 'Permite configurar la conexión LDAP/AD y su sincronización.',
                'grupo' => 'Configuración',
                'rutas' => ['configuracion/servidor_ad*', 'configuracion/saveldap_server*', 'configuracion/probar_conexionad*', 'configuracion/guardar_formato_ldap*'],
            ],
            'servidor_smtp' => [
                'etiqueta' => 'Servidor de correo (SMTP)',
                'ruta' => 'configuracion/servidor_smtp',
                'descripcion' => 'Permite configurar y probar el servidor de correo.',
                'grupo' => 'Configuración',
                'rutas' => ['configuracion/servidor_smtp*', 'configuracion/guardarservcorreo*', 'configuracion/probar_smtp*', 'configuracion/enviarcorreo*', 'configuracion/desactivar_servicio_smtp*'],
            ],
            'backup' => [
                'etiqueta' => 'Backup',
                'ruta' => 'configuracion/mantenimiento',
                'descripcion' => 'Permite realizar mantenimiento y backups.',
                'grupo' => 'Seguridad',
                'rutas' => ['configuracion/mantenimiento*'],
            ],
            'matriz_legajos' => [
                'etiqueta' => 'Matriz de legajos',
                'ruta' => 'configuracion/configuracion_legajos',
                'descripcion' => 'Permite configurar la matriz de legajos.',
                'grupo' => 'Administración',
                'rutas' => ['configuracion/configuracion_legajos*'],
            ],
            'tipos_relacion_archivos' => [
                'etiqueta' => 'Tipos de Relación',
                'ruta' => 'configuracion/configuracion_legajos',
                'descripcion' => 'Permite ver y modificar los tipos de relación en Datos generales.',
                'grupo' => 'Administración',
                'rutas' => ['configuracion/guardar_relacion*', 'configuracion/cambiar_estado_relacion*', 'configuracion/eliminar_relacion*'],
            ],
            'metodos_actualizacion_archivos' => [
                'etiqueta' => 'Métodos de Actualización de Archivos',
                'ruta' => 'configuracion/configuracion_legajos',
                'descripcion' => 'Permite ver y modificar los Métodos de Actualización de Archivos.',
                'grupo' => 'Administración',
                'rutas' => ['configuracion/cambiar_estado_politica*'],
            ],
            'gestion_usuarios' => [
                'etiqueta' => 'Gestión de usuarios',
                'ruta' => 'usuarios/listar',
                'descripcion' => 'Permite administrar usuarios del sistema.',
                'grupo' => 'Administración',
                'rutas' => ['usuarios/listar*', 'usuarios/detalle*', 'usuarios/editar*', 'usuarios/actualizar*', 'usuarios/insertar*', 'usuarios/eliminar*', 'usuarios/bloquear*', 'usuarios/reingresar*', 'usuarios/reingresar_masivo*', 'usuarios/importar*', 'usuarios/confirmar_importacion*', 'usuarios/cancelar_importacion*', 'usuarios/excel*', 'usuarios/usuario_muestra*'],
            ],
            'conexiones' => [
                'etiqueta' => 'Monitor de conexiones',
                'ruta' => 'usuarios/activos',
                'descripcion' => 'Permite monitorear sesiones y conexiones activas.',
                'grupo' => 'Auditoría',
                'rutas' => ['usuarios/activos*', 'usuarios/fin_session*'],
            ],
            'alertas_programadas' => [
                'etiqueta' => 'Alertas Programadas',
                'ruta' => 'alerta/listar',
                'descripcion' => 'Gestiona las alertas programadas del sistema.',
                'grupo' => 'Administración',
                'rutas' => ['alerta/*'],
            ],
            'roles' => [
                'etiqueta' => 'Roles',
                'ruta' => 'seguridad/roles',
                'descripcion' => 'Permite administrar roles del sistema.',
                'grupo' => 'Administración',
                'rutas' => ['seguridad/roles*', 'seguridad/guardar_rol*', 'seguridad/actualizar_rol*', 'seguridad/eliminar_rol*', 'seguridad/cambiar_estado_rol*'],   // <- guardar_rol
            ],
            'log_sistema' => [
                'etiqueta' => 'Log Sistema',
                'ruta' => 'logs/views',
                'descripcion' => 'Muestra el log general del sistema.',
                'grupo' => 'Auditoría',
                'rutas' => ['logs/views*', 'logs/pdf*', 'logs/excel*'],
            ],
            'fallos_sesion' => [
                'etiqueta' => 'Fallos de sesión',
                'ruta' => 'logs/registro_session_fail',
                'descripcion' => 'Muestra los fallos de inicio de sesión.',
                'grupo' => 'Auditoría',
                'rutas' => ['logs/registro_session_fail*'],
            ],
            'sesiones' => [
                'etiqueta' => 'Sesiones',
                'ruta' => 'logs/registro_sesiones',
                'descripcion' => 'Muestra el historial de sesiones.',
                'grupo' => 'Auditoría',
                'rutas' => ['logs/registro_sesiones*'],
            ],
            'funcionalidades_accesos' => [
                'etiqueta' => 'Accesos por rol y departamento',
                'ruta' => 'funcionalidades/accesos',
                'descripcion' => 'Permite configurar el acceso a Funcionalidades según rol y departamento.',
                'grupo' => 'Funcionalidades',
                'rutas' => ['funcionalidades/accesos*', 'funcionalidades/guardar_accesos*'],
            ],
        ];
    }

    public static function getSeccionesDisponibles(): array
    {
        return [
            'personas' => [
                'etiqueta' => 'Personas',
                'descripcion' => 'Gestiona clientes y empleados que pueden asociarse a legajos.',
                'icono' => 'fas fa-address-book',
                'grupo' => 'Módulo',
                'rutas' => ['personas/*'],
                'ruta_menu' => 'personas/listar',
            ],
            'dashboard' => [
                'etiqueta' => 'Dashboard',
                'descripcion' => 'Controla el acceso al tablero general.',
                'icono' => 'fas fa-chart-line',
                'grupo' => 'Módulo',
                'rutas' => ['dashboard/*'],
                'ruta_menu' => 'dashboard/dashboard',
            ],
            'archivos' => [
                'etiqueta' => 'Archivos',
                'descripcion' => 'Incluye buscador, reportes, carga y acciones sobre archivos.',
                'icono' => 'fas fa-folder-open',
                'grupo' => 'Módulo',
                'rutas' => ['expedientes/*', 'logsumango/*', 'logs/registro_views*'],
                'ruta_menu' => 'expedientes/indice_busqueda',
            ],
            'legajos' => [
                'etiqueta' => 'Legajos',
                'descripcion' => 'Abarca armado, búsqueda, verificación, administración y PDF final.',
                'icono' => 'fas fa-layer-group',
                'grupo' => 'Módulo',
                'rutas' => ['legajos/*', 'seguridad/permisos_legajos*'],
                'ruta_menu' => 'legajos/buscar_legajos',
            ],
            'unir_pdf' => [
                'etiqueta' => "Unir PDF's",
                'descripcion' => 'Habilita la utilidad para unir documentos PDF.',
                'icono' => 'fas fa-file-pdf',
                'grupo' => 'Módulo',
                'rutas' => ['unirpdf/*'],
                'ruta_menu' => 'unirpdf/unir_documentos',
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

    public function asegurarTablaAccesosRolDepartamento(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS funcionalidades_acceso_rol_departamento (
                        id INT NOT NULL AUTO_INCREMENT,
                        id_rol INT NOT NULL,
                        id_departamento INT NOT NULL,
                        item_key VARCHAR(80) NOT NULL,
                        habilitado TINYINT(1) NOT NULL DEFAULT 1,
                        actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        actualizado_por INT DEFAULT NULL,
                        PRIMARY KEY (id),
                        UNIQUE KEY uk_funcionalidad_acceso (id_rol, id_departamento, item_key)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
            $this->insert($sql, []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla funcionalidades_acceso_rol_departamento: ' . $e->getMessage());
        }
    }

    public function asegurarTablaParametros(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS funcionalidades_parametros (
                        id INT NOT NULL AUTO_INCREMENT,
                        clave VARCHAR(80) NOT NULL,
                        valor VARCHAR(255) NOT NULL,
                        actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        actualizado_por INT DEFAULT NULL,
                        PRIMARY KEY (id),
                        UNIQUE KEY uk_funcionalidad_parametro_clave (clave)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
            $this->insert($sql, []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla funcionalidades_parametros: ' . $e->getMessage());
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

    public function selectAccesosRolDepartamentoConfigurados(int $idRol, int $idDepartamento): array
    {
        $this->asegurarTablaAccesosRolDepartamento();
        if ($idRol <= 0 || $idDepartamento < 0) {
            return [];
        }

        $sql = "SELECT item_key, habilitado
                FROM funcionalidades_acceso_rol_departamento
                WHERE id_rol = ? AND id_departamento = ?";
        $rows = $this->select_all($sql, [$idRol, $idDepartamento]);
        $mapa = [];
        foreach ($rows as $row) {
            $mapa[$row['item_key']] = intval($row['habilitado'] ?? 0);
        }
        return $mapa;
    }

    private function resolverDepartamentoRol(int $idRol, int $idDepartamento = 0): int
    {
        if ($idRol <= 0) {
            return 0;
        }

        try {
            $row = $this->select("SELECT id_departamento FROM roles WHERE id_rol = ? LIMIT 1", [$idRol]);
            if (is_array($row) && isset($row[0])) {
                $row = $row[0];
            }
            $idDepartamentoRol = intval($row['id_departamento'] ?? 0);
            if ($idDepartamentoRol > 0) {
                return $idDepartamentoRol;
            }
        } catch (Throwable $e) {
            // Continuamos con el valor recibido por contexto.
        }

        return $idDepartamento > 0 ? $idDepartamento : 0;
    }

    public function selectAccesosPorRolDepartamento(int $idRol, int $idDepartamento): array
    {
        $definiciones = self::getAccesosDisponibles();

        // Superadmin Scantec: todo habilitado
        if ($idRol === 1) {
            return array_fill_keys(array_keys($definiciones), 1);
        }

        // Sin sesión o rol inválido: nada habilitado
        if ($idRol <= 0) {
            return array_fill_keys(array_keys($definiciones), 0);
        }

        // Valor por defecto desde el catálogo: 'defecto' => 1 nace habilitado; sin 'defecto', nace cerrado
        $accesos = [];
        foreach ($definiciones as $clave => $info) {
            $accesos[$clave] = intval($info['defecto'] ?? 0);
        }

        // Lo configurado en la pantalla de accesos pisa el valor por defecto
        $idDepartamento = $this->resolverDepartamentoRol($idRol, $idDepartamento);
        $configurados = $this->selectAccesosRolDepartamentoConfigurados($idRol, $idDepartamento);
        foreach ($configurados as $clave => $habilitado) {
            if (isset($accesos[$clave])) {
                $accesos[$clave] = intval($habilitado);
            }
        }

        return $accesos;
    }


    /**
     * Guarda los accesos de un rol. Solo escribe los items de $itemsGestionables (los que quien guarda puede ver y gestionar);
     * los demas quedan como estaban. Sin lista, recorre todo el catalogo.
     */
    public function guardarAccesosRolDepartamento(int $idRol, int $idDepartamento, array $items, int $idUsuario, ?array $itemsGestionables = null): bool
    {
        $this->asegurarTablaAccesosRolDepartamento();
        if ($idRol <= 0 || $idDepartamento < 0) {
            return false;
        }

        $definiciones = self::getAccesosDisponibles();
        $claves = $itemsGestionables ?? array_keys($definiciones);

        try {
            foreach ($claves as $itemKey) {
                if (!isset($definiciones[$itemKey])) {
                    continue;
                }
                $habilitado = isset($items[$itemKey]) ? 1 : 0;
                $sql = "INSERT INTO funcionalidades_acceso_rol_departamento (id_rol, id_departamento, item_key, habilitado, actualizado_por)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            habilitado = VALUES(habilitado),
                            actualizado_por = VALUES(actualizado_por),
                            actualizado_en = NOW()";
                $this->insert($sql, [$idRol, $idDepartamento, $itemKey, $habilitado, $idUsuario]);
            }
            return true;
        } catch (Throwable $e) {
            error_log('Error guardando accesos por rol y departamento: ' . $e->getMessage());
            return false;
        }
    }

    public function guardarAccesoItemPorRol(int $idRol, int $idDepartamento, string $itemKey, bool $habilitado, int $idUsuario): bool
    {
        $this->asegurarTablaAccesosRolDepartamento();
        if ($idRol <= 0 || $idDepartamento < 0) {
            return false;
        }

        $definiciones = self::getAccesosDisponibles();
        if (!isset($definiciones[$itemKey])) {
            return false;
        }

        try {
            $sql = "INSERT INTO funcionalidades_acceso_rol_departamento (id_rol, id_departamento, item_key, habilitado, actualizado_por)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        habilitado = VALUES(habilitado),
                        actualizado_por = VALUES(actualizado_por),
                        actualizado_en = NOW()";
            $this->insert($sql, [$idRol, $idDepartamento, $itemKey, $habilitado ? 1 : 0, $idUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('Error guardando acceso individual por rol/departamento: ' . $e->getMessage());
            return false;
        }
    }

    // Pantalla de inicio elegida para un rol ('' = automatica). Se guarda en funcionalidades_parametros.
    public function selectInicioRol(int $idRol): string
    {
        try {
            $row = $this->select("SELECT valor FROM funcionalidades_parametros WHERE clave = ? LIMIT 1", ['inicio_rol_' . $idRol]);
            if (is_array($row) && isset($row[0])) {
                $row = $row[0];
            }
            return (string) ($row['valor'] ?? '');
        } catch (Throwable $e) {
            return '';
        }
    }

    public function guardarInicioRol(int $idRol, string $itemKey, int $idUsuario): bool
    {
        // solo se acepta '' (automatica) o un item marcado como pantalla de inicio en el catalogo
        if ($idRol <= 0 || ($itemKey !== '' && !isset(self::getAccesosDisponibles()[$itemKey]['inicio']))) {
            return false;
        }

        try {
            $sql = "INSERT INTO funcionalidades_parametros (clave, valor, actualizado_por)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        valor = VALUES(valor),
                        actualizado_por = VALUES(actualizado_por),
                        actualizado_en = NOW()";
            $this->insert($sql, ['inicio_rol_' . $idRol, $itemKey, $idUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('Error guardando la pantalla de inicio del rol: ' . $e->getMessage());
            return false;
        }
    }

    public function selectMaxUsuariosConfigurado(): int
    {
        $this->asegurarTablaParametros();

        try {
            $sql = "SELECT valor FROM funcionalidades_parametros WHERE clave = ? LIMIT 1";
            $row = $this->select($sql, ['max_usuarios']);
            if (is_array($row) && isset($row[0])) {
                $row = $row[0];
            }

            $valor = intval($row['valor'] ?? 0);
            if ($valor > 0) {
                return $valor;
            }
        } catch (Throwable $e) {
            error_log('Error consultando maximo de usuarios configurado: ' . $e->getMessage());
        }

        return defined('LIMITE_USUARIOS') ? intval(LIMITE_USUARIOS) : 0;
    }

    public function guardarMaxUsuariosConfigurado(int $maxUsuarios, int $idUsuario): bool
    {
        $this->asegurarTablaParametros();

        try {
            $sql = "INSERT INTO funcionalidades_parametros (clave, valor, actualizado_por)
                    VALUES ('max_usuarios', ?, ?)
                    ON DUPLICATE KEY UPDATE
                        valor = VALUES(valor),
                        actualizado_por = VALUES(actualizado_por),
                        actualizado_en = NOW()";
            $this->insert($sql, [strval($maxUsuarios), $idUsuario]);
            return true;
        } catch (Throwable $e) {
            error_log('Error guardando maximo de usuarios configurado: ' . $e->getMessage());
            return false;
        }
    }

    public function estaSeccionHabilitada(string $clave): bool
    {
        $estados = $this->selectEstadosSecciones();
        return intval($estados[$clave] ?? 1) === 1;
    }

    public function puedeAccederItemPorContexto(string $itemKey, int $idRol = 0, int $idDepartamento = 0): bool
    {
        if ($idRol <= 0) {
            return false;
        }
        if ($idRol === 1) {
            return true;
        }

        $definiciones = self::getAccesosDisponibles();
        if (!isset($definiciones[$itemKey])) {
            error_log('Clave de funcionalidad inexistente: ' . $itemKey);
            return false;
        }

        $accesos = $this->selectAccesosPorRolDepartamento($idRol, $idDepartamento);
        return intval($accesos[$itemKey] ?? 0) === 1;
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

    public static function resolverItemAccesoPorRuta(string $ruta): ?string
    {
        $ruta = strtolower(trim($ruta, '/'));
        if ($ruta === '') {
            return null;
        }

        $mejorCoincidencia = null;
        $mejorLongitud = -1;

        foreach (self::getAccesosDisponibles() as $clave => $info) {
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

    /**
     * Ruta a la que se envia al usuario despues de iniciar sesion (y cuando se le deniega una seccion):
     * la pantalla de inicio elegida para su rol si sigue permitida; si no, la primera permitida por prioridad
     * (los items marcados con 'inicio' en el catalogo). Ultimo recurso: rol 1 -> gestion de modulos; resto -> cambiar contrasena.
     */
    public static function obtenerRutaRedireccionSegura(int $idRol = 0, int $idDepartamento = 0): string
    {
        try {
            $model = new self();
            $items = self::getAccesosDisponibles();
            $estados = $model->selectEstadosSecciones();
            $accesos = $model->selectAccesosPorRolDepartamento($idRol, $idDepartamento);
            $candidatas = [];                                              // item => prioridad: solo los permitidos y con la seccion activa
            foreach ($items as $clave => $info) {
                if (!isset($info['inicio']) || intval($accesos[$clave] ?? 0) !== 1) {
                    continue;
                }
                $seccion = self::resolverSeccionPorRuta($info['ruta']);
                if ($seccion !== null && intval($estados[$seccion] ?? 1) !== 1) {
                    continue;
                }
                $candidatas[$clave] = intval($info['inicio']);
            }
            if ($candidatas) {
                $elegida = $model->selectInicioRol($idRol);                // la que configuro el administrador, si sigue permitida
                if (!isset($candidatas[$elegida])) {
                    asort($candidatas);                                    // si no, la de mayor prioridad
                    $elegida = array_key_first($candidatas);
                }
                return $items[$elegida]['ruta'];
            }
        } catch (Throwable $e) {
            error_log('Error obteniendo redireccion segura de funcionalidades: ' . $e->getMessage());
        }

        return $idRol === 1 ? 'funcionalidades/listar' : 'usuarios/cambiar_pass';
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