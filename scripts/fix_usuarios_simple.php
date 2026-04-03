<?php

$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Usuarios.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "No se pudo leer Usuarios.php\n");
    exit(1);
}

$replacements = [
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ãº' => 'ú',
    'Ã±' => 'ñ',
    'Ã' => 'Á',
    'Ã‰' => 'É',
    'Ã' => 'Í',
    'Ã“' => 'Ó',
    'Ãš' => 'Ú',
    'Ã‘' => 'Ñ',
    'Â¡' => '¡',
    'Â¿' => '¿',
    'Â°' => '°',
];

$updated = strtr($content, $replacements);
file_put_contents($path, $updated);

echo "Usuarios.php simplificado\n";
