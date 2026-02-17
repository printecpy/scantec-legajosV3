<?php
    class Procesos extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();
        }
        public function procesos()
        {
            $proceso = $this->model->selectProceso();
            $lote = $this->model->selectLote();
            $usuario = $this->model->selectUsuario();
            $tipo_proceso = $this->model->selectTipo( );
            //$historial_proceso = $this->model->historialProceso();   
        //    $historial_proceso = $this->model->selectHistorial_proceso();  'historial_proceso' => $historial_proceso           
            $data = ['proceso' => $proceso, 'lote' => $lote, 'usuario' => $usuario, 'tipo_proceso' => $tipo_proceso];
            $this->views->getView($this, "listar", $data);
        }


        public function historial_procesos()
        {         
            $id_proceso = $_GET['id_proceso'];
            $historial_proceso = $this->model->histoProceso($id_proceso);            
            $tipo_proceso = $this->model->selectTipo();
            $data = ['historial_proceso' => $historial_proceso, 'tipo_proceso' => $tipo_proceso];
            $this->views->getView($this, "historial_proceso", $data);
            echo $data;
            
        }

        public function registrar()
        {
            $lote = $_POST['lote'];
            $usuario = $_POST['usuario'];
            $tipo_proceso = $_POST['tipo_proceso'];
            $nro_caja = $_POST['nro_caja'];
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $fecha_proceso = $_POST['fecha_proceso'];
            $observacion = $_POST['observacion'];
           /*  
            $img = $_FILES['imagen'];
            $imgName = $img['name'];
            $nombreTemp = $img['tmp_name'];
            $fecha = md5(date("Y-m-d h:i:s")) ."_". $imgName;
            $destino = "Assets/images/libros/" . $fecha; */
            $insert = $this->model->insertarProceso($lote, $usuario, $tipo_proceso, $nro_caja, $desde, $hasta, $fecha_proceso, $observacion);
        if ($insert) {
            header("location: " . base_url() . "procesos");
            die();    
        }
        }
        public function editar()
        {
            $id_proceso = $_GET['id_proceso'];
            $proceso = $this->model->editProceso($id_proceso);
            $tipo_proceso = $this->model->selectTipo();
            $usuario = $this->model->selectUsuario();
            $data = ['proceso' => $proceso, 'tipo_proceso' => $tipo_proceso, 'usuario' => $usuario];
            if ($data == 0) {
                $this->procesos();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

        public function reporte()
        {
            $data = $this->model->selectProceso();
            $this->views->getView($this, "reporte", $data);
        }

        
        public function cerrar()
        {
            $id_proceso = $_GET['id_proceso'];
            $proceso = $this->model->editProceso($id_proceso);
            $data = ['proceso' => $proceso];
            if ($data == 0) {
                $this->procesos();
            } else {
                $this->views->getView($this, "editar", $data);
            }
        }

   public function modificar()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $id_tipo_proceso = $_POST['id_tipo_proceso'];
        $fecha_proceso = $_POST['fecha_proceso'];
        $nro_caja = $_POST['nro_caja'];
        $observacion = $_POST['observacion'];
        $id = $_POST['id'];
        $id_proceso = $_POST['id_proceso'];
        $actualizar = $this->model->actualizarProceso($desde, $hasta, $id_tipo_proceso, $fecha_proceso, $nro_caja, $observacion, $id, $id_proceso);
        if ($actualizar) {   
            header("location: " . base_url() . "procesos"); 
            die();
        }
    }
        public function cierre_proceso()
        {
            $id_proceso = $_POST['id_proceso'];
            $this->model->actualizarTotal($id_proceso);
            header("location: " . base_url() . "procesos");
            die();
        }
        public function eliminar()
        {
            $id_proceso = $_POST['id_proceso'];
            $this->model->estadoLote('INACTIVO', $id_proceso);
            header("location: " . base_url() . "procesos");
            die();
        }
        public function reingresar()
        {
            $id_proceso = $_POST['id_proceso'];
            $this->model->estadoLote('EN PROCESO', $id_proceso);
            header("location: " . base_url() . "procesos");
            die();
        }
        public function pdf()
        {
            if (ob_get_length()) ob_end_clean();

            // 1. Obtener datos
            $datosEmpresa = $this->model->selectDatos();
            $procesos = $this->model->selectProceso();

            // 2. Cargar plantilla
            require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';

            // 3. Instanciar PDF
            $pdf = new ReportTemplatePDF($datosEmpresa, 'Registro de Procesos', 'L', 'A4');

            // 4. Configurar Cabecera de la Tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetTextColor(0, 0, 0);

            // Definir anchos de columna y centrar tabla
            $w = array(15, 20, 25, 25, 25, 25, 50, 50, 42); // Suma ~277
            $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
            $pdf->setX($margin);

            $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell($w[1], 7, utf8_decode('Nro Lote'), 1, 0, 'C', true);
            $pdf->Cell($w[2], 7, utf8_decode('Fecha'), 1, 0, 'C', true);
            $pdf->Cell($w[3], 7, 'Nro Caja', 1, 0, 'C', true);
            $pdf->Cell($w[4], 7, 'Desde', 1, 0, 'C', true);
            $pdf->Cell($w[5], 7, 'Hasta', 1, 0, 'C', true);
            $pdf->Cell($w[6], 7, 'Usuario', 1, 0, 'C', true);
            $pdf->Cell($w[7], 7, 'Tipo Proceso', 1, 0, 'C', true);
            $pdf->Cell($w[8], 7, 'Observacion', 1, 1, 'C', true);

            // 5. Llenar filas
            $pdf->SetFont('Arial', '', 8);
            foreach ($procesos as $row) {
                $pdf->setX($margin);
                $pdf->Cell($w[0], 6, $row['id_proceso'], 1, 0, 'C');
                $pdf->Cell($w[1], 6, utf8_decode($row['id_registro']), 1, 0, 'C');
                $pdf->Cell($w[2], 6, utf8_decode($row['fecha_proceso']), 1, 0, 'C');
                $pdf->Cell($w[3], 6, utf8_decode($row['nro_caja']), 1, 0, 'C');
                $pdf->Cell($w[4], 6, utf8_decode($row['desde']), 1, 0, 'C');
                $pdf->Cell($w[5], 6, utf8_decode($row['hasta']), 1, 0, 'C');
                $pdf->Cell($w[6], 6, utf8_decode($row['nombre']), 1, 0, 'L');
                $pdf->Cell($w[7], 6, utf8_decode($row['tipo_proceso']), 1, 0, 'L');
                $pdf->Cell($w[8], 6, utf8_decode($row['observacion']), 1, 1, 'L');
            }

            // 6. Salida
            $pdf->Output("Procesos_" . date('Y_m_d') . ".pdf", "I");
        }

        public function pdf_filtro()
        {
            if (ob_get_length()) ob_end_clean();
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datosEmpresa = $this->model->selectDatos();
            $procesos = $this->model->reporteProcesoCajas($desde, $hasta);

            require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
            $pdf = new ReportTemplatePDF($datosEmpresa, 'Registro de Procesos (Filtrado por Caja)', 'L', 'A4');

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetTextColor(0, 0, 0);

            $w = array(15, 20, 25, 25, 25, 25, 50, 50, 42);
            $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
            $pdf->setX($margin);

            $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell($w[1], 7, utf8_decode('Nro Lote'), 1, 0, 'C', true);
            $pdf->Cell($w[2], 7, utf8_decode('Fecha'), 1, 0, 'C', true);
            $pdf->Cell($w[3], 7, 'Nro Caja', 1, 0, 'C', true);
            $pdf->Cell($w[4], 7, 'Desde', 1, 0, 'C', true);
            $pdf->Cell($w[5], 7, 'Hasta', 1, 0, 'C', true);
            $pdf->Cell($w[6], 7, 'Usuario', 1, 0, 'C', true);
            $pdf->Cell($w[7], 7, 'Tipo Proceso', 1, 0, 'C', true);
            $pdf->Cell($w[8], 7, 'Observacion', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            foreach ($procesos as $row) {
                $pdf->setX($margin);
                $pdf->Cell($w[0], 6, $row['id_proceso'], 1, 0, 'C');
                $pdf->Cell($w[1], 6, utf8_decode($row['id_registro']), 1, 0, 'C');
                $pdf->Cell($w[2], 6, utf8_decode($row['fecha_proceso']), 1, 0, 'C');
                $pdf->Cell($w[3], 6, utf8_decode($row['nro_caja']), 1, 0, 'C');
                $pdf->Cell($w[4], 6, utf8_decode($row['desde']), 1, 0, 'C');
                $pdf->Cell($w[5], 6, utf8_decode($row['hasta']), 1, 0, 'C');
                $pdf->Cell($w[6], 6, utf8_decode($row['nombre']), 1, 0, 'L');
                $pdf->Cell($w[7], 6, utf8_decode($row['tipo_proceso']), 1, 0, 'L');
                $pdf->Cell($w[8], 6, utf8_decode($row['observacion']), 1, 1, 'L');
            }
            $pdf->Output("Procesos_Filtro_Caja_" . date('Y_m_d') . ".pdf", "I");
        }

        public function pdf_filtroFecha()
        {
            if (ob_get_length()) ob_end_clean();
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $datosEmpresa = $this->model->selectDatos();
            $procesos = $this->model->reporteProcesoFechas($desde, $hasta);

            require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
            $pdf = new ReportTemplatePDF($datosEmpresa, 'Registro de Procesos (Filtrado por Fecha)', 'L', 'A4');

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetTextColor(0, 0, 0);

            $w = array(15, 20, 25, 25, 25, 25, 50, 50, 42);
            $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
            $pdf->setX($margin);

            $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
            $pdf->Cell($w[1], 7, utf8_decode('Nro Lote'), 1, 0, 'C', true);
            $pdf->Cell($w[2], 7, utf8_decode('Fecha'), 1, 0, 'C', true);
            $pdf->Cell($w[3], 7, 'Nro Caja', 1, 0, 'C', true);
            $pdf->Cell($w[4], 7, 'Desde', 1, 0, 'C', true);
            $pdf->Cell($w[5], 7, 'Hasta', 1, 0, 'C', true);
            $pdf->Cell($w[6], 7, 'Usuario', 1, 0, 'C', true);
            $pdf->Cell($w[7], 7, 'Tipo Proceso', 1, 0, 'C', true);
            $pdf->Cell($w[8], 7, 'Observacion', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            foreach ($procesos as $row) {
                $pdf->setX($margin);
                $pdf->Cell($w[0], 6, $row['id_proceso'], 1, 0, 'C');
                $pdf->Cell($w[1], 6, utf8_decode($row['id_registro']), 1, 0, 'C');
                $pdf->Cell($w[2], 6, utf8_decode($row['fecha_proceso']), 1, 0, 'C');
                $pdf->Cell($w[3], 6, utf8_decode($row['nro_caja']), 1, 0, 'C');
                $pdf->Cell($w[4], 6, utf8_decode($row['desde']), 1, 0, 'C');
                $pdf->Cell($w[5], 6, utf8_decode($row['hasta']), 1, 0, 'C');
                $pdf->Cell($w[6], 6, utf8_decode($row['nombre']), 1, 0, 'L');
                $pdf->Cell($w[7], 6, utf8_decode($row['tipo_proceso']), 1, 0, 'L');
                $pdf->Cell($w[8], 6, utf8_decode($row['observacion']), 1, 1, 'L');
            }
            $pdf->Output("Procesos_Filtro_Fecha_" . date('Y_m_d') . ".pdf", "I");
        }

        public function excel()
        {
            if (ob_get_length()) ob_end_clean();

            require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
            date_default_timezone_set('America/Asuncion');

            // Datos
            $datosEmpresa = $this->model->selectDatos();
            $cajas_procesos = $this->model->selectProceso();

            // Instancia
            $nombreEmpresa = $datosEmpresa['nombre'];
            $excel = new ReportTemplateExcel('CAJAS EN PROCESOS DE EXPEDIENTES', $nombreEmpresa);
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

            // Encabezados
            $headerRow = 4;
            $headers = ['ID', 'NRO LOTE', 'FECHA', 'NRO CAJA', 'DESDE', 'HASTA', 'USUARIO', 'TIPO PROCESO', 'OBSERVACION'];
            $col = 'A';
            foreach ($headers as $txt) {
                $sheet->setCellValue($col . $headerRow, $txt);
                $col++;
            }
            $sheet->getStyle("A$headerRow:I$headerRow")->applyFromArray($headerStyle);

            // Datos
            $contentStyle = [
                'font' => ['size' => 9],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
            ];
            $dataRow = $headerRow + 1;
            foreach ($cajas_procesos as $value) {
                $sheet->setCellValue('A' . $dataRow, $value["id_proceso"]);
                $sheet->setCellValue('B' . $dataRow, $value["id_registro"]);
                $sheet->setCellValue('C' . $dataRow, $value['fecha_proceso']);
                $sheet->setCellValue('D' . $dataRow, $value["nro_caja"]);
                $sheet->setCellValue('E' . $dataRow, $value['desde']);
                $sheet->setCellValue('F' . $dataRow, $value["hasta"]);
                $sheet->setCellValue('G' . $dataRow, $value["nombre"]);
                $sheet->setCellValue('H' . $dataRow, $value["tipo_proceso"]);
                $sheet->setCellValue('I' . $dataRow, $value["observacion"]);
                $sheet->getStyle('A' . $dataRow . ':I' . $dataRow)->applyFromArray($contentStyle);
                $dataRow++;
            }

            // Auto-size
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Salida
            $nombreArchivo = 'Cajas_Procesos_' . date('Y_m_d_His');
            $excel->output($nombreArchivo);
        }

        public function excel_filtro(){
            if (ob_get_length()) ob_end_clean();
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
            date_default_timezone_set('America/Asuncion');

            $datosEmpresa = $this->model->selectDatos();
            $procesos = $this->model->reporteProcesoCajas($desde, $hasta);

            $nombreEmpresa = $datosEmpresa['nombre'];
            $excel = new ReportTemplateExcel('PROCESOS FILTRADOS POR CAJA', $nombreEmpresa);
            $sheet = $excel->getSheet();

            $headerStyle = [
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];

            $headerRow = 4;
            $headers = ['ID', 'NRO LOTE', 'FECHA', 'NRO CAJA', 'DESDE', 'HASTA', 'USUARIO', 'TIPO PROCESO', 'OBSERVACION'];
            $col = 'A';
            foreach ($headers as $txt) {
                $sheet->setCellValue($col . $headerRow, $txt);
                $col++;
            }
            $sheet->getStyle("A$headerRow:I$headerRow")->applyFromArray($headerStyle);

            $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
            $dataRow = $headerRow + 1;
            foreach ($procesos as $value) {
                $sheet->setCellValue('A' . $dataRow, $value["id_proceso"]);
                $sheet->setCellValue('B' . $dataRow, $value["id_registro"]);
                $sheet->setCellValue('C' . $dataRow, $value['fecha_proceso']);
                $sheet->setCellValue('D' . $dataRow, $value["nro_caja"]);
                $sheet->setCellValue('E' . $dataRow, $value['desde']);
                $sheet->setCellValue('F' . $dataRow, $value["hasta"]);
                $sheet->setCellValue('G' . $dataRow, $value["nombre"]);
                $sheet->setCellValue('H' . $dataRow, $value["tipo_proceso"]);
                $sheet->setCellValue('I' . $dataRow, $value["observacion"]);
                $sheet->getStyle('A' . $dataRow . ':I' . $dataRow)->applyFromArray($contentStyle);
                $dataRow++;
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $nombreArchivo = 'Procesos_Filtro_Caja_' . date('Y_m_d_His');
            $excel->output($nombreArchivo);
        }

        public function excel_filtroFecha(){
            if (ob_get_length()) ob_end_clean();
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
            date_default_timezone_set('America/Asuncion');

            $datosEmpresa = $this->model->selectDatos();
            $procesos = $this->model->reporteProcesoFechas($desde, $hasta);

            $nombreEmpresa = $datosEmpresa['nombre'];
            $excel = new ReportTemplateExcel('PROCESOS FILTRADOS POR FECHA', $nombreEmpresa);
            $sheet = $excel->getSheet();

            $headerStyle = [
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            ];

            $headerRow = 4;
            $headers = ['ID', 'NRO LOTE', 'FECHA', 'NRO CAJA', 'DESDE', 'HASTA', 'USUARIO', 'TIPO PROCESO', 'OBSERVACION'];
            $col = 'A';
            foreach ($headers as $txt) {
                $sheet->setCellValue($col . $headerRow, $txt);
                $col++;
            }
            $sheet->getStyle("A$headerRow:I$headerRow")->applyFromArray($headerStyle);

            $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
            $dataRow = $headerRow + 1;
            foreach ($procesos as $value) {
                $sheet->setCellValue('A' . $dataRow, $value["id_proceso"]);
                $sheet->setCellValue('B' . $dataRow, $value["id_registro"]);
                $sheet->setCellValue('C' . $dataRow, $value['fecha_proceso']);
                $sheet->setCellValue('D' . $dataRow, $value["nro_caja"]);
                $sheet->setCellValue('E' . $dataRow, $value['desde']);
                $sheet->setCellValue('F' . $dataRow, $value["hasta"]);
                $sheet->setCellValue('G' . $dataRow, $value["nombre"]);
                $sheet->setCellValue('H' . $dataRow, $value["tipo_proceso"]);
                $sheet->setCellValue('I' . $dataRow, $value["observacion"]);
                $sheet->getStyle('A' . $dataRow . ':I' . $dataRow)->applyFromArray($contentStyle);
                $dataRow++;
            }

            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $nombreArchivo = 'Procesos_Filtro_Fecha_' . date('Y_m_d_His');
            $excel->output($nombreArchivo);
        }

}