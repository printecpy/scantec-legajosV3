<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Asuncion');
require_once("Config/Config.php");
require_once("Helpers/Helpers.php");

// 1. Obtener y sanear la URL
$url = isset($_GET['url']) ? filter_var($_GET['url'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : "Home/home";
$arrUrl = explode("/", $url);

// Variables por defecto
$controller = "";
$methop = "";
$params = "";

// 2. Lógica de Detección de API vs WEB
// Verificamos si la ruta empieza con 'api' y 'v1'
if (isset($arrUrl[0]) && strtolower($arrUrl[0]) == 'api' && isset($arrUrl[1]) && strtolower($arrUrl[1]) == 'v1') {
    // --- ES UNA PETICIÓN API ---
    // La estructura es: api/v1/Controlador/Metodo/Parametros
    // Definimos una constante para saber que estamos en modo API (útil para Load.php)
    define('IS_API', true);

    // El controlador está en la posición 2 (ej: AuthController)
    $controller = isset($arrUrl[2]) ? $arrUrl[2] : "";

    // El método está en la posición 3 (ej: login)
    $methop = isset($arrUrl[3]) ? $arrUrl[3] : "";

    // Los parámetros empiezan desde la posición 4
    $paramIndex = 4;
} else {

    // --- ES UNA PETICIÓN WEB NORMAL ---
    // La estructura es: Controlador/Metodo/Parametros

    define('IS_API', false); // Modo Web

    $controller = isset($arrUrl[0]) ? $arrUrl[0] : "Home";
    $methop = isset($arrUrl[1]) ? $arrUrl[1] : "home";

    // Los parámetros empiezan desde la posición 2
    $paramIndex = 2;
}

// 3. Procesamiento de Parámetros (Lógica reutilizada)
if (isset($arrUrl[$paramIndex])) {
    if ($arrUrl[$paramIndex] != "") {
        for ($i = $paramIndex; $i < count($arrUrl); $i++) {
            $params .= $arrUrl[$i] . ',';
        }
        $params = trim($params, ',');
    }
}

// 4. Carga del Autoload y Load
require_once("Libraries/Core/Autoload.php");
require_once("Libraries/Core/Load.php");
