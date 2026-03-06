<?php
require_once 'Libraries/php-jwt-main/src/JWT.php';
require_once 'Libraries/php-jwt-main/src/Key.php';
require_once 'Libraries/php-jwt-main/src/JWTExceptionWithPayloadInterface.php';
require_once 'Libraries/php-jwt-main/src/BeforeValidException.php';
require_once 'Libraries/php-jwt-main/src/ExpiredException.php';
require_once 'Libraries/php-jwt-main/src/SignatureInvalidException.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

abstract class ApiController extends Controllers // <-- Hereda correctamente de Controllers.php
{
    // Propiedad para el modelo específico del controlador (Ej. ExpedientesModel)
    protected $model;
    // Propiedad NUEVA para el modelo de Logs (se usará en todos los controladores API)
    protected $apiLogModel;
    // Datos del usuario JWT extraídos
    protected $api_user_id;
    protected $api_user_roles;
    protected $api_user_group_id; 
    protected $UsuariosModel;

    protected $secretKey;


    public function __construct()
    {
        $env_path = dirname(__DIR__, 2) . '/.env'; 
        if (file_exists($env_path)) {
            $env_vars = parse_ini_file($env_path);
            $this->secretKey = $env_vars['JWT_SECRET'] ?? 'CLAVE_RESPALDO';
        } else {
            // Si por algún motivo no existe el archivo, usamos un respaldo o tiramos error
            $this->secretKey = 'vbWYSAMa1Tb0u&eMiKtopgGkS$9GjoJrmTr970Geh5sEZbY(PJ2q)$wn1^cXsANnj%I)UMY(U9td&l7K2g%Rd*nfB$jm&sA9rb@15(X4IbxwZv(%b9(fEJKaaXOw^lJQ4^rMye%l04J7ioi$#1J(BL'; 
        }
        $this->apiLogModel = new ApiLogModel();
        // 1. Carga del modelo de usuario
        $modelName = 'UsuariosModel';
        $modelFile = "Models/{$modelName}.php"; // Ajusta la ruta si es necesario
        if (file_exists($modelFile)) {
            require_once($modelFile);
            $this->UsuariosModel = new $modelName();
        } else {
            $this->sendErrorResponse(500, 'Error interno: El modelo UsuariosModel es requerido para el API y no fue encontrado.');
        }
        // REMOVIDO: $this->authenticateJwt();
        // La autenticación ahora es explícita en checkApiAccess o se llama en el constructor de controladores específicos.
        parent::__construct();
    }

    /**
     * Registra una transacción completa de la API en archivos (debug) y en la BD (auditoría).
     * Es el punto central de logging en el ApiController.
     * * @param array $auth_data Datos del JWT (id, roles).
     * @param string $endpoint El endpoint llamado (ej: /Expedientes/subirDocumentoApi).
     * @param string $http_method El método HTTP (POST, GET).
     * @param int $code El código de respuesta HTTP.
     * @param string $audit_message Mensaje corto para la BD y el log de archivo.
     * @param array $response_data Datos completos de la respuesta/error.
     */
    protected function logApiTransaction(array $auth_data, string $endpoint, string $http_method, int $code, string $audit_message, array $response_data)
    {
        // ----------------------------------------------------
        // 1. LOGGING A ARCHIVO (DEBUGGING y DETALLES COMPLETOS)
        // ----------------------------------------------------
        $log_dir = RUTA_BASE . 'scantec2/logs/api/';
        if (!is_dir($log_dir)) {
            // Intentar crear el directorio. Usar @ para suprimir errores de permisos/existencia.
            @mkdir($log_dir, 0777, true);
        }
        $fecha = date('Y-m-d');
        $log_file = $log_dir . $fecha . '.log';

        // Construye el array de datos para el archivo
        $log_data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $auth_data['id'] ?? 'N/A',
            'user_roles' => $auth_data['roles'] ?? [],
            'endpoint' => $endpoint,
            'http_method' => $http_method,
            'http_code' => $code,
            'audit_message' => $audit_message,
            'ip_origen' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            // Incluir todo el payload POST (metadatos de subida)
            'request_post' => $_POST,
            // Incluir la respuesta completa
            'response_data' => $response_data,
        ];
        // Formatear a JSON con buen formato y delimitadores
        $log_message = json_encode($log_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            . "\n" . str_repeat('=', 80) . "\n";
        // Escribir en el archivo. FILE_APPEND añade al final. LOCK_EX previene corrupción de datos.
        @file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
        // 2. LOGGING A BASE DE DATOS (AUDITORÍA y SEGURIDAD)
        // Verificamos que el modelo de logs exista y el método esté disponible
        if (isset($this->apiLogModel) && method_exists($this->apiLogModel, 'registrarLogApi')) {
            $id_usuario = $auth_data['id'] ?? null;
            // 🚨 Regla de Auditoría: Solo registrar transacciones que cambian datos (POST, PUT, DELETE) 
            // o que son errores (>= 400). Evita llenar la BD con logs de GETs masivos.
            if ($code >= 400 || in_array($http_method, ['POST', 'PUT', 'DELETE'])) {
                $endpoint_raw = $_SERVER['REQUEST_URI'] ?? 'N/A';
                // Limpiar la base del proyecto, dejando solo el recurso (Ej: /Expedientes/verDocumento)
                $endpoint = str_replace('/scantec2/api/v1', '', $endpoint_raw);
                $this->apiLogModel->registrarLogApi(
                    $id_usuario,
                    $endpoint,
                    $http_method,
                    $code,
                    $audit_message, // Mensaje breve para la BD
                    $_SERVER['REMOTE_ADDR'] ?? 'N/A'
                );
            }
        }
    }

