<?php
// CLAVE MAESTRA (Debe coincidir con la del Generador)
define('ENCRYPTION_KEY', 'xBgTvL9#mKz@4dNqR2$pW8jF5vCyH3sA');

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
                'msg' => 'No se encontro ningun archivo de licencia (scantec-*.lic).'
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

        list($iv, $encrypted_data) = explode('::', $contenidoDecodificado, 2);
        $json = openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);

        if ($json === false) {
            self::$estado = [
                'status' => false,
                'msg' => 'No se pudo validar la licencia. La clave maestra no coincide o el archivo fue manipulado.'
            ];
            return;
        }

        $datos = json_decode($json, true);
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

        self::$licenciaData = $datos;
        self::$estado = [
            'status' => true,
            'msg' => 'Licencia activa'
        ];
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
}

LicenseLoader::cargar();
