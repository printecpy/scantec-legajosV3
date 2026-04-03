<?php encabezado() ?>

<?php
$termino = $data['termino'] ?? '';
$resultados = $data['resultados'] ?? [];
$estado_legajo = $data['estado_legajo'] ?? '';
$id_tipo_legajo = intval($data['id_tipo_legajo'] ?? 0);
$tipos_legajo = $data['tipos_legajo'] ?? [];
$puedeAdministrarLegajo = !empty($data['puede_administrar_legajo']);
$puedeEliminarLegajo = !empty($data['puede_eliminar_legajo']);
$puedeRearmarLote = !empty($data['puede_rearmar_lote']);
$puedeDescargarLote = !empty($data['puede_descargar_lote']);
$formatearCi = static function ($valor) {
    $digitos = preg_replace('/\D+/', '', (string) $valor);
    return $digitos === '' ? '' : preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $digitos);
};
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-sliders-h mr-3"></i> Administrar Legajos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Vista administrativa para edición, cierre, eliminación y acciones por lote.
                </p>
            </div>

            <div>
                <a href="#" onclick="window.history.back(); return false;"
                    class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                    title="Volver atrás">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <form action="<?php echo base_url(); ?>legajos/administrar_legajos" method="GET" autocomplete="off">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dato básico del legajo</label>
                        <input type="text" name="termino" value="<?php echo htmlspecialchars($termino); ?>"
                            placeholder="CI del socio o Nro. Solicitud"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                    </div>

                    <div class="md:col-span-4">
                        <label for="estado_legajo" class="block text-xs font-bold text-gray-500 uppercase mb-1">Estado de legajo</label>
                        <select id="estado_legajo" name="estado_legajo"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-white">
                            <option value="">Todos los estados</option>
                            <option value="Incompleto" <?php echo $estado_legajo === 'Incompleto' ? 'selected' : ''; ?>>Incompleto</option>
                            <option value="Vencido" <?php echo $estado_legajo === 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                            <option value="Completado" <?php echo $estado_legajo === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                            <option value="Verificación rechazada" <?php echo $estado_legajo === 'Verificación rechazada' ? 'selected' : ''; ?>>Verificación rechazada</option>
                            <option value="Verificado" <?php echo $estado_legajo === 'Verificado' ? 'selected' : ''; ?>>Verificado</option>
                            <option value="Cerrado" <?php echo $estado_legajo === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                        </select>
                    </div>

                    <div class="md:col-span-5">
                        <label for="id_tipo_legajo" class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de legajo</label>
                        <select id="id_tipo_legajo" name="id_tipo_legajo"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-white">
                            <option value="0">Todos los tipos</option>
                            <?php foreach ($tipos_legajo as $tipo_legajo): ?>
                            <option value="<?php echo intval($tipo_legajo['id_tipo_legajo']); ?>" <?php echo intval($tipo_legajo['id_tipo_legajo']) === $id_tipo_legajo ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo_legajo['nombre']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-3 flex gap-3">
                        <button type="submit"
                            class="flex-1 px-5 py-2.5 bg-gray-800 text-white rounded-xl font-bold shadow-sm hover:bg-black transition-all flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i> Buscar
                        </button>
                        <?php if ($termino !== '' || $estado_legajo !== '' || $id_tipo_legajo > 0): ?>
                        <a href="<?php echo base_url(); ?>legajos/administrar_legajos"
                            class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition-all flex items-center">
                            Limpiar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <form action="<?php echo base_url(); ?>legajos/procesar_lote_legajos" method="post" id="frmLoteAdministrarLegajos">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="termino" value="<?php echo htmlspecialchars($termino); ?>">
            <input type="hidden" name="estado_legajo" value="<?php echo htmlspecialchars($estado_legajo); ?>">
            <input type="hidden" name="id_tipo_legajo" value="<?php echo intval($id_tipo_legajo); ?>">
            <input type="hidden" name="origen_lote" value="administrar_legajos">
            <input type="hidden" name="accion_lote" id="accion_lote_admin" value="">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <h5 class="font-bold text-slate-800 text-sm uppercase tracking-wider">
                            <i class="fas fa-folder-open mr-2 text-gray-400"></i> Legajos para Administración
                        </h5>
                        <p class="text-xs text-gray-500 mt-1">Seleccione varios legajos para rearmar sus PDFs o descargarlos en lote.</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <?php if ($puedeRearmarLote): ?>
                        <button type="button"
                            class="px-4 py-2.5 bg-scantec-blue text-white rounded-xl font-bold text-sm shadow-sm hover:bg-blue-800 transition-all"
                            onclick="confirmarAccionLoteAdministrar('rearmar')">
                            <i class="fas fa-sync-alt mr-2"></i> Rearmar Seleccionados
                        </button>
                        <?php endif; ?>
                        <?php if ($puedeDescargarLote): ?>
                        <button type="button"
                            class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-sm shadow-sm hover:bg-emerald-700 transition-all"
                            onclick="confirmarAccionLoteAdministrar('descargar')">
                            <i class="fas fa-download mr-2"></i> Descargar Seleccionados
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider w-14 text-center">
                                    <input type="checkbox" id="seleccionar_todos_admin" class="w-4 h-4 rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue">
                                </th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">CI</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Legajo</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Armado por</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nro. Solicitud</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Observación</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($resultados)): ?>
                                <?php foreach ($resultados as $resultado): ?>
                                    <?php
                                    $armadoPor = trim((string)($resultado['nombre_usuario_armado'] ?? ''));
                                    $observacionLegajo = trim((string)($resultado['observacion'] ?? ''));
                                    $estadoTexto = $resultado['estado_legajo_texto'] ?? ucfirst($resultado['estado'] ?? '');
                                    $claseEstado = 'bg-gray-100 text-gray-700';
                                    if ($estadoTexto === 'Cerrado') {
                                        $claseEstado = 'bg-slate-200 text-slate-800';
                                    } elseif ($estadoTexto === 'Verificación rechazada') {
                                        $claseEstado = 'bg-amber-100 text-amber-800';
                                    } elseif ($estadoTexto === 'Completado') {
                                        $claseEstado = 'bg-green-100 text-green-800';
                                    } elseif ($estadoTexto === 'Verificado') {
                                        $claseEstado = 'bg-cyan-100 text-cyan-800';
                                    } elseif ($estadoTexto === 'Vencido' || $estadoTexto === 'Incompleto') {
                                        $claseEstado = 'bg-red-100 text-red-800';
                                    }
                                    ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" name="legajos[]" value="<?php echo intval($resultado['id_legajo'] ?? 0); ?>" class="chk-legajo-admin w-4 h-4 rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue">
                                        </td>
                                        <td class="px-6 py-3 font-bold text-gray-800 text-sm">
                                            <?php echo htmlspecialchars($formatearCi($resultado['ci_socio'] ?? '')); ?>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700 text-sm">
                                            <?php echo htmlspecialchars($resultado['nombre_completo'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 text-sm">
                                            <?php echo htmlspecialchars($resultado['nombre_tipo_legajo'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 text-sm">
                                            <?php echo htmlspecialchars($armadoPor !== '' ? $armadoPor : 'Aún no armado'); ?>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 text-sm">
                                            <?php echo htmlspecialchars($resultado['nro_solicitud'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-3 text-gray-600 text-sm max-w-xs">
                                            <div class="truncate" title="<?php echo htmlspecialchars($observacionLegajo); ?>">
                                                <?php echo htmlspecialchars($observacionLegajo !== '' ? $observacionLegajo : 'Sin observación'); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 text-sm">
                                            <span class="px-2 py-1 inline-flex text-xs font-bold rounded-full <?php echo $claseEstado; ?>">
                                                <?php echo htmlspecialchars($estadoTexto); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <?php if (!empty($resultado['pdf_final_disponible'])): ?>
                                                <a href="<?php echo base_url(); ?>legajos/ver_pdf_final/<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    class="w-10 h-10 bg-red-700 text-white rounded-lg font-bold text-xs hover:bg-red-900 transition-all inline-flex items-center justify-center"
                                                    title="Ver PDF"
                                                    target="_blank"
                                                    rel="noopener noreferrer">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <?php else: ?>
                                                <button type="button"
                                                    class="w-10 h-10 bg-red-200 text-red-400 rounded-lg font-bold text-xs cursor-not-allowed inline-flex items-center justify-center opacity-80"
                                                    title="Ver PDF no disponible"
                                                    disabled>
                                                    <i class="fas fa-file-pdf"></i>
                                                </button>
                                                <?php endif; ?>
                                                <a href="<?php echo base_url(); ?>legajos/armar_legajo?id_legajo=<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    class="w-10 h-10 bg-scantec-blue text-white rounded-lg font-bold text-xs hover:bg-blue-800 transition-all inline-flex items-center justify-center"
                                                    title="Abrir">
                                                    <i class="fas fa-folder-open"></i>
                                                </a>
                                                <?php if ($puedeAdministrarLegajo): ?>
                                                <button type="button"
                                                    class="w-10 h-10 bg-slate-700 text-white rounded-lg font-bold text-xs hover:bg-slate-900 transition-all inline-flex items-center justify-center btn-cerrar-legajo"
                                                    data-action="<?php echo base_url(); ?>legajos/cerrar_legajo/<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    data-id-legajo="<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    title="Cerrar">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if ($puedeEliminarLegajo): ?>
                                                <button type="button"
                                                    class="w-10 h-10 bg-red-600 text-white rounded-lg font-bold text-xs hover:bg-red-800 transition-all inline-flex items-center justify-center btn-eliminar-legajo"
                                                    data-action="<?php echo base_url(); ?>legajos/eliminar_legajo/<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    data-id-legajo="<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-6 text-center text-sm text-gray-500">
                                        No hay legajos para mostrar con los filtros aplicados.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectorTodos = document.getElementById('seleccionar_todos_admin');
        const checksAdmin = Array.from(document.querySelectorAll('.chk-legajo-admin'));
        const formularioLote = document.getElementById('frmLoteAdministrarLegajos');
        const accionLote = document.getElementById('accion_lote_admin');

        if (selectorTodos) {
            selectorTodos.addEventListener('change', function () {
                checksAdmin.forEach((check) => {
                    check.checked = selectorTodos.checked;
                });
            });
        }

        checksAdmin.forEach((check) => {
            check.addEventListener('change', function () {
                if (!selectorTodos) {
                    return;
                }
                selectorTodos.checked = checksAdmin.length > 0 && checksAdmin.every((item) => item.checked);
            });
        });

        window.confirmarAccionLoteAdministrar = function (accion) {
            const seleccionados = checksAdmin.filter((check) => check.checked).length;
            if (seleccionados <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Seleccione al menos un legajo para continuar.'
                });
                return;
            }

            const configuracion = accion === 'descargar'
                ? {
                    titulo: 'Descargar legajos seleccionados',
                    texto: 'Se preparará un archivo ZIP con los PDFs finales disponibles.',
                    confirmacion: 'Sí, descargar',
                    color: '#059669'
                }
                : {
                    titulo: 'Rearmar legajos seleccionados',
                    texto: 'Se regenerarán los PDFs finales de los legajos seleccionados.',
                    confirmacion: 'Sí, rearmar',
                    color: '#1d4ed8'
                };

            Swal.fire({
                icon: 'question',
                title: configuracion.titulo,
                text: configuracion.texto,
                showCancelButton: true,
                confirmButtonText: configuracion.confirmacion,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: configuracion.color
            }).then((result) => {
                if (result.isConfirmed) {
                    accionLote.value = accion;
                    HTMLFormElement.prototype.submit.call(formularioLote);
                }
            });
        };

        const csrfToken = <?php echo json_encode($_SESSION['csrf_token'] ?? '', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const terminoActual = <?php echo json_encode($termino, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const estadoActual = <?php echo json_encode($estado_legajo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const tipoActual = <?php echo json_encode((string) intval($id_tipo_legajo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        const enviarAccionIndividual = (action, idLegajo) => {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = action;
            form.style.display = 'none';

            const campos = {
                token: csrfToken,
                id_legajo: idLegajo,
                termino: terminoActual,
                estado_legajo: estadoActual,
                id_tipo_legajo: tipoActual
            };

            Object.entries(campos).forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        };

        document.querySelectorAll('.btn-cerrar-legajo').forEach((button) => {
            button.addEventListener('click', function () {
                const action = this.dataset.action || '';
                const idLegajo = this.dataset.idLegajo || '';
                Swal.fire({
                    icon: 'warning',
                    title: 'Cerrar legajo',
                    text: '¿Confirma que este legajo debe cerrarse? Luego ya no podrá modificarse.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cerrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#334155'
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarAccionIndividual(action, idLegajo);
                    }
                });
            });
        });

        document.querySelectorAll('.btn-eliminar-legajo').forEach((button) => {
            button.addEventListener('click', function () {
                const action = this.dataset.action || '';
                const idLegajo = this.dataset.idLegajo || '';
                Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar legajo',
                    text: '¿Confirma que desea eliminar este legajo? Esta acción no podrá deshacerse.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626'
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarAccionIndividual(action, idLegajo);
                    }
                });
            });
        });
    });
</script>

<?php pie() ?>
