<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                        Historial de Envíos
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Registro detallado de todas las alertas enviadas por el sistema.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="<?php echo base_url(); ?>alerta/listar" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Volver a Alertas
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha de Envío</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Destinatario</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data['historial'])) {
                                foreach ($data['historial'] as $log) { 
                                    // Lógica para Tailwind colors según estado
                                    $isSuccess = ($log['estado'] == 'Exitoso');
                                    $badgeBg = $isSuccess ? 'bg-green-50' : 'bg-red-50';
                                    $badgeText = $isSuccess ? 'text-green-700' : 'text-red-700';
                                    $badgeBorder = $isSuccess ? 'border-green-200' : 'border-red-200';
                                    $icon = $isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
                            ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center text-sm text-gray-600 font-medium">
                                        <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                        <?php echo $log['fecha_envio']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800"><?php echo $log['correo_destino']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo "$badgeBg $badgeText $badgeBorder border"; ?>">
                                        <i class="fas <?php echo $icon; ?> mr-1.5"></i>
                                        <?php echo $log['estado']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-500 truncate max-w-xs" title="<?php echo htmlspecialchars($log['detalle']); ?>">
                                        <?php echo $log['detalle']; ?>
                                    </p>
                                </td>
                            </tr>
                            <?php 
                                } 
                            } else {
                                echo "<tr><td colspan='4' class='px-6 py-12 text-center text-gray-400 font-medium'>
                                <i class='fas fa-inbox text-3xl mb-3 block opacity-50'></i>
                                No hay registros en el historial.
                                </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>
</div>