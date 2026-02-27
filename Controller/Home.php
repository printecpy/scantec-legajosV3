<?php
class Home extends Controllers
{
    public function __construct()
    {
        session_start();
        if (!empty($_SESSION['activo'])) {
            header("location: " . base_url() . "Admin/Listar");
        }
        parent::__construct();
    }
    public function home($params)
    {
        $this->views->getView($this, "home");
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