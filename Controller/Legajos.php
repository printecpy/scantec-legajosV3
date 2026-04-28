<?php

class Legajos extends Controllers
{
    private array $configuracionBrandingLegajosPorTipo = [];

    private function esAccionPublicaFormularioExterno(): bool
    {
        $rutaActual = strtolower(trim((string)($_GET['url'] ?? ''), '/'));
        $accionesPublicas = [
            'legajos/formulario_externo',
            'legajos/guardar_borrador_formulario_externo',
            'legajos/enviar_formulario_externo',
            'legajos/confirmacion_formulario_externo',
        ];

        return in_array($rutaActual, $accionesPublicas, true);
    }

    private function asegurarTokenCsrfPublico(): void
    {
        if (empty($_SESSION['csrf_token']) || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }
    }

    private function normalizarCedula(string $cedula): string
    {
        return preg_replace('/\D+/', '', trim($cedula));
    }

    private function tokenFormularioExternoValido(string $token): bool
    {
        $autorizados = $_SESSION['formularios_externos_autorizados'] ?? [];
        if (!is_array($autorizados) || empty($autorizados[$token])) {
            return false;
        }

        return intval($autorizados[$token]) >= time();
    }

    private function autorizarTokenFormularioExterno(string $token, int $segundos = 3600): void
    {
        if (!isset($_SESSION['formularios_externos_autorizados']) || !is_array($_SESSION['formularios_externos_autorizados'])) {
            $_SESSION['formularios_externos_autorizados'] = [];
        }

        $_SESSION['formularios_externos_autorizados'][$token] = time() + max(300, $segundos);
    }

    private function revocarTokenFormularioExterno(string $token): void
    {
        if (isset($_SESSION['formularios_externos_autorizados'][$token])) {
            unset($_SESSION['formularios_externos_autorizados'][$token]);
        }
    }

