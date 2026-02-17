<?php
function base_url()
{
    return BASE_URL;
}
function encabezado($data = "")
{
    $VistaH = "Views/Template/header.php";
    require_once($VistaH);
}
function pie($data = "")
{
    $VistaP = "Views/Template/footer.php";
    require_once($VistaP);
}
function report($data = "")
{
    $VistaP = "Views/Template/report.php";
    require_once($VistaP);
}
function setAlert($type, $message)
{
    $validAlertTypes = ['success', 'error', 'warning', 'info'];
    if (!in_array($type, $validAlertTypes)) {
        $type = 'info';
    }
    $_SESSION['alert'] = [
        'type' => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
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
