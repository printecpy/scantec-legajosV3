<?php
require_once("Libraries/Core/ApiController.php");
class ExpedientesController extends ApiController
{
    protected $ExpedientesModel; // Referencia al modelo de expedientes

    public function __construct()
    {
        parent::__construct(); // Llama al constructor de ApiController (que carga UsuariosModel)

        // Carga del ExpedientesModel
        $modelName = 'ExpedientesModel';
        $modelFile = "Models/{$modelName}.php";

        if (file_exists($modelFile)) {
            require_once($modelFile);
            $this->ExpedientesModel = new $modelName();
        } else {
            $this->sendErrorResponse(500, 'Error interno: El modelo ExpedientesModel no fue encontrado.');
        }
    }

    // ==========================================================
    // 1. LISTAR (GET) - api/v1/expedientes/listar
    // ==========================================================
    public function listar()
    {
        try {
            // ROL REQUERIDO: [1] root, [2] Admin, [3] Usuario
            $userData = $this->checkApiAccess();

            $id_grupo_jwt = $userData['id_grupo'];
            $usuarioRoles = $userData['roles'];

            // 1. Obtener parámetros de filtrado (simulando tu $_GET)
            $indice_01 = trim((string) ($_GET['indice_01'] ?? ''));
            $nombre_tipoDoc = trim((string) ($_GET['nombre_tipoDoc'] ?? ''));
            $termino = trim((string) ($_GET['termino'] ?? ''));

            // 2. Definir el ID de grupo a usar para el filtro
            if (in_array(1, $usuarioRoles)) {
                $id_grupo_a_filtrar = 'ALL'; // Admin sin filtro
            } else if ($id_grupo_jwt) {
                $id_grupo_a_filtrar = $id_grupo_jwt; // Filtro por grupo del token
            } else {
                // No es admin y no tiene grupo asociado
                $this->sendErrorResponse(403, 'Acceso denegado. Usuario no asociado a un grupo válido.');
                return; // Añadir return después de sendErrorResponse/sendSuccessResponse es buena práctica
            }

            if ($nombre_tipoDoc !== '' && !$this->ExpedientesModel->existeTipoDocParaGrupo($id_grupo_a_filtrar, $nombre_tipoDoc)) {
                $this->sendErrorResponse(404, 'El tipo de documento especificado no existe o no está disponible para tu grupo.');
                return;
            }

            // 3. Llamar al modelo (Usando $this->ExpedientesModel)
            $registros = $this->ExpedientesModel->selectRegistrosApi($id_grupo_a_filtrar, $indice_01, $nombre_tipoDoc, $termino);

            if (empty($registros)) {
                $registros = [];
            }
            // 4. Devolver los datos como JSON (Ahora usando sendSuccessResponse)
            $this->sendSuccessResponse(200, $registros, 'Listado de expedientes obtenido correctamente.');
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al listar expedientes: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // 2. EDITAR (POST/PUT) - api/v1/expedientes/editar/[ID]
    // ==========================================================
    public function editar(int $idExpediente)
    {
        try {
            // ROL REQUERIDO: [1] Admin, [2] Ejecutor (Escritura)
            $userData = $this->checkApiAccess([1]); // temporal, hasta remover el endpoint

            $input = json_decode(file_get_contents('php://input'), true);

            // --- CAPA DE VALIDACIÓN ---
            // Se crea un helper para centralizar la validación de datos de entrada.
            require_once 'Helpers/RequestValidator.php';
            $validator = new RequestValidator($input ?? []);
            $validator->validate([
                'indice_01' => 'required', // Ejemplo: Aseguramos que campos críticos vengan
                'indice_02' => 'required',
            ]);

            if ($validator->fails()) {
                $this->sendErrorResponse(400, "Datos de actualización inválidos.", ['errors' => $validator->getErrors()]);
                return;
            }

            // Lógica de validación de propiedad (Autorización Fina)
            $registro = $this->ExpedientesModel->getExpedienteById($idExpediente);
            $usuario_id_grupo = $userData['id_grupo'];

            if (!$registro) {
                $this->sendErrorResponse(404, "Expediente no encontrado.");
                return;
            }

            // Verificar si el usuario NO es Admin Y el documento NO pertenece a su grupo
            if (!in_array(1, $userData['roles']) && intval($registro['id_grupo'] ?? -1) !== intval($usuario_id_grupo)) {
                $this->sendErrorResponse(403, "No tienes permiso para editar este expediente.");
            }

            // Actualizar (asume que esta función existe en ExpedientesModel)
            $resultado = $this->ExpedientesModel->updateExpediente($idExpediente, $input);

            if ($resultado) {
                // Usar sendSuccessResponse para registrar la acción
                $this->sendSuccessResponse(200, ["id_expediente" => $idExpediente], "Expediente $idExpediente actualizado.");
            } else {
                $this->sendErrorResponse(500, "Error al actualizar el expediente.");
            }
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al editar: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // 3. ELIMINAR (DELETE - Lógico) - api/v1/expedientes/eliminar/[ID]
    // ==========================================================
    public function eliminar(int $idExpediente)
    {
        try {
            // ROL REQUERIDO: [1] Admin
            $userData = $this->checkApiAccess([1]); // temporal, hasta remover el endpoint

            // 1. Verificar existencia
            $registro = $this->ExpedientesModel->getExpedienteById($idExpediente);
            if (!$registro) {
                $this->sendErrorResponse(404, "Expediente no encontrado.");
                return;
            }

            // 2. Lógica de Eliminación Lógica
            $resultado = $this->ExpedientesModel->deleteExpedienteLogico($idExpediente);

            if ($resultado) {
                // Usar sendSuccessResponse para registrar la acción
                $this->sendSuccessResponse(200, ["id_expediente" => $idExpediente], "Expediente $idExpediente marcado como Inactivo.");
            } else {
                $this->sendErrorResponse(500, "Error al realizar la eliminación lógica.");
            }
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al eliminar: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // 2. VER DOCUMENTO (GET) - api/v1/expedientes/verDocumento?ruta=...&id_expediente=...
    // ==========================================================
    public function verDocumento()
    {
        try {
            $userData = $this->checkApiAccess();

            // 1. Ahora solo se requiere el id_expediente
            if (!isset($_GET['id_expediente']) || empty($_GET['id_expediente'])) {
                $this->sendErrorResponse(400, 'Error: Parámetro "id_expediente" faltante.');
            }
            $id_expediente = intval($_GET['id_expediente']);
            $id_user = $userData['id'];

            // 2. Buscar el expediente en la BD (esto trae la ruta REAL, no la del input)
            $registro = $this->ExpedientesModel->getExpedienteApiPorId($id_expediente);
            if (!$registro) {
                $this->sendErrorResponse(404, 'Expediente no encontrado.');
            }

            // 3. AUTORIZACIÓN POR GRUPO (IDOR)
            if (!in_array(1, $userData['roles']) && intval($registro['id_grupo'] ?? -1) !== intval($userData['id_grupo'])) {
                $this->sendErrorResponse(403, 'No tienes permiso para acceder a este documento.');
            }

            // 4. La ruta viene de la BASE DE DATOS, no del cliente.
            //    El sistema externo nunca ve ni controla la ruta física.
            $archivo_relativo = trim((string) ($registro['ruta_original'] ?? ''));
            if ($archivo_relativo === '') {
                $this->sendErrorResponse(404, 'El expediente no tiene un archivo asociado.');
            }

            // 5. PATH TRAVERSAL (defensa en profundidad): aunque la ruta ya viene de la BD,
            //    confirmamos que el archivo real quede dentro de RUTA_BASE.
            $rutaBaseReal = realpath(rtrim(RUTA_BASE, '/\\'));
            $ruta_archivo_real = realpath(rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $archivo_relativo));

            if (
                $rutaBaseReal === false || $ruta_archivo_real === false
                || strpos($ruta_archivo_real, $rutaBaseReal) !== 0
                || !is_file($ruta_archivo_real)
            ) {
                $this->sendErrorResponse(404, 'El documento no existe en el servidor.');
            }

            // 6. Logging
            $this->logApiTransaction(
                ['id' => $id_user],
                $_SERVER['REQUEST_URI'] ?? 'N/A',
                $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                200,
                "Visualización exitosa del documento ID {$id_expediente}.",
                ['id_expediente' => $id_expediente, 'filename' => basename($ruta_archivo_real)]
            );

            // 7. Enviar el archivo en streaming
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $ruta_archivo_real);
            finfo_close($finfo);

            if (ob_get_level() > 0) {
                ob_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: inline; filename="' . basename($ruta_archivo_real) . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($ruta_archivo_real));
            readfile($ruta_archivo_real);
            exit;
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al intentar visualizar el documento: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/Expedientes/subirDocumentoApi",
     * summary="Sube, procesa (OCR, orientación) y registra un nuevo documento de expediente.",
     * tags={"Expedientes"},
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * description="Metadatos del expediente y el archivo PDF.",
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * @OA\Property(property="file_pdf", type="string", format="binary", description="El archivo PDF a subir (requerido)."),
     * @OA\Property(property="id_tipoDoc", type="string", description="Código del tipo de documento."),
     * @OA\Property(property="indice_04", type="string", description="Índice 04 (Nombre del archivo final, e.g., 'CI_1234567').")
     * )
     * )
     * ),
     * @OA\Response(response=201, description="Documento procesado y registrado exitosamente."),
     * @OA\Response(response=400, description="Solicitud incorrecta."),
     * @OA\Response(response=403, description="Acceso denegado. Rol insuficiente."),
     * @OA\Response(response=500, description="Error interno del servidor.")
     * )
     */
    public function subirDocumentoApi()
    {
        set_time_limit(500);
        $userData = $this->checkApiAccess();

        if (!defined('MAGICK_EXECUTABLE_PATH') || !defined('TESSERACT_EXECUTABLE_PATH') || !defined('RUTA_BASE')) {
            $this->sendErrorResponse(500, "Error de configuración: Rutas de ejecutables o RUTA_BASE no definidas.");
            return;
        }

        date_default_timezone_set('America/Asuncion');
        $id_proceso = date("Ymd-His");

        // --- Datos e índices ---
        $id_tipoDoc = trim((string) ($_POST['id_tipoDoc'] ?? ''));
        $indice_01 = trim((string) ($_POST['indice_01'] ?? ''));
        $indice_02 = trim((string) ($_POST['indice_02'] ?? ''));
        $indice_03 = trim((string) ($_POST['indice_03'] ?? ''));
        $indice_04 = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['indice_04'] ?? '');
        $indice_05 = trim((string) ($_POST['indice_05'] ?? ''));
        $indice_06 = trim((string) ($_POST['indice_06'] ?? ''));

        if (empty($indice_04)) {
            $this->sendErrorResponse(400, "Falta el 'indice_04' (nombre de archivo).");
            return;
        }

        // --- VALIDACIÓN DE GRUPO (API): el tipo de doc debe pertenecer al grupo del usuario ---
        $id_grupo_a_filtrar = in_array(1, $userData['roles']) ? 'ALL' : (string) intval($userData['id_grupo']);
        if (!$this->ExpedientesModel->tipoDocPerteneceAGrupo($id_grupo_a_filtrar, $id_tipoDoc)) {
            $this->sendErrorResponse(403, "No tienes permiso para subir documentos de este tipo.");
            return;
        }

        // --- VALIDACIÓN DEL ARCHIVO (mismo criterio que la web) ---
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            $error_code = $_FILES['file_pdf']['error'] ?? 'N/A';
            $this->sendErrorResponse(400, "Error al subir el archivo. Código UPLOAD: " . $error_code);
            return;
        }
        if (!scantecValidarUpload($_FILES['file_pdf'], ['pdf'], ['application/pdf'], 50 * 1024 * 1024)) {
            $this->sendErrorResponse(400, "El archivo debe ser un PDF válido y no superar 50 MB.");
            return;
        }

        // --- RUTA con carpeta por tipo (mismo patrón que la web) ---
        $tipoDocData = $this->ExpedientesModel->getTipoDocById($id_tipoDoc);
        $nombre_tipo = (!empty($tipoDocData) && isset($tipoDocData[0]['nombre_tipoDoc']))
            ? $tipoDocData[0]['nombre_tipoDoc'] : 'Otros';
        $nombre_carpeta_tipo = preg_replace('/[^a-zA-Z0-9_-]/', '', $nombre_tipo);

        $nombreSeguro = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $indice_04);
        $nombre_final = ($nombreSeguro !== '' ? $nombreSeguro : uniqid('expediente_', true)) . '.pdf';
        $ruta_relativa = 'Expedientes/' . $nombre_carpeta_tipo . '/' . $nombre_final;
        $ubicacion_fisica = RUTA_BASE . $ruta_relativa;

        $dir_expedientes = dirname($ubicacion_fisica);
        if (!is_dir($dir_expedientes)) {
            if (!@mkdir($dir_expedientes, 0777, true)) {
                $this->sendErrorResponse(500, "Error al crear el directorio de expedientes.");
                return;
            }
        }
        if (!move_uploaded_file($_FILES['file_pdf']['tmp_name'], $ubicacion_fisica)) {
            $this->sendErrorResponse(500, "Error al mover el archivo al repositorio.");
            return;
        }

        // --- CONTEO DE PÁGINAS liviano (magick identify, igual que la web) ---
        $num_paginas = 1;
        $magick_escaped = escapeshellarg(MAGICK_EXECUTABLE_PATH);
        $pdf_escaped = escapeshellarg($ubicacion_fisica);
        exec("$magick_escaped identify $pdf_escaped 2>&1", $salida_ident);
        if (!empty($salida_ident)) {
            $paginas_reales = 0;
            foreach ($salida_ident as $linea) {
                if (stripos($linea, 'PDF') !== false) {
                    $paginas_reales++;
                }
            }
            if ($paginas_reales > 0) {
                $num_paginas = $paginas_reales;
            }
        }

        // --- REGISTRO EN BD ---
        $registrar = $this->ExpedientesModel->registrarExpediente(
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
            $this->sendSuccessResponse(201, [
                'id_proceso' => $id_proceso,
                'indice_01' => $indice_01,
                'indice_04' => $indice_04,
                'ruta_original' => $ruta_relativa,
                'paginas' => $num_paginas
            ], "Archivo subido y registrado correctamente.");
        } else {
            // Si falló el registro, borramos el archivo huérfano para no dejar basura.
            if (is_file($ubicacion_fisica)) {
                @unlink($ubicacion_fisica);
            }
            $this->sendErrorResponse(500, "Error interno al registrar el expediente en la base de datos.");
        }
    }
}
