<?php
    class Admin extends Controllers{
        public function __construct()
        {
            session_start();
            if (empty($_SESSION['ACTIVO'])) {
                header("location: " . base_url());
            }
            parent::__construct();

        }
        public function select()
        {
            $sql = "SELECT * FROM prestamo WHERE estado = 1";
            $res = $this->select_all($sql);
            return $res;
        }
        public function listar()
        {
            $expediente = $this->model->selectExpedientes();
            $funcionarios = $this->model->selectFuncionarios();
            $prestamo = $this->model->selectPrestamo();
            $especialidad = $this->model->selectEspecialidad();
            $data = ['expediente' => $expediente, 'funcionario' => $funcionarios, 'prestamo' => $prestamo, 'especialidad' =>$especialidad];
            $this->views->getView($this, "listar", $data);
        } 
       public function registrar()
        {
            $expediente = $_POST['expediente'];
            $funcionario = $_POST['funcionario'];
            $especialidad = $_POST['especialidad'];
            $fecha_prestamo = $_POST['fecha_prestamo'];
            $fecha_devolucion = $_POST['fecha_devolucion'];
            $observacion = $_POST['observacion'];
            $insert = $this->model->insertarPrestamo($funcionario, $expediente, $especialidad, $fecha_prestamo, $fecha_devolucion, $observacion);
                if ($insert) {
                    header("location: " . base_url() . "admin/listar");
                    die();
                } 
        } 
         public function devolver()
        {
            $id = $_POST['id'];
            $prest = $this->model->estadoPrestamo(0 , $id);
            if ($prest) {
                header("location: " . base_url() . "admin/listar");
                die();
            }
        } 
        public function pdf()
        {
        $datos = $this->model->selectDatos();
        $prestamo = $this->model->selectPrestamo();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle("Salida de Expedientes");
        $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() .'Assets/img/icoScantec2.png',10,7,33);        
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 275, 5, 20, 20);
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
        $pdf->SetFillColor(96, 96, 96);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(280, 8, "Movimiento de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(33);
        $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'L',1);
        $pdf->Cell(65, 5, utf8_decode('Paciente'), 1, 0, 'L',1);
        $pdf->Cell(30, 5, 'Documento', 1, 0, 'L',1);
        $pdf->Cell(40, 5, 'Fecha Prestamo', 1, 0, 'L',1);
        $pdf->Cell(40, 5, 'Fecha Devolucion', 1, 0, 'L',1);
        $pdf->Cell(25, 5, 'Estado', 1, 1, 'L',1);
        $pdf->SetFont('Arial', '', 8);
        $contador = 1;
        foreach ($prestamo as $row) {
            if ($row['estado'] == 1) {
                $estado = 'Prestado';
            } else {
                $estado = 'Devuelto';
            }
            $pdf->setX(33);
            $pdf->Cell(15, 5, $contador, 1, 0, 'L');
            $pdf->Cell(65, 5, $row['nombre'], 1, 0, 'L');
            $pdf->Cell(30, 5, utf8_decode($row['documento']), 1, 0, 'L');
            $pdf->Cell(40, 5, $row['fecha_prestamo'], 1, 0, 'L');
            $pdf->Cell(40, 5, $row['fecha_devolucion'], 1, 0, 'L');
            $pdf->Cell(25, 5, $estado, 1, 1, 'L');
            $contador++;
        }
        $pdf->SetXY(160,185);
        // Arial italic 8
        $pdf->SetFont('Arial','B',8);
        // Número de página
        $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
        $pdf->Output("Movimiento expedientes", "I");
        }
    
    }