<?php
use setasign\Fpdi\Fpdi;
    class Indexador extends Controllers{
        public function __construct()
        {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
            parent::__construct();

        }
        public function listar()
        {
            $data = $this->model->selectConfiguracion();         
            $this->views->getView($this, "listar", $data, "");
        }
       
    public function indexar_archivo()
    {   
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Libraries/fpdi/src/autoload.php';
        
        // Inicializar FPDI
        $pdf = new Fpdi();
        // Agregar los datos variables
        $columna_01 =  htmlspecialchars($_POST['columna_01']);
        $columna_02 =  htmlspecialchars($_POST['columna_02']);
        $columna_03 =  htmlspecialchars($_POST['columna_03']);
        $columna_04 =  htmlspecialchars($_POST['columna_04']);
        $columna_05 =  htmlspecialchars($_POST['columna_05']);
        $columna_06 =  htmlspecialchars($_POST['columna_06']);
        date_default_timezone_set('America/Asuncion');
        $day=date("d");
        $mont=date("m");
        $year=date("Y");
        $hora=date("H:i:s");
        $fecha=$day.'/'.$mont.'/'.$year.' '.$hora;
        $usuario = $_SESSION['usuario'];
        // Lista de archivos PDF a combinar
        // Agregar los archivos PDF
        $files = array($_FILES['pdf1']['tmp_name']); 
        // Recorrer la lista de archivos
        foreach ($files as $file) {
            $pdf->setSourceFile($file);
            $pageCount = $pdf->setSourceFile($file);
            
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                    $tplIdx = $pdf->importPage($pageNumber);
                    // Obtener el tamaño original de la página
                    $size = $pdf->getTemplateSize($pageNumber);
                    // Agregar una nueva página con el mismo formato que la original
                    $pdf->AddPage($size['orientation'], $size);
                    $pdf->useTemplate($tplIdx, null, null, null, null, true); // Escalar automáticamente al tamaño de la página original
                }
            }            
        }      
        $ruta_creada = 'Expedientes/';
        $pdf->Output('F', RUTA_BASE . $ruta_creada . $columna_06.'.pdf');
            /* $usuario = $_SESSION['usuario'];
            $nombre_archivo = $columna_06.'.pdf';
            $this->model->insertarUnirpdf($columna_01, $columna_02, $columna_03, $usuario, $nombre_archivo, $ruta_archivo);
            echo "<script>alert('Se ha generado el documento con éxito');window.history.back();</script>";
            //header("location: " . base_url() . "unirpdf/listar_playa");
            die(); */
            
    }

        public function pdf()
        {
            $expediente = $this->model->selectExpediente();
            $datos = $this->model->selectDatos();
            require_once 'Libraries/pdf/fpdf.php';
            $pdf = new FPDF('L', 'mm', 'LEGAL');
            $pdf->AddPage();
            $pdf->SetMargins(10, 5, 5);
            //  $pdf->SetTitle("Libros");
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(120);
            $pdf->Cell(20, 5, utf8_decode("Proyecto: ".$datos['nombre']), 0, 0, 'L');
            $pdf->Ln();
            $pdf->image(base_url() .'Assets/img/icoPrintec.png',13,10,8);
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
            $pdf->Cell(338, 8, "EXPEDIENTES", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(10);
            $pdf->Cell(15, 6, utf8_decode('N°'), 1, 0, 'C',1);
           // $pdf->Cell(45, 6, utf8_decode('ID proceso'), 1, 0, 'C',1);
            $pdf->Cell(35, 6, utf8_decode('Indice 1'), 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Indice 2', 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Indice 3', 1, 0, 'C',1);
            $pdf->Cell(35, 6, utf8_decode('Indice 4'), 1, 0, 'C',1);
            $pdf->Cell(168, 6, 'Indice 5', 1, 0, 'C',1);
          //  $pdf->Cell(35, 6, 'Usuario', 1, 0, 'C',1);
            $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 7);
       //     $contador = 1;
            foreach ($expediente as $row) {
                $pdf->setX(10);
                $pdf->Cell(15, 6,$row['id_expediente'], 1, 0, 'C');
             //   $pdf->Cell(45, 6, utf8_decode($row['id_proceso']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_01']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_02']),1, 0,'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_03']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_04']), 1, 0, 'C');
                $pdf->Cell(168, 6, utf8_decode($row['indice_05']), 1, 0, 'J');
               // $pdf->Cell(35, 6, utf8_decode($row['paginas']), 1, 0, 'C');
                $pdf->Cell(15, 6, utf8_decode($row['paginas']), 1, 1, 'C');
                //$pdf->Cell(30, 5, utf8_decode($row['total_pag']), 1, 1, 'C');
                
               // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');
                
              //  $contador++;
                
            }
            //$pdf->tablaHorizontal($miCabecera, $misDatos);
            /* $pdf->SetXY(160,195);
                // Arial italic 8
            $pdf->SetFont('Arial','B',8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R'); */
            $pdf->Output("Expedientes.pdf", "I");
        }

        public function pdf_filtro()
        {
            $desde = $_POST['desde'];
            $hasta = $_POST['hasta'];
            $expediente = $this->model->reporteExpedientes($desde, $hasta);
            $datos = $this->model->selectDatos();
            require_once 'Libraries/pdf/fpdf.php';
            $pdf = new FPDF('L', 'mm', 'LEGAL');
            $pdf->AddPage();
            $pdf->SetMargins(10, 5, 5);
            //  $pdf->SetTitle("Libros");
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(120);
            $pdf->Cell(20, 5, utf8_decode("Proyecto: ".$datos['nombre']), 0, 0, 'L');
            $pdf->Ln();
            $pdf->image(base_url() .'Assets/img/icoPrintec.png',13,10,8);
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
            $pdf->Cell(338, 8, "EXPEDIENTES", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(10);
            $pdf->Cell(15, 6, utf8_decode('N°'), 1, 0, 'C',1);
           // $pdf->Cell(45, 6, utf8_decode('ID proceso'), 1, 0, 'C',1);
            $pdf->Cell(35, 6, utf8_decode('Indice 1'), 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Indice 2', 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Indice 3', 1, 0, 'C',1);
            $pdf->Cell(35, 6, utf8_decode('Indice 4'), 1, 0, 'C',1);
            $pdf->Cell(168, 6, 'Indice 5', 1, 0, 'C',1);
          //  $pdf->Cell(35, 6, 'Usuario', 1, 0, 'C',1);
            $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 1, 'C',1);
            
            $pdf->SetFont('Arial', '', 7);
       //     $contador = 1;
            foreach ($expediente as $row) {
                $pdf->setX(10);
                $pdf->Cell(15, 6,$row['id_expediente'], 1, 0, 'C');
             //   $pdf->Cell(45, 6, utf8_decode($row['id_proceso']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_01']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_02']),1, 0,'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_03']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_04']), 1, 0, 'C');
                $pdf->Cell(168, 6, utf8_decode($row['indice_05']), 1, 0, 'J');
               // $pdf->Cell(35, 6, utf8_decode($row['paginas']), 1, 0, 'C');
                $pdf->Cell(15, 6, utf8_decode($row['paginas']), 1, 1, 'C');
                //$pdf->Cell(30, 5, utf8_decode($row['total_pag']), 1, 1, 'C');
                
               // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');
                
              //  $contador++;
                
            }
            //$pdf->tablaHorizontal($miCabecera, $misDatos);
            /* $pdf->SetXY(160,195);
                // Arial italic 8
            $pdf->SetFont('Arial','B',8);
                // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R'); */
            $pdf->Output("Expedientes.pdf", "I");
        }

}