<?php
header("Content-Type: application/json; charset=UTF-8");

include "../../Config/Config.php";
include "../utils.php";
$dbConn = connect($db);
session_start();

// Verificar si el token de sesión existe y si ha expirado (5 minutos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    session_unset();     // Eliminar las variables de sesión
    session_destroy();   // Destruir la sesión
}

$_SESSION['LAST_ACTIVITY'] = time(); // Actualizar el timestamp

// Verificar autenticación HTTP básica
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Zona Privada"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Credenciales requeridas']);
    exit;
} else {
    $usuario = $_SERVER['PHP_AUTH_USER'];
    $clave = $_SERVER['PHP_AUTH_PW'];

    // Buscar usuario en la base de datos
    $stmt = $dbConn->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar contraseña con password_verify (bcrypt)
    if ($result && password_verify($clave, $result['clave'])) {
        // ✅ Acceso permitido
        echo json_encode(['OK' => 'Acceso permitido.']);

        // Si se pasa un ID, intentar mostrar el PDF
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            try {
                $stmt = $dbConn->prepare('SELECT ruta_original FROM expediente WHERE indice_01 = :id');
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    $ubicacion = 'D:/Incan/Expedientes/';
                    $file_path = $ubicacion . $result['ruta_original'];
                    $file_name = basename($file_path);

                    if (file_exists($file_path)) {
                        $file_type = mime_content_type($file_path);
                        header('Content-Type: ' . $file_type);
                        header('Content-Disposition: inline; filename="' . $file_name . '"');
                        header('Content-Length: ' . filesize($file_path));
                        readfile($file_path);
                        exit;
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Archivo no encontrado.']);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Documento no encontrado.']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error interno del servidor', 'detalle' => $e->getMessage()]);
            }
        }
    } else {
        // ❌ Credenciales inválidas
        header('WWW-Authenticate: Basic realm="Zona Privada"');
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Credenciales incorrectas.']);
        exit;
    }
}
