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
    public function __construct()
    {
        parent::__construct(); // Llama al constructor de ApiController
    }

    public function login()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['usuario'], $data['clave'])) {
            $this->sendErrorResponse(400, 'Se requieren los campos "usuario" y "clave".');
        }

        $usuarioDb = $this->UsuariosModel->verificarCredenciales($data['usuario'], $data['clave']);

        if (!$usuarioDb) {
            $this->UsuariosModel->bloquarPC_IP('INTENTO_API', 'Login fallido para usuario: ' . $data['usuario']);
            $this->sendErrorResponse(401, 'Credenciales de acceso inválidas.');
        }

        $this->api_user_id = intval($usuarioDb['id']);

        // FALTA 1: solo cuentas 'API' pueden autenticarse por este canal.
        if (strtoupper(trim((string) ($usuarioDb['fuente_registro'] ?? ''))) !== 'API') {
            $this->UsuariosModel->bloquarPC_IP('INTENTO_API', 'Intento de acceso API con cuenta no-API: ' . $data['usuario']);
            $this->sendErrorResponse(403, 'Esta cuenta no está habilitada para el acceso vía API.');
        }

        // Access token (1 hora)
        $accessToken = $this->generarAccessToken($usuarioDb);

        // FALTA 2: generar y guardar el refresh token (30 días)
        require_once 'Models/ApiRefreshTokenModel.php';
        $refreshModel = new ApiRefreshTokenModel();
        $refreshTokenClaro = bin2hex(random_bytes(40));
        $diasVigenciaRefresh = 30;
        $refreshModel->crear(
            intval($usuarioDb['id']),
            $refreshTokenClaro,
            $diasVigenciaRefresh,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        // FALTA 3: devolver ambos tokens con el formato del patrón access + refresh
        $this->sendSuccessResponse(200, [
            'access_token' => $accessToken['token'],
            'access_expires_at' => $accessToken['expires_at'],
            'refresh_token' => $refreshTokenClaro,
            'refresh_expires_in_days' => $diasVigenciaRefresh,
            'token_type' => 'Bearer'
        ], 'Login exitoso.');
    }

    public function refresh()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['refresh_token']) || trim((string) $data['refresh_token']) === '') {
            $this->sendErrorResponse(400, 'Se requiere el campo "refresh_token".');
        }

        $refreshTokenClaro = trim((string) $data['refresh_token']);

        require_once 'Models/ApiRefreshTokenModel.php';
        $refreshModel = new ApiRefreshTokenModel();

        $tokenInfo = $refreshModel->obtenerValido($refreshTokenClaro);
        if (empty($tokenInfo)) {
            $this->sendErrorResponse(401, 'Refresh token inválido, expirado o revocado. Debe iniciar sesión nuevamente.');
        }

        $idUsuario = intval($tokenInfo['id_usuario']);

        // Traer los datos actuales del usuario (rol/grupo pueden haber cambiado desde el login).
        $usuarioDb = $this->UsuariosModel->obtenerUsuarioApiPorId($idUsuario);
        if (empty($usuarioDb)) {
            // El usuario ya no existe o está inactivo: revocamos y cortamos.
            $refreshModel->revocar($refreshTokenClaro);
            $this->sendErrorResponse(401, 'La cuenta asociada ya no está disponible.');
        }

        $this->api_user_id = $idUsuario;

        // --- ROTACIÓN: invalidamos el refresh usado y emitimos uno nuevo ---
        $refreshModel->revocar($refreshTokenClaro);
        $nuevoRefreshClaro = bin2hex(random_bytes(40));
        $refreshModel->crear($idUsuario, $nuevoRefreshClaro, 30, $_SERVER['REMOTE_ADDR'] ?? null);

        // Nuevo access token
        $accessToken = $this->generarAccessToken($usuarioDb);

        $this->sendSuccessResponse(200, [
            'access_token' => $accessToken['token'],
            'access_expires_at' => $accessToken['expires_at'],
            'refresh_token' => $nuevoRefreshClaro,
            'refresh_expires_in_days' => 30,
            'token_type' => 'Bearer'
        ], 'Token renovado correctamente.');
    }

    private function generarAccessToken(array $usuarioDb): array
    {
        $time = time();
        $expiracionSegundos = 60 * 60; // 1 hora
        $expirationTime = $time + $expiracionSegundos;

        $payload = [
            'exp' => $expirationTime,
            'data' => [
                'id' => intval($usuarioDb['id']),
                'roles' => [intval($usuarioDb['id_rol'])],
                'id_grupo' => intval($usuarioDb['id_grupo'] ?? 0)
            ]
        ];

        return [
            'token' => JWT::encode($payload, $this->secretKey, 'HS256'),
            'expires_at' => date(DATE_ISO8601, $expirationTime)
        ];
    }

}
