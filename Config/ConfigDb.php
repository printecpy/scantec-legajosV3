<?php
/**
 * HELPER DE BASE DE DATOS
 *
 * Este archivo no debe requerir cambios al instalar.
 * La configuracion real de conexion vive en:
 *
 * - Config/config.local.php
 * - Config/config.hosting.php
 *
 * Si falta algun valor, se aplican defaults tecnicos neutrales
 * solo para evitar errores fatales, no como configuracion final.
 */

if (!function_exists('scantecConfigDb')) {
    function scantecConfigDb(): array
    {
        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];

        return [
            'db_name' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($config['db_name'] ?? '')),
            'db_host' => trim((string) ($config['db_host'] ?? '')),
            'db_port' => trim((string) ($config['db_port'] ?? '')),
            'db_user' => trim((string) ($config['db_user'] ?? '')),
            'db_password' => (string) ($config['db_password'] ?? ''),
        ];
    }
}

if (!function_exists('obtenerBaseDatosSeleccionada')) {
    function obtenerBaseDatosSeleccionada(string $basePorDefecto = ''): string
    {
        $config = scantecConfigDb();
        $base = $config['db_name'] !== '' ? $config['db_name'] : preg_replace('/[^A-Za-z0-9_]/', '', (string) $basePorDefecto);
        return $base !== '' ? $base : BD_DEFAULT;
    }
}

if (!function_exists('obtenerConfiguracionesBases')) {
    function obtenerConfiguracionesBases(): array
    {
        $config = scantecConfigDb();
        $base = $config['db_name'] !== '' ? $config['db_name'] : BD_DEFAULT;

        return [
            $base => [
                'host' => $config['db_host'] !== '' ? $config['db_host'] : DB_HOST_DEFAULT,
                'port' => $config['db_port'] !== '' ? $config['db_port'] : DB_PORT_DEFAULT,
                'user' => $config['db_user'] !== '' ? $config['db_user'] : DB_APP_USER_DEFAULT,
                'password' => $config['db_password'],
            ],
        ];
    }
}

if (!function_exists('obtenerConexionBaseSeleccionada')) {
    function obtenerConexionBaseSeleccionada(string $baseSeleccionada = '', string $basePorDefecto = ''): array
    {
        $configuraciones = obtenerConfiguracionesBases();
        $baseActiva = obtenerBaseDatosSeleccionada($baseSeleccionada !== '' ? $baseSeleccionada : $basePorDefecto);

        if (isset($configuraciones[$baseActiva])) {
            return $configuraciones[$baseActiva];
        }

        return [
            'host' => DB_HOST_DEFAULT,
            'port' => DB_PORT_DEFAULT,
            'user' => DB_APP_USER_DEFAULT,
            'password' => DB_APP_PASS_DEFAULT,
        ];
    }
}

define('DB_HOST_DEFAULT', 'localhost');
define('DB_PORT_DEFAULT', '3306');
define('BD_DEFAULT', 'scantec');
define('DB_APP_USER_DEFAULT', '');
define('DB_APP_PASS_DEFAULT', '');

// Configuración de base de datos de usuarios
if (!function_exists('scantecConfigDbUsuarios')) {
    function scantecConfigDbUsuarios(): array
    {
        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];

        return [
            'db_name' => preg_replace('/[^A-Za-z0-9_]/', '', (string) ($config['db_usuarios_name'] ?? '')),
            'db_host' => trim((string) ($config['db_usuarios_host'] ?? '')),
            'db_port' => trim((string) ($config['db_usuarios_port'] ?? '')),
            'db_user' => trim((string) ($config['db_usuarios_user'] ?? '')),
            'db_password' => (string) ($config['db_usuarios_password'] ?? ''),
        ];
    }
}

if (!function_exists('obtenerConfiguracionUsuarios')) {
    function obtenerConfiguracionUsuarios(): array
    {
        $config = scantecConfigDbUsuarios();

        return [
            'host' => $config['db_host'] !== '' ? $config['db_host'] : DB_HOST_DEFAULT,
            'port' => $config['db_port'] !== '' ? $config['db_port'] : DB_PORT_DEFAULT,
            'user' => $config['db_user'] !== '' ? $config['db_user'] : DB_APP_USER_DEFAULT,
            'password' => $config['db_password'],
        ];
    }
}

if (!function_exists('obtenerBaseDatosUsuariosSeleccionada')) {
    function obtenerBaseDatosUsuariosSeleccionada(string $basePorDefecto = 'usuarios'): string
    {
        $config = scantecConfigDbUsuarios();
        $base = $config['db_name'] !== '' ? $config['db_name'] : preg_replace('/[^A-Za-z0-9_]/', '', (string) $basePorDefecto);
        return $base !== '' ? $base : 'usuarios';
    }
}

define('DB_USER_ROOT', DB_APP_USER_DEFAULT);
define('ROOT_PASS', DB_APP_PASS_DEFAULT);
