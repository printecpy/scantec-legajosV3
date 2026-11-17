<?php
require_once 'Controller/Configuracion.php';
require_once 'Libraries/fpdi/src/autoload.php';
require_once 'Libraries/pdf/fpdf.php';

use setasign\Fpdi\Fpdi;

class Expedientes extends Controllers
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            $retorno = ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' ? rutaRetornoSegura((string) ($_SERVER['REQUEST_URI'] ?? ''), base_url()) : null;
            if ($retorno !== null) {          // solo las dos rutas de la consulta devuelven algo
                $_SESSION['retorno_login'] = $retorno;
                setAlert('info', 'Inicie sesión para continuar con la consulta.');
            }
            header("location: " . base_url());
            exit();
        }
        parent::__construct();
    }
    public function expedientes()
    {
        $expediente = $this->model->selectExpediente();
        $data = [
            'expediente' => $expediente,
            'puede_gestionar' => $this->tieneAccesoFuncionalidad('gestionar_expedientes'),
        ];
        $this->views->getView($this, "listar", $data);
    }

    public function indice_busqueda()
    {
        $tipos_documentos = $this->model->selectTipoDoc();
        $data = ['tipos_documentos' => $tipos_documentos];
        $this->views->getView($this, "indice_busqueda", $data);
    }

    // Valida el permiso de gestión y que el documento sea de un tipo del grupo del usuario. Rol 1: sin restricción de grupo.
    private function expedienteGestionable(int $id_expediente): array
    {
        $this->asegurarAccesoFuncionalidad(
            'gestionar_expedientes',
            'No tienes permiso para modificar documentos.',
            'expedientes/gestion'
        );

        $expediente = null;
        if ($id_expediente > 0) {
            if (intval($_SESSION['id_rol'] ?? 0) === 1) {
                $res = $this->model->editExpediente($id_expediente);
                $expediente = isset($res[0]) ? $res[0] : ($res ?: null);
            } else {
                $expediente = $this->model->obtenerExpedienteGestionable($id_expediente, intval($_SESSION['id_grupo'] ?? 0));
            }
        }

        if (empty($expediente)) {
            setAlert('warning', 'Documento no encontrado o sin permisos de acceso.');
            header('Location: ' . base_url() . 'expedientes/busqueda');
            exit();
        }

        return $expediente;
    }

    public function upload_files()
    {
        $tipos_documentos = $this->model->selectTipoDoc();
        $data = ['tipos_documentos' => $tipos_documentos];
        $this->views->getView($this, "upload_files", $data);
    }

    public function estado_legajo()
    {
        header("Location: " . base_url() . "legajos/estado_legajo");
        exit();
    }

    public function busqueda()
    {
        // FILTER_SANITIZE_NUMBER_INT se mantiene, pero validamos explícitamente el tipo con VALIDATE_INT
        $id_tipoDoc = filter_input(INPUT_GET, 'id_tipoDoc', FILTER_VALIDATE_INT);
        $id_tipoDoc = $id_tipoDoc ?: 0;

        // FILTER_SANITIZE_STRING está DEPRECADO desde PHP 8.1 (removido en 9.0).
        // Reemplazado por trim + htmlspecialchars, que cumple el mismo propósito de forma soportada.
        $terminoRaw = filter_input(INPUT_GET, 'termino', FILTER_UNSAFE_RAW);
        $termino = $terminoRaw !== null ? trim($terminoRaw) : '';
        $termino = htmlspecialchars($termino, ENT_QUOTES, 'UTF-8');

        $busqueda = $this->model->buscarExpedientePorTermino($id_tipoDoc, $termino);
        $data = ['busqueda' => $busqueda, 'termino' => $termino];
        $this->views->getView($this, "busqueda", $data);
    }

    public function mostrar_registros()
    {
        // Único parámetro que ahora viaja en la URL: id_expediente (numérico, no revela datos de negocio)
        $id_expediente = filter_input(INPUT_GET, 'id_expediente', FILTER_VALIDATE_INT);

        if (!$id_expediente || $id_expediente <= 0) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Solicitud inválida: expediente no especificado.'];
            header('Location: ' . base_url() . 'expedientes/busqueda');
            exit;
        }

        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);

        // Resuelve indice_01 + id_tipoDoc de forma segura a partir del ID,
        // validando en la misma consulta que el usuario tenga permiso sobre ese tipo de documento.
        $referencia = $this->model->obtenerReferenciaExpediente($id_expediente, $id_grupo);

        if (!$referencia) {
            // No existe O no pertenece al grupo del usuario: mismo mensaje en ambos casos
            // para no revelar si el ID existe (evita enumeración de expedientes ajenos).
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Expediente no encontrado o sin permisos de acceso.'];
            header('Location: ' . base_url() . 'expedientes/busqueda');
            exit;
        }

        $mostrar_registros = $this->model->selectRegistros2(
            $referencia['indice_01'],
            (int) $referencia['id_tipoDoc'],
            $id_grupo
        );

        $data = [
            'mostrar_registros' => $mostrar_registros,
            'puede_gestionar' => $this->tieneAccesoFuncionalidad('gestionar_expedientes'),
        ];
        $this->views->getView($this, "mostrar_registros", $data);
    }

    // Formatos aceptados para el indice 1. Se elige UNO por nombre desde el .env (nunca una regex libre).
    private const FORMATOS_INDICE_1 = [
        'numerico' => '/^[0-9]{1,15}$/D',
        'alfanumerico' => '/^[A-Za-z0-9._-]{1,30}$/D',
    ];

    private function etiquetaIndice1(): string
    {
        return (defined('CONSULTA_INDICE1_ETIQUETA') && CONSULTA_INDICE1_ETIQUETA !== '')
            ? (string) CONSULTA_INDICE1_ETIQUETA
            : 'socio';
    }

    private function patronIndice1(): string
    {

        $formato = defined('CONSULTA_INDICE1_FORMATO') ? strtolower(trim((string) CONSULTA_INDICE1_FORMATO)) : 'numerico';
        return self::FORMATOS_INDICE_1[$formato] ?? self::FORMATOS_INDICE_1['numerico'];   // valor desconocido -> el mas estricto
    }

    public function buscarporindice01()
    {
        $id_grupo = $this->accesoConsultaIndice1();

        $id_tipoDoc = max(0, (int) filter_var($_GET['id_tipoDoc'] ?? 0, FILTER_VALIDATE_INT));
        $indice1 = is_string($_GET['indice1'] ?? null) ? trim($_GET['indice1']) : '';

        $data = ['etiqueta' => $this->etiquetaIndice1(), 'indice1' => $indice1, 'buscarporindice' => [], 'error' => ''];

        if (!preg_match($this->patronIndice1(), $indice1)) {
            http_response_code(400);
            $data['error'] = 'El valor de búsqueda no es válido.';
        } else {
            $data['buscarporindice'] = $this->model->buscarExpedientesPorIndice01($indice1, $id_tipoDoc, $id_grupo);
        }

        $this->views->getView($this, "buscarporindice", $data);
    }

    public function mostrar_resultados()
    {
        $id_grupo = $this->accesoConsultaIndice1();

        $data = ['mostrar_resultados' => [], 'indice1' => '', 'volver' => '', 'error' => ''];
        $id_expediente = (int) filter_var($_GET['id_expediente'] ?? 0, FILTER_VALIDATE_INT);

        // Resuelve indice_01 + tipo desde el id, solo si el grupo tiene permiso vigente sobre ese tipo.
        // Mismo mensaje si el id no existe o no tiene permiso: no se revela si existe.
        $ref = $id_expediente > 0 ? $this->model->obtenerReferenciaExpediente2($id_expediente, $id_grupo) : null;

        if (!$ref) {
            http_response_code(404);
            $data['error'] = 'Expediente no encontrado o sin permisos de acceso.';
        } else {
            $data['indice1'] = (string) $ref['indice_01'];
            $data['mostrar_resultados'] = $this->model->selectRegistros($data['indice1'], (int) $ref['id_tipoDoc'], $id_grupo);
            if (preg_match($this->patronIndice1(), $data['indice1'])) {
                $data['volver'] = base_url() . 'expedientes/buscarporindice01?id_tipoDoc=0&indice1=' . urlencode($data['indice1']);
            }
        }

        $this->views->getView($this, "mostrar_resultados", $data);
    }

    // Mismos permisos que el menu (header.php): seccion "archivos" habilitada y acceso "buscador_archivos"
    // para su rol/departamento. Rol 1 y root las ignoran. Devuelve el id_grupo del usuario.
    private function accesoConsultaIndice1(): int
    {
        if (!$this->tieneAccesoFuncionalidad('consulta_indice1')) {
            http_response_code(403);
            echo 'No tiene permiso para esta consulta.';
            exit;
        }
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        if ($id_grupo <= 0) {
            http_response_code(403);
            echo 'Su usuario no tiene un grupo asignado.';
            exit;
        }
        header('Cache-Control: no-store, private');   // hay datos personales en pantalla
        return $id_grupo;
    }

    // Valida la ruta relativa guardada en la BD y la resuelve dentro de RUTA_BASE.
