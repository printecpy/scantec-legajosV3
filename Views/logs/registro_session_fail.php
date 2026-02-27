<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-red-500">
                            <i class="fas fa-user-lock"></i>
                        </div>
                        Sesiones Fallidas y Bloqueos
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Auditoría de seguridad: Intentos de acceso fallidos y usuarios bloqueados por el sistema.</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_session_failPdf" title="Exportar a PDF" 
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm">
                        <i class="fas fa-file-pdf text-lg"></i>
                    </a>
                    
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_session_failExcel" title="Exportar a Excel" 
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-green-500 rounded-lg hover:bg-green-50 hover:text-green-600 hover:border-green-200 transition-colors shadow-sm">
                        <i class="fas fa-file-excel text-lg"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Usuario</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Nombre de HOST</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Dirección IP</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Fecha y Hora</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap w-1/3">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data['registro_session_fail'])) {
                                foreach ($data['registro_session_fail'] as $fallo) { 
                            ?>
                                <tr class="hover:bg-red-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-800 whitespace-nowrap">
                                        <i class="fas fa-user text-gray-400 mr-1.5"></i> <?php echo $fallo['usuario']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <i class="fas fa-desktop mr-1 opacity-50"></i> <?php echo $fallo['nombre_pc']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                                        <?php echo $fallo['direccion_ip']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <i class="far fa-clock mr-1 opacity-50 text-red-400"></i> <?php echo $fallo['timestamp']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-red-600">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-red-50 border border-red-100">
                                            <i class="fas fa-exclamation-triangle mr-1.5 opacity-70"></i>
                                            <?php echo $fallo['motivo']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        <i class="fas fa-shield-alt text-4xl mb-3 block opacity-30 text-green-500"></i>
                                        ¡Todo seguro! No hay registros de sesiones fallidas ni bloqueos.
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
    <?php pie() ?>
</div>