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
    // Ejemplo: http://localhost:8880/scantec-legajosV2Host
    'base_url' => 'http://localhost:8880/scantec-legajosV2Host',

    // Ruta absoluta de la carpeta del proyecto.
    // Si se deja vacia, se detecta automaticamente desde la ruta real del proyecto.
    // Ejemplo detectado: C:/xampp/htdocs/scantec-legajosV2Host/
    'root_path' => '',

    // Identificador del entorno.
    'host' => 'local',

    // Base de datos local.
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'scantec_basic',
    'db_user' => 'scantec',
    'db_password' => '@Scantec*23',

    // Rutas locales de almacenamiento y backups.
    'storage_path' => 'C:/xampp/scantec_storage/',
    'backup_path' => 'C:/xampp/backups_scantec/',

    // Ejecutables del sistema. Puede usar nombre o ruta completa.
    'exiftool_path' => 'C:/xampp/htdocs/Tools/exiftool.exe',
    'magick_path' => 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe',
    'tesseract_path' => 'C:/Program Files/Tesseract-OCR/tesseract.exe',
];
