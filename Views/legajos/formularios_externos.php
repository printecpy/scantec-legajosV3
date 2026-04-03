<?php encabezado(); ?>

<?php
$tipos_legajo = $data['tipos_legajo'] ?? [];
$legajo_base = $data['legajo_base'] ?? [];
$formularios = $data['formularios'] ?? [];
$id_legajo_base = intval($data['id_legajo_base'] ?? 0);
$link_generado = trim((string)($data['link_generado'] ?? ''));
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-file-signature mr-3"></i> Solicitar Documentos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Genere enlaces únicos para solicitar documentos y datos a clientes o empleados sin que ingresen al sistema.
                </p>
            </div>
            <a href="<?php echo base_url(); ?>dashboard/dashboard_legajos"
                class="px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <?php if (!empty($legajo_base)): ?>
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 mb-6">
            <h3 class="font-bold text-scantec-blue mb-2">Legajo base</h3>
            <p class="text-sm text-gray-700">
                <?php echo htmlspecialchars($legajo_base['nombre_completo'] ?? ''); ?>
                | CI: <?php echo htmlspecialchars($legajo_base['ci_socio'] ?? ''); ?>
                | Solicitud: <?php echo htmlspecialchars($legajo_base['nro_solicitud'] ?? '-'); ?>
            </p>
        </div>
        <?php endif; ?>

        <?php if ($link_generado !== ''): ?>
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6">
            <h3 class="font-bold text-green-800 mb-2">Link generado</h3>
            <div class="flex flex-col md:flex-row gap-3">
                <input type="text" readonly value="<?php echo htmlspecialchars($link_generado); ?>"
                    class="flex-1 rounded-xl border border-green-200 bg-white px-4 py-3 text-sm text-gray-700">
                <button
                    type="button"
                    id="btnCopiarLinkFormularioExterno"
                    data-link="<?php echo htmlspecialchars($link_generado, ENT_QUOTES); ?>"
                    class="px-5 py-3 rounded-xl bg-green-700 text-white font-bold hover:bg-green-800 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-copy" aria-hidden="true"></i>
                    <span>Copiar link</span>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                <h5 class="font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-2"></i> Generar nueva solicitud
                </h5>
            </div>

            <form action="<?php echo base_url(); ?>legajos/generar_formulario_externo" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="id_legajo_base" value="<?php echo $id_legajo_base; ?>">
                <input type="hidden" name="tipo_destinatario" value="cliente">

                <input type="hidden" name="modo_carga" value="nuevo">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Vigencia del link</label>
                    <select name="horas_vigencia" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700">
                        <option value="1">1 hora</option>
                        <option value="3">3 horas</option>
                        <option value="24">24 horas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Tipo de legajo</label>
                    <select id="idTipoLegajoFormularioExterno" name="id_tipo_legajo" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700">
                        <?php foreach ($tipos_legajo as $tipo): ?>
                        <option
                            value="<?php echo intval($tipo['id_tipo_legajo'] ?? 0); ?>"
                            data-nombre="<?php echo htmlspecialchars(mb_strtolower((string)($tipo['nombre'] ?? ''), 'UTF-8')); ?>"
                            data-requiere-solicitud="<?php echo !empty($tipo['requiere_nro_solicitud']) ? '1' : '0'; ?>"
                            <?php echo intval($tipo['id_tipo_legajo'] ?? 0) === intval($legajo_base['id_tipo_legajo'] ?? 0) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['nombre'] ?? ''); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Cédula obligatoria</label>
                    <input type="text" name="ci_validacion" required value="<?php echo htmlspecialchars($legajo_base['ci_socio'] ?? ''); ?>"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Nombre de referencia</label>
                    <input type="text" name="nombre_referencia" value="<?php echo htmlspecialchars($legajo_base['nombre_completo'] ?? ''); ?>"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700">
                </div>

                <div class="md:col-span-2" id="contenedorSolicitudFormularioExterno">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Número de solicitud</label>
                    <input type="text" id="inputSolicitudFormularioExterno" name="nro_solicitud_referencia" value="<?php echo htmlspecialchars($legajo_base['nro_solicitud'] ?? ''); ?>"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700">
                    <p id="ayudaSolicitudFormularioExterno" class="text-xs text-gray-500 mt-2 hidden">
                        Este tipo de legajo requiere número de solicitud.
                    </p>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-scantec-blue hover:bg-gray-800 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition-all">
                        Generar link
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                <h5 class="font-bold text-white flex items-center">
                    <i class="fas fa-clock mr-2"></i> Links recientes
                </h5>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Tipo</th>
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Cédula</th>
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Modo</th>
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Estado</th>
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Vence</th>
                            <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($formularios)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay links generados todavía.</td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($formularios as $formulario): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-gray-800"><?php echo htmlspecialchars($formulario['nombre_tipo_legajo'] ?? ''); ?></td>
                            <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars($formulario['ci_validacion'] ?? ''); ?></td>
                            <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars(ucfirst($formulario['modo_carga'] ?? 'nuevo')); ?></td>
                            <td class="px-6 py-4 text-gray-700"><?php echo htmlspecialchars(ucfirst($formulario['estado'] ?? 'activo')); ?></td>
                            <td class="px-6 py-4 text-gray-700"><?php echo !empty($formulario['vence_en']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($formulario['vence_en']))) : '-'; ?></td>
                            <td class="px-6 py-4">
                                <a href="<?php echo htmlspecialchars(base_url() . 'legajos/formulario_externo?token=' . urlencode($formulario['token'] ?? '')); ?>"
                                    target="_blank"
                                    class="text-scantec-blue font-bold hover:underline">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php pie(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectTipoLegajo = document.getElementById('idTipoLegajoFormularioExterno');
    const contenedorSolicitud = document.getElementById('contenedorSolicitudFormularioExterno');
    const inputSolicitud = document.getElementById('inputSolicitudFormularioExterno');
    const ayudaSolicitud = document.getElementById('ayudaSolicitudFormularioExterno');
    const btnCopiar = document.getElementById('btnCopiarLinkFormularioExterno');

    function actualizarSolicitudRequerida() {
        if (!selectTipoLegajo || !contenedorSolicitud || !inputSolicitud || !ayudaSolicitud) {
            return;
        }

        const opcionSeleccionada = selectTipoLegajo.options[selectTipoLegajo.selectedIndex];
        const nombreTipo = ((opcionSeleccionada && opcionSeleccionada.getAttribute('data-nombre')) || '').toLowerCase();
        const requierePorConfig = opcionSeleccionada && opcionSeleccionada.getAttribute('data-requiere-solicitud') === '1';
        const esTipoEmpleado = nombreTipo.indexOf('empleado') !== -1;
        const requiereSolicitud = requierePorConfig && !esTipoEmpleado;

        inputSolicitud.required = requiereSolicitud;
        inputSolicitud.disabled = !requiereSolicitud;
        contenedorSolicitud.classList.toggle('hidden', !requiereSolicitud);
        ayudaSolicitud.classList.toggle('hidden', !requiereSolicitud);

        if (!requiereSolicitud) {
            inputSolicitud.value = '';
        }
    }

    if (selectTipoLegajo) {
        selectTipoLegajo.addEventListener('change', actualizarSolicitudRequerida);
        actualizarSolicitudRequerida();
    }

    if (btnCopiar) {
        btnCopiar.addEventListener('click', async function() {
            const link = btnCopiar.getAttribute('data-link') || '';
            const icono = btnCopiar.querySelector('i');
            const texto = btnCopiar.querySelector('span');
            if (link === '') {
                return;
            }

            try {
                await navigator.clipboard.writeText(link);
                if (icono) {
                    icono.className = 'fas fa-check';
                }
                if (texto) {
                    texto.textContent = 'Copiado';
                }
                btnCopiar.classList.remove('bg-green-700', 'hover:bg-green-800');
                btnCopiar.classList.add('bg-emerald-600');
            } catch (error) {
                if (texto) {
                    texto.textContent = 'No se pudo copiar';
                }
            }
        });
    }
});
</script>
