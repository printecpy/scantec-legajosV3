<?php
    class Lotes extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function lotes()
        {
            $lote = $this->model->selectLote();
            $data = ['lote' => $lote];
            $this->views->getView($this, "listar", $data);
        }
        public function registrar()
        {
            $inicio_lote = $_POST['inicio_lote'];
            $fin_lote = $_POST['fin_lote'];
            $cant_expediente = $_POST['cant_expediente'];
            $fecha_recibido = $_POST['fecha_recibido'];
           /*$img = $_FILES['imagen'];
            $imgName = $img['name'];
            $nombreTemp = $img['tmp_name'];
            $fecha = md5(date("Y-m-d h:i:s")) ."_". $imgName;
            $destino = "Assets/images/libros/" . $fecha; */
            $insert = $this->model->insertarLote($inicio_lote, $fin_lote, $cant_expediente, $fecha_recibido);
        if ($insert) {
            header("location: " . base_url() . "lotes");
            die();    
        }
        }

        public function reporte()
        {
            $data = $this->model->selectLote();
            $this->views->getView($this, "reporte", $data);
        }

        public function editar()
        {
            $id_registro = $_GET['id_registro'];
            $lote = $this->model->editLote($id_registro);
            $cant_exped = $this->model->selectExpCant($id_registro);
            $total_pag = $this->model->selectCantPag($id_registro);
            $data = ['lote' => $lote , 'cant_exped' => $cant_exped, 'total_pag' => $total_pag];
            if ($data == 0) {
                $this->lotes();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function modificar()
        {
                $id_registro = $_POST['id_registro'];
                $inicio_lote = $_POST['inicio_lote'];
                $fin_lote = $_POST['fin_lote'];
                $fecha_entregado = $_POST['fecha_entregado'];
                $cant_expediente = $_POST['cant_expediente'];
                $total_paginas = $_POST['total_paginas'];
                $actualizar = $this->model->actualizarLote($inicio_lote, $fin_lote, $fecha_entregado, $cant_expediente, $total_paginas, $id_registro);
                if ($actualizar) {   
                    header("location: " . base_url() . "lotes"); 
                    die();
                }
        }
        public function eliminar()
        {
            $id_registro = $_POST['id_registro'];
            $this->model->estadoLote('INACTIVO', $id_registro);
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
            if (ob_get_length()) ob_end_clean();

            // 1. Obtener datos
            $datosEmpresa = $this->model->selectDatos();
            $lotes = $this->model->selectLote();

            // 2. Cargar plantilla
            require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';

            // 3. Instanciar PDF
            $pdf = new ReportTemplatePDF($datosEmpresa, 'Registro de Lotes', 'L', 'A4');

            // 4. Configurar Cabecera de la Tabla
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetTextColor(0, 0, 0);

            // Definir anchos de columna y centrar tabla
            $w = array(15, 40, 40, 30, 40, 40, 40); // Suma: 245mm
            $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
            $pdf->setX($margin);

            $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell($w[1], 7, utf8_decode('Inicio Lote'), 1, 0, 'C', true);
            $pdf->Cell($w[2], 7, utf8_decode('Fin Lote'), 1, 0, 'C', true);
            $pdf->Cell($w[3], 7, 'Cant. Exped.', 1, 0, 'C', true);
            $pdf->Cell($w[4], 7, 'Fecha Recib.', 1, 0, 'C', true);
            $pdf->Cell($w[5], 7, 'Fecha Entreg.', 1, 0, 'C', true);
            $pdf->Cell($w[6], 7, 'Total Pág.', 1, 1, 'C', true);

            // 5. Llenar filas
            $pdf->SetFont('Arial', '', 10);
            foreach ($lotes as $row) {
                $pdf->setX($margin);
                $pdf->Cell($w[0], 6, $row['id_registro'], 1, 0, 'C');
                $pdf->Cell($w[1], 6, utf8_decode($row['inicio_lote']), 1, 0, 'C');
                $pdf->Cell($w[2], 6, utf8_decode($row['fin_lote']), 1, 0, 'C');
                $pdf->Cell($w[3], 6, $row['cant_expediente'], 1, 0, 'C');
                $pdf->Cell($w[4], 6, utf8_decode($row['fecha_recibido']), 1, 0, 'C');
                $pdf->Cell($w[5], 6, utf8_decode($row['fecha_entregado']), 1, 0, 'C');
                $pdf->Cell($w[6], 6, utf8_decode($row['total_paginas']), 1, 1, 'C');
            }

            // 6. Salida
            $pdf->Output("Lotes_" . date('Y_m_d') . ".pdf", "I");
        }

        public function pdf_filtro()
        {
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datos = $this->model->selectDatos();
            $lotes = $this->model->reporteLotesInicio($desde, $hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('LOTES');
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
            $pdf->Cell(280, 8, "Registro de lotes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(33);
            $pdf->Cell(11, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Inicio lote'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Fin lote'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, 'Cant. exped', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Fecha Recib.', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Fecha Entreg.', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Total Pag.', 1, 1, 'C',1);
           // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');
            
            $pdf->SetFont('Arial', '', 10);
       //     $contador = 1;
            foreach ($lotes as $row) {
                $pdf->setX(33);
                $pdf->Cell(11, 5,$row['id_registro'], 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['inicio_lote']), 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['fin_lote']), 1, 0, 'C');
                $pdf->Cell(30, 5, $row['cant_expediente'], 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['fecha_recibido']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['fecha_entregado']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['total_paginas']), 1, 1, 'C');
                
               // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');
                
              //  $contador++;
                
            }
            $pdf->SetXY(160,185);
                // Arial italic 8
            $pdf->SetFont('Arial','B',8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Lotes.pdf", "I");
        }

        public function pdf_filtroFecha()
        {
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datos = $this->model->selectDatos();
            $lotes = $this->model->reporteLotesFechaRecib($desde, $hasta);
            require_once 'Libraries/pdf/fpdf.php';
            require_once 'Config/ConfigPath.php';
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('LOTES');
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
            $pdf->Cell(280, 8, "Registro de lotes", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(45);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(33);
            $pdf->Cell(11, 5, utf8_decode('N°'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Inicio lote'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, utf8_decode('Fin lote'), 1, 0, 'C',1);
            $pdf->Cell(30, 5, 'Cant. exped', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Fecha Recib.', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Fecha Entreg.', 1, 0, 'C',1);
            $pdf->Cell(40, 5, 'Total Pag.', 1, 1, 'C',1);
           // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');
            
            $pdf->SetFont('Arial', '', 10);
       //     $contador = 1;
            foreach ($lotes as $row) {
                $pdf->setX(33);
                $pdf->Cell(11, 5,$row['id_registro'], 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['inicio_lote']), 1, 0, 'C');
                $pdf->Cell(30, 5, utf8_decode($row['fin_lote']), 1, 0, 'C');
                $pdf->Cell(30, 5, $row['cant_expediente'], 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['fecha_recibido']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['fecha_entregado']), 1, 0, 'C');
                $pdf->Cell(40, 5, utf8_decode($row['total_paginas']), 1, 1, 'C');
                
               // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');
                
              //  $contador++;
                
            }
            $pdf->SetXY(160,185);
                // Arial italic 8
            $pdf->SetFont('Arial','B',8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Lotes.pdf", "I");
        }

        public function excel()
        {
            if (ob_get_length()) ob_end_clean();

            require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
            date_default_timezone_set('America/Asuncion');

            // Obtener datos
            $datosEmpresa = $this->model->selectDatos();
            $lotes = $this->model->selectLote();

            // Instanciar plantilla
            $nombreEmpresa = $datosEmpresa['nombre'];
            $excel = new ReportTemplateExcel('LOTES DE EXPEDIENTES', $nombreEmpresa);
            $sheet = $excel->getSheet();

            // Estilos
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '878787'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ];

            // Encabezados de tabla (Fila 4)
            $headerRow = 4;
            $headers = ['ID', 'INICIO LOTE', 'FIN LOTE', 'CANT. EXPEDIENTE', 'FECHA RECIB.', 'FECHA ENTREG.', 'TOTAL PAG.', 'STATUS'];
            $col = 'A';
            foreach ($headers as $txt) {
                $sheet->setCellValue($col . $headerRow, $txt);
                $col++;
            }
            $sheet->getStyle("A$headerRow:H$headerRow")->applyFromArray($headerStyle);

            // Rellenar datos (Desde Fila 5)
            $contentStyle = [
                'font' => ['size' => 9],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
            ];
            $dataRow = $headerRow + 1;
            foreach ($lotes as $value) {
                $sheet->setCellValue('A' . $dataRow, $value["id_registro"]);
                $sheet->setCellValue('B' . $dataRow, $value["inicio_lote"]);
                $sheet->setCellValue('C' . $dataRow, $value['fin_lote']);
                $sheet->setCellValue('D' . $dataRow, $value["cant_expediente"]);
                $sheet->setCellValue('E' . $dataRow, $value['fecha_recibido']);
                $sheet->setCellValue('F' . $dataRow, $value["fecha_entregado"]);
                $sheet->setCellValue('G' . $dataRow, $value["total_paginas"]);
                $sheet->setCellValue('H' . $dataRow, $value["estado"]);
                $sheet->getStyle('A' . $dataRow . ':H' . $dataRow)->applyFromArray($contentStyle);
                $dataRow++;
            }

            // Ajustar ancho de columnas
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Salida
            $nombreArchivo = 'Lotes_' . date('Y_m_d_His');
            $excel->output($nombreArchivo);
        }        


}