    private function formularioExternoDisponible(array $formulario): bool
    {
        if (empty($formulario)) {
            return false;
        }

        $estado = strtolower(trim((string)($formulario['estado'] ?? '')));
        if (in_array($estado, ['activo', 'borrador'], true)) {
            return true;
        }

        if (in_array($estado, ['enviado', 'anulado', 'desactivado'], true)) {
            return false;
        }

        $venceEn = trim((string)($formulario['vence_en'] ?? ''));
        if ($venceEn === '') {
            return false;
        }

        try {
            $fechaVencimiento = new DateTime($venceEn);
            return $fechaVencimiento >= new DateTime();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function obtenerDirectorioFormularioExterno(string $token): string
    {
        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        return rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Temp' . DIRECTORY_SEPARATOR . 'FormulariosExternos' . DIRECTORY_SEPARATOR . $token . DIRECTORY_SEPARATOR;
    }

    private function construirUrlFormularioExterno(string $token): string
    {
        return base_url() . 'legajos/formulario_externo?token=' . urlencode($token);
    }

    private function guardarArchivoTemporalFormularioExterno(
        string $token,
        array $regla,
        int $idRequisito,
        array $archivo,
        string $ciSocio,
        string $nroSolicitud
    ): ?string {
        $directorio = $this->obtenerDirectorioFormularioExterno($token);
        if (!is_dir($directorio)) {
            @mkdir($directorio, 0777, true);
        }

        $codigoDocumento = trim((string)($regla['codigo_interno'] ?? ''));
        if ($codigoDocumento === '') {
            $codigoDocumento = 'DOC' . $idRequisito;
        }
        $codigoDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', $codigoDocumento);
        $rolDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', strtoupper(trim((string)($regla['rol_vinculado'] ?? 'TITULAR'))));
        if ($rolDocumento === '') {
            $rolDocumento = 'TITULAR';
        }
        $cedula = $this->normalizarCedula($ciSocio);
        if ($cedula === '') {
            $cedula = 'SINCI';
        }
        $solicitud = preg_replace('/[^0-9A-Za-z]+/', '', (string)$nroSolicitud);

        $segmentos = [$codigoDocumento, $rolDocumento, 'REQ' . $idRequisito, $cedula];
        if ($solicitud !== '') {
            $segmentos[] = $solicitud;
        }

        $nombreArchivo = implode('_', $segmentos) . '.pdf';
        $rutaDestino = $directorio . $nombreArchivo;
        if ($this->generarPdfDesdeArchivosSubidos($archivo, $rutaDestino, $directorio)) {
            return 'Temp/FormulariosExternos/' . $token . '/' . $nombreArchivo;
        }

        return null;
    }

    private function eliminarArchivoRelativoSiExiste(?string $rutaRelativa): void
    {
        $rutaRelativa = trim((string)$rutaRelativa);
        if ($rutaRelativa === '') {
            return;
        }

        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $rutaFisica = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);
        if (is_file($rutaFisica)) {
            @unlink($rutaFisica);
        }
    }

    private function guardarFlashFormularioLegajo(array $payload): void
    {
        $_SESSION['legajo_form_flash'] = $payload;
    }

    private function consumirFlashFormularioLegajo(): array
    {
        $flash = $_SESSION['legajo_form_flash'] ?? [];
        unset($_SESSION['legajo_form_flash']);
        return is_array($flash) ? $flash : [];
    }

    private function moduloPersonasActivo(): bool
    {
        try {
            require_once 'Models/FuncionalidadesModel.php';
            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->estaSeccionHabilitada('personas');
        } catch (Throwable $e) {
            return false;
        }
    }

    private function obtenerPersonasActivas(): array
    {
        if (!$this->moduloPersonasActivo()) {
            return [];
        }

        require_once 'Models/PersonasModel.php';
        $personasModel = new PersonasModel();
        return $personasModel->selectPersonasActivas();
    }

    private function obtenerPersonaActivaPorId(int $idPersona): array
    {
        if ($idPersona <= 0 || !$this->moduloPersonasActivo()) {
            return [];
        }

        require_once 'Models/PersonasModel.php';
        $personasModel = new PersonasModel();
        $persona = $personasModel->selectPersonaPorId($idPersona);
        return strtolower((string)($persona['estado'] ?? '')) === 'activo' ? $persona : [];
    }

    private function baseUsuariosExternaHabilitada(): bool
    {
        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];
        $valor = strtolower(trim((string)($config['db_usuarios_enabled'] ?? '0')));
        return in_array($valor, ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
    }

    private function obtenerFuentePersonasLegajo(): string
    {
        if ($this->baseUsuariosExternaHabilitada()) {
            return 'externa';
        }

        if ($this->moduloPersonasActivo()) {
            return 'interna';
        }

        return 'ninguna';
    }

    private function normalizarPersonaExterna(array $persona): array
    {
        $nombreCompleto = trim((string)($persona['nombre_completo'] ?? ''));

        return [
            'selector_valor' => (string)intval($persona['id'] ?? 0),
            'id_persona' => 0,
            'id_externo' => intval($persona['id'] ?? 0),
            'nombre_completo' => $nombreCompleto,
            'ci' => trim((string)($persona['nro_cedula'] ?? '')),
            'nro_solicitud' => trim((string)($persona['nro_solicitud'] ?? '')),
            'origen' => 'externa',
        ];
    }

    private function obtenerPersonasExternasActivas(): array
    {
        if (!$this->baseUsuariosExternaHabilitada()) {
            return [];
        }

        try {
            require_once 'Models/UsuariosDatosModel.php';
            $usuariosDatosModel = new UsuariosDatosModel();
            $personas = [];

            foreach ($usuariosDatosModel->obtenerTodos() as $persona) {
                $normalizada = $this->normalizarPersonaExterna($persona);
                if ($normalizada['selector_valor'] !== '' && $normalizada['ci'] !== '' && $normalizada['nombre_completo'] !== '') {
                    $personas[] = $normalizada;
                }
            }

            return $personas;
        } catch (Throwable $e) {
            error_log('No se pudo cargar la base externa de personas: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerPersonaExternaPorId(int $idPersonaExterna): array
    {
        if ($idPersonaExterna <= 0 || !$this->baseUsuariosExternaHabilitada()) {
            return [];
        }

        try {
            require_once 'Models/UsuariosDatosModel.php';
            $usuariosDatosModel = new UsuariosDatosModel();
            $persona = $usuariosDatosModel->obtenerPorId($idPersonaExterna);
            return empty($persona) ? [] : $this->normalizarPersonaExterna($persona);
        } catch (Throwable $e) {
            error_log('No se pudo obtener la persona desde la base externa: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerSelectorPersonaActual(array $legajo, array $personasActivas, string $fuente): string
    {
        if ($fuente === 'interna') {
            $idPersona = intval($legajo['id_persona'] ?? 0);
            return $idPersona > 0 ? (string)$idPersona : '';
        }

        if ($fuente === 'externa') {
            $ciLegajo = $this->normalizarCedula((string)($legajo['ci_socio'] ?? ''));
            if ($ciLegajo === '') {
                return '';
            }

            foreach ($personasActivas as $persona) {
                if ($this->normalizarCedula((string)($persona['ci'] ?? '')) === $ciLegajo) {
                    return (string)($persona['selector_valor'] ?? '');
                }
            }
        }

        return '';
    }

    private function construirFlashDocumentosDesdePost(): array
    {
        $documentos = [];

        foreach ($_POST as $clave => $valor) {
            if (strpos($clave, 'valor_campo_') === 0) {
                $idRequisito = intval(substr($clave, strlen('valor_campo_')));
                if ($idRequisito > 0) {
                    $documentos[$idRequisito]['valor_campo'] = is_array($valor)
                        ? ''
                        : trim((string)$valor);
                }
                continue;
            }

            if (strpos($clave, 'fecha_expedicion_') === 0) {
                $idRequisito = intval(substr($clave, strlen('fecha_expedicion_')));
                if ($idRequisito > 0) {
                    $documentos[$idRequisito]['fecha_expedicion'] = trim((string)$valor);
                }
                continue;
            }

            if (strpos($clave, 'observacion_') === 0) {
                $idRequisito = intval(substr($clave, strlen('observacion_')));
                if ($idRequisito > 0) {
                    $documentos[$idRequisito]['observacion'] = trim((string)$valor);
                }
            }
        }

        return $documentos;
    }

    private function normalizarTipoCampo(array $regla): string
    {
        $tipoCampo = strtolower(trim((string)($regla['tipo_campo'] ?? 'documento')));
        return in_array($tipoCampo, ['documento', 'texto', 'lista', 'casilla'], true) ? $tipoCampo : 'documento';
    }

    private function esCampoDocumento(array $regla): bool
    {
        return $this->normalizarTipoCampo($regla) === 'documento';
    }

    private function obtenerValorCampoDesdePost(int $idRequisito, array $regla, ?string $valorAnterior = ''): string
    {
        $clave = 'valor_campo_' . $idRequisito;
        $tipoCampo = $this->normalizarTipoCampo($regla);

        if ($tipoCampo === 'casilla') {
            return isset($_POST[$clave]) ? '1' : '0';
        }

        return trim((string)($_POST[$clave] ?? $valorAnterior ?? ''));
    }

    private function campoValorCompleto(array $regla, ?string $valor): bool
    {
        if ($this->normalizarTipoCampo($regla) === 'casilla') {
            return trim((string)$valor) === '1';
        }

        return trim((string)$valor) !== '';
    }

    private function construirUrlArmarLegajo(int $idLegajo = 0, int $duplicadoDesde = 0): string
    {
        $url = base_url() . "legajos/armar_legajo";
        if ($idLegajo > 0) {
            $url .= "?id_legajo=" . $idLegajo;
        } elseif ($duplicadoDesde > 0) {
            $url .= "?duplicar_desde=" . $duplicadoDesde;
        }
        return $url;
    }

    private function obtenerContextoAuditoria(): array
    {
        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        $host = '';
        if ($ip !== '') {
            $host = (string)@gethostbyaddr($ip);
            if ($host === false) {
                $host = '';
            }
        }
        if ($host === '') {
            $host = (string)@gethostname();
        }

        return [
            'ip_host' => $ip,
            'nombre_host' => trim($host),
            'id_usuario' => intval($_SESSION['id'] ?? 0),
        ];
    }

    private function sanitizarSegmentoNombreArchivo(string $valor, string $fallback): string
    {
        $valor = trim($valor);
        $valor = preg_replace('/[^A-Za-z0-9]+/', '_', $valor);
        $valor = trim((string)$valor, '_');
        return $valor !== '' ? strtoupper($valor) : $fallback;
    }

    private function construirNombrePdfFinalLegajo(array $legajoActual, int $idLegajo): string
    {
        $nombreTipoLegajo = 'LEGAJO';
        $idTipoLegajo = intval($legajoActual['id_tipo_legajo'] ?? 0);
        if ($idTipoLegajo > 0) {
            $tipoLegajo = $this->model->selectTipoLegajoPorId($idTipoLegajo);
            $nombreTipoLegajo = trim((string)($tipoLegajo['nombre'] ?? 'LEGAJO'));
        }

        $prefijoTipo = $this->sanitizarSegmentoNombreArchivo(substr($nombreTipoLegajo, 0, 20), 'LEGAJO');
        $cedulaLegajo = preg_replace('/[^0-9]+/', '', (string)($legajoActual['ci_socio'] ?? ''));
        if ($cedulaLegajo === '') {
            $cedulaLegajo = 'SINCI';
        }
        $numeroSolicitud = preg_replace('/[^0-9A-Za-z]+/', '', (string)($legajoActual['nro_solicitud'] ?? ''));
        $segmentosNombre = [$prefijoTipo, $cedulaLegajo];
        if ($numeroSolicitud !== '') {
            $segmentosNombre[] = $numeroSolicitud;
        }
        $segmentosNombre[] = 'LEGAJO';
        $nombreFinal = implode('_', $segmentosNombre) . '.pdf';
        return $nombreFinal !== '' ? $nombreFinal : ('LEGAJO_' . $idLegajo . '.pdf');
    }

    private function obtenerRutaPdfFinalLegajo(int $idLegajo): ?array
    {
        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $carpetaLegajo = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($carpetaLegajo)) {
            return null;
        }

        $coincidencias = [];
        foreach ((glob($carpetaLegajo . '*.pdf') ?: []) as $archivoPdf) {
            $nombreArchivo = basename($archivoPdf);
            if (preg_match('/_(legajo|unificado)\.pdf$/i', $nombreArchivo)) {
                $coincidencias[] = $archivoPdf;
            }
        }
        if (empty($coincidencias)) {
            return null;
        }

        usort($coincidencias, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $rutaAbsoluta = $coincidencias[0];
        return [
            'ruta_absoluta' => $rutaAbsoluta,
            'nombre_archivo' => basename($rutaAbsoluta),
        ];
    }

    private function invalidarPdfFinalLegajo(int $idLegajo): void
    {
        if ($idLegajo <= 0 || !defined('RUTA_BASE')) {
            return;
        }

        $carpetaLegajo = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($carpetaLegajo)) {
            return;
        }

        foreach ((glob($carpetaLegajo . '*.pdf') ?: []) as $archivoPdf) {
            $nombreArchivo = basename($archivoPdf);
            if (preg_match('/_(legajo|unificado)\.pdf$/i', $nombreArchivo) && is_file($archivoPdf)) {
                @unlink($archivoPdf);
            }
        }

        if (isset($_SESSION['legajo_pdf_final_listo']['id_legajo']) && intval($_SESSION['legajo_pdf_final_listo']['id_legajo']) === $idLegajo) {
            unset($_SESSION['legajo_pdf_final_listo']);
        }

        if (method_exists($this->model, 'limpiarCantidadPaginasProcesadas')) {
            $this->model->limpiarCantidadPaginasProcesadas($idLegajo);
        }
    }

    private function contarPaginasPdf(string $rutaArchivo): int
    {
        if ($rutaArchivo === '' || !is_file($rutaArchivo)) {
            return 0;
        }

        try {
            if (!class_exists('\setasign\Fpdi\Fpdi')) {
                require_once 'Libraries/fpdi/src/autoload.php';
            }

            $fpdi = new \setasign\Fpdi\Fpdi();
            return max(0, intval($fpdi->setSourceFile($rutaArchivo)));
        } catch (Throwable $e) {
            error_log('No se pudo contar las paginas del PDF final del legajo: ' . $e->getMessage());
            return 0;
        }
    }

    private function sincronizarCantidadPaginasProcesadasLegajo(array $legajo, string $rutaArchivo): void
    {
        $idLegajo = intval($legajo['id_legajo'] ?? 0);
        if ($idLegajo <= 0 || $rutaArchivo === '') {
            return;
        }

        $paginasTotales = $this->contarPaginasPdf($rutaArchivo);
        $paginasProcesadas = $paginasTotales > 0 ? max(0, $paginasTotales - 1) : 0;

        if (method_exists($this->model, 'actualizarCantidadPaginasProcesadas')) {
            $this->model->actualizarCantidadPaginasProcesadas($idLegajo, $paginasProcesadas);
        }

        if (method_exists($this->model, 'registrarFacturacionLegajo')) {
            $this->model->registrarFacturacionLegajo($idLegajo, $paginasProcesadas, $_SESSION['nombre'] ?? null);
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
                    . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim((string)($registroPdf['ruta_creacion'] ?? ''), '/\\'))
                    . DIRECTORY_SEPARATOR
                    . basename((string)($registroPdf['nombre_archivo'] ?? ''));
            }

            $archivoRegistradoExiste = !empty($rutaArchivoRegistrado) && is_file($rutaArchivoRegistrado);
            $resultado['pdf_legajo_tiene_fila'] = $tieneFilaPdf;
            $resultado['pdf_final_disponible'] = $archivoRegistradoExiste;
            $resultado['pdf_legajo_armado'] = $tieneFilaPdf && $archivoRegistradoExiste;
        }
        unset($resultado);

        return $resultados;
    }

    private function legajoTienePdfFinalValido(int $idLegajo): bool
    {
        if ($idLegajo <= 0 || !defined('RUTA_BASE')) {
            return false;
        }

        $registroPdf = $this->model->selectRegistroPdfFinalLegajo($idLegajo);
        if (empty($registroPdf['ruta_creacion']) || empty($registroPdf['nombre_archivo'])) {
            return false;
        }

        $rutaArchivo = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim((string)$registroPdf['ruta_creacion'], '/\\'))
            . DIRECTORY_SEPARATOR
            . basename((string)$registroPdf['nombre_archivo']);

        return is_file($rutaArchivo);
    }

    private function obtenerRutaLogoReducidoEmpresa(): ?string
    {
        if (!defined('ROOT_PATH')) {
            require_once 'Config/Config.php';
        }

        $directorio = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'branding' . DIRECTORY_SEPARATOR;
        if (!is_dir($directorio)) {
            return null;
        }

        $coincidencias = glob($directorio . 'logo_empresa_reducido.*') ?: [];
        if (empty($coincidencias)) {
            return null;
        }

        foreach ($coincidencias as $archivo) {
            $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            if (in_array($extension, ['png', 'jpg', 'jpeg'], true) && is_file($archivo)) {
                return $archivo;
            }
        }

        return null;
    }

    private function obtenerConfiguracionBrandingLegajos(int $idTipoLegajo = 0): array
    {
        if ($idTipoLegajo > 0 && isset($this->configuracionBrandingLegajosPorTipo[$idTipoLegajo])) {
            return $this->configuracionBrandingLegajosPorTipo[$idTipoLegajo];
        }

        $datos = $idTipoLegajo > 0 ? $this->model->selectTipoLegajoPorId($idTipoLegajo) : [];

        $textoMarcaAgua = trim((string)($datos['sello_caratula_texto'] ?? ''));
        $marcaAguaActiva = $textoMarcaAgua !== '';
        $marcaAguaPosicion = trim((string)($datos['sello_caratula_posicion'] ?? 'arriba'));
        $textoSello = trim((string)($datos['sello_anexos_texto'] ?? ''));
        $selloActivo = $textoSello !== '';
        $selloPosicion = trim((string)($datos['sello_anexos_posicion'] ?? 'derecha'));
        if ($marcaAguaPosicion === 'cruzado') {
            $marcaAguaPosicion = 'arriba';
        }
        if (!in_array($marcaAguaPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $marcaAguaPosicion = 'arriba';
        }
        if (!in_array($selloPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $selloPosicion = 'derecha';
        }

        $configuracionTipo = [
            'marca_agua_texto' => $textoMarcaAgua,
            'marca_agua_activa' => $marcaAguaActiva,
            'marca_agua_posicion' => $marcaAguaPosicion,
            'sello_texto' => $textoSello,
            'sello_activo' => $selloActivo,
            'sello_posicion' => $selloPosicion,
        ];

        $this->configuracionBrandingLegajosPorTipo[$idTipoLegajo] = $configuracionTipo;
        return $configuracionTipo;
    }

    private function aplicarMarcaAguaSiCorresponde($pdf, int $idTipoLegajo, bool $comoFondo = false): void
    {
        if (!is_object($pdf) || !method_exists($pdf, 'WatermarkText')) {
            return;
        }

        $configuracion = $this->obtenerConfiguracionBrandingLegajos($idTipoLegajo);
        if (empty($configuracion['marca_agua_activa']) || empty($configuracion['marca_agua_texto'])) {
            return;
        }

        $pdf->WatermarkText(
            (string)$configuracion['marca_agua_texto'],
            $comoFondo,
            (string)($configuracion['marca_agua_posicion'] ?? 'arriba')
        );
    }

    private function aplicarSelloSiCorresponde($pdf, int $idTipoLegajo): void
    {
        if (!is_object($pdf) || !method_exists($pdf, 'SecurityStampText')) {
            return;
        }

        $configuracion = $this->obtenerConfiguracionBrandingLegajos($idTipoLegajo);
        if (empty($configuracion['sello_activo']) || empty($configuracion['sello_texto'])) {
            return;
        }

        $pdf->SecurityStampText((string)$configuracion['sello_texto'], (string)($configuracion['sello_posicion'] ?? 'derecha'));
    }

    private function crearInstanciaPdf()
    {
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Libraries/fpdi/src/autoload.php';

        if (!class_exists('ScantecLegajoPdf', false)) {
            eval('
                class ScantecLegajoPdf extends \setasign\Fpdi\Fpdi
                {
                    protected $anguloRotacion = 0;

                    public function Rotate(float $angulo, float $x = -1, float $y = -1): void
                    {
                        if ($x < 0) {
                            $x = $this->x;
                        }
                        if ($y < 0) {
                            $y = $this->y;
                        }

                        if ($this->anguloRotacion !== 0) {
                            $this->_out("Q");
                        }

                        $this->anguloRotacion = $angulo;
                        if ($angulo !== 0.0) {
                            $anguloRad = $angulo * M_PI / 180;
                            $c = cos($anguloRad);
                            $s = sin($anguloRad);
                            $cx = $x * $this->k;
                            $cy = ($this->h - $y) * $this->k;
                            $this->_out(sprintf(
                                "q %.5F %.5F %.5F %.5F %.5F %.5F cm 1 0 0 1 %.5F %.5F cm",
                                $c,
                                $s,
                                -$s,
                                $c,
                                $cx,
                                $cy,
                                -$cx,
                                -$cy
                            ));
                        }
                    }

                    public function WatermarkText(string $texto, bool $comoFondo = false, string $posicion = "arriba"): void
                    {
                        $texto = trim($texto);
                        if ($texto === "") {
                            return;
                        }

                        $longitud = function_exists("mb_strlen") ? mb_strlen($texto, "UTF-8") : strlen($texto);
                        $tamanoFuente = 34;
                        if ($longitud > 40) {
                            $tamanoFuente = 24;
                        } elseif ($longitud > 24) {
                            $tamanoFuente = 28;
                        }

                        $xCentro = $this->w / 2;
                        $yCentro = $this->h / 2;
                        $textoPdf = function_exists("iconv")
                            ? (@iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $texto) ?: $texto)
                            : $texto;
                        $anchoTexto = $this->GetStringWidth($textoPdf);
                        if ($posicion === "cruzado") {
                            $posicion = "arriba";
                        }
                        $posicion = in_array($posicion, ["arriba", "abajo", "derecha", "izquierda"], true) ? $posicion : "arriba";

                        $this->SetFont("Arial", "B", $tamanoFuente);
                        if ($comoFondo) {
                            $this->SetTextColor(242, 242, 242);
                        } else {
                            $this->SetTextColor(236, 236, 236);
                        }

                        if ($posicion === "arriba") {
                            $x = max(12, ($this->w / 2) - ($anchoTexto / 2));
                            $y = max(24, 28);
                            $this->Text($x, $y, $textoPdf);
                        } elseif ($posicion === "abajo") {
                            $x = max(12, ($this->w / 2) - ($anchoTexto / 2));
                            $y = max(18, $this->h - 18);
                            $this->Text($x, $y, $textoPdf);
                        } elseif ($posicion === "izquierda") {
                            $x = 16;
                            $y = max(40, min($this->h - 30, ($this->h / 2) + 28));
                            $this->Rotate(270, $x, $y);
                            $this->Text($x - ($anchoTexto / 2), $y, $textoPdf);
                            $this->Rotate(0);
                        } elseif ($posicion === "derecha") {
                            $x = max(8, $this->w - 12);
                            $y = max(40, min($this->h - 30, ($this->h / 2) + 28));
                            $this->Rotate(90, $x, $y);
                            $this->Text($x - ($anchoTexto / 2), $y, $textoPdf);
                            $this->Rotate(0);
                        }

                        $this->SetTextColor(0, 0, 0);
                    }

                    public function SecurityStampText(string $texto, string $posicion = "derecha"): void
                    {
                        $texto = trim($texto);
                        if ($texto === "") {
                            return;
                        }

                        $longitud = function_exists("mb_strlen") ? mb_strlen($texto, "UTF-8") : strlen($texto);
                        $tamanoFuente = $longitud > 28 ? 7 : 8;
                        $textoPdf = function_exists("iconv")
                            ? (@iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", $texto) ?: $texto)
                            : $texto;
                        $this->SetFont("Arial", "B", $tamanoFuente);
                        $this->SetTextColor(210, 210, 210);
                        $anchoTexto = $this->GetStringWidth($textoPdf);
                        $posicion = in_array($posicion, ["arriba", "abajo", "derecha", "izquierda"], true) ? $posicion : "derecha";

                        if ($posicion === "arriba") {
                            $x = max(12, ($this->w / 2) - ($anchoTexto / 2));
                            $y = 10;
                            $this->Text($x, $y, $textoPdf);
                        } elseif ($posicion === "abajo") {
                            $x = max(12, ($this->w / 2) - ($anchoTexto / 2));
                            $y = max(8, $this->h - 8);
                            $this->Text($x, $y, $textoPdf);
                        } elseif ($posicion === "izquierda") {
                            $x = 8;
                            $y = max(24, min($this->h - 20, ($this->h / 2) + 18));
                            $this->Rotate(270, $x, $y);
                            $this->Text($x - ($anchoTexto / 2), $y, $textoPdf);
                            $this->Rotate(0);
                        } else {
                            $x = max(3, $this->w - 5);
                            $y = max(24, min($this->h - 20, ($this->h / 2) + 18));
                            $this->Rotate(90, $x, $y);
                            $this->Text($x - ($anchoTexto / 2), $y, $textoPdf);
                            $this->Rotate(0);
                        }

                        $this->SetTextColor(0, 0, 0);
                    }

                    function _endpage()
                    {
                        if ($this->anguloRotacion !== 0) {
                            $this->anguloRotacion = 0;
                            $this->_out("Q");
                        }
                        parent::_endpage();
                    }
                }
            ');
        }

        $pdfClass = 'ScantecLegajoPdf';
        return new $pdfClass();
    }

    private function unirPdfsSimple(array $rutasOrigen, string $rutaDestino): bool
    {
        $rutasValidas = [];
        foreach ($rutasOrigen as $rutaOrigen) {
            $rutaOrigen = trim((string)$rutaOrigen);
            if ($rutaOrigen !== '' && is_file($rutaOrigen)) {
                $rutasValidas[] = $rutaOrigen;
            }
        }

        if (empty($rutasValidas)) {
            return false;
        }

        try {
            $pdf = $this->crearInstanciaPdf();
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);

            foreach ($rutasValidas as $rutaOrigen) {
                $pageCount = $pdf->setSourceFile($rutaOrigen);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }

            $pdf->Output('F', $rutaDestino);
            return is_file($rutaDestino) && filesize($rutaDestino) > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function convertirImagenAPdf(string $rutaImagen, string $rutaPdf, ?string $tipoImagen = null): bool
    {
        if (!is_file($rutaImagen)) {
            return false;
        }

        $extension = strtolower(trim((string)($tipoImagen ?? pathinfo($rutaImagen, PATHINFO_EXTENSION))));
        $rutaTrabajo = $rutaImagen;
        $rutaTemporalJpg = null;

        try {
            if ($extension === 'png' && function_exists('imagecreatefrompng')) {
                $src = @imagecreatefrompng($rutaImagen);
                if (!$src) {
                    return false;
                }

                $w = imagesx($src);
                $h = imagesy($src);
                $dst = imagecreatetruecolor($w, $h);
                $white = imagecolorallocate($dst, 255, 255, 255);
                imagefill($dst, 0, 0, $white);
                imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);

                $rutaTemporalJpg = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'legajo_img_' . uniqid('', true) . '.jpg';
                imagejpeg($dst, $rutaTemporalJpg, 90);
                $rutaTrabajo = $rutaTemporalJpg;
            }

            $tamano = @getimagesize($rutaTrabajo);
            if (!$tamano || empty($tamano[0]) || empty($tamano[1])) {
                return false;
            }

            $pdf = $this->crearInstanciaPdf();
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $tipoFpdf = '';
            if (in_array($extension, ['jpg', 'jpeg', 'jfif'], true)) {
                $tipoFpdf = 'JPEG';
            } elseif ($extension === 'png') {
                $tipoFpdf = 'PNG';
            }
            $this->agregarImagenEnHojaA4($pdf, $rutaTrabajo, $tipoFpdf, $tamano);
            $pdf->Output('F', $rutaPdf);

            return is_file($rutaPdf) && filesize($rutaPdf) > 0;
        } catch (Throwable $e) {
            return false;
        } finally {
            if ($rutaTemporalJpg !== null && is_file($rutaTemporalJpg)) {
                @unlink($rutaTemporalJpg);
            }
        }
    }

    private function agregarImagenEnHojaA4($pdf, string $rutaImagen, string $tipoFpdf = '', ?array $tamanoImagen = null): bool
    {
        if (!is_file($rutaImagen)) {
            return false;
        }

        if ($tamanoImagen === null) {
            $tamanoImagen = @getimagesize($rutaImagen) ?: null;
        }

        if (!$tamanoImagen || empty($tamanoImagen[0]) || empty($tamanoImagen[1])) {
            return false;
        }

        $anchoPx = (float)$tamanoImagen[0];
        $altoPx = (float)$tamanoImagen[1];

        $pageWidth = 210.0;
        $pageHeight = 297.0;
        $margin = 15.0;
        $maxWidth = $pageWidth - ($margin * 2);
        $maxHeight = $pageHeight - ($margin * 2);

        $ratio = min($maxWidth / $anchoPx, $maxHeight / $altoPx);
        $renderWidth = max(1.0, $anchoPx * $ratio);
        $renderHeight = max(1.0, $altoPx * $ratio);
        $posX = ($pageWidth - $renderWidth) / 2;
        $posY = ($pageHeight - $renderHeight) / 2;

        $pdf->AddPage('P', [$pageWidth, $pageHeight]);
        $pdf->Image($rutaImagen, $posX, $posY, $renderWidth, $renderHeight, $tipoFpdf);

        return true;
    }

    private function rasterizarPdfAImagenesTemporales(string $rutaPdf, string $directorioTemporal): array
    {
        if (!is_file($rutaPdf)) {
            return [];
        }

        $imagenesGeneradas = [];
        $prefijoTemporal = rtrim($directorioTemporal, '/\\') . DIRECTORY_SEPARATOR . 'legajo_pdf_' . uniqid('', true);

        if (extension_loaded('imagick')) {
            try {
                $imagickClass = '\Imagick';
                $imagick = new $imagickClass();
                $imagick->setResolution(150, 150);
                $imagick->readImage($rutaPdf);

                $indice = 0;
                foreach ($imagick as $pagina) {
                    $paginaActual = clone $pagina;
                    $paginaActual->setImageAlphaChannel(constant('\Imagick::ALPHACHANNEL_REMOVE'));
                    $paginaActual->setImageBackgroundColor('white');
                    $paginaActual = $paginaActual->mergeImageLayers(constant('\Imagick::LAYERMETHOD_FLATTEN'));
                    $paginaActual->setImageFormat('jpg');

                    $rutaImagen = $prefijoTemporal . '_' . str_pad((string)$indice, 3, '0', STR_PAD_LEFT) . '.jpg';
                    $paginaActual->writeImage($rutaImagen);
                    if (is_file($rutaImagen)) {
                        $imagenesGeneradas[] = $rutaImagen;
                    }
                    $paginaActual->clear();
                    $paginaActual->destroy();
                    $indice++;
                }

                $imagick->clear();
                $imagick->destroy();
            } catch (Throwable $e) {
                foreach ($imagenesGeneradas as $imagenTemporal) {
                    if (is_file($imagenTemporal)) {
                        @unlink($imagenTemporal);
                    }
                }
                $imagenesGeneradas = [];
            }
        }

        if (!empty($imagenesGeneradas)) {
            return $imagenesGeneradas;
        }

        if (!defined('MAGICK_EXECUTABLE_PATH')) {
            return [];
        }

        $magick = trim((string)MAGICK_EXECUTABLE_PATH);
        if ($magick === '') {
            return [];
        }

        $salidaPatron = $prefijoTemporal . '_%03d.jpg';
        $comando = escapeshellarg($magick)
            . ' -density 150 '
            . escapeshellarg($rutaPdf)
            . ' -background white -alpha remove -alpha off '
            . escapeshellarg($salidaPatron)
            . ' 2>&1';

        @shell_exec($comando);
        $imagenesGeneradas = glob($prefijoTemporal . '_*.jpg') ?: [];
        sort($imagenesGeneradas);

        return $imagenesGeneradas;
    }

    private function agregarPdfAlLegajoConFallback($pdf, string $rutaPdf, string $tempDir, int $idTipoLegajo): bool
    {
        if (!is_file($rutaPdf)) {
            return false;
        }

        try {
            $pageCount = $pdf->setSourceFile($rutaPdf);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tpl = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tpl);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
                $this->aplicarSelloSiCorresponde($pdf, $idTipoLegajo);
            }
            return $pageCount > 0;
        } catch (Throwable $e) {
            $imagenesTemporales = $this->rasterizarPdfAImagenesTemporales($rutaPdf, $tempDir);
            $agregadas = false;

            foreach ($imagenesTemporales as $imagenTemporal) {
                $tamanoTemp = @getimagesize($imagenTemporal) ?: null;
                if ($this->agregarImagenEnHojaA4($pdf, $imagenTemporal, 'JPEG', $tamanoTemp)) {
                    $this->aplicarSelloSiCorresponde($pdf, $idTipoLegajo);
                    $agregadas = true;
                }
                if (is_file($imagenTemporal)) {
                    @unlink($imagenTemporal);
                }
            }

            return $agregadas;
        }
    }

    private function normalizarArchivoAPdf(string $rutaOrigen, string $directorioTemporal): ?string
    {
        if (!is_file($rutaOrigen)) {
            return null;
        }

        $extension = strtolower(pathinfo($rutaOrigen, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $rutaOrigen;
        }

        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'jfif'], true)) {
            return null;
        }

        $rutaTemporalPdf = rtrim($directorioTemporal, '/\\') . DIRECTORY_SEPARATOR . 'tmp_pdf_' . uniqid('', true) . '.pdf';
        return $this->convertirImagenAPdf($rutaOrigen, $rutaTemporalPdf) ? $rutaTemporalPdf : null;
    }

    private function generarPdfDesdeArchivosSubidos(array $archivo, string $rutaDestino, string $directorioTemporal): bool
    {
        $nombres = $archivo['name'] ?? null;
        $temporales = $archivo['tmp_name'] ?? null;
        $errores = $archivo['error'] ?? null;

        if (is_array($nombres) && is_array($temporales) && is_array($errores)) {
            $rutasPdf = [];
            $temporalesGenerados = [];
            $cantidadEsperada = 0;

            foreach ($nombres as $indice => $nombreOriginal) {
                $error = intval($errores[$indice] ?? UPLOAD_ERR_NO_FILE);
                $rutaTemporal = trim((string)($temporales[$indice] ?? ''));
                if ($error !== UPLOAD_ERR_OK || $rutaTemporal === '' || !is_file($rutaTemporal)) {
                    continue;
                }

                $extension = strtolower(pathinfo((string)$nombreOriginal, PATHINFO_EXTENSION));
                $archivoValidar = [
                    'name' => $nombreOriginal,
                    'type' => $archivo['type'][$indice] ?? '',
                    'tmp_name' => $rutaTemporal,
                    'error' => $error,
                    'size' => $archivo['size'][$indice] ?? 0,
                ];
                if (!scantecValidarUpload($archivoValidar, ['pdf', 'jpg', 'jpeg', 'png', 'jfif'], ['application/pdf', 'image/jpeg', 'image/png'], 50 * 1024 * 1024)) {
                    continue;
                }
                $cantidadEsperada++;

                if ($extension === 'pdf') {
                    $rutaPdf = rtrim($directorioTemporal, '/\\') . DIRECTORY_SEPARATOR . 'tmp_upload_' . uniqid('', true) . '.pdf';
                    if (!@move_uploaded_file($rutaTemporal, $rutaPdf)) {
                        if (!@copy($rutaTemporal, $rutaPdf)) {
                            continue;
                        }
                    }
                    $rutasPdf[] = $rutaPdf;
                    $temporalesGenerados[] = $rutaPdf;
                    continue;
                }

                $rutaPdf = rtrim($directorioTemporal, '/\\') . DIRECTORY_SEPARATOR . 'tmp_upload_' . uniqid('', true) . '.pdf';
                if ($this->convertirImagenAPdf($rutaTemporal, $rutaPdf, $extension)) {
                    $rutasPdf[] = $rutaPdf;
                    $temporalesGenerados[] = $rutaPdf;
                }
            }

            if ($cantidadEsperada === 0 || count($rutasPdf) !== $cantidadEsperada) {
                foreach ($temporalesGenerados as $temporal) {
                    if (is_file($temporal)) {
                        @unlink($temporal);
                    }
                }
                return false;
            }

            if (count($rutasPdf) === 1) {
                $ok = @copy($rutasPdf[0], $rutaDestino);
            } else {
                $ok = $this->unirPdfsSimple($rutasPdf, $rutaDestino);
            }

            foreach ($temporalesGenerados as $temporal) {
                if (is_file($temporal)) {
                    @unlink($temporal);
                }
            }

            return $ok && is_file($rutaDestino) && filesize($rutaDestino) > 0;
        }

        $error = intval($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        $rutaTemporal = trim((string)($archivo['tmp_name'] ?? ''));
        if ($error !== UPLOAD_ERR_OK || $rutaTemporal === '') {
            return false;
        }

        $extension = strtolower(pathinfo((string)($archivo['name'] ?? ''), PATHINFO_EXTENSION));
        if (!scantecValidarUpload($archivo, ['pdf', 'jpg', 'jpeg', 'png', 'jfif'], ['application/pdf', 'image/jpeg', 'image/png'], 50 * 1024 * 1024)) {
            return false;
        }

        if ($extension === 'pdf') {
            return move_uploaded_file($rutaTemporal, $rutaDestino);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'jfif'], true)) {
            return $this->convertirImagenAPdf($rutaTemporal, $rutaDestino, $extension);
        }

        return false;
    }

    private function unirSegunPolitica(string $politicaActualizacion, string $rutaFisicaNueva, string $rutaFisicaExistente, string $rutaBaseLegajos, int $idLegajo): ?string
    {
        if (!is_file($rutaFisicaNueva) || !is_file($rutaFisicaExistente)) {
            return null;
        }

        $nombreArchivoBase = pathinfo($rutaFisicaNueva, PATHINFO_FILENAME);
        $nombreArchivoUnido = $nombreArchivoBase . '_unido.pdf';
        $rutaFisicaUnida = $rutaBaseLegajos . $nombreArchivoUnido;
        $rutasOrigen = $politicaActualizacion === 'UNIR_AL_FINAL'
            ? [$rutaFisicaExistente, $rutaFisicaNueva]
            : [$rutaFisicaNueva, $rutaFisicaExistente];

        $rutas = [];
        $temporales = [];
        foreach ($rutasOrigen as $rutaOrigen) {
            $rutaPdfNormalizada = $this->normalizarArchivoAPdf($rutaOrigen, $rutaBaseLegajos);
            if ($rutaPdfNormalizada === null) {
                foreach ($temporales as $temporal) {
                    if (is_file($temporal)) {
                        @unlink($temporal);
                    }
                }
                return null;
            }
            if ($rutaPdfNormalizada !== $rutaOrigen) {
                $temporales[] = $rutaPdfNormalizada;
            }
            $rutas[] = $rutaPdfNormalizada;
        }

        $unionExitosa = $this->unirPdfsSimple($rutas, $rutaFisicaUnida);
        foreach ($temporales as $temporal) {
            if (is_file($temporal)) {
                @unlink($temporal);
            }
        }
        if (!$unionExitosa) {
            return null;
        }

        if (is_file($rutaFisicaNueva)) {
            @unlink($rutaFisicaNueva);
        }
        if (strpos(str_replace('\\', '/', $rutaFisicaExistente), 'Legajos/' . $idLegajo . '/') !== false && is_file($rutaFisicaExistente)) {
            @unlink($rutaFisicaExistente);
        }

        return 'Legajos/' . $idLegajo . '/' . $nombreArchivoUnido;
    }

    private function calcularFechaVencimiento(?string $fechaExpedicion, array $regla): ?string
    {
        $fechaExpedicion = trim((string)$fechaExpedicion);
        $tieneVencimiento = !empty($regla['tiene_vencimiento']);
        $diasVigenciaBase = intval($regla['dias_vigencia_base'] ?? 0);

        if (!$tieneVencimiento || $fechaExpedicion === '' || $diasVigenciaBase <= 0) {
            return null;
        }

        $fecha = DateTime::createFromFormat('Y-m-d', $fechaExpedicion);
        if (!$fecha) {
            return null;
        }

        $fecha->modify('+' . $diasVigenciaBase . ' years');
        return $fecha->format('Y-m-d');
    }

    private function requiereFechaExpedicion(array $regla): bool
    {
        return $this->esCampoDocumento($regla) && !empty($regla['tiene_vencimiento']);
    }

    private function resolverEstadoDocumentoPorRegla(array $regla, ?string $rutaArchivo, ?string $fechaVencimiento, ?string $valorCampo = null): string
    {
        if (!$this->esCampoDocumento($regla)) {
            return $this->campoValorCompleto($regla, $valorCampo) ? 'cargado' : 'pendiente';
        }

        $rutaArchivo = trim((string)$rutaArchivo);
        $fechaVencimiento = trim((string)$fechaVencimiento);

        if ($rutaArchivo === '') {
            return 'pendiente';
        }

        if ($this->requiereFechaExpedicion($regla) && $fechaVencimiento === '') {
            return 'pendiente';
        }

        return $this->resolverEstadoDocumento($rutaArchivo, $fechaVencimiento);
    }

    private function resolverEstadoDocumento(?string $rutaArchivo, ?string $fechaVencimiento): string
    {
        $rutaArchivo = trim((string)$rutaArchivo);
        if ($rutaArchivo === '') {
            return 'pendiente';
        }

        $fechaVencimiento = trim((string)$fechaVencimiento);
        if ($fechaVencimiento !== '') {
            $hoy = new DateTime('today');
            $fecha = DateTime::createFromFormat('Y-m-d', $fechaVencimiento);
            if (!$fecha) {
                $fecha = DateTime::createFromFormat('Y-m-d H:i:s', $fechaVencimiento);
            }

            if ($fecha instanceof DateTime) {
                $fecha->setTime(0, 0, 0);
                if ($fecha < $hoy) {
                    return 'vencido';
                }
                $limite = (clone $hoy)->modify('+30 days');
                if ($fecha <= $limite) {
                    return 'por_vencer';
                }
            }
        }

        return 'cargado';
    }

    private function resolverEstadoLegajo(array $matriz, array $legajoDocumentos): string
    {
        if (empty($matriz)) {
            return 'borrador';
        }

        $reglasPorRequisito = [];
        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito > 0) {
                $reglasPorRequisito[$idRequisito] = $regla;
            }
        }