    // ==========================================================
    // MÉTODOS DE RESPUESTA
    // ==========================================================

    protected function sendErrorResponse(int $code, string $message, ?array $data = null)
    {
        // 1. Logear la Transacción (Error)
        // Usamos 'Error: ' en el mensaje para identificarlo fácilmente en los logs de BD
        $this->logApiTransaction(
            ['id' => $this->api_user_id ?? 0], // Si falla auth, id es 0 o null
            $_SERVER['REQUEST_URI'] ?? 'N/A',
            $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            $code,
            "Error: " . $message, // Mensaje para log
            ['error_message' => $message, 'details' => $data] // Datos de error
        );

        // 2. Enviar Respuesta
        if (ob_get_level() > 0) {
            ob_clean();
        }

        header('Content-Type: application/json');
        http_response_code($code);
        $response = ['status' => 'error', 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Envía una respuesta de éxito JSON, registra la transacción y termina la ejecución.
     * * @param int $code Código HTTP (ej: 200, 201).
     * @param array $data Datos de la respuesta.
     * @param string $auditMessage Mensaje para el log de BD/Archivo.
     */
    protected function sendSuccessResponse(int $code, array $data, string $auditMessage = 'Operación exitosa')
    {
        // 1. Logear la Transacción (Éxito)
        // Asumimos que $this->api_user_id y $_SERVER['REQUEST_METHOD'] están disponibles
        $this->logApiTransaction(
            ['id' => $this->api_user_id], // Datos mínimos de Auth
            $_SERVER['REQUEST_URI'] ?? 'N/A', // Endpoint actual
            $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            $code,
            $auditMessage,
            $data // Los datos de la respuesta
        );

        // 2. Enviar Respuesta
        if (ob_get_level() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(['status' => 'success', 'message' => $auditMessage, 'data' => $data]);
        exit();
    }

    // ==========================================================
    // AUTENTICACIÓN Y AUTORIZACIÓN
    // ==========================================================

    /**
     * Extrae, decodifica el token y almacena los datos en las propiedades de la clase.
     * Es llamada internamente por checkApiAccess.
     */
    protected function authenticateJwt()
    {
        $headers = getallheaders();
        // Intenta obtener el header Authorization en mayúsculas o minúsculas
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $this->sendErrorResponse(401, 'Token Bearer no proporcionado.');
        }

        $token = $matches[1];

        try {
            $payload = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            // Asignar datos a las propiedades de la clase
            $this->api_user_id = $payload->data->id ?? null;
            $this->api_user_roles = (array)($payload->data->roles ?? []);
            $this->api_user_group_id = $payload->data->id_grupo ?? null; // Asume que el grupo ID viene aquí

        } catch (Exception $e) {
            // Log de seguridad
            if (isset($this->UsuariosModel)) {
                $this->UsuariosModel->bloquarPC_IP('INTENTO_API', 'Fallo JWT: ' . $e->getMessage());
            }
            // Mantenemos el error genérico por seguridad
            $this->sendErrorResponse(401, message: 'Token invalido o expirado.');
        }
    }

    /**
     * Helper para verificar si el usuario API (extraído del JWT) tiene un rol permitido.
     * Llama a authenticateJwt() internamente.
     * @param array $allowedRoles Array de strings o enteros de los IDs de roles requeridos.
     * @return array Retorna un array con el id, roles, y id_grupo del usuario API.
     */
    protected function checkApiAccess(array $allowedRoles): array
    {
        // 1. Autenticar (Decodificar el token y cargar los datos)
        $this->authenticateJwt();

        // 2. Control de Autorización (Verificar Roles)
        $userRoles = $this->api_user_roles;

        if (count(array_intersect($userRoles, $allowedRoles)) > 0) {
            // Coincidencia encontrada: Acceso concedido.
            // Retornamos un array limpio de los datos principales
            return [
                'id' => $this->api_user_id,
                'roles' => $this->api_user_roles,
                'id_grupo' => $this->api_user_group_id
            ];
        }

        // 3. Acceso denegado: Loguear y responder con 403
        if (isset($this->UsuariosModel)) {
            $logMessage = 'Acceso API denegado por rol insuficiente. Usuario ID: ' . $this->api_user_id . ' - Roles Requeridos: ' . implode(',', $allowedRoles);
            $this->UsuariosModel->bloquarPC_IP('INTENTO_API_AUTH', $logMessage);
        }

        $this->sendErrorResponse(403, 'Acceso denegado. Rol insuficiente para esta operación.');
        // La ejecución se detiene en sendErrorResponse, esta línea es solo para completar la firma.
    }
}
