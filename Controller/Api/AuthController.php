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

class AuthController extends ApiController
{
    public function __construct() {
        parent::__construct(); // Llama al constructor de ApiController
    }
    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['usuario'], $data['clave'])) {
            $this->sendErrorResponse(400, 'Se requieren los campos "usuario" y "clave".');
        }

        // El modelo UsuariosModel ya está cargado en $this->UsuariosModel por el constructor de ApiController
        // $usuarioDb contiene los datos del usuario (id, id_rol, id_grupo, etc.) o false.
        $usuarioDb = $this->UsuariosModel->verificarCredenciales($data['usuario'], $data['clave']);

        if (!$usuarioDb) {
            $this->UsuariosModel->bloquarPC_IP('INTENTO_API', 'Login fallido para usuario: ' . $data['usuario']);
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

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        // Estandarizar la respuesta usando el formato de ApiController
        $this->sendSuccessResponse(200, [
            'token' => $jwt,
            'expires_at' => date(DATE_ISO8601, $expirationTime)
        ], 'Login exitoso.');
        // La función sendSuccessResponse ya incluye exit(), por lo que el código siguiente no se ejecutará.
    }
}
