<?php
require_once("Libraries/Core/ApiController.php");
class LegajosController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $modelName = 'LegajosModel';
        $modelFile = "Models/{$modelName}.php";
        if (file_exists($modelFile)) {
            require_once($modelFile);
            $this->model = new \LegajosModel();
        } else {
            $this->sendErrorResponse(500, 'Error interno: El modelo LegajosModel no fue encontrado.');
        }
    }

    // ==========================================================
    // 1. LISTAR TIPOS DE LEGAJO — api/v1/legajosController/listar_tipoLegajos
    //    Devuelve solo el catálogo de tipos permitidos para el rol del token.
    // ==========================================================
    public function listar_tipoLegajos()
    {
        try {
            $userData = $this->checkApiAccess();
            $idRol = intval($userData['roles'][0] ?? 0);

            $tipos_legajo = $this->model->selectTiposLegajo();
            $tiposPermitidos = $this->obtenerTiposLegajoPermitidos($idRol);

            if ($this->rolTieneFiltroTipos($idRol)) {
                $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                    return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
                }));
            }

            $this->sendSuccessResponse(200, [
                'total' => count($tipos_legajo),
                'tipos_legajo' => $tipos_legajo
            ], 'Tipos de legajo obtenidos correctamente.');
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al obtener tipos de legajo: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // 2. LISTAR / BUSCAR LEGAJOS — api/v1/legajosController/listar
    // ==========================================================
    public function listar()
    {
        try {
            $userData = $this->checkApiAccess();
            $idRol = intval($userData['roles'][0] ?? 0);
            $idUsuarioActual = intval($userData['id'] ?? 0);

            $busquedaEjecutada = array_key_exists('termino', $_GET) || array_key_exists('estado_legajo', $_GET)
                || array_key_exists('id_tipo_legajo', $_GET) || array_key_exists('filtro_documentos', $_GET);
            $termino = trim($_GET['termino'] ?? '');
            $estado_legajo = trim($_GET['estado_legajo'] ?? '');
            $id_tipo_legajo = intval($_GET['id_tipo_legajo'] ?? 0);
            $filtro_documentos = trim($_GET['filtro_documentos'] ?? '');

            $tiposPermitidos = $this->obtenerTiposLegajoPermitidos($idRol);
            $tieneFiltroTipos = $this->rolTieneFiltroTipos($idRol);

            // Autorización: si pide un tipo puntual que no le corresponde, 403
            if ($id_tipo_legajo > 0 && $tieneFiltroTipos && !in_array($id_tipo_legajo, $tiposPermitidos, true)) {
                $this->sendErrorResponse(403, 'No tiene permisos para buscar en este tipo de legajo.');
                return;
            }

            $soloPropios = $this->usuarioSoloVePropios($idRol);

            $resultados = [];
            if ($busquedaEjecutada) {
                if ($estado_legajo === 'Proceso') {
                    $terminoBusqueda = $termino !== '' ? $termino : '*.*';
                    $resultados = $this->model->buscarLegajosPorTermino($terminoBusqueda, '', $id_tipo_legajo, $filtro_documentos, $idUsuarioActual, $soloPropios, $tiposPermitidos);
                } else {
                    $resultados = $this->model->buscarLegajosPorTermino($termino, $estado_legajo, $id_tipo_legajo, $filtro_documentos, $idUsuarioActual, $soloPropios, $tiposPermitidos);
                }
            }

            if (!empty($resultados)) {
                $resultados = $this->enriquecerResultadosConPdfFinal($resultados);
                foreach ($resultados as &$resultado) {
                    $resultado['estado_legajo_texto'] = $this->resolverEstadoVisualBusquedaLegajo($resultado);
                }
                unset($resultado);

                if ($estado_legajo === 'Proceso') {
                    $resultados = array_values(array_filter($resultados, function ($r) {
                        $t = $r['estado_legajo_texto'];
                        return strcasecmp($t, 'Incompleto') === 0 || strcasecmp($t, 'Proceso') === 0;
                    }));
                } elseif ($estado_legajo === 'Completado') {
                    $resultados = array_values(array_filter($resultados, function ($r) {
                        $t = $r['estado_legajo_texto'];
                        $bd = strtolower(trim((string) ($r['estado'] ?? '')));
                        return strcasecmp($t, 'Completado') === 0 || $bd === 'generado';
                    }));
                }
            }

            $this->sendSuccessResponse(200, [
                'busqueda_ejecutada' => $busquedaEjecutada,
                'filtros_aplicados' => [
                    'termino' => $termino,
                    'estado_legajo' => $estado_legajo,
                    'id_tipo_legajo' => $id_tipo_legajo,
                    'filtro_documentos' => $filtro_documentos,
                ],
                'total_resultados' => count($resultados),
                'resultados' => $resultados
            ], 'Búsqueda de legajos completada.');
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al buscar legajos: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // 3. VER PDF FINAL DEL LEGAJO — api/v1/legajosController/verDocumento?id_legajo=N
    // ==========================================================
    public function verDocumento()
    {
        try {
            $userData = $this->checkApiAccess();
            $idRol = intval($userData['roles'][0] ?? 0);
            $idUsuario = intval($userData['id'] ?? 0);

            if (!isset($_GET['id_legajo']) || empty($_GET['id_legajo'])) {
                $this->sendErrorResponse(400, 'Error: Parámetro "id_legajo" faltante.');
            }
            $idLegajo = intval($_GET['id_legajo']);

            // 1. El legajo debe existir
            $legajo = $this->model->selectLegajoPorId($idLegajo);
            if (empty($legajo)) {
                $this->sendErrorResponse(404, 'Legajo no encontrado.');
            }

            // 2. AUTORIZACIÓN por tipo de legajo permitido para el rol (salvo admin)
            if ($idRol !== 1) {
                $tiposPermitidos = $this->obtenerTiposLegajoPermitidos($idRol);
                if (
                    $this->rolTieneFiltroTipos($idRol)
                    && !in_array(intval($legajo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true)
                ) {
                    $this->sendErrorResponse(403, 'No tienes permiso para acceder a este tipo de legajo.');
                }
                // Si el rol solo ve legajos propios, validar propiedad
                if (
                    $this->usuarioSoloVePropios($idRol)
                    && !$this->model->usuarioEsPropietarioLegajo($idLegajo, $idUsuario)
                ) {
                    $this->sendErrorResponse(403, 'Solo puedes acceder a legajos creados por tu propio usuario.');
                }
            }

            // 3. Resolver la ruta del PDF final DESDE LA BD (no del cliente)
            $registroPdf = $this->model->selectRegistroPdfFinalLegajo($idLegajo);
            if (empty($registroPdf['ruta_creacion']) || empty($registroPdf['nombre_archivo'])) {
                $this->sendErrorResponse(404, 'El legajo no tiene un PDF final generado.');
            }

            $rutaRelativa = trim((string) $registroPdf['ruta_creacion'], '/\\')
                . '/' . basename((string) $registroPdf['nombre_archivo']);

            // 4. PATH TRAVERSAL: el archivo real debe quedar dentro de RUTA_BASE
            $rutaBaseReal = realpath(rtrim(RUTA_BASE, '/\\'));
            $rutaArchivoReal = realpath(rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR
                . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa));

            if (
                $rutaBaseReal === false || $rutaArchivoReal === false
                || strpos($rutaArchivoReal, $rutaBaseReal) !== 0
                || !is_file($rutaArchivoReal)
            ) {
                $this->sendErrorResponse(404, 'El PDF del legajo no existe en el servidor.');
            }

            // 5. Logging
            $this->logApiTransaction(
                ['id' => $idUsuario],
                $_SERVER['REQUEST_URI'] ?? 'N/A',
                $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                200,
                "Visualización exitosa del PDF del legajo ID {$idLegajo}.",
                ['id_legajo' => $idLegajo, 'filename' => basename($rutaArchivoReal)]
            );

            // 6. Enviar en streaming
            if (ob_get_level() > 0) {
                ob_clean();
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($rutaArchivoReal) . '"');
            header('Content-Length: ' . filesize($rutaArchivoReal));
            readfile($rutaArchivoReal);
            exit;
        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno al servir el PDF del legajo: ' . $e->getMessage());
        }
    }

    public function verDocumentoIndividual()
    {
        try {
            $userData = $this->checkApiAccess(); 

            if (
                !isset($_GET['id_legajo'], $_GET['id_requisito'])
                || empty($_GET['id_legajo']) || empty($_GET['id_requisito'])
            ) {
                $this->sendErrorResponse(400, 'Parámetros "id_legajo" e "id_requisito" son obligatorios.');
            }

            $idLegajo = intval($_GET['id_legajo']);
            $idRequisito = intval($_GET['id_requisito']);

            $registro = $this->model->getDocumentoChecklistApiPorId($idLegajo, $idRequisito);
            if (empty($registro)) {
                $this->sendErrorResponse(404, 'Documento no encontrado o sin versión vigente para el legajo indicado.');
            }

            // Autorización por tipo de legajo
            $tiposPermitidos = $this->obtenerTiposLegajoPermitidos($userData['roles'][0] ?? 0);
            if (!empty($tiposPermitidos) && !in_array(intval($registro['id_tipo_legajo']), $tiposPermitidos)) {
                $this->sendErrorResponse(403, 'No tienes permiso para acceder a documentos de este tipo de legajo.');
            }

            // Autorización por propietario
            if ($this->usuarioSoloVePropios($userData['roles'][0] ?? 0)) {
                $esPropietario = intval($registro['id_usuario']) === intval($userData['id'])
                    || intval($registro['id_usuario_armado']) === intval($userData['id']);
                if (!$esPropietario) {
                    $this->sendErrorResponse(403, 'No tienes permiso para acceder a este documento.');
                }
            }

            // Estado del requisito — no tiene sentido servir un archivo si nunca se cargó
            if ($registro['estado'] === 'pendiente' || empty($registro['ruta_archivo'])) {
                $this->sendErrorResponse(404, 'El requisito no tiene un archivo cargado.');
            }

            $rutaRelativa = trim((string) $registro['ruta_archivo']);
            if (trim($rutaRelativa, DIRECTORY_SEPARATOR) === '') {
                $this->sendErrorResponse(404, 'El requisito no tiene un archivo asociado.');
            }

            // 4) Path traversal — idéntico patrón a Expedientes::verDocumento
            $rutaBaseReal = realpath(rtrim(RUTA_BASE, '/\\'));
            $rutaArchivoReal = realpath(
                rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR
                . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa)
            );
            if (
                $rutaBaseReal === false || $rutaArchivoReal === false
                || strpos($rutaArchivoReal, $rutaBaseReal) !== 0 || !is_file($rutaArchivoReal)
            ) {
                $this->sendErrorResponse(404, 'El documento no existe en el servidor.');
            }

            // 5) Log de auditoría (mismo patrón dual archivo+BD)
            $this->logApiTransaction(
                ['id' => $userData['id']],
                $_SERVER['REQUEST_URI'] ?? 'N/A',
                $_SERVER['REQUEST_METHOD'] ?? 'N/A',
                200,
                "Visualización de documento individual (legajo {$idLegajo}, requisito {$idRequisito}).",
                ['id_legajo' => $idLegajo, 'id_requisito' => $idRequisito, 'filename' => basename($rutaArchivoReal)]
            );

            // 6) Streaming
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $rutaArchivoReal);
            finfo_close($finfo);
            if (ob_get_level() > 0) {
                ob_clean();
            }
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . basename($rutaArchivoReal) . '"');
            header('Content-Length: ' . filesize($rutaArchivoReal));
            readfile($rutaArchivoReal);
            exit;

        } catch (\Exception $e) {
            $this->sendErrorResponse(500, 'Error interno: ' . $e->getMessage());
        }
    }

    // ==========================================================
    // MÉTODOS DE PERMISOS — reciben id_rol del JWT, NO de sesión
    // ==========================================================
    private function obtenerTiposLegajoPermitidos(int $idRol): array
    {
        if ($idRol === 1) {
            return [];
        }
        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }
            $segModel = new SeguridadLegajosModel();
            $tiposDisponibles = $this->model->selectTiposLegajo();
            $tiposPermitidos = $segModel->obtenerTiposLegajoPermitidosPorRol($idRol, $tiposDisponibles);
            return !empty($tiposPermitidos) ? $tiposPermitidos : [-1];
        } catch (Throwable $e) {
            return [-1];
        }
    }

    private function rolTieneFiltroTipos(int $idRol): bool
    {
        return $idRol > 2;
    }

    private function usuarioSoloVePropios(int $idRol): bool
    {
        if (in_array($idRol, [1, 2, 5], true)) {
            return false;
        }
        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }
            $segModel = new SeguridadLegajosModel();
            return !$segModel->puedeVerLegajosOtrosUsuarios($idRol);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function enriquecerResultadosConPdfFinal(array $resultados): array
    {
        foreach ($resultados as &$resultado) {
            $idLegajo = intval($resultado['id_legajo'] ?? 0);
            $registroPdf = $idLegajo > 0 ? $this->model->selectRegistroPdfFinalLegajo($idLegajo) : [];
            $tieneFilaPdf = !empty($registroPdf['ruta_creacion']) && !empty($registroPdf['nombre_archivo']);
            $rutaArchivoRegistrado = null;
            if ($tieneFilaPdf && defined('RUTA_BASE')) {
                $rutaArchivoRegistrado = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR
                    . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim((string) ($registroPdf['ruta_creacion'] ?? ''), '/\\'))
                    . DIRECTORY_SEPARATOR . basename((string) ($registroPdf['nombre_archivo'] ?? ''));
            }
            $archivoRegistradoExiste = !empty($rutaArchivoRegistrado) && is_file($rutaArchivoRegistrado);
            $resultado['pdf_legajo_tiene_fila'] = $tieneFilaPdf;
            $resultado['pdf_final_disponible'] = $archivoRegistradoExiste;
            $resultado['pdf_legajo_armado'] = $tieneFilaPdf && $archivoRegistradoExiste;
        }
        unset($resultado);
        return $resultados;
    }

    private function resolverEstadoVisualBusquedaLegajo(array $resultado): string
    {
        $estadoCrudo = strtolower(trim((string) ($resultado['estado'] ?? '')));
        if ($estadoCrudo === 'generado') {
            return 'Generado';
        }
        if ($estadoCrudo === 'completado' || $estadoCrudo === 'finalizado') {
            return 'Completado';
        }
        return trim((string) ($resultado['estado_legajo_texto'] ?? $this->obtenerTextoEstadoLegajo($estadoCrudo)));
    }

    private function obtenerTextoEstadoLegajo(?string $estado): string
    {
        $estado = strtolower(trim((string) $estado));
        switch ($estado) {
            case 'cerrado':
            case 'aprobado':
                return 'Cerrado';
            case 'verificacion_rechazada':
                return 'Verificación rechazada';
            case 'verificado':
                return 'Verificado';
            case 'generado':
                return 'Generado';
            case 'completado':
            case 'finalizado':
                return 'Completado';
            case 'activo':
                return 'Vencido';
            default:
                return 'Incompleto';
        }
    }
}