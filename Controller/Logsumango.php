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

    public function registrar()
    {
        //$codigo = $_POST['codigo'];
        $documento = $_POST['documento'];
        $nombre = $_POST['nombre'];
        //$carrera = $_POST['carrera'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $insert = $this->model->insertarFuncionario($documento, $nombre, $direccion, $telefono);
        if ($insert) {
            header("location: " . base_url() . "funcionarios");
            die();
        }
    }
    public function editar()
    {
        $id = $_GET['id'];
        $data = $this->model->editFuncionario($id);
        if ($data == 0) {
            $this->funcionarios();
        } else {
            $this->views->getView($this, "editar", $data);
        }
    }
    public function modificar()
    {
        $id = $_POST['id'];
        $documento = $_POST['documento'];
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $actualizar = $this->model->actualizarFuncionario($documento, $nombre, $direccion, $telefono, $id);
        if ($actualizar) {
            header("location: " . base_url() . "funcionarios");
            die();
        }
    }

    public function eliminar()
    {
        $id = $_POST['id'];
        $this->model->estadoFuncionario(0, $id);
        header("location: " . base_url() . "funcionarios");
        die();
    }
    public function reingresar()
    {
        $id = $_POST['id'];
        $this->model->estadoFuncionario(1, $id);
        header("location: " . base_url() . "funcionarios");
        die();
    }

    public function pdf()
    {
        $datos = $this->model->selectDatos();
        $logsumango = $this->model->selectLogsumango();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'LEGAL');
        $pdf->AddPage();
        $pdf->SetMargins(10, 5, 5);
        // Agregar metadatos
        $pdf->SetTitle('LOGS UMANGO');
        $pdf->SetAuthor('SCANTEC' . $_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() . 'Assets/img/icoScantec2.png', 10, 7, 33);
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 320, 5, 20, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setX(20);
        $pdf->Ln();
        $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(338, 8, "Logs Umango", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(10);
        $pdf->Cell(15, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('ID proceso'), 1, 0, 'C', 1);
        $pdf->Cell(20, 6, utf8_decode('Id lote'), 1, 0, 'C', 1);
        $pdf->Cell(28, 6, 'Fuente Capt.', 1, 0, 'C', 1);
        $pdf->Cell(110, 6, 'Archivo Orig.', 1, 0, 'C', 1);
        $pdf->Cell(15, 6, utf8_decode('Pág.'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Fecha', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Usuario', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Host', 1, 1, 'C', 1);
        // $pdf->Cell(30, 6, utf8_decode('Páginas'), 1, 1, 'C',1);
        // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');

        $pdf->SetFont('Arial', '', 8);
        //     $contador = 1;
        foreach ($logsumango as $row) {
            $pdf->setX(10);
            $pdf->Cell(15, 6, $row['idlog_umango'], 1, 0, 'C');
            $pdf->Cell(45, 6, utf8_decode($row['id_proceso_umango']), 1, 0, 'C');
            $pdf->Cell(20, 6, utf8_decode($row['id_lote']), 1, 0, 'C');
            $pdf->Cell(28, 6, utf8_decode($row['fuente_captura']), 1, 0, 'C');
            $pdf->Cell(110, 6, utf8_decode($row['archivo_origen']), 1, 0, 'J');
            $pdf->Cell(15, 6, utf8_decode($row['paginas_exportadas']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['fecha_inicio']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['usuario']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['nombre_host']), 1, 1, 'C');
            //$pdf->Cell(30, 5, utf8_decode($row['total_pag']), 1, 1, 'C');

            // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');

            //  $contador++;

        }
        //$pdf->tablaHorizontal($miCabecera, $misDatos);
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output("Logs Umango.pdf", "I");
    }

    public function excel_fecha()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $desde = $_POST['desde'];
        $logsumango = $this->model->reporteLogFecha($desde);
        $nombre = 'logsumango';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs umango');
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');
        $sheet->setCellValue('A1', 'ID PROCESO');
        $sheet->setCellValue('B1', 'NRO LOTE');
        $sheet->setCellValue('C1', 'FUENTE CAPTURA');
        $sheet->setCellValue('D1', 'ARCHIVO ORIG');
        $sheet->setCellValue('E1', 'ORDEN DOC.');
        $sheet->setCellValue('F1', 'PAGINAS');
        $sheet->setCellValue('G1', 'FECHA INICIO');
        $sheet->setCellValue('H1', 'FECHA FINALIZACION');
        $sheet->setCellValue('I1', 'USUARIO IMPORT.');
        $sheet->setCellValue('J1', 'USUARIO EXPORT.');
        $sheet->setCellValue('K1', 'STATUS');
        $sheet->setCellValue('L1', 'NOMBRE HOST');
        $sheet->setCellValue('M1', 'DIRECCION IP');
        // Obtener el estilo de la celda A1 y aplicar color de fondo
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];
        // Establecer estilo de fuente para la cabecera
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];
        // Establecer estilo de fuente para el resto del contenido
        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($logsumango as $value) {
            $sheet->setCellValue('A' . $row, $value["id_proceso_umango"]);
            $sheet->setCellValue('B' . $row, $value['id_lote']);
            $sheet->setCellValue('C' . $row, $value["fuente_captura"]);
            $sheet->setCellValue('D' . $row, $value["archivo_origen"]);
            $sheet->setCellValue('E' . $row, $value["orden_documento"]);
            $sheet->setCellValue('F' . $row, $value["paginas_exportadas"]);
            $sheet->setCellValue('G' . $row, $value["fecha_inicio"]);
            $sheet->setCellValue('H' . $row, $value["fecha_finalizacion"]);
            $sheet->setCellValue('I' . $row, $value["creador"]);
            $sheet->setCellValue('J' . $row, $value["usuario"]);
            $sheet->setCellValue('K' . $row, $value["estado"]);
            $sheet->setCellValue('L' . $row, $value["nombre_host"]);
            $sheet->setCellValue('M' . $row, $value["ip_host"]);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($contentStyle);
            $row++;
        }
        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }

    public function excel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $logsumango = $this->model->selectLogsumango();
        $nombre = 'logsumango';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs umango');
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');
        $sheet->setCellValue('A1', 'ID PROCESO');
        $sheet->setCellValue('B1', 'NRO LOTE');
        $sheet->setCellValue('C1', 'FUENTE CAPTURA');
        $sheet->setCellValue('D1', 'ARCHIVO ORIG');
        $sheet->setCellValue('E1', 'ORDEN DOC.');
        $sheet->setCellValue('F1', 'PAGINAS');
        $sheet->setCellValue('G1', 'FECHA INICIO');
        $sheet->setCellValue('H1', 'FECHA FINALIZACION');
        $sheet->setCellValue('I1', 'USUARIO IMPORT.');
        $sheet->setCellValue('J1', 'USUARIO EXPORT.');
        $sheet->setCellValue('K1', 'STATUS');
        $sheet->setCellValue('L1', 'NOMBRE HOST');
        $sheet->setCellValue('M1', 'DIRECCION IP');
        // Obtener el estilo de la celda A1 y aplicar color de fondo
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];
        // Establecer estilo de fuente para la cabecera
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];
        // Establecer estilo de fuente para el resto del contenido
        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];
        $sheet->getStyle('A1:M1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($logsumango as $value) {
            $sheet->setCellValue('A' . $row, $value["id_proceso_umango"]);
            $sheet->setCellValue('B' . $row, $value['id_lote']);
            $sheet->setCellValue('C' . $row, $value["fuente_captura"]);
            $sheet->setCellValue('D' . $row, $value["archivo_origen"]);
            $sheet->setCellValue('E' . $row, $value["orden_documento"]);
            $sheet->setCellValue('F' . $row, $value["paginas_exportadas"]);
            $sheet->setCellValue('G' . $row, $value["fecha_inicio"]);
            $sheet->setCellValue('H' . $row, $value["fecha_finalizacion"]);
            $sheet->setCellValue('I' . $row, $value["creador"]);
            $sheet->setCellValue('J' . $row, $value["usuario"]);
            $sheet->setCellValue('K' . $row, $value["estado"]);
            $sheet->setCellValue('L' . $row, $value["nombre_host"]);
            $sheet->setCellValue('M' . $row, $value["ip_host"]);
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray($contentStyle);
            $row++;
        }
        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }
}
