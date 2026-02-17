<?php
    class Indexar extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function indexar()
        {
            $indexado = $this->model->selectIndexado();
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $estTrabajo = $this->model->selectEstTrabajo();
            $data = ['indexado' => $indexado, 'usuario' => $usuario, 'operador' => $operador, 'estTrabajo' => $estTrabajo];
            $this->views->getView($this, "listar", $data);
        }
        
        public function reporte()
        {
            $indexado = $this->model->selectIndexado();
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $estTrabajo = $this->model->selectEstTrabajo();
            $data = ['indexado' => $indexado, 'usuario' => $usuario, 'operador' => $operador, 'estTrabajo' => $estTrabajo];
            $this->views->getView($this, "reporte", $data);
        }

        public function registrar()
        {
            $fecha = $_POST['fecha'];
            $pag_index = $_POST['pag_index'];
            $exp_index = $_POST['exp_index'];
            $id_est = $_POST['id_est'];
            $id = $_POST['id'];
            $id_operador = $_POST['id_operador'];
            $insert = $this->model->insertarIndexado($fecha, $pag_index, $exp_index, $id_est, $id, $id_operador);
            if ($insert) {
            header("location: " . base_url() . "indexar");
            die();    
            }
        }

        public function editar()
        {
            $id_index = $_GET['id_index'];
            $indexado = $this->model->editIndexar($id_index);
            $operador = $this->model->selectOperador();
            $usuario = $this->model->selectUsuarios();
            $estTrabajo = $this->model->selectEstTrabajo();
            $data = ['indexado' => $indexado, 'operador' => $operador, 'usuario' => $usuario, 'estTrabajo' => $estTrabajo];
            if ($data == 0) {
                $this->indexar();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function modificar()
        {
            $id_index = $_POST['id_index'];
            $fecha = $_POST['fecha'];
            $pag_index = $_POST['pag_index'];
            $exp_index = $_POST['exp_index'];
            $id_est = $_POST['id_est'];
            $id_operador = $_POST['id_operador'];
            $actualizar = $this->model->actualizarIndexado($fecha, $pag_index, $exp_index, $id_est, $id_operador, $id_index);
            if ($actualizar) {   
                    header("location: " . base_url() . "indexar"); 
                    die();
            }
        }

        public function inactivar()
        {
            $id_index  = $_POST['id_index'];
            $this->model->estadoIndexado('INACTIVO', $id_index);
            header("location: " . base_url() . "indexar");
            die();
        }
        
        public function reingresar()
        {
            $id_index = $_POST['id_index'];
            $this->model->estadoIndexado('ACTIVO', $id_index);
            header("location: " . base_url() . "indexar");
            die();
        }

        public function pdf()
        {
            $datos = $this->model->selectDatos();
            $indexado = $this->model->selectIndexado();
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Indexado de expedientes');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 275, 5, 20, 20);
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
            $pdf->Cell(280, 8, "Indexado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(13);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Pág. indexadas'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Exp. indexados'), 1, 0, 'C',1);
            $pdf->Cell(50, 5, 'Est. de Trabajo', 1, 0, 'C',1);
            $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C',1);
           // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($indexado as $row) {
                $pdf->setX(13);
                $pdf->Cell(15, 5, $row['id_index'], 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['pag_index'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['exp_index'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(50, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
                $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,185);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Indexado.pdf", "D");
        }

        public function pdf_filtroFecha()
        {
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datos = $this->model->selectDatos();
            $indexado = $this->model->reporteIndexFecha($desde, $hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Indexado de expedientes');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg', 275, 5, 20, 20);
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
            $pdf->Cell(280, 8, "Indexado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(13);
            $pdf->SetFont('Arial', '', 10);
           // $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Pág. indexadas'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Exp. indexados'), 1, 0, 'C',1);
            $pdf->Cell(50, 5, 'PC', 1, 0, 'C',1);
            $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C',1);
           // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($indexado as $row) {
                $pdf->setX(13);
               // $pdf->Cell(15, 5, $row['id_index'], 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['pag_index'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['exp_index'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(50, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
                $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,185);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Indexado.pdf", "D");
        }

        public function pdf_filtroTotal()
        {
            $mes_desde = $_POST['mes_desde'];
            $anio_desde = $_POST['anio_desde'];
            $mes_hasta = $_POST['mes_hasta'];
            $anio_hasta = $_POST['anio_hasta'];
            $datos = $this->model->selectDatos();
            $indexado = $this->model->reporteIndextotal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Indexado de expedientes');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
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
            $pdf->Cell(195, 8, "Indexado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(12);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Pág. control.'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Exp. control.'), 1, 1, 'C',1);
            //$pdf->Cell(40, 6, utf8_decode('Operador'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($indexado as $row) {
                $pdf->setX(12);
                $pdf->Cell(25, 5, strtoupper($row['mes_anio']), 1, 0, 'L');
                $pdf->Cell(30, 5, number_format($row['pag_indexadas'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['exp_indexadas'], 0, ',', '.'), 1, 1, 'C');
               // $pdf->Cell(40, 5, utf8_decode($row['operador']), 1, 1, 'C');
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Indexado.pdf", "D");
        }

        public function pdf_filtroOperador()
        {
            $id_operador = $_POST['id_operador'];
            $datos = $this->model->selectDatos();
            $indexado = $this->model->reporteIndexOperador($id_operador);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Indexado de expedientes');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
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
            $pdf->Cell(195, 8, "Indexado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(20);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Pág. Index.'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Exp. Index.'), 1, 0, 'C',1);
            $pdf->Cell(40, 6, utf8_decode('Operador'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($indexado as $row) {
                $pdf->setX(20);
                $pdf->Cell(25, 5, strtoupper($row['fecha']), 1, 0, 'L');
                $pdf->Cell(30, 5, number_format($row['pag_indexadas'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['exp_indexadas'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['operador']), 1, 1, 'C');
                
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Indexado.pdf", "D");
        }

        public function pdf_filtroPC()
        {
            $id_est = $_POST['id_est'];
            $datos = $this->model->selectDatos();
            $indexado = $this->model->reporteIndexpc($id_est);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Indexado de expedientes');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
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
            $pdf->Cell(195, 8, "Indexado de expedientes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(20);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Pág. Index.'), 1, 0, 'C',1);
            $pdf->Cell(30, 6, utf8_decode('Exp. Index.'), 1, 0, 'C',1);
            $pdf->Cell(40, 6, utf8_decode('PC'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
            foreach ($indexado as $row) {
                $pdf->setX(20);
                $pdf->Cell(25, 5, strtoupper($row['fecha']), 1, 0, 'L');
                $pdf->Cell(30, 5, number_format($row['pag_indexadas'], 0, ',', '.'), 1, 0, 'C');
                $pdf->Cell(30, 5, number_format($row['exp_indexadas'], 0, ',', '.'), 1, 0, 'C');
               $pdf->Cell(40, 5, utf8_decode($row['nombre_pc']), 1, 1, 'C');
                
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Indexado.pdf", "D");
        }

        public function excel()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $indexado = $this->model->selectIndexado();
            $nombre = 'indexado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Indexado de expedientes')  
                ->setSubject('Reporte de indexado')
                ->setDescription('Reporte de expedientes y páginas indexadas')
                ->setKeywords('indexado, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'FECHA');
            $sheet->setCellValue('C1', 'PAG. INDEXADOS');
            $sheet->setCellValue('D1', 'EXP. INDEXADOS');
            $sheet->setCellValue('E1', 'EST. TRABAJO');
            $sheet->setCellValue('F1', 'OPERADOR');
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
            $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($indexado as $value) {
                $sheet->setCellValue('A'.$row, $value["id_index"]);
                $sheet->setCellValue('B'.$row, $value["fecha"]);
                $sheet->setCellValue('C'.$row, $value['pag_index']); 
                $sheet->setCellValue('D'.$row, $value["exp_index"]);  
                $sheet->setCellValue('E'.$row, $value["nombre_pc"]);
                $sheet->setCellValue('F'.$row, $value["operador"]);
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'F') as $col) {
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
            $indexado = $this->model->reporteIndexFecha($desde, $hasta);
            $nombre ='indexado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Indexado de expedientes')  
                ->setSubject('Reporte de indexado')
                ->setDescription('Reporte de expedientes y páginas indexadas')
                ->setKeywords('indexado, expedientes, reporte')
                ->setCategory('Reporte');
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'FECHA');
            $sheet->setCellValue('C1', 'PAG. INDEXADOS');
            $sheet->setCellValue('D1', 'EXP. INDEXADOS');
            $sheet->setCellValue('E1', 'EST. TRABAJO');
            $sheet->setCellValue('F1', 'OPERADOR');
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
            $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($indexado as $value) {
                $sheet->setCellValue('A'.$row, $value["id_index"]);
                $sheet->setCellValue('B'.$row, $value["fecha"]);
                $sheet->setCellValue('C'.$row, $value['pag_index']); 
                $sheet->setCellValue('D'.$row, $value["exp_index"]);  
                $sheet->setCellValue('E'.$row, $value["nombre_pc"]);
                $sheet->setCellValue('F'.$row, $value["operador"]);
                $sheet->getStyle('A'.$row.':F'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'F') as $col) {
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
            $indexado = $this->model->reporteIndextotal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
            $nombre ='indexado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Indexado de expedientes')  
                ->setSubject('Reporte de indexado')
                ->setDescription('Reporte de expedientes y páginas indexadas')
                ->setKeywords('indexado, expedientes, reporte')
                ->setCategory('Reporte');
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'PAG. INDEXADOS');
            $sheet->setCellValue('C1', 'EXP. INDEXADOS');
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
            foreach ($indexado as $value) {
                $sheet->setCellValue('A'.$row, $value["mes_anio"]);
                $sheet->setCellValue('B'.$row, $value["pag_indexadas"]);
                $sheet->setCellValue('C'.$row, $value['exp_indexadas']); 
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
            $indexado = $this->model->reporteIndexOperador($id_operador);
            $nombre ='indexado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Indexado de expedientes')  
                ->setSubject('Reporte de indexado')
                ->setDescription('Reporte de expedientes y páginas indexadas')
                ->setKeywords('indexado, expedientes, reporte')
                ->setCategory('Reporte');
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'PAG. INDEXADOS');
            $sheet->setCellValue('C1', 'EXP. INDEXADOS');
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
            foreach ($indexado as $value) {
                $sheet->setCellValue('A'.$row, $value["fecha"]);
                $sheet->setCellValue('B'.$row, $value["pag_indexadas"]);
                $sheet->setCellValue('C'.$row, $value['exp_indexadas']); 
                $sheet->setCellValue('D'.$row, $value["operador"]);
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

        public function excel_filtroPC()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $id_est = $_POST['id_est'];
            $indexado = $this->model->reporteIndexpc($id_est);
            $nombre ='indexado';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Indexado de expedientes')  
                ->setSubject('Reporte de indexado')
                ->setDescription('Reporte de expedientes y páginas indexadas')
                ->setKeywords('indexado, expedientes, reporte')
                ->setCategory('Reporte');
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'PAG. INDEXADOS');
            $sheet->setCellValue('C1', 'EXP. INDEXADOS');
            $sheet->setCellValue('D1', 'PC');
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
            foreach ($indexado as $value) {
                $sheet->setCellValue('A'.$row, $value["fecha"]);
                $sheet->setCellValue('B'.$row, $value["pag_indexadas"]);
                $sheet->setCellValue('C'.$row, $value['exp_indexadas']); 
                $sheet->setCellValue('D'.$row, $value["nombre_pc"]);
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