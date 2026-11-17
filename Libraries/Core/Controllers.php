<?php
class Controllers
{
    //$this->views = new Views();
    //$this->loadModel();
    // Propiedades para vistas y modelos
    public $views;
    public $model;

    private const RUTAS_PUBLICAS = [
        'home/home',
        'home/restablecer_pw',
        'home/restaurarpass',
        'home/registrarse',
        'home/guardar_registro',
        'home/seleccionar_bd',
        'usuarios/login',
        'usuarios/sesion_duplicada',
        'usuarios/confirmar_sesion',
        'usuarios/salir',
        'legajos/formulario_externo',
        'legajos/guardar_borrador_formulario_externo',
        'legajos/enviar_formulario_externo',
        'legajos/confirmacion_formulario_externo',
    ];
    private const CONTROLADORES_PUBLICOS = ['errors'];
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->exigirSesionWeb();
        $this->bootstrapJerarquiaRolesScantec();
        $this->views = new Views();
        $this->loadModel();
        $this->validarSesionActivaEnBD();
        $this->validarFuncionalidadHabilitada();
        $this->sincronizarEstadosLegajosDiario();
    }

    public function loadModel()
    {
        $model = get_class($this) . "Model";
        $routClass = "Models/" . $model . ".php";
        if (file_exists($routClass)) {
            require_once($routClass);
            $this->model = new $model();
        }
    }

    private function rutaActualNormalizada(): string
    {
        $url = strtolower(trim((string) ($_GET['url'] ?? ''), '/'));
        if ($url === '') {
            return 'home/home';
        }
        $partes = explode('/', $url);
        $controlador = $partes[0] !== '' ? $partes[0] : 'home';
        $metodo = (isset($partes[1]) && $partes[1] !== '') ? $partes[1] : 'home';
        return $controlador . '/' . $metodo;
    }

    // ===== NUEVO =====
    private function exigirSesionWeb(): void
    {
        if (PHP_SAPI === 'cli') {
            return;                       // tareas programadas
        }
        if (defined('IS_API') && IS_API === true) {
            return;                       // la API usa JWT
        }

        $ruta = $this->rutaActualNormalizada();
        $controlador = explode('/', $ruta)[0];

        if (
            in_array($controlador, self::CONTROLADORES_PUBLICOS, true)
            || in_array($ruta, self::RUTAS_PUBLICAS, true)
        ) {
            return;
        }

        if (empty($_SESSION['ACTIVO']) || $_SESSION['ACTIVO'] !== true) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Location: ' . base_url());
            exit();
        }
    }


    // Método auxiliar para manejo de CSRF (No requiere cambios)
    protected function checkCsrfSafety()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
    }
    /**
     * Verifica permisos para acceder a una vista (acción GET).
     * @param array $allowedRoles Roles permitidos.
     * @param string $actionContext Cadena 'controlador/accion' para el log.
     */
    protected function checkAccessSafetyView($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {

            // 1. Alerta
            setAlert('warning', "No tienes permiso para acceder a esta sección");

            // 2. Log de Seguridad (Utilizando el contexto)
            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a la vista: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            // 3. Redirección
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }
    // Ejemplo: checkAccessSafetyInsert
    protected function checkAccessSafetyInsert($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {
            setAlert('warning', "No tienes permiso para insertar registros a esta sección");

            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a inserción de: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }

    protected function checkAccessSafetyUpdate($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {
            setAlert('warning', "No tienes permiso para modificar registros a esta sección");

            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a modificación de: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }

    /**
     * Acceso dinámico por ítem de FuncionalidadesModel.
     * Los roles 1 (superadmin Scantec) y 2 (admin local) siempre tienen acceso total.
     * Cualquier otro rol se resuelve contra funcionalidades_acceso_rol_departamento.
     */
    protected function tieneAccesoFuncionalidad(string $itemKey): bool
    {
        if (Validador::puedeVer($_SESSION, [1, 2])) {
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

    /**
     * Igual que los checkAccessSafety*, pero dinámico por itemKey en vez de
     * una lista fija de roles. Usar al inicio de cualquier acción que deba
     * depender de funcionalidades_acceso_rol_departamento.
     */
    protected function asegurarAccesoFuncionalidad(string $itemKey, string $mensaje, string $actionContext = ''): void
    {
        if ($this->tieneAccesoFuncionalidad($itemKey)) {
            return;
        }

        setAlert('warning', $mensaje);

        try {
            require_once 'Models/UsuariosModel.php';
            if (class_exists('UsuariosModel')) {
                $logModel = new UsuariosModel();
                $logModel->bloquearPC_IP(
                    $_SESSION['nombre'] ?? '',
                    'Acceso no autorizado a: ' . ($actionContext !== '' ? $actionContext : $itemKey)
                );
            }
        } catch (Throwable $e) {
            // no bloqueamos el flujo por un fallo de logging
        }

        header('Location: ' . base_url() . 'expedientes/indice_busqueda');
        exit();
    }

    /**
     * Verifica permisos dinámicamente usando la tabla Permisos_Rol.
     * @param string $accion Nombre de la acción que se intenta ejecutar (ej. 'listar', 'actualizar').
     * @param string $mensajeAlerta El mensaje de error específico (ej. "No tienes permiso para insertar...").
     */
    protected function checkDynamicAccess(string $accion, string $mensajeAlerta)
    {
        if (intval($_SESSION['id_rol'] ?? 0) === 1) {
            return;
        }

        // Obtiene el nombre real del controlador (ej. 'Usuarios' o 'Expedientes')
        $controlador = str_replace('Controller', '', get_class($this));
        // Si usas namespaces, podría ser necesario un trim o explode para obtener solo el nombre de la clase.
        // Crea la clave de permiso a buscar en la sesión
        $clavePermiso = $controlador . '/' . $accion;
        // Verificar si el permiso está en la sesión y si está marcado como 'permitido' (1)
        if (!isset($_SESSION['PERMISOS'][$clavePermiso]) || $_SESSION['PERMISOS'][$clavePermiso] !== 1) {
            // 1. Alerta y Log
            setAlert('warning', $mensajeAlerta);
            if (isset($this->model)) {
                $logMessage = 'Acceso dinámico denegado a: ' . $clavePermiso;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }
            // 2. Redirección
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }
    // Método privado para validar si el Admin cerró la sesión
    private function validarSesionActivaEnBD()
    {
        // Solo verificamos si el usuario dice estar logueado
        // CORRECCIÓN: se usa $_SESSION['ACTIVO'] que es la flag real seteada en el login
        if (!isset($_SESSION['ACTIVO']) || $_SESSION['ACTIVO'] !== true) {
            return;
        }

        $mi_session_id = session_id();
        if ($mi_session_id === '') {
            return;
        }

        // CORRECCIÓN: se usa prepared statement a través del modelo para evitar SQL Injection
        // y se elimina la llamada al inexistente $this->select()
        require_once 'Models/UsuariosModel.php';
        if (!class_exists('UsuariosModel')) {
            return;
        }

        try {
            $usuariosModel = new UsuariosModel();
            $request = $usuariosModel->obtenerVisitaActivaPorSession($mi_session_id);

            if (empty($request)) {
                session_unset();
                session_destroy();
                header("Location: " . base_url() . "?msg=kicked");
                exit();
            }

            // Sesión válida: registrar actividad como mucho una vez por minuto
            if (($_SESSION['actividad_bd'] ?? 0) < time() - 60) {
                $usuariosModel->registrarActividad($mi_session_id);
                $_SESSION['actividad_bd'] = time();
            }
        } catch (Throwable $e) {
            return;
        }
    }

    private function sincronizarEstadosLegajosDiario()
    {
        // CORRECCIÓN: se usa $_SESSION['ACTIVO'] que es la flag real seteada en el login
        if (!isset($_SESSION['ACTIVO']) || $_SESSION['ACTIVO'] !== true) {
            return;
        }

        $hoy = date('Y-m-d');
        if (($_SESSION['legajos_sync_fecha'] ?? '') === $hoy) {
            return;
        }

        $modelPath = "Models/LegajosModel.php";
        if (!file_exists($modelPath)) {
            $_SESSION['legajos_sync_fecha'] = $hoy;
            return;
        }

        require_once $modelPath;
        if (!class_exists('LegajosModel')) {
            $_SESSION['legajos_sync_fecha'] = $hoy;
            return;
        }

        try {
            $legajosModel = new LegajosModel();
            $legajosModel->sincronizarEstadosDocumentosLegajo(30);
            $_SESSION['legajos_sync_fecha'] = $hoy;
        } catch (Throwable $e) {
            return;
        }
    }

    private function validarFuncionalidadHabilitada()
    {
        if (defined('IS_API') && IS_API === true) {
            return;
        }

        $rutaActual = strtolower(trim((string) ($_GET['url'] ?? ''), '/'));
        if ($rutaActual === '') {
            return;
        }

        $controladorActual = strtolower(get_class($this));
        if (in_array($controladorActual, ['home', 'errors', 'funcionalidades'], true)) {
            return;
        }

        $modelPath = 'Models/FuncionalidadesModel.php';
        if (!file_exists($modelPath)) {
            return;
        }

        require_once $modelPath;
        if (!class_exists('FuncionalidadesModel')) {
            return;
        }

        try {
            $itemAcceso = FuncionalidadesModel::resolverItemAccesoPorRuta($rutaActual);
            $rutasGestionUsuarios = [
                'usuarios/listar',
                'usuarios/detalle',
                'usuarios/editar',
                'usuarios/actualizar',
                'usuarios/insertar',
                'usuarios/eliminar',
                'usuarios/bloquear',
                'usuarios/reingresar',
                'usuarios/reingresar_masivo',
                'usuarios/importar',
                'usuarios/confirmar_importacion',
                'usuarios/cancelar_importacion',
                'usuarios/excel',
                'usuarios/usuario_muestra',
            ];
            if ($itemAcceso === 'gestion_usuarios' || in_array($rutaActual, $rutasGestionUsuarios, true)) {
                return;
            }

            $funcionalidadesModel = new FuncionalidadesModel();
            $idRolActual = intval($_SESSION['id_rol'] ?? 0);
            $idDepartamentoActual = intval($_SESSION['id_departamento'] ?? 0);

            if (
                $itemAcceso !== null &&
                !$funcionalidadesModel->puedeAccederItemPorContexto($itemAcceso, $idRolActual, $idDepartamentoActual)
            ) {
                setAlert('warning', 'La sección solicitada no está disponible para tu rol y departamento.');
                $rutaDestino = FuncionalidadesModel::obtenerRutaRedireccionSegura($idRolActual, $idDepartamentoActual);
                header('Location: ' . base_url() . $rutaDestino);
                exit();
            }

            $seccion = FuncionalidadesModel::resolverSeccionPorRuta($rutaActual);
            if ($seccion === null) {
                return;
            }

            if ($funcionalidadesModel->estaSeccionHabilitada($seccion)) {
                return;
            }

            setAlert('warning', 'La seccion solicitada se encuentra desactivada por el Administrador del sistema.');
            $rutaDestino = FuncionalidadesModel::obtenerRutaRedireccionSegura($idRolActual, $idDepartamentoActual);
            header('Location: ' . base_url() . $rutaDestino);
            exit();
        } catch (Throwable $e) {
            return;
        }
    }

    private function bootstrapJerarquiaRolesScantec(): void
    {
        try {
            // Solo para usuarios con sesión: refrescar el rol desde la base en cada petición.
            // Si un administrador le cambia o quita el rol a alguien, el cambio aplica de inmediato.
            if (empty($_SESSION['ACTIVO']) || empty($_SESSION['id'])) {
                return;
            }

            require_once 'Models/UsuariosModel.php';
            $usuariosModel = new UsuariosModel();
            $idRolActual = $usuariosModel->obtenerRolUsuarioPorId(intval($_SESSION['id']));
            if ($idRolActual > 0) {
                $_SESSION['id_rol'] = $idRolActual;
                $_SESSION['PERMISOS'] = $usuariosModel->getPermisosByRol($idRolActual);
            }
        } catch (Throwable $e) {
            return;
        }
    }

}
