<?php

class Seguridad extends Controllers
{
    private function puedeAccederItemSeguridad(string $itemKey, string $permisoLegajo): bool
    {
        require_once 'Models/SeguridadLegajosModel.php';
        $seguridadModel = new SeguridadLegajosModel();

        if ($seguridadModel->tienePermisoLegajo(intval($_SESSION['id_rol'] ?? 0), $permisoLegajo)) {
            return true;
        }

        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                $itemKey,
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    private function tokenValidoGetOPost(): bool
    {
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        return isset($_SESSION['csrf_token'], $_SESSION['csrf_expiration'])
            && is_string($token)
            && hash_equals((string) $_SESSION['csrf_token'], $token)
            && intval($_SESSION['csrf_expiration']) >= time();
    }

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
            exit();
        }
        parent::__construct();
    }

    /**
     * Vista principal: matrix de permisos de legajos por rol.
     */
    public function permisos_legajos()
    {
        require_once 'Models/SeguridadLegajosModel.php';
        require_once 'Models/UsuariosModel.php';
        require_once 'Models/FuncionalidadesModel.php';
        $seguridadModel = new SeguridadLegajosModel();
        $usuariosModel = new UsuariosModel();
        $funcionalidadesModel = new FuncionalidadesModel();

        // Verificar permiso para gestionar permisos
        if (!$this->puedeAccederItemSeguridad('permisos_legajos', 'permisos_legajos')) {
            setAlert('warning', 'No tienes permiso para gestionar permisos de legajos.');
            header('Location: ' . base_url() . 'dashboard/listar');
            exit();
        }

        // Asegurar que la tabla exista (primera ejecución)
        $this->asegurarTablaPermisos();

        $roles = $seguridadModel->selectRolesVisiblesPara(intval($_SESSION['id_rol'] ?? 0));
        $acciones = SeguridadLegajosModel::getAccionesDisponibles();
        $permisos = $seguridadModel->selectTodosPermisosLegajos();
        $visibilidadLegajosOtros = $seguridadModel->selectVisibilidadLegajosOtrosPorRol();
        $dashboardCards = SeguridadLegajosModel::getDashboardCardsDisponibles();
        $dashboardCardsPorRol = $seguridadModel->selectPermisosDashboardCardsPorRol();
        $legajosModel = new LegajosModel();
        $tiposLegajo = $legajosModel->selectTiposLegajo();
        $tiposLegajoPorRol = $seguridadModel->selectTiposLegajoVisiblesPorRol();
        $facturacionPorRol = [];
        $baseDatosExternaPorRol = [];
        foreach ($roles as $rol) {
            $idRol = intval($rol['id_rol'] ?? 0);
            $idDepartamento = intval($rol['id_departamento'] ?? 0);
            $accesosRol = $funcionalidadesModel->selectAccesosPorRolDepartamento($idRol, $idDepartamento);
            $facturacionPorRol[$idRol] = $idRol === 1
                ? 1
                : intval($accesosRol['facturacion'] ?? 1);
            $baseDatosExternaPorRol[$idRol] = $idRol === 1
                ? 1
                : intval($accesosRol['base_datos_externa'] ?? 1);
        }

        $data = [
            'roles'    => $roles,
            'acciones' => $acciones,
            'permisos' => $permisos,
            'visibilidad_legajos_otros' => $visibilidadLegajosOtros,
            'dashboard_cards' => $dashboardCards,
            'dashboard_cards_por_rol' => $dashboardCardsPorRol,
            'tipos_legajo' => $tiposLegajo,
            'tipos_legajo_por_rol' => $tiposLegajoPorRol,
            'facturacion_por_rol' => $facturacionPorRol,
            'base_datos_externa_por_rol' => $baseDatosExternaPorRol,
            'selected_role_id' => intval($_GET['id_rol'] ?? 0),
        ];
        $this->views->getView($this, "permisos_legajos", $data);
    }

    /**
     * POST: Guardar los permisos marcados en la matrix.
     */
    public function guardar_permisos_legajos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "seguridad/permisos_legajos");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido o expirado.');
            session_write_close();
            header("Location: " . base_url() . "seguridad/permisos_legajos");
            exit();
        }

        require_once 'Models/SeguridadLegajosModel.php';
        require_once 'Models/FuncionalidadesModel.php';
        $seguridadModel = new SeguridadLegajosModel();
        $funcionalidadesModel = new FuncionalidadesModel();

        // Verificar permiso para gestionar permisos
        if (!$this->puedeAccederItemSeguridad('permisos_legajos', 'gestionar_permisos')) {
            setAlert('warning', 'No tienes permiso para gestionar permisos.');
            session_write_close();
            header("Location: " . base_url() . "seguridad/permisos_legajos");
            exit();
        }

        $roles = $seguridadModel->selectRolesVisiblesPara(intval($_SESSION['id_rol'] ?? 0));
        $acciones = SeguridadLegajosModel::getAccionesDisponibles();
        $visibilidadPost = $_POST['visibilidad_legajos_otros'] ?? [];
        $dashboardCardsPost = $_POST['dashboard_cards'] ?? [];
        $tiposLegajoPost = $_POST['tipos_legajo_visibles'] ?? [];
        $facturacionPost = $_POST['vista_facturacion'] ?? [];
        $baseDatosExternaPost = $_POST['vista_base_datos_externa'] ?? [];
        $legajosModel = new LegajosModel();
        $tiposLegajo = $legajosModel->selectTiposLegajo();

        // Los checkboxes llegan como: permisos[id_rol][accion] = "1"
        $permisosPost = $_POST['permisos'] ?? [];

        // Obtener permisos actuales para comparar y loguear cambios
        $permisosActuales = $seguridadModel->selectTodosPermisosLegajos();

        // Obtener el rol del usuario actual para prevenir auto-bloqueo
        $rolUsuarioActual = intval($_SESSION['id_rol'] ?? 0);
        $permisosCriticos = ['gestionar_permisos', 'gestionar_roles', 'permisos_legajos'];
        $autobloqueoPrevenido = false;

        foreach ($roles as $rol) {
            $idRol = intval($rol['id_rol']);
            $accionesRol = $permisosPost[$idRol] ?? [];

            // Prevención de auto-bloqueo: si el usuario está modificando su propio rol
            if ($idRol === $rolUsuarioActual) {
                foreach ($permisosCriticos as $permisosCritico) {
                    $estadoAnterior = intval($permisosActuales[$idRol][$permisosCritico] ?? 0);
                    $estadoNuevo = isset($accionesRol[$permisosCritico]) ? 1 : 0;

                    // Si intenta quitarse un permiso crítico de su propio rol
                    if ($estadoAnterior === 1 && $estadoNuevo === 0) {
                        // Forzar que mantenga el permiso crítico
                        $accionesRol[$permisosCritico] = 1;
                        $autobloqueoPrevenido = true;

                        // Registrar intento de auto-bloqueo
                        $detalleIntento = "Intento de auto-bloqueo: se intentó quitar '" . $permisosCritico . "' al propio usuario";
                        $seguridadModel->logCambioPermiso($idRol, $permisosCritico, $estadoAnterior, 1, $detalleIntento, 'Intento de auto-bloqueo bloqueado');
                    }
                }
            }

            if (!$seguridadModel->guardarPermisosLegajos($idRol, $accionesRol)) {
                setAlert('error', 'Error al guardar permisos para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            $permiteVerOtros = ($idRol === 1) ? true : (($visibilidadPost[$idRol] ?? '0') === '1');
            if (!$seguridadModel->guardarVisibilidadLegajosOtros($idRol, $permiteVerOtros)) {
                setAlert('error', 'Error al guardar visibilidad de legajos para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            if (!$seguridadModel->guardarPermisosDashboardCards($idRol, $dashboardCardsPost[$idRol] ?? [])) {
                setAlert('error', 'Error al guardar tarjetas de dashboard para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            $tiposRol = ($idRol === 1)
                ? array_fill_keys(array_map(static fn($tipo) => intval($tipo['id_tipo_legajo'] ?? 0), $tiposLegajo), 1)
                : ($tiposLegajoPost[$idRol] ?? []);
            if (!$seguridadModel->guardarTiposLegajoVisiblesPorRol($idRol, $tiposRol, $tiposLegajo)) {
                setAlert('error', 'Error al guardar tipos de legajo visibles para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            $idDepartamentoRol = intval($rol['id_departamento'] ?? 0);
            $puedeVerFacturacion = $idRol === 1 ? true : (($facturacionPost[$idRol] ?? '1') === '1');
            if (!$funcionalidadesModel->guardarAccesoItemPorRol($idRol, $idDepartamentoRol, 'facturacion', $puedeVerFacturacion, intval($_SESSION['id'] ?? 0))) {
                setAlert('error', 'Error al guardar acceso a Vista Facturación para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            $puedeVerBaseDatosExterna = $idRol === 1 ? true : (($baseDatosExternaPost[$idRol] ?? '1') === '1');
            if (!$funcionalidadesModel->guardarAccesoItemPorRol($idRol, $idDepartamentoRol, 'base_datos_externa', $puedeVerBaseDatosExterna, intval($_SESSION['id'] ?? 0))) {
                setAlert('error', 'Error al guardar acceso a Base de datos externa para el rol: ' . htmlspecialchars($rol['descripcion']));
                session_write_close();
                header("Location: " . base_url() . "seguridad/permisos_legajos");
                exit();
            }

            // Loguear cambios de permisos
            $permisosRolActuales = $permisosActuales[$idRol] ?? [];
            foreach ($acciones as $clave => $info) {
                $estadoAnterior = intval($permisosRolActuales[$clave] ?? 0);
                $estadoNuevo = isset($accionesRol[$clave]) ? 1 : 0;

                if ($estadoAnterior !== $estadoNuevo) {
                    $detalle = "Cambio de permiso para '" . $info['etiqueta'] . "' en rol '" . $rol['descripcion'] . "'";
                    $seguridadModel->logCambioPermiso($idRol, $clave, $estadoAnterior, $estadoNuevo, $detalle);
                }
            }
        }

        // Mensaje personalizado si se previno auto-bloqueo
        if ($autobloqueoPrevenido) {
            setAlert('warning', 'Permisos guardados, pero se evitó bloquearte a ti mismo. Se mantuvieron tus permisos críticos de seguridad.');
        } else {
            setAlert('success', 'Permisos de legajos guardados correctamente.');
        }
        session_write_close();
        header("Location: " . base_url() . "seguridad/permisos_legajos");
        exit();
    }

    /**
     * Vista de administración de roles del sistema.
     */
    public function roles()
    {
        require_once 'Models/SeguridadLegajosModel.php';
        require_once 'Models/UsuariosModel.php';
        $seguridadModel = new SeguridadLegajosModel();
        $usuariosModel = new UsuariosModel();

        // Verificar permiso para gestionar roles
        if (!$this->puedeAccederItemSeguridad('roles', 'gestionar_roles')) {
            setAlert('warning', 'No tienes permiso para gestionar roles.');
            header('Location: ' . base_url() . 'dashboard/listar');
            exit();
        }
        
        $data = [
            'roles' => $seguridadModel->selectRolesVisiblesPara(intval($_SESSION['id_rol'] ?? 0)),
            'departamentos' => $usuariosModel->selectDepartamentos()
        ];
        $this->views->getView($this, "roles", $data);
    }

    /**
     * POST: Agregar un nuevo rol
     */
    public function guardar_rol()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido o expirado.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        $descripcion = trim($_POST['descripcion'] ?? '');
        if ($descripcion === '') {
            setAlert('warning', 'La descripción del rol es requerida.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        $preset = trim($_POST['preset'] ?? 'basico');
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        // Validar que el preset sea válido
        require_once 'Models/SeguridadLegajosModel.php';
        $presetsValidos = array_keys(SeguridadLegajosModel::getPresetsPermisos());
        if (!in_array($preset, $presetsValidos)) {
            $preset = 'basico';
        }

        $seguridadModel = new SeguridadLegajosModel();

        // Verificar permiso para crear rol
        if (!$this->puedeAccederItemSeguridad('roles', 'crear_rol')) {
            setAlert('warning', 'No tienes permiso para crear roles.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }
        
        $id_rol = $seguridadModel->insertarRolConPermisos($descripcion, $preset, $idDepartamento);
        if ($id_rol !== null) {
            setAlert('success', 'Rol agregado.');
        } else {
            setAlert('error', 'Error al agregar el rol.');
        }

        header("Location: " . base_url() . "seguridad/roles");
        exit();
    }

    /**
     * POST: Editar nombre de un rol existente.
     */
    public function actualizar_rol()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido o expirado.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        $id_rol = intval($_POST['id_rol'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);

        if ($id_rol <= 0 || $descripcion === '') {
            setAlert('warning', 'Debe indicar un rol válido y un nuevo nombre.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        require_once 'Models/SeguridadLegajosModel.php';
        $seguridadModel = new SeguridadLegajosModel();

        if (!$this->puedeAccederItemSeguridad('roles', 'editar_rol')) {
            setAlert('warning', 'No tienes permiso para editar roles.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        $rolActual = $seguridadModel->selectRolPorId($id_rol);
        if (empty($rolActual)) {
            setAlert('warning', 'El rol seleccionado no existe.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if ($seguridadModel->existeDescripcionRol($descripcion, $id_rol)) {
            setAlert('warning', 'Ya existe otro rol con ese nombre.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if ($seguridadModel->actualizarRol($id_rol, $descripcion, $idDepartamento)) {
            setAlert('success', 'Rol actualizado correctamente.');
        } else {
            setAlert('error', 'No se pudo actualizar el rol.');
        }

        header("Location: " . base_url() . "seguridad/roles");
        exit();
    }

    /**
     * GET/POST: Eliminar un rol
     */
    public function eliminar_rol()
    {
        $id_rol = intval($_GET['id_rol'] ?? $_POST['id_rol'] ?? 0);
        $rolUsuarioActual = intval($_SESSION['id_rol'] ?? 0);

        if ($id_rol <= 0 || !$this->tokenValidoGetOPost()) {
            setAlert('error', 'Petición inválida o expirada.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if ($id_rol === $rolUsuarioActual) {
            setAlert('warning', 'No puedes eliminar tu propio rol. Solo se permite editar su nombre.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }
        
        require_once 'Models/SeguridadLegajosModel.php';
        $seguridadModel = new SeguridadLegajosModel();

        // Verificar permiso para eliminar rol
        if (!$this->puedeAccederItemSeguridad('roles', 'eliminar_rol')) {
            setAlert('warning', 'No tienes permiso para eliminar roles.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }
        
        $usuariosAsignados = $seguridadModel->contarUsuariosPorRol($id_rol);
        if ($usuariosAsignados > 0) {
            setAlert('warning', 'No se puede eliminar porque hay ' . $usuariosAsignados . ' usuario(s) con este rol. Debe asignarles otro rol primero.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if ($seguridadModel->eliminarRol($id_rol)) {
            setAlert('success', 'Rol eliminado exitosamente.');
        } else {
            setAlert('error', 'Error al eliminar el rol. Verifique que no esté siendo usado por usuarios.');
        }

        header("Location: " . base_url() . "seguridad/roles");
        exit();
    }

    /**
     * GET/POST: Cambiar el estado de un rol (activo/inactivo)
     */
    public function cambiar_estado_rol()
    {
        $id_rol = intval($_GET['id_rol'] ?? $_POST['id_rol'] ?? 0);
        $estado = $_GET['estado'] ?? $_POST['estado'] ?? '';
        $rolUsuarioActual = intval($_SESSION['id_rol'] ?? 0);

        if ($id_rol <= 0 || !in_array($estado, ['activo', 'inactivo']) || !$this->tokenValidoGetOPost()) {
            setAlert('error', 'Petición inválida o expirada.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        if ($id_rol === $rolUsuarioActual) {
            setAlert('warning', 'No puedes cambiar el estado de tu propio rol. Solo se permite editar su nombre.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }

        require_once 'Models/SeguridadLegajosModel.php';
        $seguridadModel = new SeguridadLegajosModel();

        // Verificar permiso para cambiar estado rol
        if (!$this->puedeAccederItemSeguridad('roles', 'cambiar_estado_rol')) {
            setAlert('warning', 'No tienes permiso para cambiar el estado de roles.');
            header("Location: " . base_url() . "seguridad/roles");
            exit();
        }
        
        if ($seguridadModel->cambiarEstadoRol($id_rol, $estado)) {
            $msg = $estado === 'activo' ? 'Rol activado correctamente.' : 'Rol desactivado correctamente.';
            setAlert('success', $msg);
        } else {
            setAlert('error', 'Error al actualizar el estado del rol.');
        }

        header("Location: " . base_url() . "seguridad/roles");
        exit();
    }

    /**
     * Crea la tabla permisos_legajos si no existe (primera ejecución).
     */
    private function asegurarTablaPermisos(): void
    {
        try {
            $config = obtenerConexionBaseSeleccionada(BD_DEFAULT, BD_DEFAULT);
            $pdo = new PDO("mysql:host=" . $config['host'] . ";port=" . $config['port'] . ";dbname=" . BD_DEFAULT, $config['user'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar si la tabla ya existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'permisos_legajos'");
            if ($stmt->rowCount() > 0) {
                // Verificar si tiene la columna id_grupo (modelo viejo) para recrearla
                $stmtCheck = $pdo->query("SHOW COLUMNS FROM `permisos_legajos` LIKE 'id_grupo'");
                if ($stmtCheck->rowCount() > 0) {
                    $pdo->exec("DROP TABLE IF EXISTS `permisos_legajos`");
                } else {
                    return; // Ya existe y es el modelo nuevo
                }
            }

            $sql = "CREATE TABLE IF NOT EXISTS `permisos_legajos` (
              `id` int NOT NULL AUTO_INCREMENT,
              `id_rol` int NOT NULL,
              `accion` varchar(50) NOT NULL,
              `permitido` tinyint(1) NOT NULL DEFAULT 0,
              `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_rol_accion` (`id_rol`, `accion`),
              CONSTRAINT `fk_permisos_legajos_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;";

            $pdo->exec($sql);

            $sqlTipos = "CREATE TABLE IF NOT EXISTS `permisos_legajos_tipos` (
              `id` int NOT NULL AUTO_INCREMENT,
              `id_rol` int NOT NULL,
              `id_tipo_legajo` int NOT NULL,
              `permitido` tinyint(1) NOT NULL DEFAULT 0,
              `actualizado_en` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_rol_tipo_legajo` (`id_rol`, `id_tipo_legajo`),
              KEY `idx_tipo_legajo` (`id_tipo_legajo`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;";

            $pdo->exec($sqlTipos);
        } catch (Throwable $e) {
            error_log("Error creando tabla permisos_legajos: " . $e->getMessage());
            // Si falla, no bloqueamos la ejecución
        }
    }
}
