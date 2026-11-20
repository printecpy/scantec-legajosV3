<?php
class Usuarios extends Controllers
{
    private ?array $rolSesionCache = null;

    private function esAdministradorGlobal(): bool
    {
        return in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2], true);
    }

    private function ejecutarMigracionesPendientesUsuarios()
    {
        $this->model->ensureFechaRegistroUsuarios();
        // futuras migraciones de usuarios
    }

    private function esAdministradorScantec(): bool
    {
        return esSuperadmin();
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
        // El rol 1 nunca se ofrece desde la web: el superadmin se crea solo por consola.
        $roles = array_values(array_filter($roles, static function ($rol) {
            return intval($rol['id_rol'] ?? 0) !== 1;
        }));
        // El rol 2 solo lo ofrecen el superadmin y los administradores (rol 2)
        if (!$this->esAdministradorScantec() && intval($_SESSION['id_rol'] ?? 0) !== 2) {
            $roles = array_values(array_filter($roles, static function ($rol) {
                return intval($rol['id_rol'] ?? 0) !== 2;
            }));
        }

        if ($this->esAdministradorScantec() || $this->esAdministradorGlobal() || $this->puedeGestionarRolesYGrupos()) {
            return $roles;
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
        if ($this->esAdministradorGlobal() || $this->puedeGestionarRolesYGrupos() || !$this->esAdministradorDepartamento()) {
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

    /**
     * Jerarquía: cada quien gestiona solo usuarios con MENOS privilegios.
     *  - Rol 1 (superadmin Scantec): a todos.
     *  - Rol 2 (administrador del cliente): a todos menos al rol 1.
     *  - Cualquier otro rol (ej. seguridad): a todos menos a los roles 1 y 2.
     */
    private function puedeVerUsuarioObjetivo(array $usuario): bool
    {
        $rolObjetivo = intval($usuario['id_rol'] ?? 0);

        if ($this->esAdministradorScantec()) {
            return true;
        }
        if (intval($_SESSION['id_rol'] ?? 0) === 2) {
            return $rolObjetivo !== 1;
        }
        return !in_array($rolObjetivo, [1, 2], true);
    }

    private function puedeAsignarRolYDepartamento(int $idRolObjetivo, int $idDepartamentoObjetivo): bool
    {
        $rolObjetivo = $this->obtenerRolGestionablePorId($idRolObjetivo);
        if ($idRolObjetivo === 1) {
            return false;   // nadie asigna el rol 1 desde la web
        }

        if ($this->esAdministradorScantec()) {
            return true;
        }

        // Quien gestiona roles y grupos puede asignar cualquier rol/departamento,
        // sin restricción de pertenencia. El id_rol 1 (superadmin) queda siempre
        // fuera del alcance de cualquiera que no sea esAdministradorScantec().
        if ($this->puedeGestionarRolesYGrupos()) {
            return intval($rolObjetivo['id_rol'] ?? 0) !== 1;
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

        setAlert('warning', 'No tienes permiso para acceder a esta sección.');
        if (isset($this->model) && method_exists($this->model, 'bloquarPC_IP')) {
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado a monitor de conexiones');
        }
        header('Location: ' . base_url() . 'expedientes/indice_busqueda');
        exit();
    }

    private function puedeGestionarRolesYGrupos(): bool
    {
        if (Validador::puedeVer($_SESSION, [1, 2])) {
            return true;
        }

        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                'roles',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
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

        setAlert('warning', 'No tienes permiso para acceder a la gestión de usuarios.');
        if (isset($this->model) && method_exists($this->model, 'bloquearPC_IP')) {
            $this->model->bloquearPC_IP($_SESSION['nombre'], 'Acceso no autorizado a gestion de usuarios');
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

        // Si el usuario YA ESTÁ logueado y trata de acceder a la raíz o a /usuarios/login
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
        $this->asegurarAccesoFuncionalidad('grupos', 'No tienes permiso para acceder a esta sección', 'usuarios/grupo');

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

    private function puedeAccederGrupos(): bool
    {
        if (Validador::puedeVer($_SESSION, [1, 2])) {
            return true;
        }

        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                'grupos',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    public function asignar_permisos()
    {
        // Verificación del token CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $this->asegurarAccesoFuncionalidad('grupos', 'No tienes permiso para acceder a esta sección', 'usuarios/asignar_permisos');

        // Obtener datos del formulario
        $id_grupo = intval($_POST['id_grupo']);
        $id_tipoDoc = intval($_POST['id_tipoDoc']);
        if ($this->model->esGrupoSistema($id_grupo)) {
            setAlert('warning', 'El grupo "SIN DOCUMENTOS" es del sistema: no admite tipos de documento.');
            header('Location: ' . base_url() . 'usuarios/grupo');
            exit();
        }
        // Verificar si el permiso ya existe
        $permisoExistente = $this->model->verificarPermisoExistente($id_grupo, $id_tipoDoc);
        if ($permisoExistente['total'] > 0) {
            setAlert('error', "Permiso existente para este grupo y tipo de documento!");
            session_write_close();
            header('location: ' . base_url() . "usuarios/grupo");
            exit();
        } else {
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
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $this->asegurarAccesoFuncionalidad('grupos', 'No tienes permiso para acceder a esta sección', 'usuarios/eliminar_permiso');

        $id_permiso = $_POST['id_permiso'];
        $this->model->eliminarPermiso($id_permiso);
        header("Location: " . base_url() . "usuarios/grupo");
    }

    // Método para reactivar un permiso desactivado
    public function reactivar_permiso()
    {
        // 1. Validar CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        // 2. Validar acceso a la gestión de grupos
        $this->asegurarAccesoFuncionalidad('grupos', 'No tienes permiso para acceder a esta sección', 'usuarios/reactivar_permiso');

        // 3. Procesar la reactivación
        if (isset($_POST['id_permiso'])) {
            $id_permiso = intval($_POST['id_permiso']);
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
        $this->ejecutarMigracionesPendientesUsuarios();

        // 1. Límite de la licencia
        $usuariosActuales = intval($this->model->contarUsuariosActivos()['total'] ?? 0);
        $limiteUsuarios = defined('LICENCIA_MAX_USUARIOS') ? intval(LICENCIA_MAX_USUARIOS) : (defined('LIMITE_USUARIOS') ? intval(LIMITE_USUARIOS) : 0);
        if ($limiteUsuarios > 0 && $usuariosActuales >= $limiteUsuarios) {
            $this->rechazarOperacionUsuario('No se puede agregar más usuarios. Se alcanzó el límite de la licencia.');
        }

        // 2. Datos del formulario
        $nombre = htmlspecialchars(trim((string) ($_POST['nombre'] ?? '')));
        $usuario = htmlspecialchars(trim((string) ($_POST['usuario'] ?? '')));
        $email = trim((string) ($_POST['email'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $rol = intval($_POST['id_rol'] ?? 0);
        $grupo = intval($_POST['id_grupo'] ?? 0);

        // 3. Formato
        if ($nombre === '' || $usuario === '' || $clave === '') {
            $this->rechazarOperacionUsuario('Debe completar nombre, usuario y contraseña.');
        }
        if (mb_strlen($clave) < 10) {
            $this->rechazarOperacionUsuario('La contraseña debe tener al menos 10 caracteres.');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->rechazarOperacionUsuario('El correo electrónico no es válido.');
        }
        if (esUsuarioReservado($usuario)) {
            $this->rechazarOperacionUsuario('Ese nombre de usuario no está disponible.');
        }

        // 4. Reglas de rol (el rol 1 nunca: lo rechaza puedeAsignarRolYDepartamento)
        if ($rol === 2 && !$this->esAdministradorScantec() && intval($_SESSION['id_rol'] ?? 0) !== 2) {
            $this->rechazarOperacionUsuario('Solo un administrador puede crear usuarios con rol de Administrador.');
        }
        if ($idDepartamento <= 0) {
            $idDepartamento = $this->model->idDepartamentoSinAsignar();
        }
        if (!$this->puedeAsignarRolYDepartamento($rol, $idDepartamento)) {
            $this->rechazarOperacionUsuario('No tienes permiso para crear usuarios con ese rol o departamento.');
        }

        // 5. Grupo (módulo Expedientes)
        if ($grupo <= 0) {
            $grupo = $this->model->idGrupoSinDocumentos();
        }
        if (!$this->model->existeGrupoActivo($grupo)) {
            $this->rechazarOperacionUsuario('Seleccione un grupo válido y activo.');
        }

        // 6. Que no exista
        if (intval($this->model->verificarUsuarioExistente($usuario)['total'] ?? 0) > 0) {
            $this->rechazarOperacionUsuario('Este usuario ya existe.');
        }

        // 7. Guardar
        $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        $insert = $this->model->insertarUsuarios($nombre, $idDepartamento, $usuario, $hash, (string) $rol, (string) $grupo, 'scantec', $email);

        if (!$insert) {
            $this->rechazarOperacionUsuario('No se pudo registrar el usuario. Verifique los datos ingresados.');
        }

        setAlert('success', 'Usuario registrado correctamente.');
        header('Location: ' . base_url() . 'usuarios/listar');
        exit();
    }

    public function registrar_grupo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
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
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $nombre_tipoDoc = htmlspecialchars($_POST['nombre_tipoDoc']);
        $indice_1 = htmlspecialchars($_POST['indice_1']);
        $indice_2 = htmlspecialchars($_POST['indice_2']);
        $indice_3 = htmlspecialchars($_POST['indice_3']);
        $indice_4 = htmlspecialchars($_POST['indice_4']);
        $indice_5 = htmlspecialchars($_POST['indice_5']);
        $indice_6 = htmlspecialchars($_POST['indice_6']);

        $activos = intval($this->model->contarTiposDocumentoActivos());
        $limite = defined('LICENCIA_MAX_TIPODOC') ? intval(LICENCIA_MAX_TIPODOC) : 0;

        if ($limite < 0) {
            setAlert('warning', "Su licencia no incluye la creación de tipos de documento.");
            header("location: " . base_url() . "usuarios/grupo");
            exit();
        }

        if ($limite > 0 && $activos >= $limite) {
            setAlert('warning', "Se alcanzó el límite de {$limite} tipos de documento permitidos por su licencia.");
            header("location: " . base_url() . "usuarios/grupo");
            exit();
        }

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
            'departamentos' => $departamentos,
            'puedeGestionarRolesYGrupos' => $this->puedeGestionarRolesYGrupos(),
        ];

        $this->views->getView($this, "editar", $data);
    }


    public function editar_grupo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $id_grupo = intval($_POST['id_grupo']);
        if ($this->model->esGrupoSistema($id_grupo)) {
            setAlert('warning', 'El grupo "SIN DOCUMENTOS" es del sistema y no se puede modificar.');
            header('Location: ' . base_url() . 'usuarios/grupo');
            exit();
        }
        $descripcion = htmlspecialchars($_POST['descripcion']);

        $update = $this->model->actualizarGrupo($id_grupo, $descripcion);
        if ($update) {
            setAlert('success', 'Grupo actualizado correctamente.');
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function editar_tipoDoc()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $id_tipoDoc = intval($_POST['id_tipoDoc']);
        $nombre_tipoDoc = htmlspecialchars($_POST['nombre_tipoDoc']);
        $indice_1 = htmlspecialchars($_POST['indice_1']);
        $indice_2 = htmlspecialchars($_POST['indice_2']);
        $indice_3 = htmlspecialchars($_POST['indice_3']);
        $indice_4 = htmlspecialchars($_POST['indice_4']);
        $indice_5 = htmlspecialchars($_POST['indice_5']);
        $indice_6 = htmlspecialchars($_POST['indice_6']);

        $update = $this->model->actualizarTipoDoc($id_tipoDoc, $nombre_tipoDoc, $indice_1, $indice_2, $indice_3, $indice_4, $indice_5, $indice_6);
        if ($update) {
            setAlert('success', 'Documento actualizado correctamente.');
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function get_grupo(int $id_grupo)
    {
        $data = $this->model->obtenerGrupo($id_grupo);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function get_tipoDoc(int $id_tipoDoc)
    {
        $data = $this->model->obtenerTipoDoc($id_tipoDoc);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
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
                $nombreRol = (string) ($rol['descripcion'] ?? $nombreRol);
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
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $this->asegurarAccesoGestionUsuarios();

        // 1. El usuario a modificar tiene que existir y estar dentro de mi alcance
        $id = intval($_POST['id'] ?? 0);
        $usuarioActualRaw = $id > 0 ? $this->model->editarUsuarios($id) : [];
        $usuarioActual = isset($usuarioActualRaw[0]) ? $usuarioActualRaw[0] : $usuarioActualRaw;

        if (empty($usuarioActual) || !$this->puedeVerUsuarioObjetivo($usuarioActual)) {
            $this->rechazarOperacionUsuario('Usuario no encontrado o sin permisos para modificarlo.');
        }

        // 2. Datos del formulario
        $nombre = htmlspecialchars(trim((string) ($_POST['nombre'] ?? '')));
        $usuario = htmlspecialchars(trim((string) ($_POST['usuario'] ?? '')));
        $email = trim((string) ($_POST['email'] ?? ''));
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $rol = intval($_POST['id_rol'] ?? 0);
        $grupo = intval($_POST['id_grupo'] ?? 0);
        $estadoUsuario = strtoupper(trim((string) ($_POST['estado_usuario'] ?? 'ACTIVO')));
        $claveNueva = (string) ($_POST['clave'] ?? '');

        $rolActual = intval($usuarioActual['id_rol'] ?? 0);
        $grupoActual = intval($usuarioActual['id_grupo'] ?? 0);
        if ($rol <= 0) {
            $rol = $rolActual;
        }
        if ($grupo <= 0) {
            $grupo = $grupoActual;
        }

        // 3. Formato
        if ($nombre === '' || $usuario === '') {
            $this->rechazarOperacionUsuario('El nombre y el usuario son obligatorios.', $id);
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->rechazarOperacionUsuario('El correo electrónico no es válido.', $id);
        }
        if (!in_array($estadoUsuario, ['ACTIVO', 'INACTIVO', 'BLOQUEADO', 'PENDIENTE'], true)) {
            $this->rechazarOperacionUsuario('Estado de usuario no válido.', $id);
        }
        if ($claveNueva !== '' && mb_strlen($claveNueva) < 10) {
            $this->rechazarOperacionUsuario('La contraseña debe tener al menos 10 caracteres.', $id);
        }

        // 4. Nombres reservados (si ya se llamaba así, se respeta)
        $usuarioCambia = strcasecmp($usuario, (string) ($usuarioActual['usuario'] ?? '')) !== 0;
        if ($usuarioCambia && esUsuarioReservado($usuario)) {
            $this->rechazarOperacionUsuario('Ese nombre de usuario no está disponible.', $id);
        }

        // 5. Reglas de rol
        if ($rolActual === 1) {
            if (!$this->esAdministradorScantec()) {
                $this->rechazarOperacionUsuario('No tienes permiso para modificar ese usuario.');
            }
            $rol = 1;   // el rol del superadmin no se cambia desde la web
        } else {
            if ($id === intval($_SESSION['id'] ?? 0) && $rol !== $rolActual) {
                $this->rechazarOperacionUsuario('No puedes cambiar tu propio rol.', $id);
            }
            if ($rol === 2 && !$this->esAdministradorScantec() && intval($_SESSION['id_rol'] ?? 0) !== 2) {
                $this->rechazarOperacionUsuario('Solo un administrador puede otorgar el rol de Administrador.', $id);
            }
            if (!$this->puedeAsignarRolYDepartamento($rol, $idDepartamento)) {
                $this->rechazarOperacionUsuario('No tienes permiso para asignar ese rol o departamento.', $id);
            }
        }

        // 6. Grupo (módulo Expedientes)
        if ($grupo !== $grupoActual && !$this->model->existeGrupoActivo($grupo)) {
            $this->rechazarOperacionUsuario('El grupo seleccionado no existe o está inactivo.', $id);
        }

        // 7. Guardar
        $hashNuevaClave = $claveNueva !== '' ? password_hash($claveNueva, PASSWORD_BCRYPT, ['cost' => 12]) : '';
        $actualizar = $this->model->actualizarUsuarios(
            $nombre,
            $idDepartamento,
            $usuario,
            $rol,
            $grupo,
            $email,
            $estadoUsuario,
            $id,
            $hashNuevaClave
        );

        if ($actualizar !== false) {
            setAlert('success', 'Usuario modificado correctamente.');
        } else {
            setAlert('error', 'No se pudo modificar el usuario.');
        }
        header('Location: ' . base_url() . 'usuarios/listar');
        exit();
    }

    // Corta la operación con un aviso. Con id vuelve al formulario de edición; sin id, a la lista.
    private function rechazarOperacionUsuario(string $mensaje, int $id = 0): void
    {
        setAlert('error', $mensaje);
        $destino = $id > 0 ? 'usuarios/editar?id=' . $id : 'usuarios/listar';
        header('Location: ' . base_url() . $destino);
        exit();
    }

    public function eliminar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
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
            setAlert('error', "Token CSRF inválido o expirado.");
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
            setAlert('error', "Token CSRF inválido o expirado.");
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

    public function login()
    {
        // Migraciones únicas: si ya se aplicaron, salen enseguida
        $this->model->asegurarJerarquiaRolesScantec();
        $this->model->asegurarEstructuraSinAcceso();
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if (class_exists('LicenseLoader')) {
            $licenciaEstado = LicenseLoader::verificarEstado();
            if (empty($licenciaEstado['status'])) {
                setAlert('error', (string) ($licenciaEstado['msg'] ?? 'La licencia no es valida o no esta disponible.'));
                header('location: ' . base_url());
                exit();
            }
        }
        if (!isset($_SESSION['login_attempts']))
            $_SESSION['login_attempts'] = 0;

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
        //$fuente_registro = $_POST['fuente_registro'] ?? 'scantec';

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
            setAlert('error', "Tu usuario está inactivo o bloqueado.");
            header('location: ' . base_url());
            exit();
        }

        if (strtoupper(trim((string) ($data['fuente_registro'] ?? ''))) === 'API') {
            $this->model->bloquearPC_IP($usuario, 'Intento de login web con cuenta de API');
            setAlert('error', "Esta cuenta es de uso exclusivo para integraciones y no puede iniciar sesión en el sistema.");
            header('location: ' . base_url());
            exit();
        }
        $fuente_registro = strtoupper(trim((string) ($data['fuente_registro'] ?? ''))) === 'LDAP' ? 'LDAP' : 'scantec';
        $auth_success = false;

        /// =========================================================
        // 1. MODO DIRECTORIO ACTIVO (Si seleccionó LDAP)
        // =========================================================
        if ($fuente_registro === 'LDAP') {
            if (!function_exists('ldap_connect')) {
                error_log('LDAP login: la extension ldap de PHP no esta cargada');
                setAlert('error', 'El inicio de sesión con usuario de dominio no está disponible. Contacte al administrador.');
                header('location: ' . base_url());
                exit();
            }
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

                    // --- NOMBRE DE USUARIO PARA EL AD ---
                    // Si la configuracion LDAP tiene un "formato de usuario" (ldapFormatoUsuario) se usa; si esta vacio (o no es valido),
                    // se mantiene el comportamiento de siempre: primer DC= del Base DN como dominio.
                    $usuario_ad = '';
                    $formato_ad = trim((string) ($configLdap['ldapFormatoUsuario'] ?? ''));
                    if ($formato_ad !== '') {
                        $usuario_ad = (string) ldapNombreBind($formato_ad, $usuario_raw);
                        if ($usuario_ad === '') {
                            error_log('LDAP login: el formato de usuario configurado no es valido; se usa el formato por defecto');
                        }
                    }
                    if ($usuario_ad === '') {
                        $dominio_netbios = 'DOMINIO'; // Valor por defecto
                        if (preg_match('/DC=([^,]+)/i', $configLdap['ldapBaseDn'], $matches)) {
                            $dominio_netbios = strtoupper($matches[1]);
                        }
                        $usuario_ad = $dominio_netbios . "\\" . $usuario;
                    }

                    if (@ldap_bind($ldap_conn, $usuario_ad, $claveIngresada)) {
                        $auth_success = true; // ¡El AD aceptó la clave!
                    } else {
                        // DIAGNOSTICO: motivo real del rechazo. NUNCA se registra la clave.
                        $diag = '';
                        @ldap_get_option($ldap_conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $diag);
                        error_log('LDAP login: bind fallido usuario=' . $usuario_ad . ' servidor=' . $ldap_host . ':' . $ldap_port
                            . ' -> ' . ldap_error($ldap_conn) . ' (' . ldap_errno($ldap_conn) . ')' . (!empty($diag) ? ' | ' . $diag : ''));
                    }
                } else {
                    error_log('LDAP login: ldap_connect devolvio false (servidor=' . $ldap_host . ':' . $ldap_port . ')');
                }
            } else {
                error_log('LDAP login: no hay configuracion LDAP (ldapHost vacio)');
            }
        }
        // =========================================================
        // 2. MODO USUARIO LOCAL
        // =========================================================
        else {
            if (password_verify($claveIngresada, $data['clave'])) {
                $auth_success = true;
            } else {
                // DIAGNOSTICO: si el usuario debia entrar con el AD, el formulario no envio fuente_registro=LDAP
                error_log('Login local fallido: usuario=' . $usuario . ' fuente_registro(usada)=' . $fuente_registro
                    . ' fuente_registro(base)=' . ($data['fuente_registro'] ?? ''));
            }
        }

        // =========================================================
        // 3. RESOLUCIÓN DE ACCESO
        // =========================================================
        if ($auth_success) {

            // --- DETECCIÓN DE SESIÓN DUPLICADA ---
            if ($this->model->verificarSesionActivaDeUsuario($data['id'])) {
                $_SESSION['pending_login'] = [
                    'id' => $data['id'],
                    'nombre' => $data['nombre'],
                    'usuario' => $data['usuario'],
                    'id_rol' => $data['id_rol'],
                    'id_departamento' => $data['id_departamento'] ?? 0,
                    'id_grupo' => $data['id_grupo'] ?? 0,
                    'grupo' => $data['grupo'] ?? '',
                ];
                // Generar token CSRF para el formulario de confirmación
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['csrf_expiration'] = time() + (5 * 60); // 5 minutos para decidir
                header('location: ' . base_url() . 'usuarios/sesion_duplicada');
                exit();
            }

            // --- LOGIN NORMAL (sin sesión duplicada) ---
            $this->completarLogin($data);

        } else {
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 3) {
                $this->model->bloquearUsuarios($usuario);
                $this->model->bloquearPC_IP($usuario, 'Excedió intentos');
            }

            $restantes = 3 - $_SESSION['login_attempts'];
            setAlert('error', "Usuario o contraseña incorrecta. Le quedan $restantes intentos.");
            header('location: ' . base_url());
            exit();
        }
    }

    // =========================================================
    // MÉTODO PRIVADO: Finaliza el inicio de sesión (reutilizable)
    // =========================================================
    private function completarLogin(array $data): void
    {
        session_regenerate_id(true);
        $_SESSION['id'] = $data['id'];
        $_SESSION['nombre'] = $data['nombre'];
        $_SESSION['usuario'] = $data['usuario'];
        $_SESSION['id_rol'] = $data['id_rol'];
        $_SESSION['id_departamento'] = $data['id_departamento'] ?? 0;
        $_SESSION['ACTIVO'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_expiration'] = time() + SESION_TIEMPO_SEGUNDOS;
        $_SESSION['id_grupo'] = $data['id_grupo'] ?? 0;
        $_SESSION['grupo'] = $data['grupo'] ?? '';
        $_SESSION['PERMISOS'] = $this->model->getPermisosByRol($data['id_rol']);
        $_SESSION['login_attempts'] = 0;

        $this->model->registrarVisita($_SESSION['id']);
        $this->model->conteoInicioSesion($_SESSION['id']);

        $retorno = rutaRetornoSegura((string) ($_SESSION['retorno_login'] ?? ''), base_url());   // NUEVO
        unset($_SESSION['retorno_login']);
        if ($retorno !== null) {
            header('Location: ' . $retorno, true, 303);
            exit();
        }

        require_once 'Models/FuncionalidadesModel.php';
        $rutaDestino = FuncionalidadesModel::obtenerRutaRedireccionSegura(
            intval($data['id_rol']),
            intval($data['id_departamento'] ?? 0)
        );
        header('location: ' . base_url() . $rutaDestino);
        exit();
    }

    // =========================================================
    // Muestra la pantalla de confirmación de sesión duplicada
    // =========================================================
    public function sesion_duplicada()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

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
    // Procesa la elección del usuario ante sesión duplicada
    // =========================================================
    public function confirmar_sesion()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();

        // Validar CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido.');
            header('location: ' . base_url());
            exit();
        }

        // Validar que exista un login pendiente
        if (empty($_SESSION['pending_login'])) {
            setAlert('warning', 'No hay un inicio de sesión pendiente.');
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
            setAlert('info', 'Inicio de sesión cancelado.');
            header('location: ' . base_url());
            exit();

        } else {
            setAlert('error', 'Acción no reconocida.');
            header('location: ' . base_url());
            exit();
        }
    }

    // =========================================================
    // Muestra el formulario (Tu archivo cambiar_pass.php)
    // =========================================================
    public function cambiar_pass()
    {
        $data['page_title'] = "Cambiar Contraseña";
        // Cargamos tu vista específica: Views/Usuarios/cambiar_pass.php
        $this->views->getView($this, "cambiar_pass", $data);
    }
    // =========================================================
    //Recibe los datos y actualiza
    // =========================================================
    public function actualizar_password()
    {
        // Validación CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token inválido.");
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
                setAlert('error', "Las contraseñas nuevas no coinciden.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
                exit();
            }
            // B. Verificar contraseña actual en BD
            $dataDB = $this->model->getPassword($idUser);
            // Ajuste por si el modelo devuelve array [0] o plano
            $passDB = (isset($dataDB[0]['clave'])) ? $dataDB[0]['clave'] : ($dataDB['clave'] ?? '');
            if (password_verify($actual, $passDB)) {
                // Generar Hash y Actualizar
                $nuevaHash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);
                $request = $this->model->cambiarContra($nuevaHash, $idUser);
                if ($request) {
                    setAlert('success', "Contraseña actualizada correctamente.");
                    if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2) {
                        header("Location: " . base_url() . "usuarios/listar");
                    }
                    // Si es Usuario normal, NO tiene permiso de ver 'listar', así que va a su perfil o dashboard
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
                setAlert('error', "La contraseña actual es incorrecta.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
            }
        }
        exit();
    }

    public function salir()
    {
        // 1. Recuperar la sesión existente (CRÍTICO: Esto recupera el session_id)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['id'])) {
            $idUser = $_SESSION['id'];
            // Al llamar a estas funciones, el modelo usará session_id() internamente
            // para saber exactamente qué fila cerrar en la BD.
            $this->model->actualizarVisita($idUser);
            // Restamos 1 al contador global del usuario
            $this->model->restarInicioSesion($idUser);
        }
        // 2. Destruir la sesión del servidor
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
            // 1. Restar inicio de sesión (si tu lógica lo requiere)
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
        $manualSeleccionado = trim((string) ($_GET['manual'] ?? ''));

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
        $manualSeleccionado = trim((string) ($_GET['manual'] ?? ''));

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
                'descripcion' => 'Versión general del manual integral del sistema.',
            ],
            'admin_legajos' => [
                'titulo' => 'Manual Administrador Legajos',
                'archivo' => 'Manual Administrador Legajos.pdf',
                'descripcion' => 'Funciones de armado, verificación y administración de legajos.',
            ],
            'admin_sistema' => [
                'titulo' => 'Manual Administrador Sistema',
                'archivo' => 'Manual Administrador Sistema.pdf',
                'descripcion' => 'Configuración general, usuarios, roles, seguridad y mantenimiento.',
            ],
            'operador_legajos' => [
                'titulo' => 'Manual Operador Legajos',
                'archivo' => 'Manual Operador Legajos.pdf',
                'descripcion' => 'Carga operativa y seguimiento diario de legajos.',
            ],
        ];
    }


    /**
     * Función para validar que el archivo tenga la estructura correcta.
     */
    private function validarEstructura($header)
    {
        $estructuraCorrecta = ["nombre", "usuario", "clave", "rol", "grupo", "email"];
        return count(array_intersect($estructuraCorrecta, $header)) === count($estructuraCorrecta);
    }

    /**
     * Función para validar que la contraseña cumpla los requisitos.
     */
    private function validarClave($clave)
    {
        $regex = "/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\-_.])(?=.{7,})/";
        return preg_match($regex, $clave);
    }


    public function importar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token inválido.");
            header("Location: " . base_url() . "usuarios/listar");
            exit();
        }

        $this->asegurarAccesoGestionUsuarios();
        $this->ejecutarMigracionesPendientesUsuarios();
        $idDepartamentoDefecto = $this->esAdministradorGlobal() ? 0 : $this->obtenerIdDepartamentoGestionable();
        $idGrupoDefecto = defined('LDAP_ID_GRUPO_DEFECTO') ? intval(LDAP_ID_GRUPO_DEFECTO) : 1;

        // 2. Verificar que se subió un archivo sin errores
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            if (
                !scantecValidarUpload(
                    $_FILES["file"],
                    ['csv', 'xls', 'xlsx'],
                    ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/octet-stream'],
                    10 * 1024 * 1024
                )
            ) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'El archivo debe ser CSV, XLS o XLSX valido y no superar 10 MB.'];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_name = $_FILES["file"]["name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $usuarios = [];
            $errores = [];
            $usuariosVistosEnArchivo = []; // detecta duplicados DENTRO del mismo archivo
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
                    if ($fila_actual === 1)
                        continue;

                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row[0] ?? ''));
                    $usuario = htmlspecialchars(trim($row[1] ?? ''));
                    $clave = trim($row[2] ?? '');
                    $id_rol = (int) ($row[3] ?? 0);
                    $email = filter_var(trim($row[4] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contraseña del usuario '{$usuario}' es débil.";
                        continue;
                    }

                    if (!$this->puedeAsignarRolYDepartamento($id_rol, $idDepartamentoDefecto)) {
                        $errores[] = "Fila {$fila_actual}: No tiene permiso para asignar el rol indicado al usuario '{$usuario}'.";
                        continue;
                    }

                    $usuarioLower = strtolower($usuario);
                    if (isset($usuariosVistosEnArchivo[$usuarioLower])) {
                        $errores[] = "Fila {$fila_actual}: El usuario '{$usuario}' está duplicado dentro del mismo archivo.";
                        continue;
                    }
                    if (intval($this->model->verificarUsuarioExistente($usuario)['total'] ?? 0) > 0) {
                        $errores[] = "Fila {$fila_actual}: El usuario '{$usuario}' ya existe en el sistema.";
                        continue;
                    }
                    $usuariosVistosEnArchivo[$usuarioLower] = true;

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $fuente_registro, $email];
                }
                fclose($fh);

                // 4. PROCESAR EXCEL (XLS, XLSX)
            } else if ($file_ext === 'xls' || $file_ext === 'xlsx') {
                require_once 'Libraries/vendor/autoload.php';

                $reader = ($file_ext === 'xlsx') ? new \PhpOffice\PhpSpreadsheet\Reader\Xlsx() : new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($file_tmp);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $index => $row) {
                    $fila_actual = $index;
                    if ($fila_actual === 1)
                        continue;

                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row['A'] ?? ''));
                    $usuario = htmlspecialchars(trim($row['B'] ?? ''));
                    $clave = trim($row['C'] ?? '');
                    $id_rol = (int) ($row['D'] ?? 0);
                    $email = filter_var(trim($row['E'] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contraseña del usuario '{$usuario}' es débil.";
                        continue;
                    }

                    if (!$this->puedeAsignarRolYDepartamento($id_rol, $idDepartamentoDefecto)) {
                        $errores[] = "Fila {$fila_actual}: No tiene permiso para asignar el rol indicado al usuario '{$usuario}'.";
                        continue;
                    }

                    $usuarioLower = strtolower($usuario);
                    if (isset($usuariosVistosEnArchivo[$usuarioLower])) {
                        $errores[] = "Fila {$fila_actual}: El usuario '{$usuario}' está duplicado dentro del mismo archivo.";
                        continue;
                    }
                    if (intval($this->model->verificarUsuarioExistente($usuario)['total'] ?? 0) > 0) {
                        $errores[] = "Fila {$fila_actual}: El usuario '{$usuario}' ya existe en el sistema.";
                        continue;
                    }
                    $usuariosVistosEnArchivo[$usuarioLower] = true;

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $fuente_registro, $email];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Formato no soportado. Use CSV, XLS o XLSX.'];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            // 5. VALIDACIÓN FINAL: Si hay un solo error, frenar todo para proteger la BD
            if (count($errores) > 0) {
                $errores_mostrar = array_slice($errores, 0, 5);
                $mensaje_error = "<b>La importación fue cancelada por errores de formato:</b><br><br>" . implode("<br>", $errores_mostrar);
                if (count($errores) > 5)
                    $mensaje_error .= "<br><i>...y " . (count($errores) - 5) . " errores más.</i>";

                $_SESSION['alert'] = ['type' => 'error', 'message' => $mensaje_error];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            // 6. INSERCIÓN SEGURA (Solo llega aquí si el 100% de los usuarios pasaron las validaciones)
            $importados = 0;
            foreach ($usuarios as $user) {
                $activos = intval($this->model->contarUsuariosActivos()['total'] ?? 0);
                $limite = defined('LICENCIA_MAX_USUARIOS') ? intval(LICENCIA_MAX_USUARIOS) : 0;

                if ($limite > 0 && $activos >= $limite) {
                    $_SESSION['alert'] = ['type' => 'warning', 'message' => "Se alcanzó el límite de licencia. Se importaron $importados usuarios correctamente."];
                    break;
                }

                [$nombre, $usuario, $clave, $id_rol, $fuente_registro, $email] = $user;
                $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);

                $insert = $this->model->insertarUsuarios(
                    $nombre,
                    $idDepartamentoDefecto,
                    $usuario,
                    $passwordHash,
                    (string) $id_rol,
                    (string) $idGrupoDefecto,
                    $fuente_registro,
                    $email
                );
                if ($insert) {
                    $importados++;
                }
            }

            if ($importados > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "¡Éxito! Se importaron correctamente $importados usuarios."];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'El archivo se leyó, pero no se importó ningún usuario (Archivo vacío).'];
            }

        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Ocurrió un error al subir el archivo.'];
        }

        header("Location: " . base_url() . "usuarios/listar");
        die();
    }

    public function sincronizarAD()
    {
        ini_set('max_execution_time', 300);

        // 1. Verificar Token CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        $this->ejecutarMigracionesPendientesUsuarios();
        // 2. Obtener configuración
        $id_config = intval($_POST['id']);
        $ldapConfig = $this->model->getLdapConfigById($id_config);

        if (isset($ldapConfig[0]['ldapHost'])) {
            $ldapConfig = $ldapConfig[0];
        }

        if (empty($ldapConfig) || !isset($ldapConfig['ldapHost'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se pudieron leer los datos de la configuración (ID: ' . $id_config . ').'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 3. DESENCRIPTAR CONTRASEÑA
        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($ldapConfig['ldapPass']);
        } else {
            $password_real = $ldapConfig['ldapPass'];
        }

        // 4. Conexión LDAP
        $ldapConn = ldap_connect($ldapConfig['ldapHost'], $ldapConfig['ldapPort']);
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (!$ldapConn || !@ldap_bind($ldapConn, $ldapConfig['ldapUser'], $password_real)) {
            $err = ldap_error($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Fallo de Conexión LDAP: $err"];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 5. Búsqueda
        $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(mail=*))";
        $attributes = ['samaccountname', 'mail', 'displayname', 'givenname', 'sn'];

        $search = ldap_search($ldapConn, $ldapConfig['ldapBaseDn'], $filter, $attributes);

        if (!$search) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error en la búsqueda. Verifique el BaseDN configurado.'];
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

                if (empty($username))
                    continue;

                $existe = $this->model->verificarUsuarioExistente($username)['total'];

                if ($existe == 0) {
                    $activos = intval($this->model->contarUsuariosActivos()['total'] ?? 0);
                    $limite = defined('LICENCIA_MAX_USUARIOS') ? intval(LICENCIA_MAX_USUARIOS) : 0;

                    if ($limite > 0 && $activos >= $limite) {
                        $msg = "Sincronización detenida: Límite de licencia alcanzado. Registrados: $contador_nuevos.";
                        $_SESSION['alert'] = ['type' => 'warning', 'message' => $msg];
                        break;
                    }
                }
                $username = isset($entries[$i]['samaccountname'][0]) ? trim($entries[$i]['samaccountname'][0]) : '';
                $email = isset($entries[$i]['mail'][0]) ? trim($entries[$i]['mail'][0]) : '';

                $nombre = '';
                if (isset($entries[$i]['displayname'][0])) {
                    $nombre = $entries[$i]['displayname'][0];
                } else {
                    $given = isset($entries[$i]['givenname'][0]) ? $entries[$i]['givenname'][0] : '';
                    $sn = isset($entries[$i]['sn'][0]) ? $entries[$i]['sn'][0] : '';
                    $nombre = trim($given . ' ' . $sn);
                }

                if (!empty($username) && !empty($email)) {

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
            $_SESSION['alert'] = ['type' => 'info', 'message' => 'Conexión exitosa, pero no se encontraron usuarios activos con correo en la ruta especificada.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        ldap_unbind($ldapConn);

        if ($contador_nuevos == 0 && $contador_actualizados == 0) {
            $msg = "Sincronización completada. No se encontraron usuarios nuevos ni cambios en los existentes.";
            $tipo = "info";
        } else {
            $msg = "Proceso finalizado correctamente. Registrados: $contador_nuevos | Actualizados: $contador_actualizados.";
            $tipo = "success";
        }

        $_SESSION['alert'] = ['type' => $tipo, 'message' => $msg];

        header("Location: " . base_url() . "configuracion/servidor_AD");
        die();
    }


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

        $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Grupo', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[5], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
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

        // Definir anchos y centrar tabla dinámicamente
        $w = array(15, 70, 45, 50, 35); // Suma: 215mm
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->SetLeftMargin($margin);
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
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

        $pdf->Cell($w[0], 7, utf8_decode('N° Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Descripción Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('N° Tipo Doc'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Tipo Documento', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
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
            'B' => 45, // Descripción de grupo larga
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
