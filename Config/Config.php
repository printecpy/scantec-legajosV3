<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'DB_Config.php';
require_once 'Loader.php';

if (!defined('LICENCIA_CLIENTE')) {
    die("<h1>Error Crítico de Seguridad</h1><p>No se han cargado las credenciales de acceso. Contacte al administrador.</p>");
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$serverName = $_SERVER['SERVER_NAME'];
$serverPort = $_SERVER['SERVER_PORT'];
$port = ($serverPort == '80' || $serverPort == '443') ? '' : ":$serverPort";
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$projectDir = rtrim(str_replace('/index.php', '', $scriptName), '/') . '/';

define('BASE_URL', $protocol . '://' . $serverName . $port . $projectDir);

define('HOST', 'localhost');
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . $projectDir);

const RUTA_BASE = 'C:/xampp/scantec_storage/';
const BACKUP_PATH = 'C:/xampp/backups_scantec/';

const RUTA_EXIFTOOL = 'C:/xampp/htdocs/Tools/exiftool.exe';
define('MAGICK_EXECUTABLE_PATH', 'C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe');
define('TESSERACT_EXECUTABLE_PATH', 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe');
const MEDIA_PATH = ROOT_PATH . 'Assets/img/';
define('MEDIA_URL', BASE_URL . 'Assets/img/');

define('BD', obtenerBaseDatosSeleccionada(BD_DEFAULT));

$dbConnectionConfig = obtenerConexionBaseSeleccionada(BD, BD_DEFAULT);
define('DB_HOST', $dbConnectionConfig['host']);
define('DB_PORT', $dbConnectionConfig['port']);
define('DB_USER', $dbConnectionConfig['user']);
define('PASS', $dbConnectionConfig['password']);

$db = [
    'host' => DB_HOST,
    'username' => DB_USER,
    'password' => PASS,
    'db' => BD,
];

date_default_timezone_set('America/Asuncion');
setlocale(LC_ALL, 'es_ES');
