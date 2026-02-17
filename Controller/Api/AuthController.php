<?php
// ********** 1. INCLUSION MANUAL DE CLASES JWT **********
require_once 'Libraries/php-jwt-main/src/JWT.php';
require_once 'Libraries/php-jwt-main/src/Key.php';
require_once 'Libraries/php-jwt-main/src/JWTExceptionWithPayloadInterface.php';
require_once 'Libraries/php-jwt-main/src/BeforeValidException.php';
require_once 'Libraries/php-jwt-main/src/ExpiredException.php';
require_once 'Libraries/php-jwt-main/src/SignatureInvalidException.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthController extends Controllers
{
    private const JWT_SECRET_KEY = 'vbWYSAMa1Tb0u&eMiKtopgGkS$9GjoJrmTr970Geh5sEZbY(PJ2q)$wn1^cXsANnj%I)UMY(U9td&l7K2g%Rd*nfB$jm&sA9rb@15(X4IbxwZv(%b9(fEJKaaXOw^lJQ4^rMye%l04J7ioi$#1J(BL';

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['usuario'], $data['clave'])) {
            $this->sendErrorResponse(400, 'Se requieren los campos "usuario" y "clave".');
        }

        // 1. Carga MANUAL y directa del modelo de Usuarios
        $userModel = null;
        $modelName = 'UsuariosModel';
        $modelFile = "Models/{$modelName}.php"; // Ajusta la ruta si es necesario

        if (file_exists($modelFile)) {
            require_once($modelFile);
            $userModel = new $modelName();
        } else {
            $this->sendErrorResponse(500, 'Error interno: Modelo de usuarios no disponible.');
        }

        // $usuarioDb contiene los datos del usuario (id, id_rol, id_grupo, etc.) o false.
        $usuarioDb = $userModel->verificarCredenciales($data['usuario'], $data['clave']);

        if (!$usuarioDb) {
            $userModel->bloquarPC_IP('INTENTO_API', 'Login fallido para usuario: ' . $data['usuario']);
            $this->sendErrorResponse(401, 'Credenciales de acceso inválidas.');
        }

        // Generar Payload
        $time = time(); // Tiempo actual en segundos (Unix timestamp)
        $expiracion_segundos = 60 * 60; // 1 hora de validez

        // El tiempo de expiración es el tiempo actual + segundos de validez
        $expirationTime = $time + $expiracion_segundos;

        $payload = [
            'exp' => $expirationTime, // <-- USAMOS EL TIEMPO CALCULADO
            'data' => [
                // *** CRÍTICO: USAMOS $usuarioDb, no $data['usuario'] ***
                'id' => $usuarioDb['id'],
                'roles' => [intval($usuarioDb['id_rol'])],
                'id_grupo' => intval($usuarioDb['id_grupo'] ?? 0)
                // *******************************************************
            ]
        ];

        $jwt = JWT::encode($payload, self::JWT_SECRET_KEY, 'HS256');

        header('Content-Type: application/json');
        http_response_code(200);

        // *** CRÍTICO: USAMOS $expirationTime ***
        echo json_encode([
            'token' => $jwt,
            'expiracion' => date(DATE_ISO8601, $expirationTime)
        ]);
        exit();
    }

    /**
     * Este método debe estar definido, ya sea aquí o en la clase base Controllers.
     */
    protected function sendErrorResponse(int $code, string $message)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }
}
