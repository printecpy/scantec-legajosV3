<?php
if (!function_exists('obtenerBaseDatosSeleccionada')) {
    function obtenerBaseDatosSeleccionada(string $basePorDefecto): string
    {
        $seleccion = $_SESSION['selected_db'] ?? ($_COOKIE['selected_db'] ?? $basePorDefecto);
        $seleccion = preg_replace('/[^A-Za-z0-9_]/', '', (string) $seleccion);
        return $seleccion !== '' ? $seleccion : $basePorDefecto;
    }
}

if (!function_exists('obtenerConfiguracionesBases')) {
    function obtenerConfiguracionesBases(): array
    {
        return [
            // <db-connections>
            'scantec_basic' => [
                'host' => 'localhost',
                'port' => '3306',
                'user' => 'scantec',
                'password' => '@Scantec*23',
            ],
            'Printec' => [
                'host' => 'localhost',
                'port' => '3306',
                'user' => 'scantec',
                'password' => '@Scantec*23',
            ],
            // </db-connections>
        ];
    }
}

if (!function_exists('obtenerConexionBaseSeleccionada')) {
    function obtenerConexionBaseSeleccionada(string $baseSeleccionada, string $basePorDefecto): array
    {
        $configuraciones = obtenerConfiguracionesBases();

        if (isset($configuraciones[$baseSeleccionada])) {
            return $configuraciones[$baseSeleccionada];
        }

        if (isset($configuraciones[$basePorDefecto])) {
            return $configuraciones[$basePorDefecto];
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
define('BD_DEFAULT', 'scantec_basic');
define('DB_APP_USER_DEFAULT', 'scantec');
define('DB_APP_PASS_DEFAULT', '@Scantec*23');

define('DB_USER_ROOT', 'root');
define('ROOT_PASS', 'scantec');
