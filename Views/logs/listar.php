<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-database"></i>
                        </div>
                        Bitácora Scantec
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Auditoría interna de consultas y reversiones SQL del sistema.</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a target="_blank" href="<?php echo base_url(); ?>logs/pdf" title="Exportar a PDF" 
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm">
                        <i class="fas fa-file-pdf text-lg"></i>
                    </a>
                    
                    <a target="_blank" href="<?php echo base_url(); ?>logs/excel" title="Exportar a Excel" 
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
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap w-48">Fecha</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Execute SQL</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Reverse SQL</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data)) {
                                // Cambié la variable local a $log para que tenga más sentido en esta vista, 
                                // pero sigue leyendo de tu $data sin problemas.
                                foreach ($data as $log) { 
                            ?>
                                <tr class="hover:bg-gray-50/70 transition-colors group">
                                    <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap align-top">
                                        <i class="far fa-clock mr-1 opacity-50"></i> <?php echo $log['fecha']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-700 align-top max-w-xl truncate" title="<?php echo htmlspecialchars($log['executedSQL']); ?>">
                                        <?php echo $log['executedSQL']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-500 align-top max-w-xl truncate" title="<?php echo htmlspecialchars($log['reverseSQL']); ?>">
                                        <?php echo $log['reverseSQL']; ?>
                                    </td>
                                </tr>
                            <?php 
                                } 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        <i class="fas fa-database text-4xl mb-3 block opacity-30"></i>
                                        No hay registros de auditoría SQL actualmente.
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
