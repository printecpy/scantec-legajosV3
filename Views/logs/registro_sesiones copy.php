<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        Registros de Sesiones Scantec
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Auditoría de accesos, tiempos de conexión y actividad de los usuarios.</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_sesionesPdf" title="Exportar a PDF" 
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm">
                        <i class="fas fa-file-pdf text-lg"></i>
                    </a>
                    
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_sesionesExcel" title="Exportar a Excel" 
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Fecha Inicio</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Dirección IP</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Nombre de HOST</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Nombre del Usuario</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Usuario (Login)</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Fecha Cierre</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data['registro_sesiones'])) {
                                foreach ($data['registro_sesiones'] as $sesion) { 
                            ?>
                                <tr class="hover:bg-gray-50/70 transition-colors group">
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <i class="far fa-calendar-alt mr-1 opacity-50"></i> <?php echo $sesion['fecha']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                                        <?php echo $sesion['ip']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <i class="fas fa-desktop mr-1 opacity-50"></i> <?php echo $sesion['servidor']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-800 whitespace-nowrap">
                                        <?php echo $sesion['nombre']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <span class="px-2 py-1 bg-gray-100 rounded-md border border-gray-200">
                                            @<?php echo $sesion['usuario']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <?php if (!empty($sesion['fecha_cierre'])): ?>
                                            <i class="far fa-calendar-check mr-1 opacity-50"></i> <?php echo $sesion['fecha_cierre']; ?>
                                        <?php else: ?>
                                            <span class="text-green-600 font-semibold italic">Sesión Activa</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        <i class="fas fa-user-clock text-4xl mb-3 block opacity-30"></i>
                                        No hay registros de sesiones disponibles.
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