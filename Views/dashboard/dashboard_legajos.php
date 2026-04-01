<?php encabezado(); ?>

<?php
$periodoProductividad = (string)($data['periodo_productividad'] ?? '1w');
$productividadSolicitudes = $data['productividad_solicitudes'] ?? [];
$labelsProductividad = [];
$datasetProceso = [];
$datasetCompletado = [];
$datasetVerificado = [];
foreach ($productividadSolicitudes as $filaProductividad) {
    $labelsProductividad[] = $filaProductividad['nombre_usuario'] ?? 'Sin usuario';
    $datasetProceso[] = intval($filaProductividad['cantidad_proceso'] ?? 0);
    $datasetCompletado[] = intval($filaProductividad['cantidad_completado'] ?? 0);
    $datasetVerificado[] = intval($filaProductividad['cantidad_verificado'] ?? 0);
}

// Datos para el gráfico de legajos armados por fecha y usuario
$legajosArmados = $data['legajos_armados'] ?? [];
$dashboardCards = $data['dashboard_cards'] ?? [];
$mostrarCardProceso = !empty($dashboardCards['dashboard_card_legajos_proceso']);
$mostrarCardCompletados = !empty($dashboardCards['dashboard_card_legajos_completados']);
$mostrarCardRechazados = !empty($dashboardCards['dashboard_card_legajos_rechazados']);
$mostrarCardVerificados = !empty($dashboardCards['dashboard_card_legajos_verificados']);
$mostrarCardCerrados = !empty($dashboardCards['dashboard_card_legajos_cerrados']);
$mostrarCardDocsVigentes = !empty($dashboardCards['dashboard_card_docs_vigentes']);
$mostrarCardDocsPorVencer = !empty($dashboardCards['dashboard_card_docs_por_vencer']);
$mostrarCardDocsVencidos = !empty($dashboardCards['dashboard_card_docs_vencidos']);
$mostrarLegajosPorTipo = !empty($dashboardCards['dashboard_card_legajos_por_tipo']);
$mostrarLegajosPorUsuario = !empty($dashboardCards['dashboard_card_legajos_por_usuario']);
$mostrarGraficoProductividad = !empty($dashboardCards['dashboard_card_grafico_productividad']);
$dashboardSoloPropios = !empty($data['dashboard_scope_solo_propios']);
$totalLegajosUsuario = intval($data['cant_legajos']['cant_legajos'] ?? 0);
?>

