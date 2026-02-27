<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$base_path = realpath(__DIR__ . '/../');

// Incluir PHPMailer
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

    // ==========================================
    // CARGAR VISTA SMTP (Corregido CSRF y Array anidado)
    // ==========================================
    public function servidor_smtp()
    {
        // 1. Generar Token CSRF si no existe (Soluciona los Warnings)
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        // 2. Traer solo la configuración activa
        $smtp_datos = $this->model->getActiveSMTP();

        // 3. Desanidar el array para que la vista detecte el 'ACTIVO'
        if (isset($smtp_datos[0]['host'])) {
            $smtp_datos = $smtp_datos[0];
        }

        $data = ['smtp_datos' => $smtp_datos];
        $this->views->getView($this, "servidor_smtp", $data);
    }

    public function actualizar()
    {
        if ($_POST) {
            if (empty($_POST['nombre']) || empty($_POST['correo'])) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'El nombre y correo son obligatorios.'];
                header("location: " . base_url() . "configuracion/listar");
                die();
            }

            $id = intval($_POST['id']);
            $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
            $telefono = htmlspecialchars(trim($_POST['telefono']), ENT_QUOTES, 'UTF-8');
            $direccion = htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8');
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $total_pag = intval($_POST['total_pag']);

            $actualizar = $this->model->actualizarConfiguracion($nombre, $telefono, $direccion, $correo, $total_pag, $id);

            if ($actualizar) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Datos de la empresa actualizados.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudieron guardar los cambios.'];
            }

            header("location: " . base_url() . "configuracion/listar");
            die();
        }
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function backup()
    {
        $result = $this->configuracionModel->backupDatabase();
        if ($result['status']) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $result['msg']];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function restore()
    {
        if (isset($_FILES['sqlFile']) && $_FILES['sqlFile']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['sqlFile']['tmp_name'];
            $fileName = $_FILES['sqlFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'sql') {
                $result = $this->configuracionModel->RestoreDatabase($fileTmpPath);
                if ($result['status']) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
                } else {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error en BD: ' . $result['msg']];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error: El archivo debe tener extensión .sql'];
            }
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se seleccionó ningún archivo o hubo un error en la subida.'];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function respaldo_archivos()
    {
        try {
            // 1. Verificar Token CSRF (Seguridad)
            if (!isset($_POST['token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
                header("Location: " . base_url() . "expedientes?error=csrf");
                die();
            }

            // 2. Recibir y limpiar la ruta de forma segura
            $ruta_destino = isset($_POST['ruta_destino']) ? trim($_POST['ruta_destino']) : '';

            if (empty($ruta_destino)) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Debe especificar una ruta de destino válida.'];
                header('Location: ' . base_url() . 'configuracion/mantenimiento');
                exit();
            }

            // 3. Ejecutar el respaldo
            $resultado = $this->configuracionModel->ejecutarRespaldo($ruta_destino);

            if ($resultado['status']) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => $resultado['msg']];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: ' . $resultado['msg']];
            }

        } catch (Throwable $e) {
            // ESTO EVITA EL ERROR 500. Atrapa el error fatal y lo muestra en la alerta.
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error Crítico (500): ' . $e->getMessage() . ' en la línea ' . $e->getLine()];
        }

        header('Location: ' . base_url() . 'configuracion/mantenimiento');
        exit;
    }

    // ==========================================
    // ENVÍO PARA OTRAS PARTES DEL SISTEMA
    // ==========================================
    public function sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje)
    {
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host']))
            $smtpConfig = $smtpConfig[0];

        if (empty($smtpConfig))
            return false;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];

            // Desencriptar contraseña de la BD
            if (function_exists('stringDecryption')) {
                $mail->Password = stringDecryption($smtpConfig['password']);
            } else {
                $mail->Password = $smtpConfig['password'];
            }

            if ($smtpConfig['smtpsecure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = $smtpConfig['port'];

            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';
            // Forzamos username como remitente para evitar bloqueos
            $mail->setFrom($smtpConfig['username'], $fromName);

            if (is_array($destinatarios)) {
                foreach ($destinatarios as $email => $nombre) {
                    $mail->addAddress($email, $nombre);
                }
            } else {
                $mail->addAddress($destinatarios);
            }
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            if ($filePath && file_exists($filePath)) {
                $mail->addAttachment($filePath);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // 1. GUARDAR CONFIGURACIÓN SMTP
    // ==========================================
    public function guardarServCorreo()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // Recuperar config actual para no borrar la clave
        $configActual = $this->model->getActiveSMTP();
        if (isset($configActual[0]['host']))
            $configActual = $configActual[0];

        // LOGICA DE CONTRASEÑA: Si viene vacía, usamos la que ya estaba guardada.
        if (empty($password_raw) && !empty($configActual['password'])) {
            $password = $configActual['password'];
        } else {
            $password = function_exists('stringEncryption') ? stringEncryption($password_raw) : $password_raw;
        }

        $remitente = !empty($_POST['remitente']) ? trim($_POST['remitente']) : $username;
        $nombre_remitente = !empty($_POST['nombre_remitente']) ? trim($_POST['nombre_remitente']) : 'SCANTEC Notificaciones';

        $request = $this->model->insertarServSMTP($host, $username, $password, $smtpsecure, $port, $remitente, $nombre_remitente);

        if ($request) {
            unset($_SESSION['smtp_temp']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Configuración guardada y activada correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar los datos en la base de datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 2. PROBAR CONEXIÓN SMTP (Inteligente con la clave)
    // ==========================================
    public function probar_smtp()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // --- LÓGICA INTELIGENTE DE CONTRASEÑA PARA EL TEST ---
        if (empty($password_raw)) {
            // Si dejó el campo vacío, buscamos la contraseña de la BD y la DESENCRIPTAMOS
            $configActual = $this->model->getActiveSMTP();
            if (isset($configActual[0]['host'])) {
                $configActual = $configActual[0];
            }

            if (!empty($configActual['password'])) {
                $password = function_exists('stringDecryption') ? stringDecryption($configActual['password']) : $configActual['password'];
            } else {
                $password = '';
            }
        } else {
            // Si el usuario escribió algo, asumimos que es su contraseña real y la usamos tal cual
            $password = $password_raw;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password; // Usamos la contraseña real o la desencriptada

            if ($smtpsecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpsecure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $port;
            $mail->Timeout = 10;

            // Tolerancia de certificados para evitar problemas de conexión locales
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Conexión Exitosa! El servidor aceptó las credenciales.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Conexión fallida. Verifique los datos o su firewall.'];
            }

        } catch (Exception $e) {
            $errorMsg = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de conexión: ' . $errorMsg];
        }

        $_SESSION['smtp_temp'] = $_POST;
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }
    public function desactivar_servicio_smtp()
    {
        $this->model->desactivarSMTP();
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'El servicio de correo ha sido desactivado.'];
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 4. TEST DE ENVÍO
    // ==========================================
    public function enviarCorreo()
    {
        // 1. Validar CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        // 2. Obtener datos
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host'])) {
            $smtpConfig = $smtpConfig[0];
        }

        if (empty($smtpConfig) || !isset($smtpConfig['host'])) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'No hay configuración SMTP activa.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $destinatario = trim($_POST['destinatario']);
        $asunto = trim($_POST['asunto']);
        $mensaje = trim($_POST['mensaje']);

        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($smtpConfig['password']);
        } else {
            $password_real = $smtpConfig['password'];
        }

        try {
            $mail = new PHPMailer(true);

            // Silenciamos el debug para que no imprima texto en la pantalla
            $mail->SMTPDebug = 0;
            $mail->setLanguage('es', '../vendor/phpmailer/phpmailer/language/');

            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $password_real;

            if (strtolower($smtpConfig['smtpsecure']) == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else if (strtolower($smtpConfig['smtpsecure']) == 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $smtpConfig['port'];
            $mail->Timeout = 15;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Blindaje de remitente: Usamos el username para evitar bloqueos del servidor
            $fromEmail = $smtpConfig['username'];
            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            $mail->send();

            $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Correo enviado correctamente a ' . $destinatario . '!'];

        } catch (Exception $e) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    /*       // ==========================================
      // 4. TEST DE ENVÍO REAL (DIAGNÓSTICO EN VIVO)
      // ==========================================
      public function enviarCorreo()
      {
          // 1. Validar CSRF
          if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
              die("Error de seguridad CSRF.");
          }

          // 2. Obtener datos
          $smtpConfig = $this->model->getActiveSMTP();
          if (isset($smtpConfig[0]['host'])) {
              $smtpConfig = $smtpConfig[0];
          }

          if (empty($smtpConfig) || !isset($smtpConfig['host'])) {
              die("No hay configuración SMTP activa en la base de datos.");
          }

          $destinatario = trim($_POST['destinatario']);
          $asunto = trim($_POST['asunto']);
          $mensaje = trim($_POST['mensaje']);

          if (function_exists('stringDecryption')) {
              $password_real = stringDecryption($smtpConfig['password']);
          } else {
              $password_real = $smtpConfig['password'];
          }

          // --- PANTALLA NEGRA DE DIAGNÓSTICO EN VIVO ---
          echo "<div style='background: #1e1e1e; color: #0f0; padding: 20px; font-family: monospace; height: 100vh; overflow-y: scroll;'>";
          echo "<h2 style='color: white;'>Iniciando comunicación con el servidor SMTP...</h2>";
          echo "<p>Host: {$smtpConfig['host']} | Puerto: {$smtpConfig['port']} | Seguridad: {$smtpConfig['smtpsecure']}</p><hr>";

          // Le damos permiso a PHP de tardar más sin tirar el Error 500
          set_time_limit(300);

          try {
              $mail = new PHPMailer(true);

              // DEBUG DIRECTO: Imprime en pantalla sin ocultar nada
              $mail->SMTPDebug = SMTP::DEBUG_SERVER;
              $mail->Debugoutput = 'html'; 

              $mail->isSMTP();
              $mail->Host = $smtpConfig['host'];
              $mail->SMTPAuth = true;
              $mail->Username = $smtpConfig['username'];
              $mail->Password = $password_real;

              if (strtolower($smtpConfig['smtpsecure']) == 'ssl') {
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
              } else if (strtolower($smtpConfig['smtpsecure']) == 'tls') {
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
              } else {
                  $mail->SMTPSecure = '';
                  $mail->SMTPAutoTLS = false;
              }

              $mail->Port = $smtpConfig['port'];

              // Tiempo límite corto para que no espere eternamente
              $mail->Timeout = 15;

              $mail->SMTPOptions = array(
                  'ssl' => array(
                      'verify_peer' => false,
                      'verify_peer_name' => false,
                      'allow_self_signed' => true
                  )
              );

              // Remitente (Forzado a ser el usuario logueado para evitar bloqueos)
              $fromEmail = $smtpConfig['username'];
              $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';

              $mail->setFrom($fromEmail, $fromName);
              $mail->addAddress($destinatario);

              $mail->isHTML(true);
              $mail->CharSet = 'UTF-8';
              $mail->Subject = $asunto;
              $mail->Body = $mensaje;

              $mail->send();

              echo "<br><hr><h2 style='color: yellow;'>¡ÉXITO! EL CORREO SALIÓ CORRECTAMENTE.</h2>";

          } catch (Exception $e) {
              echo "<br><hr><h2 style='color: red;'>FALLO AL ENVIAR</h2>";
              echo "<p style='color: red;'>Error de PHPMailer: " . $mail->ErrorInfo . "</p>";
          }

          echo "</div>";
          die(); // Congela la pantalla para que podamos leer el resultado
      } */

    // ==========================================
    // GUARDAR LDAP
    // ==========================================
    public function probar_conexionAD()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'CSRF inválido.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapHost = filter_input(INPUT_POST, 'ldapHost', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPort = filter_input(INPUT_POST, 'ldapPort', FILTER_VALIDATE_INT);
        $ldapUser = filter_input(INPUT_POST, 'ldapUser', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPass = $_POST['ldapPass'];
        $ldapBaseDn = $_POST['ldapBaseDn'];

        $_SESSION['ldap_data'] = [
            'ldapHost' => $ldapHost,
            'ldapPort' => $ldapPort,
            'ldapUser' => $ldapUser,
            'ldapPass' => $ldapPass,
            'ldapBaseDn' => $ldapBaseDn
        ];
        if (!$ldapHost || !$ldapPort || !$ldapUser || !$ldapPass || !$ldapBaseDn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo conectar al servidor LDAP.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldapConn, $ldapUser, $ldapPass)) {
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Conexión exitosa.'];
        } else {
            $errorMsg = ldap_error($ldapConn);
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Fallo al conectar: ' . $errorMsg];
        }
        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }

    public function saveLDAP_server()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        $ldapHost = filter_var(trim($_POST['ldapHost']), FILTER_SANITIZE_URL);
        $ldapPort = filter_var($_POST['ldapPort'], FILTER_VALIDATE_INT);
        $ldapBaseDn = trim($_POST['ldapBaseDn']);
        $ldapUser = trim($_POST['ldapUser']);
        $ldapPass = $_POST['ldapPass'];

        if (empty($ldapHost) || empty($ldapPort) || empty($ldapBaseDn) || empty($ldapUser) || empty($ldapPass)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        if (!$ldapPort || $ldapPort <= 0 || $ldapPort > 65535) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Puerto inválido (1-65535).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        date_default_timezone_set('America/Asuncion');
        $fecha_registro = date('Y-m-d H:i:s');

        $guardarLDAPserver = $this->model->insertarServLDAP($ldapHost, $ldapPort, $ldapBaseDn, $ldapUser, $ldapPass, $fecha_registro);

        if ($guardarLDAPserver) {
            unset($_SESSION['ldap_data']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Servidor LDAP registrado y encriptado correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar en la Base de Datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }
}