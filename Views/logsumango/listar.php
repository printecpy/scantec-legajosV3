<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                                <i class="fas fa-server"></i>
                            </div>
                            Bitácora del Motor de Documentos
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Auditoría detallada de los procesos de captura.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a target="_blank" href="<?php echo base_url(); ?>logsumango/pdf" title="Exportar a PDF"
                            class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm">
                            <i class="fas fa-file-pdf text-lg"></i>
                        </a>

                        <a target="_blank" href="<?php echo base_url(); ?>logsumango/excel" title="Exportar a Excel"
                            class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-green-500 rounded-lg hover:bg-green-50 hover:text-green-600 hover:border-green-200 transition-colors shadow-sm">
                            <i class="fas fa-file-excel text-lg"></i>
                        </a>

                        <a href="<?php echo base_url(); ?>logsumango/reporte" title="Ver Reporte"
                            class="ml-1 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold rounded-lg transition-all shadow-sm flex items-center gap-2">
                            <i class="fas fa-chart-pie"></i> Reporte
                        </a>
                    </div>
                </div>


            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    ID Proceso Umango</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Nro Lote</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Fuente Captura</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Archivo Orig.</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap text-center">
                                    Orden Doc.</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap text-center">
                                    Pág.</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Fecha Inicio</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Fecha Fin</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Creador</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Usuario</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap text-center">
                                    Estado</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Nombre Host</th>
                                <th
                                    class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">
                                    Dirección IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data)) {
                                foreach ($data as $logsumango) { 
                                    // Lógica visual opcional: Si el estado es "Error" u "OK", puedes cambiar el color
                                    $estado = strtoupper(trim($logsumango['estado']));
                                    $badgeBg = 'bg-gray-100 text-gray-700 border-gray-200';
                                    
                                    if ($estado == 'OK' || $estado == 'COMPLETADO' || $estado == 'SUCCESS') {
                                        $badgeBg = 'bg-green-50 text-green-700 border-green-200';
                                    } elseif ($estado == 'ERROR' || $estado == 'FALLIDO') {
                                        $badgeBg = 'bg-red-50 text-red-700 border-red-200';
                                    }
                            ?>
                            <tr class="hover:bg-gray-50/70 transition-colors">
                                <td class="px-4 py-3 text-xs font-bold text-gray-800 whitespace-nowrap">
                                    <?php echo $logsumango['id_proceso_umango']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    <?php echo $logsumango['id_lote']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    <?php echo $logsumango['fuente_captura']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap max-w-xs truncate"
                                    title="<?php echo $logsumango['archivo_origen']; ?>">
                                    <?php echo $logsumango['archivo_origen']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap text-center">
                                    <?php echo $logsumango['orden_documento']; ?></td>
                                <td class="px-4 py-3 text-xs font-semibold text-gray-700 whitespace-nowrap text-center">
                                    <?php echo $logsumango['paginas_exportadas']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><i
                                        class="far fa-calendar-alt mr-1 opacity-50"></i><?php echo $logsumango['fecha_inicio']; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><i
                                        class="far fa-calendar-check mr-1 opacity-50"></i><?php echo $logsumango['fecha_finalizacion']; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    <?php echo $logsumango['creador']; ?></td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                    <?php echo $logsumango['usuario']; ?></td>
                                <td class="px-4 py-3 text-xs whitespace-nowrap text-center">
                                    <span
                                        class="px-2.5 py-1 rounded-md border <?php echo $badgeBg; ?> font-bold text-[10px] tracking-wide">
                                        <?php echo $logsumango['estado']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap"><i
                                        class="fas fa-desktop mr-1 opacity-50"></i><?php echo $logsumango['nombre_host']; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 font-mono whitespace-nowrap">
                                    <?php echo $logsumango['ip_host']; ?></td>
                            </tr>
                            <?php 
                                } 
                            } else {
                            ?>
                            <tr>
                                <td colspan="13" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    <i class="fas fa-server text-4xl mb-3 block opacity-30"></i>
                                    No hay registros en la bitácora actualmente.
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