<?php

$baseDir = dirname(__DIR__);

$files = [
    'Controller/Usuarios.php',
    'Controller/Expedientes.php',
    'Controller/Legajos.php',
    'Controller/Configuracion.php',
    'Models/UsuariosModel.php',
    'Models/SeguridadLegajosModel.php',
    'Models/EscaneosModel.php',
    'Models/ConfiguracionModel.php',
];

function mojibakeScore(string $text): int
{
    preg_match_all('/Ã|Â|â€|â€¦|�/u', $text, $matches);
    return count($matches[0]);
}

function fixMojibakeLine(string $line): string
{
    $current = $line;
    $best = $line;
    $bestScore = mojibakeScore($line);

    for ($i = 0; $i < 4; $i++) {
        if ($bestScore === 0) {
            break;
        }

        $converted = @mb_convert_encoding($current, 'Windows-1252', 'UTF-8');
        if (!is_string($converted) || $converted === '' || $converted === $current) {
            break;
        }

        $score = mojibakeScore($converted);
        if ($score < $bestScore) {
            $best = $converted;
            $bestScore = $score;
        }

        $current = $converted;
    }

    return $best;
}

foreach ($files as $file) {
    $path = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $content = file_get_contents($path);
    if ($content === false) {
        fwrite(STDERR, "No se pudo leer {$file}\n");
        continue;
    }

    $lines = preg_split("/(\r\n|\n|\r)/", $content);
    if ($lines === false) {
        fwrite(STDERR, "No se pudieron dividir líneas en {$file}\n");
        continue;
    }

    $updated = [];
    $changes = 0;

    foreach ($lines as $line) {
        if (mojibakeScore($line) > 0) {
            $fixed = fixMojibakeLine($line);
            if ($fixed !== $line) {
                $changes++;
            }
            $updated[] = $fixed;
        } else {
            $updated[] = $line;
        }
    }

    if ($changes > 0) {
        $newContent = implode(PHP_EOL, $updated);
        file_put_contents($path, $newContent);
        echo "Actualizado: {$file} ({$changes} líneas)" . PHP_EOL;
    } else {
        echo "Sin cambios: {$file}" . PHP_EOL;
    }
}
