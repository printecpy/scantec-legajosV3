<?php
$rutaAutoload = __DIR__ . '/../Libraries/vendor/autoload.php';

if (file_exists($rutaAutoload)) {
    require_once $rutaAutoload;
} else {
    die("Error Crítico: No se encuentra el autoload de Composer en: " . $rutaAutoload);
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportTemplateExcel
{
    protected $spreadsheet;
    protected $sheet;

    public function __construct(string $titulo = 'Reporte', string $empresa = 'SCANTEC')
    {
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy($empresa)
            ->setTitle($titulo);

        // Fuente base
        $this->spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $this->spreadsheet->getDefaultStyle()->getFont()->setSize(10);

        $this->spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $this->spreadsheet->getDefaultStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle(substr($titulo, 0, 30)); // Excel limita nombres de hoja a 31 chars

        $this->setHeader($titulo, $empresa);
    }

    private function setHeader($titulo, $empresa)
    {
        // Fila 1: Nombre Empresa
        $this->sheet->mergeCells('A1:F1');
        $this->sheet->setCellValue('A1', strtoupper($empresa));
        $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $this->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fila 2: Título Reporte
        $this->sheet->mergeCells('A2:F2');
        $this->sheet->setCellValue('A2', strtoupper($titulo));
        $this->sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $this->sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function getSheet()
    {
        return $this->sheet;
    }

    public function createSheet()
    {
        return $this->spreadsheet->createSheet();
    }

    public function setColumnWidths(array $widths)
    {
        foreach ($widths as $col => $width) {
            if ($width === 'auto') {
                // Si es auto, Excel decide el ancho (ideal para fechas o números cortos)
                $this->sheet->getColumnDimension($col)->setAutoSize(true);
            } else {
                // Si es un número, fuerza ese ancho máximo. Al llegar al límite, el texto bajará de renglón
                $this->sheet->getColumnDimension($col)->setWidth($width);
            }
        }
    }

    public function output(string $nombreArchivo)
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '.xlsx"');
        header('Cache-Control: max-age=0');

        //Guardar archivo
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}