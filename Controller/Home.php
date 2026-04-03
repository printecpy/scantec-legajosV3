<?php
class Home extends Controllers
{
    private function obtenerUsuariosModelPublico(): UsuariosModel
    {
        if (!class_exists('UsuariosModel')) {
            require_once 'Models/UsuariosModel.php';
        }

        return new UsuariosModel();
    }

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['ACTIVO']) && $_SESSION['ACTIVO'] === true) {
            if (isset($_SESSION['id_rol']) && ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4)) {
                header('location: ' . base_url() . 'expedientes/indice_busqueda');
            } else {
                header('location: ' . base_url() . 'dashboard/listar');
            }
            exit();
        }

        $urlActual = strtolower(trim((string) ($_GET['url'] ?? ''), '/'));
        $rutasSinConexion = ['', 'home', 'home/home', 'home/restablecer_pw', 'home/seleccionar_bd', 'home/registrarse', 'home/guardar_registro'];

        if (in_array($urlActual, $rutasSinConexion, true)) {
            $this->views = new Views();
            return;
        }

        parent::__construct();
    }
    public function home($params)
    {
        $licenciaEstado = class_exists('LicenseLoader')
            ? LicenseLoader::verificarEstado()
            : ['status' => true, 'msg' => ''];

        $data = [
            'base_actual' => defined('BD') ? BD : BD_DEFAULT,
            'licencia_estado' => $licenciaEstado
        ];
        $this->views->getView($this, "home", $data);
    }
    public function restablecer_pw()
    {
        date_default_timezone_set('America/Asuncion');
        $expirationTime = time() + (60 * 3); // 3 minutos ahora
        $_SESSION['csrf_expiration'] = $expirationTime;

        // Genera el token CSRF
        $csrf_token = bin2hex(random_bytes(32)); // Generar token CSRF único
        $_SESSION['csrf_token'] = $csrf_token; // Almacenar en la sesión del usuario

        // Pasa el token a la vista
        $data['csrf_token'] = $csrf_token;
        $data['csrf_expiration'] = $expirationTime;
        // Carga la vista
        $this->views->getView($this, "restablecer_pw", $data);
    }

    public function registrarse()
    {
        $usuariosModel = $this->obtenerUsuariosModelPublico();
        $roles = $usuariosModel->selectRolesRegistrables();
        $departamentos = $usuariosModel->selectDepartamentos();

        if (empty($roles) || empty($departamentos)) {
            setAlert('warning', 'No hay datos disponibles para completar el registro en este momento.');
            header("Location: " . base_url());
            exit();
        }

        $expirationTime = time() + (60 * 10);
        $_SESSION['csrf_expiration'] = $expirationTime;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $data = [
            'roles' => $roles,
            'departamentos' => $departamentos,
            'csrf_token' => $_SESSION['csrf_token'],
            'csrf_expiration' => $expirationTime
        ];

        $this->views->getView($this, "home/registrarse", $data);
    }

    public function guardar_registro()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || ($_SESSION['csrf_expiration'] ?? 0) < time()) {
            setAlert('error', 'Token de seguridad invalido o expirado.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        $usuariosModel = $this->obtenerUsuariosModelPublico();
        $nombre = htmlspecialchars(trim((string)($_POST['nombre'] ?? '')), ENT_QUOTES, 'UTF-8');
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $usuario = htmlspecialchars(trim((string)($_POST['usuario'] ?? '')), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim((string)($_POST['email'] ?? '')), ENT_QUOTES, 'UTF-8');
        $clave = (string)($_POST['clave'] ?? '');
        $claveConfirm = (string)($_POST['clave_confirm'] ?? '');
        $idRol = intval($_POST['id_rol'] ?? 0);

        if ($nombre === '' || $usuario === '' || $email === '' || $clave === '' || $claveConfirm === '' || $idRol <= 0 || $idDepartamento <= 0) {
            setAlert('warning', 'Complete todos los campos obligatorios.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setAlert('warning', 'Ingrese un correo electronico valido.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        if ($clave !== $claveConfirm) {
            setAlert('warning', 'Las contrasenas no coinciden.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        $rolesRegistrables = array_column($usuariosModel->selectRolesRegistrables(), 'id_rol');
        if (!in_array($idRol, array_map('intval', $rolesRegistrables), true)) {
            setAlert('warning', 'El rol seleccionado no esta disponible para registro.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        if (intval($usuariosModel->verificarUsuarioExistente($usuario)['total'] ?? 0) > 0) {
            setAlert('warning', 'Ese usuario ya existe.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        if (intval($usuariosModel->verificarEmailExistente($email)['total'] ?? 0) > 0) {
            setAlert('warning', 'Ese correo ya esta registrado.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        $idGrupo = $usuariosModel->obtenerGrupoRegistroPorDefecto();
        if ($idGrupo <= 0) {
            setAlert('error', 'No hay un grupo disponible para completar el registro. Contacte al administrador.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        $registro = $usuariosModel->insertarUsuarioPendiente($nombre, $idDepartamento, $usuario, $hash, $idRol, $idGrupo, $email);

        if (!$registro) {
            setAlert('error', 'No se pudo completar el registro. Intente nuevamente.');
            header("Location: " . base_url() . "home/registrarse");
            exit();
        }

        setAlert('success', 'Su usuario fue registrado y quedo pendiente de aprobacion por un administrador.');
        header("Location: " . base_url());
        exit();
    }

    public function seleccionar_bd()
    {
        header("Location: " . base_url());
        exit();
    }

    public function restaurarPass()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Validación CSRF
        if (!isset($_POST['token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            setAlert('error', "Token de seguridad inválido o expirado. Por favor, reintente.");
            header("Location: " . base_url() . "home/restablecer_pw");
            exit();
        }

        // 2. Limpieza de entradas
        $nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
        $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');

        // 3. Hashear nueva contraseña
        $nueva = password_hash($_POST['nueva'], PASSWORD_BCRYPT, ['cost' => 12]);

        // 4. Verificación y Proceso
        $data = $this->model->consultarPW($nombre, $usuario);

        if (!empty($data)) {
            $cambio = $this->model->restaurar_pw($nueva, $nombre, $usuario);
            if ($cambio == 1) {
                setAlert('success', "Contraseña restablecida con éxito. Ya puede ingresar al sistema.");
                header("Location: " . base_url()); // Redirige al Login tras el éxito
                exit();
            }
        }

        // 5. Caso de error genérico (Usuario no encontrado o fallo en DB)
        setAlert('error', "Los datos ingresados no coinciden con nuestros registros.");
        header("Location: " . base_url() . "home/restablecer_pw");
        exit();
    }

}