// No usa realpath(): funciona igual con disco local (C:, D:), carpetas enlazadas y NAS por UNC (\\servidor\recurso).
    private function rutaDentroDeStorage(string $relativa): ?string
    {
        $relativa = trim(str_replace('\\', '/', $relativa));
        if ($relativa === '' || preg_match('/[\x00-\x1F]/', $relativa)) {
            return null;                                   // vacía o con caracteres de control (\0, saltos)
        }
        if ($relativa[0] === '/' || preg_match('#^[A-Za-z]:#', $relativa)) {
            return null;                                   // absoluta, UNC (//servidor) o con letra de unidad
        }

        $partes = [];
        foreach (explode('/', $relativa) as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if (strpbrk($seg, ':*?"<>|') !== false) {
                return null;                               // flujos alternos (::$DATA) y caracteres inválidos en Windows
            }
            if (trim($seg, ' .') === '') {
                return null;                               // "..", "...", ".. " (Windows ignora puntos y espacios finales)
            }
            $partes[] = $seg;
        }
        if (!$partes) {
            return null;
        }

        $ruta = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $partes);
        return is_file($ruta) ? $ruta : null;
    }

    public function reporte()
    {
        $data = $this->model->selectExpediente();
        $this->views->getView($this, "reporte", $data);
    }

    public function firmador()
    {
        $data = $this->model->selectExpediente();
        $this->views->getView($this, "firmador", $data);
    }

    public function editar()
    {
        $expediente = $this->expedienteGestionable(intval($_GET['id_expediente'] ?? 0));

        $data = [
            'page_title' => 'Modificar Expediente',
            'expediente' => $expediente,
        ];
        $this->views->getView($this, "editar", $data);
    }

    public function modificar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', 'Token de seguridad inválido o expirado.');
            header('Location: ' . base_url() . 'expedientes/busqueda');
            exit();
        }

        $id_expediente = intval($_POST['id_expediente'] ?? 0);
        $this->expedienteGestionable($id_expediente);   // corta si no corresponde

        $id_proceso = (string) ($_POST['id_proceso'] ?? '');
        $indice_01 = trim((string) ($_POST['indice_01'] ?? ''));
        $indice_02 = trim((string) ($_POST['indice_02'] ?? ''));
        $indice_03 = trim((string) ($_POST['indice_03'] ?? ''));
        $indice_04 = trim((string) ($_POST['indice_04'] ?? ''));
        $indice_05 = trim((string) ($_POST['indice_05'] ?? ''));
        $indice_06 = trim((string) ($_POST['indice_06'] ?? ''));
        $ubicacion = trim((string) ($_POST['ubicacion'] ?? ''));
        $firma_digital = in_array($_POST['firma_digital'] ?? 'no', ['si', 'no'], true) ? $_POST['firma_digital'] : 'no';
        $version = trim((string) ($_POST['version'] ?? '1.0'));
        $paginas = max(1, intval($_POST['paginas'] ?? 1));

        $actualizar = $this->model->actualizarExpediente(
            $id_proceso,
            $indice_01,
            $indice_02,
            $indice_03,
            $indice_04,
            $indice_05,
            $indice_06,
            $ubicacion,
            $firma_digital,
            $version,
            $paginas,
            $id_expediente
        );

        if ($actualizar !== false) {
            setAlert('success', 'Expediente modificado correctamente.');
        } else {
            setAlert('error', 'Error al guardar los cambios en la base de datos.');
        }
        header('Location: ' . base_url() . $this->rutaVolver('expedientes/editar?id_expediente=' . $id_expediente));
        exit();
    }

    public function subir()
    {
        // 1. CONFIGURACIÓN BÁSICA
        set_time_limit(500);

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "?error=csrf");
            die();
        }

        date_default_timezone_set('America/Asuncion');
        $id_proceso = date("Ymd-His");

        // 2. RECUPERACIÓN DE DATOS E ÍNDICES
        $id_tipoDoc = filter_input(INPUT_POST, 'id_tipoDoc', FILTER_SANITIZE_STRING);

        $tipoDocData = $this->model->getTipoDocById($id_tipoDoc);
        $nombre_tipo = (!empty($tipoDocData) && isset($tipoDocData[0]['nombre_tipoDoc']))
            ? $tipoDocData[0]['nombre_tipoDoc']
            : 'Otros';
        $nombre_carpeta_tipo = preg_replace('/[^a-zA-Z0-9_-]/', '', $nombre_tipo);

        $indice_01 = filter_input(INPUT_POST, 'indice_01', FILTER_SANITIZE_STRING);
        $indice_02 = filter_input(INPUT_POST, 'indice_02', FILTER_SANITIZE_STRING);
        $indice_03 = filter_input(INPUT_POST, 'indice_03', FILTER_SANITIZE_STRING);
        $indice_04 = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['indice_04']);
        $indice_05 = filter_input(INPUT_POST, 'indice_05', FILTER_SANITIZE_STRING);
        $indice_06 = filter_input(INPUT_POST, 'indice_06', FILTER_SANITIZE_STRING);

        $nombreSeguro = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $indice_04);
        $nombre_final = ($nombreSeguro !== '' ? $nombreSeguro : uniqid('expediente_', true)) . '.pdf';

        $ruta_relativa = 'Expedientes/' . $nombre_carpeta_tipo . '/' . $nombre_final;
        $ubicacion_fisica = RUTA_BASE . $ruta_relativa;

        // 3. VALIDACIÓN DE SEGURIDAD DEL ARCHIVO
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            setAlert('error', "Error al subir el archivo.");
            header("location: " . base_url() . "expedientes/upload_files");
            exit();
        }

        if (!scantecValidarUpload($_FILES['file_pdf'], ['pdf'], ['application/pdf'], 50 * 1024 * 1024)) {
            setAlert('error', "El archivo debe ser un PDF válido y no superar 50 MB.");
            header("location: " . base_url() . "expedientes/upload_files");
            exit();
        }

        // 4. GUARDAR ARCHIVO ORIGINAL (SIN ALTERACIONES)
        $dir_expedientes = dirname($ubicacion_fisica);
        if (!is_dir($dir_expedientes)) {
            mkdir($dir_expedientes, 0777, true);
        }

        if (!move_uploaded_file($_FILES['file_pdf']['tmp_name'], $ubicacion_fisica)) {
            setAlert('error', "Error al guardar el archivo en el servidor.");
            header("location: " . base_url() . "expedientes/upload_files");
            exit();
        }

        // =============================================
        // 5. CONTEO DE PÁGINAS EXACTO E INOFENSIVO
        // =============================================
        $num_paginas = 1;
        $magick_escaped = escapeshellarg(MAGICK_EXECUTABLE_PATH);
        $pdf_escaped = escapeshellarg($ubicacion_fisica);

        // Usamos identify básico: Esto devuelve una línea de texto por cada página que detecte
        exec("$magick_escaped identify $pdf_escaped 2>&1", $salida_ident);

        if (!empty($salida_ident)) {
            $paginas_reales = 0;
            foreach ($salida_ident as $linea) {
                // Filtramos que la línea sea legítima (contiene 'PDF')
                if (stripos($linea, 'PDF') !== false) {
                    $paginas_reales++;
                }
            }
            if ($paginas_reales > 0) {
                $num_paginas = $paginas_reales;
            }
        }

        // =============================================
        // 6. REGISTRO EN BASE DE DATOS
        // =============================================
        $registrar = $this->model->registrarExpediente(
            $id_proceso,
            $id_tipoDoc,
            $indice_01,
            $indice_02,
            $indice_03,
            $indice_04,
            $indice_05,
            $indice_06,
            $num_paginas,
            $ruta_relativa,
            "scantec",
            "1.0",
            date("Y-m-d")
        );

        if ($registrar) {
            setAlert('success', "Procesado correctamente. Páginas: $num_paginas");
        } else {
            setAlert('error', "Error al registrar en la base de datos.");
        }

        session_write_close();
        header("location: " . base_url() . "expedientes/upload_files");
        exit();
    }

    public function eliminar()
    {
        $this->cambiarEstadoExpediente('Inactivo', 'Documento anulado correctamente.');
    }

    public function reingresar()
    {
        $this->cambiarEstadoExpediente('Activo', 'Documento reactivado correctamente.');
    }

    private function cambiarEstadoExpediente(string $estado, string $mensaje): void
    {
        if (!Validador::csrfValido()) {
            setAlert('error', 'Token de seguridad inválido o expirado.');
            header('Location: ' . base_url() . $this->rutaVolver());
            exit();
        }

        $id_expediente = intval($_POST['id_expediente'] ?? 0);
        $this->expedienteGestionable($id_expediente);

        $this->model->estadoExpediente($estado, $id_expediente);
        setAlert('success', $mensaje);
        header('Location: ' . base_url() . 'expedientes/busqueda');
        exit();
    }

    // Ruta interna a la que volver después de una acción. Solo se aceptan pantallas conocidas del módulo,
