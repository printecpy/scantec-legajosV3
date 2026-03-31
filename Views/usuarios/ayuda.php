<?php encabezado(); ?>

<?php
$manuales = $data['manuales'] ?? [];
$manualSeleccionado = $data['manual_seleccionado'] ?? '';
$manualActual = $manuales[$manualSeleccionado] ?? null;
$urlManualActual = $manualActual ? (base_url() . 'usuarios/ver_manual_ayuda?manual=' . urlencode($manualSeleccionado)) : '';
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-question-circle mr-3"></i> Centro de Ayuda
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Seleccione el manual que desea consultar según el tipo de usuario.
                </p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                title="Volver">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider">Manuales disponibles</h2>
                        <p class="text-xs text-blue-100 mt-1">Puede abrirlos en pantalla o descargarlos cuando lo necesite.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <form method="GET" action="<?php echo base_url(); ?>usuarios/Ayuda" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                    <div class="lg:col-span-7">
                        <label for="manual" class="block text-xs font-bold text-gray-500 uppercase mb-2">Manual</label>
                        <select id="manual" name="manual"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-white">
                            <?php foreach ($manuales as $clave => $manual): ?>
                                <option value="<?php echo htmlspecialchars($clave); ?>" <?php echo $clave === $manualSeleccionado ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($manual['titulo'] ?? 'Manual'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="lg:col-span-5 flex gap-3">
                        <button type="button"
                            id="btnVerManual"
                            class="px-5 py-3 bg-scantec-blue text-white rounded-xl font-bold shadow-sm hover:bg-blue-800 transition-all inline-flex items-center">
                            <i class="fas fa-eye mr-2"></i> Ver manual
                        </button>
                        <?php if ($urlManualActual !== ''): ?>
                            <a href="<?php echo $urlManualActual; ?>"
                                id="btnDescargarManual"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-5 py-3 bg-gray-800 text-white rounded-xl font-bold shadow-sm hover:bg-black transition-all inline-flex items-center">
                                <i class="fas fa-download mr-2"></i> Descargar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($manualActual)): ?>
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <h3 class="text-base font-bold text-gray-800"><?php echo htmlspecialchars($manualActual['titulo'] ?? 'Manual'); ?></h3>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php echo htmlspecialchars($manualActual['descripcion'] ?? ''); ?>
                        </p>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 text-sm text-blue-900">
                        El botón <span class="font-bold">Ver manual</span> abre el documento seleccionado en una pestaña nueva para una lectura más cómoda.
                    </div>
                <?php else: ?>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 font-semibold">
                        No hay manuales configurados para mostrar.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectManual = document.getElementById('manual');
        const btnVerManual = document.getElementById('btnVerManual');
        const btnDescargarManual = document.getElementById('btnDescargarManual');
        const baseUrl = <?php echo json_encode(base_url(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        function obtenerUrlManualSeleccionado() {
            const manual = selectManual ? String(selectManual.value || '').trim() : '';
            if (!manual) {
                return '';
            }
            return baseUrl + 'usuarios/ver_manual_ayuda?manual=' + encodeURIComponent(manual);
        }

        function refrescarAccionesManual() {
            const url = obtenerUrlManualSeleccionado();
            if (btnDescargarManual && url) {
                btnDescargarManual.href = url;
            }
        }

        if (selectManual) {
            selectManual.addEventListener('change', refrescarAccionesManual);
        }

        if (btnVerManual) {
            btnVerManual.addEventListener('click', function () {
                const url = obtenerUrlManualSeleccionado();
                if (url !== '') {
                    window.open(url, '_blank', 'noopener,noreferrer');
                }
            });
        }

        refrescarAccionesManual();
    });
</script>

<?php pie(); ?>
