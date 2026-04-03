<?php
class Usuarios extends Controllers
{
    private ?array $rolSesionCache = null;

    private function esAdministradorGlobal(): bool
    {
        return in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2, 5], true);
    }

    private function esAdministradorScantec(): bool
    {
        return intval($_SESSION['id_rol'] ?? 0) === 1;
    }

    private function obtenerRolSesion(): array
    {
        if ($this->rolSesionCache !== null) {
            return $this->rolSesionCache;
        }

        $idRolSesion = intval($_SESSION['id_rol'] ?? 0);
        foreach ($this->model->selectRoles() as $rol) {
            if (intval($rol['id_rol'] ?? 0) === $idRolSesion) {
                $this->rolSesionCache = $rol;
                return $rol;
            }
        }

        $this->rolSesionCache = [];
        return [];
    }

    private function obtenerIdDepartamentoGestionable(): int
    {
        return intval($this->obtenerRolSesion()['id_departamento'] ?? 0);
    }

    private function esAdministradorDepartamento(): bool
    {
        return !$this->esAdministradorGlobal() && $this->obtenerIdDepartamentoGestionable() > 0;
    }

    private function filtrarRolesGestionables(array $roles): array
    {
        if ($this->esAdministradorScantec()) {
            return $roles;
        }

        if ($this->esAdministradorGlobal()) {
            return array_values(array_filter($roles, static function ($rol) {
                return intval($rol['id_rol'] ?? 0) !== 1;
            }));
        }

        $idDepartamentoGestionable = $this->obtenerIdDepartamentoGestionable();

        return array_values(array_filter($roles, function ($rol) use ($idDepartamentoGestionable) {
            $idRol = intval($rol['id_rol'] ?? 0);
            $idDepartamentoRol = intval($rol['id_departamento'] ?? 0);

            if ($idRol === 1) {
                return false;
            }

            if ($idDepartamentoGestionable > 0) {
                return $idDepartamentoRol === $idDepartamentoGestionable;
            }

            return true;
        }));
    }

    private function filtrarDepartamentosGestionables(array $departamentos): array
    {
        if ($this->esAdministradorGlobal() || !$this->esAdministradorDepartamento()) {
            return $departamentos;
        }

        $idDepartamentoGestionable = $this->obtenerIdDepartamentoGestionable();
        return array_values(array_filter($departamentos, static function ($departamento) use ($idDepartamentoGestionable) {
            return intval($departamento['id_departamento'] ?? 0) === $idDepartamentoGestionable;
        }));
    }

    private function obtenerRolGestionablePorId(int $idRol): array
    {
        foreach ($this->filtrarRolesGestionables($this->model->selectRoles()) as $rol) {
            if (intval($rol['id_rol'] ?? 0) === $idRol) {
                return $rol;
            }
        }

        return [];
    }

    private function puedeVerUsuarioObjetivo(array $usuario): bool
    {
        if ($this->esAdministradorScantec()) {
            return true;
        }

        return intval($usuario['id_rol'] ?? 0) !== 1;
    }

    private function puedeAsignarRolYDepartamento(int $idRolObjetivo, int $idDepartamentoObjetivo): bool
    {
        $rolObjetivo = $this->obtenerRolGestionablePorId($idRolObjetivo);
        if (empty($rolObjetivo)) {
            return false;
        }

        if ($this->esAdministradorScantec()) {
            return true;
        }

        if ($this->esAdministradorGlobal()) {
            return intval($rolObjetivo['id_rol'] ?? 0) !== 1;
        }

        if ($this->esAdministradorDepartamento()) {
            $idDepartamentoGestionable = $this->obtenerIdDepartamentoGestionable();
            return $idDepartamentoGestionable > 0
                && $idDepartamentoObjetivo === $idDepartamentoGestionable
                && intval($rolObjetivo['id_departamento'] ?? 0) === $idDepartamentoGestionable;
        }

        return intval($rolObjetivo['id_rol'] ?? 0) !== 1;
    }

    private function puedeAccederConexiones(): bool
    {
        if (Validador::puedeVer($_SESSION, [1, 2])) {
            return true;
        }

        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                'conexiones',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    private function asegurarAccesoConexiones(): void
    {
        if ($this->puedeAccederConexiones()) {
            return;
        }

        setAlert('warning', 'No tienes permiso para acceder a esta secci?n.');
        if (isset($this->model) && method_exists($this->model, 'bloquarPC_IP')) {
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado a monitor de conexiones');
        }
        header('Location: ' . base_url() . 'expedientes/indice_busqueda');
        exit();
    }

    private function puedeAccederGestionUsuarios(): bool
    {
        if (Validador::puedeVer($_SESSION, [1, 2])) {
            return true;
        }

        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                'gestion_usuarios',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    private function asegurarAccesoGestionUsuarios(): void
    {
        if ($this->puedeAccederGestionUsuarios()) {
            return;
        }

        setAlert('warning', 'No tienes permiso para acceder a la gesti?n de usuarios.');
        if (isset($this->model) && method_exists($this->model, 'bloquarPC_IP')) {
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado a gestion de usuarios');
        }
        header('Location: ' . base_url() . 'expedientes/indice_busqueda');
        exit();
    }

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $urlActual = strtolower(trim((string) ($_GET['url'] ?? ''), '/'));
        
        // Si el usuario YA EST? logueado y trata de acceder a la ra?z o a /usuarios/login
        if (!empty($_SESSION['ACTIVO']) && $_SESSION['ACTIVO'] === true) {
            if ($urlActual === '' || $urlActual === 'usuarios/login' || $urlActual === 'usuarios' || $urlActual === 'home') {
                require_once 'Models/FuncionalidadesModel.php';
                $rutaDestino = FuncionalidadesModel::obtenerRutaRedireccionSegura(
                    intval($_SESSION['id_rol'] ?? 0),
                    intval($_SESSION['id_departamento'] ?? 0)
                );
                header('location: ' . base_url() . $rutaDestino);
                exit();
            }
        }

        $accionesPublicas = ['usuarios/login', '', 'home', 'usuarios/sesion_duplicada', 'usuarios/confirmar_sesion'];

        if (!in_array($urlActual, $accionesPublicas, true) && empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
            exit();
        }
        parent::__construct();
    }
    public function listar()
    {
        $this->asegurarAccesoGestionUsuarios();
        $usuario = array_values(array_filter($this->model->selectUsuarios(), function ($item) {
            return $this->puedeVerUsuarioObjetivo($item);
        }));
        $rolesCatalogo = $this->model->selectRoles();
        $roles = $this->filtrarRolesGestionables($rolesCatalogo);
        $grupos = $this->model->selectGrupos();
        $departamentos = $this->filtrarDepartamentosGestionables($this->model->selectDepartamentos());
        $data = [
            'usuario' => $usuario,
            'roles' => $roles,
            'roles_catalogo' => $rolesCatalogo,
            'grupos' => $grupos,
            'departamentos' => $departamentos
        ];
        $this->views->getView($this, "listar", $data);
    }

    public function activos()
    {
        $this->asegurarAccesoConexiones();
        $activos = $this->model->selectUsuariosActivos();
        $data = ['activos' => $activos];
        $this->views->getView($this, "activos", $data);
    }

    public function grupo()
    {
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta secci?n");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        // Usuario autorizado: obtener datos y mostrar vista
        $grupos = $this->model->selectGrupos();
        $tipos_documentos = $this->model->selectTipoDoc();
        $permisos = $this->model->selectPerDoc();
        $data = [
            'grupos' => $grupos,
            'tipos_documentos' => $tipos_documentos,
            'permisos' => $permisos
        ];
        $this->views->getView($this, "grupo", $data);
    }

    public function asignar_permisos()
    {
        // Verificaci?n del token CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta secci?n");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        // Obtener datos del formulario
        $id_grupo = intval($_POST['id_grupo']);
        $id_tipoDoc = intval($_POST['id_tipoDoc']);
        // Verificar si el permiso ya existe
        $permisoExistente = $this->model->verificarPermisoExistente($id_grupo, $id_tipoDoc);
        if ($permisoExistente['total'] > 0) {
            // Mostrar mensaje de error en caso de duplicado
            setAlert('error', "Permiso existente para este grupo y tipo de documento!");
            session_write_close();
            header('location: ' . base_url() . "usuarios/grupo");
            exit();
        } else {
            // Asignar el nuevo permiso
            $this->model->asignarPermiso($id_grupo, $id_tipoDoc);
            setAlert('success', "El permiso ha sido registrado!");
            session_write_close();
            header('location: ' . base_url() . "usuarios/grupo");
            exit();
        }
    }

    public function eliminar_permiso()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta secci?n");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        $id_permiso = $_POST['id_permiso'];
        $this->model->eliminarPermiso($id_permiso);
        // Redirigir de nuevo a la gesti?n de grupos
        header("Location: " . base_url() . "usuarios/grupo");
    }

    // M?todo para reactivar un permiso desactivado
    public function reactivar_permiso()
    {
        // 1. Validar CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        // 2. Validar Permisos de Usuario (Admin)
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            setAlert('warning', "No tienes permiso para acceder a esta secci?n");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }

        // 3. Procesar la reactivaci?n
        if (isset($_POST['id_permiso'])) {
            $id_permiso = intval($_POST['id_permiso']);

            // Llamamos al modelo para actualizar estado a 1 (ACTIVO)
            $request = $this->model->reactivarPermiso($id_permiso);

            if ($request) {
                setAlert('success', "Permiso reactivado correctamente.");
            } else {
                setAlert('error', "Error al reactivar el permiso.");
            }
        }

        // 4. Redirigir
        header("Location: " . base_url() . "usuarios/grupo");
        die();
    }

    public function reporte()
    {
        $usuario = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();
        $data = ['usuario' => $usuario, 'roles' => $roles, 'grupos' => $grupos];
        ;
        $this->views->getView($this, "reporte", $data);
    }

    public function insertar()
    {
        $this->checkCsrfSafety();
        $this->asegurarAccesoGestionUsuarios();
        // Verificar l?mite de usuarios antes de insertar
        $usuariosActuales = intval($this->model->contarUsuariosActivos()['total'] ?? 0);
        $limiteUsuarios = defined('LICENCIA_MAX_USUARIOS') ? intval(LICENCIA_MAX_USUARIOS) : (defined('LIMITE_USUARIOS') ? intval(LIMITE_USUARIOS) : 0);
        if ($limiteUsuarios > 0 && $usuariosActuales >= $limiteUsuarios) {
            // Pod?s redirigir con mensaje de error o mostrar alerta
            setAlert('warning', 'No se puede agregar m?s usuarios. Se alcanz? el l?mite de la licencia.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit;
        }
        $nombre = htmlspecialchars($_POST['nombre']);
        $usuario = htmlspecialchars($_POST['usuario']);
        $clave = $_POST['clave'];
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $rol = intval($_POST['id_rol'] ?? 0);
        $grupo = intval($this->model->obtenerGrupoRegistroPorDefecto());
        $email = htmlspecialchars($_POST['email']);
        $fuente_registro = 'scantec';

        if (!$this->puedeAsignarRolYDepartamento($rol, $idDepartamento)) {
            setAlert('error', 'No tienes permiso para crear usuarios con ese rol o departamento.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit;
        }

        // Verificar usuarios existente antes de insertar
        $usuariosExiste = $this->model->verificarUsuarioExistente($usuario)['total'];
        if ($usuariosExiste > 0) {
            // Pod?s redirigir con mensaje de error o mostrar alerta
            setAlert('warning', 'Este usuario ya existe.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit;
        }
        // Encriptar la contrase?a con bcrypt y cost de 12
        $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->model->insertarUsuarios($nombre, $idDepartamento, $usuario, $hash, $rol, $grupo, $fuente_registro, $email);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function registrar_grupo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $descripcion = htmlspecialchars($_POST['descripcion']);
        $insert = $this->model->insertarGrupo($descripcion);
        if ($insert) {
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function registrar_tipoDoc()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        // El token CSRF es v?lido y no ha caducado, proceder con la inserci?n de datos
        // Realizar cualquier sanitizaci?n adicional de los datos si es necesario
        $nombre_tipoDoc = htmlspecialchars($_POST['nombre_tipoDoc']);
        $indice_1 = htmlspecialchars($_POST['indice_1']);
        $indice_2 = htmlspecialchars($_POST['indice_2']);
        $indice_3 = htmlspecialchars($_POST['indice_3']);
        $indice_4 = htmlspecialchars($_POST['indice_4']);
        $indice_5 = htmlspecialchars($_POST['indice_5']);
        $indice_6 = htmlspecialchars($_POST['indice_6']);
        $insert = $this->model->insertarTipoDoc($nombre_tipoDoc, $indice_1, $indice_2, $indice_3, $indice_4, $indice_5, $indice_6);
        if ($insert) {
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function editar()
    {
        $this->asegurarAccesoGestionUsuarios();
        if (empty($_GET['id'])) {
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }
        $id = intval($_GET['id']);
        // 2. Obtener Datos del Modelo
        $usuarioRaw = $this->model->editarUsuarios($id);
        $usuario = [];
        if (!empty($usuarioRaw)) {
            if (isset($usuarioRaw[0])) {
                $usuario = $usuarioRaw[0]; // Sacamos la fila 0
            } else {
                $usuario = $usuarioRaw;
            }
        }
        if (empty($usuario)) {
            header("Location: " . base_url() . "usuarios/listar");
            exit();
        }
        if (!$this->puedeVerUsuarioObjetivo($usuario)) {
            setAlert('warning', 'No tienes permiso para acceder a ese usuario.');
            header("Location: " . base_url() . "usuarios/listar");
            exit();
        }
        $rol = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();
        $departamentos = $this->filtrarDepartamentosGestionables($this->model->selectDepartamentos());
        $data = [
            'usuario' => $usuario,
            'rol' => $this->filtrarRolesGestionables($rol),
            'grupos' => $grupos,
            'departamentos' => $departamentos
        ];

        $this->views->getView($this, "editar", $data);
    }

    public function detalle()
    {
        $this->asegurarAccesoGestionUsuarios();

        if (empty($_GET['id'])) {
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }

        $id = intval($_GET['id']);
        $usuarioRaw = $this->model->editarUsuarios($id);
        $usuario = [];

        if (!empty($usuarioRaw)) {
            $usuario = isset($usuarioRaw[0]) ? $usuarioRaw[0] : $usuarioRaw;
        }

        if (empty($usuario)) {
            setAlert('warning', 'Usuario no encontrado.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }
        if (!$this->puedeVerUsuarioObjetivo($usuario)) {
            setAlert('warning', 'No tienes permiso para acceder a ese usuario.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }

        $nombreRol = 'Sin rol';
        $roles = $this->model->selectRoles();
        foreach ($roles as $rol) {
            if (intval($rol['id_rol'] ?? 0) === intval($usuario['id_rol'] ?? 0)) {
                $nombreRol = (string)($rol['descripcion'] ?? $nombreRol);
                break;
            }
        }

        $data = [
            'usuario' => $usuario,
            'nombre_rol' => $nombreRol
        ];

        $this->views->getView($this, "detalle", $data);
    }

    public function actualizar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        // El token CSRF es v?lido y no ha caducado, proceder con la actualizaci?n de usuario
        // Realizar cualquier sanitizaci?n adicional de los datos si es necesario
        $this->asegurarAccesoGestionUsuarios();
        $id = intval($_POST['id'] ?? 0);
        $usuarioActualRaw = $id > 0 ? $this->model->editarUsuarios($id) : [];
        $usuarioActual = [];
        if (!empty($usuarioActualRaw)) {
            $usuarioActual = isset($usuarioActualRaw[0]) ? $usuarioActualRaw[0] : $usuarioActualRaw;
        }
        if (!empty($usuarioActual) && !$this->puedeVerUsuarioObjetivo($usuarioActual)) {
            setAlert('error', 'No tienes permiso para modificar ese usuario.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }
        $nombre = htmlspecialchars(trim((string)($_POST['nombre'] ?? '')));
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $usuario = htmlspecialchars(trim((string)($_POST['usuario'] ?? '')));
        $rol = intval($_POST['id_rol'] ?? 0);
        if ($rol <= 0) {
            $rol = intval($usuarioActual['id_rol'] ?? 0);
        }
        $grupo = intval($_POST['id_grupo'] ?? 0);
        $email = htmlspecialchars(trim((string)($_POST['email'] ?? '')));
        $claveNueva = trim((string)($_POST['clave'] ?? ''));
        $estadoUsuario = htmlspecialchars(trim((string)($_POST['estado_usuario'] ?? 'ACTIVO')));
        $hashNuevaClave = '';
        if ($claveNueva !== '') {
            $hashNuevaClave = password_hash($claveNueva, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (!$this->puedeAsignarRolYDepartamento($rol, $idDepartamento)) {
            setAlert('error', 'No tienes permiso para asignar ese rol o departamento.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }

        // Actualizar el usuario en la base de datos
        $actualizar = $this->model->actualizarUsuarios($nombre, $idDepartamento, $usuario, $rol, $grupo, $email, $estadoUsuario, $id, $hashNuevaClave);
        // Verificar si la actualizaci?n fue exitosa
        if ($actualizar == 1) {
            $alert = 'modificado';
        } else {
            $alert = 'error';
        }
        header('Location: ' . base_url() . 'usuarios/listar');
        exit();
    }

    public function eliminar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $this->checkAccessSafetyUpdate([1, 2], 'usuarios/eliminar');
        $id = htmlspecialchars($_POST['id']);
        $this->model->eliminarUsuarios($id);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function bloquear()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $id = htmlspecialchars($_POST['id']);
        $this->checkAccessSafetyUpdate([1, 2], 'usuarios/bloquear');
        $this->model->bloquearUsuarios($id);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function reingresar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inv?lido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $id = htmlspecialchars($_POST['id']);
        $this->checkAccessSafetyUpdate([1, 2], 'usuarios/reingresar');
        $this->model->reingresarUsuarios($id);
        $this->model->selectUsuarios();
        header('location: ' . base_url() . 'usuarios/listar');
        die();
    }

    // public function login()
    // {
    //     // Aseguramos que la sesi?n est? activa para manejar los intentos y las alertas
    //     if (session_status() === PHP_SESSION_NONE) {
    //         session_start();
    //     }

    //     if (!isset($_SESSION['login_attempts'])) {
    //         $_SESSION['login_attempts'] = 0;
    //     }

    //     if (!empty($_POST['usuario']) && !empty($_POST['clave'])) {
    //         $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');
    //         $claveIngresada = $_POST['clave'];

    //         // Consulta al modelo
    //         $data = $this->model->selectUsuario($usuario);

    //         /**
    //          * VALIDACI?N UNIFICADA
    //          * Se comprueba en un solo bloque si el usuario existe, est? activo y la clave es correcta.
    //          * Si cualquiera de estas falla, el flujo va al 'else' gen?rico.
    //          */
    //         if (!empty($data) && $data['estado_usuario'] == 'ACTIVO' && password_verify($claveIngresada, $data['clave'])) {

    //             // --- CASO 1: LOGIN EXITOSO ---
    //             $_SESSION['id'] = $data['id'];
    //             $_SESSION['nombre'] = $data['nombre'];
    //             $_SESSION['usuario'] = $data['usuario'];
    //             $_SESSION['id_rol'] = $data['id_rol'];
    //             $_SESSION['ACTIVO'] = true;

    //             // Tokens de seguridad
    //             $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    //             $_SESSION['csrf_expiration'] = time() + (30 * 60);

    //             $_SESSION['id_grupo'] = $data['id_grupo'];
    //             $_SESSION['grupo'] = $data['grupo'];
    //             $_SESSION['PERMISOS'] = $this->model->getPermisosByRol($data['id_rol']);

    //             // Reiniciamos intentos al entrar con ?xito
    //             $_SESSION['login_attempts'] = 0;

    //             // Auditor?a
    //             $this->model->registrarVisita($_SESSION['id']);
    //             $this->model->conteoInicioSesion($_SESSION['id']);

    //             // Redirecci?n seg?n rol
    //             if ($data['id_rol'] == 3 || $data['id_rol'] == 4) {
    //                 header('location: ' . base_url() . 'expedientes/indice_busqueda');
    //             } else {
    //                 header('location: ' . base_url() . 'dashboard/listar');
    //             }
    //             exit();

    //         } else {
    //             // --- CASO 2: LOGIN FALLIDO (Gen?rico por seguridad) ---
    //             $_SESSION['login_attempts']++;

    //             if ($_SESSION['login_attempts'] >= 3) {
    //                 // Bloqueo de seguridad
    //                 $motivo = 'Excedi? el n?mero de intentos de inicio de sesi?n (Credenciales inv?lidas)';

    //                 // Solo intentamos bloquear en la DB si el usuario realmente existe
    //                 if (!empty($data)) {
    //                     $this->model->bloquearUsuarios($usuario);
    //                 }

    //                 // Bloqueo por IP (Siempre se ejecuta para frenar ataques de fuerza bruta)
    //                 $this->model->bloquarPC_IP($usuario, $motivo);

    //                 setAlert('error', "ACCESO RESTRINGIDO: Demasiados intentos fallidos. Su acceso ha sido bloqueado por seguridad.");
    //                 header('location: ' . base_url());
    //                 exit();

    //             } else {
    //                 // Mensaje gen?rico para no revelar si el usuario existe o no
    //                 $restantes = 3 - $_SESSION['login_attempts'];
    //                 setAlert('error', "Usuario o contrase?a incorrecta. Le quedan $restantes intentos.");

    //                 header('location: ' . base_url());
    //                 exit();
    //             }
    //         }
    //     } else {
    //         // CASO 3: CAMPOS VAC?OS
    //         setAlert('warning', "Debe completar todos los campos del formulario.");
    //         header('location: ' . base_url());
    //         exit();
    //     }
    // }
    public function login()
    {
          if (session_status() === PHP_SESSION_NONE) session_start();
          if (class_exists('LicenseLoader')) {
              $licenciaEstado = LicenseLoader::verificarEstado();
              if (empty($licenciaEstado['status'])) {
                  setAlert('error', (string) ($licenciaEstado['msg'] ?? 'La licencia no es valida o no esta disponible.'));
                  header('location: ' . base_url());
                  exit();
              }
          }
          if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;

        // --- 1. LIMPIEZA INTELIGENTE DE USUARIO ---
        $usuario_raw = trim($_POST['usuario'] ?? '');
        
        // Si el usuario escribe por costumbre "DOMINIO\usuario", le quitamos el dominio
        if (strpos($usuario_raw, '\\') !== false) {
            $usuario_raw = explode('\\', $usuario_raw)[1]; 
        }        
        if (strpos($usuario_raw, '@') !== false) {
            $usuario_raw = explode('@', $usuario_raw)[0]; 
        }

        $usuario = htmlspecialchars($usuario_raw, ENT_QUOTES, 'UTF-8');
        $claveIngresada = $_POST['clave'] ?? '';
        $fuente_registro = $_POST['fuente_registro'] ?? 'scantec';

        if (empty($usuario) || empty($claveIngresada)) {
            setAlert('warning', "Debe completar todos los campos."); 
            header('location: ' . base_url()); 
            exit();
        }

        try {
            $data = $this->model->selectUsuario($usuario);
        } catch (Throwable $e) {
            setAlert('error', "No se pudo conectar a la base de datos configurada. Verifique las credenciales del sistema.");
            header('location: ' . base_url());
            exit();
        }
        if (empty($data)) {
            setAlert('error', "El usuario no existe en la base de datos.");
            header('location: ' . base_url()); 
            exit();
        }

        if ($data['estado_usuario'] !== 'ACTIVO') {
            setAlert('error', "Tu usuario est? inactivo o bloqueado.");
            header('location: ' . base_url()); 
            exit();
        }

        $auth_success = false;

        // =========================================================
        // 1. MODO DIRECTORIO ACTIVO (Si seleccion? LDAP)
        // =========================================================
        if ($fuente_registro === 'LDAP') {
            require_once 'Models/ConfiguracionModel.php';
            $configModel = new ConfiguracionModel();
            $configLdapArray = $configModel->selectLDAP_datos();
            $configLdap = !empty($configLdapArray) ? $configLdapArray[0] : null;

            if ($configLdap && !empty($configLdap['ldapHost'])) {
                $ldap_host = $configLdap['ldapHost'];
                $ldap_port = !empty($configLdap['ldapPort']) ? $configLdap['ldapPort'] : 389;
                $ldap_host = str_replace(['ldap://', 'ldaps://'], '', $ldap_host);

                $ldap_conn = @ldap_connect($ldap_host, $ldap_port);
                
                if ($ldap_conn) {
                    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

                    // --- GENERADOR DIN?MICO DE DOMINIO NETBIOS ---
                    // Extrae la empresa desde "OU=printec,DC=printec,DC=local" -> "PRINTEC"
                    $dominio_netbios = 'DOMINIO'; // Valor por defecto
                    if (preg_match('/DC=([^,]+)/i', $configLdap['ldapBaseDn'], $matches)) {
                        $dominio_netbios = strtoupper($matches[1]);
                    }

                    // Arma el formato correcto autom?ticamente (Ej: PRINTEC\aldo.silva)
                    $usuario_ad = $dominio_netbios . "\\" . $usuario; 

                    if (@ldap_bind($ldap_conn, $usuario_ad, $claveIngresada)) {
                        $auth_success = true; // ?El AD acept? la clave!
                    }
                }
            }
        } 
        // =========================================================
        // 2. MODO USUARIO LOCAL
        // =========================================================
        else {
            if (password_verify($claveIngresada, $data['clave'])) {
                $auth_success = true;
            }
        }

        // =========================================================
        // 3. RESOLUCI?N DE ACCESO
        // =========================================================
        if ($auth_success) {

            // --- DETECCI?N DE SESI?N DUPLICADA ---
            if ($this->model->verificarSesionActivaDeUsuario($data['id'])) {
                $_SESSION['pending_login'] = [
                    'id'       => $data['id'],
                    'nombre'   => $data['nombre'],
                    'usuario'  => $data['usuario'],
                    'id_rol'   => $data['id_rol'],
                    'id_grupo' => $data['id_grupo'] ?? 0,
                    'grupo'    => $data['grupo'] ?? '',
                ];
                // Generar token CSRF para el formulario de confirmaci?n
                $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
                $_SESSION['csrf_expiration'] = time() + (5 * 60); // 5 minutos para decidir
                header('location: ' . base_url() . 'usuarios/sesion_duplicada');
                exit();
            }

            // --- LOGIN NORMAL (sin sesi?n duplicada) ---
            $this->completarLogin($data);

        } else {
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 3) {
                $this->model->bloquearUsuarios($usuario);
                $this->model->bloquearPC_IP($usuario, 'Excedi? intentos');
            }

            $restantes = 3 - $_SESSION['login_attempts'];
            setAlert('error', "Usuario o contrase?a incorrecta. Le quedan $restantes intentos.");
            header('location: ' . base_url());
            exit();
        }
    }

    // =========================================================
    // M?TODO PRIVADO: Finaliza el inicio de sesi?n (reutilizable)
    // =========================================================
    private function completarLogin(array $data): void
    {
        $_SESSION['id']              = $data['id'];
        $_SESSION['nombre']          = $data['nombre'];
        $_SESSION['usuario']         = $data['usuario'];
        $_SESSION['id_rol']          = $data['id_rol'];
        $_SESSION['id_departamento'] = $data['id_departamento'] ?? 0;
        $_SESSION['ACTIVO']          = true;
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        $_SESSION['csrf_expiration'] = time() + (30 * 60);
        $_SESSION['id_grupo']        = $data['id_grupo'] ?? 0;
        $_SESSION['grupo']           = $data['grupo'] ?? '';
        $_SESSION['PERMISOS']        = $this->model->getPermisosByRol($data['id_rol']);
        $_SESSION['login_attempts']  = 0;

        $this->model->registrarVisita($_SESSION['id']);
        $this->model->conteoInicioSesion($_SESSION['id']);

        require_once 'Models/FuncionalidadesModel.php';
        $rutaDestino = FuncionalidadesModel::obtenerRutaRedireccionSegura(
            intval($data['id_rol']),
            intval($data['id_departamento'] ?? 0)
        );
        header('location: ' . base_url() . $rutaDestino);
        exit();
    }

    // =========================================================
    // Muestra la pantalla de confirmaci?n de sesi?n duplicada
    // =========================================================
    public function sesion_duplicada()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Si no hay un login pendiente, volver al inicio (acceso directo no permitido)
        if (empty($_SESSION['pending_login'])) {
            header('location: ' . base_url());
            exit();
        }

        $data = [
            'nombre_usuario' => htmlspecialchars($_SESSION['pending_login']['nombre'] ?? 'Usuario'),
        ];
        $this->views->getView($this, 'sesion_duplicada', $data);
    }

    // =========================================================
    // Procesa la elecci?n del usuario ante sesi?n duplicada
    // =========================================================
    public function confirmar_sesion()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Validar CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inv?lido.');
            header('location: ' . base_url());
            exit();
        }

        // Validar que exista un login pendiente
        if (empty($_SESSION['pending_login'])) {
            setAlert('warning', 'No hay un inicio de sesi?n pendiente.');
            header('location: ' . base_url());
            exit();
        }

        $accion = trim($_POST['accion'] ?? '');
        $pendingData = $_SESSION['pending_login'];
        unset($_SESSION['pending_login']); // Limpiar siempre

        if ($accion === 'cerrar_anterior') {
            // Cerrar todas las sesiones activas del usuario y completar el login
            $this->model->cerrarSesionesActivasDeUsuario($pendingData['id']);
            $this->model->restarInicioSesion($pendingData['id']);
            $this->completarLogin($pendingData);

        } elseif ($accion === 'cancelar') {
            setAlert('info', 'Inicio de sesi?n cancelado.');
            header('location: ' . base_url());
            exit();

        } else {
            setAlert('error', 'Acci?n no reconocida.');
            header('location: ' . base_url());
            exit();
        }
    }

    // =========================================================
    // Muestra el formulario (Tu archivo cambiar_pass.php)
    // =========================================================
    public function cambiar_pass()
    {
        $data['page_title'] = "Cambiar Contrase?a";
        // Cargamos tu vista espec?fica: Views/Usuarios/cambiar_pass.php
        $this->views->getView($this, "cambiar_pass", $data);
    }
    // =========================================================
    //Recibe los datos y actualiza
    // =========================================================
    public function actualizar_password()
    {
        // Validaci?n CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token inv?lido.");
            header("Location: " . base_url() . "usuarios/cambiar_pass");
            exit();
        }
        if ($_POST) {
            $idUser = $_SESSION['idUser'] ?? $_SESSION['id'];
            // Nombres de los inputs que definimos en la vista
            $actual = $_POST['clave_actual'];
            $nueva = $_POST['clave_nueva'];
            $confirmar = $_POST['clave_confirmar'];
            // A. Validaciones 
            if (empty($actual) || empty($nueva) || empty($confirmar)) {
                setAlert('error', "Todos los campos son obligatorios.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
                exit();
            }
            if ($nueva !== $confirmar) {
                setAlert('error', "Las contrase?as nuevas no coinciden.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
                exit();
            }
            // B. Verificar contrase?a actual en BD
            $dataDB = $this->model->getPassword($idUser);
            // Ajuste por si el modelo devuelve array [0] o plano
            $passDB = (isset($dataDB[0]['clave'])) ? $dataDB[0]['clave'] : ($dataDB['clave'] ?? '');
            if (password_verify($actual, $passDB)) {
                // Generar Hash y Actualizar
                $nuevaHash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);
                $request = $this->model->cambiarContra($nuevaHash, $idUser);
                if ($request) {
                    setAlert('success', "Contrase?a actualizada correctamente.");
                    if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2) {
                        header("Location: " . base_url() . "usuarios/listar");
                    }
                    // Si es Usuario normal, NO tiene permiso de ver 'listar', as? que va a su perfil o dashboard
                    else {
                        header("Location: " . base_url() . "usuarios/perfil");
                        // O si prefieres que vaya al inicio: "dashboard"
                    }
                    exit();
                } else {
                    setAlert('error', "Error al guardar en base de datos.");
                    header("Location: " . base_url() . "usuarios/cambiar_pass");
                }
            } else {
                setAlert('error', "La contrase?a actual es incorrecta.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
            }
        }
        exit();
    }

    public function salir()
    {
        // 1. Recuperar la sesi?n existente (CR?TICO: Esto recupera el session_id)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['id'])) {
            $idUser = $_SESSION['id'];
            // Al llamar a estas funciones, el modelo usar? session_id() internamente
            // para saber exactamente qu? fila cerrar en la BD.
            $this->model->actualizarVisita($idUser);
            // Restamos 1 al contador global del usuario
            $this->model->restarInicioSesion($idUser);
        }
        // 2. Destruir la sesi?n del servidor
        session_unset();
        session_destroy();
        // 3. Redirigir
        header('location: ' . base_url());
        exit();
    }

    public function fin_session()
    {
        $this->asegurarAccesoConexiones();
        if (isset($_GET['id_visita']) && isset($_GET['id'])) {
            // Limpieza de datos
            $id_visita = intval($_GET['id_visita']); // Forzamos a entero por seguridad
            $id_usuario = intval($_GET['id']);
            // 1. Restar inicio de sesi?n (si tu l?gica lo requiere)
            $this->model->restarInicioSesion($id_usuario);
            // 2. ACTUALIZAR VISITA A 'INACTIVO' (Esto es lo que dispara el Kick)
            $this->model->actualizarVisitas($id_visita);
            // 3. Redireccionar
            header("Location: " . base_url() . "Usuarios/activos?msg=killed");
            die();
        } else {
            // Si faltan datos
            header("Location: " . base_url() . "Usuarios/activos?msg=error");
            die();
        }
    }

    public function Ayuda()
    {
        $manuales = $this->obtenerManualesAyudaDisponibles();
        $manualSeleccionado = trim((string)($_GET['manual'] ?? ''));

        if ($manualSeleccionado === '' || !isset($manuales[$manualSeleccionado])) {
            $manualSeleccionado = array_key_first($manuales);
        }

        $data = [
            'manuales' => $manuales,
            'manual_seleccionado' => $manualSeleccionado,
        ];

        $this->views->getView($this, 'ayuda', $data);
    }

    public function ver_manual_ayuda()
    {
        $manuales = $this->obtenerManualesAyudaDisponibles();
        $manualSeleccionado = trim((string)($_GET['manual'] ?? ''));

        if ($manualSeleccionado === '' || !isset($manuales[$manualSeleccionado])) {
            header('HTTP/1.1 404 Not Found');
            exit('Manual no encontrado.');
        }

        $archivoConfig = $manuales[$manualSeleccionado];
        $rutaPdf = ROOT_PATH . 'Assets/files/' . $archivoConfig['archivo'];
        if (!is_file($rutaPdf)) {
            header('HTTP/1.1 404 Not Found');
            exit('Archivo no disponible.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($archivoConfig['archivo']) . '"');
        header('Content-Length: ' . filesize($rutaPdf));
        readfile($rutaPdf);
        exit;
    }

    private function obtenerManualesAyudaDisponibles(): array
    {
        return [
            'manual_general' => [
                'titulo' => 'Manual General Scantec',
                'archivo' => 'SCANTEC_MANUAL.pdf',
                'descripcion' => 'Versi?n general del manual integral del sistema.',
            ],
            'admin_legajos' => [
                'titulo' => 'Manual Administrador Legajos',
                'archivo' => 'Manual Administrador Legajos.pdf',
                'descripcion' => 'Funciones de armado, verificaci?n y administraci?n de legajos.',
            ],
            'admin_sistema' => [
                'titulo' => 'Manual Administrador Sistema',
                'archivo' => 'Manual Administrador Sistema.pdf',
                'descripcion' => 'Configuraci?n general, usuarios, roles, seguridad y mantenimiento.',
            ],
            'operador_legajos' => [
                'titulo' => 'Manual Operador Legajos',
                'archivo' => 'Manual Operador Legajos.pdf',
                'descripcion' => 'Carga operativa y seguimiento diario de legajos.',
            ],
        ];
    }

    /*  public function importar(){
        if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            // Redirigir y mostrar un mensaje de error en caso de token CSRF inv?lido o caducado
          header("Location: " . base_url() . "?error=csrf");
          die();
          }
         require_once 'Config/Config.php';

        // Conexi?n a la base de datos
        try {
            $pdo = new PDO(
            "mysql:host=".HOST.";dbname=".BD.";charset=utf8",
            DB_USER, PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Exception $ex) { exit($ex->getMessage()); }

        if(isset($_FILES["file"]))
        {
            $file_type=$_FILES["file"]["type"];
            $file_name=$_FILES["file"]["name"];
            $file_size=$_FILES["file"]["size"];
            $file_tmp=$_FILES["file"]["tmp_name"];
            $file_ext=pathinfo($file_name,PATHINFO_EXTENSION);

            if($file_ext=='csv')
            {
                $fh = fopen($file_tmp, "r");
                if ($fh === false) {
                    exit("No se pudo abrir el archivo CSV cargado");
                }

                // (C) IMPORT ROW BY ROW
                while (($row = fgetcsv($fh)) !== false) {
                    try {
                        //print_r($row);
                        $nombre = htmlspecialchars($row[0]);
                        $usuario = htmlspecialchars($row[1]);
                        $clave = $row[2];
                        // Encriptar la contrase?a con SHA-512
                        $passwordHash = hash('SHA512', $clave);
                        $id_rol = 3;
                        $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                    }catch (Exception $ex) { echo $ex->getmessage(); }
                }
                fclose($fh);                
                header("location: " . base_url() . "usuarios/listar");
                die();
            }
            else if($file_ext=='xls' || $file_ext=='xlsx')
            {
                require_once 'Libraries/vendor/autoload.php';

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

                if($file_ext == 'xls')
                {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }

                $spreadsheet = $reader->load($file_tmp);

                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                foreach ($sheetData as $key=>$value) {
                    //if($key == 1) continue; //si quieres omitir la primer fila
                    try {
                        //print_r($value);
                        $nombre = htmlspecialchars($value['A']);
                        $usuario = htmlspecialchars($value['B']);
                        $clave = $value['C'];
                        // Encriptar la contrase?a con SHA-512
                        $passwordHash = hash('SHA512', $clave);
                        $id_rol = 3;
                        $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                    }catch (Exception $ex){  // Encriptar la contrase?a con SHA-512
                    $passwordHash = hash('SHA512', $clave);
                    $id_rol = 3;
                    $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                }catch (Exception $ex) { 
                    echo $ex->getmessage(); }
                }
                header("location: " . base_url() . "usuarios/listar");
                die();
                }
            }
        } */

    /**
     * Funci?n para validar que el archivo tenga la estructura correcta.
     */
    private function validarEstructura($header)
    {
        $estructuraCorrecta = ["nombre", "usuario", "clave", "rol", "grupo", "email"];
        return count(array_intersect($estructuraCorrecta, $header)) === count($estructuraCorrecta);
    }

    /**
     * Funci?n para validar que la contrase?a cumpla los requisitos.
     */
    private function validarClave($clave)
    {
        $regex = "/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\-_.])(?=.{7,})/";
        return preg_match($regex, $clave);
    }


    public function importar()
    {
        // 1. Validaci?n de Seguridad (CSRF)
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "usuarios/listar");
            die();
        }

        // 2. Verificar que se subi? un archivo sin errores
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {

            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_name = $_FILES["file"]["name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $usuarios = [];
            $errores = []; // Acumulador de errores para no dejar el proceso a medias
            $fila_actual = 0;

            // 3. PROCESAR CSV
            if ($file_ext === 'csv') {
                $fh = fopen($file_tmp, "r");
                if ($fh === false) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo leer el archivo CSV.'];
                    header("Location: " . base_url() . "usuarios/listar");
                    die();
                }

                while (($row = fgetcsv($fh)) !== false) {
                    $fila_actual++;
                    // Saltar la primera fila si contiene cabeceras (t?tulos)
                    if ($fila_actual === 1)
                        continue;

                    // Evitar filas vac?as
                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row[0] ?? ''));
                    $usuario = htmlspecialchars(trim($row[1] ?? ''));
                    $clave = trim($row[2] ?? '');
                    $id_rol = (int) ($row[3] ?? 0);
                    $id_grupo = (int) ($row[4] ?? 0);
                    $email = filter_var(trim($row[5] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contrase?a del usuario '{$usuario}' es d?bil.";
                        continue;
                    }

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email];
                }
                fclose($fh);

                // 4. PROCESAR EXCEL (XLS, XLSX)
            } else if ($file_ext === 'xls' || $file_ext === 'xlsx') {
                require_once 'Libraries/vendor/autoload.php';

                $reader = ($file_ext === 'xlsx') ? new \PhpOffice\PhpSpreadsheet\Reader\Xlsx() : new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $reader->setReadDataOnly(true); // Optimiza la lectura de memoria
                $spreadsheet = $reader->load($file_tmp);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $index => $row) {
                    $fila_actual = $index;
                    // Saltar la cabecera (Fila 1 de Excel)
                    if ($fila_actual === 1)
                        continue;

                    // Evitar filas vac?as
                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row['A'] ?? ''));
                    $usuario = htmlspecialchars(trim($row['B'] ?? ''));
                    $clave = trim($row['C'] ?? '');
                    $id_rol = (int) ($row['D'] ?? 0);
                    $id_grupo = (int) ($row['E'] ?? 0);
                    $email = filter_var(trim($row['F'] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contrase?a del usuario '{$usuario}' es d?bil.";
                        continue;
                    }

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Formato no soportado. Use CSV, XLS o XLSX.'];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            // 5. VALIDACI?N FINAL: Si hay un solo error, frenar todo para proteger la BD
            if (count($errores) > 0) {
                // Limitamos a mostrar solo los primeros 5 errores para no desbordar la alerta
                $errores_mostrar = array_slice($errores, 0, 5);
                $mensaje_error = "<b>La importaci?n fue cancelada por errores de formato:</b><br><br>" . implode("<br>", $errores_mostrar);
                if (count($errores) > 5)
                    $mensaje_error .= "<br><i>...y " . (count($errores) - 5) . " errores m?s.</i>";

                $_SESSION['alert'] = ['type' => 'error', 'message' => $mensaje_error];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }
            // 6. INSERCI?N SEGURA (Solo llega aqu? si el 100% de los usuarios pasaron las validaciones)
            $importados = 0;
            foreach ($usuarios as $user) {
                [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email] = $user;
                $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);

                $insert = $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, (string) $id_rol, (string) $id_grupo, $fuente_registro, $email);
                if ($insert) {
                    $importados++;
                }
            }

            if ($importados > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "??xito! Se importaron correctamente $importados usuarios."];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'El archivo se ley?, pero no se import? ning?n usuario (Archivo vac?o).'];
            }

        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Ocurri? un error al subir el archivo.'];
        }

        header("Location: " . base_url() . "usuarios/listar");
        die();
    }

    public function sincronizarAD()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('max_execution_time', 300);

        // 1. Verificar Token CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inv?lido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 2. Obtener configuraci?n
        $id_config = intval($_POST['id']);
        $ldapConfig = $this->model->getLdapConfigById($id_config);

        // Parche de robustez: Si el modelo devuelve un array de arrays, tomamos el primero
        if (isset($ldapConfig[0]['ldapHost'])) {
            $ldapConfig = $ldapConfig[0];
        }

        if (empty($ldapConfig) || !isset($ldapConfig['ldapHost'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se pudieron leer los datos de la configuraci?n (ID: ' . $id_config . ').'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 3. DESENCRIPTAR CONTRASE?A
        // Usamos la funci?n si existe, sino texto plano (para evitar errores fatales)
        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($ldapConfig['ldapPass']);
        } else {
            $password_real = $ldapConfig['ldapPass'];
        }

        // 4. Conexi?n LDAP
        $ldapConn = ldap_connect($ldapConfig['ldapHost'], $ldapConfig['ldapPort']);
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (!$ldapConn || !@ldap_bind($ldapConn, $ldapConfig['ldapUser'], $password_real)) {
            $err = ldap_error($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Fallo de Conexi?n LDAP: $err"];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 5. B?squeda
        $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(mail=*))";
        $attributes = ['samaccountname', 'mail', 'displayname', 'givenname', 'sn'];

        $search = ldap_search($ldapConn, $ldapConfig['ldapBaseDn'], $filter, $attributes);

        if (!$search) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error en la b?squeda. Verifique el BaseDN configurado.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        $entries = ldap_get_entries($ldapConn, $search);

        $contador_nuevos = 0;
        $contador_actualizados = 0;

        // Validamos que haya resultados antes de iterar
        if ($entries['count'] > 0) {
            for ($i = 0; $i < $entries['count']; $i++) {

                $username = isset($entries[$i]['samaccountname'][0]) ? trim($entries[$i]['samaccountname'][0]) : '';
                $email = isset($entries[$i]['mail'][0]) ? trim($entries[$i]['mail'][0]) : '';

                // Construir nombre
                $nombre = '';
                if (isset($entries[$i]['displayname'][0])) {
                    $nombre = $entries[$i]['displayname'][0];
                } else {
                    $given = isset($entries[$i]['givenname'][0]) ? $entries[$i]['givenname'][0] : '';
                    $sn = isset($entries[$i]['sn'][0]) ? $entries[$i]['sn'][0] : '';
                    $nombre = trim($given . ' ' . $sn);
                }

                if (!empty($username) && !empty($email)) {

                    // Contrase?a dummy segura
                    try {
                        $bytes = random_bytes(10);
                    } catch (Exception $e) {
                        $bytes = openssl_random_pseudo_bytes(10);
                    }
                    $password_dummy = password_hash(bin2hex($bytes), PASSWORD_DEFAULT);

                    $rol_defecto = 3;

                    $resultado = $this->model->sincronizarUsuarioLDAP($username, $nombre, $email, $password_dummy, $rol_defecto);

                    if ($resultado == 'insert')
                        $contador_nuevos++;
                    if ($resultado == 'update')
                        $contador_actualizados++;
                }
            }
        } else {
            // MENSAJE MEJORADO: Caso sin resultados
            $_SESSION['alert'] = ['type' => 'info', 'message' => 'Conexi?n exitosa, pero no se encontraron usuarios activos con correo en la ruta especificada.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        ldap_unbind($ldapConn);

        // MENSAJE FINAL MEJORADO (Sin HTML)
        if ($contador_nuevos == 0 && $contador_actualizados == 0) {
            $msg = "Sincronizaci?n completada. No se encontraron usuarios nuevos ni cambios en los existentes.";
            $tipo = "info";
        } else {
            // Ejemplo: "Proceso finalizado. Registrados: 5 | Actualizados: 2"
            $msg = "Proceso finalizado correctamente. Registrados: $contador_nuevos | Actualizados: $contador_actualizados.";
            $tipo = "success";
        }

        $_SESSION['alert'] = ['type' => $tipo, 'message' => $msg];

        header("Location: " . base_url() . "configuracion/servidor_AD");
        die();
    }

    // public function importar(){
    //     if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
    //         header("Location: " . base_url() . "?error=csrf");
    //         die();
    //     }
    //     require_once 'Config/Config.php';

    //     try {
    //         $pdo = new PDO(
    //             "mysql:host=".HOST.";dbname=".BD.";charset=utf8",
    //             DB_USER, PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    //         );
    //     } catch (Exception $ex) { exit($ex->getMessage()); }

    //     if (isset($_FILES["file"])) {
    //         $file_type = $_FILES["file"]["type"];
    //         $file_name = $_FILES["file"]["name"];
    //         $file_size = $_FILES["file"]["size"];
    //         $file_tmp = $_FILES["file"]["tmp_name"];
    //         $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    //         if ($file_ext == 'csv') {
    //             $fh = fopen($file_tmp, "r");
    //             if ($fh === false) {
    //                 exit("No se pudo abrir el archivo CSV cargado");
    //             }

    //             while (($row = fgetcsv($fh)) !== false) {
    //                 try {
    //                     $nombre = htmlspecialchars($row[0]);
    //                     $usuario = htmlspecialchars($row[1]);
    //                     $clave = $row[2];
    //                     $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
    //                     $id_rol = htmlspecialchars($row[3]);
    //                     $id_grupo = htmlspecialchars($row[4]);
    //                     $fuenteRegistro = "scantec-import";
    //                     $id_grupo = htmlspecialchars($row[5]);
    //                     $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol, $id_grupo, $fuenteRegistro, $email);
    //                 } catch (Exception $ex) { 
    //                     echo $ex->getMessage(); 
    //                 }
    //             }
    //             fclose($fh);                
    //             header("location: " . base_url() . "usuarios/listar");
    //             die();
    //         }
    //         else if ($file_ext == 'xls' || $file_ext == 'xlsx') {
    //             require_once 'Libraries/vendor/autoload.php';

    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //             if ($file_ext == 'xls') {
    //                 $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //             }

    //             $spreadsheet = $reader->load($file_tmp);
    //             $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //             foreach ($sheetData as $key => $value) {
    //                 try {
    //                     $nombre = htmlspecialchars($value['A']);
    //                     $usuario = htmlspecialchars($value['B']);
    //                     $clave = $value['C'];
    //                     $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
    //                     $id_rol = htmlspecialchars($value['D']);
    //                     $id_grupo = htmlspecialchars($value['E']);
    //                     $fuenteRegistro = "scantec-import";
    //                     $email = htmlspecialchars($value['F']);
    //                     $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol, $id_grupo, $fuenteRegistro, $email);
    //                 } catch (Exception $ex) { 
    //                     echo $ex->getMessage(); 
    //                 }
    //             }
    //             header("location: " . base_url() . "usuarios/listar");
    //             die();
    //         }
    //     }
    // }


    public function pdf()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $usuarios = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();

        // 2. Instanciar PDF (Usamos plantilla con SCANTEC fijo)
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Usuarios', 'L', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Centrar tabla (A4 Landscape = 297mm. Suma anchos = 265mm. Margen = ~16mm)
        $w = array(15, 70, 45, 50, 50, 35);
        $pdf->SetLeftMargin(16);
        $pdf->setX(16);

        $pdf->Cell($w[0], 7, utf8_decode('N?'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Grupo', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[5], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multil?nea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($usuarios as $row) {
            $nombreGrupo = '';
            foreach ($grupos as $grup) {
                if ($grup['id_grupo'] == $row['id_grupo']) {
                    $nombreGrupo = $grup['descripcion'];
                    break;
                }
            }
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $row['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $pdf->Row(array(
                $row['id'],
                utf8_decode($row['nombre']),
                utf8_decode($row['usuario']),
                utf8_decode($nombreGrupo),
                utf8_decode($nombreRol),
                $row['estado_usuario']
            ));
        }

        $pdf->Output("Usuarios_" . date('Y_m_d') . ".pdf", "I");
    }

    public function pdf_filtro()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $usuarios = $this->model->reporteUsuarios($desde, $hasta);
        $roles = $this->model->selectRoles();

        // 2. Instanciar PDF
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Usuarios (Filtrado)', 'L', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Definir anchos y centrar tabla din?micamente
        $w = array(15, 70, 45, 50, 35); // Suma: 215mm
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->SetLeftMargin($margin);
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N?'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multil?nea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($usuarios as $row) {
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $row['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $pdf->Row(array(
                $row['id'],
                utf8_decode($row['nombre']),
                utf8_decode($row['usuario']),
                utf8_decode($nombreRol),
                $row['estado_usuario']
            ));
        }

        $pdf->Output("Usuarios_Filtrado_" . date('Y_m_d') . ".pdf", "I");
    }

    public function grupo_pdf()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $permisos = $this->model->selectPerDoc();

        // 2. Instanciar PDF
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Permisos por Grupo', 'P', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Centrar tabla
        $w = array(30, 70, 30, 50); // Suma: 180mm
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->SetLeftMargin($margin);
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N? Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Descripci?n Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('N? Tipo Doc'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Tipo Documento', 1, 1, 'C', true);

        // 4. Configurar motor multil?nea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'L'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($permisos as $row) {
            $pdf->Row(array(
                $row['id_grupo'],
                utf8_decode($row['descripcion']),
                $row['id_tipoDoc'],
                utf8_decode($row['nombre_tipoDoc'])
            ));
        }

        $pdf->Output("Permisos_Grupos_" . date('Y_m_d') . ".pdf", "I");
    }

    public function excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $usuario = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();

        $excel = new ReportTemplateExcel('REGISTRO DE USUARIOS', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Encabezados en fila 4
        $headerRow = 4;
        $headers = ['NOMBRE', 'USUARIO', 'GRUPO', 'ROL', 'EMAIL', 'STATUS'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:F$headerRow")->applyFromArray($headerStyle);

        // Datos desde fila 5
        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($usuario as $value) {
            $nombreGrupo = '';
            foreach ($grupos as $grup) {
                if ($grup['id_grupo'] == $value['id_grupo']) {
                    $nombreGrupo = $grup['descripcion'];
                    break;
                }
            }
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $value['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $sheet->setCellValue('A' . $dataRow, $value["nombre"]);
            $sheet->setCellValue('B' . $dataRow, $value["usuario"]);
            $sheet->setCellValue('C' . $dataRow, $nombreGrupo);
            $sheet->setCellValue('D' . $dataRow, $nombreRol);
            $email = isset($value['email']) ? $value['email'] : '';
            $sheet->setCellValue('E' . $dataRow, $email);
            $sheet->setCellValue('F' . $dataRow, $value['estado_usuario']);

            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 40,     // Nombre
            'B' => 'auto', // Usuario
            'C' => 30,     // Grupo
            'D' => 30,     // Rol
            'E' => 45,     // Email (Suele ser largo)
            'F' => 'auto'  // Status
        ]);

        $nombreArchivo = 'Usuarios_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function grupo_excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $perdoc = $this->model->selectPerDoc();

        $excel = new ReportTemplateExcel('GRUPOS Y DEPENDENCIAS', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Encabezados en fila 4 (estandarizado)
        $headerRow = 4;
        $headers = ['ID GRUPO', 'NOMBRE GRUPO', 'ID TIPO DOC', 'TIPO DOCUMENTO'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:D$headerRow")->applyFromArray($headerStyle);

        // Datos desde fila 5
        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($perdoc as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["id_grupo"]);
            $sheet->setCellValue('B' . $dataRow, $value["descripcion"]);
            $sheet->setCellValue('C' . $dataRow, $value["id_tipoDoc"]);
            $sheet->setCellValue('D' . $dataRow, $value['nombre_tipoDoc']);

            $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 45, // Descripci?n de grupo larga
            'C' => 'auto',
            'D' => 45  // Tipo de documento largo
        ]);

        $excel->output('Grupo_Dependencias_' . date('Y_m_d_His'));
    }

    public function usuario_muestra()
    {
        date_default_timezone_set('America/Asuncion');
        $ruta = base_url() . 'Assets/files/usuarios.csv';

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $ruta . '".csv');
        readfile("./saldos.csv");
    }
}
