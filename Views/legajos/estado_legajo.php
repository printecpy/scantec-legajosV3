<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4 max-w-7xl">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-shield-check mr-3"></i> Validacion Documental
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Auditoria de requisitos y vencimientos en tiempo real.
                </p>
            </div>
            <a href="<?php echo base_url(); ?>expedientes/indice_busqueda"
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Busqueda
            </a>
        </div>

        <?php
        $socio = $data['socio'] ?? ['nombre' => 'Juan Perez', 'ci' => '4.500.200', 'tipo_legajo' => 'Carpeta de Credito'];
        $estado_global = $data['estado_global'] ?? 'COMPLETO';

        $bg_global = ($estado_global == 'COMPLETO') ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
        $icon_global = ($estado_global == 'COMPLETO') ? 'fa-check-circle' : 'fa-times-circle';
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8 flex flex-col md:flex-row justify-between items-center">

            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-full bg-blue-50 flex items-center justify-center text-scantec-blue text-2xl">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($socio['nombre']); ?></h2>
                    <p class="text-sm text-gray-600 font-medium">
                        <i class="fas fa-id-card text-gray-400 mr-1"></i> CI: <?php echo htmlspecialchars($socio['ci']); ?> |
                        <i class="fas fa-folder text-gray-400 mr-1 ml-2"></i> <?php echo htmlspecialchars($socio['tipo_legajo']); ?>
                    </p>
                </div>
            </div>

            <div class="mt-4 md:mt-0 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold border <?php echo $bg_global; ?>">
                    <i class="fas <?php echo $icon_global; ?> mr-2"></i>
                    LEGAJO <?php echo $estado_global; ?>
                </span>

                <?php if ($estado_global == 'COMPLETO'): ?>
                    <a href="<?php echo base_url(); ?>expedientes/exportar_legajo_unificado/<?php echo urlencode($socio['ci']); ?>"
                        class="px-5 py-2 bg-gray-800 text-white border border-gray-700 rounded-lg text-sm font-bold shadow-md hover:bg-black transition-all flex items-center group"
                        title="Unir todos los documentos vigentes en un solo PDF">
                        <i class="fas fa-file-pdf mr-2 text-red-400 group-hover:text-red-500 transition-colors"></i>
                        Descargar Legajo Completo
                    </a>
                <?php else: ?>
                    <button disabled
                        class="px-5 py-2 bg-gray-100 text-gray-400 border border-gray-200 rounded-lg text-sm font-bold flex items-center cursor-not-allowed"
                        title="Debe cargar todos los documentos obligatorios para habilitar la exportacion">
                        <i class="fas fa-lock mr-2 text-gray-400"></i>
                        Descargar Legajo Completo
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $roles_simulados = ['TITULAR', 'CONYUGE'];
        foreach ($roles_simulados as $rol):
        ?>
            <h3 class="text-lg font-bold text-scantec-blue uppercase tracking-wider mb-4 flex items-center">
                <i class="fas <?php echo ($rol == 'TITULAR') ? 'fa-user' : 'fa-user-friends'; ?> mr-2"></i>
                Documentacion: <?php echo $rol; ?>
            </h3>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Documento Requerido</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Estado (Semaforo)</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Vencimiento</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <?php
                            $simulacion_docs = [
                                ['nombre' => 'Cedula de Identidad', 'obligatorio' => 'SI', 'estado' => 'VIGENTE', 'fecha' => '01/01/2034', 'id_expediente' => 150],
                                ['nombre' => 'Certificado de Trabajo', 'obligatorio' => 'SI', 'estado' => 'FALTANTE', 'fecha' => null, 'id_expediente' => null],
                                ['nombre' => 'Liquidacion de IVA', 'obligatorio' => 'NO', 'estado' => 'POR VENCER', 'fecha' => '15/03/2026', 'id_expediente' => 151],
                            ];
                            if ($rol == 'CONYUGE') {
                                $simulacion_docs = [['nombre' => 'Cedula de Identidad', 'obligatorio' => 'NO', 'estado' => 'OPCIONAL', 'fecha' => null, 'id_expediente' => null]];
                            }

                            foreach ($simulacion_docs as $doc):
                                $badge_class = 'bg-gray-100 text-gray-600';
                                if (strpos($doc['estado'], 'VIGENTE') !== false) {
                                    $badge_class = 'bg-green-100 text-green-800';
                                }
                                if (strpos($doc['estado'], 'FALTANTE') !== false || strpos($doc['estado'], 'VENCIDO') !== false) {
                                    $badge_class = 'bg-red-100 text-red-800';
                                }
                                if (strpos($doc['estado'], 'POR VENCER') !== false) {
                                    $badge_class = 'bg-yellow-100 text-yellow-800';
                                }
                            ?>
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900"><?php echo $doc['nombre']; ?></div>
                                        <?php if ($doc['obligatorio'] == 'SI'): ?>
                                            <div class="text-xs text-red-500 font-bold">* Obligatorio</div>
                                        <?php else: ?>
                                            <div class="text-xs text-gray-400 font-semibold">Opcional</div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo $badge_class; ?> shadow-sm">
                                            <?php echo $doc['estado']; ?>
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-center text-sm <?php echo ($doc['fecha']) ? 'text-gray-700 font-semibold' : 'text-gray-400 italic'; ?>">
                                        <?php echo $doc['fecha'] ?? 'Sin registro'; ?>
                                    </td>

                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <?php if ($doc['id_expediente']): ?>
                                            <button class="text-scantec-blue hover:text-blue-900 mr-3 transition-colors" title="Ver Documento">
                                                <i class="fas fa-eye"></i> Ver PDF
                                            </button>
                                            <button class="text-gray-500 hover:text-gray-900 transition-colors" title="Actualizar PDF">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="bg-scantec-blue text-white px-4 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-blue-800 transition-colors">
                                                <i class="fas fa-upload mr-1"></i> Subir PDF
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="mt-8 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-wrap gap-4 items-center justify-center text-xs font-bold text-gray-600">
            <span class="mr-2 uppercase tracking-wide text-gray-400">Leyenda:</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1"></span> Vigente</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400 mr-1"></span> Vence en &lt; 30 dias</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span> Faltante / Vencido</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-gray-300 mr-1"></span> Opcional sin cargar</span>
        </div>

    </div>
</main>

<?php pie() ?>
