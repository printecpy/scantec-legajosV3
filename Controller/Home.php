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
            require_once 'Models/FuncionalidadesModel.php';
            header('location: ' . base_url() . FuncionalidadesModel::obtenerRutaRedireccionSegura(
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            ));
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
        $this->views->getView($this, "restablecer_pw", []);
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

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $_POST['token']) || ($_SESSION['csrf_expiration'] ?? 0) < time()) {
            $this->rechazarRegistro('Token de seguridad inválido o expirado.', 'error');
        }

        $usuariosModel = $this->obtenerUsuariosModelPublico();
        $nombre = htmlspecialchars(trim((string) ($_POST['nombre'] ?? '')), ENT_QUOTES, 'UTF-8');
        $usuario = htmlspecialchars(trim((string) ($_POST['usuario'] ?? '')), ENT_QUOTES, 'UTF-8');
        $email = trim((string) ($_POST['email'] ?? ''));
        $clave = (string) ($_POST['clave'] ?? '');
        $claveConfirm = (string) ($_POST['clave_confirm'] ?? '');
        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $idRol = intval($_POST['id_rol'] ?? 0);

        // 1. Formato (validaciones que no consultan la base)
        if ($nombre === '' || $usuario === '' || $email === '' || $clave === '' || $claveConfirm === '' || $idRol <= 0 || $idDepartamento <= 0) {
            $this->rechazarRegistro('Complete todos los campos obligatorios.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->rechazarRegistro('Ingrese un correo electrónico válido.');
        }
        if (esUsuarioReservado($usuario)) {
            $this->rechazarRegistro('Ese nombre de usuario no está disponible.');
        }
        if ($clave !== $claveConfirm) {
            $this->rechazarRegistro('Las contraseñas no coinciden.');
        }
        if (mb_strlen($clave) < 10) {
            $this->rechazarRegistro('La contraseña debe tener al menos 10 caracteres.');
        }
        if (strcasecmp($clave, $usuario) === 0) {
            $this->rechazarRegistro('La contraseña no puede ser igual al usuario.');
        }

        // 2. Reglas que consultan la base
        $rolesRegistrables = array_map('intval', array_column($usuariosModel->selectRolesRegistrables(), 'id_rol'));
        if (!in_array($idRol, $rolesRegistrables, true)) {
            $this->rechazarRegistro('El rol seleccionado no está disponible para registro.');
        }
        if (intval($usuariosModel->verificarUsuarioExistente($usuario)['total'] ?? 0) > 0) {
            $this->rechazarRegistro('Ese usuario ya existe.');
        }
        if (intval($usuariosModel->verificarEmailExistente($email)['total'] ?? 0) > 0) {
            $this->rechazarRegistro('Ese correo ya está registrado.');
        }

        $idGrupo = $usuariosModel->obtenerGrupoRegistroPorDefecto();
        if ($idGrupo <= 0) {
            $this->rechazarRegistro('No hay un grupo disponible para completar el registro. Contacte al administrador.', 'error');
        }

        // 3. Guardar (queda PENDIENTE hasta que un administrador lo apruebe)
        $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        if (!$usuariosModel->insertarUsuarioPendiente($nombre, $idDepartamento, $usuario, $hash, $idRol, $idGrupo, $email)) {
            $this->rechazarRegistro('No se pudo completar el registro. Intente nuevamente.', 'error');
        }

        unset($_SESSION['csrf_token']);   // el token del formulario público no se reutiliza
        setAlert('success', 'Su usuario fue registrado y quedó pendiente de aprobación por un administrador.');
        header('Location: ' . base_url());
        exit();
    }

    // Corta el registro con un aviso y vuelve al formulario.
    private function rechazarRegistro(string $mensaje, string $tipo = 'warning'): void
    {
        setAlert($tipo, $mensaje);
        header('Location: ' . base_url() . 'home/registrarse');
        exit();
    }

    public function seleccionar_bd()
    {
        header("Location: " . base_url());
        exit();
    }

    // Controller/Home.php — mientras no exista flujo por correo
    public function restaurarPass()
    {
        setAlert('info', 'Solicite el restablecimiento de su contraseña al administrador del sistema.');
        header('Location: ' . base_url());
        exit();
    }

    // Flujo definitivo (esquema):
// 1) solicitar: usuario -> se genera $token = bin2hex(random_bytes(32)); se guarda hash('sha256',$token),
//    id_usuario, expira = NOW() + 15 min, usado = 0. Se envía enlace por correo. Respuesta idéntica exista o no.
// 2) confirmar: se valida hash, expiración y usado = 0; se marca usado = 1 y se exige política de contraseñas.


}
