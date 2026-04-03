<?php
/**
 * CONFIGURACION HOSTING
 *
 * Ajuste estos valores a su servidor real.
 * Recomendado para Hostinger u otro hosting Linux.
 */
return [
    // URL base completa del sitio publicado.
    // Ejemplo: https://su-dominio.com/
    'base_url' => '',

    // Ruta absoluta de public_html o de la carpeta publica real.
    // Ejemplo: /home/usuario/public_html/
    'root_path' => '',

    // Identificador del entorno.
    'host' => 'hosting',

    // Base de datos del hosting.
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'u788392792_scantec',
    'db_user' => 'u788392792_scantecadmin',
    'db_password' => '@Scantec*23',

    // Rutas persistentes del servidor.
    // Idealmente fuera de public_html.
    'storage_path' => '',
    'backup_path' => '',

    // Ejecutables disponibles en Linux.
    'exiftool_path' => '/usr/bin/exiftool',
    'magick_path' => '/usr/bin/magick',
    'tesseract_path' => '/usr/bin/tesseract',
];
