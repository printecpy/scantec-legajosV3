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
            $userData = $this->checkApiAccess([1, 2, 3]);

            $id_grupo_jwt = $userData['id_grupo'];
            $usuarioRoles = $userData['roles'];

            // 1. Obtener parámetros de filtrado (simulando tu $_GET)
            $indice_01 = htmlspecialchars($_GET['indice_01'] ?? '');
            $nombre_tipoDoc = htmlspecialchars($_GET['nombre_tipoDoc'] ?? '');
            $termino = htmlspecialchars($_GET['termino'] ?? '');

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

            // 3. Llamar al modelo (Usando $this->ExpedientesModel)
            $registros = $this->ExpedientesModel->selectRegistrosApi($id_grupo_a_filtrar, $indice_01, $nombre_tipoDoc, $termino);
            if (empty($registros)) {
                $this->sendErrorResponse(404, 'No se encontraron registros que coincidan con los filtros.');
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
            $userData = $this->checkApiAccess([1, 2]);

            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || !isset($data['indice_02'])) {
                $this->sendErrorResponse(400, "Datos de actualización incompletos o JSON inválido.");
            }

            // Lógica de validación de propiedad (Autorización Fina)
            $registro = $this->ExpedientesModel->getExpedienteById($idExpediente);
            $usuario_id_grupo = $userData['id_grupo'];

            if (!$registro) {
                $this->sendErrorResponse(404, "Expediente no encontrado.");
            }

            // Verificar si el usuario NO es Admin Y el documento NO pertenece a su grupo
            if (!in_array(1, $userData['roles']) && $registro['id_grupo'] != $usuario_id_grupo) {
                $this->sendErrorResponse(403, "No tienes permiso para editar este expediente.");
            }

            // Actualizar (asume que esta función existe en ExpedientesModel)
            $resultado = $this->ExpedientesModel->updateExpediente($idExpediente, $data);

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
            $userData = $this->checkApiAccess([1]);

            // 1. Verificar existencia
            $registro = $this->ExpedientesModel->getExpedienteById($idExpediente);
            if (!$registro) {
                $this->sendErrorResponse(404, "Expediente no encontrado.");
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
            // ROL REQUERIDO: [1] Admin, [2] Ejecutivo, [3] Consulta
            $userData = $this->checkApiAccess([1, 2, 3]);

            // 1. Validar parámetros de entrada
            if (!isset($_GET['ruta'], $_GET['id_expediente']) || empty($_GET['ruta']) || empty($_GET['id_expediente'])) {
                $this->sendErrorResponse(400, 'Error: Parámetros "ruta" o "id_expediente" faltantes.');
            }
            $archivo_relativo = urldecode($_GET['ruta']);
            $id_expediente = intval($_GET['id_expediente']);
            $id_user = $userData['id'];
            $usuario = $userData['usuario'] ?? 'API_USER';

            // --- CONFIGURACIÓN DE RUTA ---
            $ruta_archivo_real = RUTA_BASE . '/' . $archivo_relativo;
            $ruta_archivo_real = realpath($ruta_archivo_real);

            if ($ruta_archivo_real === false || !file_exists($ruta_archivo_real)) {
                $this->sendErrorResponse(404, 'El documento no existe en el servidor.');
            }
            // 3. LOGGING DE ÉXITO (ANTES de enviar el binario y salir)
            // Ya que no podemos llamar a sendSuccessResponse, debemos llamar a logApiTransaction directamente.
            $this->logApiTransaction(
                ['id' => $id_user],
                $_SERVER['REQUEST_URI'] ?? 'N/A',
                $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                200, // Código 200 OK
                "Visualización exitosa del documento ID {$id_expediente}.",
                ['id_expediente' => $id_expediente, 'filename' => basename($ruta_archivo_real)]
            );
            // 4. Devolver el archivo binario
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $ruta_archivo_real);
            finfo_close($finfo);
            // Leer el contenido del archivo
            $file_content = file_get_contents($ruta_archivo_real);
            // Enviar headers para forzar la descarga o visualización en línea
            // Evitar que el output buffer interfiera con el binario
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
            header('Content-Length: ' . strlen($file_content));
            echo $file_content;
            exit; // Detener la ejecución
        } catch (\Exception $e) {
            // Manejar cualquier error durante el proceso de visualización o registro
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
        // --- 🔐 SEGURIDAD: Validación de JWT y Roles ---
        // Roles permitidos: 1 (Admin) y 2 (Ejecutivo, por ejemplo).
        // Si el rol no es permitido, checkApiAccess enviará un 403 y detendrá la ejecución.
        $userData = $this->checkApiAccess([1, 2]);
        // === 🛠️ DEFINICIÓN DE RUTAS ABSOLUTAS DE EJECUTABLES ===
        // Asegúrate de que estas constantes estén disponibles en el scope de la aplicación.
        if (!defined('MAGICK_EXECUTABLE_PATH') || !defined('TESSERACT_EXECUTABLE_PATH') || !defined('RUTA_BASE')) {
            $this->sendErrorResponse(500, "Error de configuración: Rutas de ejecutables o RUTA_BASE no definidas.");
            return;
        }
        // Escapar rutas para usarlas de forma segura en los comandos de shell
        $magick_escaped = escapeshellarg(MAGICK_EXECUTABLE_PATH);
        $tesseract_escaped = escapeshellarg(TESSERACT_EXECUTABLE_PATH);
        // --- ⚙️ PREPARACIÓN Y SANITIZACIÓN DE DATOS ---
        date_default_timezone_set('America/Asuncion');
        $id_proceso = date("Ymd-His");
        // Recogemos y sanitizamos los datos de los índices desde $_POST (form-data)
        // --- ⚙️ PREPARACIÓN Y SANITIZACIÓN DE DATOS (desde POST/form-data) ---
        $id_tipoDoc = filter_var($_POST['id_tipoDoc'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $indice_01  = filter_var($_POST['indice_01'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $indice_02  = filter_var($_POST['indice_02'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $indice_03  = filter_var($_POST['indice_03'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        // Indice 04 se mantiene por el uso de caracteres especiales en preg_replace
        $indice_04  = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['indice_04'] ?? '');
        $indice_05  = filter_var($_POST['indice_05'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        $indice_06  = filter_var($_POST['indice_06'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS);
        // Validación mínima de metadatos
        if (empty($indice_04)) {
            $this->sendErrorResponse(400, "Falta el 'indice_04' (nombre de archivo).");
            return;
        }
        // --- 📁 RUTAS DE ARCHIVOS ---
        $nombre_final = $indice_04 . '.pdf';
        $ruta_original = 'Expedientes/' . $nombre_final;
        $ubicacion_fisica = RUTA_BASE . $ruta_original;
        // --- 📤 VERIFICAR Y MOVER ARCHIVO SUBIDO ---
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            $error_code = $_FILES['file_pdf']['error'] ?? 'N/A';
            $this->sendErrorResponse(400, "Error al subir el archivo. Código de error UPLOAD: " . $error_code);
            return;
        }
        // Crear el directorio si no existe
        $dir_expedientes = dirname($ubicacion_fisica);
        if (!is_dir($dir_expedientes)) {
            if (!@mkdir($dir_expedientes, 0777, true)) {
                $this->sendErrorResponse(500, "Error al crear el directorio de expedientes.");
                return;
            }
        }
        // Mover el archivo al repositorio
        if (!move_uploaded_file($_FILES['file_pdf']['tmp_name'], $ubicacion_fisica)) {
            $this->sendErrorResponse(500, "Error al mover el archivo al repositorio.");
            return;
        }
        // =============================================
        // 🔄 PROCESAMIENTO DE IMAGEN: OCR
        // =============================================
        $temp_dir = RUTA_BASE . 'Temp/' . $id_proceso . '/';
        $temp_output = RUTA_BASE . 'Temp/oriented_' . $nombre_final;
        $num_paginas = 0;
        $procesamiento_exitoso = false;
        if (!is_dir($temp_dir)) {
            @mkdir($temp_dir, 0777, true);
        }
        if (is_dir($temp_dir)) {
            $pdf_path_escaped = escapeshellarg($ubicacion_fisica);
            $img_output_path = escapeshellarg($temp_dir . 'page_%03d.png');

            // 1️⃣ Convertir PDF a imágenes
            $cmd_convert = "$magick_escaped -density 300 -units PixelsPerInch $pdf_path_escaped $img_output_path 2>&1";
            exec($cmd_convert, $output_lines, $return_var);
            $images = glob($temp_dir . "page_*.png");
            if (!empty($images)) {
                $procesamiento_exitoso = true;
                foreach ($images as $img) {
                    $img_escaped = escapeshellarg($img);

                    // 2️⃣ Enderezar visualmente (deskew)
                    $cmd_deskew = "$magick_escaped mogrify -deskew 40% $img_escaped 2>&1";
                    shell_exec($cmd_deskew);

                    // 3️⃣ Detectar orientación y rotar
                    $cmd_tesseract = "$tesseract_escaped $img_escaped stdout --psm 0 -l osd 2>&1";
                    exec($cmd_tesseract, $output_lines_tess, $return_var_tess);
                    $output = implode("\n", $output_lines_tess);

                    if (preg_match('/Orientation in degrees: (\d+)/', $output, $match)) {
                        $angle = intval($match[1]);
                        if ($angle > 0 && $angle < 360) {
                            $rotate = 360 - $angle;
                            $cmd_rotate = "$magick_escaped mogrify -rotate $rotate $img_escaped 2>&1";
                            shell_exec($cmd_rotate);
                        }
                    }
                }
                // 4️⃣ Reconstruir PDF
                $temp_output_escaped = escapeshellarg($temp_output);
                $img_input_path = escapeshellarg($temp_dir . "page_*.png");
                $cmd_rebuild = "$magick_escaped -density 300 -units PixelsPerInch $img_input_path $temp_output_escaped 2>&1";
                shell_exec($cmd_rebuild);

                // Reemplazar el PDF original si la reconstrucción fue exitosa
                if (file_exists($temp_output)) {
                    if (!rename($temp_output, $ubicacion_fisica)) {
                        $procesamiento_exitoso = false; // Fallo al reemplazar el archivo
                    }
                } else {
                    $procesamiento_exitoso = false; // Fallo al crear el PDF reconstruido
                }
            }
        }
        // 5️⃣ Contar páginas con Imagick
        if (extension_loaded('imagick')) {
            try {
                $imagick = new \Imagick($ubicacion_fisica);
                $num_paginas = $imagick->getNumberImages();
                $imagick->clear();
                $imagick->destroy();
            } catch (\Exception $e) {
                // Error al contar páginas, se queda en 0.
            }
        }
        // Limpiar archivos temporales
        if (is_dir($temp_dir)) {
            array_map('unlink', glob($temp_dir . "page_*.png"));
            @rmdir($temp_dir);
        }
        // =============================
        // 💾 REGISTRO EN BD
        // =============================
        $ubicacion = "scantec";
        $version = "1.0";
        $fecha_indexado = date("Y-m-d");
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
            $ruta_original,
            $ubicacion,
            $version,
            $fecha_indexado
        );
        if ($registrar) {
            $this->sendSuccessResponse(
                201, // Código HTTP 201: Created
                [
                    'id_proceso' => $id_proceso,
                    'indice_01' => $indice_01,
                    'indice_04' => $indice_04,
                    'ruta_original' => $ruta_original,
                    'paginas' => $num_paginas,
                    'procesado_ok' => $procesamiento_exitoso
                ],
                "Archivo subido, procesado y registrado correctamente."
            );
        } else {
            // Fallo de registro.
            $this->sendErrorResponse(500, "Error interno al registrar el expediente en la base de datos.");
        }
    }
}
