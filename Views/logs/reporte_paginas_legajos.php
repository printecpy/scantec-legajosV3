<?php encabezado() ?>
<?php
$filtros = $data['filtros'] ?? ['desde' => date('Y-m-01'), 'hasta' => date('Y-m-t')];
$totales = $data['totales'] ?? [];
$resumenPeriodos = $data['resumen_periodos'] ?? [];
$detalleLegajos = $data['detalle_legajos'] ?? [];
$queryExport = http_build_query([
    'desde' => $filtros['desde'] ?? '',
    'hasta' => $filtros['hasta'] ?? '',
]);
?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-blue-500">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        Páginas procesadas de legajos
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Cuenta las páginas del PDF final de cada legajo sin incluir la carátula y las agrupa por período de creación.</p>
                </div>

                <div class="flex items-center gap-2">
                    <a target="_blank" href="<?php echo base_url(); ?>logs/reporte_paginas_legajosPdf?<?php echo htmlspecialchars($queryExport, ENT_QUOTES, 'UTF-8'); ?>" title="Exportar a PDF"
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-red-500 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors shadow-sm">
                        <i class="fas fa-file-pdf text-lg"></i>
                    </a>

                    <a target="_blank" href="<?php echo base_url(); ?>logs/reporte_paginas_legajosExcel?<?php echo htmlspecialchars($queryExport, ENT_QUOTES, 'UTF-8'); ?>" title="Exportar a Excel"
                       class="w-9 h-9 flex items-center justify-center bg-white border border-gray-200 text-green-500 rounded-lg hover:bg-green-50 hover:text-green-600 hover:border-green-200 transition-colors shadow-sm">
                        <i class="fas fa-file-excel text-lg"></i>
                    </a>
                </div>
            </div>

            <form method="get" action="<?php echo base_url(); ?>logs/reporte_paginas_legajos" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Desde</label>
                        <input type="date" name="desde" value="<?php echo htmlspecialchars((string)($filtros['desde'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Hasta</label>
                        <input type="date" name="hasta" value="<?php echo htmlspecialchars((string)($filtros['hasta'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900 transition-colors">
                            Filtrar
                        </button>
                        <a href="<?php echo base_url(); ?>logs/reporte_paginas_legajos"
                           class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors">
                            Reiniciar
                        </a>
                    </div>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Legajos contabilizados</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800"><?php echo intval($totales['total_legajos'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Páginas procesadas</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800"><?php echo intval($totales['total_paginas'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Promedio por legajo</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800"><?php echo number_format((float)($totales['promedio_paginas'] ?? 0), 2, ',', '.'); ?></p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Resumen por período</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Período</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Legajos</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Páginas procesadas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($resumenPeriodos)): ?>
                                <?php foreach ($resumenPeriodos as $periodo): ?>
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars((string)($periodo['periodo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo intval($periodo['total_legajos'] ?? 0); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo intval($periodo['total_paginas'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        No hay legajos con páginas procesadas en el período seleccionado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Detalle de legajos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Tipo</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">CI</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Nombre</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Solicitud</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Páginas</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Estado</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider whitespace-nowrap">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (!empty($detalleLegajos)): ?>
                                <?php foreach ($detalleLegajos as $legajo): ?>
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="px-6 py-4 text-xs font-semibold text-gray-800 whitespace-nowrap"><?php echo intval($legajo['id_legajo'] ?? 0); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars(substr((string)($legajo['fecha_creacion'] ?? ''), 0, 16), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['nombre_tipo_legajo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['ci_socio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-700 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['nombre_completo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['nro_solicitud'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs font-semibold text-blue-700 whitespace-nowrap"><?php echo intval($legajo['cantidad_paginas_procesadas'] ?? 0); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-6 py-4 text-xs text-gray-600 whitespace-nowrap"><?php echo htmlspecialchars((string)($legajo['usuario_responsable'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium">
                                        No se encontraron legajos con páginas procesadas para el filtro indicado.
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
