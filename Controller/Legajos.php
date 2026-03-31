<?php
use setasign\Fpdi\Fpdi;

class Legajos extends Controllers
{
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
        if ($numeroSolicitud === '') {
            $numeroSolicitud = 'SINSOLICITUD';
        }

        $nombreFinal = $prefijoTipo . '_' . $cedulaLegajo . '_' . $numeroSolicitud . '_LEGAJO.pdf';
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
    }

    private function enriquecerResultadosConPdfFinal(array $resultados): array
    {
        foreach ($resultados as &$resultado) {
            $idLegajo = intval($resultado['id_legajo'] ?? 0);
            $archivoPdf = $idLegajo > 0 ? $this->obtenerRutaPdfFinalLegajo($idLegajo) : null;
            $resultado['pdf_final_disponible'] = !empty($archivoPdf['ruta_absoluta']) && is_file($archivoPdf['ruta_absoluta']);
        }
        unset($resultado);

        return $resultados;
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

    private function crearInstanciaPdf()
    {
        require_once 'Libraries/pdf/fpdf.php';
        require_once 'Libraries/fpdi/src/autoload.php';

        if (class_exists('\setasign\Fpdi\Fpdi')) {
            return new \setasign\Fpdi\Fpdi();
        }

        return new Fpdi();
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

    private function unirSegunPolitica(string $politicaActualizacion, string $rutaFisicaNueva, string $rutaFisicaExistente, string $rutaBaseLegajos, int $idLegajo): ?string
    {
        if (!is_file($rutaFisicaNueva) || !is_file($rutaFisicaExistente)) {
            return null;
        }

        $nombreArchivoBase = pathinfo($rutaFisicaNueva, PATHINFO_FILENAME);
        $nombreArchivoUnido = $nombreArchivoBase . '_unido.pdf';
        $rutaFisicaUnida = $rutaBaseLegajos . $nombreArchivoUnido;
        $rutas = $politicaActualizacion === 'UNIR_AL_FINAL'
            ? [$rutaFisicaExistente, $rutaFisicaNueva]
            : [$rutaFisicaNueva, $rutaFisicaExistente];

        $unionExitosa = $this->unirPdfsSimple($rutas, $rutaFisicaUnida);
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

        $fecha->modify('+' . $diasVigenciaBase . ' days');
        return $fecha->format('Y-m-d');
    }

    private function requiereFechaExpedicion(array $regla): bool
    {
        return !empty($regla['tiene_vencimiento']);
    }

    private function resolverEstadoDocumentoPorRegla(array $regla, ?string $rutaArchivo, ?string $fechaVencimiento): string
    {
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
                $documento['fecha_vencimiento'] ?? null
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

        return 'finalizado';
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
            case 'finalizado':
                return 'Completado';
            case 'activo':
                return 'Vencido';
            default:
                return 'Incompleto';
        }
    }

    private function agregarCaratulaLegajo($pdf, array $legajo, array $reglas, int $cantidadDocumentosCargados, string $usuarioGenerador): void
    {
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

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
        $pdf->Cell(0, 12, utf8_decode($titulo), 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(0, 6, utf8_decode('Generación: ' . date('d/m/Y H:i')), 0, 1, 'R');
        $pdf->Cell(0, 6, utf8_decode('Generado por: ' . trim($usuarioGenerador)), 0, 1, 'R');
        $estadoLegajo = strtolower(trim((string)($legajo['estado'] ?? '')));
        $fechaVerificacion = trim((string)($legajo['fecha_cierre'] ?? ''));
        $verificadoPor = '';
        if ($estadoLegajo === 'verificado' || $estadoLegajo === 'cerrado') {
            $verificadoPor = trim((string)($legajo['nombre_usuario_armado'] ?? $usuarioGenerador));
        }
        if ($fechaVerificacion !== '' && $verificadoPor === '') {
            $verificadoPor = trim((string)($legajo['nombre_usuario_armado'] ?? $legajo['nombre_usuario_creador'] ?? $usuarioGenerador));
        }
        $pdf->Cell(0, 6, utf8_decode('Verificación: ' . ($fechaVerificacion !== '' ? date('d/m/Y H:i', strtotime($fechaVerificacion)) : '---')), 0, 1, 'R');
        $pdf->Cell(0, 6, utf8_decode('Verificado por: ' . ($verificadoPor !== '' ? $verificadoPor : '---')), 0, 1, 'R');

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
        $pdf->Cell($valueWLeft, $rowH, utf8_decode(substr($legajo['nombre_completo'] ?? '', 0, 34)), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelWRight, $rowH, 'Solicitud:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWRight, $rowH, utf8_decode(substr((string)($legajo['nro_solicitud'] ?? ''), 0, 18)), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, $rowH, 'CI:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWLeft, $rowH, utf8_decode(substr((string)($legajo['ci_socio'] ?? ''), 0, 25)), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelWRight, $rowH, 'Estado:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($valueWRight, $rowH, utf8_decode($this->obtenerTextoEstadoLegajo($legajo['estado'] ?? '')), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell($labelW, $rowH, 'Tipo de legajo:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(131, $rowH, utf8_decode(substr($legajo['nombre_tipo_legajo'] ?? '', 0, 55)), 0, 1);

        $pdf->SetX($x);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, $rowH, 'Documentos cargados:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(18, $rowH, strval($cantidadDocumentosCargados), 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, $rowH, 'Checklist total:', 0, 0);
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(18, $rowH, strval(count($reglas)), 0, 1);

        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(24, 37, 65);
        $pdf->Cell(0, 8, utf8_decode('Checklist de verificación manual'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->MultiCell(0, 6, utf8_decode('Marque manualmente cada documento verificado en la revision fisica del legajo.'));
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
            $pdf->Cell(88, 8, utf8_decode($regla['documento_nombre'] ?? ''), 1, 0, 'L');
            $pdf->Cell(35, 8, utf8_decode($regla['rol_vinculado'] ?? ''), 1, 0, 'L');
            $pdf->Cell(35, 8, $tipo, 1, 1, 'L');
        }

        $pdf->SetY(248);
        $pdf->SetDrawColor(120, 120, 120);
        $pdf->Line(115, 260, 190, 260);
        $pdf->SetXY(115, 262);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell(75, 6, utf8_decode('Verificado por:'), 0, 1, 'L');
    }

    private function unirArchivosLegajo(array $documentos, string $rutaDestino, string $titulo, string $usuario, array $legajo = [], array $reglas = []): bool
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

        $this->agregarCaratulaLegajo($pdf, $legajo, $reglas, count($documentos), $usuario);
        $agregoPaginas = true;

        foreach ($documentos as $documento) {
            $rutaRelativa = trim((string)($documento['ruta_archivo'] ?? ''));
            if ($rutaRelativa === '') {
                continue;
            }

            $rutaAbsoluta = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rutaRelativa);
            if (!file_exists($rutaAbsoluta)) {
                continue;
            }

            $extension = strtolower(pathinfo($rutaAbsoluta, PATHINFO_EXTENSION));

            try {
                if ($extension === 'pdf') {
                    $pageCount = $pdf->setSourceFile($rutaAbsoluta);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $tpl = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($tpl);
                        $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                        $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                        $pdf->useTemplate($tpl);
                        $agregoPaginas = true;
                    }
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
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
                            imagedestroy($src);
                            imagedestroy($dst);
                            $ok = true;
                        }
                    } elseif (in_array($extension, ['jpg', 'jpeg'], true) && function_exists('imagecreatefromjpeg')) {
                        $src = @imagecreatefromjpeg($rutaAbsoluta);
                        if ($src) {
                            imagejpeg($src, $tempJpg, 90);
                            imagedestroy($src);
                            $ok = true;
                        }
                    }

                    if (!$ok && copy($rutaAbsoluta, $tempJpg)) {
                        $ok = true;
                    }

                    if ($ok && file_exists($tempJpg)) {
                        [$wp, $hp] = getimagesize($tempJpg);
                        $mm = 25.4;
                        $dpi = 96;
                        $wm = ($wp * $mm) / $dpi;
                        $hm = ($hp * $mm) / $dpi;
                        $orientation = ($wm > $hm) ? 'L' : 'P';
                        $pdf->AddPage($orientation, [$wm, $hm]);
                        $pdf->Image($tempJpg, 0, 0, $wm, $hm);
                        unlink($tempJpg);
                        $agregoPaginas = true;
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
        return file_exists($rutaDestino);
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
        if (empty($documentosParaUnir)) {
            return false;
        }

        $matriz = $this->model->obtenerMatrizLegajoPorTipo(intval($legajoActual['id_tipo_legajo'] ?? 0));
        $rutaBaseLegajos = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos' . DIRECTORY_SEPARATOR . $idLegajo . DIRECTORY_SEPARATOR;
        if (!is_dir($rutaBaseLegajos)) {
            mkdir($rutaBaseLegajos, 0777, true);
        }

        $nombreFinal = $this->construirNombrePdfFinalLegajo($legajoActual, $idLegajo);
        $rutaFinal = $rutaBaseLegajos . $nombreFinal;

        return $this->unirArchivosLegajo($documentosParaUnir, $rutaFinal, 'Legajo ' . $idLegajo, $usuario, $legajoActual, $matriz);
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

    public function __construct()
    {
        // CORRECCIÓN: verificar antes de iniciar para evitar "session already started"
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
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
                    $legajo_documento['fecha_vencimiento'] ?? null
                );
            }
            unset($legajo_documento);
            if (!empty($legajo) && !in_array(($legajo['estado'] ?? ''), ['aprobado', 'verificado', 'cerrado'], true)) {
                $estadoLegajo = $this->resolverEstadoLegajo($matrizActual, $legajo_documentos);
                if (($legajo['estado'] ?? '') !== $estadoLegajo) {
                    $this->model->actualizarEstadoLegajo($id_legajo, $estadoLegajo, $estadoLegajo === 'finalizado');
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
                        $legajo_documento['fecha_vencimiento'] ?? null
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

        if (!empty($_SESSION['legajo_pdf_final_listo'])) {
            $pdf_final_listo = $_SESSION['legajo_pdf_final_listo'];
            unset($_SESSION['legajo_pdf_final_listo']);
        }

        $data = [
            'tipos_legajo' => $tipos_legajo,
            'matriz_legajo' => $matriz_legajo,
            'legajo' => $legajo,
            'legajo_documentos' => $legajo_documentos,
            'pdf_final_listo' => $pdf_final_listo,
            'buscar_legajo' => $buscar_legajo,
            'resultados_busqueda_legajo' => $resultados_busqueda_legajo,
            'duplicar_desde' => $duplicar_desde
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
        $resultados = $busquedaEjecutada
            ? $this->model->buscarLegajosPorTermino($termino, $estado_legajo, $id_tipo_legajo, $filtro_documentos, $idUsuarioActual, $soloPropios, $tiposPermitidos)
            : [];
        $resultados = $this->enriquecerResultadosConPdfFinal($resultados);

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
            : 'Completado';
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
        ];
        $this->views->getView($this, "administrar_legajos", $data);
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
        }

        $this->guardarFlashFormularioLegajo([
            'duplicado_desde' => $duplicadoDesde,
            'legajo' => [
                'id_legajo' => $idLegajo,
                'id_tipo_legajo' => $idTipoLegajo,
                'ci_socio' => $ciSocio,
                'nombre_completo' => $nombreCompleto,
                'nro_solicitud' => $nroSolicitud,
            ],
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
        $legajoEstabaVerificado = strtolower(trim((string)($legajoAntesGuardar['estado'] ?? ''))) === 'verificado';
        $legajoModificadoTrasVerificacion = false;

        if ($idLegajo > 0) {
            if (
                $legajoEstabaVerificado
                && (
                    intval($legajoAntesGuardar['id_tipo_legajo'] ?? 0) !== $idTipoLegajo
                    || trim((string)($legajoAntesGuardar['ci_socio'] ?? '')) !== $ciSocio
                    || trim((string)($legajoAntesGuardar['nombre_completo'] ?? '')) !== $nombreCompleto
                    || trim((string)($legajoAntesGuardar['nro_solicitud'] ?? '')) !== $nroSolicitud
                )
            ) {
                $legajoModificadoTrasVerificacion = true;
            }

            $actualizado = $this->model->actualizarLegajo(
                $idLegajo,
                $idTipoLegajo,
                $ciSocio,
                $nombreCompleto,
                $nroSolicitud,
                $estadoLegajo
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
                $estadoLegajo
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

            $marcadoEliminarArchivo = intval($_POST['eliminar_archivo_' . $idRequisito] ?? 0) === 1;
            $fechaExpedicion = trim($_POST['fecha_expedicion_' . $idRequisito] ?? '');
            $observacionDocumento = trim($_POST['observacion_' . $idRequisito] ?? '');
            $fechaVencimiento = !$marcadoEliminarArchivo && $fechaExpedicion !== ''
                ? $this->calcularFechaVencimiento($fechaExpedicion, $regla)
                : ($documentosExistentes[$idRequisito]['fecha_vencimiento'] ?? null);
            $rutaArchivoDuplicado = trim($_POST['ruta_existente_' . $idRequisito] ?? '');
            $fileKey = null;
            foreach ($_FILES as $inputName => $fileInfo) {
                if (strpos($inputName, 'doc_' . $idRequisito . '_') === 0) {
                    $fileKey = $inputName;
                    break;
                }
            }

            $rutaRelativa = null;
            $estadoDocumento = null;
            $rutaArchivoExistente = trim((string)($documentosExistentes[$idRequisito]['ruta_archivo'] ?? ''));
            if ($rutaArchivoExistente === '' && $rutaArchivoDuplicado !== '') {
                $rutaArchivoExistente = $rutaArchivoDuplicado;
            }

            if ($marcadoEliminarArchivo) {
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

            if ($fileKey !== null && isset($_FILES[$fileKey]) && intval($_FILES[$fileKey]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                if ($this->requiereFechaExpedicion($regla) && $fechaExpedicion === '') {
                    if (function_exists('setAlert')) {
                        setAlert('warning', 'Debe completar la fecha de expedicion antes de cargar el documento "' . trim((string)($regla['documento_nombre'] ?? '')) . '".');
                    }
                    header("Location: " . $this->construirUrlArmarLegajo(intval($idLegajo), $duplicadoDesde));
                    exit();
                }
                $archivo = $_FILES[$fileKey];
                $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
                $codigoDocumento = trim((string)($regla['codigo_interno'] ?? ''));
                if ($codigoDocumento === '') {
                    $codigoDocumento = 'DOC' . $idRequisito;
                }
                $codigoDocumento = preg_replace('/[^A-Za-z0-9_-]+/', '', $codigoDocumento);
                $cedulaLegajo = preg_replace('/[^0-9]+/', '', (string)($ciSocio ?? ($legajoAntesGuardar['ci_socio'] ?? '')));
                if ($cedulaLegajo === '') {
                    $cedulaLegajo = 'SINCI';
                }
                $numeroSolicitudArchivo = preg_replace('/[^0-9A-Za-z]+/', '', (string)($nroSolicitud ?? ($legajoAntesGuardar['nro_solicitud'] ?? '')));
                if ($numeroSolicitudArchivo === '') {
                    $numeroSolicitudArchivo = 'SINSOLICITUD';
                }
                $nombreArchivo = $codigoDocumento . '_' . $cedulaLegajo . '_' . $numeroSolicitudArchivo;
                if ($extension !== '') {
                    $nombreArchivo .= '.' . $extension;
                }
                $rutaFisica = $rutaBaseLegajos . $nombreArchivo;
                if (move_uploaded_file($archivo['tmp_name'], $rutaFisica)) {
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

            $rutaEstado = $rutaRelativa !== null ? $rutaRelativa : $rutaArchivoExistente;
            $fechaVencimientoEstado = $fechaVencimiento !== null
                ? $fechaVencimiento
                : ($documentosExistentes[$idRequisito]['fecha_vencimiento'] ?? null);
            $estadoDocumento = $this->resolverEstadoDocumentoPorRegla($regla, $rutaEstado, $fechaVencimientoEstado);
            $estadoDocumentoGuardar = $estadoDocumento === 'por_vencer' ? 'cargado' : $estadoDocumento;

            $rutaGuardar = $marcadoEliminarArchivo ? '' : $rutaRelativa;
            if ($rutaGuardar === null && empty($documentosExistentes[$idRequisito]['ruta_archivo']) && $rutaArchivoExistente !== '') {
                $rutaGuardar = $rutaArchivoExistente;
            }

            $documentoAnterior = $documentosExistentes[$idRequisito] ?? [];
            $rutaAnterior = trim((string)($documentoAnterior['ruta_archivo'] ?? ''));
            $estadoAnteriorDocumento = trim((string)($documentoAnterior['estado'] ?? 'pendiente'));
            $observacionAnteriorDocumento = trim((string)($documentoAnterior['observacion'] ?? ''));
            $fechaAnteriorDocumento = trim((string)($documentoAnterior['fecha_vencimiento'] ?? ''));
            $fechaVencimientoLog = $fechaVencimiento !== null
                ? $fechaVencimiento
                : trim((string)($documentoAnterior['fecha_vencimiento'] ?? ''));
            $rutaNuevaDocumento = trim((string)($rutaGuardar ?? $rutaArchivoExistente));
            $fechaNuevaDocumento = trim((string)($fechaVencimiento ?? $fechaAnteriorDocumento));

            if (
                $legajoEstabaVerificado
                && (
                    $marcadoEliminarArchivo
                    || $rutaRelativa !== null
                    || $rutaNuevaDocumento !== $rutaAnterior
                    || $fechaNuevaDocumento !== $fechaAnteriorDocumento
                )
            ) {
                $legajoModificadoTrasVerificacion = true;
            }

            $this->model->actualizarLegajoDocumento(
                intval($idLegajo),
                $idRequisito,
                $rutaGuardar,
                $fechaVencimiento,
                $estadoDocumentoGuardar,
                $observacionDocumento
            );

            if ($marcadoEliminarArchivo && $rutaAnterior !== '') {
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
        if ($requiereRearmadoDespuesDeVerificar) {
            $this->invalidarPdfFinalLegajo(intval($idLegajo));
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

        $mensajeExito = "Legajo guardado como borrador.";
        if ($esFinalizacion) {
            $this->model->actualizarUsuarioArmado(intval($idLegajo), $idUsuario);
            $documentosParaUnir = $this->model->obtenerDocumentosCargadosParaUnir(intval($idLegajo));
            if (!empty($documentosParaUnir)) {
                $legajoActual = $this->model->selectLegajoPorId(intval($idLegajo));
                $usuario = $_SESSION['nombre'] ?? 'Sistema';
                $nombreFinal = $this->construirNombrePdfFinalLegajo($legajoActual, intval($idLegajo));
                $rutaFinal = $rutaBaseLegajos . $nombreFinal;

                if ($this->unirArchivosLegajo($documentosParaUnir, $rutaFinal, 'Legajo ' . intval($idLegajo), $usuario, $legajoActual, $matriz)) {
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
                    $mensajeExito = "Legajo finalizado y PDF unificado generado.";
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
                setAlert('warning', 'El legajo fue modificado y volvió a Completado. Debe armarlo nuevamente para generar un PDF actualizado.');
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
            setAlert('warning', 'No se encontro el documento solicitado.');
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
            setAlert('warning', 'No se encontro el archivo cargado para este documento.');
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

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($rutaArchivoReal) . '"');
        header('Content-Length: ' . filesize($rutaArchivoReal));
        readfile($rutaArchivoReal);
        exit();
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

