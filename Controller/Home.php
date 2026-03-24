<?php
class Home extends Controllers
{
    private function obtenerBasesDisponibles(): array
    {
        $basesConfiguradas = function_exists('obtenerConfiguracionesBases')
            ? array_keys(obtenerConfiguracionesBases())
            : [defined('BD') ? BD : 'scantec_basic'];

        if (empty($basesConfiguradas)) {
            return [defined('BD') ? BD : 'scantec_basic'];
        }

        return array_values($basesConfiguradas);
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
        $rutasSinConexion = ['', 'home', 'home/home', 'home/restablecer_pw', 'home/seleccionar_bd'];

        if (in_array($urlActual, $rutasSinConexion, true)) {
            $this->views = new Views();
            return;
        }

        parent::__construct();
    }
    public function home($params)
    {
        $basesDisponibles = $this->obtenerBasesDisponibles();
        $baseActual = $_SESSION['selected_db'] ?? ($_COOKIE['selected_db'] ?? (defined('BD') ? BD : 'scantec_basic'));
        if (!in_array($baseActual, $basesDisponibles, true) && !empty($basesDisponibles)) {
            $baseActual = $basesDisponibles[0];
            $_SESSION['selected_db'] = $baseActual;
            setcookie('selected_db', $baseActual, time() + (365 * 24 * 60 * 60), '/');
        }

        $data = [
            'bases_disponibles' => $basesDisponibles,
            'base_actual' => $baseActual
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

    public function seleccionar_bd()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $db = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_POST['selected_db'] ?? $_GET['db'] ?? ''));
        $basesDisponibles = $this->obtenerBasesDisponibles();

        if ($db !== '' && in_array($db, $basesDisponibles, true)) {
            $_SESSION['selected_db'] = $db;
            setcookie('selected_db', $db, time() + (365 * 24 * 60 * 60), '/');
        }

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
