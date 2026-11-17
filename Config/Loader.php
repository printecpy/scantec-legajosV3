<?php
// Clave legacy AES — se conserva únicamente para poder seguir leyendo
// licencias ya emitidas antes de esta actualización. No se usa para nada nuevo.
define('ENCRYPTION_KEY', 'xBgTvL9#mKz@4dNqR2$pW8jF5vCyH3sA');

// Clave PÚBLICA del nuevo esquema de firma digital (Ed25519). No es secreta —
// puede quedar visible en el código sin comprometer la seguridad, porque con
// ella solo se puede VERIFICAR una firma, nunca crear una nueva.
// Reemplazar el string de abajo por la clave pública real que te imprimió
// generar_claves.php (la que dice "CLAVE PÚBLICA", NO la privada).
define('LICENSE_PUBLIC_KEY', base64_decode('zPHFlEEq2AePxDMdECFAYM/60kxp00GWk3D33pxVBdE='));

class LicenseLoader
{
    private static $licenciaData = [];
    private static $estado = [
        'status' => true,
        'msg' => 'Licencia activa'
    ];

    public static function cargar()
    {
        if (!empty(self::$licenciaData)) {
            return;
        }

        $directorio = __DIR__ . '/../';
        $patron = $directorio . 'scantec-*.lic';
        $archivos = glob($patron);

        if ($archivos === false || count($archivos) === 0) {
            self::$estado = [
                'status' => false,
                'msg' => 'No se encontró ningún archivo de licencia (scantec-*.lic).'
            ];
            return;
        }

        usort($archivos, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $archivoSeleccionado = $archivos[0];
        $contenido = file_get_contents($archivoSeleccionado);
        $contenidoDecodificado = base64_decode($contenido);

        if ($contenidoDecodificado === false || strpos($contenidoDecodificado, '::') === false) {
            self::$estado = [
                'status' => false,
                'msg' => 'El archivo de licencia ' . basename($archivoSeleccionado) . ' no es valido o esta corrupto.'
            ];
            return;
        }

        // 1. Intentamos primero el esquema NUEVO (firma digital Ed25519).
        $resultado = self::intentarEsquemaFirmaDigital($contenidoDecodificado);

        // 2. Si no corresponde al esquema nuevo, probamos el esquema VIEJO
        //    (cifrado AES simétrico) — para no romper licencias existentes.
        if ($resultado === null) {
            $resultado = self::intentarEsquemaAesLegacy($contenidoDecodificado);
        }

        if ($resultado === null) {
            self::$estado = [
                'status' => false,
                'msg' => 'No se pudo procesar el archivo de licencia. Formato no reconocido.'
            ];
            return;
        }

        if ($resultado['status'] === false) {
            self::$estado = $resultado;
            return;
        }

        $datos = $resultado['datos'];

        if (!is_array($datos) || empty($datos['cliente']) || empty($datos['fecha_expira'])) {
            self::$estado = [
                'status' => false,
                'msg' => 'El contenido de la licencia es invalido o esta incompleto.'
            ];
            return;
        }

        if (!defined('LICENCIA_CLIENTE')) {
            define('LICENCIA_CLIENTE', $datos['cliente']);
        }
        
        if (!defined('LICENCIA_AMBIENTE')) {
            define('LICENCIA_AMBIENTE', $datos['ambiente'] ?? '');
        }
        if (!defined('LICENCIA_EXPIRA')) {
            define('LICENCIA_EXPIRA', $datos['fecha_expira']);
        }
        if (!defined('LICENCIA_MAX_USUARIOS')) {
            define('LICENCIA_MAX_USUARIOS', intval($datos['max_usuarios'] ?? 0));
        }
        if (!defined('LICENCIA_MAX_TIPODOC')) {
            define('LICENCIA_MAX_TIPODOC', intval($datos['max_tipodoc'] ?? 0));
        }
        if (!defined('LICENCIA_MAX_TIPOLEGAJO')) {
            define('LICENCIA_MAX_TIPOLEGAJO', intval($datos['max_tipolegajo'] ?? 0));
        }

        self::$licenciaData = $datos;
        self::$estado = [
            'status' => true,
            'msg' => 'Licencia activa'
        ];
    }

    /**
     * Esquema nuevo: JSON en claro + firma Ed25519 detached, separados por '::'.
     * Se distingue del esquema viejo porque el JSON incluye el campo '_esquema'.
     * Devuelve null si el contenido no corresponde a este esquema (para que
     * cargar() pruebe el esquema legacy a continuación).
     */
    private static function intentarEsquemaFirmaDigital(string $contenidoDecodificado): ?array
    {
        $partes = explode('::', $contenidoDecodificado, 2);
        if (count($partes) !== 2) {
            return null;
        }
        list($json, $firmaB64) = $partes;

        $datos = json_decode($json, true);
        if (!is_array($datos) || !isset($datos['_esquema'])) {
            return null;
        }

        if (!extension_loaded('sodium')) {
            return ['status' => false, 'msg' => 'El servidor no tiene soporte para validar este formato de licencia (extensión sodium no disponible).'];
        }

        $firma = base64_decode($firmaB64, true);
        if ($firma === false || strlen($firma) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return ['status' => false, 'msg' => 'La licencia tiene un formato de firma inválido.'];
        }

        if (!sodium_crypto_sign_verify_detached($firma, $json, LICENSE_PUBLIC_KEY)) {
            return ['status' => false, 'msg' => 'La licencia no es válida o fue alterada.'];
        }

        return ['status' => true, 'datos' => $datos];
    }

    /**
     * Esquema legacy: AES-256-CBC, IV + contenido cifrado separados por '::'.
     * Se mantiene únicamente por compatibilidad con licencias emitidas antes
     * de la migración a firma digital.
     */
    private static function intentarEsquemaAesLegacy(string $contenidoDecodificado): ?array
    {
        $partes = explode('::', $contenidoDecodificado, 2);
        if (count($partes) !== 2) {
            return null;
        }
        list($iv, $encrypted_data) = $partes;

        $json = @openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
        if ($json === false) {
            return null;
        }

        $datos = json_decode($json, true);
        if (!is_array($datos)) {
            return null;
        }

        return ['status' => true, 'datos' => $datos];
    }

    public static function obtenerDatosArray()
    {
        if (empty(self::$licenciaData)) {
            self::cargar();
        }

        return self::$licenciaData;
    }

    public static function verificarEstado()
    {
        if (!defined('LICENCIA_EXPIRA')) {
            self::cargar();
        }

        if (empty(self::$estado['status'])) {
            return self::$estado;
        }

        $hoy = new DateTime();
        $expira = new DateTime(LICENCIA_EXPIRA);
        if ($hoy > $expira) {
            return ['status' => false, 'msg' => 'Su licencia ha caducado.'];
        }

        return ['status' => true, 'msg' => 'Licencia activa'];
    }

    public static function verificarLimiteRecurso($constanteLimite, $cantidadActual, $etiqueta = 'elementos')
    {
        if (!defined($constanteLimite)) {
            self::cargar();
        }

        $limite = defined($constanteLimite) ? intval(constant($constanteLimite)) : 0;

        if ($limite <= 0) {
            return ['status' => true, 'msg' => 'Sin límite'];
        }

        if ($cantidadActual >= $limite) {
            return [
                'status' => false,
                'msg' => "Ha alcanzado el límite de {$limite} {$etiqueta} activos habilitados por su licencia. Para ampliarlo, contacte al proveedor."
            ];
        }

        return ['status' => true, 'msg' => 'OK'];
    }
}

LicenseLoader::cargar();