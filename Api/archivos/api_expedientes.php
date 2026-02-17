<?php
header("Content-Type: application/json; charset=UTF-8");

include "../../Config/Config.php";
include "../utils.php";
$dbConn = connect($db);
session_start();

// Verificar expiración de sesión
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Autenticación HTTP Básica
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Zona Privada"');
    header('HTTP/1.2 401 Unauthorized');
    echo json_encode(['error' => 'Credenciales requeridas.']);
    exit;
}

$usuario = $_SERVER['PHP_AUTH_USER'];
$clave = $_SERVER['PHP_AUTH_PW'];

$stmt = $dbConn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
$stmt->bindParam(':usuario', $usuario);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica la contraseña con bcrypt
if (!$user || !password_verify($clave, $user['clave'])) {
    header('WWW-Authenticate: Basic realm="Zona Privada"');
    header('HTTP/1.2 401 Unauthorized');
    echo json_encode(['error' => 'Credenciales incorrectas.']);
    exit;
}

// Parámetro requerido
$indice = $_GET['indice_01'] ?? null;

if (!$indice) {
    http_response_code(400);
    echo json_encode(['error' => "Parámetro 'indice_01' no proporcionado."]);
    exit;
}

// Consultar todos los documentos del CI (indice_01)
try {
    $stmt = $dbConn->prepare("SELECT id_expediente, id_tipoDoc, indice_04, ruta_original FROM expediente WHERE indice_01 = :indice and id_tipoDoc=3;");
    $stmt->bindParam(':indice', $indice);
    $stmt->execute();
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($docs) {
        // Devuelve listado JSON
        http_response_code(200);
        echo json_encode([
            'socio_ci' => $indice,
            'documentos' => array_map(function($doc) {
                return [
                    'indice_04' => $doc['indice_04'],
                    'archivo' => $doc['ruta_original'],
                    'url' => BASE_URL . 'api/archivos/verpdf.php?archivo=' . urlencode(basename($doc['ruta_original']))
                ];
            }, $docs)
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontraron documentos para el CI proporcionado.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos', 'detalle' => $e->getMessage()]);
}
