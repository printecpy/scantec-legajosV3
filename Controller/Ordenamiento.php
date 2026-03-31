<?php
// Incluye el archivo del modewls de Usuarios
require_once 'Models/UsuariosModel.php';
    class Ordenamiento extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function ordenamiento()
        {
            if (!in_array($_SESSION['id_rol'], [1, 2, 3])) {
                $_SESSION['alert_message'] = 'No tienes permiso para acceder a esta página';
                $motivo = 'Acceso no autorizado';
                $usuario = $_SESSION['nombre'];
                // Obtener la instancia de UsuariosModel
                $usuariosModel = new UsuariosModel();
                // Llama al método bloquarPC_IP() del modelo de Usuarios
                $usuariosModel->bloquarPC_IP($usuario, $motivo);
                header('Location: '.base_url().'expedientes/indice_busqueda', true, 302);// Redirigir a la página de índice de búsqueda
                exit();
            }
            $ordenamiento = $this->model->selectOrdenamiento();
            $data = ['ordenamiento' => $ordenamiento];
            $this->views->getView($this, "listar", $data);
        }

       public function registrar()
        {
            $codigo_caja = $_POST['codigo_caja'];
            $descripcion = $_POST['descripcion'];
            $ubicacion = $_POST['ubicacion'];
            $fecha_almacenamiento = $_POST['fecha_almacenamiento'];
            $observaciones = $_POST['observaciones'];
            $tipo = $_POST['tipo'];
            $insert = $this->model->insertarOrdenamiento($codigo_caja, $descripcion, $ubicacion, $fecha_almacenamiento, $observaciones, $tipo);
            if ($insert) {
            header("location: " . base_url() . "lotes");
            die();    
            }
        }

        public function editar()
        {
            $id = $_GET['id'];
            $ordenamiento = $this->model->editOrdenamiento($id);
            $data = ['ordenamiento' => $ordenamiento];
            if ($data == 0) {
                $this->ordenamiento();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function modificar()
        {
            $id = $_POST['id'];
            $codigo_caja = $_POST['codigo_caja'];
            $descripcion = $_POST['descripcion'];
            $ubicacion = $_POST['ubicacion'];
            $fecha_almacenamiento = $_POST['fecha_almacenamiento'];
            $observaciones = $_POST['observaciones'];
            $tipo = $_POST['tipo'];
            $actualizar = $this->model->actualizarOrdenamiento($codigo_caja, $descripcion, $ubicacion, $fecha_almacenamiento, $observaciones, $tipo, $id);
            if ($actualizar) {   
                header("location: " . base_url() . "ordenamiento"); 
                die();
            }
        }

        public function eliminar()
        {
            $id = $_POST['id'];
            $this->model->estadoLote('INACTIVO', $id);
            header("location: " . base_url() . "lotes");
            die();
        }
        
        public function reingresar()
        {
            $id_registro = $_POST['id_registro'];
            $this->model->estadoLote('EN PROCESO', $id_registro);
            header("location: " . base_url() . "lotes");
            die();
        }

        public function pdf()
        {
            $datos = $this->model->selectDatos();
            $lotes = $this->model->selectOrdenamiento();
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetTitle('Ordenamiento fisico de expedientes');
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
            $pdf->Cell(280, 8, "Registro de ordenamiento fisico", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(10);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(40, 5, 'Codigo caja', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Descripción', 1, 0, 'C',1);
            $pdf->Cell(30, 5, 'Ubicacion', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Fecha.', 1, 0, 'C',1);
            $pdf->Cell(90, 5, 'Observaciones', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Tipo', 1, 1, 'C',1);
            $pdf->SetFont('Arial', '', 7);
            foreach ($lotes as $row) {
                $pdf->setX(10);
                $pdf->Cell(40, 5, utf8_decode($row['codigo_caja']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['descripcion']), 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['ubicacion']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['fecha_almacenamiento']), 1, 0, 'C');
                $pdf->Cell(90, 5, utf8_decode($row['observaciones']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['tipo']), 1, 1, 'C');
            }
            $pdf->SetXY(160,185);
                // Arial italic 8
            $pdf->SetFont('Arial','B',8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Ordenamiento.pdf", "D");
        }

        public function excel()
        {
            require_once 'Libraries/vendor/autoload.php';
            date_default_timezone_set('America/Asuncion');
            $ordenamiento = $this->model->selectOrdenamiento();
            $nombre ='ordenamiento';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet(); 
            // Establecer el nombre de la hoja
            $sheet->setTitle($nombre);
            // Agregar metadatos
            $spreadsheet->getProperties()
                ->setCreator('SCANTEC')
                ->setLastModifiedBy('scantec - '.$_SESSION['usuario'])
                ->setTitle('Ordenamiento de expedientes')  
                ->setSubject('Reporte de ordenamiento archivos')
                ->setDescription('Reporte de ordenamiento de expedientes')
                ->setKeywords('ordenamiento, expedientes, reporte')
                ->setCategory('Reporte');  
            // Establecer estilo de fuente predeterminado
            $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
            $sheet->setCellValue('A1', 'CODIGO CAJA');
            $sheet->setCellValue('B1', 'DESCRIPCION');
            $sheet->setCellValue('C1', 'UBICACION');
            $sheet->setCellValue('D1', 'FECHA');
            $sheet->setCellValue('E1', 'OBSERVACIONES');
            $sheet->setCellValue('F1', 'TIPO');
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
            foreach ($ordenamiento as $value) {
                $sheet->setCellValue('A'.$row, $value["codigo_caja"]);
                $sheet->setCellValue('B'.$row, $value["descripcion"]);
                $sheet->setCellValue('C'.$row, $value['ubicacion']); 
                $sheet->setCellValue('D'.$row, $value["fecha_almacenamiento"]);  
                $sheet->setCellValue('E'.$row, $value["observaciones"]);
                $sheet->setCellValue('F'.$row, $value["tipo"]);
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


}
