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

    public function output(string $nombreArchivo)
    {
        // LIMPIEZA DE BUFFER (Obligatorio para evitar archivo corrupto)
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$nombreArchivo.'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }
}