// nunca una URL completa: así no se abre una redirección hacia otro sitio.
    private function rutaVolver(string $porDefecto = 'expedientes/busqueda'): string
    {
        $volver = trim((string) ($_POST['volver'] ?? ''));
        $permitida = '#^expedientes/(busqueda|listar|mostrar_registros|editar)(\?[A-Za-z0-9_=&%+.\-]*)?$#';
        return preg_match($permitida, $volver) ? $volver : $porDefecto;
    }

    // Método auxiliar privado para validar la seguridad del archivo
    private function getRutaSegura(int $id_expediente)
    {
        $expedienteData = $this->model->obtenerRutaExpediente($id_expediente);

        if (!$expedienteData || empty($expedienteData['ruta_original'])) {
            return false;
        }

        $ruta_absoluta = realpath(RUTA_BASE . $expedienteData['ruta_original']);
        $ruta_base_segura = realpath(RUTA_BASE);

        // Validación: El archivo debe existir y estar dentro de RUTA_BASE
        if ($ruta_absoluta === false || strpos($ruta_absoluta, $ruta_base_segura) !== 0 || !file_exists($ruta_absoluta)) {
            return false;
        }

        return $ruta_absoluta;
    }

    public function ver_expediente()
    {
        $id_expediente = intval($_GET['id_expediente'] ?? 0);
        $ruta_archivo = $this->getRutaSegura($id_expediente);

        if (!$ruta_archivo) {
            setAlert('warning', 'Archivo no encontrado o acceso no autorizado.');
            if (!$ruta_archivo) {
                setAlert('warning', 'Archivo no encontrado o acceso no autorizado.');
                header('Location: ' . base_url() . 'expedientes/busqueda');
                exit;
            }
            exit;
        }

        // Registrar visualización
        $this->model->registrar_visualizacion(
            intval($_SESSION['id']),
            $id_expediente,
            htmlspecialchars($_SESSION['usuario']),
            @gethostbyaddr($_SERVER['REMOTE_ADDR']),
            basename($ruta_archivo),
            $_SERVER['REMOTE_ADDR'] ?? '',
            date("Y-m-d H:i:s")
        );

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($ruta_archivo) . '"');
        header('Content-Length: ' . filesize($ruta_archivo));
        readfile($ruta_archivo);
        exit;
    }

    public function expediente()
    {
        $id_expediente = intval($_GET['id_expediente'] ?? 0);
        $ruta_archivo = $this->getRutaSegura($id_expediente);

        if (!$ruta_archivo) {
            echo "<script>alert('Archivo no encontrado!');window.history.back();</script>";
            exit;
        }

        // 1. Registramos la visualización primero para asegurarnos de que quede en el log
        $this->model->registrar_visualizacion(
            intval($_SESSION['id']),
            $id_expediente,
            htmlspecialchars($_SESSION['usuario']),
            @gethostbyaddr($_SERVER['REMOTE_ADDR']),
            basename($ruta_archivo),
            $_SERVER['REMOTE_ADDR'] ?? '',
            date('Y-m-d H:i:s')
        );

        // 2. Intentamos usar FPDI. Si el PDF es moderno (1.5+) y explota, lo capturamos.
        try {
            $pdf = new FPDI();
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(128, 128, 128);
            $fecha_hora = date('Y-m-d H:i:s');
            $token = bin2hex(random_bytes(16));

            // Esta es la línea que causaba el Error 500
            $pageCount = $pdf->setSourceFile($ruta_archivo);

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], $size);
                $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);
                $pdf->SetXY(10, 5);
                $pdf->Write(0, "{$token}_{$fecha_hora}_User:{$_SESSION['nombre']}");
            }

            ob_clean();
            $pdf->Output('I', basename($ruta_archivo));

        } catch (\Exception $e) {
            // FALLBACK 1: Excepción de FPDI. Mostramos el original sin colapsar.
            ob_clean();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($ruta_archivo) . '"');
            header('Content-Length: ' . filesize($ruta_archivo));
            readfile($ruta_archivo);
        } catch (\Throwable $e) {
            // FALLBACK 2: Error Fatal general de PHP.
            ob_clean();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($ruta_archivo) . '"');
            header('Content-Length: ' . filesize($ruta_archivo));
            readfile($ruta_archivo);
        }
        exit;
    }

    public function getApiToken()
    {
        $credentials = $this->model->getApiCredentials('API de Firma Digital');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['base_url'] . '/api/v1/Auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'username' => $credentials['api_key'],
                'password' => $credentials['api_secret']
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $responseData = json_decode($response, true);

        if (isset($responseData['token'])) {
            return $responseData['token']; // Token solo para esta operación
        } else {
            throw new Exception("No se pudo obtener el token.");
        }
    }

    // Método para firmar el archivo usando un webhook
    public function signFileWithWebhook($idExpediente, $fileToSign, $usernameCert, $passwordCert, $pinCert)
    {
        $token = $this->getApiToken();
        $webhookUrl = "http://181.94.210.128/webhooks/webhook.php"; // URL del webhook accesible
        $logUrl = "http://181.94.210.128/webhooks/webhook_log.php"; // URL para logs

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://datamex.com.py:11801/ltv/api/v1/Timestamp/signfilewebhook',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array("Authorization: Bearer $token"),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'level' => 'T',
                'username' => $usernameCert,
                'password' => $passwordCert,
                'pin' => $pinCert,
                'urlout' => $webhookUrl . "/result_" . basename($fileToSign),
                'urlback' => $logUrl . "/log_" . basename($fileToSign),
                'file_in' => new CURLFILE($fileToSign),
                'graficarfirma' => '1'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response, true);

        // Registrar firma en 'registros_firmas'
        if ($result['success']) {
            $this->model->registrarFirma($idExpediente, array(
                'tipo_firma' => 'avanzada', // O el tipo seleccionado
                'detalles_firma' => $result
            ));
        }

        return $result;
    }

    public function metadatos()
    {
        $id_expediente = intval($_POST['id_expediente'] ?? 0);
        $archivo_pdf = $this->getRutaSegura($id_expediente);

        if (!$archivo_pdf) {
            http_response_code(404);
            echo "<div class='text-red-500 p-4'>Archivo no encontrado o sin permisos de acceso.</div>";
            exit;
        }

        $nombre = basename($archivo_pdf);
        $peso = round(filesize($archivo_pdf) / 1024, 2) . " KB";
        $fecha = date("d/m/Y H:i:s", filemtime($archivo_pdf));

        $meta_avanzada = [];
        if (defined('RUTA_EXIFTOOL') && is_file(RUTA_EXIFTOOL)) {
            $comando = escapeshellarg(RUTA_EXIFTOOL) . ' -j ' . escapeshellarg($archivo_pdf);
            $output = shell_exec($comando);
            if ($output) {
                $data = json_decode($output, true);
                if (!empty($data) && isset($data[0])) {
                    $meta_avanzada = $data[0];
                }
            }
        }

        $e = function ($v) {
            return htmlspecialchars(is_scalar($v) ? (string) $v : json_encode($v), ENT_QUOTES, 'UTF-8');
        };
        ?>
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <h4 class="font-bold text-scantec-blue mb-2 text-sm uppercase">Información del Archivo</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li class="flex justify-between"><span
                            class="font-semibold">Nombre:</span><span><?php echo $e($nombre); ?></span></li>
                    <li class="flex justify-between"><span
                            class="font-semibold">Tamaño:</span><span><?php echo $e($peso); ?></span></li>
                    <li class="flex justify-between"><span
                            class="font-semibold">Modificado:</span><span><?php echo $e($fecha); ?></span></li>
                </ul>
            </div>

            <?php if (!empty($meta_avanzada)): ?>
                <div class="border-t border-gray-100 pt-4">
                    <h4 class="font-bold text-gray-600 mb-3 text-sm uppercase">Metadatos Internos (PDF)</h4>
                    <div class="grid grid-cols-1 gap-2 text-sm">
                        <?php
                        $campos_interes = [
                            'Title' => 'Título',
                            'Author' => 'Autor',
                            'Subject' => 'Asunto',
                            'Keywords' => 'Palabras Clave',
                            'Creator' => 'Creador',
                            'Producer' => 'Productor PDF',
                            'CreateDate' => 'Fecha Creación',
                            'PageCount' => 'Páginas',
                            'PDFVersion' => 'Versión PDF',
                        ];
                        foreach ($campos_interes as $key => $label):
                            if (isset($meta_avanzada[$key])): ?>
                                <div class="flex flex-col border-b border-gray-50 pb-1">
                                    <span class="text-xs text-gray-400 font-bold uppercase"><?php echo $e($label); ?></span>
                                    <span class="text-gray-800"><?php echo $e($meta_avanzada[$key]); ?></span>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-2">
                    <p class="text-xs text-gray-400 italic">No se pudieron leer metadatos internos (ExifTool no configurado o sin
                        datos).</p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function asegurarAccesoFacturacionExpedientes(): void
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        if ($idRol === 1) {
            return;
        }

        require_once 'Models/FuncionalidadesModel.php';
        $funcionalidadesModel = new FuncionalidadesModel();
        $idDepartamento = intval($_SESSION['id_departamento'] ?? 0);

        if ($funcionalidadesModel->puedeAccederItemPorContexto('facturacion', $idRol, $idDepartamento)) {
            return;
        }

        if (function_exists('setAlert')) {
            setAlert('warning', 'No tienes permiso para acceder a facturación.');
        }
        header('Location: ' . base_url() . 'dashboard/dashboard');
        exit();
    }

    private function facturacionFechaValida(string $fecha): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d !== false && $d->format('Y-m-d') === $fecha;
    }

    private function facturacionFiltros(): array
    {
        $desde = trim((string) ($_GET['desde'] ?? ''));
        $hasta = trim((string) ($_GET['hasta'] ?? ''));

        if (!$this->facturacionFechaValida($desde)) {
            $desde = date('Y-m-01');
        }
        if (!$this->facturacionFechaValida($hasta)) {
            $hasta = date('Y-m-t');
        }
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return ['desde' => $desde, 'hasta' => $hasta];
    }

    /** Definiciones que se imprimen en pantalla, PDF y Excel: lo que se factura y lo que no, sin zonas grises. */
    private function facturacionCriterios(): array
    {
        return [
            'Documento: un archivo PDF indexado en SCANTEC (un registro por archivo), cargado desde la aplicación o desde Umango.',
            'Documentos nuevos: se cuentan una sola vez por documento, según su fecha de indexado.',
            'Actualizaciones: se registra una actualización cuando cambia la cantidad de páginas de un documento o se le asigna una nueva fecha de actualización. Se cuentan según la fecha de actualización y todas las páginas del documento actualizado se consideran procesadas.',
            'Páginas: la cantidad registrada en el sistema en el momento de cada hecho. El total de páginas es la suma de las páginas de los documentos nuevos y de las actualizaciones.',
            'Anular o reingresar un documento no agrega ni quita hechos. La columna "Activos hoy" informa cuántos de los documentos del período siguen activos al generar el reporte; es solo informativa.',
            'No se incluyen los documentos de legajos: se informan en la pestaña Legajos.',
            'Limitación: dos actualizaciones del mismo documento en el mismo día y con la misma cantidad de páginas no se pueden distinguir y se registran como una.',
            'Hechos reconstruidos: los documentos que ya existían al activar este registro se cargaron con su fecha de indexado y sus páginas actuales. Se indican en el control del reporte.',
        ];
    }

    /** Todas las cifras del reporte, en un solo lugar: pantalla, PDF y Excel salen de los mismos numeros. */
    private function facturacionDatos(array $filtros): array
    {
        $instalada = $this->model->existeFacturacionExpedientes();
        $nuevos = $instalada ? $this->model->selectFacturacionNuevos($filtros['desde'], $filtros['hasta']) : [];
        $actualizados = $instalada ? $this->model->selectFacturacionActualizados($filtros['desde'], $filtros['hasta']) : [];

        $t = [
            'nuevos_documentos' => 0,
            'nuevos_paginas' => 0,
            'nuevos_activos_documentos' => 0,
            'nuevos_activos_paginas' => 0,
            'act_actualizaciones' => 0,
            'act_archivos' => 0,
            'act_paginas' => 0,
            'sin_conteo' => 0,
            'reconstruidos' => 0,
        ];
        foreach ($nuevos as $f) {
            $t['nuevos_documentos'] += intval($f['documentos'] ?? 0);
            $t['nuevos_paginas'] += intval($f['paginas'] ?? 0);
            $t['nuevos_activos_documentos'] += intval($f['documentos_activos_hoy'] ?? 0);
            $t['nuevos_activos_paginas'] += intval($f['paginas_activas_hoy'] ?? 0);
            $t['sin_conteo'] += intval($f['sin_conteo'] ?? 0);
            $t['reconstruidos'] += intval($f['reconstruidos'] ?? 0);
        }
        foreach ($actualizados as $f) {
            $t['act_actualizaciones'] += intval($f['actualizaciones'] ?? 0);
            $t['act_archivos'] += intval($f['archivos'] ?? 0);
            $t['act_paginas'] += intval($f['paginas'] ?? 0);
            $t['sin_conteo'] += intval($f['sin_conteo'] ?? 0);
            $t['reconstruidos'] += intval($f['reconstruidos'] ?? 0);
        }
        $t['total_paginas'] = $t['nuevos_paginas'] + $t['act_paginas'];
        $t['total_operaciones'] = $t['nuevos_documentos'] + $t['act_actualizaciones'];
        $t['nuevos_no_activos'] = $t['nuevos_documentos'] - $t['nuevos_activos_documentos'];

        return [
            'instalada' => $instalada,
            'nuevos' => $nuevos,
            'actualizados' => $actualizados,
            'totales' => $t,
            'inicio_registro' => $instalada ? $this->model->selectFacturacionInicioRegistro() : null,
            'criterios' => $this->facturacionCriterios(),
        ];
    }

    public function facturacion()
    {
        $this->asegurarAccesoFacturacionExpedientes();
        $filtros = $this->facturacionFiltros();
        $datos = $this->facturacionDatos($filtros);

        $limiteDetalle = 300;
        $detalle = $datos['instalada'] ? $this->model->selectFacturacionDetalle($filtros['desde'], $filtros['hasta']) : [];

        $data = [
            'filtros' => $filtros,
            'datos' => $datos,
            'detalle_total' => count($detalle),
            'detalle' => array_slice($detalle, 0, $limiteDetalle),
            'detalle_limite' => $limiteDetalle,
        ];

        header('Cache-Control: no-store, private');
        $this->views->getView($this, "facturacion", $data);
    }

    private function facturacionPdfTexto($texto): string
    {
        $r = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $texto);
        return $r === false ? '' : $r;
    }

    /** Dibuja una tabla con encabezado que se repite en cada pagina y filas que nunca se parten entre paginas. */
    private function facturacionPdfTabla($pdf, array $encabezados, array $anchos, array $alineaciones, array $filas, ?array $total): void
    {
        $alto = 6;
        $dibujarEncabezado = function () use ($pdf, $encabezados, $anchos, $alto) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(24, 37, 65);
            $pdf->SetTextColor(255, 255, 255);
            foreach ($encabezados as $i => $h) {
                $pdf->Cell($anchos[$i], $alto + 1, $this->facturacionPdfTexto($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 9);
        };
        $ajustar = function (string $texto, float $ancho) use ($pdf): string {
            $t = $this->facturacionPdfTexto($texto);
            if ($pdf->GetStringWidth($t) <= $ancho - 2) {
                return $t;
            }
            while (strlen($t) > 3 && $pdf->GetStringWidth($t . '...') > $ancho - 2) {
                $t = substr($t, 0, -1);
            }
            return $t . '...';
        };
        $hayEspacio = function (float $necesario) use ($pdf): bool {
            return $pdf->GetY() + $necesario <= $pdf->GetPageHeight() - 25;
        };

        if (!$hayEspacio($alto * 3)) {
            $pdf->AddPage();
        }
        $dibujarEncabezado();
        foreach ($filas as $fila) {
            if (!$hayEspacio($alto)) {
                $pdf->AddPage();
                $dibujarEncabezado();
            }
            foreach ($fila as $i => $valor) {
                $pdf->Cell($anchos[$i], $alto, $ajustar((string) $valor, $anchos[$i]), 1, 0, $alineaciones[$i]);
            }
            $pdf->Ln();
        }
        if ($total !== null) {
            if (!$hayEspacio($alto)) {
                $pdf->AddPage();
                $dibujarEncabezado();
            }
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(230, 230, 230);
            foreach ($total as $i => $valor) {
                $pdf->Cell($anchos[$i], $alto, $ajustar((string) $valor, $anchos[$i]), 1, 0, $alineaciones[$i], true);
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 9);
        }
    }

    public function facturacionPdf()
    {
        $this->asegurarAccesoFacturacionExpedientes();
        $filtros = $this->facturacionFiltros();
        $datos = $this->facturacionDatos($filtros);
        $t = $datos['totales'];
        $fmt = function ($n) {
            return number_format((int) $n, 0, ',', '.');
        };
        $fecha = function ($iso) {
            $ts = strtotime((string) $iso);
            return $ts ? date('d/m/Y', $ts) : '';
        };

        if (ob_get_length()) {
            ob_end_clean();
        }
        require_once 'Helpers/ReportTemplatePDF.php';

        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Facturacion de expedientes', 'L', 'A4');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $this->facturacionPdfTexto('Reporte de Facturación de Expedientes'), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $this->facturacionPdfTexto('Período: ' . $fecha($filtros['desde']) . ' a ' . $fecha($filtros['hasta'])), 0, 1, 'L');
        $pdf->Cell(0, 6, $this->facturacionPdfTexto('Generado: ' . date('d/m/Y H:i') . ' por ' . ($_SESSION['nombre'] ?? '')), 0, 1, 'L');
        if (!$datos['instalada']) {
            $pdf->Ln(2);
            $pdf->Cell(0, 6, $this->facturacionPdfTexto('El registro de facturación de expedientes todavía no está instalado en esta base de datos.'), 0, 1, 'L');
            $pdf->Output("Facturacion_Expedientes.pdf", "I");
            exit();
        }
        $pdf->Ln(3);

        // Resumen
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(65, 7, $this->facturacionPdfTexto('Documentos nuevos'), 0, 0, 'L', true);
        $pdf->Cell(65, 7, $this->facturacionPdfTexto('Actualizaciones'), 0, 0, 'L', true);
        $pdf->Cell(65, 7, $this->facturacionPdfTexto('Total de operaciones'), 0, 0, 'L', true);
        $pdf->Cell(65, 7, $this->facturacionPdfTexto('Total de páginas'), 0, 1, 'L', true);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(65, 10, $fmt($t['nuevos_documentos']), 0, 0, 'C', true);
        $pdf->Cell(65, 10, $fmt($t['act_actualizaciones']), 0, 0, 'C', true);
        $pdf->Cell(65, 10, $fmt($t['total_operaciones']), 0, 0, 'C', true);
        $pdf->Cell(65, 10, $fmt($t['total_paginas']), 0, 1, 'C', true);
        $pdf->Ln(5);

        // A. Nuevos
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->facturacionPdfTexto('A. Documentos digitalizados (nuevos) por tipo de documento'), 0, 1, 'L');
        $filas = [];
        foreach ($datos['nuevos'] as $f) {
            $filas[] = [$f['tipo'], $fmt($f['documentos']), $fmt($f['paginas']), $fmt($f['documentos_activos_hoy']), $fmt($f['paginas_activas_hoy'])];
        }
        $this->facturacionPdfTabla(
            $pdf,
            ['Tipo de documento', 'Documentos', 'Páginas', 'Activos hoy (docs)', 'Activos hoy (págs)'],
            [95, 35, 35, 45, 45],
            ['L', 'R', 'R', 'R', 'R'],
            $filas,
            ['TOTAL', $fmt($t['nuevos_documentos']), $fmt($t['nuevos_paginas']), $fmt($t['nuevos_activos_documentos']), $fmt($t['nuevos_activos_paginas'])]
        );
        if (!$filas) {
            $pdf->Cell(0, 6, $this->facturacionPdfTexto('No hay documentos nuevos en el período.'), 0, 1, 'L');
        }
        $pdf->Ln(5);

        // B. Actualizados
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->facturacionPdfTexto('B. Documentos actualizados por tipo de documento'), 0, 1, 'L');
        $filas = [];
        foreach ($datos['actualizados'] as $f) {
            $filas[] = [$f['tipo'], $fmt($f['archivos']), $fmt($f['actualizaciones']), $fmt($f['paginas'])];
        }
        $this->facturacionPdfTabla(
            $pdf,
            ['Tipo de documento', 'Archivos actualizados', 'Actualizaciones', 'Páginas'],
            [95, 50, 45, 40],
            ['L', 'R', 'R', 'R'],
            $filas,
            ['TOTAL', $fmt($t['act_archivos']), $fmt($t['act_actualizaciones']), $fmt($t['act_paginas'])]
        );
        if (!$filas) {
            $pdf->Cell(0, 6, $this->facturacionPdfTexto('No hay actualizaciones en el período.'), 0, 1, 'L');
        }
        $pdf->Ln(5);

        // Total general
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->facturacionPdfTexto('Total de páginas procesadas en el período: ' . $fmt($t['total_paginas'])
            . ' (nuevos: ' . $fmt($t['nuevos_paginas']) . ' + actualizaciones: ' . $fmt($t['act_paginas']) . ')'), 0, 1, 'L');
        $pdf->Ln(2);

        // Control
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->facturacionPdfTexto('Control'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        $control = [
            'Documentos nuevos del período que hoy no están activos (anulados o eliminados): ' . $fmt($t['nuevos_no_activos']),
            'Hechos con cantidad de páginas sin registrar (menor a 1): ' . $fmt($t['sin_conteo']),
            'Hechos reconstruidos de la carga inicial dentro del período: ' . $fmt($t['reconstruidos']),
            $datos['inicio_registro']
            ? 'El registro en el momento en que ocurren los hechos rige desde: ' . date('d/m/Y H:i', strtotime($datos['inicio_registro']))
            : 'Todavía no hay hechos registrados en el momento en que ocurren: todos los datos son reconstruidos.',
        ];
        foreach ($control as $linea) {
            $pdf->MultiCell(0, 5, $this->facturacionPdfTexto('- ' . $linea), 0, 'L');
        }
        $pdf->Ln(3);

        // Criterios
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, $this->facturacionPdfTexto('Criterios de facturación'), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 9);
        foreach ($datos['criterios'] as $linea) {
            $pdf->MultiCell(0, 5, $this->facturacionPdfTexto('- ' . $linea), 0, 'L');
        }

        $pdf->Output("Facturacion_Expedientes.pdf", "I");
        exit();
    }

    public function pdf_email()
    {
        $mailController = new Configuracion();
        $expediente = $this->model->selectExpediente();

        // Datos del correo (pueden venir de la vista o de la base de datos)
        $destinatario = 'aldo.silva@printec.com.py';
        $nombreDestinatario = 'Aldo Silva';
        $asunto = 'Informe de Expedientes';
        $mensaje = 'Adjunto el informe de expedientes generado.';

        // Generar PDF con plantilla
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras
        $pdf->Cell(45, 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell(65, 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        // Configurar anchos para el bucle
        $pdf->SetWidths(array(45, 65, 40, 40, 50, 35, 35, 15, 15));
        $pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($expediente as $row) {
            $pdf->Row(array(
                utf8_decode($row['nombre_tipoDoc']),
                utf8_decode($row['indice_01']),
                utf8_decode($row['indice_02']),
                utf8_decode($row['indice_03']),
                utf8_decode($row['indice_04']),
                utf8_decode($row['indice_05']),
                utf8_decode($row['fecha_indexado']),
                utf8_decode($row['paginas']),
                utf8_decode($row['version'])
            ));
        }

        // Guardar el PDF temporalmente
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . "/Expedientes_" . date('Y_m_d_His') . ".pdf";
        $pdf->Output('F', $filePath);

        // Validar si el archivo se generó correctamente
        if (file_exists($filePath)) {
            if ($mailController->model->sendEmailWithAttachment($filePath, [$destinatario => $nombreDestinatario], $asunto, $mensaje)) {
                echo 'Correo enviado correctamente.';
            } else {
                echo 'Error al enviar correo: ';
            }
            unlink($filePath);
        } else {
            echo 'Error al generar el PDF.';
        }
    }

    public function pdf_emails()
    {
        $desde = $_POST['desde'];
        $dias = $_POST['dias'];
        $mailController = new Configuracion();
        $expediente = $this->model->reporteExpedientesFecha($desde, $dias);

        // Obtener los destinatarios y nombres del formulario
        $emails = isset($_POST['emails']) ? $_POST['emails'] : '';
        $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';

        // Separar los valores por coma
        $listaEmails = array_map('trim', explode(',', $emails));
        $listaNombres = array_map('trim', explode(',', $nombres));
        $destinatarios = [];
        foreach ($listaEmails as $index => $email) {
            $nombre = isset($listaNombres[$index]) && !empty($listaNombres[$index]) ? $listaNombres[$index] : $email;
            $destinatarios[$email] = $nombre;
        }

        if (empty($destinatarios)) {
            echo 'Error: No se ingresaron correos válidos.';
            return;
        }
        $asunto = 'Informe de Archivos';
        $mensaje = 'Se adjunta el informe de archivos generado';

        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras
        $pdf->Cell(45, 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell(65, 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetWidths(array(45, 65, 40, 40, 50, 35, 35, 15, 15));
        $pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($expediente as $row) {
            $pdf->Row(array(
                utf8_decode($row['nombre_tipoDoc']),
                utf8_decode($row['indice_01']),
                utf8_decode($row['indice_02']),
                utf8_decode($row['indice_03']),
                utf8_decode($row['indice_04']),
                utf8_decode($row['indice_05']),
                utf8_decode($row['fecha_indexado']),
                utf8_decode($row['paginas']),
                utf8_decode($row['version'])
            ));
        }

        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . "/Expedientes_" . date('Y_m_d_His') . ".pdf";
        $pdf->Output('F', $filePath);

        if (file_exists($filePath)) {
            $mailController->model->sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje);
            unlink($filePath);
        } else {
            echo 'Error al generar el PDF.';
        }
    }


    public function pdf()
    {
        $expediente = $this->model->selectExpediente();

        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte General de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras
        $pdf->Cell(45, 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell(65, 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetWidths(array(45, 65, 40, 40, 50, 35, 35, 15, 15));
        $pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($expediente as $row) {
            $pdf->Row(array(
                utf8_decode($row['nombre_tipoDoc']),
                utf8_decode($row['indice_01']),
                utf8_decode($row['indice_02']),
                utf8_decode($row['indice_03']),
                utf8_decode($row['indice_04']),
                utf8_decode($row['indice_05']),
                utf8_decode($row['fecha_indexado']),
                utf8_decode($row['paginas']),
                utf8_decode($row['version'])
            ));
        }

        $pdf->Output("Expedientes_" . date('Y_m_d_H_i_s') . ".pdf", "I");
    }

    public function pdf_filtroFecha()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $expediente = $this->model->reporteExpedientesFecha($desde, $hasta);

        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Expedientes por Fecha de Carga', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras
        $pdf->Cell(45, 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell(65, 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetWidths(array(45, 65, 40, 40, 50, 35, 35, 15, 15));
        $pdf->SetAligns(array('L', 'L', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($expediente as $row) {
            $pdf->Row(array(
                utf8_decode($row['nombre_tipoDoc']),
                utf8_decode($row['indice_01']),
                utf8_decode($row['indice_02']),
                utf8_decode($row['indice_03']),
                utf8_decode($row['indice_04']),
                utf8_decode($row['indice_05']),
                utf8_decode($row['fecha_indexado']),
                utf8_decode($row['paginas']),
                utf8_decode($row['version'])
            ));
        }

        $pdf->Output("Expedientes_" . date('Y_m_d_H_i_s') . ".pdf", "I");
    }

    public function pdf_filtro()
    {
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $expediente = $this->model->reporteExpedientes($desde, $hasta);

        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Expedientes por Rango de Indice', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Cabeceras
        $pdf->Cell(15, 7, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell(168, 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell(15, 7, utf8_decode('Páginas'), 1, 1, 'C', 1);

        $pdf->SetWidths(array(15, 35, 35, 35, 35, 168, 15));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'L', 'C'));
        $pdf->SetFont('Arial', '', 8);

        foreach ($expediente as $row) {
            $pdf->Row(array(
                $row['id_expediente'],
                utf8_decode($row['indice_01']),
                utf8_decode($row['indice_02']),
                utf8_decode($row['indice_03']),
                utf8_decode($row['indice_04']),
                utf8_decode($row['indice_05']),
                utf8_decode($row['paginas'])
            ));
        }

        $pdf->Output("Expedientes.pdf", "I");
    }

    public function excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $expediente = $this->model->selectExpediente();

        $excel = new ReportTemplateExcel('REPORTE GENERAL DE EXPEDIENTES', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        $headerRow = 4;
        $headers = ['TIPO DOCUMENTO', 'INDICE 01', 'INDICE 02', 'INDICE 03', 'INDICE 04', 'INDICE 05', 'PAGINAS', 'UBICACION', 'FECHA CARGA', 'VERSION'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:J$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["nombre_tipoDoc"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('C' . $dataRow, $value["indice_02"]);
            $sheet->setCellValue('D' . $dataRow, $value['indice_03']);
            $sheet->setCellValue('E' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('F' . $dataRow, $value["indice_05"]);
            $sheet->setCellValue('G' . $dataRow, $value["paginas"]);
            $sheet->setCellValue('H' . $dataRow, $value["ubicacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["fecha_indexado"]);
            $sheet->setCellValue('J' . $dataRow, $value["version"]);
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas: Las largas con número fijo (Wrap), las cortas con 'auto'
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 45, // Indice 01
            'C' => 30, // Indice 02
            'D' => 30, // Indice 03
            'E' => 40, // Indice 04
            'F' => 30, // Indice 05
            'G' => 'auto',
            'H' => 'auto',
            'I' => 'auto',
            'J' => 'auto'
        ]);

        $nombreArchivo = 'Expedientes_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function facturacionExcel()
    {
        $this->asegurarAccesoFacturacionExpedientes();
        $filtros = $this->facturacionFiltros();
        $datos = $this->facturacionDatos($filtros);

        if (!$datos['instalada']) {
            if (function_exists('setAlert')) {
                setAlert('warning', 'El registro de facturación de expedientes todavía no está instalado.');
            }
            header('Location: ' . base_url() . 'expedientes/facturacion?' . http_build_query($filtros));
            exit();
        }

        @ini_set('memory_limit', '512M');
        @set_time_limit(120);
        $detalle = $this->model->selectFacturacionDetalle($filtros['desde'], $filtros['hasta']);
        $t = $datos['totales'];

        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $TEXTO = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
        $col = function (int $n) {
            return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($n);
        };
        $fecha = function ($iso) {
            $ts = strtotime((string) $iso);
            return $ts ? date('d/m/Y', $ts) : '';
        };
        $periodo = $fecha($filtros['desde']) . ' a ' . $fecha($filtros['hasta']);
        $generado = date('d/m/Y H:i') . ' por ' . ($_SESSION['nombre'] ?? '');

        $estiloEncabezado = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];
        $estiloContenido = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $estiloTotal = [
            'font' => ['bold' => true, 'size' => 9],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6E6E6']],
        ];

        // Todo texto que viene de los datos se escribe como TEXTO explicito: un valor que empiece con "=" no se interpreta como formula.
        $poner = function ($hoja, string $celda, $valor, bool $esTexto) use ($TEXTO) {
            if ($esTexto) {
                $hoja->setCellValueExplicit($celda, (string) $valor, $TEXTO);
            } else {
                $hoja->setCellValue($celda, $valor);
            }
        };
        $titulo = function ($hoja, string $texto) use ($periodo) {
            $hoja->setCellValue('A1', $texto);
            $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(12);
            $hoja->setCellValue('A2', 'Período: ' . $periodo);
        };
        $tabla = function ($hoja, int $filaEnc, array $encabezados, array $filas, ?array $total, array $columnasTexto) use ($col, $poner, $estiloEncabezado, $estiloContenido, $estiloTotal) {
            $ultima = $col(count($encabezados));
            foreach ($encabezados as $i => $h) {
                $hoja->setCellValue($col($i + 1) . $filaEnc, $h);
            }
            $hoja->getStyle("A{$filaEnc}:{$ultima}{$filaEnc}")->applyFromArray($estiloEncabezado);
            $fila = $filaEnc + 1;
            foreach ($filas as $f) {
                foreach (array_values($f) as $i => $v) {
                    $poner($hoja, $col($i + 1) . $fila, $v, in_array($i, $columnasTexto, true));
                }
                $hoja->getStyle("A{$fila}:{$ultima}{$fila}")->applyFromArray($estiloContenido);
                $fila++;
            }
            if ($total !== null) {
                foreach (array_values($total) as $i => $v) {
                    $poner($hoja, $col($i + 1) . $fila, $v, in_array($i, $columnasTexto, true));
                }
                $hoja->getStyle("A{$fila}:{$ultima}{$fila}")->applyFromArray($estiloTotal);
                $fila++;
            }
            return $fila;
        };

        $excel = new ReportTemplateExcel('FACTURACION DE EXPEDIENTES', 'SCANTEC');

        // ---------------- Hoja 1: Resumen ----------------
        $hoja = $excel->getSheet();
        $hoja->setTitle('Resumen');
        $hoja->setCellValue('A4', 'Período');
        $hoja->setCellValue('B4', $periodo);
        $hoja->setCellValue('A5', 'Generado');
        $hoja->setCellValue('B5', $generado);
        $hoja->setCellValue('A6', 'Registro en el momento en que ocurren los hechos, desde');
        $hoja->setCellValue('B6', $datos['inicio_registro'] ? date('d/m/Y H:i', strtotime($datos['inicio_registro'])) : 'Todavía no hay: todos los datos son reconstruidos');
        $hoja->getStyle('A4:A6')->getFont()->setBold(true);
        $fila = $tabla($hoja, 8, ['CONCEPTO', 'DOCUMENTOS / OPERACIONES', 'PÁGINAS'], [
            ['Documentos nuevos', $t['nuevos_documentos'], $t['nuevos_paginas']],
            ['Actualizaciones', $t['act_actualizaciones'], $t['act_paginas']],
            ['   (archivos distintos actualizados)', $t['act_archivos'], ''],
        ], ['TOTAL (nuevos + actualizaciones)', $t['total_operaciones'], $t['total_paginas']], [0]);
        $fila = $tabla($hoja, $fila + 1, ['INFORMATIVO', 'DOCUMENTOS', 'PÁGINAS'], [
            ['De los documentos nuevos, siguen activos hoy', $t['nuevos_activos_documentos'], $t['nuevos_activos_paginas']],
        ], null, [0]);
        $fila = $tabla($hoja, $fila + 1, ['CONTROL', 'CANTIDAD', ''], [
            ['Documentos nuevos del período que hoy no están activos (anulados o eliminados)', $t['nuevos_no_activos'], ''],
            ['Hechos con cantidad de páginas sin registrar (menor a 1)', $t['sin_conteo'], ''],
            ['Hechos reconstruidos de la carga inicial dentro del período', $t['reconstruidos'], ''],
        ], null, [0]);
        $excel->setColumnWidths(['A' => 70, 'B' => 28, 'C' => 16]);

        // ---------------- Hoja 2: Nuevos por tipo ----------------
        $h2 = $excel->createSheet();
        $h2->setTitle('Nuevos por tipo');
        $titulo($h2, 'DOCUMENTOS NUEVOS POR TIPO DE DOCUMENTO');
        $filas = [];
        foreach ($datos['nuevos'] as $f) {
            $filas[] = [$f['tipo'], intval($f['documentos']), intval($f['paginas']), intval($f['documentos_activos_hoy']), intval($f['paginas_activas_hoy'])];
        }
        $tabla(
            $h2,
            4,
            ['TIPO DE DOCUMENTO', 'DOCUMENTOS', 'PÁGINAS', 'ACTIVOS HOY (DOCS)', 'ACTIVOS HOY (PÁGS)'],
            $filas,
            ['TOTAL', $t['nuevos_documentos'], $t['nuevos_paginas'], $t['nuevos_activos_documentos'], $t['nuevos_activos_paginas']],
            [0]
        );
        foreach (['A' => 45, 'B' => 14, 'C' => 14, 'D' => 20, 'E' => 20] as $c => $w) {
            $h2->getColumnDimension($c)->setWidth($w);
        }

        // ---------------- Hoja 3: Actualizados por tipo ----------------
        $h3 = $excel->createSheet();
        $h3->setTitle('Actualizados por tipo');
        $titulo($h3, 'DOCUMENTOS ACTUALIZADOS POR TIPO DE DOCUMENTO');
        $filas = [];
        foreach ($datos['actualizados'] as $f) {
            $filas[] = [$f['tipo'], intval($f['archivos']), intval($f['actualizaciones']), intval($f['paginas'])];
        }
        $tabla(
            $h3,
            4,
            ['TIPO DE DOCUMENTO', 'ARCHIVOS ACTUALIZADOS', 'ACTUALIZACIONES', 'PÁGINAS'],
            $filas,
            ['TOTAL', $t['act_archivos'], $t['act_actualizaciones'], $t['act_paginas']],
            [0]
        );
        foreach (['A' => 45, 'B' => 24, 'C' => 18, 'D' => 14] as $c => $w) {
            $h3->getColumnDimension($c)->setWidth($w);
        }

        // ---------------- Hoja 4: Detalle (completo, sin limite) ----------------
        $h4 = $excel->createSheet();
        $h4->setTitle('Detalle');
        $titulo($h4, 'DETALLE DE LOS HECHOS DEL PERÍODO');
        $encabezados = ['FECHA', 'HECHO', 'TIPO DE DOCUMENTO', 'ID DOCUMENTO', 'ÍNDICE 01', 'PÁGINAS', 'PÁGINAS PREVIAS', 'ESTADO HOY', 'CUENTA', 'ORIGEN DEL DATO'];
        foreach ($encabezados as $i => $h) {
            $h4->setCellValue($col($i + 1) . '4', $h);
        }
        $h4->getStyle('A4:J4')->applyFromArray($estiloEncabezado);
        $fila = 5;
        foreach ($detalle as $f) {
            $ts = strtotime((string) $f['fecha_negocio']);
            if ($ts) {
                $h4->setCellValue('A' . $fila, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($ts));
            }
            $poner($h4, 'B' . $fila, $f['tipo_evento'] === 'NUEVO' ? 'Nuevo' : 'Actualización', true);
            $poner($h4, 'C' . $fila, $f['tipo'], true);
            $h4->setCellValue('D' . $fila, intval($f['id_expediente']));
            $poner($h4, 'E' . $fila, $f['indice_01'] ?? '', true);
            $h4->setCellValue('F' . $fila, intval($f['paginas']));
            if ($f['paginas_previas'] !== null) {
                $h4->setCellValue('G' . $fila, intval($f['paginas_previas']));
            }
            $poner($h4, 'H' . $fila, $f['estado_hoy'], true);
            $poner($h4, 'I' . $fila, $f['usuario_db'] ?? '', true);
            $poner($h4, 'J' . $fila, intval($f['reconstruido']) === 1 ? 'Reconstruido' : 'Registrado', true);
            $fila++;
        }
        $ultimaFila = max(5, $fila - 1);
        $h4->getStyle("A5:J{$ultimaFila}")->applyFromArray($estiloContenido);
        $h4->getStyle("A5:A{$ultimaFila}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $h4->setAutoFilter("A4:J{$ultimaFila}");
        $h4->freezePane('A5');
        foreach (['A' => 12, 'B' => 14, 'C' => 38, 'D' => 14, 'E' => 30, 'F' => 10, 'G' => 16, 'H' => 14, 'I' => 16, 'J' => 18] as $c => $w) {
            $h4->getColumnDimension($c)->setWidth($w);
        }

        // ---------------- Hoja 5: Criterios ----------------
        $h5 = $excel->createSheet();
        $h5->setTitle('Criterios');
        $h5->setCellValue('A1', 'CRITERIOS DE FACTURACIÓN');
        $h5->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $fila = 3;
        foreach ($datos['criterios'] as $criterio) {
            $poner($h5, 'A' . $fila, $criterio, true);
            $fila++;
        }
        $h5->getColumnDimension('A')->setWidth(150);

        $excel->output('Facturacion_Expedientes_' . date('Y_m_d_His'));
    }

    public function excel_filtroFecha()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $expediente = $this->model->reporteExpedientesFecha($desde, $hasta);

        $excel = new ReportTemplateExcel('EXPEDIENTES POR FECHA', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        $headerRow = 4;
        $headers = ['TIPO DOCUMENTO', 'INDICE 01', 'INDICE 02', 'INDICE 03', 'INDICE 04', 'INDICE 05', 'PAGINAS', 'UBICACION', 'FECHA CARGA', 'VERSION'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:J$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["nombre_tipoDoc"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('C' . $dataRow, $value["indice_02"]);
            $sheet->setCellValue('D' . $dataRow, $value['indice_03']);
            $sheet->setCellValue('E' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('F' . $dataRow, $value["indice_05"]);
            $sheet->setCellValue('G' . $dataRow, $value["paginas"]);
            $sheet->setCellValue('H' . $dataRow, $value["ubicacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["fecha_indexado"]);
            $sheet->setCellValue('J' . $dataRow, $value["version"]);
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 45,
            'C' => 30,
            'D' => 30,
            'E' => 40,
            'F' => 30,
            'G' => 'auto',
            'H' => 'auto',
            'I' => 'auto',
            'J' => 'auto'
        ]);

        $nombreArchivo = 'Expedientes_Por_Fecha_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function excel_filtroDuplic()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $expediente = $this->model->reporteExpedientesDuplic();

        $excel = new ReportTemplateExcel('EXPEDIENTES DUPLICADOS', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        $headerRow = 4;
        $headers = ['INDICE 01', 'INDICE 04', 'CANTIDAD'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:C$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('C' . $dataRow, $value["cantidad"]);
            $sheet->getStyle('A' . $dataRow . ':C' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 45, // Indice 01
            'B' => 40, // Indice 04
            'C' => 'auto' // Cantidad
        ]);

        $nombreArchivo = 'Expedientes_Duplicados_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }
}
