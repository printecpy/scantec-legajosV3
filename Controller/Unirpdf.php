<?php
use setasign\Fpdi\Fpdi;
class Unirpdf extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();

    }
    public function unir_documentos()
    {
        $tipos = $this->model->selectTipoDoc();
        $data = [
            'page_title' => 'Unir Documentos',
            'tipos_documento' => $tipos
        ];
        $this->views->getView($this, "unir_documentos", $data);
    }

    public function listar_tienda()
    {
        $data = $this->model->selectConfiguracion();
        $this->views->getView($this, "listar_tienda", $data, "");
    }

    public function listar_super()
    {
        $data = $this->model->selectConfiguracion();
        $this->views->getView($this, "listar_super", $data, "");
    }

    public function indice_busqueda()
    {
        $indice_04 = $this->model->selectDocumento();
        $tipos_documentos = $this->model->selectTipoDoc();
        // Asegúrate de que la clave sea la misma en la vista y en el controlador
        $data = ['indice_04' => $indice_04, 'tipos_documentos' => $tipos_documentos];
        $this->views->getView($this, "indice_busqueda", $data);
    }

    public function procesar_pdf1()
    {
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Libraries/fpdi/src/autoload.php';

        // Inicializar FPDI
        $pdf = new Fpdi();
        $pdf->SetMargins(55, 5, 5);
        // Agregar una nueva página para la carátula
        $pdf->AddPage();
        // set font
        $pdf->SetFont('times', 'B', 24);
        //establecer logo del cliente
        $pdf->image(base_url() . 'Assets/img/logo_empresa.jpg', 90, 20, 25);
        // Establecer la posición del texto
        $pdf->SetXY(50, 55);
        // Escribir el texto de la carátula
        $columna_03 = $_POST['columna_03'];
        $nombre_doc = $_POST['nombre_doc'];
        $pdf->Write(8, $nombre_doc . " - " . $columna_03);

        // Agregar los datos variables
        $columna_01 = $_POST['columna_01'];
        $columna_02 = $_POST['columna_02'];
        $columna_03 = $_POST['columna_03'];
        $pdf->SetXY(40, 57);
        $pdf->SetFont('times', 'B', 18);
        $pdf->Write(8, "\n         Sucursal: ");
        $pdf->SetFont('times', '', 14);
        $pdf->Write(8, "$columna_01");
        $pdf->SetFont('times', 'B', 18);
        $pdf->Write(8, "\n         Periodo: ");
        $pdf->SetFont('times', '', 14);
        $pdf->Write(8, "$columna_02");
        $pdf->SetFont('times', 'B', 18);
        $pdf->Write(8, "\n         Tipo: ");
        $pdf->SetFont('times', '', 14);
        $pdf->Write(8, "$columna_03");
        date_default_timezone_set('America/Asuncion');
        $day = date("d");
        $mont = date("m");
        $year = date("Y");
        $hora = date("H:i:s");
        $fecha = $day . '/' . $mont . '/' . $year . ' ' . $hora;
        $usuario = $_SESSION['usuario'];
        //pie de pagina hora
        $pdf->SetMargins(15, 2, 2);
        $pdf->SetY(266);
        $pdf->SetFont('times', '', 7);
        $pdf->Write(10, $fecha . ' - ' . $usuario);
        //pie de pagina scantec
        $pdf->SetMargins(170, 2, 2);
        $pdf->SetY(266);
        $pdf->SetFont('times', '', 7);
        $pdf->Write(10, "Generado por SCANTEC");
        // Lista de archivos PDF a combinar
        // Agregar los archivos PDF
        /*  $files = array($_FILES['pdf1']['tmp_name'], $_FILES['pdf2']['tmp_name'], $_FILES['pdf3']['tmp_name'],
         $_FILES['pdf4']['tmp_name'], $_FILES['pdf5']['tmp_name'], $_FILES['pdf6']['tmp_name'], $_FILES['pdf7']['tmp_name'],
         $_FILES['pdf8']['tmp_name'], $_FILES['pdf9']['tmp_name'], $_FILES['pdf10']['tmp_name'], $_FILES['pdf11']['tmp_name'],
         $_FILES['pdf12']['tmp_name'], $_FILES['pdf13']['tmp_name']);  */
        // Inicializar array vacío
        $files = array();

        // Recorremos del 1 al 13 para detectar los que sí fueron subidos
        for ($i = 1; $i <= 13; $i++) {
            if (!empty($_FILES["pdf$i"]['tmp_name']) && file_exists($_FILES["pdf$i"]['tmp_name'])) {
                $files[] = $_FILES["pdf$i"]['tmp_name'];
            }
        }
        // Recorrer la lista de archivos
        foreach ($files as $file) {
            //$pdf->setSourceFile($file);
            $pageCount = $pdf->setSourceFile($file);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $template = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($template);
                $pdf->addPage($size['orientation'], $size);
                $pdf->useTemplate($template);
                //$pag= $pageNo + 1;
            }
        }
        $ruta_creada = 'C:/unirpdf/';
        // $ruta_creada = '\\/Printeccdi\/unirpdf\/';
        $pdf->Output('F', $ruta_creada . $nombre_doc . '.pdf');
        //$pdf->Output('F', $ruta_creada . $columna_01.'-'.$columna_02.'-'.$columna_03.'.pdf');
        // funcion para insertar en la tabla unirpdf
        /* foreach ($files as $file) {
            $pdf->setSourceFile($file);
            $pageCount = $pdf->setSourceFile($file);
        }   */
        $usuario = $_SESSION['usuario'];
        // $usuario = $_POST[$usu];
        $columna_01 = "--";
        $columna_02 = "--";
        $columna_03 = "--";
        $ruta_creacion = $ruta_creada;
        $nombre_archivo = $columna_01 . '-' . $columna_02 . '-' . $columna_03 . '.pdf';
        $this->model->insertarUnirpdf($columna_01, $columna_02, $columna_03, $usuario, $nombre_archivo, $ruta_creacion);
        echo "<script>alert('Se ha generado el documento con éxito');window.history.back();</script>";
        //header("location: " . base_url() . "unirpdf/listar_playa");
        die();
        //echo "<script>alert('Se ha generado el archivo');window.history.back();</script>";            

        //echo "<script>alert('Se ha generado el archivo');window.history.back();</script>";

    }

    public function procesar_pdf()
    {
        ini_set('display_errors', 1);

    ini_set('display_startup_errors', 1);

    error_reporting(E_ALL); 
        // 1. Configuración
        set_time_limit(0);
        ini_set('memory_limit', '2048M');

        // Librerías
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Libraries/fpdi/src/autoload.php';
        require_once 'Config/ConfigPath.php';

        // 2. CSRF
        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        // 3. Datos
        $id_tipoDoc = filter_input(INPUT_POST, 'id_tipoDoc', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $indice_01  = filter_input(INPUT_POST, 'indice_01', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $indice_02  = filter_input(INPUT_POST, 'indice_02', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $indice_03  = filter_input(INPUT_POST, 'indice_03', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $indice_05  = filter_input(INPUT_POST, 'indice_05', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $indice_06  = filter_input(INPUT_POST, 'indice_06', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

        // Nombre del Archivo Final
        $raw_indice_04 = $_POST['indice_04'] ?? 'documento_sin_nombre';
        $nombre_archivo = preg_replace('/[^a-zA-Z0-9_-]/', '', $raw_indice_04);
        if(empty($nombre_archivo)) $nombre_archivo = "Doc_" . date("YmdHis");

        $usuario = $_SESSION['usuario'] ?? 'Sistema';
        $id_proceso = date("Ymd-His");

        // 4. Iniciar PDF y Metadatos
        try {
            if (class_exists('\setasign\Fpdi\Fpdi')) {
                $pdf = new \setasign\Fpdi\Fpdi();
            } else {
                $pdf = new Fpdi();
            }
        } catch (Exception $e) { die("Error PDF: " . $e->getMessage()); }

        $pdf->SetTitle($raw_indice_04, true);
        $pdf->SetAuthor($usuario, true);
        $pdf->SetCreator('Scantec', true);
        
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        // 5. Procesar Archivos (Con lógica de imágenes robusta)
        if (isset($_FILES['archivos']) && count($_FILES['archivos']['name']) > 0) {
            $total_files = count($_FILES['archivos']['name']);
            $tempDir = RUTA_BASE . 'Temp/';
            if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['archivos']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $tmpName = $_FILES['archivos']['tmp_name'][$i];
                $ext = strtolower(pathinfo($_FILES['archivos']['name'][$i], PATHINFO_EXTENSION));

                try {
                    if ($ext === 'pdf') {
                        $pageCount = $pdf->setSourceFile($tmpName);
                        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                            $tpl = $pdf->importPage($pageNo);
                            $sz = $pdf->getTemplateSize($tpl);
                            $ori = ($sz['width'] > $sz['height']) ? 'L' : 'P';
                            $pdf->AddPage($ori, [$sz['width'], $sz['height']]);
                            $pdf->useTemplate($tpl);
                        }
                    } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        $tempJpg = $tempDir . 'img_' . uniqid() . '.jpg';
                        $ok = false;
                        if ($ext === 'png') {
                            if (function_exists('imagecreatefrompng')) {
                                $src = @imagecreatefrompng($tmpName);
                                if ($src) {
                                    $w = imagesx($src); $h = imagesy($src);
                                    $dst = imagecreatetruecolor($w, $h);
                                    $white = imagecolorallocate($dst, 255, 255, 255);
                                    imagefill($dst, 0, 0, $white);
                                    imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
                                    imagejpeg($dst, $tempJpg, 90);
                                    imagedestroy($src); imagedestroy($dst);
                                    $ok = true;
                                }
                            } else { if(copy($tmpName, $tempJpg)) $ok = true; }
                        } else {
                            if (function_exists('imagecreatefromjpeg')) {
                                $src = @imagecreatefromjpeg($tmpName);
                                if ($src) { imagejpeg($src, $tempJpg, 90); imagedestroy($src); $ok = true; }
                                else { if(copy($tmpName, $tempJpg)) $ok = true; }
                            } else { if(copy($tmpName, $tempJpg)) $ok = true; }
                        }

                        if ($ok && file_exists($tempJpg)) {
                            list($wp, $hp) = getimagesize($tempJpg);
                            $mm = 25.4; $dpi = 96; 
                            $wm = ($wp * $mm) / $dpi; $hm = ($hp * $mm) / $dpi;
                            $o = ($wm > $hm) ? 'L' : 'P';
                            $pdf->AddPage($o, [$wm, $hm]);
                            $pdf->Image($tempJpg, 0, 0, $wm, $hm);
                            unlink($tempJpg);
                        }
                    }
                } catch (Exception $e) { continue; }
            }
        }

        // ============================================================
        // 6. GUARDAR (CARPETA = NOMBRE RECIBIDO DEL FORMULARIO)
        // ============================================================

        // A) Obtenemos el nombre directamente del POST (Input Oculto)
        // Si por alguna razón viene vacío, usamos 'Tipo_ID' como respaldo para no perder el archivo
        $nombreTipoRaw = $_POST['nombre_tipo_doc'] ?? ('Tipo_' . $id_tipoDoc);

        // B) Limpieza de seguridad para nombre de carpeta (CRÍTICO)
        // 1. Reemplazar espacios por guiones bajos para evitar problemas en URLs
        $nombreCarpeta = str_replace(' ', '_', $nombreTipoRaw);
        
        // 2. Eliminar acentos, ñ y caracteres especiales para compatibilidad Windows/Linux
        // Solo permitimos Letras (A-Z), Números (0-9), Guion bajo (_) y Guion medio (-)
        $nombreCarpeta = preg_replace('/[^A-Za-z0-9_\-]/', '', $nombreCarpeta);

        // Validación final: Si después de limpiar quedó vacío, ponemos un default
        if (empty($nombreCarpeta)) {
            $nombreCarpeta = "Documentos_Varios";
        }

        $nombre_final = $nombre_archivo . '.pdf';

        // C) Ruta Final: Expedientes / Nombre_Limpio / Archivo.pdf
        $ruta_relativa = 'Expedientes/' . $nombreCarpeta . '/' . $nombre_final;
        $ruta_absoluta = RUTA_BASE . $ruta_relativa;

        // Crear carpeta recursiva si no existe
        $dir = dirname($ruta_absoluta);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Guardar el PDF
        $pdf->Output('F', $ruta_absoluta);
        $paginas_totales = $pdf->PageNo();
        // 7. Base de Datos
        $this->model->insertarUnirpdf($indice_01, $indice_02, $indice_03, $usuario, $nombre_final, RUTA_BASE . 'Expedientes/' . $nombreCarpeta);
        $registrar = $this->model->registrarExpediente(
            $id_proceso, $id_tipoDoc, $indice_01, $indice_02, $indice_03,
            $raw_indice_04, $indice_05, $indice_06, $paginas_totales,
            $ruta_relativa, "scantec", "1.0", date("Y-m-d")
        );
        if ($registrar) {
            if(function_exists('setAlert')) {
                setAlert('success', "Guardado en carpeta: $nombreCarpeta");
            } else {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "Guardado."];
            }
        } else {
            if(function_exists('setAlert')) setAlert('error', "Error al registrar.");
        }

        session_write_close();
        header("Location: " . base_url() . "unirpdf/unir_documentos");
        exit();
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
        $pdf->Cell(20, 5, utf8_decode("Proyecto: " . $datos['nombre']), 0, 0, 'L');
        $pdf->Ln();
        $pdf->image(base_url() . 'Assets/img/icoPrintec.png', 13, 10, 8);
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
        $pdf->Cell(15, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        // $pdf->Cell(45, 6, utf8_decode('ID proceso'), 1, 0, 'C',1);
        $pdf->Cell(35, 6, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(168, 6, 'Indice 5', 1, 0, 'C', 1);
        //  $pdf->Cell(35, 6, 'Usuario', 1, 0, 'C',1);
        $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 7);
        //     $contador = 1;
        foreach ($expediente as $row) {
            $pdf->setX(10);
            $pdf->Cell(15, 6, $row['id_expediente'], 1, 0, 'C');
            //   $pdf->Cell(45, 6, utf8_decode($row['id_proceso']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['indice_01']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['indice_02']), 1, 0, 'C');
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
        $pdf->Cell(20, 5, utf8_decode("Proyecto: " . $datos['nombre']), 0, 0, 'L');
        $pdf->Ln();
        $pdf->image(base_url() . 'Assets/img/icoPrintec.png', 13, 10, 8);
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
        $pdf->Cell(15, 6, utf8_decode('N°'), 1, 0, 'C', 1);
        // $pdf->Cell(45, 6, utf8_decode('ID proceso'), 1, 0, 'C',1);
        $pdf->Cell(35, 6, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(35, 6, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(168, 6, 'Indice 5', 1, 0, 'C', 1);
        //  $pdf->Cell(35, 6, 'Usuario', 1, 0, 'C',1);
        $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 7);
        //     $contador = 1;
        foreach ($expediente as $row) {
            $pdf->setX(10);
            $pdf->Cell(15, 6, $row['id_expediente'], 1, 0, 'C');
            //   $pdf->Cell(45, 6, utf8_decode($row['id_proceso']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['indice_01']), 1, 0, 'C');
            $pdf->Cell(35, 6, utf8_decode($row['indice_02']), 1, 0, 'C');
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

    public function excel()
    {
        date_default_timezone_set('America/Asuncion');
        $expediente = $this->model->selectExpediente();
        $nombre = 'expediente';

        header('Expires: 0');
        header('Cache-control: private');
        header("Content-type: application/vnd.ms-excel;charset=utf-8"); // Archivo de Excel
        header("Cache-Control: cache, must-revalidate");
        header('Content-Description: File Transfer');
        header('Last-Modified: ' . date('D, d M Y H:i:s'));
        header("Pragma: public");
        header('Content-Disposition:attachment; filename="' . $nombre . '_' . date('Y_m_d_H:i:s') . '.xls"');
        header("Content-Transfer-Encoding: binary");


        echo utf8_decode("<table border='0'> 
						<tr > 
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;'>ID</td> 
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 01</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 02</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 03</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 04</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 05</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>PAGINAS</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>UBICACION</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>ESTADO</td>
						</tr>");


        //$reporte=conexion::consultas('productos','AND id<>0');

        foreach ($expediente as $value) {
            echo utf8_decode("<tr>
				 			
						<td style='border:1px solid #eee;'>" . $value["id_expediente"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_01"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_02"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_03"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_04"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["indice_05"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["paginas"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["ubicacion"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["estado"] . "</td>   
						</tr>");
        }

        echo "</table>";
    }

    public function excel_filtro()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        date_default_timezone_set('America/Asuncion');
        $expediente = $this->model->reporteExpedientes($desde, $hasta);
        $nombre = 'expediente';

        header('Expires: 0');
        header('Cache-control: private');
        header("Content-type: application/vnd.ms-excel;charset=utf-8"); // Archivo de Excel
        header("Cache-Control: cache, must-revalidate");
        header('Content-Description: File Transfer');
        header('Last-Modified: ' . date('D, d M Y H:i:s'));
        header("Pragma: public");
        header('Content-Disposition:attachment; filename="' . $nombre . '_' . date('Y_m_d_H:i:s') . '.xls"');
        header("Content-Transfer-Encoding: binary");


        echo utf8_decode("<table border='0'> 
						<tr > 
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;'>ID</td> 
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 01</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 02</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 03</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 04</td>
						<td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>INDICE 05</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>PAGINAS</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>UBICACION</td>
                        <td style='font-weight:bold; border:1px solid #eee;background: #A0A0A0;color:black;padding:10px;'>ESTADO</td>
						</tr>");


        //$reporte=conexion::consultas('productos','AND id<>0');

        foreach ($expediente as $value) {
            echo utf8_decode("<tr>
				 			
						<td style='border:1px solid #eee;'>" . $value["id_expediente"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_01"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_02"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_03"] . "</td>
						<td style='border:1px solid #eee;'>" . $value["indice_04"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["indice_05"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["paginas"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["ubicacion"] . "</td>
                        <td style='border:1px solid #eee;'>" . $value["estado"] . "</td>   
						</tr>");
        }

        echo "</table>";
    }

}