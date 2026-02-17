<?php
    class Reagrupado extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function reagrupado()
        {
            $reagrupar = $this->model->selectReagrupado();
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $data = ['reagrupar' => $reagrupar, 'usuario' => $usuario, 'operador' => $operador];
            $this->views->getView($this, "listar", $data);
        }

        public function reporte()
        {
            $reagrupar = $this->model->selectReagrupado();
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $data = ['reagrupar' => $reagrupar, 'usuario' => $usuario, 'operador' => $operador];
            $this->views->getView($this, "reporte", $data);
        }

        public function registrar()
        {
            $fecha = $_POST['fecha'];
            $solicitado = $_POST['solicitado'];
            $cant_cajas = $_POST['cant_cajas'];
            $observaciones = $_POST['observaciones'];
            $id = $_POST['id'];
            $id_operador = $_POST['id_operador'];
            $insert = $this->model->insertarReagrupado($fecha, $solicitado, $cant_cajas, $observaciones, $id, $id_operador);
            if ($insert) {
            header("location: " . base_url() . "reagrupado");
            die();    
            }
        }

        public function editar()
        {
            $id_reagrup = $_GET['id_reagrup'];
            $reagrupar = $this->model->editReagrupado($id_reagrup);
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $data = ['reagrupar' => $reagrupar, 'operador' => $operador, 'usuario' => $usuario];
            if ($data == 0) {
                $this->preparado();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function modificar()
        {
                $id_reagrup = $_POST['id_reagrup'];
                $fecha = $_POST['fecha'];
                $solicitado = $_POST['solicitado'];
                $cant_cajas = $_POST['cant_cajas'];
                $observaciones = $_POST['observaciones'];
                $id_operador = $_POST['id_operador'];
                $actualizar = $this->model->actualizarReagrupado($fecha, $solicitado, $cant_cajas, $observaciones, $id_operador, $id_reagrup);
                if ($actualizar) {   
                    header("location: " . base_url() . "reagrupado"); 
                    die();
                }
        }
        public function inactivar()
        {
            $id_reagrup = $_POST['id_reagrup'];
            $this->model->estadoReagrupado('INACTIVO', $id_reagrup);
            header("location: " . base_url() . "reagrupado");
            die();
        }
        
        public function reingresar()
        {
            $id_reagrup = $_POST['id_reagrup'];
            $this->model->estadoReagrupado('ACTIVO', $id_reagrup);
            header("location: " . base_url() . "reagrupado");
            die();
        }

        public function pdf()
        {
            $datos = $this->model->selectDatos();
            $control = $this->model->selectReagrupado();
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('Reagrupado');
            $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 275, 5, 20, 20);
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
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(280, 8, "Reagrupado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(13);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C',1);
            $pdf->Cell(25, 5, utf8_decode('Solicitado'), 1, 0, 'C',1);
            $pdf->Cell(25, 5, utf8_decode('Cant. cajas'), 1, 0, 'C',1);
            $pdf->Cell(70, 5, 'Observaciones', 1, 0, 'C',1);
            $pdf->Cell(50, 5,  utf8_decode('Usuario'), 1, 0, 'C',1);
            $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C',1);
          
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($control as $row) {
                $pdf->setX(13);
                $pdf->Cell(15, 5, $row['id_reagrup'], 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
                $pdf->Cell(25, 5, number_format($row['solicitado'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(25, 5, number_format($row['cant_cajas'], 1, '.', ''), 1, 0, 'C');
                $pdf->Cell(70, 5, utf8_decode($row['observaciones']), 1, 0, 'C');
                $pdf->Cell(50, 5, utf8_decode($row['nombre']), 1, 0, 'C');
                $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,185);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Reagrupado.pdf", "I");
        }

        public function pdf_filtroFecha()
        {
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datos = $this->model->selectDatos();
            $control = $this->model->reporteReagrupadoFecha($desde, $hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('Reagrupado');
            $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 275, 5, 20, 20);
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
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(280, 8, "Reagrupado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(13);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C',1);
            $pdf->Cell(25, 5, utf8_decode('Solicitado'), 1, 0, 'C',1);
            $pdf->Cell(25, 5, utf8_decode('Cant. cajas'), 1, 0, 'C',1);
            $pdf->Cell(70, 5, 'Observaciones', 1, 0, 'C',1);
            $pdf->Cell(50, 5,  utf8_decode('Usuario'), 1, 0, 'C',1);
            $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($control as $row) {
                $pdf->setX(13);
                $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
                $pdf->Cell(25, 5, number_format($row['solicitado'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(25, 5, number_format($row['cant_cajas'], 1, '.', ''), 1, 0, 'C');
                $pdf->Cell(70, 5, utf8_decode($row['observaciones']), 1, 0, 'C');
                $pdf->Cell(50, 5, utf8_decode($row['nombre']), 1, 0, 'C');
                $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,185);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Reagrupado.pdf", "I");
        }

        public function pdf_filtroTotal()
        {
            $mes_desde = $_POST['mes_desde'];
            $anio_desde = $_POST['anio_desde'];
            $mes_hasta = $_POST['mes_hasta'];
            $anio_hasta = $_POST['anio_hasta'];
            $datos = $this->model->selectDatos();
            $control = $this->model->reporteReagrupadototal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('Reagrupado');
            $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 190, 5, 20, 20);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->setX(30);
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
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(195, 8, "Reagrupado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(12);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Solicitados'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Cajas totales'), 1, 1, 'C',1);
           // $pdf->Cell(40, 6, utf8_decode('Operador'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($control as $row) {
                $pdf->setX(12);
                $pdf->Cell(25, 5, strtoupper($row['mes_anio']), 1, 0, 'L');                
                $pdf->Cell(30, 5, number_format($row['solicitados'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['cajas_totales'], 1, '.', ''), 1, 1, 'C');
              //  $pdf->Cell(40, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Reagrupado.pdf", "I");
        }

        public function pdf_filtroOperador()
        {
            $id_operador = $_POST['id_operador'];
            $datos = $this->model->selectDatos();
            $control = $this->model->reporteReagrupadoOperador($id_operador);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Reagrupado');
            $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 190, 5, 20, 20);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->setX(30);
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
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(195, 8, "Reagrupado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(20);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Solicitados'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Cajas totales'), 1, 0, 'C',1);
            $pdf->Cell(40, 6, utf8_decode('Operador'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($control as $row) {
                $pdf->setX(20);
                $pdf->Cell(25, 5, strtoupper($row['fecha']), 1, 0, 'L');
                $pdf->Cell(30, 5, number_format($row['solicitados'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['cajas_totales'], 1, '.', ''), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Reagrupado.pdf", "I");
        }

        public function excel()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $reagrupado = $this->model->selectReagrupado();
            $nombre ='reagrupado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Establecer el nombre de la hoja
            $sheet->setTitle($nombre);
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Reagrupado de expedientes')  
                ->setSubject('Reporte de reagrupado')
                ->setDescription('Reporte de expedientes y cajas reagrupado')
                ->setKeywords('reagrupado, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'FECHA');
            $sheet->setCellValue('C1', 'SOLICITADO');
            $sheet->setCellValue('D1', 'CANT CAJAS');
            $sheet->setCellValue('E1', 'OBSERVACIONES');
            $sheet->setCellValue('F1', 'USUARIO');
            $sheet->setCellValue('G1', 'OPERADOR');
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
            $sheet->getStyle('A1:G1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($reagrupado as $value) {
                $sheet->setCellValue('A'.$row, $value["id_reagrup"]);
                $sheet->setCellValue('B'.$row, $value["fecha"]);
                $sheet->setCellValue('C'.$row, $value['solicitado']); 
                $sheet->setCellValue('D'.$row, $value["cant_cajas"]);  
                $sheet->setCellValue('E'.$row, $value["observaciones"]);
                $sheet->setCellValue('F'.$row, $value["nombre"]);
                $sheet->setCellValue('G'.$row, $value["operador"]);
                $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$nombre.'_'.date('Y_m_d_H:_i:_s').'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer->save('php://output');
        }

        public function excel_filtroFecha()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $reagrupado = $this->model->reporteReagrupadoFecha($desde, $hasta);
            $nombre ='reagrupado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Reagrupado de expedientes')  
                ->setSubject('Reporte de reagrupado')
                ->setDescription('Reporte de expedientes y cajas reagrupado')
                ->setKeywords('reagrupados, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'FECHA');
            $sheet->setCellValue('C1', 'SOLICITADO');
            $sheet->setCellValue('D1', 'CANT CAJAS');
            $sheet->setCellValue('E1', 'OBSERVACIONES');
            $sheet->setCellValue('F1', 'USUARIO');
            $sheet->setCellValue('G1', 'OPERADOR');
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
            $sheet->getStyle('A1:G1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($reagrupado as $value) {
                $sheet->setCellValue('A'.$row, $value["id_reagrup"]);
                $sheet->setCellValue('B'.$row, $value["fecha"]);
                $sheet->setCellValue('C'.$row, $value['solicitado']); 
                $sheet->setCellValue('D'.$row, $value["cant_cajas"]);  
                $sheet->setCellValue('E'.$row, $value["observaciones"]);
                $sheet->setCellValue('F'.$row, $value["nombre"]);
                $sheet->setCellValue('G'.$row, $value["operador"]);
                $sheet->getStyle('A'.$row.':G'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$nombre.'_'.date('Y_m_d_H:_i:_s').'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer->save('php://output');
        }

        public function excel_filtroTotal()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $mes_desde = $_POST['mes_desde'];
            $anio_desde = $_POST['anio_desde'];
            $mes_hasta = $_POST['mes_hasta'];
            $anio_hasta = $_POST['anio_hasta'];
            $reagrupado = $this->model->reporteReagrupadototal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
            $nombre ='reagrupado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Reagrupado de expedientes')  
                ->setSubject('Reporte de reagrupado')
                ->setDescription('Reporte de expedientes y cajas reagrupados')
                ->setKeywords('reagrupados, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'SOLICITADOS');
            $sheet->setCellValue('C1', 'CANT CAJAS');
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
            $sheet->getStyle('A1:C1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($reagrupado as $value) {
                $sheet->setCellValue('A'.$row, $value["mes_anio"]);
                $sheet->setCellValue('B'.$row, $value["solicitados"]);
                $sheet->setCellValue('C'.$row, $value['cajas_totales']);
                $sheet->getStyle('A'.$row.':C'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'C') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$nombre.'_'.date('Y_m_d_H:_i:_s').'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer->save('php://output');
        }

        public function excel_filtroOperador()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $id_operador = $_POST['id_operador'];
            $reagrupado = $this->model->reporteReagrupadoOperador($id_operador);
            $nombre ='reagrupado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Reagrupado de expedientes')  
                ->setSubject('Reporte de reagrupado')
                ->setDescription('Reporte de expedientes y cajas reagrupados')
                ->setKeywords('reagrupados, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'SOLICITADOS');
            $sheet->setCellValue('C1', 'CANT CAJAS');
            $sheet->setCellValue('D1', 'OPERADOR');
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
            $sheet->getStyle('A1:D1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($reagrupado as $value) { 
                $sheet->setCellValue('A'.$row, $value["fecha"]);
                $sheet->setCellValue('B'.$row, $value["solicitados"]);
                $sheet->setCellValue('C'.$row, $value['cajas_totales']);
                $sheet->setCellValue('D'.$row, $value['operador']);
                $sheet->getStyle('A'.$row.':D'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$nombre.'_'.date('Y_m_d_H:_i:_s').'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer->save('php://output');
        }

       
}