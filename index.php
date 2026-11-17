<?php
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
ob_start();
date_default_timezone_set('America/Asuncion');

// Tiempo de vida de sesion / tokens, en minutos. Ajustable por instalacion con SCANTEC_SESION_MINUTOS en el .env.
// Se lee aqui, ANTES de session_start(), porque Config/Config.php carga el .env recien despues.
if (!defined('SESION_TIEMPO_MINUTOS')) {
    $minutosSesion = 120;                                   // valor por defecto
    $archivoEnv = __DIR__ . DIRECTORY_SEPARATOR . '.env';
    if (is_readable($archivoEnv)) {
        foreach (file($archivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            if (preg_match('/^\s*SCANTEC_SESION_MINUTOS\s*=\s*["\']?(\d{1,4})["\']?\s*$/', $linea, $m)) {
                $minutosSesion = (int) $m[1];               // si la clave se repite, vale la ultima (igual que el cargador del .env)
            }
        }
    }
    define('SESION_TIEMPO_MINUTOS', max(5, min(1440, $minutosSesion)));   // limites: 5 minutos a 24 horas
}
if (!defined('SESION_TIEMPO_SEGUNDOS')) {
    define('SESION_TIEMPO_SEGUNDOS', SESION_TIEMPO_MINUTOS * 60);
}

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
if (isset($arrUrl[0]) && strtolower($arrUrl[0]) == 'api' && isset($arrUrl[1]) && strtolower($arrUrl[1]) == 'v1') {
    define('IS_API', true);
    $controller = isset($arrUrl[2]) ? $arrUrl[2] : "";
    $methop = isset($arrUrl[3]) ? $arrUrl[3] : "";
    $paramIndex = 4;
} else {
    define('IS_API', false);
    $controller = isset($arrUrl[0]) ? $arrUrl[0] : "Home";
    $methop = isset($arrUrl[1]) ? $arrUrl[1] : "home";
    $paramIndex = 2;
}
// 3. Procesamiento de Parámetros
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