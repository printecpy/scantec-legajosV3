<?php
/**
 * Genera (o rota) el JWT_SECRET de esta instalación en el .env.
 * Uso:  php tools/generar_jwt_secret.php           -> solo si está vacío
 *       php tools/generar_jwt_secret.php --rotar   -> reemplaza el actual
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$rutaEnv = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (!is_file($rutaEnv) || !is_writable($rutaEnv)) {
    fwrite(STDERR, "No se encontró el .env o no se puede escribir: {$rutaEnv}\n");
    exit(1);
}

$rotar = in_array('--rotar', $argv, true);
$contenido = file_get_contents($rutaEnv);
$nuevo = bin2hex(random_bytes(64));   // 64 bytes aleatorios seguros = 128 caracteres hex

if (preg_match('/^JWT_SECRET=([^\r\n]*)/m', $contenido, $m)) {
    $actual = trim($m[1], " \t\"'");
    if ($actual !== '' && !$rotar) {
        echo "JWT_SECRET ya está definido. Use --rotar para reemplazarlo.\n";
        exit(0);
    }
    $contenido = preg_replace('/^JWT_SECRET=[^\r\n]*/m', 'JWT_SECRET=' . $nuevo, $contenido, 1);
} else {
    $contenido = rtrim($contenido, "\r\n") . PHP_EOL . 'JWT_SECRET=' . $nuevo . PHP_EOL;
}

copy($rutaEnv, $rutaEnv . '.bak-' . date('Ymd-His'));   // respaldo antes de modificar
file_put_contents($rutaEnv, $contenido, LOCK_EX);

echo 'JWT_SECRET ' . ($rotar ? 'rotado' : 'generado') . " correctamente (no se muestra por seguridad).\n";