<div id="layoutSidenav_content" class="bg-gray-50">

    <?php if (isset($_GET['no_s'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'info',
            title: 'Sin datos',
            text: 'No hay registros disponibles para la consulta realizada.',
            confirmButtonColor: '#182541'
        });
    });
    </script>
    <?php endif; ?>

    <main class="p-6">

        <?php if ($dashboardSoloPropios): ?>
        <div id="dashboard-aviso-solo-propios" class="hidden mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-800">
            Este dashboard está mostrando solamente legajos creados por tu propio usuario dentro de los tipos permitidos para tu rol.
        </div>
        <?php endif; ?>

        <?php if ($dashboardSoloPropios && $totalLegajosUsuario === 0): ?>
        <div id="dashboard-aviso-sin-legajos" class="hidden mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800">
            Todavía no tenés legajos creados con tu usuario. Las tarjetas se muestran en cero hasta que generes tus propios registros.
        </div>
        <?php endif; ?>

        <?php if ($mostrarCardProceso || $mostrarCardCompletados || $mostrarCardRechazados || $mostrarCardVerificados || $mostrarCardCerrados): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6 mb-8">
            <?php if ($mostrarCardProceso): ?>
            <a href="<?php echo base_url(); ?>legajos/buscar_legajos?estado_legajo=Incompleto"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-indigo-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-indigo-50 rounded-xl">
                        <i class="fas fa-copy text-indigo-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest">Proceso</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-indigo-700">
                    <?php echo number_format($data['cant_legajos_proceso']['cant_legajos_proceso'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-2">Legajos en proceso</p>
                <span
                    class="inline-block text-[10px] font-bold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full uppercase tracking-wide">Ver
                    legajos</span>
            </a>
            <?php endif; ?>

            <?php if ($mostrarCardCompletados): ?>
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-scantec-blue">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-scantec-blue/5 rounded-xl">
                        <i class="fas fa-folder-open text-scantec-blue fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-scantec-blue uppercase tracking-widest">Completado</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['cant_legajos_completados']['cant_legajos_completados'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Legajos completados</p>
            </div>
            <?php endif; ?>

            <?php if ($mostrarCardRechazados): ?>
            <a href="<?php echo base_url(); ?>legajos/buscar_legajos?estado_legajo=Verificaci%C3%B3n%20rechazada"
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-rose-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-rose-50 rounded-xl">
                        <i class="fas fa-circle-xmark text-rose-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-widest">Rechazado</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-rose-700">
                    <?php echo number_format($data['cant_legajos_rechazados']['cant_legajos_rechazados'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-2">Legajos rechazados</p>
                <span
                    class="inline-block text-[10px] font-bold bg-rose-100 text-rose-700 px-3 py-1 rounded-full uppercase tracking-wide">Ver
                    legajos</span>
            </a>
            <?php endif; ?>

            <?php if ($mostrarCardVerificados): ?>
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-sky-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-sky-50 rounded-xl">
                        <i class="fas fa-check-double text-sky-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-sky-600 uppercase tracking-widest">Verificado</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-sky-700">
                    <?php echo number_format($data['cant_legajos_verificados']['cant_legajos_verificados'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Legajos verificados</p>
            </div>
            <?php endif; ?>

            <?php if ($mostrarCardCerrados): ?>
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-stone-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-stone-50 rounded-xl">
                        <i class="fas fa-box-archive text-stone-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-stone-600 uppercase tracking-widest">Cerrado</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-stone-700">
                    <?php echo number_format($data['cant_legajos_cerrados']['cant_legajos_cerrados'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Legajos cerrados</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($mostrarCardDocsVigentes || $mostrarCardDocsPorVencer || $mostrarCardDocsVencidos): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <?php if ($mostrarCardDocsVigentes): ?>
            <a href="<?php echo base_url(); ?>expedientes/busqueda?id_tipoDoc=0&termino="
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-green-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 rounded-xl">
                        <i class="fas fa-circle-check text-green-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-green-600 uppercase tracking-widest">Vigentes</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['docs_vigentes']['cant_documentos_vigentes'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-2">Documentos vigentes</p>
            </a>
            <?php endif; ?>

            <?php if ($mostrarCardDocsPorVencer): ?>
            <a href="<?php echo base_url(); ?>legajos/buscar_legajos?filtro_documentos=por_vencer"
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-yellow-400">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-yellow-50 rounded-xl">
                        <i class="fas fa-clock text-yellow-500 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-yellow-600 uppercase tracking-widest">Por vencer</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['docs_por_vencer']['cant_documentos_por_vencer'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-2">Próximos a vencer</p>
                <span
                    class="inline-block text-[10px] font-bold bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full uppercase tracking-wide">Ver
                    legajos</span>
            </a>
            <?php endif; ?>

            <?php if ($mostrarCardDocsVencidos): ?>
            <a href="<?php echo base_url(); ?>legajos/buscar_legajos?filtro_documentos=vencidos"
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-red-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-red-50 rounded-xl">
                        <i class="fas fa-circle-exclamation text-red-500 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Crítico</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-red-500">
                    <?php echo number_format($data['docs_vencidos']['cant_documentos_criticos'] ?? 0, 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-2">Vencidos / faltantes</p>
                <span
                    class="inline-block text-[10px] font-bold bg-red-100 text-red-700 px-3 py-1 rounded-full uppercase tracking-wide">Ver
                    legajos</span>
            </a>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php if ($mostrarLegajosPorTipo || $mostrarLegajosPorUsuario): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            <?php if ($mostrarLegajosPorTipo): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Legajos completados por
                        tipo</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="table1">
                            <thead>
                                <tr class="bg-scantec-blue text-white">
                                    <th class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tl-lg">Tipo
                                        de legajo</th>
                                    <th
                                        class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tr-lg text-center">
                                        Cant. Completados</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach (($data['legajos_por_tipo'] ?? []) as $row): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-xs font-medium text-scantec-black">
                                        <?php echo htmlspecialchars($row['nombre_tipo_legajo'] ?? 'Sin tipo'); ?></td>
                                    <td class="p-3 text-xs font-bold text-scantec-blue text-center">
                                        <?php echo number_format($row['cantidad_legajos'] ?? 0, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($mostrarLegajosPorUsuario): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Legajos verificados por
                        usuario</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="table2">
                            <thead>
                                <tr class="bg-scantec-blue text-white">
                                    <th class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tl-lg">Usuario</th>
                                    <th
                                        class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tr-lg text-center">
                                        Cant. Verificados</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach (($data['legajos_por_usuario'] ?? []) as $row): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-xs font-medium text-scantec-black">
                                        <?php echo htmlspecialchars($row['nombre_usuario'] ?? 'Sin usuario'); ?></td>
                                    <td class="p-3 text-xs font-bold text-scantec-blue text-center">
                                        <?php echo number_format($row['cantidad_legajos'] ?? 0, 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php endif; ?>

        <?php if ($mostrarGraficoProductividad): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Productividad de solicitudes completadas por usuario</h5>
                    <p class="text-xs text-gray-500 mt-1">Solo incluye legajos cuyo tipo requiere número de solicitud.</p>
                </div>
                <form action="<?php echo base_url(); ?>dashboard/dashboard_legajos" method="get" class="flex items-end gap-3">
                    <div>
                        <label for="periodo_productividad" class="block text-xs font-bold text-gray-500 uppercase mb-1">Período</label>
                        <select id="periodo_productividad" name="periodo_productividad"
                            class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-scantec-blue">
                            <option value="1d" <?php echo $periodoProductividad === '1d' ? 'selected' : ''; ?>>Último día</option>
                            <option value="1w" <?php echo $periodoProductividad === '1w' ? 'selected' : ''; ?>>Última semana</option>
                            <option value="4w" <?php echo $periodoProductividad === '4w' ? 'selected' : ''; ?>>Últimas 4 semanas</option>
                            <option value="8w" <?php echo $periodoProductividad === '8w' ? 'selected' : ''; ?>>Últimas 8 semanas</option>
                            <option value="12w" <?php echo $periodoProductividad === '12w' ? 'selected' : ''; ?>>Últimas 12 semanas</option>
                            <option value="24w" <?php echo $periodoProductividad === '24w' ? 'selected' : ''; ?>>Últimas 24 semanas</option>
                        </select>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-scantec-blue text-white text-sm font-bold hover:bg-blue-800 transition-all">
                        Aplicar
                    </button>
                </form>
            </div>
            <div class="p-5">
                <?php
                $legajosArmados = $data['legajos_armados'] ?? [];
                $periodoDias = 1;
                switch ($periodoProductividad) {
                    case '1d': $periodoDias = 1; break;
                    case '1w': $periodoDias = 7; break;
                    case '4w': $periodoDias = 28; break;
                    case '8w': $periodoDias = 56; break;
                    case '12w': $periodoDias = 84; break;
                    case '24w': $periodoDias = 168; break;
                    default: $periodoDias = 7; break;
                }

                $hoy = new DateTimeImmutable('today');
                $fechaInicio = $hoy->sub(new DateInterval('P' . ($periodoDias - 1) . 'D'));
                $fechas = [];
                for ($i = 0; $i < $periodoDias; $i++) {
                    $fechas[] = $fechaInicio->add(new DateInterval('P' . $i . 'D'))->format('Y-m-d');
                }

                $usuarios = [];
                $serie = [];
                foreach ($legajosArmados as $row) {
                    $fecha = $row['fecha_indexado'] ?? null;
                    $usuario = trim($row['nombre_usuario'] ?? 'Sin usuario');
                    $cantidad = intval($row['cantidad_legajos'] ?? 0);
                    if ($fecha === null || $fecha === '') continue;
                    if (!in_array($usuario, $usuarios, true)) {
                        $usuarios[] = $usuario;
                    }
                    if (!isset($serie[$usuario])) {
                        $serie[$usuario] = array_fill(0, count($fechas), 0);
                    }
                    $index = array_search($fecha, $fechas, true);
                    if ($index !== false) {
                        $serie[$usuario][$index] = $cantidad;
                    }
                }

                $maxCantidad = 0;
                foreach ($serie as $dataUsuario) {
                    $maxCantidad = max($maxCantidad, max($dataUsuario));
                }
                $maxCantidad = max($maxCantidad, 1);

                $colores = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];

                if (!empty($serie)):
                    $chartWidth = 910;
                    $chartHeight = 360;
                    $paddingLeft = 60;
                    $paddingBottom = 45;
                    $paddingTop = 20;
                    $paddingRight = 20;
                    $plotWidth = $chartWidth - $paddingLeft - $paddingRight;
                    $plotHeight = $chartHeight - $paddingTop - $paddingBottom;
                    $xStep = $periodoDias > 1 ? $plotWidth / ($periodoDias - 1) : 0;
                ?>

                <div class="mb-3 font-semibold text-sm text-gray-600">Periodo: <?php echo htmlspecialchars($periodoProductividad); ?> (<?php echo count($fechas); ?> días)</div>
                <div class="overflow-auto">
                    <svg width="100%" viewBox="0 0 <?php echo $chartWidth; ?> <?php echo $chartHeight; ?>" role="img" aria-label="Gráfico de legajos armados">
                        <rect x="0" y="0" width="<?php echo $chartWidth; ?>" height="<?php echo $chartHeight; ?>" fill="#ffffff" />
                        <g>
                            <?php for ($i = 0; $i < $periodoDias; $i++):
                                $x = $paddingLeft + ($xStep * $i);
                            ?>
                            <line x1="<?php echo $x; ?>" y1="<?php echo $paddingTop; ?>" x2="<?php echo $x; ?>" y2="<?php echo $chartHeight - $paddingBottom; ?>" stroke="#e2e8f0" stroke-width="1" />
                            <text x="<?php echo $x; ?>" y="<?php echo $chartHeight - 18; ?>" font-size="10" fill="#475569" text-anchor="middle"><?php echo date('d/m', strtotime($fechas[$i])); ?></text>
                            <?php endfor; ?>

                            <?php for ($i = 0; $i <= 5; $i++):
                                $yValue = round($maxCantidad * (5 - $i) / 5);
                                $y = $paddingTop + $plotHeight * (5 - $i) / 5;
                            ?>
                            <line x1="<?php echo $paddingLeft; ?>" y1="<?php echo $y; ?>" x2="<?php echo $chartWidth - $paddingRight; ?>" y2="<?php echo $y; ?>" stroke="#e2e8f0" stroke-dasharray="2,2" stroke-width="1" />
                            <text x="<?php echo $paddingLeft - 8; ?>" y="<?php echo $y + 4; ?>" font-size="10" fill="#475569" text-anchor="end"><?php echo $yValue; ?></text>
                            <?php endfor; ?>

                            <?php $colorIndex = 0; foreach ($serie as $usuario => $valores):
                                $path = '';
                                foreach ($valores as $index => $valor) {
                                    $x = $paddingLeft + ($xStep * $index);
                                    $y = $paddingTop + $plotHeight * (1 - ($maxCantidad ? $valor / $maxCantidad : 0));
                                    $path .= ($index === 0 ? 'M' : 'L') . $x . ' ' . $y . ' ';
                                }
                                $color = $colores[$colorIndex % count($colores)];
                                $colorIndex++;
                            ?>
                                <path d="<?php echo $path; ?>" fill="none" stroke="<?php echo $color; ?>" stroke-width="2" />
                                <?php foreach ($valores as $index => $valor):
                                    $x = $paddingLeft + ($xStep * $index);
                                    $y = $paddingTop + $plotHeight * (1 - ($maxCantidad ? $valor / $maxCantidad : 0));
                                ?>
                                <circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="3" fill="<?php echo $color; ?>" />
                                <?php if ($valor > 0): ?>
                                <text x="<?php echo $x; ?>" y="<?php echo $y - 10; ?>" font-size="10" fill="<?php echo $color; ?>" text-anchor="middle"><?php echo $valor; ?></text>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>

                        </g>
                    </svg>
                </div>

                <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-2">
                    <?php $colorIndex = 0; foreach ($serie as $usuario => $valores): $color = $colores[$colorIndex % count($colores)]; $colorIndex++; ?>
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-gray-700">
                        <span class="w-3 h-3 rounded-full" style="background-color: <?php echo $color; ?>"></span>
                        <?php echo htmlspecialchars($usuario); ?>
                    </span>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>
                <div class="px-4 py-10 text-center text-sm text-gray-500">
                    No hay datos de legajos armados para mostrar en el período seleccionado.
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div id="dashboard-avisos-inferiores" class="space-y-4 mt-8"></div>
<?php pie(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contenedorAvisos = document.getElementById('dashboard-avisos-inferiores');
    if (!contenedorAvisos) {
        return;
    }

    ['dashboard-aviso-solo-propios', 'dashboard-aviso-sin-legajos'].forEach(function (idAviso) {
        const avisoOriginal = document.getElementById(idAviso);
        if (!avisoOriginal) {
            return;
        }

        avisoOriginal.classList.remove('hidden');
        avisoOriginal.classList.add('relative', 'pr-12');

        const botonCerrar = document.createElement('button');
        botonCerrar.type = 'button';
        botonCerrar.className = 'absolute right-4 top-4 transition-colors';
        botonCerrar.setAttribute('aria-label', 'Cerrar aviso');
        botonCerrar.innerHTML = '<i class="fas fa-times"></i>';

        if (idAviso === 'dashboard-aviso-solo-propios') {
            botonCerrar.classList.add('text-yellow-700', 'hover:text-yellow-900');
        } else {
            botonCerrar.classList.add('text-blue-700', 'hover:text-blue-900');
        }

        botonCerrar.addEventListener('click', function () {
            avisoOriginal.remove();
        });

        avisoOriginal.appendChild(botonCerrar);
        contenedorAvisos.appendChild(avisoOriginal);
    });
});
</script>

</div>

