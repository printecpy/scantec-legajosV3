<?php

declare(strict_types=1);

$mode = $argv[1] ?? '--staged';
$validModes = ['--staged', '--worktree'];
if (!in_array($mode, $validModes, true)) {
    fwrite(STDERR, "Uso: php tools/check_encoding.php [--staged|--worktree]\n");
    exit(2);
}

$patterns = [
    '/\xEF\xBF\xBD/' => 'caracter de reemplazo UTF-8',
    '/Ã|Â|â€|â€œ|â€\x9D|â€™|â€“|â€”|ï¿½|�/' => 'mojibake tipico de UTF-8/ANSI',
];

$extensions = ['php', 'phtml', 'js', 'css', 'html', 'htm', 'json', 'xml', 'md', 'txt'];
$diffs = $mode === '--staged'
    ? [['git', ['diff', '--cached', '--unified=0', '--no-ext-diff', '--']]]
    : [
        ['git', ['diff', '--cached', '--unified=0', '--no-ext-diff', '--']],
        ['git', ['diff', '--unified=0', '--no-ext-diff', '--']],
    ];

$errors = [];

foreach ($diffs as [$bin, $args]) {
    $cmd = escapeshellarg($bin);
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }

    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "No se pudo ejecutar git diff para revisar codificacion.\n");
        exit(2);
    }

    $file = '';
    $lineNumber = 0;
    foreach ($output as $line) {
        if (str_starts_with($line, '+++ b/')) {
            $file = substr($line, 6);
            $lineNumber = 0;
            continue;
        }

        if (preg_match('/^\@\@ -\d+(?:,\d+)? \+(\d+)(?:,\d+)? \@\@/', $line, $match)) {
            $lineNumber = (int)$match[1] - 1;
            continue;
        }

        if ($line === '' || $line[0] !== '+' || str_starts_with($line, '+++')) {
            continue;
        }

        $lineNumber++;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, $extensions, true)) {
            continue;
        }

        $added = substr($line, 1);
        if (!preg_match('//u', $added)) {
            $errors[] = "{$file}:{$lineNumber} contiene bytes que no son UTF-8 valido.";
            continue;
        }

        foreach ($patterns as $pattern => $reason) {
            if (preg_match($pattern, $added) === 1) {
                $errors[] = "{$file}:{$lineNumber} contiene {$reason}.";
                break;
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Revision de codificacion fallida:\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - {$error}\n");
    }
    fwrite(STDERR, "Guardá los archivos como UTF-8 y corregí tildes/mojibake antes de continuar.\n");
    exit(1);
}

echo "Revision de codificacion OK.\n";
