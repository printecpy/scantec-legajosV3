<?php
class Escaneos extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    public function escaneos()
    {
        $escaneo = $this->model->selectEscaneo();
        $operador = $this->model->selectOperador();
        $usuario = $this->model->selectUsuarios();
        $estTrabajo = $this->model->selectEstTrabajo();
        $data = ['escaneo' => $escaneo, 'usuario' => $usuario, 'operador' => $operador, 'estTrabajo' => $estTrabajo];
        $this->views->getView($this, "listar", $data);
    }

    public function reporte()
    {
        $indexado = $this->model->selectEscaneo();
        $operador = $this->model->selectOperador();
        $usuario = $this->model->selectUsuarios();
        $estTrabajo = $this->model->selectEstTrabajo();
        $data = ['indexado' => $indexado, 'usuario' => $usuario, 'operador' => $operador, 'estTrabajo' => $estTrabajo];
        $this->views->getView($this, "reporte", $data);
    }

    public function registrar()
    {
        $fecha = $_POST['fecha'];
        $pag_esc = $_POST['pag_esc'];
        $cant_exp = $_POST['cant_exp'];
        $id_est = $_POST['id_est'];
        $id = $_POST['id'];
        $id_operador = $_POST['id_operador'];
        $insert = $this->model->insertarEscaneo($fecha, $pag_esc, $cant_exp, $id_est, $id, $id_operador);
        if ($insert) {
            header("location: " . base_url() . "escaneos");
            die();
        }
    }

    public function editar()
    {
        $id_esc = $_GET['id_esc'];
        $escaneo = $this->model->editEscaneo($id_esc);
        $operador = $this->model->selectOperador();
        $usuario = $this->model->selectUsuarios();
        $estTrabajo = $this->model->selectEstTrabajo();
        $detEscaneo = $this->model->detEscaneo($id_esc);
        $escaneoPag = $this->model->selectDetEscaneoPag($id_esc);
        $escaneoExp = $this->model->selectDetEscaneoExp($id_esc);
        $data = [
            'escaneo' => $escaneo, 'operador' => $operador, 'usuario' => $usuario, 'estTrabajo' => $estTrabajo,
            'detEscaneo' => $detEscaneo, 'escaneoPag' => $escaneoPag, 'escaneoExp' => $escaneoExp];
        $this->views->getView($this, "editar", $data);
        if ($data == 0) {
            $this->escaneos();
        } else {
            $this->views->getView($this, "editar", $data);
        }
    }

    public function importar()
    {
       if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
                // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
              header("Location: " . base_url() . "?error=csrf");
              die();
              }  
        // Verifica si se ha enviado un archivo
        if (isset($_FILES["file"])) {
            $file_type = $_FILES["file"]["type"];
            $file_name = $_FILES["file"]["name"];
            $file_size = $_FILES["file"]["size"];
            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

            // Verifica si la extensión del archivo es .xls o .xlsx
            if ($file_ext == 'xls' || $file_ext == 'xlsx') {
                require_once 'Libraries/vendor/autoload.php';

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

                if ($file_ext == 'xls') {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }

                $spreadsheet = $reader->load($file_tmp);
                $id_esc = $_POST['id_esc'];
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                foreach ($sheetData as $key => $value) {
                    if($key == 1) continue; //si quieres omitir la primer fila
                    try {
                        //print_r($value);
                        $nombre_archivo = htmlspecialchars($value['A']);
                        $num_pag = htmlspecialchars($value['B']);
                        $fecha_creacion = htmlspecialchars($value['C']);
                        $fecha_modificacion = htmlspecialchars($value['D']);
                        $ruta_archivo = htmlspecialchars($value['E']);
                        $this->model->insertarDetEscaneo($nombre_archivo, $num_pag, $fecha_creacion, $fecha_modificacion, $ruta_archivo, $id_esc);
                    } catch (Exception $ex) {
                        echo $ex->getmessage();
                    }
                }
                // Redirecciona después de insertar los datos
                header("Location: " . base_url() . "escaneos");
                die();
            } else {
                // Si la extensión del archivo no es válida, muestra un mensaje de error
                echo "Error: Solo se permiten archivos .xls o .xlsx";
            }
        }
    }

    public function modificar()
    {
        $id_esc = $_POST['id_esc'];
        $fecha = $_POST['fecha'];
        $pag_esc = $_POST['pag_esc'];
        $cant_exp = $_POST['cant_exp'];
        $id_est = $_POST['id_est'];
        $id_operador = $_POST['id_operador'];
        $actualizar = $this->model->actualizarEscaneo($fecha, $pag_esc, $cant_exp, $id_est, $id_operador, $id_esc);
        if ($actualizar) {
            header("location: " . base_url() . "escaneos");
            die();
        }
    }

    public function inactivar()
    {
        $id_esc = $_POST['id_esc'];
        $this->model->estadoEscaneo('INACTIVO', $id_esc);
        header("location: " . base_url() . "escaneos");
        die();
    }

    public function reingresar()
    {
        $id_esc = $_POST['id_esc'];
        $this->model->estadoEscaneo('ACTIVO', $id_esc);
        header("location: " . base_url() . "escaneos");
        die();
    }

    public function pdf()
    {
        $datos = $this->model->selectDatos();
        $escaneo = $this->model->selectEscaneo();
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Config/ConfigPath.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle('Escaneos');
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
        $pdf->Cell(280, 8, "Escaneos de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->setX(45);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(13);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Pág. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Exp. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(50, 5, 'PC', 1, 0, 'C', 1);
        $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C', 1);
        // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');

        $pdf->SetFont('Arial', '', 8);
        foreach ($escaneo as $row) {
            $pdf->setX(13);
            $pdf->Cell(15, 5, $row['id_esc'], 1, 0, 'C');
            $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
            $pdf->Cell(30, 5, number_format($row['pag_esc'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(30, 5, number_format($row['cant_exp'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
            $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
        }
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output(date('Y_m_d_H:_i:_s'), "I");
    }

    public function pdf_filtroFecha()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $datos = $this->model->selectDatos();
        $escaneo = $this->model->reporteEscFecha($desde, $hasta);
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Config/ConfigPath.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle('Escaneos');
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
        $pdf->Cell(280, 8, "Escaneos de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->setX(45);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(13);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(15, 5, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Pág. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(30, 5, utf8_decode('Exp. escaneados'), 1, 0, 'C', 1);
        $pdf->Cell(50, 5, 'PC', 1, 0, 'C', 1);
        $pdf->Cell(60, 5,  utf8_decode('Operador'), 1, 1, 'C', 1);
        // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');

        $pdf->SetFont('Arial', '', 8);
        foreach ($escaneo as $row) {
            $pdf->setX(13);
            $pdf->Cell(15, 5, $row['id_esc'], 1, 0, 'C');
            $pdf->Cell(30, 5, utf8_decode($row['fecha']), 1, 0, 'C');
            $pdf->Cell(30, 5, number_format($row['pag_esc'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(30, 5, number_format($row['cant_exp'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
            $pdf->Cell(60, 5, utf8_decode($row['operador']), 1, 1, 'C');
        }
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 7);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output(date('Y_m_d_H:_i:_s'), "I");
    }

    public function pdf_filtroTotal()
    {
        $mes_desde = $_POST['mes_desde'];
        $anio_desde = $_POST['anio_desde'];
        $mes_hasta = $_POST['mes_hasta'];
        $anio_hasta = $_POST['anio_hasta'];
        $datos = $this->model->selectDatos();
        $escaneo = $this->model->reporteEsctotal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Config/ConfigPath.php';
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle('Escaneos');
        $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        //
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
        $pdf->Cell(195, 8, "Escaneos de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->setX(45);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(12);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(30, 6, utf8_decode('Mes'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Pág. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Exp. escaneados'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($escaneo as $row) {
            $pdf->setX(12);
            $pdf->Cell(30, 5, strtoupper($row['mes_anio']), 1, 0, 'L');
            $pdf->Cell(35, 5, number_format($row['pag_escaneadas'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(35, 5, number_format($row['cant_expedientes'], 0, ',', '.'), 1, 1, 'C');
        }
        $pdf->SetXY(160, 273);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 7);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output(date('Y_m_d_H:_i:_s'), "I");
    }

    public function pdf_filtroOperador()
    {
        $id_operador = $_POST['id_operador'];
        $datos = $this->model->selectDatos();
        $escaneo = $this->model->reporteEscOperador($id_operador);
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Config/ConfigPath.php';
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle('Escaneos');
        $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        //
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
        $pdf->Cell(195, 8, "Escaneos de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->setX(45);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(20);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(30, 6, utf8_decode('Mes'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Pág. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Exp. escaneados'), 1, 0, 'C', 1);
        $pdf->Cell(40, 6, utf8_decode('Operador'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($escaneo as $row) {
            $pdf->setX(20);
            $pdf->Cell(30, 5, strtoupper($row['fecha']), 1, 0, 'L');
            $pdf->Cell(35, 5, number_format($row['pag_escaneadas'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(35, 5, number_format($row['cant_expedientes'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(40, 5, utf8_decode($row['operador']), 1, 1, 'C');
        }
        $pdf->SetXY(160, 273);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 7);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output(date('Y_m_d_H:_i:_s'), "I");
    }

    public function pdf_filtroPC()
    {
        $id_est = $_POST['id_est'];
        $datos = $this->model->selectDatos();
        $escaneo = $this->model->reporteEscpc($id_est);
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Config/ConfigPath.php';
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        // Agregar metadatos
        $pdf->SetTitle('Escaneos');
        $pdf->SetAuthor('SCANTEC'.$_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        //
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
        $pdf->Cell(195, 8, "Escaneos de expedientes", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->setX(45);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(20);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(25, 6, utf8_decode('Mes'), 1, 0, 'C', 1);
        $pdf->Cell(34, 6, utf8_decode('Pág. escaneadas'), 1, 0, 'C', 1);
        $pdf->Cell(34, 6, utf8_decode('Exp. escaneados'), 1, 0, 'C', 1);
        $pdf->Cell(40, 6, utf8_decode('PC'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($escaneo as $row) {
            $pdf->setX(20);
            $pdf->Cell(25, 5, strtoupper($row['fecha']), 1, 0, 'L');
            $pdf->Cell(34, 5, number_format($row['pag_escaneadas'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(34, 5, number_format($row['cant_expedientes'], 0, ',', '.'), 1, 0, 'C');
            $pdf->Cell(40, 5, utf8_decode($row['nombre_pc']), 1, 1, 'C');
        }
        $pdf->SetXY(160, 273);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 7);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output(date('Y_m_d_H:_i:_s'), "I");
    }

    public function excel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $escaneo = $this->model->selectEscaneo();
        $nombre = 'escaneo';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet(); 
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - '.$_SESSION['usuario'])
            ->setTitle('Escaneos');  
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'FECHA');
        $sheet->setCellValue('C1', 'PAG. ESCANEADOS');
        $sheet->setCellValue('D1', 'EXP. ESCANEADOS');
        $sheet->setCellValue('E1', 'PC');
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
        foreach ($escaneo as $value) {
            $sheet->setCellValue('A'.$row, $value["id_esc"]);
            $sheet->setCellValue('B'.$row, $value["fecha"]);
            $sheet->setCellValue('C'.$row, $value['pag_esc']); 
            $sheet->setCellValue('D'.$row, $value["cant_exp"]); 
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
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $escaneo = $this->model->reporteEscFecha($desde, $hasta);
        $nombre = 'escaneo_filfec';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();   
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'FECHA');
        $sheet->setCellValue('C1', 'PAG. ESCANEADOS');
        $sheet->setCellValue('D1', 'EXP. ESCANEADOS');
        $sheet->setCellValue('E1', 'PC');
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
        foreach ($escaneo as $value) {
            $sheet->setCellValue('A'.$row, $value["id_esc"]);
            $sheet->setCellValue('B'.$row, $value["fecha"]);
            $sheet->setCellValue('C'.$row, $value['pag_esc']); 
            $sheet->setCellValue('D'.$row, $value["cant_exp"]); 
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

    public function excel_filtroTotal(){
        require_once 'Libraries/vendor/autoload.php';
    
        $mes_desde = $_POST['mes_desde'];
        $anio_desde = $_POST['anio_desde'];
        $mes_hasta = $_POST['mes_hasta'];
        $anio_hasta = $_POST['anio_hasta'];
        $escaneo = $this->model->reporteEsctotal($mes_desde, $anio_desde, $mes_hasta, $anio_hasta);
        $nombre ='escaneo_filtot';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();   
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'PAG. ESCANEADOS');
        $sheet->setCellValue('C1', 'EXP. ESCANEADOS');
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
                'size' => 8, // Tamaño de fuente 10 para el resto del contenido
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        $row = 2;    
        foreach ($escaneo as $value) {
            $sheet->setCellValue('A'.$row, strtoupper($value["mes_anio"]));
            $sheet->setCellValue('B'.$row, $value['pag_escaneadas']);
            $sheet->setCellValue('C'.$row, $value["cant_expedientes"]); 
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
       
    public function excel_filtroOperador(){
        require_once 'Libraries/vendor/autoload.php';
        
        $id_operador = $_POST['id_operador'];
        $escaneo = $this->model->reporteEscOperador($id_operador);
        $nombre = 'escaneo_filoper';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();   
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'PAG. ESCANEADOS');
        $sheet->setCellValue('C1', 'EXP. ESCANEADOS');
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
        foreach ($escaneo as $value) {
            $sheet->setCellValue('A'.$row, strtoupper($value["fecha"]));
            $sheet->setCellValue('B'.$row, $value['pag_escaneadas']); // Números enteros y separador de miles con puntos
            $sheet->setCellValue('C'.$row, $value["cant_expedientes"]); // Números enteros y separador de miles con puntos
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

    public function excel_filtroPC(){
        require_once 'Libraries/vendor/autoload.php';
    
        $id_est = $_POST['id_est'];
        $escaneo = $this->model->reporteEscpc($id_est);
        $nombre = 'escaneo_filpc';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();   
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue'); 
        $sheet->setCellValue('A1', 'FECHA');
        $sheet->setCellValue('B1', 'PAG. ESCANEADOS');
        $sheet->setCellValue('C1', 'EXP. ESCANEADOS');
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
        foreach ($escaneo as $value) {
            $sheet->setCellValue('A'.$row, strtoupper($value["fecha"]));
            $sheet->setCellValue('B'.$row, $value['pag_escaneadas']); // Números enteros y separador de miles con puntos
            $sheet->setCellValue('C'.$row, $value["cant_expedientes"]); // Números enteros y separador de miles con puntos
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