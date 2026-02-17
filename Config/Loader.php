<?php
// CLAVE MAESTRA (Debe coincidir con la del Generador)
define('ENCRYPTION_KEY', 'xBgTvL9#mKz@4dNqR2$pW8jF5vCyH3sA');

class LicenseLoader
{

    public static function cargar()
    {
        // 1. BUSCAR EL ARCHIVO MÁS RECIENTE
        $directorio = __DIR__ . '/../';
        // Buscamos cualquier archivo .lic que empiece con scantec-
        $patron = $directorio . 'scantec-*.lic';
        $archivos = glob($patron);

        if ($archivos === false || count($archivos) === 0) {
            die("<div style='color:red; text-align:center; padding:50px; font-family:sans-serif;'>
                    ⛔ <b>ERROR CRÍTICO</b><br>
                    No se encontró ningún archivo de licencia (scantec-*.lic).
                 </div>");
        }

        // Ordenar por fecha (el más nuevo primero)
        usort($archivos, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $archivoSeleccionado = $archivos[0];
        $contenido = file_get_contents($archivoSeleccionado);

        // --- CORRECCIÓN AQUÍ ---
        // Primero decodificamos de Base64 a texto plano
        $contenidoDecodificado = base64_decode($contenido);

        // Validamos si la decodificación falló o si no tiene el separador '::'
        if ($contenidoDecodificado === false || strpos($contenidoDecodificado, '::') === false) {
            die("<div style='color:red; text-align:center; padding:50px; font-family:sans-serif;'>
                    ⛔ <b>ERROR DE FORMATO</b><br>
                    El archivo <i>" . basename($archivoSeleccionado) . "</i> no es válido o está corrupto.<br>
                    Verifique que el generador y el loader usen el mismo algoritmo.
                  </div>");
        }

        // Separamos el Vector de Inicialización (IV) de los Datos Encriptados
        list($iv, $encrypted_data) = explode('::', $contenidoDecodificado, 2);

        // Desencriptamos (AES-256-CBC)
        $json = openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);

        if ($json === false) {
            die("<div style='color:red; text-align:center; padding:50px; font-family:sans-serif;'>
                    ⛔ <b>ERROR DE ENCRIPTACIÓN</b><br>
                    La clave maestra no coincide o el archivo fue manipulado.
                  </div>");
        }

        $datos = json_decode($json, true);

        // DEFINIR SOLO REGLAS DE NEGOCIO (Provenientes de la licencia)
        if (!defined('LICENCIA_CLIENTE'))
            define('LICENCIA_CLIENTE', $datos['cliente']);
        if (!defined('LICENCIA_AMBIENTE'))
            define('LICENCIA_AMBIENTE', $datos['ambiente']);
        if (!defined('LICENCIA_EXPIRA'))
            define('LICENCIA_EXPIRA', $datos['fecha_expira']);

        // Nota: En el generador usaste 'max_usuarios', asegúrate de mapearlo bien
        if (!defined('LICENCIA_MAX_USUARIOS'))
            define('LICENCIA_MAX_USUARIOS', $datos['max_usuarios']);

        // Guardar datos para uso interno (guardar en BD luego)
        self::$licenciaData = $datos;
    }

    // Almacén temporal de datos para el controlador de configuración
    private static $licenciaData = [];

    public static function obtenerDatosArray()
    {
        if (empty(self::$licenciaData)) {
            self::cargar();
        }
        return self::$licenciaData;
    }

    public static function verificarEstado()
    {
        if (!defined('LICENCIA_EXPIRA'))
            self::cargar();

        $hoy = new DateTime();
        $expira = new DateTime(LICENCIA_EXPIRA);
        if ($hoy > $expira) {
            return ['status' => false, 'msg' => 'Su licencia ha caducado.'];
        }
        return ['status' => true, 'msg' => 'Licencia Activa'];
    }
}

// Ejecutar carga automática al incluir el archivo
LicenseLoader::cargar();