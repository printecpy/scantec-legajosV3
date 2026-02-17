<?php
/**
 * --------------------------------------------------------------------------
 * CONFIGURACIÓN PRINCIPAL DEL SISTEMA (CORE)
 * --------------------------------------------------------------------------
 * Este archivo maneja la detección dinámica del entorno (Dev/Prod),
 * definición de rutas absolutas y carga de dependencias binarias.
 */

// Carga de credenciales y reglas de negocio ofuscadas (IonCube)
require_once 'Loader.php'; 
// Verificación de Seguridad: Si Loader no hizo su trabajo, detenemos todo.
if (!defined('LICENCIA_CLIENTE')) {
    die("<h1>Error Crítico de Seguridad</h1><p>No se han cargado las credenciales de acceso. Contacte al administrador.</p>");
}
// --------------------------------------------------------------------------
// 1. DETECCIÓN DE ENTORNO Y URL BASE
// --------------------------------------------------------------------------
// Protocolo: Detecta automáticamente HTTP vs HTTPS
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
// Host: Detecta si es localhost, IP (192.168.x.x) o Dominio
$serverName = $_SERVER['SERVER_NAME'];
// Puerto: Detecta el puerto de escucha (ej. 8882) y lo omite si es estándar (80/443)
$serverPort = $_SERVER['SERVER_PORT'];
$port = ($serverPort == "80" || $serverPort == "443") ? "" : ":$serverPort";
// Directorio del Proyecto: Ajustar si cambia el nombre de la carpeta raíz
$projectDir = '/scantec/'; 
// Definición de URL Base Global
define('BASE_URL', $protocol . "://" . $serverName . $port . $projectDir);
// --------------------------------------------------------------------------
// 2. RUTAS DEL SISTEMA DE ARCHIVOS (FILESYSTEM PATHS)
// --------------------------------------------------------------------------
define('HOST', 'localhost');
// Ruta Raíz del Documento (Evita hardcoding de C:/xampp...)
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . $projectDir);
// Rutas de Almacenamiento y Respaldos
// TODO: En producción, mover fuera de htdocs por seguridad (otra carpeta local o compartida accesible al servidor)
const RUTA_BASE = "C:/xampp/scantec_storage/";
const BACKUP_PATH = "C:/xampp/backups_scantec/";
// --------------------------------------------------------------------------
// 3. DEPENDENCIAS EXTERNAS (BINARIOS)
// --------------------------------------------------------------------------
// ExifTool: Utilidad para lectura/escritura de metadatos
const RUTA_EXIFTOOL = "C:/xampp/htdocs/Tools/exiftool.exe";
// ImageMagick: Motor de procesamiento de imágenes
define('MAGICK_EXECUTABLE_PATH', 'C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe');
// Tesseract OCR: Motor de reconocimiento óptico de caracteres
define('TESSERACT_EXECUTABLE_PATH', 'C:\\Program Files\\Tesseract-OCR\tesseract.exe');
const MEDIA_PATH = ROOT_PATH . 'Assets/img/';
define('MEDIA_URL', BASE_URL . 'Assets/img/');
// --------------------------------------------------------------------------
// 4. CONEXIÓN A BASE DE DATOS
// --------------------------------------------------------------------------
define('DB_HOST', 'localhost'); // O la IP del servidor: 192.168.1.50
//define('DB_PORT', '3306');      // Puerto
define('BD',      'scantec_2'); 
define('DB_USER', 'scantec');   // Usuario MySQL
define('PASS',    '@Scantec*23'); // Contraseña MySQL

// Array para PDO
$db = [
    'host'     => DB_HOST,
    'username' => DB_USER,
    'password' => PASS,
    'db'       => BD
];
// Configuración Regional (Locale)
date_default_timezone_set('America/Asuncion');
setlocale(LC_ALL, "es_ES");