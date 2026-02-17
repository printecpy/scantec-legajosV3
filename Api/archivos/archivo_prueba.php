<?php
header("Content-Type: application/json; charset=UTF-8");

include "../../Config/Config.php";
include "../utils.php";
$dbConn =  connect($db);
session_start();

// Verificar si el token de sesión existe y si ha expirado
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    // Última petición fue hace más de 5 minutos
    session_unset();     // Eliminar las variables de sesión
    session_destroy();   // Destruir la sesión
}

$_SESSION['LAST_ACTIVITY'] = time(); // Actualizar el timestamp

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Zona Privada"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Las credenciales son incorrectas.';
    exit;
} else {
    $usuario = $_SERVER['PHP_AUTH_USER'];
    $clave = $_SERVER['PHP_AUTH_PW'];

    // Convertir la contraseña a SHA512
    $clave_hash = hash("SHA512", $clave);

    // Consulta a la base de datos para verificar las credenciales
    $stmt = $dbConn->prepare('SELECT * FROM usuarios WHERE usuario = :usuario AND clave = :clave');
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':clave', $clave_hash);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Las credenciales son correctas, el usuario puede acceder al sistema API
        echo 'Acceso permitido.';
        // Aquí puedes agregar el resto de tu código...
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
        try {
            $stmt = $dbConn->prepare('SELECT ruta_original FROM expediente WHERE documento = :id');
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            header("HTTP/1.1 200 OK");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($result) {
                $ubicacion = 'D:/Incan/Expedientes/';
                $file_path = $ubicacion.''.$result['ruta_original']; // Use 'ruta_original' instead of 'file_path'
                $file_name = basename($file_path);
                $file_type = mime_content_type($file_path);
    
                // Change Content-Disposition from "attachment" to "inline"
                header('Content-Type: ' . $file_type);
                header('Content-Disposition: inline; filename="' . $file_name . '"');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error'] . $e->getMessage());
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Solicitud incorrecta']);
    }
    } else {
        // Las credenciales son incorrectas, se niega el acceso
        header('WWW-Authenticate: Basic realm="Zona Privada"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Las credenciales son incorrectas.';
        exit;
    }
}
?>