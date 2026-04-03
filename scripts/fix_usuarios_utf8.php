<?php

$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . 'Usuarios.php';
$content = file_get_contents($path);

if ($content === false) {
    fwrite(STDERR, "No se pudo leer Usuarios.php\n");
    exit(1);
}

$markers = ['Ã', 'Â', 'â€', 'â€¦', 'ï¿½', '�'];

$scoreLine = static function (string $line) use ($markers): int {
    $score = 0;
    foreach ($markers as $marker) {
        $score += substr_count($line, $marker);
    }
    return $score;
};

$lines = preg_split("/\r\n|\n|\r/", $content);
if ($lines === false) {
    fwrite(STDERR, "No se pudieron procesar las líneas\n");
    exit(1);
}

$changed = 0;

foreach ($lines as $index => $line) {
    $best = $line;
    $bestScore = $scoreLine($line);

    if ($bestScore === 0) {
        continue;
    }

    $current = $line;

    for ($i = 0; $i < 4; $i++) {
        $converted = @mb_convert_encoding($current, 'Windows-1252', 'UTF-8');
        if (!is_string($converted) || $converted === '' || $converted === $current) {
            break;
        }

        $convertedScore = $scoreLine($converted);
        if ($convertedScore < $bestScore) {
            $best = $converted;
            $bestScore = $convertedScore;
        }

        $current = $converted;
        if ($bestScore === 0) {
            break;
        }
    }

    if ($best !== $line) {
        $lines[$index] = $best;
        $changed++;
    }
}

$fixed = implode(PHP_EOL, $lines);

// Ajustes puntuales que suelen sobrevivir a la recodificación automática.
$fixed = str_replace('Ã‚Â¡', '¡', $fixed);
$fixed = str_replace('INSERCIÃƒâ€œN', 'INSERCIÓN', $fixed);
$fixed = str_replace('VALIDACIÃƒâ€œN', 'VALIDACIÓN', $fixed);
$fixed = str_replace('RESOLUCIÃƒâ€œN', 'RESOLUCIÓN', $fixed);
$fixed = str_replace('DETECCIÃƒâ€œN', 'DETECCIÓN', $fixed);
$fixed = str_replace('DESENCRIPTAR CONTRASEÃƒâ€˜A', 'DESENCRIPTAR CONTRASEÑA', $fixed);

file_put_contents($path, $fixed);
echo "Usuarios.php procesado. Lineas ajustadas: {$changed}" . PHP_EOL;
