<?php
/**
 * CONFIGURACION LOCAL
 *
 * Ajuste estos valores para su entorno de desarrollo local.
 * Recomendado para XAMPP o instalaciones en su PC.
 */
return [
    // URL base completa del proyecto local.
    // Si ya conoce la URL exacta, conviene dejarla fija para evitar errores.
    // Ejemplo: https://192.168.100.35:8881/scantec-legajosV2Host
    'base_url' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_BASE_URL', '') : '',

    // Ruta absoluta de la carpeta del proyecto.
    // Si se deja vacia, se detecta automaticamente desde la ruta real del proyecto.
    // Ejemplo detectado: C:/xampp/htdocs/scantec-legajosV2Host/
    'root_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_ROOT_PATH', '') : '',

    // Identificador del entorno.
    'host' => 'local',

    // Base de datos local (legajos).
    'db_host' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_HOST', 'localhost') : 'localhost',
    'db_port' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_PORT', '3306') : '3306',
    'db_name' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_NAME', '') : '',
    'db_user' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_USER', '') : '',
    'db_password' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_DB_PASSWORD', '') : '',

    // Base de datos de usuarios (para buscar datos de clientes).
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

    // Rutas locales de almacenamiento y backups.
    'storage_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_STORAGE_PATH', 'C:/xampp/scantec_storage/') : 'C:/xampp/scantec_storage/',
    'backup_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_BACKUP_PATH', 'C:/xampp/backups_scantec/') : 'C:/xampp/backups_scantec/',

    // Ejecutables del sistema. Puede usar nombre o ruta completa.
    'exiftool_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_EXIFTOOL_PATH', 'C:/xampp/htdocs/Tools/exiftool.exe') : 'C:/xampp/htdocs/Tools/exiftool.exe',
    'magick_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_MAGICK_PATH', 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe') : 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe',
    'tesseract_path' => function_exists('scantecEnv') ? scantecEnv('SCANTEC_TESSERACT_PATH', 'C:/Program Files/Tesseract-OCR/tesseract.exe') : 'C:/Program Files/Tesseract-OCR/tesseract.exe',
];
