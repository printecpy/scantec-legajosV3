<?php encabezado() ?>

<?php
$tipos_legajo = $data['tipos_legajo'] ?? [];
$matriz_legajo = $data['matriz_legajo'] ?? [];
$legajo = $data['legajo'] ?? [];
$legajo_documentos = $data['legajo_documentos'] ?? [];
$form_documentos = $data['form_documentos'] ?? [];
$pdf_final_listo = $data['pdf_final_listo'] ?? null;
$buscar_legajo = $data['buscar_legajo'] ?? '';
$resultados_busqueda_legajo = $data['resultados_busqueda_legajo'] ?? [];
$duplicado_desde = intval($data['duplicar_desde'] ?? 0);
$id_legajo_actual = intval($legajo['id_legajo'] ?? ($pdf_final_listo['id_legajo'] ?? 0));
$estado_legajo_actual = strtolower(trim($legajo['estado'] ?? ''));
$observacion_rechazo_legajo = trim((string)($legajo['observacion'] ?? ''));
$legajo_bloqueado = in_array($estado_legajo_actual, ['aprobado', 'cerrado'], true);
$pdf_final_disponible = !empty($pdf_final_listo['nombre_archivo']);
$matriz_por_tipo = [];
$documentos_legajo_por_requisito = [];
$tipos_legajo_por_id = [];
$formatearCi = static function ($valor) {
    $digitos = preg_replace('/\D+/', '', (string) $valor);
    return $digitos === '' ? '' : preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $digitos);
};

foreach ($tipos_legajo as $tipo_legajo) {
    $tipos_legajo_por_id[intval($tipo_legajo['id_tipo_legajo'] ?? 0)] = [
        'requiere_nro_solicitud' => !empty($tipo_legajo['requiere_nro_solicitud'])
    ];
}

foreach ($legajo_documentos as $legajo_documento) {
    $documentos_legajo_por_requisito[intval($legajo_documento['id_requisito'] ?? 0)] = [
        'estado' => strtolower(trim($legajo_documento['estado'] ?? 'pendiente')),
        'ruta_archivo' => $legajo_documento['ruta_archivo'] ?? '',
        'fecha_vencimiento' => !empty($legajo_documento['fecha_vencimiento']) ? date('Y-m-d', strtotime($legajo_documento['fecha_vencimiento'])) : '',
        'observacion' => $legajo_documento['observacion'] ?? '',
    ];
}

foreach ($matriz_legajo as $regla) {
    $ids_tipo = array_unique(array_filter([
        intval($regla['id_tipo_legajo'] ?? 0),
        intval($regla['id_tipoDoc'] ?? 0)
    ]));
    if (empty($ids_tipo)) {
        continue;
    }

    $regla_normalizada = [
        'id_requisito' => intval($regla['id_requisito'] ?? 0),
        'documento_nombre' => $regla['documento_nombre'] ?? '',
        'rol_vinculado' => $regla['rol_vinculado'] ?? 'TITULAR',
        'es_obligatorio' => !empty($regla['es_obligatorio']),
        'permite_reemplazo' => !empty($regla['permite_reemplazo']),
        'politica_actualizacion' => strtoupper(trim((string)($regla['politica_actualizacion'] ?? ''))),
        'tiene_vencimiento' => !empty($regla['tiene_vencimiento']),
        'dias_vigencia_base' => intval($regla['dias_vigencia_base'] ?? 0),
        'orden_visual' => intval($regla['orden_visual'] ?? 0),
    ];

    foreach ($ids_tipo as $id_tipo_legajo) {
        if (!isset($matriz_por_tipo[$id_tipo_legajo])) {
            $matriz_por_tipo[$id_tipo_legajo] = [];
        }

        $duplicada = false;
        foreach ($matriz_por_tipo[$id_tipo_legajo] as $existente) {
            if (intval($existente['id_requisito']) === intval($regla_normalizada['id_requisito'])) {
                $duplicada = true;
                break;
            }
        }

        if (!$duplicada) {
            $matriz_por_tipo[$id_tipo_legajo][] = $regla_normalizada;
        }
    }
}

