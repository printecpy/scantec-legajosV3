<?php
class Logsumango extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    public function views()
    {
        $data = $this->model->selectLogsumango();
        $this->views->getView($this, "listar", $data);
    }

    public function reporte()
    {
        $logsumango = $this->model->selectLogsumango();
        $data = ['logsumango' => $logsumango];
        $this->views->getView($this, "reporte", $data);
    }

   public function pdf()
    {
        if (ob_get_length()) ob_end_clean();
        
        $logsumango = $this->model->selectLogsumango();

        // Llamada a la nueva plantilla
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Logs Umango', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras (Suma total 338)
        $pdf->Cell(15, 7, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(45, 7, utf8_decode('ID proceso'), 1, 0, 'C', 1);
        $pdf->Cell(20, 7, utf8_decode('Id lote'), 1, 0, 'C', 1);
        $pdf->Cell(28, 7, 'Fuente Capt.', 1, 0, 'C', 1);
        $pdf->Cell(110, 7, 'Archivo Orig.', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Pág.'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fecha', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Usuario', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Host', 1, 1, 'C', 1);

        // Configurar anchos para el bucle (Motor Multilínea)
        $pdf->SetWidths(array(15, 45, 20, 28, 110, 15, 35, 35, 35));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'L', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($logsumango as $row) {
            $pdf->Row(array(
                $row['idlog_umango'],
                utf8_decode($row['id_proceso_umango']),
                utf8_decode($row['id_lote']),
                utf8_decode($row['fuente_captura']),
                utf8_decode($row['archivo_origen']),
                utf8_decode($row['paginas_exportadas']),
                utf8_decode($row['fecha_inicio']),
                utf8_decode($row['usuario']),
                utf8_decode($row['nombre_host'])
            ));
        }

        $pdf->Output("Logs_Umango_" . date('Y_m_d_H_i_s') . ".pdf", "I");
    }

    public function excel_fecha()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $desde = $_POST['desde'];
        $logsumango = $this->model->reporteLogFecha($desde);

        $excel = new ReportTemplateExcel('LOGS UMANGO POR FECHA', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Cabeceras en la fila 4
        $headerRow = 4;
        $headers = ['ID PROCESO', 'NRO LOTE', 'FUENTE CAPTURA', 'ARCHIVO ORIGEN', 'ORDEN DOC.', 'PAGINAS', 'FECHA INICIO', 'FECHA FINALIZACION', 'USUARIO IMPORT.', 'USUARIO EXPORT.', 'STATUS', 'NOMBRE HOST', 'DIRECCION IP'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:M$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1; // Fila 5

        foreach ($logsumango as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["id_proceso_umango"]);
            $sheet->setCellValue('B' . $dataRow, $value['id_lote']);
            $sheet->setCellValue('C' . $dataRow, $value["fuente_captura"]);
            $sheet->setCellValue('D' . $dataRow, $value["archivo_origen"]);
            $sheet->setCellValue('E' . $dataRow, $value["orden_documento"]);
            $sheet->setCellValue('F' . $dataRow, $value["paginas_exportadas"]);
            $sheet->setCellValue('G' . $dataRow, $value["fecha_inicio"]);
            $sheet->setCellValue('H' . $dataRow, $value["fecha_finalizacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["creador"]);
            $sheet->setCellValue('J' . $dataRow, $value["usuario"]);
            $sheet->setCellValue('K' . $dataRow, $value["estado"]);
            $sheet->setCellValue('L' . $dataRow, $value["nombre_host"]);
            $sheet->setCellValue('M' . $dataRow, $value["ip_host"]);
            $sheet->getStyle('A' . $dataRow . ':M' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 'auto',
            'C' => 'auto',
            'D' => 60, // Archivo origen: Ancho fijo para que baje de renglón si es muy largo
            'E' => 'auto',
            'F' => 'auto',
            'G' => 'auto',
            'H' => 'auto',
            'I' => 'auto',
            'J' => 'auto',
            'K' => 'auto',
            'L' => 'auto',
            'M' => 'auto'
        ]);

        $nombreArchivo = 'Logs_Umango_Por_Fecha_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $logsumango = $this->model->selectLogsumango();

        $excel = new ReportTemplateExcel('REPORTE GENERAL LOGS UMANGO', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Cabeceras en la fila 4
        $headerRow = 4;
        $headers = ['ID PROCESO', 'NRO LOTE', 'FUENTE CAPTURA', 'ARCHIVO ORIGEN', 'ORDEN DOC.', 'PAGINAS', 'FECHA INICIO', 'FECHA FINALIZACION', 'USUARIO IMPORT.', 'USUARIO EXPORT.', 'STATUS', 'NOMBRE HOST', 'DIRECCION IP'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:M$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($logsumango as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["id_proceso_umango"]);
            $sheet->setCellValue('B' . $dataRow, $value['id_lote']);
            $sheet->setCellValue('C' . $dataRow, $value["fuente_captura"]);
            $sheet->setCellValue('D' . $dataRow, $value["archivo_origen"]);
            $sheet->setCellValue('E' . $dataRow, $value["orden_documento"]);
            $sheet->setCellValue('F' . $dataRow, $value["paginas_exportadas"]);
            $sheet->setCellValue('G' . $dataRow, $value["fecha_inicio"]);
            $sheet->setCellValue('H' . $dataRow, $value["fecha_finalizacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["creador"]);
            $sheet->setCellValue('J' . $dataRow, $value["usuario"]);
            $sheet->setCellValue('K' . $dataRow, $value["estado"]);
            $sheet->setCellValue('L' . $dataRow, $value["nombre_host"]);
            $sheet->setCellValue('M' . $dataRow, $value["ip_host"]);
            $sheet->getStyle('A' . $dataRow . ':M' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 'auto',
            'C' => 'auto',
            'D' => 60, // Archivo origen: Ancho fijo para que baje de renglón si es muy largo
            'E' => 'auto',
            'F' => 'auto',
            'G' => 'auto',
            'H' => 'auto',
            'I' => 'auto',
            'J' => 'auto',
            'K' => 'auto',
            'L' => 'auto',
            'M' => 'auto'
        ]);

        $nombreArchivo = 'Logs_encaminador_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }
}
