<?php
function base_url()
{
    return BASE_URL;
}
function encabezado($data = "")
{
    $VistaH = "Views/template/header.php";
    require_once($VistaH);
}
function pie($data = "")
{
    $VistaP = "Views/template/footer.php";
    require_once($VistaP);
}
function report($data = "")
{
    $VistaP = "Views/template/report.php";
    require_once($VistaP);
}
function setAlert($type, $message)
{
    $validAlertTypes = ['success', 'error', 'warning', 'info'];
    if (!in_array($type, $validAlertTypes)) {
        $type = 'info';
    }
    $_SESSION['alert'] = [
        'type' => (string) $type,
        'message' => (string) $message
    ];
}

function verificarSesion()
{
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . base_url() . "/login");
        exit();
    }
}

function limpiar($cadena)
{
    // Eliminar etiquetas peligrosas
    $cadena = strip_tags($cadena);

    // Quitar espacios y barras innecesarias
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);

    // Escapar HTML especial
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');

    return $cadena;
}

function stringEncryption($string)
{
    if (!$string) return false;
    
    // Usamos tu clave maestra existente
    $key = hash('sha256', ENCRYPTION_KEY);
    // Generamos un vector de inicializaciÃ³n basado en tu clave
    $iv = substr(hash('sha256', md5(ENCRYPTION_KEY)), 0, 16);
    
    $output = openssl_encrypt($string, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($output);
}

function stringDecryption($string)
{
    if (!$string) return false;
    
    $key = hash('sha256', ENCRYPTION_KEY);
    $iv = substr(hash('sha256', md5(ENCRYPTION_KEY)), 0, 16);
    
    $output = openssl_decrypt(base64_decode($string), 'AES-256-CBC', $key, 0, $iv);
    return $output;
}
