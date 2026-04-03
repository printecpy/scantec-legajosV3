<?php
/**
 * FORZAR ENTORNO MANUALMENTE
 *
 * Use solo si quiere obligar al sistema a trabajar en un entorno especifico.
 * Opciones:
 * - ''        => deteccion automatica
 * - 'local'   => fuerza entorno local
 * - 'hosting' => fuerza entorno hosting
 */

$SCANTEC_FORCE_ENV = 'local';

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('scantecDetectarEntorno')) {
    function scantecDetectarEntorno(string $forcedEnv = ''): string
    {
        $forcedEnv = strtolower(trim($forcedEnv));
        if (in_array($forcedEnv, ['local', 'hosting'], true)) {
            return $forcedEnv;
        }

        $env = strtolower(trim((string) ($_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?: '')));
        if (in_array($env, ['local', 'hosting'], true)) {
            return $env;
        }

        $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'cli')));
        if (
            $host === 'localhost'
            || $host === '127.0.0.1'
            || str_starts_with($host, 'localhost:')
            || str_starts_with($host, '127.0.0.1:')
            || str_ends_with($host, '.local')
        ) {
            return 'local';
        }

        return 'hosting';
    }
}

if (!function_exists('scantecNormalizarRuta')) {
    function scantecNormalizarRuta(string $ruta, bool $conSeparadorFinal = false): string
    {
        $ruta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($ruta));
        if ($ruta === '') {
            return '';
        }

        return $conSeparadorFinal
            ? rtrim($ruta, '/\\') . DIRECTORY_SEPARATOR
            : rtrim($ruta, '/\\');
    }
}

if (!function_exists('scantecPrimerDirectorioDisponible')) {
    function scantecPrimerDirectorioDisponible(array $rutas, bool $conSeparadorFinal = false): string
    {
        foreach ($rutas as $ruta) {
            $ruta = scantecNormalizarRuta((string) $ruta, false);
            if ($ruta === '') {
                continue;
            }

            if (is_dir($ruta) && is_writable($ruta)) {
                return $conSeparadorFinal ? $ruta . DIRECTORY_SEPARATOR : $ruta;
            }
        }

        foreach ($rutas as $ruta) {
            $ruta = scantecNormalizarRuta((string) $ruta, false);
            if ($ruta !== '') {
                return $conSeparadorFinal ? $ruta . DIRECTORY_SEPARATOR : $ruta;
            }
        }

        return '';
    }
}

if (!function_exists('scantecPrimerEjecutableDisponible')) {
    function scantecPrimerEjecutableDisponible(array $opciones, string $fallback = ''): string
    {
        foreach ($opciones as $opcion) {
            $opcion = trim((string) $opcion);
            if ($opcion === '') {
                continue;
            }

            if (strpos($opcion, DIRECTORY_SEPARATOR) === false && strpos($opcion, '/') === false && strpos($opcion, '\\') === false) {
                return $opcion;
            }

            if (file_exists($opcion)) {
                return $opcion;
            }
        }

        return $fallback;
    }
}

$scantecEnv = scantecDetectarEntorno($SCANTEC_FORCE_ENV ?? '');
$scantecConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'config.' . $scantecEnv . '.php';

if (!is_file($scantecConfigFile)) {
    http_response_code(500);
    exit('Missing config for env: ' . htmlspecialchars($scantecEnv, ENT_QUOTES, 'UTF-8'));
}

$SCANTEC_APP_CONFIG = require $scantecConfigFile;
if (!is_array($SCANTEC_APP_CONFIG)) {
    http_response_code(500);
    exit('Invalid config for env: ' . htmlspecialchars($scantecEnv, ENT_QUOTES, 'UTF-8'));
}

$SCANTEC_APP_CONFIG['env'] = $scantecEnv;
$GLOBALS['SCANTEC_APP_CONFIG'] = $SCANTEC_APP_CONFIG;

require_once 'ConfigDb.php';
require_once 'Loader.php';

$licenciaEstado = LicenseLoader::verificarEstado();

if (!defined('APP_CHARSET')) {
    define('APP_CHARSET', 'UTF-8');
}

if (!defined('APP_LANG')) {
    define('APP_LANG', 'es-PY');
}

if (!defined('APP_LOCALE')) {
    define('APP_LOCALE', 'es_PY.UTF-8');
}

ini_set('default_charset', APP_CHARSET);

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding(APP_CHARSET);
}

if (function_exists('mb_http_output')) {
    mb_http_output(APP_CHARSET);
}

if (function_exists('mb_regex_encoding')) {
    mb_regex_encoding(APP_CHARSET);
}

if (function_exists('iconv_set_encoding')) {
    @iconv_set_encoding('input_encoding', APP_CHARSET);
    @iconv_set_encoding('internal_encoding', APP_CHARSET);
    @iconv_set_encoding('output_encoding', APP_CHARSET);
}

if (!headers_sent()) {
    header('Content-Type: text/html; charset=' . APP_CHARSET);
    header('Content-Language: ' . APP_LANG);
}

if (!defined('LICENCIA_CLIENTE')) {
    die('<h1>Error Crítico de Seguridad</h1><p>No se han cargado las credenciales de acceso. Contacte al administrador.</p>');
}

if (!defined('LICENCIA_AMBIENTE')) {
    define('LICENCIA_AMBIENTE', 'SIN_LICENCIA');
}

if (!defined('LICENCIA_EXPIRA')) {
    define('LICENCIA_EXPIRA', '1970-01-01');
}

