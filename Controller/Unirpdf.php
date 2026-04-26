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

                $archivoValidar = [
                    'name' => $_FILES['archivos']['name'][$i],
                    'type' => $_FILES['archivos']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['archivos']['tmp_name'][$i],
                    'error' => $_FILES['archivos']['error'][$i],
                    'size' => $_FILES['archivos']['size'][$i] ?? 0,
                ];
                if (!scantecValidarUpload($archivoValidar, ['pdf', 'jpg', 'jpeg', 'png'], ['application/pdf', 'image/jpeg', 'image/png'], 50 * 1024 * 1024)) {
                    continue;
                }

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


}
