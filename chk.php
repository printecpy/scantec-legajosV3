<?php
// Solo verificar que el método está disponible y FPDF existe
$contenido = file_get_contents('Controller/Legajos.php');

echo "=== Método generar_pdf_texto ===\n";
echo strpos($contenido, 'public function generar_pdf_texto') !== false ? "EXISTE\n" : "NO EXISTE\n";

echo "\n=== crearInstanciaPdf ===\n";
preg_match('/function crearInstanciaPdf\(\).*?\{(.{0,600})\}/s', $contenido, $m);
echo substr($m[1] ?? 'NO ENCONTRADO', 0, 600) . "\n";

echo "\n=== Archivos FPDF/FPDI ===\n";
$archivos = [
    'Libraries/fpdf/fpdf.php',
    'Libraries/fpdi/fpdf.php',
    'Libraries/fpdi/src/autoload.php',
    'Libraries/fpdi/src/Fpdi.php',
    'Libraries/fpdi/Fpdi.php',
];
foreach ($archivos as $f) {
    echo "  $f: " . (file_exists($f) ? 'EXISTE' : 'NO') . "\n";
}

echo "\n=== Todas las libs con fpdf/fpdi ===\n";
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('Libraries'));
foreach ($iter as $file) {
    $name = strtolower($file->getFilename());
    if (strpos($name, 'fpdf') !== false || strpos($name, 'fpdi') !== false) {
        echo "  " . $file->getPathname() . "\n";
    }
}
