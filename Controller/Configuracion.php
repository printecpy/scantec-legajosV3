<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$base_path = realpath(__DIR__ . '/../');

// Incluir PHPMailer con rutas absolutas
//require_once $base_path . '/Libraries/PHPMailer/PHPMailerAutoload.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/Exception.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/PHPMailer.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/SMTP.php';
class Configuracion extends Controllers
{
    private $configuracionModel, $db;
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
        $this->configuracionModel = new ConfiguracionModel();
        $this->db = new Mysql();
    }
    public function listar()
    {
        $data = $this->model->selectConfiguracion();
        $this->views->getView($this, "listar", $data);
    }

    public function mantenimiento()
    {
        $data = $this->model->selectConfiguracion();
        $this->views->getView($this, "mantenimiento", $data);
    }

    public function servidor_AD()
    {
        $LDAP_datos = $this->model->selectLDAP_datos();
        $data = ['LDAP_datos' => $LDAP_datos];
        $this->views->getView($this, "servidor_AD", $data);
    }

    public function servidor_smtp()
    {
        $smtp_datos = $this->model->selectSMTP_datos();
        $data = ['smtp_datos' => $smtp_datos];
        $this->views->getView($this, "servidor_smtp", $data);
    }

    public function actualizar()
    {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $correo = $_POST['correo'];
        $total_pag = $_POST['total_pag'];
        $actualizar = $this->model->actualizarConfiguracion($nombre, $telefono, $direccion, $correo, $total_pag, $id);
        if ($actualizar) {
            header("location: " . base_url() . "configuracion/listar");
        }
        die();
    }

    public function backup()
    {
        $result = $this->configuracionModel->backupDatabase();
        if ($result) {
            $_SESSION['msg'] = $result;
            // setAlert('success', $_SESSION['msg']);
        } else {
            $_SESSION['msg'] = "Error al realizar el respaldo.";
        }
        header("location: " .  base_url() . "configuracion/mantenimiento");
    }

    public function restore()
    {
        $result = $this->configuracionModel->RestoreDatabase($backup_file);
        if ($result) {
            $_SESSION['msg'] = $result;
            //etAlert('success', $_SESSION['msg']);
        } else {
            //setAlert('error', $_SESSION['msg']);
            $_SESSION['msg'] = "Error al realizar el respaldo.";
        }
        header("location: " .  base_url() . "configuracion/mantenimiento");
    }

    public function respaldo_archivos()
    {
        if ($this->configuracionModel->ejecutarRespaldo()) {
            setAlert('success', 'El respaldo se ha iniciado en segundo plano.');
        } else {
            setAlert('error', 'Hubo un problema al ejecutar el respaldo.');
        }

        header('Location: ' . base_url() . 'configuracion/mantenimiento');
        exit;
    }

    public function sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje)
    {
        // Obtener configuración SMTP de la base de datos
        $smtpConfig = $this->model->selectSMTP_datos();
        if (!$smtpConfig) {
            echo 'Error: No se pudo obtener la configuración del servidor de correo.';
            return false;
        }
        try {
            $mail = new PHPMailer(true); // Excepción habilitada

            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $smtpConfig['password'];
            $mail->SMTPSecure = $smtpConfig['smtpsecure'];  // 'ssl' o 'tls'
            $mail->Port = $smtpConfig['port'];  // 465 o 587
            $mail->setFrom('scantec@printec.com.py', 'SCANTEC');
            //$mail->addAddress($destinatario, $nombreDestinatario);
            // Recorrer la lista de destinatarios y agregarlos
            if (is_array($destinatarios)) {
                foreach ($destinatarios as $email => $nombre) {
                    $mail->addAddress($email, $nombre);
                }
            } else {
                $mail->addAddress($destinatarios); // Si es una sola dirección
            }
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->isHTML(true);

            // Adjuntar archivo si existe
            if ($filePath && file_exists($filePath)) {
                $mail->addAttachment($filePath);
            }

            // Enviar correo
            $mail->send();
            echo 'Correo enviado exitosamente.';
            return true;
        } catch (Exception $e) {
            echo 'Error al enviar correo: ' . $mail->ErrorInfo;
            return false;
        }
    }

    public function enviarCorreo()
    {
        if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
            header("Location: " . base_url() . "?error=csrf");
            die();
        }
        $destinatario = htmlspecialchars($_POST['destinatario']);
        $asunto = htmlspecialchars($_POST['asunto']);
        $mensaje = htmlspecialchars($_POST['mensaje']);
        // Crear instancia de PHPMailer
        $mail = new PHPMailer(true);
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.printec.com.py';   // Servidor SMTP
        $mail->SMTPAuth = true;               // Activar autenticación SMTP
        $mail->Username = 'aldo.silva@printec.com.py'; // Usuario SMTP
        $mail->Password = '(gvM(y*AC)m4';         // Contraseña SMTP
        $mail->SMTPSecure = 'TLS';            // Tipo de seguridad (SSL o TLS)
        $mail->Port = 485;                    // Puerto SMTP (465 para SSL, 587 para TLS)

        // Configuración del correo
        $mail->setFrom('scantec@printec.com.py', 'SCANTEC');
        $mail->addAddress($destinatario);     // Destinatario
        $mail->Subject = $asunto;             // Asunto del correo
        $mail->Body = $mensaje;               // Cuerpo del mensaje
        $mail->isHTML(true);                  // Habilitar HTML

        // Verificar si el correo se envía correctamente
        /* if ($mail->send()) {
            echo 'Correo enviado exitosamente.';
        } else {
            echo 'Error al enviar correo: ' . $mail->ErrorInfo;
        } */
    }

    public function guardarServCorreo()
    {
        if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
            header("Location: " . base_url() . "?error=csrf");
            die();
        }
        $host = htmlspecialchars($_POST['host']);
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $smtpsecure = htmlspecialchars($_POST['smtpsecure']);
        $port = htmlspecialchars($_POST['port']);

        $guardarServCorreo = $this->model->insertarServSMTP($host, $username, $password, $smtpsecure, $port);
        if ($guardarServCorreo) {
            header("location: " . base_url() . "configuracion/servidor_smtp");
        }
        die();
    }

    public function probar_conexionAD()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            setAlert('error', 'CSRF inválido.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        // Sanitizar y validar datos de entrada
        $ldapHost = filter_input(INPUT_POST, 'ldapHost', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPort = filter_input(INPUT_POST, 'ldapPort', FILTER_VALIDATE_INT);
        $ldapUser = filter_input(INPUT_POST, 'ldapUser', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPass = $_POST['ldapPass']; // No se sanitiza para evitar alteraciones
        $ldapBaseDn = $_POST['ldapBaseDn'];
        // Guardar valores en sesión para persistencia tras la redirección
        $_SESSION['ldap_data'] = [
            'ldapHost' => $ldapHost,
            'ldapPort' => $ldapPort,
            'ldapUser' => $ldapUser,
            'ldapPass' => $ldapPass,
            'ldapBaseDn' => $ldapBaseDn
        ];
        if (!$ldapHost || !$ldapPort || !$ldapUser || !$ldapPass || !$ldapBaseDn) {
            setAlert('error', 'Todos los campos son obligatorios.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        // Intentar conexión LDAP
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            setAlert('error', 'No se pudo conectar al servidor LDAP.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        // Intentar autenticación
        if (@ldap_bind($ldapConn, $ldapUser, $ldapPass)) {
            ldap_unbind($ldapConn);
            setAlert('success', 'Conexión exitosa.');
        } else {
            ldap_unbind($ldapConn);
            setAlert('error', 'Autenticación fallida.');
        }
        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }


    public function saveLDAP_server()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            setAlert('error', 'CSRF inválido.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        // Obtener y limpiar datos
        $ldapHost = filter_var(trim($_POST['ldapHost']), FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPort = filter_var($_POST['ldapPort'], FILTER_VALIDATE_INT);
        $ldapBaseDn = filter_var(trim($_POST['ldapBaseDn']), FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapUser = filter_var(trim($_POST['ldapUser']), FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPass = $_POST['ldapPass']; // No sanitizar para evitar alteraciones
        // Validar que los campos no estén vacíos
        if (empty($ldapHost) || empty($ldapPort) || empty($ldapBaseDn) || empty($ldapUser) || empty($ldapPass)) {
            setAlert('error', 'Todos los campos son obligatorios.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        // Validar que el puerto sea un número válido
        if (!$ldapPort || $ldapPort <= 0 || $ldapPort > 65535) {
            setAlert('error', 'El puerto LDAP debe ser un número válido entre 1 y 65535.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        // Guardar en la base de datos
        date_default_timezone_set('America/Asuncion');
        $fecha_registro = date('Y-m-d H:i:s');
        $guardarLDAPserver = $this->model->insertarServLDAP($ldapHost, $ldapPort, $ldapBaseDn, $ldapUser, $ldapPass, $fecha_registro);

        if ($guardarLDAPserver) {
            unset($_SESSION['ldap_data']);
            header("Location: " . base_url() . "configuracion/servidor_AD?success=conexion_registrada");
        } else {
            setAlert('error', 'Error al guardar la configuración LDAP.');
            header("Location: " . base_url() . "configuracion/servidor_AD");
        }
        exit();
    }
}
