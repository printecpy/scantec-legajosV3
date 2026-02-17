<?php
include "../../Config/Config.php";

$archivo = $_GET['archivo'] ?? null;
$ubicacion = RUTA_BASE;

if (!$archivo || !preg_match('/^[\w,\s-]+\.(pdf)$/i', $archivo)) {
    http_response_code(400);
    echo "Archivo inválido.";
    exit;
}

$ruta = $ubicacion . $archivo;

if (!file_exists($ruta)) {
    http_response_code(404);
    echo "Archivo no encontrado.";
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($archivo) . '"');
readfile($ruta);
exit;
