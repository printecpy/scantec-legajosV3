<?php
    class Dashboard extends Controllers{
        public function __construct()
        {
            session_start();
            if (empty($_SESSION['ACTIVO'])) {
                header("location: " . base_url());
            }
            parent::__construct();

        }
        public function selectPrestamo()
        {
            $sql = "SELECT * FROM prestamo WHERE estado = 1";
            $res = $this->select_all($sql);
            return $res;
        }
   
        public function listar()
        {
            $lote_finalizado = $this->model->selectLotesFinalizado();
            $lote_proceso = $this->model->selectLotesProceso();
            $cant_proceso = $this->model->selectCantProceso();
            $cant_expedient = $this->model->selectExpedienteCantidad();
            $pagina_procesada = $this->model->selectPaginasProcesada();
            $cant_faltante = $this->model->selectCantFaltante();
            $porc_avanza = $this->model->selectPorcAvanza();
            $expe_lote = $this->model->selectExpedLote();
            $logs_uman = $this->model->selectLogs_uman();
            $usuarios_activos = $this->model->selectUsuariosActivos();
            $cant_porIndice = $this->model->selectCant_porIndice();
            $exp_consultados = $this->model->selectExpConsultadosDia();
            $archiv_tipoDoc = $this->model->selectarchivosTipoDoc();
            $archiv_tipoDoc2 = $this->model->selectCantArchivosDoc();
            $data = ['lote_finalizado' => $lote_finalizado, 'lote_proceso' => $lote_proceso, 
            'cant_proceso'=> $cant_proceso, 'cant_expedient'=> $cant_expedient, 'pagina_procesada' => $pagina_procesada, 
            'cant_faltante' => $cant_faltante, 'porc_avanza' => $porc_avanza, 'expe_lote' => $expe_lote,
            'logs_uman' => $logs_uman, 'usuarios_activos' => $usuarios_activos,  'cant_porIndice' => $cant_porIndice,
            'exp_consultados' => $exp_consultados, 'archiv_tipoDoc' => $archiv_tipoDoc, 'archiv_tipoDoc2' => $archiv_tipoDoc2];
            $this->views->getView($this, "listar", $data);
        }

        public function getChartData() {
            return $this->model->getChartData();
        }

       public function registrar()
        {
            $expediente = $_POST['expediente'];
            $funcionario = $_POST['funcionario'];
            $especialidad = $_POST['especialidad'];
          //  $cantidad = $_POST['cantidad'];
            $fecha_prestamo = $_POST['fecha_prestamo'];
            $fecha_devolucion = $_POST['fecha_devolucion'];
            $observacion = $_POST['observacion'];
           // $cantidadActual = $this->model->selectLibrosCantidad($libro);
           $insert = $this->model->insertarPrestamo($funcionario, $expediente, $especialidad, $fecha_prestamo, $fecha_devolucion, $observacion);
               // $total = ($cantidadActual['cantidad'] - $cantidad);
             //   $this->model->actualizarCantidad($total, $libro);
                if ($insert) {
                    header("location: " . base_url() . "admin/listar");
                    die();
                } 
          /*  if ($cantidadActual['cantidad'] < $cantidad) {
                header("location: " . base_url() . "admin/listar?no_s");
            }else{
                
            } */
            
        } 
         public function devolver()
        {
            $id = $_POST['id'];
            /* $cantidadprestado = $this->model->selectPrestamoCantidad($id);
            $cantidadActual = $this->model->selectLibrosCantidad($cantidadprestado['id']);
            $total = ($cantidadActual['cantidad'] + $cantidadprestado['cantidad']); */
            $prest = $this->model->estadoPrestamo(0 , $id);
          // $actualizado = $this->model->actualizarCantidad($total, $cantidadprestado['id_libro']);
            if ($prest) {
                header("location: " . base_url() . "admin/listar");
                die();
            }
        } 
        public function pdf()
        {
        $datos = $this->model->selectDatos();
        $prestamo = $this->model->selectPrestamoDebe();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'letter');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTitle("Prestamos");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(195, 5, utf8_decode($datos['nombre']), 0, 1, 'C');

        $pdf->image(base_url() . "/Assets/img/logo.jpg", 180, 10, 30, 30, 'JPG');
        $pdf->SetFont('Arial', 'B', 10);
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
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(196, 5, "Detalle de Prestamos", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(14, 5, utf8_decode('N°'), 1, 0, 'L');
        $pdf->Cell(50, 5, utf8_decode('Estudiantes'), 1, 0, 'L');
        $pdf->Cell(87, 5, 'Libros', 1, 0, 'L');
        $pdf->Cell(30, 5, 'Fecha Prestamo', 1, 0, 'L');
        $pdf->Cell(15, 5, 'Cant.', 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $contador = 1;
        foreach ($prestamo as $row) {
            $pdf->Cell(14, 5, $contador, 1, 0, 'L');
            $pdf->Cell(50, 5, $row['nombre'], 1, 0, 'L');
            $pdf->Cell(87, 5, utf8_decode($row['titulo']), 1, 0, 'L');
            $pdf->Cell(30, 5, $row['fecha_prestamo'], 1, 0, 'L');
            $pdf->Cell(15, 5, $row['cantidad'], 1, 1, 'L');
            $contador++;
        }
        $pdf->Output("prestamos.pdf", "I");
        }
    
    }