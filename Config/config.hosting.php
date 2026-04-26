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
    'base_url' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_BASE_URL', '') : '',

    // Ruta absoluta de public_html o de la carpeta publica real.
    // Ejemplo: /home/usuario/public_html/
    'root_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_ROOT_PATH', '') : '',

    // Identificador del entorno.
    'host' => 'hosting',

    // Base de datos del hosting.
    'db_host' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_HOST', 'localhost') : 'localhost',
    'db_port' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_PORT', '3306') : '3306',
    'db_name' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_NAME', '') : '',
    'db_user' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USER', '') : '',
    'db_password' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_PASSWORD', '') : '',

    // Base de datos de usuarios/clientes.
    'db_usuarios_host' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_HOST', 'localhost') : 'localhost',
    'db_usuarios_port' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_PORT', '3306') : '3306',
    'db_usuarios_name' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_NAME', '') : '',
    'db_usuarios_user' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_USER', '') : '',
    'db_usuarios_password' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_PASSWORD', '') : '',
    'db_usuarios_enabled' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_ENABLED', '0') : '0',
    'db_usuarios_table' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_TABLE', 'usuarios_datos') : 'usuarios_datos',
    'db_usuarios_field_id' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_ID', 'id') : 'id',
    'db_usuarios_field_nombre' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_NOMBRE', 'nombre') : 'nombre',
    'db_usuarios_field_apellido' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_APELLIDO', 'apellido') : 'apellido',
    'db_usuarios_field_nombre_completo' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_NOMBRE_COMPLETO', '') : '',
    'db_usuarios_field_ci' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_CI', 'nro_cedula') : 'nro_cedula',
    'db_usuarios_field_solicitud' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USUARIOS_FIELD_SOLICITUD', 'nro_solicitud') : 'nro_solicitud',

    // Rutas persistentes del servidor.
    // Idealmente fuera de public_html.
    'storage_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_STORAGE_PATH', '') : '',
    'backup_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_BACKUP_PATH', '') : '',

    // Ejecutables disponibles en Linux.
    'exiftool_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_EXIFTOOL_PATH', '/usr/bin/exiftool') : '/usr/bin/exiftool',
    'magick_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_MAGICK_PATH', '/usr/bin/magick') : '/usr/bin/magick',
    'tesseract_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_TESSERACT_PATH', '/usr/bin/tesseract') : '/usr/bin/tesseract',
];
