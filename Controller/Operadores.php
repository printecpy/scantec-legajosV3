<?php
    class Operadores extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function operadores()
        {
            $operador = $this->model->selectOperador();
            $datos = $this->model->selectDatos();
            $data = ['operador' => $operador, 'datos' => $datos];
            $this->views->getView($this, "listar", $data);
        }

        public function escaner()
        {
            $operador = $this->model->selectEscaner();
            $datos = $this->model->selectDatos();
            $data = ['operador' => $operador, 'datos' => $datos];
            $this->views->getView($this, "escaner", $data);
        }

        public function estacion_trabajo()
        {
            $operador = $this->model->selectEstacio_trabajo();
            $datos = $this->model->selectDatos();
            $data = ['operador' => $operador, 'datos' => $datos];
            $this->views->getView($this, "estacion_trabajo", $data);
        }
        public function registrar()
        {
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $direccion = $_POST['direccion'];
            $proyecto = $_POST['proyecto'];
            $insert = $this->model->insertarOperador($nombre, $apellido, $direccion, $proyecto);
            if ($insert) {
            header("location: " . base_url() . "operadores");
            die();    
            }
        }

        public function insertar()
        {
            $nombre_pc = $_POST['nombre_pc'];
            $insert = $this->model->insertarPC($nombre_pc);
            if ($insert) {
            header("location: " . base_url() . "operadores");
            die();    
            }
        }

        public function editar()
        {
            $id_operador = $_GET['id_operador'];
            $operador = $this->model->editOperador($id_operador);
            $data = ['operador' => $operador];
            if ($data == 0) {
                $this->operadores();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function modificar()
        {
                $id_operador = $_POST['id_operador'];
                $nombre = $_POST['nombre'];
                $apellido = $_POST['apellido'];
                $direccion = $_POST['direccion'];
                $proyecto = $_POST['proyecto'];
                $actualizar = $this->model->actualizarOperador($nombre, $apellido, $direccion, $proyecto, $id_operador);
                if ($actualizar) {   
                    header("location: " . base_url() . "operadores"); 
                    die();
                }
        }
        public function inactivar()
        {
            $id_operador = $_POST['id_operador'];
            $this->model->estadoOperador('INACTIVO', $id_operador);
            header("location: " . base_url() . "operadores");
            die();
        }

        public function reingresar()
        {
            $id_operador = $_POST['id_operador'];
            $this->model->estadoOperador('ACTIVO', $id_operador);
            header("location: " . base_url() . "operadores");
            die();
        }

        public function pdf()
        {
            $datos = $this->model->selectDatos();
            $operadores = $this->model->selectOperador();
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle(title: 'Opeeradores Digitalizacion');
            $pdf->SetAuthor('SCANTEC '.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
            $pdf->image(IMAGE_PATH . 'logo_empresa.jpg',  275, 5, 20, 20);
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
            $pdf->Cell(280, 8, "Registro de Operadores", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(10);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(18, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(55, 5, utf8_decode('Nombre'), 1, 0, 'C',1);
            $pdf->Cell(55, 5, utf8_decode('Apellido'), 1, 0, 'C',1);
            $pdf->Cell(70, 5, utf8_decode('Dirección'), 1, 0, 'C',1);
            $pdf->Cell(50, 5, utf8_decode('Proyecto'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, 'Estado', 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 8);
       //     $contador = 1;
            foreach ($operadores as $row) {
                $pdf->setX(10);
                $pdf->Cell(18, 5,$row['id_operador'], 1, 0, 'C');
                $pdf->Cell(55, 5, utf8_decode($row['nombre']), 1, 0, 'L');
                $pdf->Cell(55, 5, utf8_decode($row['apellido']), 1, 0, 'L');
                $pdf->Cell(70, 5, utf8_decode($row['direccion']), 1, 0, 'L');
                $pdf->Cell(50, 5, utf8_decode($row['proyecto']), 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['estado']), 1, 1, 'C');
                                
              //  $contador++;
                
            }
            $pdf->SetXY(160,185);
                // Arial italic 8
            $pdf->SetFont('Arial', 'B', 8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Operadores", "I");
        }

        public function excel()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $operadores = $this->model->selectOperador();
            $nombre = 'operadores';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Establecer el nombre de la hoja
            $sheet->setTitle($nombre);
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('Scantec - '.$_SESSION['usuario'])
                ->setTitle('Operadores servicios digitalizacion');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'NOMBRE');
            $sheet->setCellValue('B1', 'APELLIDO');
            $sheet->setCellValue('C1', 'DIRECCION');
            $sheet->setCellValue('D1', 'PROYECTO');
            $sheet->setCellValue('E1', 'STATUS');
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
            $sheet->getStyle('A1:E1')->applyFromArray($styleArray);
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

            $row = 2;    
            foreach ($operadores as $value) {
                $sheet->setCellValue('A'.$row, $value["nombre"]);
                $sheet->setCellValue('B'.$row, $value["apellido"]);
                $sheet->setCellValue('C'.$row, $value['direccion']); 
                $sheet->setCellValue('D'.$row, $value["proyecto"]); 
                $sheet->setCellValue('E'.$row, $value['estado']);
                $sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($contentStyle);
                $row++;
            }
            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$nombre.'_'.date('Y_m_d_H:_i:_s').'.xlsx"');
            header('Cache-Control: max-age=0');
        
            $writer->save('php://output');
        }

       
}