$primer_tipo_id = !empty($tipos_legajo) ? intval($tipos_legajo[0]['id_tipo_legajo']) : 0;
$tipo_legajo_seleccionado = intval($legajo['id_tipo_legajo'] ?? $primer_tipo_id);
$reglas_iniciales = $tipo_legajo_seleccionado > 0 ? ($matriz_por_tipo[$tipo_legajo_seleccionado] ?? []) : [];
$total_obligatorios = count(array_filter($reglas_iniciales, function ($regla) {
    return !empty($regla['es_obligatorio']);
}));
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-folder-plus mr-3"></i>Armar Legajo
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Complete los datos base para habilitar el checklist de documentos requeridos.
                </p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                    title="Volver atrÃ¡s">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <form action="<?php echo base_url(); ?>legajos/armar_legajo" method="GET" autocomplete="off">
                <div class="flex flex-col md:flex-row gap-3 md:items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Buscar legajo</label>
                        <input type="text" name="buscar_legajo" value="<?php echo htmlspecialchars($buscar_legajo); ?>"
                            placeholder="CI del socio o Nro. Solicitud"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="px-5 py-2.5 bg-gray-800 text-white rounded-xl font-bold shadow-sm hover:bg-black transition-all flex items-center">
                            <i class="fas fa-search mr-2"></i> Buscar
                        </button>
                        <?php if ($buscar_legajo !== ''): ?>
                        <a href="<?php echo base_url(); ?>legajos/armar_legajo"
                            class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition-all flex items-center">
                            Limpiar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>

            <?php if ($buscar_legajo !== ''): ?>
            <div class="mt-4">
                <?php if (!empty($resultados_busqueda_legajo)): ?>
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">CI</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nro. Solicitud</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase">AcciÃ³n</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php foreach ($resultados_busqueda_legajo as $resultado_legajo): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($formatearCi($resultado_legajo['ci_socio'] ?? '')); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($resultado_legajo['nombre_completo'] ?? ''); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($resultado_legajo['nombre_tipo_legajo'] ?? ''); ?></td>
                                <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars($resultado_legajo['nro_solicitud'] ?? ''); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php
                                    $estadoTexto = $resultado_legajo['estado_legajo_texto'] ?? ucfirst($resultado_legajo['estado'] ?? '');
                                    $claseEstado = 'bg-gray-100 text-gray-700';
                                    if ($estadoTexto === 'Cerrado') {
                                        $claseEstado = 'bg-slate-200 text-slate-800';
                                    } elseif ($estadoTexto === 'Completado') {
                                        $claseEstado = 'bg-green-100 text-green-800';
                                    } elseif ($estadoTexto === 'Verificado') {
                                        $claseEstado = 'bg-cyan-100 text-cyan-800';
                                    } elseif ($estadoTexto === 'Vencido') {
                                        $claseEstado = 'bg-red-100 text-red-800';
                                    } elseif ($estadoTexto === 'Incompleto') {
                                        $claseEstado = 'bg-red-100 text-red-800';
                                    }
                                    ?>
                                    <span class="px-2 py-1 inline-flex text-xs font-bold rounded-full <?php echo $claseEstado; ?>">
                                        <?php echo htmlspecialchars($estadoTexto); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?php echo base_url(); ?>legajos/armar_legajo?id_legajo=<?php echo intval($resultado_legajo['id_legajo'] ?? 0); ?>"
                                        class="px-4 py-2 bg-scantec-blue text-white rounded-lg font-bold text-xs hover:bg-blue-800 transition-all inline-flex items-center">
                                        <i class="fas fa-folder-open mr-2"></i> Abrir
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="mt-3 px-4 py-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm font-semibold">
                    No se encontraron legajos para la bÃºsqueda realizada.
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <form action="<?php echo base_url(); ?>legajos/procesar_legajo" method="POST" enctype="multipart/form-data"
            id="formArmadoLegajo" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="id_legajo" value="<?php echo intval($legajo['id_legajo'] ?? 0); ?>">
            <input type="hidden" name="duplicado_desde" value="<?php echo $duplicado_desde; ?>">
            <input type="hidden" name="submit_action" id="submit_action" value="">

            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                        <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                            <i class="fas fa-user-tag mr-2"></i> 1. Datos Base
                        </h5>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 items-end">
                            <div class="xl:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de Legajo *</label>
                                <select name="tipo_legajo" id="tipo_legajo" data-original-value="<?php echo htmlspecialchars((string)($legajo['id_tipo_legajo'] ?? '')); ?>" required <?php echo $legajo_bloqueado ? 'disabled' : ''; ?>
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white font-bold text-gray-700 cursor-pointer shadow-sm transition-all">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipos_legajo as $index => $tipo_legajo): ?>
                                    <option value="<?php echo intval($tipo_legajo['id_tipo_legajo']); ?>" <?php echo intval($tipo_legajo['id_tipo_legajo']) === $tipo_legajo_seleccionado || ($tipo_legajo_seleccionado === 0 && $index === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tipo_legajo['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-scantec-blue">Progreso Obligatorios</span>
                                    <span id="progreso-obligatorios-texto" class="text-xs font-bold text-scantec-blue">0/<?php echo $total_obligatorios; ?></span>
                                </div>
                                <div class="w-full bg-blue-200 rounded-full h-2">
                                    <div id="progreso-obligatorios-barra" class="bg-scantec-blue h-2 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5 mt-5">
                            <div class="md:col-span-3 xl:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nro CI *</label>
                                <input type="text" id="ci_socio" name="ci_socio" value="<?php echo htmlspecialchars($legajo['ci_socio'] ?? ''); ?>" data-original-value="<?php echo htmlspecialchars((string)($legajo['ci_socio'] ?? '')); ?>" required <?php echo $legajo_bloqueado ? 'readonly' : ''; ?>
                                    maxlength="15"
                                    data-format-millares
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all focus:bg-blue-50">
                            </div>

                            <div class="md:col-span-6 xl:col-span-7">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo *</label>
                                <input type="text" id="nombre_socio" name="nombre_socio" value="<?php echo htmlspecialchars($legajo['nombre_completo'] ?? ''); ?>" data-original-value="<?php echo htmlspecialchars((string)($legajo['nombre_completo'] ?? '')); ?>" required <?php echo $legajo_bloqueado ? 'readonly' : ''; ?>
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all focus:bg-blue-50">
                            </div>

                            <div id="grupo-nro-solicitud" class="md:col-span-3 xl:col-span-3">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nro. Solicitud</label>
                                <input type="text" id="nro_solicitud" name="nro_solicitud" value="<?php echo htmlspecialchars($legajo['nro_solicitud'] ?? ''); ?>" data-original-value="<?php echo htmlspecialchars((string)($legajo['nro_solicitud'] ?? '')); ?>" <?php echo $legajo_bloqueado ? 'readonly' : ''; ?>
                                    maxlength="15"
                                    data-format-millares
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php if ($estado_legajo_actual === 'verificacion_rechazada' && $observacion_rechazo_legajo !== ''): ?>
                    <div id="aviso-legajo-rechazado" class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-2xl shadow-sm px-5 py-4 flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold uppercase tracking-wide text-yellow-800 mb-1">Legajo rechazado</div>
                            <div class="text-sm">
                                <span class="font-semibold">Motivo del rechazo:</span>
                                <?php echo nl2br(htmlspecialchars($observacion_rechazo_legajo)); ?>
                            </div>
                        </div>
                        <button type="submit" id="cerrar-aviso-legajo-rechazado"
                            formaction="<?php echo base_url(); ?>legajos/cerrar_aviso_rechazo_legajo/<?php echo $id_legajo_actual; ?>"
                            formmethod="POST"
                            class="shrink-0 inline-flex items-center justify-center w-8 h-8 rounded-full border border-yellow-300 text-yellow-700 hover:bg-yellow-100 transition-all"
                            title="Cerrar aviso" aria-label="Cerrar aviso">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-tasks mr-2 text-yellow-500"></i> 2. Checklist de Documentos Requeridos
                            </h5>
                            <span id="estado-checklist-badge" class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                Faltan Obligatorios
                            </span>
                        </div>

                        <div class="p-0 bg-white overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Rol</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Fecha Exped.</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Observación</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Archivo</th>
                                    </tr>
                                </thead>
                                <tbody id="checklist-legajo-body" class="divide-y divide-gray-200">
                                    <tr class="hover:bg-gray-50">
                                        <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">Seleccione un tipo de legajo.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-user-check mr-2"></i> 3. Datos de Gestión
                            </h5>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <div class="text-xs font-bold text-gray-500 uppercase mb-1">Creado por</div>
                                <div class="text-sm font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($legajo['nombre_usuario_creador'] ?? ($_SESSION['nombre'] ?? 'Sin registro')); ?>
                                </div>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <div class="text-xs font-bold text-gray-500 uppercase mb-1">Armado por</div>
                                <div class="text-sm font-semibold text-gray-800">
                                    <?php
                                    $nombreArmado = $legajo['nombre_usuario_armado'] ?? '';
                                    if ($nombreArmado === '') {
                                        $nombreArmado = !empty($legajo['id_legajo'])
                                            ? 'Aún no armado'
                                            : ($_SESSION['nombre'] ?? 'Sin registro');
                                    }
                                    echo htmlspecialchars($nombreArmado);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-4">
                        <button type="button" id="btn-guardar-borrador" <?php echo $legajo_bloqueado ? 'disabled' : ''; ?> class="px-6 py-3.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition-all <?php echo $legajo_bloqueado ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                            Guardar Borrador
                        </button>
                        <button type="button" id="btn-finalizar-legajo" <?php echo $legajo_bloqueado ? 'disabled' : ''; ?> class="px-8 py-3.5 bg-scantec-blue text-white rounded-xl font-bold shadow-lg hover:bg-blue-800 transition-all flex items-center group <?php echo $legajo_bloqueado ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                            <i class="fas fa-layer-group mr-2 group-hover:scale-110 transition-transform"></i> Armar Legajo
                        </button>
                        <?php if ($id_legajo_actual > 0 && !empty($pdf_final_listo['nombre_archivo'])): ?>
                        <a id="btn-ver-pdf-final" href="<?php echo base_url(); ?>legajos/ver_pdf_final/<?php echo $id_legajo_actual; ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-6 py-3.5 bg-red-700 text-white rounded-xl font-bold shadow-sm hover:bg-red-900 transition-all flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i> Ver PDF
                        </a>
                        <a id="btn-descargar-pdf-final" href="<?php echo base_url(); ?>legajos/descargar_pdf_final/<?php echo $id_legajo_actual; ?>"
                            class="px-6 py-3.5 bg-gray-800 text-white rounded-xl font-bold shadow-sm hover:bg-black transition-all flex items-center">
                            <i class="fas fa-download mr-2"></i> Descargar
                        </a>
                        <?php else: ?>
                        <button type="button" id="btn-ver-pdf-final" disabled
                            class="px-6 py-3.5 bg-red-200 text-red-400 rounded-xl font-bold shadow-sm cursor-not-allowed flex items-center opacity-80">
                            <i class="fas fa-file-pdf mr-2"></i> Ver PDF
                        </button>
                        <button type="button" id="btn-descargar-pdf-final" disabled
                            class="px-6 py-3.5 bg-gray-300 text-gray-500 rounded-xl font-bold shadow-sm cursor-not-allowed flex items-center opacity-80">
                            <i class="fas fa-download mr-2"></i> Descargar
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<style>
    input[type=file]::file-selector-button {
        transition: all 0.2s ease-in-out;
    }
</style>

<script>
    const matrizPorTipoLegajo = <?php echo json_encode($matriz_por_tipo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const documentosLegajoPorRequisito = <?php echo json_encode($documentos_legajo_por_requisito, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const formDocumentosPorRequisito = <?php echo json_encode($form_documentos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const tiposLegajoPorId = <?php echo json_encode($tipos_legajo_por_id, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const estadoLegajoActual = <?php echo json_encode($estado_legajo_actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const legajoBloqueado = <?php echo $legajo_bloqueado ? 'true' : 'false'; ?>;
    const pdfFinalDisponible = <?php echo $pdf_final_disponible ? 'true' : 'false'; ?>;
    const baseUrlLegajos = <?php echo json_encode(base_url(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

    function restaurarScrollPagina() {
        const limpiar = () => {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.documentElement.style.paddingRight = '';
            document.body.style.paddingRight = '';
            document.body.classList.remove('swal2-shown', 'swal2-height-auto');
            document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto');
        };

        limpiar();
        window.setTimeout(limpiar, 0);
        window.setTimeout(limpiar, 100);
        window.setTimeout(limpiar, 250);
    }

    function slugifyLegajo(texto) {
        return String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-zA-Z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .toLowerCase();
    }

    function sumarDias(fechaIso, dias) {
        if (!fechaIso) {
            return null;
        }
        const fecha = new Date(fechaIso + 'T00:00:00');
        if (Number.isNaN(fecha.getTime())) {
            return null;
        }
        fecha.setDate(fecha.getDate() + Number(dias || 0));
        return fecha;
    }

    function formatearConMillares(valor) {
        const digitos = String(valor || '').replace(/\D/g, '');
        if (!digitos) {
            return '';
        }
        return digitos.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function aplicarFormatoMillares(input) {
        if (!input) {
            return;
        }

        input.value = formatearConMillares(input.value);
    }

    function obtenerEstadoFila(fila) {
        const inputArchivo = fila.querySelector('[data-file-input]');
        const inputFecha = fila.querySelector('[data-fecha-expedicion]');
        const rutaExistente = fila.dataset.rutaArchivo || '';
        const fechaVencimientoGuardada = fila.dataset.fechaVencimiento || '';
        const archivoEliminado = fila.dataset.archivoEliminado === '1';
        const tieneVencimiento = fila.dataset.tieneVencimiento === '1';
        const diasVigenciaBase = Number(fila.dataset.diasVigenciaBase || 0);
        const tieneArchivoNuevo = !!(inputArchivo && inputArchivo.files && inputArchivo.files.length > 0);
        const tieneArchivo = tieneArchivoNuevo || (!archivoEliminado && rutaExistente !== '');

        if (!tieneArchivo) {
            return 'pendiente';
        }

        if (tieneVencimiento) {
            let fechaVencimiento = null;

            if (inputFecha && inputFecha.value) {
                fechaVencimiento = sumarDias(inputFecha.value, diasVigenciaBase);
            } else if (fechaVencimientoGuardada) {
                fechaVencimiento = new Date(fechaVencimientoGuardada + 'T00:00:00');
            } else {
                return 'pendiente';
            }

            if (fechaVencimiento && !Number.isNaN(fechaVencimiento.getTime())) {
                const hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                fechaVencimiento.setHours(0, 0, 0, 0);
                if (fechaVencimiento < hoy) {
                    return 'vencido';
                }
                const limite = new Date(hoy);
                limite.setDate(limite.getDate() + 30);
                if (fechaVencimiento <= limite) {
                    return 'por_vencer';
                }
            } else {
                return 'pendiente';
            }
        }

        return 'cargado';
    }

    function aplicarEstadoVisualFila(fila, estado) {
        const badge = fila.querySelector('[data-estado-badge]');
        if (!badge) {
            return;
        }

        const estadoNormalizado = String(estado || 'pendiente').toLowerCase();
        let claseEstado = 'bg-gray-100 text-gray-600';
        let textoEstado = 'PENDIENTE';
        if (estadoNormalizado === 'cargado') {
            claseEstado = 'bg-green-200 text-green-800';
            textoEstado = 'CARGADO';
        } else if (estadoNormalizado === 'por_vencer') {
            claseEstado = 'bg-yellow-200 text-yellow-800';
            textoEstado = 'POR VENCER';
        } else if (estadoNormalizado === 'vencido') {
            claseEstado = 'bg-red-200 text-red-800';
            textoEstado = 'VENCIDO';
        }

        badge.className = 'px-2 py-1 inline-flex text-xs leading-5 font-bold rounded ' + claseEstado;
        badge.textContent = textoEstado;
        fila.dataset.estadoActual = estadoNormalizado;
    }

    function actualizarResumenChecklist() {
        const body = document.getElementById('checklist-legajo-body');
        const badge = document.getElementById('estado-checklist-badge');
        const progresoTexto = document.getElementById('progreso-obligatorios-texto');
        const progresoBarra = document.getElementById('progreso-obligatorios-barra');
        const btnFinalizarLegajo = document.getElementById('btn-finalizar-legajo');
        const btnVerPdfFinal = document.getElementById('btn-ver-pdf-final');
        const btnDescargarPdfFinal = document.getElementById('btn-descargar-pdf-final');

        if (!body || !badge || !progresoTexto || !progresoBarra) {
            return;
        }

        const aplicarEstadoBotonFinalizar = (habilitado) => {
            if (!btnFinalizarLegajo || legajoBloqueado) {
                return;
            }

            btnFinalizarLegajo.disabled = !habilitado;
            if (habilitado) {
                btnFinalizarLegajo.classList.remove('opacity-50', 'cursor-not-allowed');
                btnFinalizarLegajo.classList.add('hover:bg-blue-800');
            } else {
                btnFinalizarLegajo.classList.add('opacity-50', 'cursor-not-allowed');
                btnFinalizarLegajo.classList.remove('hover:bg-blue-800');
            }
        };

        const alternarBotonesPdfFinal = (habilitado) => {
            [btnVerPdfFinal, btnDescargarPdfFinal].forEach((elemento) => {
                if (!elemento) {
                    return;
                }

                if (elemento.tagName === 'A') {
                    if (habilitado) {
                        elemento.classList.remove('pointer-events-none', 'opacity-50');
                        elemento.removeAttribute('aria-disabled');
                    } else {
                        elemento.classList.add('pointer-events-none', 'opacity-50');
                        elemento.setAttribute('aria-disabled', 'true');
                    }
                    return;
                }

                elemento.disabled = !habilitado;
            });
        };

        const filas = Array.from(body.querySelectorAll('[data-checklist-row]'));
        const legajoVerificadoFueModificado = () => {
            if (estadoLegajoActual !== 'verificado') {
                return false;
            }

            const camposCabecera = ['tipo_legajo', 'ci_socio', 'nombre_socio', 'nro_solicitud'];
            for (const idCampo of camposCabecera) {
                const campo = document.getElementById(idCampo);
                if (!campo) {
                    continue;
                }
                if (String(campo.value || '').trim() !== String(campo.dataset.originalValue || '').trim()) {
                    return true;
                }
            }

            return filas.some((fila) => {
                const inputArchivo = fila.querySelector('[data-file-input]');
                const flagEliminar = fila.querySelector('[data-remove-file-flag]');
                const fechaExpedicion = fila.querySelector('[data-fecha-expedicion]');
                const observacion = fila.querySelector(`textarea[name="observacion_${fila.dataset.requisito}"]`);

                if (inputArchivo && inputArchivo.files && inputArchivo.files.length > 0) {
                    return true;
                }
                if (flagEliminar && String(flagEliminar.value) === '1') {
                    return true;
                }
                if (fechaExpedicion && String(fechaExpedicion.value || '').trim() !== String(fechaExpedicion.dataset.originalValue || '').trim()) {
                    return true;
                }
                if (observacion && String(observacion.value || '').trim() !== String(observacion.dataset.originalValue || '').trim()) {
                    return true;
                }

                return false;
            });
        };
        if (!filas.length) {
            badge.className = 'hidden px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold';
            badge.textContent = 'Sin configuracion';
            progresoTexto.textContent = '0/0';
            progresoBarra.style.width = '0%';
            aplicarEstadoBotonFinalizar(false);
            alternarBotonesPdfFinal(false);
            return;
        }

        const filasObligatorias = filas.filter((fila) => fila.dataset.esObligatorio === '1');
        const obligatoriosCargados = filasObligatorias.filter((fila) => (fila.dataset.estadoActual || 'pendiente') !== 'pendiente').length;
        const totalObligatorios = filasObligatorias.length;
        const porcentaje = totalObligatorios > 0 ? Math.round((obligatoriosCargados / totalObligatorios) * 100) : 0;
        const hayVencidos = filas.some((fila) => (fila.dataset.estadoActual || 'pendiente') === 'vencido');
        const verificadoModificado = legajoVerificadoFueModificado();

        progresoTexto.textContent = obligatoriosCargados + '/' + totalObligatorios;
        progresoBarra.style.width = porcentaje + '%';

        if (estadoLegajoActual === 'cerrado' || estadoLegajoActual === 'aprobado') {
            badge.className = 'px-3 py-1 bg-slate-200 text-slate-800 rounded-full text-xs font-bold';
            badge.textContent = 'Cerrado';
            aplicarEstadoBotonFinalizar(false);
            alternarBotonesPdfFinal(true);
        } else if (estadoLegajoActual === 'verificado' && !verificadoModificado) {
            badge.className = 'px-3 py-1 bg-cyan-100 text-cyan-800 rounded-full text-xs font-bold';
            badge.textContent = 'Verificado';
            aplicarEstadoBotonFinalizar(false);
            alternarBotonesPdfFinal(true);
        } else if (estadoLegajoActual === 'verificacion_rechazada') {
            badge.className = 'px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold';
            badge.textContent = 'Verificación rechazada';
            aplicarEstadoBotonFinalizar(false);
        } else if (hayVencidos) {
            badge.className = 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold';
            badge.textContent = 'Vencido';
            aplicarEstadoBotonFinalizar(false);
            alternarBotonesPdfFinal(false);
        } else if (totalObligatorios > obligatoriosCargados) {
            badge.className = 'px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold';
            badge.textContent = 'Incompleto';
            aplicarEstadoBotonFinalizar(false);
            alternarBotonesPdfFinal(false);
        } else {
            badge.className = 'px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold';
            badge.textContent = 'Completado';
            aplicarEstadoBotonFinalizar(true);
            alternarBotonesPdfFinal(pdfFinalDisponible);
        }
    }

    function refrescarEstadosChecklist() {
        const body = document.getElementById('checklist-legajo-body');
        if (!body) {
            return;
        }

        body.querySelectorAll('[data-checklist-row]').forEach((fila) => {
            aplicarEstadoVisualFila(fila, obtenerEstadoFila(fila));
        });
        actualizarResumenChecklist();
    }

    async function mostrarAvisoAccionArchivo(accion) {
        const accionNormalizada = String(accion || '').toUpperCase();
        let titulo = 'AcciÃ³n seleccionada';
        let texto = 'Se aplicarÃ¡ la acciÃ³n elegida al guardar el legajo.';
        let icono = 'info';

        if (accionNormalizada === 'REEMPLAZAR') {
            titulo = 'Se reemplazara el archivo';
            texto = 'Al guardar, el archivo nuevo reemplazara al documento actual.';
            icono = 'warning';
        } else if (accionNormalizada === 'UNIR_AL_INICIO') {
            titulo = 'Se agregara al inicio';
            texto = 'Al guardar, el archivo nuevo se agregara al inicio del documento actual.';
            icono = 'success';
        } else if (accionNormalizada === 'UNIR_AL_FINAL') {
            titulo = 'Se agregara al final';
            texto = 'Al guardar, el archivo nuevo se agregara al final del documento actual.';
            icono = 'success';
        } else if (accionNormalizada === 'NO_PERMITIR') {
            titulo = 'Actualizacion no permitida';
            texto = 'Este documento no permite reemplazar ni agregar otro archivo.';
            icono = 'warning';
        }

        if (typeof Swal !== 'undefined') {
            await Swal.fire({
                title: titulo,
                text: texto,
                icon: icono,
                confirmButtonText: 'Aceptar',
                willClose: restaurarScrollPagina,
                didClose: restaurarScrollPagina
            });
            restaurarScrollPagina();
            return;
        }

        alert(titulo + '\n\n' + texto);
    }

    function actualizarCampoSolicitud(idTipoLegajo) {
        const grupo = document.getElementById('grupo-nro-solicitud');
        const input = document.getElementById('nro_solicitud');
        const config = tiposLegajoPorId[String(idTipoLegajo)] || tiposLegajoPorId[idTipoLegajo] || {};
        const requiere = !!config.requiere_nro_solicitud;

        if (!grupo || !input) {
            return;
        }

        if (requiere) {
            grupo.classList.remove('hidden');
            input.required = true;
        } else {
            grupo.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    async function validarSolicitudDuplicadaAntesDeEnviar() {
        const inputSolicitud = document.getElementById('nro_solicitud');
        const inputTipoLegajo = document.getElementById('tipo_legajo');
        if (!inputSolicitud || !inputTipoLegajo) {
            return true;
        }

        const tipoConfig = tiposLegajoPorId[String(inputTipoLegajo.value)] || tiposLegajoPorId[inputTipoLegajo.value] || {};
        const requiereSolicitud = !!tipoConfig.requiere_nro_solicitud;
        const nroSolicitud = String(inputSolicitud.value || '').trim();
        if (!requiereSolicitud || nroSolicitud === '') {
            return true;
        }

        const idLegajo = <?php echo intval($id_legajo_actual); ?>;
        const url = `${baseUrlLegajos}legajos/validar_solicitud_duplicada?nro_solicitud=${encodeURIComponent(nroSolicitud)}&id_legajo=${idLegajo}`;

        try {
            const response = await fetch(url, { credentials: 'same-origin' });
            const data = await response.json();
            if (data && data.ok && data.duplicado) {
                if (typeof Swal !== 'undefined') {
                    await Swal.fire({
                        title: 'Número duplicado',
                        text: 'Ya existe un legajo con ese número de solicitud. Se abrirá el legajo existente.',
                        icon: 'warning',
                        confirmButtonText: 'Aceptar'
                    });
                    restaurarScrollPagina();
                } else {
                    alert('Ya existe un legajo con ese número de solicitud. Se abrirá el legajo existente.');
                }

                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                }
                return false;
            }
        } catch (error) {
            return true;
        }

        return true;
    }

    function renderChecklistLegajo(idTipoLegajo) {
        const body = document.getElementById('checklist-legajo-body');
        const badge = document.getElementById('estado-checklist-badge');
        const progresoTexto = document.getElementById('progreso-obligatorios-texto');
        const progresoBarra = document.getElementById('progreso-obligatorios-barra');
        const reglas = matrizPorTipoLegajo[String(idTipoLegajo)] || [];

        if (!body || !badge || !progresoTexto || !progresoBarra) {
            return;
        }

        if (!reglas.length) {
            body.innerHTML = '<tr class="hover:bg-gray-50"><td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No hay documentos configurados para este tipo de legajo.</td></tr>';
            badge.className = 'hidden px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold';
            badge.textContent = 'Sin configuracion';
            progresoTexto.textContent = '0/0';
            progresoBarra.style.width = '0%';
            return;
        }

        body.innerHTML = reglas.map(regla => {
            const documentoGuardado = documentosLegajoPorRequisito[String(regla.id_requisito)] || documentosLegajoPorRequisito[regla.id_requisito] || {};
            const documentoFormulario = formDocumentosPorRequisito[String(regla.id_requisito)] || formDocumentosPorRequisito[regla.id_requisito] || {};
            const estadoGuardado = String(documentoGuardado.estado || 'pendiente').toLowerCase();
            let claseEstado = 'bg-gray-100 text-gray-600';
            let textoEstado = 'PENDIENTE';
            if (estadoGuardado === 'cargado') {
                claseEstado = 'bg-green-200 text-green-800';
                textoEstado = 'CARGADO';
            } else if (estadoGuardado === 'por_vencer') {
                claseEstado = 'bg-yellow-200 text-yellow-800';
                textoEstado = 'POR VENCER';
            } else if (estadoGuardado === 'vencido') {
                claseEstado = 'bg-red-200 text-red-800';
                textoEstado = 'VENCIDO';
            }
            const requeridoTexto = regla.es_obligatorio
                ? '<div class="text-xs text-red-500 font-bold">* Obligatorio</div>'
                : '<div class="text-xs text-gray-500 font-bold">Opcional</div>';
            const inputName = 'doc_' + regla.id_requisito + '_' + slugifyLegajo(regla.rol_vinculado) + '_' + slugifyLegajo(regla.documento_nombre);
            let fechaExpedicionInicial = '';
            if (String(documentoFormulario.fecha_expedicion || '').trim() !== '') {
                fechaExpedicionInicial = String(documentoFormulario.fecha_expedicion || '').trim();
            } else if (regla.tiene_vencimiento && documentoGuardado.fecha_vencimiento && Number(regla.dias_vigencia_base || 0) > 0) {
                const fechaVencimiento = new Date(documentoGuardado.fecha_vencimiento + 'T00:00:00');
                if (!Number.isNaN(fechaVencimiento.getTime())) {
                    fechaVencimiento.setDate(fechaVencimiento.getDate() - Number(regla.dias_vigencia_base));
                    fechaExpedicionInicial = fechaVencimiento.toISOString().slice(0, 10);
                }
            }
            const fechaExpedicionHtml = regla.tiene_vencimiento
                ? `<input type="date" name="fecha_expedicion_${regla.id_requisito}" value="${fechaExpedicionInicial}" class="w-full px-2 py-1.5 border rounded text-sm text-gray-700" data-fecha-expedicion data-original-value="${String(fechaExpedicionInicial || '').replace(/"/g, '&quot;')}" ${legajoBloqueado ? 'disabled' : ''}>`
                : '<span class="text-xs text-gray-400 font-bold">No aplica</span>';
            let politicaInicial = String(regla.politica_actualizacion || '').toUpperCase();
            if (!politicaInicial) {
                politicaInicial = regla.permite_reemplazo ? 'REEMPLAZAR' : 'NO_PERMITIR';
            }
            const observacionInicial = String(documentoFormulario.observacion ?? documentoGuardado.observacion ?? '').replace(/"/g, '&quot;');
            const nombreArchivoActual = documentoGuardado.ruta_archivo
                ? String(documentoGuardado.ruta_archivo).split('/').pop().split('\\').pop()
                : '';
            const nombreArchivoHtml = nombreArchivoActual
                ? `<div class="mt-1 text-xs text-gray-600 truncate" data-file-name-label>${nombreArchivoActual}</div>`
                : '<div class="mt-1 text-xs text-gray-400 truncate" data-file-name-label>Ningun archivo seleccionado</div>';
            const urlVerArchivo = `${baseUrlLegajos}legajos/ver_documento_checklist?id_legajo=<?php echo $id_legajo_actual; ?>&id_requisito=${regla.id_requisito}`;
            const botonVerArchivoHtml = documentoGuardado.ruta_archivo
                ? `<a href="${urlVerArchivo}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50 transition-all text-xs font-bold"
                        data-view-file-link
                        data-view-file-url="${urlVerArchivo}">
                        <i class="fas fa-eye mr-1"></i> Ver archivo
                   </a>`
                : `<span
                        class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 bg-gray-50 transition-all text-xs font-bold cursor-not-allowed"
                        data-view-file-link
                        data-view-file-url="${urlVerArchivo}">
                        <i class="fas fa-eye mr-1"></i> Ver archivo
                   </span>`;
            const botonEliminarArchivoHtml = !legajoBloqueado
                ? `<button type="button" class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 transition-all text-xs font-bold" data-remove-file-btn>
                        <i class="fas fa-times mr-1"></i> Quitar archivo
                   </button>`
                : '';

            return `
                <tr class="hover:bg-gray-50 transition-colors"
                    data-checklist-row
                    data-requisito="${regla.id_requisito}"
                    data-es-obligatorio="${regla.esObligatorio ? 1 : (regla.es_obligatorio ? 1 : 0)}"
                    data-tiene-vencimiento="${regla.tiene_vencimiento ? 1 : 0}"
                    data-dias-vigencia-base="${Number(regla.dias_vigencia_base || 0)}"
                    data-ruta-archivo="${String(documentoGuardado.ruta_archivo || '').replace(/"/g, '&quot;')}"
                    data-fecha-vencimiento="${String(documentoGuardado.fecha_vencimiento || '').replace(/"/g, '&quot;')}"
                    data-archivo-eliminado="0"
                    data-estado-actual="${estadoGuardado}">
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold text-gray-900">${regla.documento_nombre}</div>
                        ${requeridoTexto}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-700 font-semibold">${regla.rol_vinculado}</td>
                    <td class="px-4 py-4 text-center">
                        <span data-estado-badge class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded ${claseEstado}">
                            ${textoEstado}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        ${fechaExpedicionHtml}
                    </td>
                    <td class="px-4 py-4">
                        <textarea
                            name="observacion_${regla.id_requisito}"
                            rows="2"
                            class="w-full px-2 py-1.5 border rounded text-sm text-gray-700 resize-none"
                            data-original-value="${observacionInicial}"
                            placeholder="Agregar observacion"
                            ${legajoBloqueado ? 'disabled' : ''}>${observacionInicial}</textarea>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <input type="hidden" name="ruta_existente_${regla.id_requisito}" value="${String(documentoGuardado.ruta_archivo || '').replace(/"/g, '&quot;')}">
                        <input type="hidden" name="eliminar_archivo_${regla.id_requisito}" value="0" data-remove-file-flag>
                        <input type="hidden" name="accion_archivo_${regla.id_requisito}" value="${politicaInicial}" data-file-action>
                        <input type="file" name="${inputName}[]" multiple accept=".pdf,.jpg,.jpeg,.png,image/jpeg,image/png,application/pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-blue-50 file:text-scantec-blue hover:file:bg-scantec-blue hover:file:text-white transition-all cursor-pointer" data-file-input data-policy-default="${politicaInicial}" ${legajoBloqueado ? 'disabled' : ''}>
                        ${nombreArchivoHtml}
                        ${botonVerArchivoHtml}
                        ${botonEliminarArchivoHtml}
                    </td>
                </tr>
            `;
        }).join('');

        body.querySelectorAll('[data-file-input]').forEach((input) => {
            input.addEventListener('change', async function () {
                const label = this.parentElement.querySelector('[data-file-name-label]');
                if (!label) {
                    return;
                }

                const fila = this.closest('[data-checklist-row]');
                const hiddenRuta = this.parentElement.querySelector('input[name^="ruta_existente_"]');
                const actionInput = this.parentElement.querySelector('[data-file-action]');
                const politicaConfigurada = String(this.dataset.policyDefault || 'REEMPLAZAR').toUpperCase();
                const tieneArchivoActual = !!(hiddenRuta && hiddenRuta.value && hiddenRuta.value.trim() !== '');

                if (this.files && this.files.length > 0) {
                    const extensionesInvalidas = Array.from(this.files)
                        .map((file) => {
                            const nombre = String(file.name || '');
                            const partes = nombre.split('.');
                            return partes.length > 1 ? partes.pop().toLowerCase() : '';
                        })
                        .filter((extension) => extension && !['pdf', 'jpg', 'jpeg', 'png'].includes(extension));

                    if (extensionesInvalidas.length > 0) {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                title: 'Formato no permitido',
                                text: 'No se permiten archivos JFIF ni otros formatos distintos de PDF, JPG, JPEG o PNG.',
                                icon: 'warning',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            alert('No se permiten archivos JFIF ni otros formatos distintos de PDF, JPG, JPEG o PNG.');
                        }
                        this.value = '';
                        label.textContent = 'Ningun archivo seleccionado';
                        label.className = 'mt-1 text-xs text-gray-400 truncate';
                        return;
                    }
                }

                if (this.files && this.files.length > 0 && tieneArchivoActual) {
                    let accionSeleccionada = actionInput ? String(actionInput.value || 'REEMPLAZAR').toUpperCase() : 'REEMPLAZAR';
                    if (politicaConfigurada === 'NO_PERMITIR') {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                title: 'Actualizacion no permitida',
                                text: 'Este documento no permite cargar un nuevo archivo cuando ya existe uno.',
                                icon: 'warning',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                        this.value = '';
                        return;
                    } else if (['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL'].includes(politicaConfigurada)) {
                        accionSeleccionada = politicaConfigurada;
                    } else if (typeof Swal !== 'undefined') {
                        const result = await Swal.fire({
                            title: 'Actualizar documento',
                            text: 'El documento ya tiene un archivo. ¿Quieres reemplazarlo o añadir el nuevo al original?',
                            icon: 'question',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Reemplazar',
                            denyButtonText: 'Añadir',
                            cancelButtonText: 'Anadir al final'
                        });

                        if (result.isConfirmed) {
                            accionSeleccionada = 'REEMPLAZAR';
                        } else if (result.isDenied) {
                            accionSeleccionada = 'UNIR_AL_INICIO';
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            accionSeleccionada = 'UNIR_AL_FINAL';
                        } else {
                            this.value = '';
                            return;
                        }
                    } else {
                        accionSeleccionada = window.confirm('El documento ya tiene un archivo. Aceptar = Reemplazar / Cancelar = Añadir al original')
                            ? 'REEMPLAZAR'
                            : 'UNIR_AL_INICIO';
                    }

                    if (actionInput) {
                        actionInput.value = accionSeleccionada;
                    }
                    await mostrarAvisoAccionArchivo(accionSeleccionada);
                }

                if (this.files && this.files.length > 0) {
                    if (this.files.length === 1) {
                        label.textContent = this.files[0].name;
                    } else {
                        label.textContent = `${this.files.length} archivos seleccionados`;
                    }
                    label.className = 'mt-1 text-xs text-blue-700 truncate';
                    if (fila) {
                        const enlaceActual = fila.querySelector('[data-view-file-link]');
                        if (enlaceActual) {
                            const hrefOriginal = enlaceActual.dataset.viewFileUrl || '#';
                            const previewAnterior = enlaceActual.dataset.previewUrl || '';
                            if (previewAnterior) {
                                URL.revokeObjectURL(previewAnterior);
                            }
                            if (this.files.length === 1) {
                                const previewUrl = URL.createObjectURL(this.files[0]);
                                enlaceActual.outerHTML = `<a href="${previewUrl}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50 transition-all text-xs font-bold" data-view-file-link data-view-file-url="${hrefOriginal}" data-preview-url="${previewUrl}"><i class="fas fa-eye mr-1"></i> Ver archivo</a>`;
                            } else {
                                enlaceActual.outerHTML = `<button type="button" class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 transition-all text-xs font-bold" data-view-file-link data-view-file-url="${hrefOriginal}" data-merge-files-btn><i class="fas fa-layer-group mr-1"></i> Unir archivos</button>`;
                            }
                        }
                    }
                } else {
                    label.textContent = 'Ningun archivo seleccionado';
                    label.className = 'mt-1 text-xs text-gray-400 truncate';
                    if (fila) {
                        const enlaceActual = fila.querySelector('[data-view-file-link]');
                        if (enlaceActual) {
                            const hrefOriginal = enlaceActual.dataset.viewFileUrl || '#';
                            const previewAnterior = enlaceActual.dataset.previewUrl || '';
                            if (previewAnterior) {
                                URL.revokeObjectURL(previewAnterior);
                            }
                            if (hiddenRuta && hiddenRuta.value && hiddenRuta.value.trim() !== '') {
                                enlaceActual.outerHTML = `<a href="${hrefOriginal}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50 transition-all text-xs font-bold" data-view-file-link data-view-file-url="${hrefOriginal}"><i class="fas fa-eye mr-1"></i> Ver archivo</a>`;
                            } else {
                                enlaceActual.outerHTML = `<span class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 bg-gray-50 transition-all text-xs font-bold cursor-not-allowed" data-view-file-link data-view-file-url="${hrefOriginal}"><i class="fas fa-eye mr-1"></i> Ver archivo</span>`;
                            }
                        }
                    }
                }

                const flag = this.parentElement.querySelector('[data-remove-file-flag]');
                if (flag) {
                    flag.value = '0';
                }
                if (fila) {
                    fila.dataset.archivoEliminado = '0';
                    fila.setAttribute('data-archivo-eliminado', '0');
                }
                refrescarEstadosChecklist();
            });
        });

        body.querySelectorAll('[data-remove-file-btn]').forEach((button) => {
            button.addEventListener('click', function () {
                const fila = this.closest('[data-checklist-row]');
                if (!fila) {
                    return;
                }

                const inputArchivo = fila.querySelector('[data-file-input]');
                const label = fila.querySelector('[data-file-name-label]');
                const hiddenRuta = fila.querySelector('input[name^="ruta_existente_"]');
                const hiddenEliminar = fila.querySelector('[data-remove-file-flag]');
                const fechaExpedicion = fila.querySelector('[data-fecha-expedicion]');
                const enlaceVerArchivo = fila.querySelector('[data-view-file-link]');

                if (inputArchivo) {
                    inputArchivo.value = '';
                }
                if (label) {
                    label.textContent = 'Ningun archivo seleccionado';
                    label.className = 'mt-1 text-xs text-gray-400 truncate';
                }
                if (hiddenRuta) {
                    hiddenRuta.value = '';
                }
                if (hiddenEliminar) {
                    hiddenEliminar.value = '1';
                }
                if (fechaExpedicion) {
                    fechaExpedicion.value = '';
                }
                if (enlaceVerArchivo) {
                    const previewAnterior = enlaceVerArchivo.dataset.previewUrl || '';
                    if (previewAnterior) {
                        URL.revokeObjectURL(previewAnterior);
                    }
                    const href = enlaceVerArchivo.dataset.viewFileUrl || '#';
                    enlaceVerArchivo.outerHTML = `<span class="mt-2 inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-gray-400 bg-gray-50 transition-all text-xs font-bold cursor-not-allowed" data-view-file-link data-view-file-url="${href}"><i class="fas fa-eye mr-1"></i> Ver archivo</span>`;
                }

                fila.dataset.rutaArchivo = '';
                fila.dataset.fechaVencimiento = '';
                fila.dataset.archivoEliminado = '1';
                fila.dataset.estadoActual = 'pendiente';
                fila.setAttribute('data-ruta-archivo', '');
                fila.setAttribute('data-fecha-vencimiento', '');
                fila.setAttribute('data-archivo-eliminado', '1');

                refrescarEstadosChecklist();
            });
        });

        body.querySelectorAll('[data-fecha-expedicion]').forEach((input) => {
            input.addEventListener('change', function () {
                refrescarEstadosChecklist();
            });
        });

        body.querySelectorAll('textarea[name^="observacion_"]').forEach((input) => {
            input.addEventListener('input', function () {
                refrescarEstadosChecklist();
            });
        });

        refrescarEstadosChecklist();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectTipoLegajo = document.getElementById('tipo_legajo');
        const formLegajo = document.getElementById('formArmadoLegajo');
        const submitActionInput = document.getElementById('submit_action');
        const btnGuardarBorrador = document.getElementById('btn-guardar-borrador');
        const btnFinalizarLegajo = document.getElementById('btn-finalizar-legajo');
        if (!selectTipoLegajo) {
            return;
        }

        renderChecklistLegajo(selectTipoLegajo.value);
        actualizarCampoSolicitud(selectTipoLegajo.value);
        selectTipoLegajo.addEventListener('change', function () {
            actualizarCampoSolicitud(this.value);
            renderChecklistLegajo(this.value);
        });

        if (formLegajo && submitActionInput && btnGuardarBorrador) {
            btnGuardarBorrador.addEventListener('click', async function () {
                const puedeContinuar = await validarSolicitudDuplicadaAntesDeEnviar();
                if (!puedeContinuar) {
                    return;
                }
                submitActionInput.value = 'borrador';
                formLegajo.submit();
            });
        }

        if (formLegajo && submitActionInput && btnFinalizarLegajo) {
            btnFinalizarLegajo.addEventListener('click', async function () {
                const puedeContinuar = await validarSolicitudDuplicadaAntesDeEnviar();
                if (!puedeContinuar) {
                    return;
                }
                submitActionInput.value = 'finalizar';
                formLegajo.submit();
            });
        }

        document.addEventListener('click', async function (event) {
            const mergeButton = event.target.closest('[data-merge-files-btn]');
            if (!mergeButton || !formLegajo || !submitActionInput) {
                return;
            }

            const puedeContinuar = await validarSolicitudDuplicadaAntesDeEnviar();
            if (!puedeContinuar) {
                return;
            }

            submitActionInput.value = 'borrador';
            formLegajo.submit();
        });

        document.querySelectorAll('[data-format-millares]').forEach((input) => {
            aplicarFormatoMillares(input);
            input.addEventListener('input', function () {
                aplicarFormatoMillares(this);
            });
        });

    });
</script>

<?php pie() ?>
