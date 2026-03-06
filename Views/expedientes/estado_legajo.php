<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4 max-w-7xl">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-shield-check mr-3"></i> Validación Documental (Legajo)
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Auditoría de requisitos y vencimientos en tiempo real.
                </p>
            </div>
            <a href="<?php echo base_url(); ?>expedientes/indice_busqueda" 
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Volver a Búsqueda
            </a>
        </div>

        <?php 
            // Variables simuladas que enviará tu controlador
            $socio = $data['socio'] ?? ['nombre' => 'Juan Pérez', 'ci' => '4.500.200', 'tipo_legajo' => 'Carpeta de Crédito'];
            $estado_global = $data['estado_global'] ?? 'BLOQUEADO'; // Puede ser 'COMPLETO' o 'BLOQUEADO'
            
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
            <div class="mt-4 md:mt-0">
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold border <?php echo $bg_global; ?>">
                    <i class="fas <?php echo $icon_global; ?> mr-2"></i>
                    LEGAJO <?php echo $estado_global; ?>
                </span>
            </div>
        </div>

        <?php 
            // El controlador debe agrupar los resultados SQL por 'Rol' (TITULAR, CONYUGE, etc.)
            // if(!empty($data['documentos_por_rol'])): 
            // foreach($data['documentos_por_rol'] as $rol => $documentos): 
            
            // Simulación del foreach para que veas la estructura (borrar en producción)
            $roles_simulados = ['TITULAR', 'CÓNYUGE']; 
            foreach($roles_simulados as $rol): 
        ?>
        
        <h3 class="text-lg font-bold text-scantec-blue uppercase tracking-wider mb-4 flex items-center">
            <i class="fas <?php echo ($rol == 'TITULAR') ? 'fa-user' : 'fa-user-friends'; ?> mr-2"></i> 
            Documentación: <?php echo $rol; ?>
        </h3>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Documento Requerido</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Estado (Semáforo)</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Vencimiento</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        
                        <?php 
                            // Aquí iría tu: foreach($documentos as $doc):
                            // Voy a simular 3 estados diferentes para que veas cómo funciona el PHP mixto
                            $simulacion_docs = [
                                ['nombre' => 'Cédula de Identidad', 'obligatorio' => 'SI', 'estado' => '✅ VIGENTE', 'fecha' => '01/01/2034', 'id_expediente' => 150],
                                ['nombre' => 'Certificado de Trabajo', 'obligatorio' => 'SI', 'estado' => '🔴 FALTANTE', 'fecha' => null, 'id_expediente' => null],
                                ['nombre' => 'Liquidación de IVA', 'obligatorio' => 'NO', 'estado' => '⚠️ POR VENCER', 'fecha' => '15/03/2026', 'id_expediente' => 151],
                            ];
                            if ($rol == 'CÓNYUGE') {
                                $simulacion_docs = [['nombre' => 'Cédula de Identidad', 'obligatorio' => 'NO', 'estado' => '⚪ OPCIONAL', 'fecha' => null, 'id_expediente' => null]];
                            }

                            foreach($simulacion_docs as $doc): 
                                
                                // Lógica del Semáforo en PHP (Traduciendo la respuesta del SQL a clases de Tailwind)
                                $badge_class = 'bg-gray-100 text-gray-600'; // Default Opcional
                                if (strpos($doc['estado'], 'VIGENTE') !== false) $badge_class = 'bg-green-100 text-green-800';
                                if (strpos($doc['estado'], 'FALTANTE') !== false) $badge_class = 'bg-red-100 text-red-800';
                                if (strpos($doc['estado'], 'VENCIDO') !== false) $badge_class = 'bg-red-100 text-red-800';
                                if (strpos($doc['estado'], 'POR VENCER') !== false) $badge_class = 'bg-yellow-100 text-yellow-800';
                        ?>
                        <tr class="hover:bg-blue-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900"><?php echo $doc['nombre']; ?></div>
                                <?php if($doc['obligatorio'] == 'SI'): ?>
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
                                <?php if($doc['id_expediente']): ?>
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
        <?php endforeach; // Fin del bucle de Roles ?>

        <div class="mt-8 bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-wrap gap-4 items-center justify-center text-xs font-bold text-gray-600">
            <span class="mr-2 uppercase tracking-wide text-gray-400">Leyenda:</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-1"></span> Vigente</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400 mr-1"></span> Vence en < 30 días</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-1"></span> Faltante / Vencido</span>
            <span class="flex items-center"><span class="w-3 h-3 rounded-full bg-gray-300 mr-1"></span> Opcional sin cargar</span>
        </div>

    </div>
</main>

<?php pie() ?>