        $documentosPorRequisito = [];
        foreach ($legajoDocumentos as $documento) {
            $idRequisito = intval($documento['id_requisito'] ?? 0);
            if ($idRequisito <= 0) {
                continue;
            }

            $reglaDocumento = $reglasPorRequisito[$idRequisito] ?? [];
            $documento['estado'] = $this->resolverEstadoDocumentoPorRegla(
                $reglaDocumento,
                $documento['ruta_archivo'] ?? '',
                $documento['fecha_vencimiento'] ?? null,
                $documento['valor_campo'] ?? null
            );
            $documentosPorRequisito[$idRequisito] = $documento;
        }

        foreach ($documentosPorRequisito as $documento) {
            if (($documento['estado'] ?? 'pendiente') === 'vencido') {
                return 'activo';
            }
        }

        foreach ($matriz as $regla) {
            if (empty($regla['es_obligatorio'])) {
                continue;
            }

            $idRequisito = intval($regla['id_requisito'] ?? 0);
            $estado = $documentosPorRequisito[$idRequisito]['estado'] ?? 'pendiente';
            if ($estado === 'pendiente' || $estado === '') {
                return 'borrador';
            }
        }

        return $this->model->soportaEstadoLegajo('completado') ? 'completado' : 'finalizado';
    }

    private function recalcularEstadoLegajoActual(int $idLegajo): ?string
    {
        if ($idLegajo <= 0) {
            return null;
        }

        $legajo = $this->model->selectLegajoPorId($idLegajo);
        if (empty($legajo)) {
            return null;
        }

        $estadoActual = strtolower(trim((string)($legajo['estado'] ?? '')));
        if (in_array($estadoActual, ['verificado', 'cerrado', 'aprobado', 'verificacion_rechazada'], true)) {
            return $estadoActual;
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($legajo['id_tipo_legajo'] ?? 0));
        $legajoDocumentos = $this->model->selectLegajoDocumentosPorLegajo($idLegajo);
        $estadoCalculado = $this->resolverEstadoLegajo($matriz, $legajoDocumentos);

        if ($estadoActual === 'generado' && in_array($estadoCalculado, ['completado', 'finalizado'], true) && $this->legajoTienePdfFinalValido($idLegajo)) {
            return 'generado';
        }

        if ($estadoCalculado !== $estadoActual && $this->model->soportaEstadoLegajo($estadoCalculado)) {
            $this->model->actualizarEstadoLegajo($idLegajo, $estadoCalculado, false);
        }

        return $estadoCalculado;
    }

    private function normalizarResultadosVerificacion(array $resultados, string $estadoFiltro = ''): array
    {
        $estadoFiltro = trim($estadoFiltro);
        $normalizados = [];

        foreach ($resultados as $resultado) {
            $idLegajo = intval($resultado['id_legajo'] ?? 0);
            if ($idLegajo <= 0) {
                continue;
            }

            $estadoRecalculado = $this->recalcularEstadoLegajoActual($idLegajo);
            if ($estadoRecalculado === null) {
                continue;
            }

            $resultado['estado'] = $estadoRecalculado;
            $resultado['estado_legajo_texto'] = $this->obtenerTextoEstadoLegajo($estadoRecalculado);

            if ($estadoFiltro !== '') {
                $estadoFiltroNormalizado = mb_strtolower($estadoFiltro, 'UTF-8');
                if ($estadoFiltroNormalizado === 'generado') {
                    if ($estadoRecalculado !== 'generado') {
                        continue;
                    }
                } elseif ($estadoFiltroNormalizado === 'completado') {
                    if (!in_array($estadoRecalculado, ['completado', 'finalizado'], true)) {
                        continue;
                    }
                } elseif (strcasecmp($resultado['estado_legajo_texto'], $estadoFiltro) !== 0) {
                    continue;
                }
            }

            $normalizados[] = $resultado;
        }

        return $normalizados;
    }

    private function obtenerTextoEstadoLegajo(?string $estado): string
    {
        $estado = strtolower(trim((string)$estado));
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

    private function resolverEstadoVisualBusquedaLegajo(array $resultado): string
    {
        $estadoCrudo = strtolower(trim((string)($resultado['estado'] ?? '')));
        if ($estadoCrudo === 'generado') {
            return 'Generado';
        }
        if ($estadoCrudo === 'completado' || $estadoCrudo === 'finalizado') {
            return 'Completado';
        }

        return trim((string)($resultado['estado_legajo_texto'] ?? $this->obtenerTextoEstadoLegajo($estadoCrudo)));
    }

    private function agregarCaratulaLegajo($pdf, array $legajo, array $reglas, int $cantidadDocumentosCargados, string $usuarioGenerador): void
    {
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        $this->aplicarMarcaAguaSiCorresponde($pdf, intval($legajo['id_tipo_legajo'] ?? 0), true);

        $rutaLogoReducido = $this->obtenerRutaLogoReducidoEmpresa();
        if (!empty($rutaLogoReducido)) {
            try {
                $pdf->Image($rutaLogoReducido, 15, 10, 28);
            } catch (Throwable $e) {
            }
        }

        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(24, 37, 65);
        $titulo = 'LEGAJO';
        $tipoLegajo = trim((string)($legajo['nombre_tipo_legajo'] ?? ''));
        if ($tipoLegajo !== '') {
            $titulo .= ' - ' . $tipoLegajo;
        }
        $pdf->Cell(0, 12, $this->pdfText($titulo), 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 6, $this->pdfText('Generación: ' . date('d/m/Y H:i')), 0, 1, 'R');
        $pdf->Cell(0, 6, $this->pdfText('Generado por: ' . trim($usuarioGenerador)), 0, 1, 'R');
        $estadoLegajo = strtolower(trim((string)($legajo['estado'] ?? '')));
        $fechaVerificacion = trim((string)($legajo['fecha_cierre'] ?? ''));
        $verificadoPor = '';
        if ($estadoLegajo === 'verificado' || $estadoLegajo === 'cerrado') {
            $verificadoPor = trim((string)($legajo['nombre_usuario_armado'] ?? $usuarioGenerador));
        }
        if ($fechaVerificacion !== '' && $verificadoPor === '') {
            $verificadoPor = trim((string)($legajo['nombre_usuario_armado'] ?? $legajo['nombre_usuario_creador'] ?? $usuarioGenerador));
        }
        $pdf->Cell(0, 6, $this->pdfText('Verificación: ' . ($fechaVerificacion !== '' ? date('d/m/Y H:i', strtotime($fechaVerificacion)) : '---')), 0, 1, 'R');
        $pdf->Cell(0, 6, $this->pdfText('Verificado por: ' . ($verificadoPor !== '' ? $verificadoPor : '---')), 0, 1, 'R');

        $pdf->SetDrawColor(210, 210, 210);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->Rect(15, 48, 180, 42, 'D');

        $x = 20;
        $y = 54;
        $labelW = 34;
        $valueWLeft = 64;
        $labelWRight = 32;
        $valueWRight = 35;
        $rowH = 8;

        $pdf->SetXY($x, $y);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, $rowH, 'Titular:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWLeft, $rowH, $this->pdfText(substr($legajo['nombre_completo'] ?? '', 0, 34)), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelWRight, $rowH, 'Solicitud:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWRight, $rowH, $this->pdfText(substr((string)($legajo['nro_solicitud'] ?? ''), 0, 18)), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, $rowH, 'CI:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWLeft, $rowH, $this->pdfText(substr((string)($legajo['ci_socio'] ?? ''), 0, 25)), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelWRight, $rowH, 'Estado:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWRight, $rowH, $this->pdfText($this->obtenerTextoEstadoLegajo($legajo['estado'] ?? '')), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, $rowH, 'Tipo de legajo:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(131, $rowH, $this->pdfText(substr($legajo['nombre_tipo_legajo'] ?? '', 0, 55)), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, $rowH, 'Documentos cargados:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(18, $rowH, strval($cantidadDocumentosCargados), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, $rowH, 'Listado total:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(18, $rowH, strval(count($reglas)), 0, 1);

        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(24, 37, 65);
        $pdf->Cell(0, 8, $this->pdfText('Listado de verificación manual'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->MultiCell(0, 6, $this->pdfText('Marque manualmente cada documento verificado en la revision fisica del legajo.'));
        $pdf->Ln(2);

        $pdf->SetFillColor(240, 244, 248);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetX(15);
        $pdf->Cell(22, 8, 'Verificado', 1, 0, 'C', true);
        $pdf->Cell(88, 8, 'Documento', 1, 0, 'L', true);
        $pdf->Cell(35, 8, 'Rol', 1, 0, 'L', true);
        $pdf->Cell(35, 8, 'Tipo', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 9);
        foreach ($reglas as $regla) {
            $tipo = !empty($regla['es_obligatorio']) ? 'Obligatorio' : 'Opcional';
            $yAntes = $pdf->GetY();
            if ($yAntes > 265) {
                $pdf->AddPage('P', 'A4');
            }
            $pdf->SetX(15);
            $xCelda = $pdf->GetX();
            $yCelda = $pdf->GetY();
            $pdf->Cell(22, 8, '', 1, 0, 'C');
            $pdf->Rect($xCelda + 8, $yCelda + 2, 4, 4);
            $pdf->Cell(88, 8, $this->pdfText($regla['documento_nombre'] ?? ''), 1, 0, 'L');
            $pdf->Cell(35, 8, $this->pdfText($regla['rol_vinculado'] ?? ''), 1, 0, 'L');
            $pdf->Cell(35, 8, $tipo, 1, 1, 'L');
        }

        $pdf->SetY(248);
        $pdf->SetDrawColor(120, 120, 120);
        $pdf->Line(115, 260, 190, 260);
        $pdf->SetXY(115, 262);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell(75, 6, $this->pdfText('Verificado por:'), 0, 1, 'L');
    }


    /**
     * Genera la segunda hoja del PDF del legajo con los campos no-documento
     * (tipo texto, lista y casilla). Muestra TODOS, incluso sin valor (aparecen con "-").
     * Diseño de 2 columnas en tarjetas con título en negrita y valor debajo.
     */
    /**
     * Genera un PDF individual para un campo que no es un documento (texto, lista, casilla)
     * y devuelve la ruta relativa para ser guardada en la base de datos.
     */
        /**
     * Genera un PDF temporal en disco con los campos no-documento (texto, lista, casilla)
     * y devuelve la ruta del archivo generado, o null si no hay campos.
     */
    private function generarPdfCamposNoDocumento(array $legajo, array $camposNoDoc, string $dirDestino, string $nombreArchivo): ?string
    {
        if (empty($camposNoDoc)) {
            return null;
        }

        $pdf = $this->crearInstanciaPdf();
        $pdf->SetTitle('Datos Adicionales', true);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage('P', 'A4');

        $this->aplicarMarcaAguaSiCorresponde($pdf, intval($legajo['id_tipo_legajo'] ?? 0), true);

        // â”€â”€ Encabezado â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $rutaLogoReducido = $this->obtenerRutaLogoReducidoEmpresa();
        if (!empty($rutaLogoReducido)) {
            try { $pdf->Image($rutaLogoReducido, 15, 10, 22); } catch (Throwable $e) {}
        }

        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor(24, 37, 65);
        $pdf->Cell(0, 10, $this->pdfText('DATOS ADICIONALES DEL LEGAJO'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $titular = trim((string)($legajo['nombre_completo'] ?? '-'));
        $ci      = trim((string)($legajo['ci_socio'] ?? '-'));
        $pdf->Cell(0, 5, $this->pdfText("Titular: $titular   |   CI: $ci"), 0, 1, 'C');

        $pdf->SetDrawColor(200, 210, 225);
        $pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
        $pdf->Ln(7);

        // â”€â”€ Tarjetas en 1 columna â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        $colW        = 180;   // ancho total (210 - 2*15)
        $cardPad     = 4;     // padding interno
        $titleH      = 7;     // altura del encabezado de tarjeta
        $minValueH   = 8;     // altura mínima del valor
        $marginLeft  = 15;

        $xCard    = $marginLeft;
        $rowTopY  = $pdf->GetY();

        foreach ($camposNoDoc as $campo) {
            $nombreCampo = $this->pdfText(trim((string)($campo['nombre'] ?? $campo['documento_nombre'] ?? $campo['campo_nombre'] ?? 'Campo' )));
            $tipoCampo   = strtolower(trim((string)($campo['tipo_campo'] ?? 'texto')));
            $valorRaw    = trim((string)($campo['valor_campo'] ?? ''));

            // Formatear valor según tipo
            if ($tipoCampo === 'casilla') {
                $valorMostrar = ($valorRaw === '1') ? $this->pdfText('Si (marcado)') : $this->pdfText('No (sin marcar)');
            } elseif ($valorRaw === '') {
                $valorMostrar = '-';
            } else {
                $valorMostrar = $this->pdfText($valorRaw);
            }

            // Calcular altura del valor (MultiCell puede necesitar varias líneas)
            $pdf->SetFont('Arial', '', 9);
            $lineas      = $pdf->GetStringWidth($valorMostrar) > ($colW - $cardPad * 2)
                           ? ceil($pdf->GetStringWidth($valorMostrar) / ($colW - $cardPad * 2 - 1))
                           : 1;
            $valueH      = max($minValueH, $lineas * 5 + 3);
            $cardH       = $titleH + $valueH + $cardPad;

            // Salto de página si no cabe
            if ($rowTopY + $cardH > 270) {
                $pdf->AddPage('P', 'A4');
                $this->aplicarMarcaAguaSiCorresponde($pdf, intval($legajo['id_tipo_legajo'] ?? 0), true);
                $rowTopY = $pdf->GetY();
            }

            // Fondo de la tarjeta
            $pdf->SetFillColor(245, 248, 252);
            $pdf->SetDrawColor(210, 220, 235);
            $pdf->Rect($xCard, $rowTopY, $colW, $cardH, 'DF');

            // Encabezado de tarjeta (fondo azul oscuro)
            $pdf->SetFillColor(24, 37, 65);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY($xCard, $rowTopY);
            $pdf->Cell($colW, $titleH, $nombreCampo, 0, 0, 'L', true);

            // Valor
            $pdf->SetTextColor(40, 40, 40);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY($xCard + $cardPad, $rowTopY + $titleH + 2);
            $pdf->MultiCell($colW - $cardPad * 2, 5, $valorMostrar, 0, 'L');

            $rowTopY += $cardH + 5;
        }

        $pdf->SetY($rowTopY);

        $pdf->SetTextColor(0, 0, 0);

        // Guardar como PDF temporal
        $rutaFinal = $dirDestino . $nombreArchivo;
        try {
            $pdf->Output('F', $rutaFinal);
            return file_exists($rutaFinal) ? $rutaFinal : null;
        } catch (Throwable $e) {
            return null;
        }
    }
    private function unirArchivosLegajo(array $documentos, string $rutaDestino, string $titulo, string $usuario, array $legajo = [], array $reglas = [], array $camposNoDoc = []): bool
    {
        $pdf = $this->crearInstanciaPdf();
        $pdf->SetTitle($titulo, true);
        $pdf->SetAuthor($usuario, true);
        $pdf->SetCreator('Scantec', true);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);

        $agregoPaginas = false;
        $tempDir = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Temp' . DIRECTORY_SEPARATOR;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Generar PDF temporal de campos no-documento e insertar en lista
        $rutaBaseLegajo = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . intval($legajo['id_legajo'] ?? 0) . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajo)) {
            @mkdir($rutaBaseLegajo, 0777, true);
        }
        
        $ciL = $legajo['ci_socio'] ?? '';
        $solL = $legajo['nro_solicitud'] ?? '';
        $cedulaL = preg_replace('/[^0-9]+/', '', $ciL);
        if ($cedulaL === '') $cedulaL = 'SINCI';
        $solLObj = preg_replace('/[^0-9A-Za-z]+/', '', $solL);
        $nombreAdicional = 'DA_TITULAR_' . $cedulaL . ($solLObj !== '' ? '_' . $solLObj : '') . '.pdf';

        $pdfCamposTempPath = $this->generarPdfCamposNoDocumento($legajo, $camposNoDoc, $rutaBaseLegajo, $nombreAdicional);
        $documentosConCampos = $documentos;
        if ($pdfCamposTempPath !== null) {
            // Insertar al inicio (después de carátula, antes de documentos adjuntos)
            array_unshift($documentosConCampos, ['ruta_archivo' => $pdfCamposTempPath, '_es_temp' => true]);
        }

        $this->agregarCaratulaLegajo($pdf, $legajo, $reglas, count($documentos), $usuario);
        $agregoPaginas = true;

        foreach ($documentosConCampos as $documento) {
            $rutaRelativa = trim((string)($documento['ruta_archivo'] ?? ''));
            // Si es un archivo temporal, la ruta ya es absoluta
            if (!empty($documento['_es_temp'])) {
                $rutaAbsoluta = $rutaRelativa;
            } elseif ($rutaRelativa === '') {
                continue;
            } else {
                $rutaAbsoluta = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);
            }

            if (!file_exists($rutaAbsoluta)) {
                continue;
            }

            $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));

            try {
                if ($extension === 'pdf') {
                    if ($this->agregarPdfAlLegajoConFallback($pdf, $rutaAbsoluta, $tempDir, intval($legajo['id_tipo_legajo'] ?? 0))) {
                        $agregoPaginas = true;
                    }
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'jfif'], true)) {
                    $tempJpg = $tempDir . 'legajo_' . uniqid() . '.jpg';
                    $ok = false;

                    if ($extension === 'png' && function_exists('imagecreatefrompng')) {
                        $src = @imagecreatefrompng($rutaAbsoluta);
                        if ($src) {
                            $w = imagesx($src);
                            $h = imagesy($src);
                            $dst = imagecreatetruecolor($w, $h);
                            $white = imagecolorallocate($dst, 255, 255, 255);
                            imagefill($dst, 0, 0, $white);
                            imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
                            imagejpeg($dst, $tempJpg, 90);
                            $ok = true;
                        }
                    } elseif (in_array($extension, ['jpg', 'jpeg', 'jfif'], true) && function_exists('imagecreatefromjpeg')) {
                        $src = @imagecreatefromjpeg($rutaAbsoluta);
                        if ($src) {
                            imagejpeg($src, $tempJpg, 90);
                            $ok = true;
                        }
                    }

                    if (!$ok && copy($rutaAbsoluta, $tempJpg)) {
                        $ok = true;
                    }

                    if ($ok && file_exists($tempJpg)) {
                        $tamanoTemp = @getimagesize($tempJpg) ?: null;
                        if ($this->agregarImagenEnHojaA4($pdf, $tempJpg, 'JPEG', $tamanoTemp)) {
                            $this->aplicarSelloSiCorresponde($pdf, intval($legajo['id_tipo_legajo'] ?? 0));
                            $agregoPaginas = true;
                        }
                        unlink($tempJpg);
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        if (!$agregoPaginas) {
            return false;
        }

        $pdf->Output('F', $rutaDestino);
        $archivoGenerado = file_exists($rutaDestino);
        if ($archivoGenerado) {
            $this->sincronizarCantidadPaginasProcesadasLegajo($legajo, $rutaDestino);
        }

        return $archivoGenerado;
    }

    private function regenerarPdfFinalLegajo(int $idLegajo, string $usuario): bool
    {
        if ($idLegajo <= 0) {
            return false;
        }

        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $legajoActual = $this->model->selectLegajoPorId($idLegajo);
        if (empty($legajoActual)) {
            return false;
        }

        $documentosParaUnir = $this->model->obtenerDocumentosCargadosParaUnir($idLegajo);
        $camposNoDoc = $this->model->obtenerCamposNoDocumentoConValor($idLegajo);

        // Genera el PDF si hay documentos adjuntos O si hay campos no-documento configurados
        if (empty($documentosParaUnir) && empty($camposNoDoc)) {
            return false;
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($legajoActual['id_tipo_legajo'] ?? 0));
        $rutaBaseLegajos = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajos)) {
            mkdir($rutaBaseLegajos, 0777, true);
        }

        $nombreFinal = $this->construirNombrePdfFinalLegajo($legajoActual, $idLegajo);
        $rutaFinal = $rutaBaseLegajos . $nombreFinal;

        return $this->unirArchivosLegajo($documentosParaUnir ?? [], $rutaFinal, 'Legajo ' . $idLegajo, $usuario, $legajoActual, $matriz, $camposNoDoc);
    }

    private function eliminarDirectorioRecursivo(string $directorio): bool
    {
        if (!is_dir($directorio)) {
            return true;
        }

        $elementos = array_diff(scandir($directorio), ['.', '..']);
        foreach ($elementos as $elemento) {
            $ruta = $directorio . DIRECTORY_SEPARATOR . $elemento;
            if (is_dir($ruta)) {
                $this->eliminarDirectorioRecursivo($ruta);
            } else {
                @unlink($ruta);
            }
        }

        return @rmdir($directorio);
    }

    private function eliminarArchivosLegajo(int $idLegajo): bool
    {
        if ($idLegajo <= 0 || !defined('RUTA_BASE')) {
            return false;
        }

        $directorio = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo;
        if (!is_dir($directorio)) {
            return true;
        }

        return $this->eliminarDirectorioRecursivo($directorio);
    }

    private function fechaIsoValida(string $fecha): bool
    {
        $fecha = trim($fecha);
        if ($fecha === '') {
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d', $fecha);
        return $date instanceof DateTime && $date->format('Y-m-d') === $fecha;
    }

    private function resolverFiltrosDocumentosProcesados(): array
    {
        $desde = trim((string)($_GET['desde'] ?? ''));
        $hasta = trim((string)($_GET['hasta'] ?? ''));

        if (!$this->fechaIsoValida($desde)) {
            $desde = date('Y-m-01');
        }

        if (!$this->fechaIsoValida($hasta)) {
            $hasta = date('Y-m-t');
        }

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
        ];
    }

    private function pdfText(?string $texto): string
    {
        $texto = (string)$texto;

        if ($texto === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $texto);
            if ($convertido !== false) {
                return $convertido;
            }
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
        }

        return $texto;
    }

    private function obtenerModeloDocumentosProcesados(): LogsModel
    {
        if (!class_exists('LogsModel')) {
            require_once 'Models/LogsModel.php';
        }

        return new LogsModel();
    }

    private function asegurarAccesoFacturacion(): void
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
        header('Location: ' . base_url() . 'dashboard/dashboard_legajos');
        exit();
    }

    public function facturacion()
    {
        $this->asegurarAccesoFacturacion();
        $filtros = $this->resolverFiltrosDocumentosProcesados();

        $data = [
            'filtros' => $filtros,
            'totales' => $this->model->selectTotalesFacturacionLegajos($filtros['desde'], $filtros['hasta']),
            'resumen_fechas' => $this->model->selectResumenFacturacionLegajosPorFecha($filtros['desde'], $filtros['hasta']),
        ];

        $this->views->getView($this, "facturacion", $data);
    }

    public function facturacionPdf()
    {
        $this->asegurarAccesoFacturacion();
        $filtros = $this->resolverFiltrosDocumentosProcesados();
        $totales = $this->model->selectTotalesFacturacionLegajos($filtros['desde'], $filtros['hasta']);

        if (ob_get_length()) {
            ob_end_clean();
        }
        require_once 'Helpers/ReportTemplatePDF.php';

        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Facturacion de legajos', 'L', 'A4');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Reporte de Facturación de Legajos'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Período: ' . $filtros['desde'] . ' a ' . $filtros['hasta']), 0, 1, 'L');
        $pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Generado: ' . date('d/m/Y H:i')), 0, 1, 'L');
        $pdf->Ln(3);

        // Tarjetas de contadores
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Legajos con incrementos'), 0, 0, 'L', true);
        $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Legajos facturados'), 0, 0, 'L', true);
        $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Páginas facturables'), 0, 1, 'L', true);
        
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(60, 10, strval(intval($totales['total_movimientos'] ?? 0)), 0, 0, 'C', true);
        $pdf->Cell(60, 10, strval(intval($totales['total_legajos'] ?? 0)), 0, 0, 'C', true);
        $pdf->Cell(60, 10, strval(intval($totales['total_paginas'] ?? 0)), 0, 1, 'C', true);
        $pdf->Ln(5);

        $pdf->Output("Facturacion_Legajos.pdf", "I");
    }

    public function __construct()
    {
        // CORRECCIÓN: verificar antes de iniciar para evitar "session already started"
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO']) && !$this->esAccionPublicaFormularioExterno()) {
            header("location: " . base_url());
            exit(); // CORRECCIÓN: agregar exit() para detener la ejecución tras redirigir
        }
        parent::__construct();
    }

    /**
     * Verifica si el usuario actual tiene permiso de grupo para una acción de legajos.
     * Solo el rol 1 (Administrador del sistema) siempre tiene acceso total.
     */
    private function checkLegajoGroupPermission(string $accion)
    {
        if (intval($_SESSION['id_rol'] ?? 0) === 1) {
            return; // Administrador del sistema tiene acceso a todo
        }

        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }
            $segModel = new SeguridadLegajosModel();
            $tienePermiso = $segModel->tienePermisoLegajo(intval($_SESSION['id_rol'] ?? 0), $accion);
            
            if (!$tienePermiso) {
                setAlert('warning', 'Tu rol no tiene permisos para esta acción en legajos.');
                if (isset($this->model) && method_exists($this->model, 'bloquarPC_IP')) {
                    $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso denegado a legajo: ' . $accion);
                }
                header('Location: ' . base_url() . 'dashboard/dashboard_legajos');
                exit();
            }
        } catch (Throwable $e) {
            setAlert('error', 'Error al verificar permisos.');
            header('Location: ' . base_url() . 'dashboard/dashboard_legajos');
            exit();
        }
    }

    private function rolActualTienePermisoLegajo(string $accion): bool
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        if ($idRol === 1) {
            return true;
        }

        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }
            $segModel = new SeguridadLegajosModel();
            return $segModel->tienePermisoLegajo($idRol, $accion);
        } catch (Throwable $e) {
            return false;
        }
    }

    private function puedeAccederPdfLegajo(): bool
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        if ($idRol === 1) {
            return true;
        }

        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }

            $segModel = new SeguridadLegajosModel();
            foreach (['generar_pdf', 'armar_legajo', 'buscar_legajos', 'verificar_legajos', 'administrar_legajos'] as $accion) {
                if ($segModel->tienePermisoLegajo($idRol, $accion)) {
                    return true;
                }
            }
        } catch (Throwable $e) {
            return false;
        }

        return false;
    }

    private function usuarioDebeVerSoloLegajosPropios(): bool
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
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

    private function obtenerTiposLegajoPermitidosRolActual(): array
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
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

    private function rolActualTieneFiltroTiposLegajo(): bool
    {
        return intval($_SESSION['id_rol'] ?? 0) > 2;
    }

    private function asegurarAccesoLegajo(int $idLegajo, string $redirect = 'legajos/buscar_legajos'): void
    {
        if ($idLegajo <= 0) {
            return;
        }

        if ($this->usuarioDebeVerSoloLegajosPropios()) {
            $idUsuario = intval($_SESSION['id'] ?? 0);
            if ($idUsuario <= 0 || !$this->model->usuarioEsPropietarioLegajo($idLegajo, $idUsuario)) {
                if (function_exists('setAlert')) {
                    setAlert('warning', 'Tu rol solo puede visualizar legajos creados por tu propio usuario.');
                }
                header('Location: ' . base_url() . $redirect);
                exit();
            }
        }

        $legajo = $this->model->selectLegajoPorId($idLegajo);
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo() && !in_array(intval($legajo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true)) {
            if (function_exists('setAlert')) {
                setAlert('warning', 'Tu rol no tiene acceso a este tipo de legajo.');
            }
            header('Location: ' . base_url() . $redirect);
            exit();
        }
    }

    private function puedeAccederLegajoSinRedireccion(int $idLegajo): bool
    {
        if ($idLegajo <= 0) {
            return false;
        }

        if ($this->usuarioDebeVerSoloLegajosPropios()) {
            $idUsuario = intval($_SESSION['id'] ?? 0);
            if ($idUsuario <= 0 || !$this->model->usuarioEsPropietarioLegajo($idLegajo, $idUsuario)) {
                return false;
            }
        }

        $legajo = $this->model->selectLegajoPorId($idLegajo);
        if (empty($legajo)) {
            return false;
        }

        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo() && !in_array(intval($legajo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true)) {
            return false;
        }

        return true;
    }

    private function obtenerFiltrosBusquedaLegajosDesdeRequest(array $fuente): array
    {
        return [
            'termino' => trim((string)($fuente['termino'] ?? '')),
            'estado_legajo' => trim((string)($fuente['estado_legajo'] ?? '')),
            'id_tipo_legajo' => intval($fuente['id_tipo_legajo'] ?? 0),
            'filtro_documentos' => trim((string)($fuente['filtro_documentos'] ?? '')),
        ];
    }

    private function construirUrlLotesLegajos(array $filtros = []): string
    {
        return $this->construirUrlListadoLegajos('legajos/lotes_legajos', $filtros);
    }

    private function construirUrlAdministrarLegajos(array $filtros = []): string
    {
        return $this->construirUrlListadoLegajos('legajos/administrar_legajos', $filtros);
    }

    private function construirUrlListadoLegajos(string $ruta, array $filtros = []): string
    {
        $query = [];
        $termino = trim((string)($filtros['termino'] ?? ''));
        $estadoLegajo = trim((string)($filtros['estado_legajo'] ?? ''));
        $idTipoLegajo = intval($filtros['id_tipo_legajo'] ?? 0);
        $filtroDocumentos = trim((string)($filtros['filtro_documentos'] ?? ''));

        if ($termino !== '') {
            $query['termino'] = $termino;
        }
        if ($estadoLegajo !== '') {
            $query['estado_legajo'] = $estadoLegajo;
        }
        if ($idTipoLegajo > 0) {
            $query['id_tipo_legajo'] = $idTipoLegajo;
        }
        if ($filtroDocumentos !== '') {
            $query['filtro_documentos'] = $filtroDocumentos;
        }

        $url = base_url() . ltrim($ruta, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private function obtenerLegajosSeleccionadosDesdePost(): array
    {
        $ids = $_POST['legajos'] ?? [];
        if (!is_array($ids)) {
            return [];
        }

        $ids = array_map('intval', $ids);
        $ids = array_values(array_filter(array_unique($ids), static function ($id) {
            return $id > 0;
        }));

        return $ids;
    }

    public function armar_legajo()
    {
        $this->checkLegajoGroupPermission('armar_legajo');
        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }
        $matriz_legajo = $this->model->selectMatrizTiposLegajo();
        $id_legajo = intval($_GET['id_legajo'] ?? 0);
        $duplicar_desde = intval($_GET['duplicar_desde'] ?? 0);
        $buscar_legajo = trim($_GET['buscar_legajo'] ?? '');
        $soloPropios = $this->usuarioDebeVerSoloLegajosPropios();
        $idUsuarioActual = intval($_SESSION['id'] ?? 0);
        $resultados_busqueda_legajo = $buscar_legajo !== ''
            ? $this->model->buscarLegajosPorTermino($buscar_legajo, '', 0, '', $idUsuarioActual, $soloPropios, $tiposPermitidos)
            : [];
        $legajo = [];
        $legajo_documentos = [];
        $pdf_final_listo = null;
        $flashFormulario = $this->consumirFlashFormularioLegajo();
        $fuentePersonas = $this->obtenerFuentePersonasLegajo();
        $personasModuloActivo = $fuentePersonas !== 'ninguna';
        $personasActivas = $fuentePersonas === 'externa'
            ? $this->obtenerPersonasExternasActivas()
            : ($personasModuloActivo ? $this->obtenerPersonasActivas() : []);

        if ($id_legajo > 0) {
            $this->asegurarAccesoLegajo($id_legajo, 'legajos/armar_legajo');
            $legajo = $this->model->selectLegajoPorId($id_legajo);
            $legajo_documentos = $this->model->selectLegajoDocumentosPorLegajo($id_legajo);
            $matrizActual = !empty($legajo)
                ? $this->model->obtenerMatrizLegajoPorTipo(intval($legajo['id_tipo_legajo'] ?? 0))
                : [];
            $reglasPorRequisito = [];
            foreach ($matrizActual as $reglaMatriz) {
                $idRequisitoMatriz = intval($reglaMatriz['id_requisito'] ?? 0);
                if ($idRequisitoMatriz > 0) {
                    $reglasPorRequisito[$idRequisitoMatriz] = $reglaMatriz;
                }
            }
            foreach ($legajo_documentos as &$legajo_documento) {
                $reglaDocumento = $reglasPorRequisito[intval($legajo_documento['id_requisito'] ?? 0)] ?? [];
                $legajo_documento['estado'] = $this->resolverEstadoDocumentoPorRegla(
                    $reglaDocumento,
                    $legajo_documento['ruta_archivo'] ?? '',
                    $legajo_documento['fecha_vencimiento'] ?? null,
                    $legajo_documento['valor_campo'] ?? null
                );
            }
            unset($legajo_documento);
            if (!empty($legajo) && !in_array(($legajo['estado'] ?? ''), ['aprobado', 'verificado', 'cerrado', 'verificacion_rechazada', 'generado'], true)) {
                $estadoLegajo = $this->resolverEstadoLegajo($matrizActual, $legajo_documentos);
                if (($legajo['estado'] ?? '') !== $estadoLegajo) {
                    $this->model->actualizarEstadoLegajo($id_legajo, $estadoLegajo, in_array($estadoLegajo, ['finalizado', 'cerrado', 'verificado'], true));
                    $legajo['estado'] = $estadoLegajo;
                }
            }
            $pdfFinalActual = $this->obtenerRutaPdfFinalLegajo($id_legajo);
            if (!empty($pdfFinalActual)) {
                $pdf_final_listo = [
                    'id_legajo' => $id_legajo,
                    'nombre_archivo' => $pdfFinalActual['nombre_archivo']
                ];
            }
        } elseif ($duplicar_desde > 0) {
            $this->asegurarAccesoLegajo($duplicar_desde, 'legajos/armar_legajo');
            $legajoOrigen = $this->model->selectLegajoPorId($duplicar_desde);
            if (!empty($legajoOrigen)) {
                $legajo = $legajoOrigen;
                $legajo['id_legajo'] = 0;
                $legajo['estado'] = 'borrador';
                $legajo['nro_solicitud'] = '';
                $legajo['fecha_creacion'] = null;
                $legajo['fecha_cierre'] = null;
                $legajo_documentos = $this->model->selectLegajoDocumentosPorLegajo($duplicar_desde);
                $matrizDuplicado = $this->model->obtenerMatrizLegajoPorTipo(intval($legajoOrigen['id_tipo_legajo'] ?? 0));
                $reglasPorRequisito = [];
                foreach ($matrizDuplicado as $reglaMatriz) {
                    $idRequisitoMatriz = intval($reglaMatriz['id_requisito'] ?? 0);
                    if ($idRequisitoMatriz > 0) {
                        $reglasPorRequisito[$idRequisitoMatriz] = $reglaMatriz;
                    }
                }
                foreach ($legajo_documentos as &$legajo_documento) {
                    $reglaDocumento = $reglasPorRequisito[intval($legajo_documento['id_requisito'] ?? 0)] ?? [];
                $legajo_documento['estado'] = $this->resolverEstadoDocumentoPorRegla(
                    $reglaDocumento,
                    $legajo_documento['ruta_archivo'] ?? '',
                    $legajo_documento['fecha_vencimiento'] ?? null,
                    $legajo_documento['valor_campo'] ?? null
                );
            }
                unset($legajo_documento);
            }
        }

        if (!empty($flashFormulario)) {
            if (!empty($flashFormulario['legajo']) && is_array($flashFormulario['legajo'])) {
                $legajo = array_merge($legajo, $flashFormulario['legajo']);
            }
            if (!empty($flashFormulario['duplicado_desde'])) {
                $duplicado_desde = intval($flashFormulario['duplicado_desde']);
            }
        }

        $formDocumentos = [];
        if (!empty($flashFormulario['documentos']) && is_array($flashFormulario['documentos'])) {
            foreach ($flashFormulario['documentos'] as $idRequisito => $documentoFlash) {
                $idRequisito = intval($idRequisito);
                if ($idRequisito > 0 && is_array($documentoFlash)) {
                    $formDocumentos[$idRequisito] = $documentoFlash;
                }
            }
        }

        if (!empty($_SESSION['legajo_pdf_final_listo'])) {
            $pdf_final_listo = $_SESSION['legajo_pdf_final_listo'];
            unset($_SESSION['legajo_pdf_final_listo']);
        }

        $data = [
            'tipos_legajo' => $tipos_legajo,
            'matriz_legajo' => $matriz_legajo,
            'legajo' => $legajo,
            'legajo_documentos' => $legajo_documentos,
            'form_documentos' => $formDocumentos,
            'pdf_final_listo' => $pdf_final_listo,
            'buscar_legajo' => $buscar_legajo,
            'resultados_busqueda_legajo' => $resultados_busqueda_legajo,
            'duplicar_desde' => $duplicar_desde,
            'personas_modulo_activo' => $personasModuloActivo,
            'personas_activas' => $personasActivas,
            'personas_fuente' => $fuentePersonas,
            'selector_persona_valor_actual' => $this->obtenerSelectorPersonaActual($legajo, $personasActivas, $fuentePersonas)
        ];
        $this->views->getView($this, "armar_legajo", $data);
    }

    public function buscar_legajos()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        $busquedaEjecutada = array_key_exists('termino', $_GET) || array_key_exists('estado_legajo', $_GET) || array_key_exists('id_tipo_legajo', $_GET) || array_key_exists('filtro_documentos', $_GET);
        $termino = trim($_GET['termino'] ?? '');
        $estado_legajo = trim($_GET['estado_legajo'] ?? '');
        $id_tipo_legajo = intval($_GET['id_tipo_legajo'] ?? 0);
        $filtro_documentos = trim($_GET['filtro_documentos'] ?? '');
        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }
        $soloPropios = $this->usuarioDebeVerSoloLegajosPropios();
        $idUsuarioActual = intval($_SESSION['id'] ?? 0);
        if ($busquedaEjecutada) {
            if ($estado_legajo === 'Proceso') {
                $terminoBusqueda = $termino !== '' ? $termino : '*.*';
                $resultados = $this->model->buscarLegajosPorTermino($terminoBusqueda, '', $id_tipo_legajo, $filtro_documentos, $idUsuarioActual, $soloPropios, $tiposPermitidos);
            } else {
                $resultados = $this->model->buscarLegajosPorTermino($termino, $estado_legajo, $id_tipo_legajo, $filtro_documentos, $idUsuarioActual, $soloPropios, $tiposPermitidos);
            }
        } else {
            $resultados = [];
        }
        $resultados = $this->enriquecerResultadosConPdfFinal($resultados);
        foreach ($resultados as &$resultado) {
            $resultado['estado_legajo_texto'] = $this->resolverEstadoVisualBusquedaLegajo($resultado);
        }
        unset($resultado);

        if ($estado_legajo === 'Proceso') {
            $resultados = array_values(array_filter($resultados, function ($resultado) {
                $estadoTexto = $this->resolverEstadoVisualBusquedaLegajo($resultado);
                return strcasecmp($estadoTexto, 'Incompleto') === 0
                    || strcasecmp($estadoTexto, 'Proceso') === 0;
            }));
        } elseif ($estado_legajo === 'Completado') {
            $resultados = array_values(array_filter($resultados, function ($resultado) {
                return strcasecmp($this->resolverEstadoVisualBusquedaLegajo($resultado), 'Completado') === 0;
            }));
        }

        $data = [
            'termino' => $termino,
            'resultados' => $resultados,
            'busqueda_ejecutada' => $busquedaEjecutada,
            'estado_legajo' => $estado_legajo,
            'id_tipo_legajo' => $id_tipo_legajo,
            'filtro_documentos' => $filtro_documentos,
            'tipos_legajo' => $tipos_legajo
        ];
        $this->views->getView($this, "buscar_legajos", $data);
    }

    public function documentos_procesados()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        $filtros = $this->resolverFiltrosDocumentosProcesados();
        $reporteModel = $this->obtenerModeloDocumentosProcesados();

        $data = [
            'filtros' => $filtros,
            'totales' => $reporteModel->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']),
            'resumen_periodos' => $reporteModel->selectResumenPaginasLegajosPorPeriodo($filtros['desde'], $filtros['hasta']),
            'detalle_legajos' => $reporteModel->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']),
        ];

        $this->views->getView($this, "documentos_procesados", $data);
    }

    public function documentos_procesadosPdf()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        $filtros = $this->resolverFiltrosDocumentosProcesados();
        $reporteModel = $this->obtenerModeloDocumentosProcesados();
        $totales = $reporteModel->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']);
        $detalle = $reporteModel->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']);

        if (ob_get_length()) {
            ob_end_clean();
        }
        require_once 'Helpers/ReportTemplatePDF.php';

        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Documentos procesados para facturacion', 'L', 'A4');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, $this->pdfText('Periodo facturable: ' . $filtros['desde'] . ' a ' . $filtros['hasta']), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 7, $this->pdfText('Legajos contabilizados: ' . intval($totales['total_legajos'] ?? 0)), 0, 0, 'L');
        $pdf->Cell(90, 7, $this->pdfText('Paginas procesadas: ' . intval($totales['total_paginas'] ?? 0)), 0, 0, 'L');
        $pdf->Cell(90, 7, $this->pdfText('Promedio por legajo: ' . number_format((float)($totales['promedio_paginas'] ?? 0), 2, ',', '.')), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell(18, 6, 'ID', 1, 0, 'C', true);
        $pdf->Cell(28, 6, $this->pdfText('Fecha'), 1, 0, 'C', true);
        $pdf->Cell(42, 6, $this->pdfText('Tipo'), 1, 0, 'C', true);
        $pdf->Cell(38, 6, $this->pdfText('CI'), 1, 0, 'C', true);
        $pdf->Cell(70, 6, $this->pdfText('Nombre'), 1, 0, 'C', true);
        $pdf->Cell(32, 6, $this->pdfText('Solicitud'), 1, 0, 'C', true);
        $pdf->Cell(20, 6, $this->pdfText('Paginas'), 1, 0, 'C', true);
        $pdf->Cell(26, 6, $this->pdfText('Estado'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 7);
        foreach ($detalle as $row) {
            $pdf->Cell(18, 6, strval($row['id_legajo']), 1, 0, 'C');
            $pdf->Cell(28, 6, $this->pdfText(substr((string)$row['fecha_creacion'], 0, 10)), 1, 0, 'C');
            $pdf->Cell(42, 6, $this->pdfText((string)($row['nombre_tipo_legajo'] ?? '')), 1, 0, 'L');
            $pdf->Cell(38, 6, $this->pdfText((string)($row['ci_socio'] ?? '')), 1, 0, 'L');
            $pdf->Cell(70, 6, $this->pdfText((string)($row['nombre_completo'] ?? '')), 1, 0, 'L');
            $pdf->Cell(32, 6, $this->pdfText((string)($row['nro_solicitud'] ?? '')), 1, 0, 'L');
            $pdf->Cell(20, 6, strval(intval($row['cantidad_paginas_procesadas'] ?? 0)), 1, 0, 'C');
            $pdf->Cell(26, 6, $this->pdfText((string)($row['estado'] ?? '')), 1, 1, 'C');
        }

        $pdf->Output("Documentos_Procesados.pdf", "I");
    }

    public function documentos_procesadosExcel()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        $filtros = $this->resolverFiltrosDocumentosProcesados();
        $reporteModel = $this->obtenerModeloDocumentosProcesados();
        $totales = $reporteModel->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']);
        $detalle = $reporteModel->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']);

        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $excel = new ReportTemplateExcel('Documentos procesados para facturacion', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A2', 'Periodo');
        $sheet->setCellValue('B2', $filtros['desde'] . ' a ' . $filtros['hasta']);
        $sheet->setCellValue('A3', 'Legajos contabilizados');
        $sheet->setCellValue('B3', intval($totales['total_legajos'] ?? 0));
        $sheet->setCellValue('C3', 'Paginas procesadas');
        $sheet->setCellValue('D3', intval($totales['total_paginas'] ?? 0));

        $sheet->setCellValue('A5', 'ID');
        $sheet->setCellValue('B5', 'Fecha');
        $sheet->setCellValue('C5', 'Tipo de legajo');
        $sheet->setCellValue('D5', 'CI');
        $sheet->setCellValue('E5', 'Nombre completo');
        $sheet->setCellValue('F5', 'Nro solicitud');
        $sheet->setCellValue('G5', 'Paginas procesadas');
        $sheet->setCellValue('H5', 'Estado');
        $sheet->setCellValue('I5', 'Usuario');
        $sheet->getStyle('A5:I5')->applyFromArray($headerStyle);

        $row = 6;
        foreach ($detalle as $value) {
            $sheet->setCellValue('A' . $row, intval($value['id_legajo'] ?? 0));
            $sheet->setCellValue('B' . $row, (string)($value['fecha_creacion'] ?? ''));
            $sheet->setCellValue('C' . $row, (string)($value['nombre_tipo_legajo'] ?? ''));
            $sheet->setCellValue('D' . $row, (string)($value['ci_socio'] ?? ''));
            $sheet->setCellValue('E' . $row, (string)($value['nombre_completo'] ?? ''));
            $sheet->setCellValue('F' . $row, (string)($value['nro_solicitud'] ?? ''));
            $sheet->setCellValue('G' . $row, intval($value['cantidad_paginas_procesadas'] ?? 0));
            $sheet->setCellValue('H' . $row, (string)($value['estado'] ?? ''));
            $sheet->setCellValue('I' . $row, (string)($value['usuario_responsable'] ?? ''));
            $row++;
        }

        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 20,
            'C' => 28,
            'D' => 18,
            'E' => 36,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 24,
        ]);

        $excel->output('Documentos_Procesados_' . date('Y_m_d_His'));
    }

    public function validar_solicitud_duplicada()
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $this->checkLegajoGroupPermission('armar_legajo');
            $nroSolicitud = trim((string)($_GET['nro_solicitud'] ?? ''));
            $idLegajo = intval($_GET['id_legajo'] ?? 0);

            if ($nroSolicitud === '') {
                echo json_encode(['ok' => true, 'duplicado' => false], JSON_UNESCAPED_UNICODE);
                exit();
            }

            $duplicado = $this->model->existeSolicitudDuplicada($nroSolicitud, $idLegajo);
            $respuesta = ['ok' => true, 'duplicado' => $duplicado];

            if ($duplicado) {
                $legajoExistente = $this->model->selectLegajoPorSolicitud($nroSolicitud, $idLegajo);
                $idLegajoExistente = intval($legajoExistente['id_legajo'] ?? 0);
                if ($idLegajoExistente > 0) {
                    $respuesta['id_legajo'] = $idLegajoExistente;
                    $respuesta['redirect_url'] = base_url() . 'legajos/armar_legajo?id_legajo=' . $idLegajoExistente;
                }
            }

            echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'duplicado' => false], JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    public function indice_busqueda()
    {
        $this->buscar_legajos();
    }

    public function verificar_legajos()
    {
        $this->checkLegajoGroupPermission('verificar_legajos');
        $termino = trim($_GET['termino'] ?? '');
        $busquedaEjecutada = array_key_exists('termino', $_GET)
            || array_key_exists('estado_legajo', $_GET)
            || array_key_exists('id_tipo_legajo', $_GET);
        $estado_legajo = $busquedaEjecutada
            ? trim($_GET['estado_legajo'] ?? '')
            : 'Generado';
        $id_tipo_legajo = intval($_GET['id_tipo_legajo'] ?? 0);
        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }
        $soloPropios = $this->usuarioDebeVerSoloLegajosPropios();
        $idUsuarioActual = intval($_SESSION['id'] ?? 0);
        $estadoFiltroSql = $estado_legajo === 'Generado' ? '' : $estado_legajo;
        $resultados = $this->model->buscarLegajosParaVerificar($termino, $estadoFiltroSql, $id_tipo_legajo, false, $idUsuarioActual, $soloPropios, $tiposPermitidos);
        $resultados = $this->normalizarResultadosVerificacion($resultados, $estado_legajo);
        $resultados = $this->enriquecerResultadosConPdfFinal($resultados);

        $data = [
            'termino' => $termino,
            'resultados' => $resultados,
            'estado_legajo' => $estado_legajo,
            'id_tipo_legajo' => $id_tipo_legajo,
            'tipos_legajo' => $tipos_legajo,
            'busqueda_ejecutada' => $busquedaEjecutada,
            'puede_gestionar_legajo' => $this->rolActualTienePermisoLegajo('verificar_legajos'),
        ];
        $this->views->getView($this, "verificar_legajos", $data);
    }

    public function administrar_legajos()
    {
        $this->checkLegajoGroupPermission('administrar_legajos');
        $termino = trim($_GET['termino'] ?? '');
        $estado_legajo = trim($_GET['estado_legajo'] ?? '');
        $id_tipo_legajo = intval($_GET['id_tipo_legajo'] ?? 0);
        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }
        $soloPropios = $this->usuarioDebeVerSoloLegajosPropios();
        $idUsuarioActual = intval($_SESSION['id'] ?? 0);
        $resultados = $this->model->buscarLegajosParaVerificar($termino, $estado_legajo, $id_tipo_legajo, false, $idUsuarioActual, $soloPropios, $tiposPermitidos);
        $resultados = $this->enriquecerResultadosConPdfFinal($resultados);

        $data = [
            'termino' => $termino,
            'resultados' => $resultados,
            'estado_legajo' => $estado_legajo,
            'id_tipo_legajo' => $id_tipo_legajo,
            'tipos_legajo' => $tipos_legajo,
            'busqueda_ejecutada' => true,
            'puede_administrar_legajo' => $this->rolActualTienePermisoLegajo('administrar_legajos'),
            'puede_eliminar_legajo' => $this->rolActualTienePermisoLegajo('eliminar_legajo'),
            'puede_rearmar_lote' => $this->rolActualTienePermisoLegajo('armar_legajo'),
            'puede_descargar_lote' => $this->puedeAccederPdfLegajo(),
        ];
        $this->views->getView($this, "administrar_legajos", $data);
    }

    public function lotes_legajos()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        $busquedaEjecutada = array_key_exists('termino', $_GET)
            || array_key_exists('estado_legajo', $_GET)
            || array_key_exists('id_tipo_legajo', $_GET)
            || array_key_exists('filtro_documentos', $_GET);

        $filtros = $this->obtenerFiltrosBusquedaLegajosDesdeRequest($_GET);
        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }

        $soloPropios = $this->usuarioDebeVerSoloLegajosPropios();
        $idUsuarioActual = intval($_SESSION['id'] ?? 0);
        $resultados = $busquedaEjecutada
            ? $this->model->buscarLegajosPorTermino(
                $filtros['termino'],
                $filtros['estado_legajo'],
                $filtros['id_tipo_legajo'],
                $filtros['filtro_documentos'],
                $idUsuarioActual,
                $soloPropios,
                $tiposPermitidos
            )
            : [];
        $resultados = $this->enriquecerResultadosConPdfFinal($resultados);

        $data = [
            'termino' => $filtros['termino'],
            'resultados' => $resultados,
            'busqueda_ejecutada' => $busquedaEjecutada,
            'estado_legajo' => $filtros['estado_legajo'],
            'id_tipo_legajo' => $filtros['id_tipo_legajo'],
            'filtro_documentos' => $filtros['filtro_documentos'],
            'tipos_legajo' => $tipos_legajo,
            'puede_rearmar_lote' => $this->rolActualTienePermisoLegajo('armar_legajo'),
            'puede_descargar_lote' => $this->puedeAccederPdfLegajo(),
        ];
        $this->views->getView($this, 'lotes_legajos', $data);
    }

    public function procesar_lote_legajos()
    {
        $this->checkLegajoGroupPermission('buscar_legajos');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'legajos/lotes_legajos');
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header('Location: ' . base_url() . 'legajos/lotes_legajos?error=csrf');
            exit();
        }

        $accion = trim((string)($_POST['accion_lote'] ?? ''));
        $filtros = $this->obtenerFiltrosBusquedaLegajosDesdeRequest($_POST);
        $origen = trim((string)($_POST['origen_lote'] ?? 'lotes_legajos'));
        $redirect = $origen === 'administrar_legajos'
            ? $this->construirUrlAdministrarLegajos($filtros)
            : $this->construirUrlLotesLegajos($filtros);
        $idsLegajo = $this->obtenerLegajosSeleccionadosDesdePost();

        if (empty($idsLegajo)) {
            if (function_exists('setAlert')) {
                setAlert('warning', 'Seleccione al menos un legajo para ejecutar la acción por lotes.');
            }
            header('Location: ' . $redirect);
            exit();
        }

        if ($accion === 'rearmar') {
            $this->checkLegajoGroupPermission('armar_legajo');

            $procesados = 0;
            $sinAcceso = 0;
            $sinDocumentos = 0;
            $errores = 0;
            foreach ($idsLegajo as $idLegajo) {
                if (!$this->puedeAccederLegajoSinRedireccion($idLegajo)) {
                    $sinAcceso++;
                    continue;
                }

                if ($this->regenerarPdfFinalLegajo($idLegajo, $_SESSION['nombre'] ?? 'Sistema')) {
                    $procesados++;
                } else {
                    $documentos = $this->model->obtenerDocumentosCargadosParaUnir($idLegajo);
                    if (empty($documentos)) {
                        $sinDocumentos++;
                    } else {
                        $errores++;
                    }
                }
            }

            if (function_exists('setAlert')) {
                $mensajes = [];
                if ($procesados > 0) {
                    $mensajes[] = $procesados . ' rearmado(s)';
                }
                if ($sinDocumentos > 0) {
                    $mensajes[] = $sinDocumentos . ' sin documentos cargados';
                }
                if ($sinAcceso > 0) {
                    $mensajes[] = $sinAcceso . ' sin acceso';
                }
                if ($errores > 0) {
                    $mensajes[] = $errores . ' con error al generar';
                }

                $tipo = $procesados > 0 ? 'success' : 'warning';
                $mensaje = empty($mensajes)
                    ? 'No se pudo rearmar ningún legajo.'
                    : 'Proceso por lotes finalizado: ' . implode(', ', $mensajes) . '.';
                setAlert($tipo, $mensaje);
            }

            header('Location: ' . $redirect);
            exit();
        }

        if ($accion === 'descargar') {
            if (!$this->puedeAccederPdfLegajo()) {
                if (function_exists('setAlert')) {
                    setAlert('warning', 'Tu rol no tiene permisos para descargar PDFs de legajos.');
                }
                header('Location: ' . $redirect);
                exit();
            }

            if (!class_exists('ZipArchive')) {
                if (function_exists('setAlert')) {
                    setAlert('error', 'El servidor no tiene disponible la extensión ZipArchive para generar la descarga por lotes.');
                }
                header('Location: ' . $redirect);
                exit();
            }

            $tempBase = tempnam(sys_get_temp_dir(), 'legajos_lote_');
            if ($tempBase === false) {
                if (function_exists('setAlert')) {
                    setAlert('error', 'No se pudo preparar el archivo temporal para la descarga por lotes.');
                }
                header('Location: ' . $redirect);
                exit();
            }

            $zipPath = $tempBase . '.zip';
            @unlink($tempBase);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                if (function_exists('setAlert')) {
                    setAlert('error', 'No se pudo crear el ZIP para la descarga por lotes.');
                }
                header('Location: ' . $redirect);
                exit();
            }

            $agregados = 0;
            $omitidos = [];
            foreach ($idsLegajo as $idLegajo) {
                if (!$this->puedeAccederLegajoSinRedireccion($idLegajo)) {
                    $omitidos[] = 'Legajo #' . $idLegajo . ': sin acceso.';
                    continue;
                }

                $archivo = $this->obtenerRutaPdfFinalLegajo($idLegajo);
                if (empty($archivo['ruta_absoluta']) || !is_file($archivo['ruta_absoluta'])) {
                    $omitidos[] = 'Legajo #' . $idLegajo . ': PDF final no disponible.';
                    continue;
                }

                $nombreZip = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string)($archivo['nombre_archivo'] ?? ('legajo_' . $idLegajo . '.pdf')));
                $nombreZip = trim((string)$nombreZip, '_');
                if ($nombreZip === '') {
                    $nombreZip = 'legajo_' . $idLegajo . '.pdf';
                }

                $zip->addFile($archivo['ruta_absoluta'], $nombreZip);
                $agregados++;
            }

            if (!empty($omitidos)) {
                $zip->addFromString('resumen_descarga.txt', implode(PHP_EOL, $omitidos) . PHP_EOL);
            }
            $zip->close();

            if ($agregados <= 0 || !is_file($zipPath)) {
                @unlink($zipPath);
                if (function_exists('setAlert')) {
                    setAlert('warning', 'No se encontraron PDFs finales disponibles para los legajos seleccionados.');
                }
                header('Location: ' . $redirect);
                exit();
            }

            $nombreDescarga = 'legajos_lote_' . date('Ymd_His') . '.zip';
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $nombreDescarga . '"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            @unlink($zipPath);
            exit();
        }

        if (function_exists('setAlert')) {
            setAlert('warning', 'La acción por lotes solicitada no es válida.');
        }
        header('Location: ' . $redirect);
        exit();
    }

    public function verificar_legajo($idLegajo = 0)
    {
        $this->checkLegajoGroupPermission('verificar_legajos');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "legajos/buscar_legajos");
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "legajos/buscar_legajos?error=csrf");
            exit();
        }

        $idLegajo = intval($idLegajo ?: ($_POST['id_legajo'] ?? 0));
        $this->asegurarAccesoLegajo($idLegajo);
        $termino = trim($_POST['termino'] ?? '');
        $estadoLegajoFiltro = trim($_POST['estado_legajo'] ?? '');
        $idTipoLegajoFiltro = intval($_POST['id_tipo_legajo'] ?? 0);
        $observacionLegajo = trim($_POST['observacion_legajo'] ?? '');

        if ($idLegajo <= 0) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el legajo a verificar.");
            }
            header("Location: " . base_url() . "legajos/buscar_legajos");
            exit();
        }

        if (!$this->model->soportaEstadoLegajo('verificado')) {
            if (function_exists('setAlert')) {
                setAlert('warning', "La base de datos aún no admite el estado Verificado. Debe actualizar el campo estado de cfg_legajo.");
            }
            $parametros = [];
            if ($termino !== '') {
                $parametros['termino'] = $termino;
            }
            if ($estadoLegajoFiltro !== '') {
                $parametros['estado_legajo'] = $estadoLegajoFiltro;
            }
            if ($idTipoLegajoFiltro > 0) {
                $parametros['id_tipo_legajo'] = $idTipoLegajoFiltro;
            }
            $redirect = base_url() . "legajos/verificar_legajos" . (!empty($parametros) ? '?' . http_build_query($parametros) : '');
            header("Location: " . $redirect);
            exit();
        }

        $legajoAntes = $this->model->selectLegajoPorId($idLegajo);
        $estadoRecalculado = $this->recalcularEstadoLegajoActual($idLegajo);
        $estadoGenerado = $this->model->soportaEstadoLegajo('generado') ? 'generado' : 'finalizado';
        if ($estadoRecalculado !== $estadoGenerado) {
            if (function_exists('setAlert')) {
                setAlert('warning', "El legajo debe estar en estado Generado para pasar a verificación.");
            }
            $parametros = [];
            if ($termino !== '') {
                $parametros['termino'] = $termino;
            }
            if ($estadoLegajoFiltro !== '') {
                $parametros['estado_legajo'] = $estadoLegajoFiltro;
            }
            if ($idTipoLegajoFiltro > 0) {
                $parametros['id_tipo_legajo'] = $idTipoLegajoFiltro;
            }
            $redirect = base_url() . "legajos/verificar_legajos" . (!empty($parametros) ? '?' . http_build_query($parametros) : '');
            header("Location: " . $redirect);
            exit();
        }

        $actualizado = $this->model->actualizarEstadoLegajo($idLegajo, 'verificado', true);
        $pdfRegenerado = false;
        if ($actualizado) {
            $auditoria = $this->obtenerContextoAuditoria();
            $this->model->actualizarObservacionLegajo($idLegajo, $observacionLegajo);
            $this->model->registrarLogLegajo(
                $idLegajo,
                'VERIFICADO',
                'Legajo verificado manualmente.',
                $legajoAntes['estado'] ?? '',
                'verificado',
                $auditoria['id_usuario'],
                $auditoria['nombre_host'],
                $auditoria['ip_host'],
                $observacionLegajo
            );
            $pdfRegenerado = $this->regenerarPdfFinalLegajo($idLegajo, $_SESSION['nombre'] ?? 'Sistema');
        }
        if (function_exists('setAlert')) {
            if ($actualizado && $pdfRegenerado) {
                setAlert('success', "Legajo verificado correctamente.");
            } elseif ($actualizado) {
                setAlert('warning', "El legajo quedó verificado, pero no se pudo regenerar el PDF final.");
            } else {
                setAlert('error', "No se pudo verificar el legajo.");
            }
        }

        $parametros = [];
        if ($termino !== '') {
            $parametros['termino'] = $termino;
        }
        if ($estadoLegajoFiltro !== '') {
            $parametros['estado_legajo'] = $estadoLegajoFiltro;
        }
        if ($idTipoLegajoFiltro > 0) {
            $parametros['id_tipo_legajo'] = $idTipoLegajoFiltro;
        }
        $redirect = base_url() . "legajos/verificar_legajos" . (!empty($parametros) ? '?' . http_build_query($parametros) : '');
        header("Location: " . $redirect);
        exit();
    }

    public function rechazar_verificacion_legajo($idLegajo = 0)
    {
        $this->checkLegajoGroupPermission('verificar_legajos');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "legajos/administrar_legajos?error=csrf");
            exit();
        }

        $idLegajo = intval($idLegajo ?: ($_POST['id_legajo'] ?? 0));
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/verificar_legajos');
        $termino = trim($_POST['termino'] ?? '');
        $observacionLegajo = trim($_POST['observacion_legajo'] ?? '');

        if ($idLegajo <= 0) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el legajo a rechazar.");
            }
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if ($observacionLegajo === '') {
            if (function_exists('setAlert')) {
                setAlert('warning', "Debe escribir una observación para rechazar la verificación.");
            }
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if (!$this->model->soportaEstadoLegajo('verificacion_rechazada')) {
            if (function_exists('setAlert')) {
                setAlert('warning', "La base de datos aún no admite el estado Verificación rechazada. Debe actualizar el campo estado de cfg_legajo.");
            }
            $redirect = base_url() . "legajos/verificar_legajos";
            if ($termino !== '') {
                $redirect .= "?termino=" . urlencode($termino);
            }
            header("Location: " . $redirect);
            exit();
        }

        $legajoAntes = $this->model->selectLegajoPorId($idLegajo);
        $actualizado = $this->model->actualizarEstadoLegajo($idLegajo, 'verificacion_rechazada', false);
        if ($actualizado) {
            $this->invalidarPdfFinalLegajo($idLegajo);
            $auditoria = $this->obtenerContextoAuditoria();
            $this->model->actualizarObservacionLegajo($idLegajo, $observacionLegajo);
            $this->model->registrarLogLegajo(
                $idLegajo,
                'VERIFICACION_RECHAZADA',
                'Se rechazó la verificación del legajo.',
                $legajoAntes['estado'] ?? '',
                'verificacion_rechazada',
                $auditoria['id_usuario'],
                $auditoria['nombre_host'],
                $auditoria['ip_host'],
                $observacionLegajo
            );
        }

        if (function_exists('setAlert')) {
            if ($actualizado) {
                setAlert('success', "Verificación rechazada correctamente.");
            } else {
                setAlert('error', "No se pudo rechazar la verificación del legajo.");
            }
        }

        $redirect = base_url() . "legajos/verificar_legajos";
        if ($termino !== '') {
            $redirect .= "?termino=" . urlencode($termino);
        }
        header("Location: " . $redirect);
        exit();
    }

    public function cerrar_aviso_rechazo_legajo($idLegajo = 0)
    {
        $this->checkLegajoGroupPermission('armar_legajo');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "legajos/armar_legajo");
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "legajos/armar_legajo?error=csrf");
            exit();
        }

        $idLegajo = intval($idLegajo ?: ($_POST['id_legajo'] ?? 0));
        if ($idLegajo <= 0) {
            header("Location: " . base_url() . "legajos/armar_legajo");
            exit();
        }

        $this->asegurarAccesoLegajo($idLegajo, 'legajos/armar_legajo');
        $legajo = $this->model->selectLegajoPorId($idLegajo);
        if (empty($legajo)) {
            header("Location: " . base_url() . "legajos/armar_legajo");
            exit();
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($legajo['id_tipo_legajo'] ?? 0));
        $legajoDocumentos = $this->model->selectLegajoDocumentosPorLegajo($idLegajo);
        $estadoLegajoCalculado = $this->resolverEstadoLegajo($matriz, $legajoDocumentos);
        $this->model->actualizarEstadoLegajo($idLegajo, $estadoLegajoCalculado, false);

        header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
        exit();
    }

    public function cerrar_legajo($idLegajo = 0)
    {
        $this->checkLegajoGroupPermission('administrar_legajos');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "legajos/administrar_legajos?error=csrf");
            exit();
        }

        $idLegajo = intval($idLegajo ?: ($_POST['id_legajo'] ?? 0));
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/administrar_legajos');
        $termino = trim($_POST['termino'] ?? '');
        $estado_legajo = trim($_POST['estado_legajo'] ?? '');
        $id_tipo_legajo = intval($_POST['id_tipo_legajo'] ?? 0);

        if ($idLegajo <= 0) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el legajo a cerrar.");
            }
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if (!$this->model->soportaEstadoLegajo('cerrado')) {
            if (function_exists('setAlert')) {
                setAlert('warning', "La base de datos aún no admite el estado Cerrado. Debe actualizar el campo estado de cfg_legajo.");
            }
            $query = [];
            if ($termino !== '') {
                $query['termino'] = $termino;
            }
            if ($estado_legajo !== '') {
                $query['estado_legajo'] = $estado_legajo;
            }
            if ($id_tipo_legajo > 0) {
                $query['id_tipo_legajo'] = $id_tipo_legajo;
            }
            $redirect = base_url() . "legajos/administrar_legajos";
            if (!empty($query)) {
                $redirect .= '?' . http_build_query($query);
            }
            header("Location: " . $redirect);
            exit();
        }

        $legajoAntes = $this->model->selectLegajoPorId($idLegajo);
        $actualizado = $this->model->actualizarEstadoLegajo($idLegajo, 'cerrado', true);
        $pdfRegenerado = false;
        if ($actualizado) {
            $auditoria = $this->obtenerContextoAuditoria();
            $this->model->registrarLogLegajo(
                $idLegajo,
                'CERRADO',
                'Legajo cerrado.',
                $legajoAntes['estado'] ?? '',
                'cerrado',
                $auditoria['id_usuario'],
                $auditoria['nombre_host'],
                $auditoria['ip_host']
            );
            $pdfRegenerado = $this->regenerarPdfFinalLegajo($idLegajo, $_SESSION['nombre'] ?? 'Sistema');
        }
        if (function_exists('setAlert')) {
            if ($actualizado && $pdfRegenerado) {
                setAlert('success', "Legajo cerrado correctamente.");
            } elseif ($actualizado) {
                setAlert('warning', "El legajo quedó cerrado, pero no se pudo regenerar el PDF final.");
            } else {
                setAlert('error', "No se pudo cerrar el legajo.");
            }
        }

        $query = [];
        if ($termino !== '') {
            $query['termino'] = $termino;
        }
        if ($estado_legajo !== '') {
            $query['estado_legajo'] = $estado_legajo;
        }
        if ($id_tipo_legajo > 0) {
            $query['id_tipo_legajo'] = $id_tipo_legajo;
        }
        $redirect = base_url() . "legajos/administrar_legajos";
        if (!empty($query)) {
            $redirect .= '?' . http_build_query($query);
        }
        header("Location: " . $redirect);
        exit();
    }

    public function eliminar_legajo($idLegajo = 0)
    {
        $this->checkLegajoGroupPermission('eliminar_legajo');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "legajos/administrar_legajos?error=csrf");
            exit();
        }

        $idLegajo = intval($idLegajo ?: ($_POST['id_legajo'] ?? 0));
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/administrar_legajos');
        $termino = trim($_POST['termino'] ?? '');
        $estado_legajo = trim($_POST['estado_legajo'] ?? '');
        $id_tipo_legajo = intval($_POST['id_tipo_legajo'] ?? 0);

        if ($idLegajo <= 0) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el legajo a eliminar.");
            }
            header("Location: " . base_url() . "legajos/administrar_legajos");
            exit();
        }

        $eliminado = $this->model->eliminarLegajo($idLegajo);
        $archivosEliminados = true;
        if ($eliminado) {
            $archivosEliminados = $this->eliminarArchivosLegajo($idLegajo);
        }
        if (function_exists('setAlert')) {
            if ($eliminado && $archivosEliminados) {
                setAlert('success', "Legajo eliminado correctamente.");
            } elseif ($eliminado) {
                setAlert('warning', "El legajo se eliminó, pero no se pudo borrar completamente su carpeta de archivos.");
            } else {
                setAlert('error', "No se pudo eliminar el legajo.");
            }
        }

        $query = [];
        if ($termino !== '') {
            $query['termino'] = $termino;
        }
        if ($estado_legajo !== '') {
            $query['estado_legajo'] = $estado_legajo;
        }
        if ($id_tipo_legajo > 0) {
            $query['id_tipo_legajo'] = $id_tipo_legajo;
        }
        $redirect = base_url() . "legajos/administrar_legajos";
        if (!empty($query)) {
            $redirect .= '?' . http_build_query($query);
        }
        header("Location: " . $redirect);
        exit();
    }

    public function procesar_legajo()
    {
        $this->checkLegajoGroupPermission('armar_legajo');
        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        $accion = trim($_POST['submit_action'] ?? '');
        $idLegajo = intval($_POST['id_legajo'] ?? 0);
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/armar_legajo');
        $duplicadoDesde = intval($_POST['duplicado_desde'] ?? 0);
        $idTipoLegajo = intval($_POST['tipo_legajo'] ?? 0);
        $ciSocio = trim($_POST['ci_socio'] ?? '');
        $nombreCompleto = trim($_POST['nombre_socio'] ?? '');
        $nroSolicitud = trim($_POST['nro_solicitud'] ?? '');
        $fuentePersonas = $this->obtenerFuentePersonasLegajo();
        $personasModuloActivo = $fuentePersonas !== 'ninguna';
        $idPersona = $personasModuloActivo ? intval($_POST['id_persona'] ?? 0) : 0;
        $idUsuario = intval($_SESSION['id'] ?? 0);
        $auditoria = $this->obtenerContextoAuditoria();
        $esFinalizacion = $accion === 'finalizar';
        $estadoLegajo = $esFinalizacion ? 'activo' : 'borrador';
        $legajoAntesGuardar = $idLegajo > 0 ? $this->model->selectLegajoPorId($idLegajo) : [];

        if (!$esFinalizacion && !empty($legajoAntesGuardar)) {
            if ($idTipoLegajo <= 0) {
                $idTipoLegajo = intval($legajoAntesGuardar['id_tipo_legajo'] ?? 0);
            }
            if ($ciSocio === '') {
                $ciSocio = trim((string)($legajoAntesGuardar['ci_socio'] ?? ''));
            }
            if ($nombreCompleto === '') {
                $nombreCompleto = trim((string)($legajoAntesGuardar['nombre_completo'] ?? ''));
            }
            if ($nroSolicitud === '') {
                $nroSolicitud = trim((string)($legajoAntesGuardar['nro_solicitud'] ?? ''));
            }
            if ($idPersona <= 0) {
                $idPersona = intval($legajoAntesGuardar['id_persona'] ?? 0);
            }
        }

        if ($personasModuloActivo) {
            $personaLegajo = $fuentePersonas === 'externa'
                ? $this->obtenerPersonaExternaPorId($idPersona)
                : $this->obtenerPersonaActivaPorId($idPersona);
            if (empty($personaLegajo)) {
                if (function_exists('setAlert')) {
                    setAlert('warning', $fuentePersonas === 'externa'
                        ? 'Seleccione una persona de la base externa para armar el legajo.'
                        : 'Seleccione una persona activa para armar el legajo.');
                }
                header("Location: " . $this->construirUrlArmarLegajo($idLegajo, $duplicadoDesde));
                exit();
            }

            $ciSocio = trim((string)($personaLegajo['ci'] ?? ''));
            $nombreCompleto = trim((string)($personaLegajo['nombre_completo'] ?? ''));
            if ($nroSolicitud === '') {
                $nroSolicitud = trim((string)($personaLegajo['nro_solicitud'] ?? ''));
            }
        }

        $this->guardarFlashFormularioLegajo([
            'duplicado_desde' => $duplicadoDesde,
            'legajo' => [
                'id_legajo' => $idLegajo,
                'id_tipo_legajo' => $idTipoLegajo,
                'id_persona' => $fuentePersonas === 'interna' && $personasModuloActivo ? $idPersona : null,
                'ci_socio' => $ciSocio,
                'nombre_completo' => $nombreCompleto,
                'nro_solicitud' => $nroSolicitud,
            ],
            'documentos' => $this->construirFlashDocumentosDesdePost(),
        ]);

        $tipoLegajoActual = $idTipoLegajo > 0 ? $this->model->selectTipoLegajoPorId($idTipoLegajo) : [];
        $requiereNroSolicitud = !empty($tipoLegajoActual['requiere_nro_solicitud']);

        if ($idTipoLegajo <= 0 || $ciSocio === '' || $nombreCompleto === '' || $idUsuario <= 0) {
            if (function_exists('setAlert')) {
                setAlert('warning', "Complete los datos base obligatorios del legajo.");
            }
            header("Location: " . $this->construirUrlArmarLegajo($idLegajo, $duplicadoDesde));
            exit();
        }

        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo() && !in_array($idTipoLegajo, $tiposPermitidos, true)) {
            if (function_exists('setAlert')) {
                setAlert('warning', "Tu rol no tiene permiso para trabajar con ese tipo de legajo.");
            }
            header("Location: " . $this->construirUrlArmarLegajo($idLegajo, $duplicadoDesde));
            exit();
        }

        if ($requiereNroSolicitud && $nroSolicitud === '') {
            if (function_exists('setAlert')) {
                setAlert('warning', "El tipo de legajo seleccionado requiere número de solicitud.");
            }
            header("Location: " . $this->construirUrlArmarLegajo($idLegajo, $duplicadoDesde));
            exit();
        }

        if ($requiereNroSolicitud && $this->model->existeSolicitudDuplicada($nroSolicitud, $idLegajo)) {
            if (function_exists('setAlert')) {
                setAlert('warning', "Ya existe un legajo con ese número de solicitud. No puede guardarse duplicado.");
            }
            header("Location: " . $this->construirUrlArmarLegajo($idLegajo, $duplicadoDesde));
            exit();
        }

        if (!$requiereNroSolicitud && $idLegajo <= 0 && $this->model->existeLegajoDuplicadoSinSolicitud($idTipoLegajo, $ciSocio, 0)) {
            $legajoExistente = $this->model->selectLegajoDuplicadoSinSolicitud($idTipoLegajo, $ciSocio, 0);
            $idLegajoExistente = intval($legajoExistente['id_legajo'] ?? 0);
            if ($idLegajoExistente > 0) {
                if (function_exists('setAlert')) {
                    setAlert('warning', "Ya existe un legajo abierto para esta persona en ese tipo. Se cargó el legajo existente.");
                }
                header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajoExistente);
                exit();
            }
        }

        if ($idLegajo > 0) {
            $legajoActual = $this->model->selectLegajoPorId($idLegajo);
            if (in_array(($legajoActual['estado'] ?? ''), ['aprobado', 'cerrado'], true)) {
                if (function_exists('setAlert')) {
                    setAlert('warning', "El legajo cerrado no puede modificarse.");
                }
                header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
                exit();
            }
        }

        $esNuevoLegajo = $idLegajo <= 0;
        $legajoAntesGuardar = $idLegajo > 0 ? $this->model->selectLegajoPorId($idLegajo) : [];
        $estadoLegajoAntesGuardar = strtolower(trim((string)($legajoAntesGuardar['estado'] ?? '')));
        $legajoEstabaVerificado = $estadoLegajoAntesGuardar === 'verificado';
        $legajoEstabaRechazado = $estadoLegajoAntesGuardar === 'verificacion_rechazada';
        $legajoModificadoTrasVerificacion = false;
        $legajoModificadoTrasRechazo = false;
        $huboCambiosParaRearmarPdf = $esNuevoLegajo;

        if ($idLegajo > 0) {
            if (
                intval($legajoAntesGuardar['id_tipo_legajo'] ?? 0) !== $idTipoLegajo
                || ($fuentePersonas === 'interna' && $personasModuloActivo && intval($legajoAntesGuardar['id_persona'] ?? 0) !== $idPersona)
                || trim((string)($legajoAntesGuardar['ci_socio'] ?? '')) !== $ciSocio
                || trim((string)($legajoAntesGuardar['nombre_completo'] ?? '')) !== $nombreCompleto
                || trim((string)($legajoAntesGuardar['nro_solicitud'] ?? '')) !== $nroSolicitud
            ) {
                $huboCambiosParaRearmarPdf = true;
                if ($legajoEstabaVerificado) {
                    $legajoModificadoTrasVerificacion = true;
                }
            }


            $idPersonaPersistir = $fuentePersonas === 'interna' && $personasModuloActivo
                ? $idPersona
                : (isset($legajoAntesGuardar['id_persona']) ? intval($legajoAntesGuardar['id_persona']) : null);
            $actualizado = $this->model->actualizarLegajo(
                $idLegajo,
                $idTipoLegajo,
                $ciSocio,
                $nombreCompleto,
                $nroSolicitud,
                $estadoLegajo,
                $idPersonaPersistir
            );
            if (!$actualizado) {
                $idLegajo = 0;
            }
        } else {
            $idLegajo = $this->model->insertarLegajo(
                $idTipoLegajo,
                $ciSocio,
                $nombreCompleto,
                $nroSolicitud,
                $idUsuario,
                $estadoLegajo,
                $fuentePersonas === 'interna' && $personasModuloActivo ? $idPersona : null
            );
        }

        if (!$idLegajo) {
            if (function_exists('setAlert')) {
                setAlert('error', "No se pudo crear el legajo.");
            }
            header("Location: " . $this->construirUrlArmarLegajo(0, $duplicadoDesde));
            exit();
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo($idTipoLegajo);
        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0 || $this->model->existeLegajoDocumento(intval($idLegajo), $idRequisito)) {
                continue;
            }
            $this->model->insertarLegajoDocumento(
                intval($idLegajo),
                $idRequisito,
                intval($regla['id_documento_maestro'] ?? 0),
                trim($regla['rol_vinculado'] ?? 'TITULAR'),
                !empty($regla['es_obligatorio']) ? 1 : 0,
                'pendiente'
            );
        }

        if ($esNuevoLegajo) {
            $this->model->registrarLogLegajo(
                intval($idLegajo),
                'CREADO',
                'Se creó el legajo.',
                null,
                $estadoLegajo,
                $auditoria['id_usuario'],
                $auditoria['nombre_host'],
                $auditoria['ip_host']
            );
        }

        $documentosExistentes = [];
        foreach ($this->model->selectLegajoDocumentosPorLegajo(intval($idLegajo)) as $documentoExistente) {
            $documentosExistentes[intval($documentoExistente['id_requisito'] ?? 0)] = $documentoExistente;
        }

        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $rutaBaseLegajos = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . intval($idLegajo) . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajos)) {
            mkdir($rutaBaseLegajos, 0777, true);
        }

        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0) {
                continue;
            }

            $esCampoDocumento = $this->esCampoDocumento($regla);
            $marcadoEliminarArchivo = intval($_POST['eliminar_archivo_' . $idRequisito] ?? 0) === 1;
            if (!$esCampoDocumento) {
                $marcadoEliminarArchivo = false;
            }
            $fechaExpedicion = trim($_POST['fecha_expedicion_' . $idRequisito] ?? '');
            $observacionDocumento = trim($_POST['observacion_' . $idRequisito] ?? '');
            $valorCampoAnterior = trim((string)($documentosExistentes[$idRequisito]['valor_campo'] ?? ''));
            $valorCampo = $esCampoDocumento ? null : $this->obtenerValorCampoDesdePost($idRequisito, $regla, $valorCampoAnterior);
            if (!$esCampoDocumento) {
                $fechaExpedicion = '';
            }
            $fechaVencimiento = !$marcadoEliminarArchivo && $fechaExpedicion !== ''
                ? $this->calcularFechaVencimiento($fechaExpedicion, $regla)
                : ($documentosExistentes[$idRequisito]['fecha_vencimiento'] ?? null);
            if (!$esCampoDocumento) {
                $fechaVencimiento = null;
            }
            $rutaArchivoDuplicado = trim($_POST['ruta_existente_' . $idRequisito] ?? '');
            $fileKey = null;
            if ($esCampoDocumento) {
                foreach ($_FILES as $inputName => $fileInfo) {
                    if (strpos($inputName, 'doc_' . $idRequisito . '_') === 0) {
                        $fileKey = $inputName;
                        break;
                    }
                }
            }

            $rutaRelativa = null;
            $estadoDocumento = null;
            $rutaArchivoExistente = trim((string)($documentosExistentes[$idRequisito]['ruta_archivo'] ?? ''));
            if ($rutaArchivoExistente === '' && $rutaArchivoDuplicado !== '') {
                $rutaArchivoExistente = $rutaArchivoDuplicado;
            }

            if ($esCampoDocumento && $marcadoEliminarArchivo) {
                $rutaEliminar = $rutaArchivoExistente !== '' ? $rutaArchivoExistente : $rutaArchivoDuplicado;
                $prefijoLegajoActual = 'Legajos/' . intval($idLegajo) . '/';
                if ($rutaEliminar !== '' && strpos(str_replace('\\', '/', $rutaEliminar), $prefijoLegajoActual) === 0) {
                    $rutaEliminarFisica = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaEliminar);
                    if (is_file($rutaEliminarFisica)) {
                        @unlink($rutaEliminarFisica);
                    }
                }

                $rutaArchivoExistente = '';
                $rutaArchivoDuplicado = '';
                $fechaExpedicion = '';
                $fechaVencimiento = null;
            }

            $hayArchivoSubido = false;
            if ($esCampoDocumento && $fileKey !== null && isset($_FILES[$fileKey])) {
                $errorArchivo = $_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE;
                if (is_array($errorArchivo)) {
                    foreach ($errorArchivo as $errorItem) {
                        if (intval($errorItem) === UPLOAD_ERR_OK) {
                            $hayArchivoSubido = true;
                            break;
                        }
                    }
                } else {
                    $hayArchivoSubido = intval($errorArchivo) === UPLOAD_ERR_OK;
                }
            }

            if ($esCampoDocumento && $fileKey !== null && isset($_FILES[$fileKey]) && $hayArchivoSubido) {
                if ($this->requiereFechaExpedicion($regla) && $fechaExpedicion === '') {
                    if (function_exists('setAlert')) {
                        setAlert('warning', 'Debe completar la fecha de expedición antes de cargar el documento "' . trim((string)($regla['documento_nombre'] ?? '')) . '".');
                    }
                    header("Location: " . $this->construirUrlArmarLegajo(intval($idLegajo), $duplicadoDesde));
                    exit();
                }
                $archivo = $_FILES[$fileKey];
                $nombresArchivo = $archivo['name'] ?? '';
                $listaNombres = is_array($nombresArchivo) ? $nombresArchivo : [$nombresArchivo];
                $extensionesInvalidas = [];
                foreach ($listaNombres as $nombreArchivoOriginal) {
                    $extension = strtolower(pathinfo((string)$nombreArchivoOriginal, PATHINFO_EXTENSION));
                if ($extension !== '' && !in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'jfif'], true)) {
                    $extensionesInvalidas[] = $extension;
                }
                }
                if (!empty($extensionesInvalidas)) {
                    if (function_exists('setAlert')) {
                        setAlert('warning', 'Solo se permiten archivos PDF o imágenes JPG, JPEG, PNG o JFIF.');
                    }
                    header("Location: " . $this->construirUrlArmarLegajo(intval($idLegajo), $duplicadoDesde));
                    exit();
                }
                $codigoDocumento = trim((string)($regla['codigo_interno'] ?? ''));
                if ($codigoDocumento === '') {
                    $codigoDocumento = 'DOC' . $idRequisito;
                }
                $codigoDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', $codigoDocumento);
                $rolDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', strtoupper(trim((string)($regla['rol_vinculado'] ?? 'TITULAR'))));
                if ($rolDocumento === '') {
                    $rolDocumento = 'TITULAR';
                }
                $cedulaLegajo = preg_replace('/[^0-9]+/', '', (string)($ciSocio ?? ($legajoAntesGuardar['ci_socio'] ?? '')));
                if ($cedulaLegajo === '') {
                    $cedulaLegajo = 'SINCI';
                }
                $numeroSolicitudArchivo = preg_replace('/[^0-9A-Za-z]+/', '', (string)($nroSolicitud ?? ($legajoAntesGuardar['nro_solicitud'] ?? '')));
                $segmentosArchivo = [$codigoDocumento, $rolDocumento, 'REQ' . $idRequisito, $cedulaLegajo];
                if ($numeroSolicitudArchivo !== '') {
                    $segmentosArchivo[] = $numeroSolicitudArchivo;
                }
                $nombreArchivo = implode('_', $segmentosArchivo) . '.pdf';
                $rutaFisica = $rutaBaseLegajos . $nombreArchivo;
                $guardadoArchivo = $this->generarPdfDesdeArchivosSubidos($archivo, $rutaFisica, $rutaBaseLegajos);
                if ($guardadoArchivo) {
                    $rutaRelativa = 'Legajos/' . intval($idLegajo) . '/' . $nombreArchivo;
                }
            }



            $politicaActualizacion = strtoupper(trim((string)($regla['politica_actualizacion'] ?? '')));
            if ($politicaActualizacion === '') {
                $politicaActualizacion = !empty($regla['permite_reemplazo']) ? 'REEMPLAZAR' : 'NO_PERMITIR';
            }
            $accionSolicitada = strtoupper(trim((string)($_POST['accion_archivo_' . $idRequisito] ?? '')));
            if (
                $rutaArchivoExistente !== ''
                && in_array($accionSolicitada, ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR'], true)
            ) {
                $politicaActualizacion = $accionSolicitada;
            }
            $permiteActualizarDocumento = in_array($politicaActualizacion, ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL'], true);

            if ($rutaRelativa !== null && $rutaArchivoExistente !== '' && !$permiteActualizarDocumento) {
                $rutaFisicaNueva = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);
                if (is_file($rutaFisicaNueva)) {
                    @unlink($rutaFisicaNueva);
                }
                $rutaRelativa = null;
            }

            if (
                $rutaRelativa !== null
                && $rutaArchivoExistente !== ''
                && in_array($politicaActualizacion, ['UNIR_AL_INICIO', 'UNIR_AL_FINAL'], true)
            ) {
                $rutaFisicaNueva = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);
                $rutaFisicaExistente = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaArchivoExistente);
                $rutaRelativaUnida = $this->unirSegunPolitica($politicaActualizacion, $rutaFisicaNueva, $rutaFisicaExistente, $rutaBaseLegajos, intval($idLegajo));
                if ($rutaRelativaUnida !== null) {
                    $rutaRelativa = $rutaRelativaUnida;
                }
            }

            $rutaEstado = $esCampoDocumento
                ? ($rutaRelativa !== null ? $rutaRelativa : $rutaArchivoExistente)
                : '';
            $fechaVencimientoEstado = $fechaVencimiento !== null
                ? $fechaVencimiento
                : ($documentosExistentes[$idRequisito]['fecha_vencimiento'] ?? null);
            $estadoDocumento = $this->resolverEstadoDocumentoPorRegla($regla, $rutaEstado, $fechaVencimientoEstado, $valorCampo);
            $estadoDocumentoGuardar = $estadoDocumento === 'por_vencer' ? 'cargado' : $estadoDocumento;

            $rutaGuardar = $esCampoDocumento ? ($marcadoEliminarArchivo ? '' : $rutaRelativa) : '';
            if ($esCampoDocumento && $rutaGuardar === null && empty($documentosExistentes[$idRequisito]['ruta_archivo']) && $rutaArchivoExistente !== '') {
                $rutaGuardar = $rutaArchivoExistente;
            }

            $documentoAnterior = $documentosExistentes[$idRequisito] ?? [];
            $rutaAnterior = trim((string)($documentoAnterior['ruta_archivo'] ?? ''));
            $estadoAnteriorDocumento = trim((string)($documentoAnterior['estado'] ?? 'pendiente'));
            $observacionAnteriorDocumento = trim((string)($documentoAnterior['observacion'] ?? ''));
            $fechaAnteriorDocumento = trim((string)($documentoAnterior['fecha_vencimiento'] ?? ''));
            $valorAnteriorDocumento = trim((string)($documentoAnterior['valor_campo'] ?? ''));
            $fechaVencimientoLog = $fechaVencimiento !== null
                ? $fechaVencimiento
                : trim((string)($documentoAnterior['fecha_vencimiento'] ?? ''));
            $rutaNuevaDocumento = $esCampoDocumento ? trim((string)($rutaGuardar ?? $rutaArchivoExistente)) : '';
            $fechaNuevaDocumento = $esCampoDocumento ? trim((string)($fechaVencimiento ?? $fechaAnteriorDocumento)) : '';

            if (
                ($esCampoDocumento && $marcadoEliminarArchivo)
                || ($esCampoDocumento && $rutaRelativa !== null)
                || $rutaNuevaDocumento !== $rutaAnterior
                || $fechaNuevaDocumento !== $fechaAnteriorDocumento
                || trim((string)$valorCampo) !== $valorAnteriorDocumento
                || trim((string)$observacionDocumento) !== $observacionAnteriorDocumento // 🔥 AGREGADO
            ) {
                $huboCambiosParaRearmarPdf = true;
                if ($legajoEstabaVerificado) {
                    $legajoModificadoTrasVerificacion = true;
                }
                if ($legajoEstabaRechazado) {
                    $legajoModificadoTrasRechazo = true;
                }
            }

            $this->model->actualizarLegajoDocumento(
                intval($idLegajo),
                $idRequisito,
                $rutaGuardar,
                $fechaVencimiento,
                $valorCampo,
                $estadoDocumentoGuardar,
                $observacionDocumento
            );

            if (!$esCampoDocumento && trim((string)$valorCampo) !== $valorAnteriorDocumento) {
                $this->model->registrarLogLegajoDocumento(
                    intval($idLegajo),
                    $idRequisito,
                    'CAMPO_ACTUALIZADO',
                    'Se actualizo el valor del campo.',
                    $valorAnteriorDocumento,
                    trim((string)$valorCampo),
                    $estadoAnteriorDocumento,
                    $estadoDocumentoGuardar,
                    null,
                    $auditoria['id_usuario'],
                    $auditoria['nombre_host'],
                    $auditoria['ip_host'],
                    $observacionDocumento
                );
            } elseif ($marcadoEliminarArchivo && $rutaAnterior !== '') {
                $this->model->registrarLogLegajoDocumento(
                    intval($idLegajo),
                    $idRequisito,
                    'DOCUMENTO_ELIMINADO',
                    'Se eliminó el archivo del documento.',
                    $rutaAnterior,
                    '',
                    $estadoAnteriorDocumento,
                    'pendiente',
                    null,
                    $auditoria['id_usuario'],
                    $auditoria['nombre_host'],
                    $auditoria['ip_host'],
                    $observacionDocumento
                );
            } elseif ($rutaRelativa !== null && $rutaRelativa !== '') {
                $accionDocumento = $rutaAnterior !== '' ? 'DOCUMENTO_REEMPLAZADO' : 'DOCUMENTO_CARGADO';
                $detalleDocumento = $rutaAnterior !== '' ? 'Se reemplazó el archivo del documento.' : 'Se cargó el archivo del documento.';
                $this->model->registrarLogLegajoDocumento(
                    intval($idLegajo),
                    $idRequisito,
                    $accionDocumento,
                    $detalleDocumento,
                    $rutaAnterior,
                    $rutaRelativa,
                    $estadoAnteriorDocumento,
                    $estadoDocumentoGuardar,
                    $fechaVencimientoLog,
                    $auditoria['id_usuario'],
                    $auditoria['nombre_host'],
                    $auditoria['ip_host'],
                    $observacionDocumento
                );
            } elseif ($observacionDocumento !== $observacionAnteriorDocumento) {
                $this->model->registrarLogLegajoDocumento(
                    intval($idLegajo),
                    $idRequisito,
                    'DOCUMENTO_OBSERVADO',
                    'Se actualizo la observacion del documento.',
                    $rutaAnterior,
                    $rutaAnterior,
                    $estadoAnteriorDocumento,
                    $estadoDocumentoGuardar,
                    $fechaVencimientoLog,
                    $auditoria['id_usuario'],
                    $auditoria['nombre_host'],
                    $auditoria['ip_host'],
                    $observacionDocumento
                );
            }
        }

        $legajoDocumentosActualizados = $this->model->selectLegajoDocumentosPorLegajo(intval($idLegajo));
        $estadoLegajoCalculado = $this->resolverEstadoLegajo($matriz, $legajoDocumentosActualizados);
        $requiereRearmadoDespuesDeVerificar = $legajoEstabaVerificado && $legajoModificadoTrasVerificacion;
        if ($huboCambiosParaRearmarPdf || $requiereRearmadoDespuesDeVerificar) {
            $this->invalidarPdfFinalLegajo(intval($idLegajo));
        }
        if ($legajoEstabaRechazado && !$legajoModificadoTrasRechazo) {
            $estadoLegajoCalculado = 'verificacion_rechazada';
        }
        $this->model->actualizarEstadoLegajo(intval($idLegajo), $estadoLegajoCalculado, false);
        $this->model->registrarLogLegajo(
            intval($idLegajo),
            $esFinalizacion ? 'FINALIZADO' : 'BORRADOR_GUARDADO',
            $esFinalizacion ? 'Se generó el armado del legajo.' : 'Se guardó el legajo como borrador.',
            $legajoAntesGuardar['estado'] ?? null,
            $estadoLegajoCalculado,
            $auditoria['id_usuario'],
            $auditoria['nombre_host'],
            $auditoria['ip_host']
        );

        // Generar y guardar el archivo ADICIONALES para los campos de texto usando datos actualizados
        $camposNoDocForced = $this->model->obtenerCamposNoDocumentoConValor(intval($idLegajo));
        $rutaBaseLegajoForced = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . intval($idLegajo) . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajoForced)) {
            @mkdir($rutaBaseLegajoForced, 0777, true);
        }

        // Siempre usar datos frescos del legajo (post-actualización) para el encabezado del PDF adicional
        $legajoInfoF = $this->model->selectLegajoPorId(intval($idLegajo));
        if (empty($legajoInfoF)) {
            $legajoInfoF = $legajoAntesGuardar ?: [];
        }
        $ciLF = $legajoInfoF['ci_socio'] ?? '';
        $solLF = $legajoInfoF['nro_solicitud'] ?? '';
        $cedulaLF = preg_replace('/[^0-9]+/', '', $ciLF);
        if ($cedulaLF === '') $cedulaLF = 'SINCI';
        $solLObjF = preg_replace('/[^0-9A-Za-z]+/', '', $solLF);
        $nombreAdicionalF = 'DA_TITULAR_' . $cedulaLF . ($solLObjF !== '' ? '_' . $solLObjF : '') . '.pdf';

        $this->generarPdfCamposNoDocumento($legajoInfoF, $camposNoDocForced, $rutaBaseLegajoForced, $nombreAdicionalF);

        $mensajeExito = "Legajo guardado como borrador.";
        if ($esFinalizacion) {
            $this->invalidarPdfFinalLegajo(intval($idLegajo));
            $this->model->actualizarUsuarioArmado(intval($idLegajo), $idUsuario);
            $documentosParaUnir = $this->model->obtenerDocumentosCargadosParaUnir(intval($idLegajo));
            $camposNoDoc = $this->model->obtenerCamposNoDocumentoConValor(intval($idLegajo));
            if (!empty($documentosParaUnir) || !empty($camposNoDoc)) {
                $legajoActual = $this->model->selectLegajoPorId(intval($idLegajo));
                $usuario = $_SESSION['nombre'] ?? 'Sistema';
                $nombreFinal = $this->construirNombrePdfFinalLegajo($legajoActual, intval($idLegajo));
                $rutaFinal = $rutaBaseLegajos . $nombreFinal;

                if ($this->unirArchivosLegajo($documentosParaUnir, $rutaFinal, 'Legajo ' . intval($idLegajo), $usuario, $legajoActual, $matriz, $camposNoDoc)) {
                    $this->model->insertarLogUnionLegajo(
                        trim($legajoActual['ci_socio'] ?? ''),
                        trim($legajoActual['nombre_completo'] ?? ''),
                        trim($legajoActual['nro_solicitud'] ?? ''),
                        $usuario,
                        $nombreFinal,
                        'Legajos/' . intval($idLegajo)
                    );
                    $_SESSION['legajo_pdf_final_listo'] = [
                        'id_legajo' => intval($idLegajo),
                        'nombre_archivo' => $nombreFinal
                    ];
                    $this->model->registrarLogLegajo(
                        intval($idLegajo),
                        'UNIDO_FINAL',
                        'Se generó el PDF final del legajo.',
                        $estadoLegajoCalculado,
                        $estadoLegajoCalculado,
                        $auditoria['id_usuario'],
                        $auditoria['nombre_host'],
                        $auditoria['ip_host'],
                        $nombreFinal
                    );
                    $estadoGenerado = $this->model->soportaEstadoLegajo('generado') ? 'generado' : 'finalizado';
                    $this->model->actualizarEstadoLegajo(intval($idLegajo), $estadoGenerado, false);
                    $estadoLegajoCalculado = $estadoGenerado;
                    $mensajeExito = "Legajo generado correctamente.";
                } else {
                    if (function_exists('setAlert')) {
                        setAlert('warning', "El legajo se guardó, pero no se pudo generar el PDF unificado.");
                    }
                    header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . intval($idLegajo));
                    exit();
                }
            } else {
                $mensajeExito = "Legajo guardado, pero no había documentos cargados para unir.";
            }
        }

        if (function_exists('setAlert')) {
            if ($requiereRearmadoDespuesDeVerificar && !$esFinalizacion) {
                setAlert('warning', 'El legajo fue modificado y volvió a Completado. Debe generarlo nuevamente para actualizar el PDF.');
            } else {
                setAlert('success', $mensajeExito);
            }
        }
        unset($_SESSION['legajo_form_flash']);
        header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . intval($idLegajo));
        exit();
    }

    public function estado_legajo($indice_01 = '')
    {
        $data = ['indice_01' => $indice_01];
        $this->views->getView($this, "estado_legajo", $data);
    }

    public function descargar_pdf_final($idLegajo = 0)
    {
        $idLegajo = intval($idLegajo ?: ($_GET['id_legajo'] ?? 0));
        if ($idLegajo <= 0) {
            header("Location: " . base_url() . "legajos/armar_legajo");
            exit();
        }
        if (!$this->puedeAccederPdfLegajo()) {
            if (function_exists('setAlert')) {
                setAlert('warning', 'Tu rol no tiene permisos para abrir el PDF del legajo.');
            }
            header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
            exit();
        }
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/buscar_legajos');

        $archivo = $this->obtenerRutaPdfFinalLegajo($idLegajo);
        if (empty($archivo) || empty($archivo['ruta_absoluta']) || !file_exists($archivo['ruta_absoluta'])) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el PDF final del legajo.");
            }
            header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
            exit();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($archivo['nombre_archivo']) . '"');
        header('Content-Length: ' . filesize($archivo['ruta_absoluta']));
        readfile($archivo['ruta_absoluta']);
        exit();
    }

    public function ver_pdf_final($idLegajo = 0)
    {
        $idLegajo = intval($idLegajo ?: ($_GET['id_legajo'] ?? 0));
        if ($idLegajo <= 0) {
            header("Location: " . base_url() . "legajos/armar_legajo");
            exit();
        }
        if (!$this->puedeAccederPdfLegajo()) {
            if (function_exists('setAlert')) {
                setAlert('warning', 'Tu rol no tiene permisos para abrir el PDF del legajo.');
            }
            header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
            exit();
        }
        $this->asegurarAccesoLegajo($idLegajo, 'legajos/buscar_legajos');

        $archivo = $this->obtenerRutaPdfFinalLegajo($idLegajo);
        if (empty($archivo) || empty($archivo['ruta_absoluta']) || !file_exists($archivo['ruta_absoluta'])) {
            if (function_exists('setAlert')) {
                setAlert('warning', "No se encontró el PDF final del legajo.");
            }
            header("Location: " . base_url() . "legajos/armar_legajo?id_legajo=" . $idLegajo);
            exit();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($archivo['nombre_archivo']) . '"');
        header('Content-Length: ' . filesize($archivo['ruta_absoluta']));
        readfile($archivo['ruta_absoluta']);
        exit();
    }

    public function ver_documento_checklist()
    {
        $this->checkLegajoGroupPermission('armar_legajo');

        $idLegajo = intval($_GET['id_legajo'] ?? 0);
        $idRequisito = intval($_GET['id_requisito'] ?? 0);
        if ($idLegajo <= 0 || $idRequisito <= 0) {
            setAlert('warning', 'No se encontró el documento solicitado.');
            header('Location: ' . base_url() . 'legajos/armar_legajo');
            exit();
        }

        $this->asegurarAccesoLegajo($idLegajo, 'legajos/armar_legajo?id_legajo=' . $idLegajo);
        $documentos = $this->model->selectLegajoDocumentosPorLegajo($idLegajo);
        $documento = null;
        foreach ($documentos as $item) {
            if (intval($item['id_requisito'] ?? 0) === $idRequisito) {
                $documento = $item;
                break;
            }
        }

        $rutaRelativa = trim((string)($documento['ruta_archivo'] ?? ''));
        if ($rutaRelativa === '') {
            setAlert('warning', 'No se encontró el archivo cargado para este documento.');
            header('Location: ' . base_url() . 'legajos/armar_legajo?id_legajo=' . $idLegajo);
            exit();
        }

        $rutaBaseReal = realpath(rtrim(RUTA_BASE, '/\\'));
        $rutaArchivoReal = realpath(rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa));
        if ($rutaBaseReal === false || $rutaArchivoReal === false || strpos($rutaArchivoReal, $rutaBaseReal) !== 0 || !file_exists($rutaArchivoReal)) {
            setAlert('warning', 'El archivo solicitado no esta disponible.');
            header('Location: ' . base_url() . 'legajos/armar_legajo?id_legajo=' . $idLegajo);
            exit();
        }

        $extension = strtolower(pathinfo($rutaArchivoReal, PATHINFO_EXTENSION));
        $contentType = 'application/octet-stream';
        if ($extension === 'pdf') {
            $contentType = 'application/pdf';
        } elseif (in_array($extension, ['jpg', 'jpeg'], true)) {
            $contentType = 'image/jpeg';
        } elseif ($extension === 'png') {
            $contentType = 'image/png';
        }


        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . basename($rutaArchivoReal) . '"');
        header('Content-Length: ' . filesize($rutaArchivoReal));
        readfile($rutaArchivoReal);
        exit();
    }

    public function generar_pdf_texto()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['id'])) {
            echo json_encode(['ok' => false, 'error' => 'No autenticado.']);
            exit();
        }

        $tokenPost   = trim((string)($_POST['token'] ?? ''));
        $tokenSesion = trim((string)($_SESSION['csrf_token'] ?? ''));
        if ($tokenPost === '' || $tokenSesion === '' || $tokenPost !== $tokenSesion) {
            echo json_encode(['ok' => false, 'error' => 'Token inválido.']);
            exit();
        }

        $idLegajo    = intval($_POST['id_legajo'] ?? 0);
        $idRequisito = intval($_POST['id_requisito'] ?? 0);
        $valorCampo  = trim($_POST['valor_campo'] ?? '');
        $nombreCampo = trim($_POST['nombre_campo'] ?? 'Campo');

        if ($idLegajo <= 0 || $idRequisito <= 0 || $valorCampo === '') {
            echo json_encode(['ok' => false, 'error' => 'Datos insuficientes.']);
            exit();
        }

        $this->asegurarAccesoLegajo($idLegajo, '');
        $legajo = $this->model->selectLegajoPorId($idLegajo);
        if (empty($legajo)) {
            echo json_encode(['ok' => false, 'error' => 'Legajo no encontrado.']);
            exit();
        }

        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $rutaBaseDir = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseDir)) {
            @mkdir($rutaBaseDir, 0777, true);
        }

        $cedula = preg_replace('/[^0-9]+/', '', (string)($legajo['ci_socio'] ?? ''));
        if ($cedula === '') {
            $cedula = 'SINCI';
        }
        $sol       = preg_replace('/[^0-9A-Za-z]+/', '', (string)($legajo['nro_solicitud'] ?? ''));
        $slug      = preg_replace('/[^A-Za-z0-9]+/', '_', $nombreCampo);
        $slug      = strtoupper(trim($slug, '_'));
        $slug      = $slug !== '' ? $slug : 'CAMPO';
        $segmentos = ['DG', $slug, 'REQ' . $idRequisito, $cedula];
        if ($sol !== '') {
            $segmentos[] = $sol;
        }
        $nombreArchivo = implode('_', $segmentos) . '.pdf';
        $rutaFisica    = $rutaBaseDir . $nombreArchivo;
        $rutaRelativa  = 'Legajos/' . $idLegajo . '/' . $nombreArchivo;

        $campoPdf = [
            'documento_nombre' => $nombreCampo,
            'tipo_campo' => 'texto',
            'valor_campo' => $valorCampo,
        ];

        $rutaGenerada = $this->generarPdfCamposNoDocumento($legajo, [$campoPdf], $rutaBaseDir, $nombreArchivo);

        if ($rutaGenerada === null || !file_exists($rutaFisica)) {
            echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el PDF en el servidor.']);
            exit();
        }

        $documentoActual = null;
        foreach ($this->model->selectLegajoDocumentosPorLegajo($idLegajo) as $doc) {
            if (intval($doc['id_requisito'] ?? 0) === $idRequisito) {
                $documentoActual = $doc;
                break;
            }
        }
        $estadoAnterior = trim((string)($documentoActual['estado'] ?? 'pendiente'));
        $rutaAnterior   = trim((string)($documentoActual['ruta_archivo'] ?? ''));

        $this->model->actualizarLegajoDocumento(
            $idLegajo,
            $idRequisito,
            $rutaRelativa,
            null,
            $valorCampo,
            'cargado',
            null
        );

        $this->invalidarPdfFinalLegajo($idLegajo);

        $auditoria = $this->obtenerContextoAuditoria();
        if (method_exists($this->model, 'registrarLogLegajoDocumento')) {
            $this->model->registrarLogLegajoDocumento(
                $idLegajo,
                $idRequisito,
                'DOCUMENTO_GENERADO',
                'Se generó PDF desde campo de texto: ' . $nombreCampo,
                $rutaAnterior,
                $rutaRelativa,
                $estadoAnterior,
                'cargado',
                null,
                $auditoria['id_usuario'],
                $auditoria['nombre_host'],
                $auditoria['ip_host'],
                null
            );
        }

        echo json_encode([
            'ok'             => true,
            'ruta_relativa'  => $rutaRelativa,
            'nombre_archivo' => $nombreArchivo,
        ]);
        exit();
    }

    private function persistirBorradorFormularioExterno(array $formulario, array $matriz): array
    {

        $idFormulario = intval($formulario['id_formulario'] ?? 0);
        $ciSocio = trim((string)($_POST['ci_socio'] ?? ($formulario['ci_validacion'] ?? '')));
        $nombreCompleto = trim((string)($_POST['nombre_socio'] ?? ($formulario['nombre_referencia'] ?? '')));
        $nroSolicitud = trim((string)($_POST['nro_solicitud'] ?? ($formulario['nro_solicitud_referencia'] ?? '')));

        $this->model->actualizarDatosFormularioExterno($idFormulario, $nombreCompleto, $nroSolicitud);

        $documentosExistentes = [];
        foreach ($this->model->obtenerDocumentosFormularioExterno($idFormulario) as $documentoExistente) {
            $documentosExistentes[intval($documentoExistente['id_requisito'] ?? 0)] = $documentoExistente;
        }

        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0) {
                continue;
            }

            $esCampoDocumento = $this->esCampoDocumento($regla);
            $documentoExistente = $documentosExistentes[$idRequisito] ?? [];
            $rutaArchivo = trim((string)($documentoExistente['ruta_archivo'] ?? ''));
            $valorCampo = $esCampoDocumento
                ? ''
                : $this->obtenerValorCampoDesdePost($idRequisito, $regla, $documentoExistente['valor_campo'] ?? '');
            $fechaExpedicion = trim((string)($_POST['fecha_expedicion_' . $idRequisito] ?? ($documentoExistente['fecha_expedicion'] ?? '')));
            $observacion = trim((string)($_POST['observacion_' . $idRequisito] ?? ($documentoExistente['observacion'] ?? '')));
            $fechaVencimiento = $fechaExpedicion !== '' ? $this->calcularFechaVencimiento($fechaExpedicion, $regla) : null;
            $marcarEliminar = $esCampoDocumento && intval($_POST['eliminar_archivo_' . $idRequisito] ?? 0) === 1;

            if (!$esCampoDocumento) {
                $fechaExpedicion = '';
                $fechaVencimiento = null;
            }

            if ($marcarEliminar && $rutaArchivo !== '') {
                $this->eliminarArchivoRelativoSiExiste($rutaArchivo);
                $rutaArchivo = '';
            }

            $fileKey = 'doc_' . $idRequisito;
            if ($esCampoDocumento && isset($_FILES[$fileKey])) {
                $hayArchivo = false;
                $errorArchivo = $_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE;
                if (is_array($errorArchivo)) {
                    foreach ($errorArchivo as $errorItem) {
                        if (intval($errorItem) === UPLOAD_ERR_OK) {
                            $hayArchivo = true;
                            break;
                        }
                    }
                } else {
                    $hayArchivo = intval($errorArchivo) === UPLOAD_ERR_OK;
                }

                if ($hayArchivo) {
                    if ($rutaArchivo !== '') {
                        $this->eliminarArchivoRelativoSiExiste($rutaArchivo);
                    }
                    $rutaNueva = $this->guardarArchivoTemporalFormularioExterno(
                        trim((string)($formulario['token'] ?? '')),
                        $regla,
                        $idRequisito,
                        $_FILES[$fileKey],
                        $ciSocio,
                        $nroSolicitud
                    );
                    if ($rutaNueva !== null) {
                        $rutaArchivo = $rutaNueva;
                    }
                }
            }

            if ($rutaArchivo === '' && trim((string)$valorCampo) === '' && $fechaExpedicion === '' && $observacion === '') {
                $this->model->eliminarDocumentoFormularioExterno($idFormulario, $idRequisito);
                continue;
            }

            $this->model->guardarDocumentoFormularioExterno(
                $idFormulario,
                $idRequisito,
                $rutaArchivo,
                $valorCampo,
                $fechaExpedicion,
                $fechaVencimiento,
                $observacion
            );
        }

        $documentosActualizados = [];
        foreach ($this->model->obtenerDocumentosFormularioExterno($idFormulario) as $documento) {
            $documentosActualizados[intval($documento['id_requisito'] ?? 0)] = $documento;
        }

        $venceEn = trim((string)($formulario['vence_en'] ?? ''));
        $borradorHasta = null;
        if ($venceEn !== '') {
            try {
                $fechaBorrador = new DateTime($venceEn);
                $fechaBorrador->modify('+1 day');
                $borradorHasta = $fechaBorrador->format('Y-m-d H:i:s');
            } catch (Throwable $e) {
                $borradorHasta = null;
            }
        }
        $this->model->marcarBorradorFormularioExterno($idFormulario, $borradorHasta);

        return $documentosActualizados;
    }

    private function validarFormularioExternoParaEnvio(array $matriz, array $documentosFormulario): ?string
    {
        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0) {
                continue;
            }

            $documento = $documentosFormulario[$idRequisito] ?? [];
            $rutaArchivo = trim((string)($documento['ruta_archivo'] ?? ''));
            $valorCampo = trim((string)($documento['valor_campo'] ?? ''));
            $fechaExpedicion = trim((string)($documento['fecha_expedicion'] ?? ''));

            if (!$this->esCampoDocumento($regla)) {
                if (!empty($regla['es_obligatorio']) && !$this->campoValorCompleto($regla, $valorCampo)) {
                    $nombreDocumento = trim((string)($regla['documento_nombre'] ?? 'el campo requerido'));
                    return 'Debes completar ' . $nombreDocumento . ' antes de enviar el formulario.';
                }
                continue;
            }

            if ($rutaArchivo !== '' && $this->requiereFechaExpedicion($regla) && $fechaExpedicion === '') {
                $nombreDocumento = trim((string)($regla['documento_nombre'] ?? 'el documento requerido'));
                return 'Debes completar la fecha de expedición de ' . $nombreDocumento . ' antes de enviar el formulario.';
            }
        }

        return null;
    }

    private function materializarFormularioExterno(array $formulario, array $matriz, array $documentosFormulario): int
    {
        $idFormulario = intval($formulario['id_formulario'] ?? 0);
        $idLegajoBase = intval($formulario['id_legajo_base'] ?? 0);
        $modoCarga = strtolower(trim((string)($formulario['modo_carga'] ?? 'nuevo')));
        $idTipoLegajo = intval($formulario['id_tipo_legajo'] ?? 0);
        $ciSocio = trim((string)($_POST['ci_socio'] ?? ($formulario['ci_validacion'] ?? '')));
        $nombreCompleto = trim((string)($_POST['nombre_socio'] ?? ($formulario['nombre_referencia'] ?? '')));
        $nroSolicitud = trim((string)($_POST['nro_solicitud'] ?? ($formulario['nro_solicitud_referencia'] ?? '')));
        $idUsuario = intval($formulario['creado_por'] ?? 0);

        if ($idTipoLegajo <= 0 || $ciSocio === '' || $nombreCompleto === '' || $idUsuario <= 0) {
            return 0;
        }

        $tipoLegajoActual = $this->model->selectTipoLegajoPorId($idTipoLegajo);
        $requiereNroSolicitud = !empty($tipoLegajoActual['requiere_nro_solicitud']);
        if ($requiereNroSolicitud && $nroSolicitud === '') {
            return 0;
        }

        $idLegajo = 0;
        if ($modoCarga === 'completar' && $idLegajoBase > 0) {
            $legajoBase = $this->model->selectLegajoPorId($idLegajoBase);
            if (empty($legajoBase) || in_array(strtolower(trim((string)($legajoBase['estado'] ?? ''))), ['cerrado', 'aprobado'], true)) {
                return 0;
            }

            $this->model->actualizarLegajo($idLegajoBase, $idTipoLegajo, $ciSocio, $nombreCompleto, $nroSolicitud, 'borrador');
            $idLegajo = $idLegajoBase;
        } else {
            if ($requiereNroSolicitud && $this->model->existeSolicitudDuplicada($nroSolicitud, 0)) {
                return 0;
            }
            if (!$requiereNroSolicitud && $this->model->existeLegajoDuplicadoSinSolicitud($idTipoLegajo, $ciSocio, 0)) {
                return 0;
            }

            $idLegajo = intval($this->model->insertarLegajo($idTipoLegajo, $ciSocio, $nombreCompleto, $nroSolicitud, $idUsuario, 'borrador'));
        }

        if ($idLegajo <= 0) {
            return 0;
        }

        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0 || $this->model->existeLegajoDocumento($idLegajo, $idRequisito)) {
                continue;
            }
            $this->model->insertarLegajoDocumento(
                $idLegajo,
                $idRequisito,
                intval($regla['id_documento_maestro'] ?? 0),
                trim((string)($regla['rol_vinculado'] ?? 'TITULAR')),
                !empty($regla['es_obligatorio']) ? 1 : 0,
                'pendiente'
            );
        }

        $documentosExistentes = [];
        foreach ($this->model->selectLegajoDocumentosPorLegajo($idLegajo) as $documentoExistente) {
            $documentosExistentes[intval($documentoExistente['id_requisito'] ?? 0)] = $documentoExistente;
        }

        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }

        $rutaBaseLegajos = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajos)) {
            @mkdir($rutaBaseLegajos, 0777, true);
        }

        foreach ($matriz as $regla) {
            $idRequisito = intval($regla['id_requisito'] ?? 0);
            if ($idRequisito <= 0) {
                continue;
            }

            $documentoFormulario = $documentosFormulario[$idRequisito] ?? [];
            $documentoLegajo = $documentosExistentes[$idRequisito] ?? [];
            $esCampoDocumento = $this->esCampoDocumento($regla);
            $rutaArchivoExistente = trim((string)($documentoLegajo['ruta_archivo'] ?? ''));
            $rutaArchivoTemporal = trim((string)($documentoFormulario['ruta_archivo'] ?? ''));
            $valorCampo = $esCampoDocumento ? null : trim((string)($documentoFormulario['valor_campo'] ?? ($documentoLegajo['valor_campo'] ?? '')));
            $fechaExpedicion = trim((string)($documentoFormulario['fecha_expedicion'] ?? ''));
            $fechaVencimiento = trim((string)($documentoFormulario['fecha_vencimiento'] ?? ($documentoLegajo['fecha_vencimiento'] ?? '')));
            $observacion = trim((string)($documentoFormulario['observacion'] ?? ($documentoLegajo['observacion'] ?? '')));
            $rutaFinal = $esCampoDocumento ? $rutaArchivoExistente : '';

            if ($esCampoDocumento && $rutaArchivoTemporal !== '') {
                $rutaTemporalFisica = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaArchivoTemporal);
                if (is_file($rutaTemporalFisica)) {
                    $codigoDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', trim((string)($regla['codigo_interno'] ?? ('DOC' . $idRequisito))));
                    $rolDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', strtoupper(trim((string)($regla['rol_vinculado'] ?? 'TITULAR'))));
                    if ($rolDocumento === '') {
                        $rolDocumento = 'TITULAR';
                    }
                    $cedula = $this->normalizarCedula($ciSocio);
                    if ($cedula === '') {
                        $cedula = 'SINCI';
                    }
                    $solicitud = preg_replace('/[^0-9A-Za-z]+/', '', (string)$nroSolicitud);
                    $segmentos = [$codigoDocumento, $rolDocumento, 'REQ' . $idRequisito, $cedula];
                    if ($solicitud !== '') {
                        $segmentos[] = $solicitud;
                    }
                    $nombreArchivo = implode('_', $segmentos) . '.pdf';
                    $rutaFisicaNueva = $rutaBaseLegajos . $nombreArchivo;
                    @copy($rutaTemporalFisica, $rutaFisicaNueva);
                    $rutaRelativaNueva = 'Legajos/' . $idLegajo . '/' . $nombreArchivo;

                    $politicaActualizacion = strtoupper(trim((string)($regla['politica_actualizacion'] ?? '')));
                    if ($politicaActualizacion === '') {
                        $politicaActualizacion = !empty($regla['permite_reemplazo']) ? 'REEMPLAZAR' : 'NO_PERMITIR';
                    }

                    if ($rutaArchivoExistente !== '' && $politicaActualizacion === 'NO_PERMITIR') {
                        @unlink($rutaFisicaNueva);
                    } elseif ($rutaArchivoExistente !== '' && in_array($politicaActualizacion, ['UNIR_AL_INICIO', 'UNIR_AL_FINAL'], true)) {
                        $rutaFisicaExistente = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaArchivoExistente);
                        $rutaUnida = $this->unirSegunPolitica($politicaActualizacion, $rutaFisicaNueva, $rutaFisicaExistente, $rutaBaseLegajos, $idLegajo);
                        if ($rutaUnida !== null) {
                            $rutaFinal = $rutaUnida;
                        }
                    } else {
                        $rutaFinal = $rutaRelativaNueva;
                    }
                }
            }

            if ($esCampoDocumento && $fechaVencimiento === '' && $fechaExpedicion !== '') {
                $fechaVencimiento = (string)$this->calcularFechaVencimiento($fechaExpedicion, $regla);
            }
            if (!$esCampoDocumento) {
                $fechaVencimiento = '';
            }

            $estadoDocumento = $this->resolverEstadoDocumentoPorRegla($regla, $rutaFinal, $fechaVencimiento, $valorCampo);
            $estadoDocumentoGuardar = $estadoDocumento === 'por_vencer' ? 'cargado' : $estadoDocumento;
            $this->model->actualizarLegajoDocumento(
                $idLegajo,
                $idRequisito,
                $rutaFinal,
                $fechaVencimiento !== '' ? $fechaVencimiento : null,
                $valorCampo,
                $estadoDocumentoGuardar,
                $observacion
            );
        }

        $legajoDocumentosActualizados = $this->model->selectLegajoDocumentosPorLegajo($idLegajo);
        $estadoLegajo = $this->resolverEstadoLegajo($matriz, $legajoDocumentosActualizados);
        $this->model->actualizarEstadoLegajo($idLegajo, $estadoLegajo, false);
        $this->model->marcarFormularioExternoEnviado($idFormulario);
        $this->revocarTokenFormularioExterno(trim((string)($formulario['token'] ?? '')));

        return $idLegajo;
    }

    public function formularios_externos()
    {
        $idLegajoBase = intval($_GET['id_legajo'] ?? 0);
        header('Location: ' . base_url() . 'legajos/solicitar_documentos' . ($idLegajoBase > 0 ? '?id_legajo=' . $idLegajoBase : ''));
        exit();
    }

    public function solicitar_documentos()
    {
        $this->checkLegajoGroupPermission('armar_legajo');
        $this->asegurarTokenCsrfPublico();

        $tipos_legajo = $this->model->selectTiposLegajo();
        $tiposPermitidos = $this->obtenerTiposLegajoPermitidosRolActual();
        if ($this->rolActualTieneFiltroTiposLegajo()) {
            $tipos_legajo = array_values(array_filter($tipos_legajo, static function ($tipo) use ($tiposPermitidos) {
                return in_array(intval($tipo['id_tipo_legajo'] ?? 0), $tiposPermitidos, true);
            }));
        }

        $idLegajoBase = intval($_GET['id_legajo'] ?? 0);
        $legajoBase = [];
        if ($idLegajoBase > 0) {
            $this->asegurarAccesoLegajo($idLegajoBase, 'legajos/formularios_externos');
            $legajoBase = $this->model->selectLegajoPorId($idLegajoBase);
        }

        $data = [
            'tipos_legajo' => $tipos_legajo,
            'legajo_base' => $legajoBase,
            'formularios' => $this->model->listarFormulariosExternos(intval($_SESSION['id'] ?? 0), $idLegajoBase),
            'id_legajo_base' => $idLegajoBase,
            'link_generado' => $_SESSION['formulario_externo_link_generado'] ?? '',
        ];
        unset($_SESSION['formulario_externo_link_generado']);
        $this->views->getView($this, 'formularios_externos', $data);
    }

    public function generar_formulario_externo()
    {
        $this->checkLegajoGroupPermission('armar_legajo');
        if (!Validador::csrfValido()) {
            header('Location: ' . base_url() . 'legajos/solicitar_documentos?error=csrf');
            exit();
        }

        $idLegajoBase = intval($_POST['id_legajo_base'] ?? 0);
        $idTipoLegajo = intval($_POST['id_tipo_legajo'] ?? 0);
        $tipoDestinatario = 'cliente';
        $modoCarga = 'nuevo';
        $ciValidacion = $this->normalizarCedula((string)($_POST['ci_validacion'] ?? ''));
        $nombreReferencia = trim((string)($_POST['nombre_referencia'] ?? ''));
        $nroSolicitudReferencia = trim((string)($_POST['nro_solicitud_referencia'] ?? ''));
        $horasVigencia = intval($_POST['horas_vigencia'] ?? 1);

        if ($idLegajoBase > 0) {
            $this->asegurarAccesoLegajo($idLegajoBase, 'legajos/formularios_externos');
            $legajoBase = $this->model->selectLegajoPorId($idLegajoBase);
            if (!empty($legajoBase)) {
                if ($idTipoLegajo <= 0) {
                    $idTipoLegajo = intval($legajoBase['id_tipo_legajo'] ?? 0);
                }
                if ($ciValidacion === '') {
                    $ciValidacion = $this->normalizarCedula((string)($legajoBase['ci_socio'] ?? ''));
                }
                if ($nombreReferencia === '') {
                    $nombreReferencia = trim((string)($legajoBase['nombre_completo'] ?? ''));
                }
                if ($nroSolicitudReferencia === '') {
                    $nroSolicitudReferencia = trim((string)($legajoBase['nro_solicitud'] ?? ''));
                }
            }
        }

        if (!in_array($horasVigencia, [1, 3, 24], true)) {
            $horasVigencia = 1;
        }

        if ($idTipoLegajo <= 0 || $ciValidacion === '') {
            setAlert('warning', 'Debes indicar tipo de legajo y cédula para generar el link.');
            header('Location: ' . base_url() . 'legajos/solicitar_documentos' . ($idLegajoBase > 0 ? '?id_legajo=' . $idLegajoBase : ''));
            exit();
        }

        $tipoLegajoActual = $this->model->selectTipoLegajoPorId($idTipoLegajo);
        $requiereNroSolicitud = !empty($tipoLegajoActual['requiere_nro_solicitud']);
        if (!$requiereNroSolicitud && $this->model->existeLegajoDuplicadoSinSolicitud($idTipoLegajo, $ciValidacion, 0)) {
            setAlert('warning', 'Ya existe un legajo de ese tipo para esa cédula. No se puede generar otro link.');
            header('Location: ' . base_url() . 'legajos/solicitar_documentos' . ($idLegajoBase > 0 ? '?id_legajo=' . $idLegajoBase : ''));
            exit();
        }

        $venceEn = (new DateTime())->modify('+' . $horasVigencia . ' hours')->format('Y-m-d H:i:s');
        $token = bin2hex(random_bytes(24));
        $idFormulario = $this->model->insertarFormularioExterno(
            $token,
            $tipoDestinatario,
            $modoCarga,
            $idTipoLegajo,
            $idLegajoBase > 0 ? $idLegajoBase : null,
            $ciValidacion,
            $nombreReferencia,
            $nroSolicitudReferencia,
            $horasVigencia,
            $venceEn,
            intval($_SESSION['id'] ?? 0)
        );

        if (!$idFormulario) {
            setAlert('error', 'No se pudo generar el link externo.');
            header('Location: ' . base_url() . 'legajos/solicitar_documentos' . ($idLegajoBase > 0 ? '?id_legajo=' . $idLegajoBase : ''));
            exit();
        }

        $_SESSION['formulario_externo_link_generado'] = $this->construirUrlFormularioExterno($token);
        setAlert('success', 'Link externo generado correctamente.');
        header('Location: ' . base_url() . 'legajos/solicitar_documentos' . ($idLegajoBase > 0 ? '?id_legajo=' . $idLegajoBase : ''));
        exit();
    }

    public function formulario_externo()
    {
        $this->asegurarTokenCsrfPublico();
        $token = trim((string)($_GET['token'] ?? $_POST['token_formulario'] ?? ((isset($_POST['cedula_acceso']) ? ($_POST['token'] ?? '') : ''))));
        $formulario = $token !== '' ? $this->model->obtenerFormularioExternoPorToken($token) : [];
        $formularioDisponible = $this->formularioExternoDisponible($formulario);
        if ($formularioDisponible && strtolower(trim((string)($formulario['estado'] ?? ''))) === 'vencido') {
            $this->model->actualizarFormularioExternoEstado(intval($formulario['id_formulario'] ?? 0), 'activo');
            $formulario['estado'] = 'activo';
        }
        $errorAcceso = '';
        $autorizado = $token !== '' && $this->tokenFormularioExternoValido($token);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula_acceso'])) {
            $cedulaIngresada = $this->normalizarCedula((string)($_POST['cedula_acceso'] ?? ''));
            $cedulaEsperada = $this->normalizarCedula((string)($formulario['ci_validacion'] ?? ''));

            if (empty($formulario)) {
                $errorAcceso = 'El enlace no es válido.';
            } elseif (!in_array(strtolower(trim((string)($formulario['estado'] ?? ''))), ['activo', 'borrador'], true)) {
                $errorAcceso = 'El enlace ya no está disponible.';
            } elseif ($cedulaIngresada === '' || $cedulaEsperada === '' || $cedulaIngresada !== $cedulaEsperada) {
                $errorAcceso = 'La cédula no coincide con la registrada para este enlace.';
            } else {
                $autorizado = true;
                $this->autorizarTokenFormularioExterno($token, 3600);
                $this->model->marcarAccesoFormularioExterno(intval($formulario['id_formulario'] ?? 0));
            }
        }

        $matriz = !empty($formulario) ? $this->model->obtenerMatrizLegajoPorTipo(intval($formulario['id_tipo_legajo'] ?? 0)) : [];
        $documentos = [];
        foreach ($this->model->obtenerDocumentosFormularioExterno(intval($formulario['id_formulario'] ?? 0)) as $documento) {
            $documentos[intval($documento['id_requisito'] ?? 0)] = $documento;
        }

        $mensajeFlash = $_SESSION['formulario_externo_mensaje'] ?? null;
        unset($_SESSION['formulario_externo_mensaje']);

        require_once 'Config/Config.php';
        $baseUrl = base_url();
        $formularioDisponible = $this->formularioExternoDisponible($formulario);
        include 'Views/legajos/formulario_externo.php';
    }

    public function guardar_borrador_formulario_externo()
    {
        $this->asegurarTokenCsrfPublico();
        if (!Validador::csrfValido()) {
            header('Location: ' . base_url() . 'legajos/formulario_externo?error=csrf');
            exit();
        }

        $token = trim((string)($_POST['token_formulario'] ?? ''));
        $formulario = $this->model->obtenerFormularioExternoPorToken($token);
        if (empty($formulario) || !$this->tokenFormularioExternoValido($token)) {
            header('Location: ' . $this->construirUrlFormularioExterno($token));
            exit();
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($formulario['id_tipo_legajo'] ?? 0));
        $this->persistirBorradorFormularioExterno($formulario, $matriz);
        $_SESSION['formulario_externo_mensaje'] = ['type' => 'success', 'action' => 'draft', 'message' => 'Borrador guardado correctamente.'];
        header('Location: ' . $this->construirUrlFormularioExterno($token));
        exit();
    }

    public function enviar_formulario_externo()
    {
        $this->asegurarTokenCsrfPublico();
        if (!Validador::csrfValido()) {
            header('Location: ' . base_url() . 'legajos/formulario_externo?error=csrf');
            exit();
        }

        $token = trim((string)($_POST['token_formulario'] ?? ''));
        $formulario = $this->model->obtenerFormularioExternoPorToken($token);
        if (empty($formulario) || !$this->tokenFormularioExternoValido($token)) {
            header('Location: ' . $this->construirUrlFormularioExterno($token));
            exit();
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($formulario['id_tipo_legajo'] ?? 0));
        $documentos = $this->persistirBorradorFormularioExterno($formulario, $matriz);
        $errorValidacion = $this->validarFormularioExternoParaEnvio($matriz, $documentos);
        if ($errorValidacion !== null) {
            $_SESSION['formulario_externo_mensaje'] = ['type' => 'error', 'message' => $errorValidacion];
            header('Location: ' . $this->construirUrlFormularioExterno($token));
            exit();
        }
        $idLegajo = $this->materializarFormularioExterno($formulario, $matriz, $documentos);

        if ($idLegajo <= 0) {
            $_SESSION['formulario_externo_mensaje'] = ['type' => 'error', 'message' => 'No se pudo enviar el formulario. Verifica los datos obligatorios o si ya existe un legajo duplicado.'];
            header('Location: ' . $this->construirUrlFormularioExterno($token));
            exit();
        }

        $_SESSION['formulario_externo_mensaje'] = ['type' => 'success', 'action' => 'sent', 'message' => 'Formulario enviado correctamente.'];
        header('Location: ' . base_url() . 'legajos/confirmacion_formulario_externo');
        exit();
    }

    public function confirmacion_formulario_externo()
    {
        $this->asegurarTokenCsrfPublico();
        $mensajeFlash = $_SESSION['formulario_externo_mensaje'] ?? null;
        unset($_SESSION['formulario_externo_mensaje']);

        include 'Views/legajos/confirmacion_formulario_externo.php';
    }

    public function log_legajos()
    {
        $this->checkLegajoGroupPermission('log_legajos');
        $data = [
            'logs_legajos' => $this->model->selectLogLegajos()
        ];
        $this->views->getView($this, "log_legajos", $data);
    }
}
