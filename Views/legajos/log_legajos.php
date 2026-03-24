<?php encabezado() ?>
<?php $logsLegajos = $data['logs_legajos'] ?? []; ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        Bitácora de Legajos
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Auditoría detallada del ciclo de vida del legajo y sus documentos.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="#" onclick="window.history.back(); return false;"
                        class="ml-1 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-bold rounded-lg transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">ID Legajo</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">CI</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Titular</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Tipo</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap text-center">Origen</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Acción</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Documento / Detalle</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap text-center">Estado</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Usuario</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Host</th>
                                <th class="px-4 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($logsLegajos)): ?>
                                <?php foreach ($logsLegajos as $log): ?>
                                    <?php
                                    $origen = strtoupper(trim((string)($log['origen'] ?? '')));
                                    $estado = strtoupper(trim((string)($log['estado_evento'] ?? '')));
                                    $badgeOrigen = $origen === 'DOCUMENTO'
                                        ? 'bg-blue-50 text-blue-700 border-blue-200'
                                        : 'bg-slate-100 text-slate-700 border-slate-200';
                                    $badgeEstado = 'bg-gray-100 text-gray-700 border-gray-200';
                                    if ($estado === 'VERIFICADO') {
                                        $badgeEstado = 'bg-cyan-50 text-cyan-700 border-cyan-200';
                                    } elseif ($estado === 'FINALIZADO' || $estado === 'COMPLETADO') {
                                        $badgeEstado = 'bg-green-50 text-green-700 border-green-200';
                                    } elseif ($estado === 'CERRADO') {
                                        $badgeEstado = 'bg-stone-100 text-stone-700 border-stone-200';
                                    } elseif ($estado === 'BORRADOR' || $estado === 'INCOMPLETO') {
                                        $badgeEstado = 'bg-amber-50 text-amber-700 border-amber-200';
                                    } elseif ($estado === 'ACTIVO' || $estado === 'VENCIDO') {
                                        $badgeEstado = 'bg-red-50 text-red-700 border-red-200';
                                    }
                                    $detalle = trim((string)($log['detalle'] ?? ''));
                                    $documento = trim((string)($log['documento'] ?? ''));
                                    $descripcion = $documento !== '' ? $documento : $detalle;
                                    if ($documento !== '' && $detalle !== '') {
                                        $descripcion .= ' | ' . $detalle;
                                    }
                                    ?>
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-4 py-3 text-xs font-bold text-gray-800 whitespace-nowrap">
                                            <?php echo intval($log['id_legajo'] ?? 0); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['ci_socio'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['nombre_completo'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['nombre_tipo_legajo'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs whitespace-nowrap text-center">
                                            <span class="px-2.5 py-1 rounded-md border <?php echo $badgeOrigen; ?> font-bold text-[10px] tracking-wide">
                                                <?php echo htmlspecialchars($origen !== '' ? $origen : 'LEGAJO'); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs font-semibold text-gray-700 whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['accion'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 max-w-md">
                                            <div class="font-medium text-gray-700"><?php echo htmlspecialchars($descripcion); ?></div>
                                            <?php if (!empty($log['nro_solicitud'])): ?>
                                                <div class="text-[11px] text-gray-400 mt-1">Solicitud: <?php echo htmlspecialchars($log['nro_solicitud']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs whitespace-nowrap text-center">
                                            <span class="px-2.5 py-1 rounded-md border <?php echo $badgeEstado; ?> font-bold text-[10px] tracking-wide">
                                                <?php echo htmlspecialchars($estado !== '' ? $estado : 'SIN ESTADO'); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['usuario_evento'] ?? 'Sistema'); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                            <i class="far fa-calendar-alt mr-1 opacity-50"></i><?php echo htmlspecialchars($log['fecha_evento'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                            <i class="fas fa-desktop mr-1 opacity-50"></i><?php echo htmlspecialchars($log['nombre_host'] ?? ''); ?>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500 font-mono whitespace-nowrap">
                                            <?php echo htmlspecialchars($log['ip_host'] ?? ''); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        <i class="fas fa-clipboard-list text-4xl mb-3 block opacity-30"></i>
                                        No hay registros en la bitácora de legajos actualmente.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>
</div>