if (!defined('LICENCIA_MAX_USUARIOS')) {
    define('LICENCIA_MAX_USUARIOS', 0);
}

if (!defined('LICENCIA_ACTIVA')) {
    define('LICENCIA_ACTIVA', !empty($licenciaEstado['status']));
}

if (!defined('LICENCIA_MENSAJE')) {
    define('LICENCIA_MENSAJE', (string) ($licenciaEstado['msg'] ?? 'Licencia no disponible.'));
}

if (!LICENCIA_ACTIVA) {
    die('<h1>Licencia inválida</h1><p>' . htmlspecialchars(LICENCIA_MENSAJE, ENT_QUOTES, 'UTF-8') . '</p>');
}

if (!defined('LIMITE_USUARIOS')) {
    define('LIMITE_USUARIOS', defined('LICENCIA_MAX_USUARIOS') ? intval(LICENCIA_MAX_USUARIOS) : 0);
}

$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
$_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
$_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] ?? '/index.php';

$directorioProyecto = dirname(__DIR__);
$esWindows = DIRECTORY_SEPARATOR === '\\';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$serverName = (string) ($_SERVER['SERVER_NAME'] ?? 'localhost');
$serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '80');
$port = ($serverPort === '80' || $serverPort === '443') ? '' : ':' . $serverPort;
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$projectDir = rtrim(str_replace('/index.php', '', $scriptName), '/') . '/';
$baseUrlDetectada = $protocol . '://' . $serverName . $port . $projectDir;
$rootPathDetectado = scantecNormalizarRuta((string) $_SERVER['DOCUMENT_ROOT'] . $projectDir, true);

$baseUrlConfigurada = trim((string) ($SCANTEC_APP_CONFIG['base_url'] ?? ''));
$rootPathConfigurado = trim((string) ($SCANTEC_APP_CONFIG['root_path'] ?? ''));

define('APP_ENV', $scantecEnv);
// Si base_url esta vacia, se construye automaticamente segun la URL y la carpeta real del proyecto.
define('BASE_URL', $baseUrlConfigurada !== '' ? rtrim($baseUrlConfigurada, '/') . '/' : $baseUrlDetectada);
define('HOST', trim((string) ($SCANTEC_APP_CONFIG['host'] ?? 'localhost')) !== '' ? trim((string) $SCANTEC_APP_CONFIG['host']) : 'localhost');
// Si root_path esta vacia, se resuelve automaticamente segun la ubicacion real del sistema.
define('ROOT_PATH', $rootPathConfigurado !== '' ? scantecNormalizarRuta($rootPathConfigurado, true) : $rootPathDetectado);

$rutaStorageDefault = scantecPrimerDirectorioDisponible([
    trim((string) ($SCANTEC_APP_CONFIG['storage_path'] ?? '')),
    getenv('SCANTEC_STORAGE_PATH'),
    $esWindows ? 'C:/xampp/scantec_storage' : '',
    $directorioProyecto . DIRECTORY_SEPARATOR . 'storage',
], true);

$rutaBackupDefault = scantecPrimerDirectorioDisponible([
    trim((string) ($SCANTEC_APP_CONFIG['backup_path'] ?? '')),
    getenv('SCANTEC_BACKUP_PATH'),
    $esWindows ? 'C:/xampp/backups_scantec' : '',
    $directorioProyecto . DIRECTORY_SEPARATOR . 'backups',
], true);

$rutaExiftoolDefault = scantecPrimerEjecutableDisponible([
    trim((string) ($SCANTEC_APP_CONFIG['exiftool_path'] ?? '')),
    getenv('SCANTEC_EXIFTOOL_PATH'),
    $esWindows ? 'C:/xampp/htdocs/Tools/exiftool.exe' : '',
    '/usr/bin/exiftool',
    '/usr/local/bin/exiftool',
], '');

$rutaMagickDefault = scantecPrimerEjecutableDisponible([
    trim((string) ($SCANTEC_APP_CONFIG['magick_path'] ?? '')),
    getenv('SCANTEC_MAGICK_PATH'),
    $esWindows ? 'C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe' : '',
    '/usr/bin/magick',
    '/usr/local/bin/magick',
    'magick',
], 'magick');

$rutaTesseractDefault = scantecPrimerEjecutableDisponible([
    trim((string) ($SCANTEC_APP_CONFIG['tesseract_path'] ?? '')),
    getenv('SCANTEC_TESSERACT_PATH'),
    $esWindows ? 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe' : '',
    '/usr/bin/tesseract',
    '/usr/local/bin/tesseract',
    'tesseract',
], 'tesseract');

define('RUTA_BASE', $rutaStorageDefault);
define('BACKUP_PATH', $rutaBackupDefault);
define('RUTA_EXIFTOOL', $rutaExiftoolDefault);
define('MAGICK_EXECUTABLE_PATH', $rutaMagickDefault);
define('TESSERACT_EXECUTABLE_PATH', $rutaTesseractDefault);
define('MEDIA_PATH', ROOT_PATH . 'Assets/img/');
define('MEDIA_URL', BASE_URL . 'Assets/img/');

define('BD', obtenerBaseDatosSeleccionada((string) ($SCANTEC_APP_CONFIG['db_name'] ?? BD_DEFAULT)));

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
setlocale(
    LC_ALL,
    APP_LOCALE,
    'es_PY.utf8',
    'es_PY',
    'es_ES.UTF-8',
    'es_ES.utf8',
    'es_ES',
    'Spanish_Paraguay.1252',
    'Spanish_Spain.1252',
    'Spanish'
);
