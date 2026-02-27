<?php encabezado(); ?>

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

        <?php if ($_SESSION['id_rol'] == 1): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-scantec-blue">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-scantec-blue/5 rounded-xl">
                        <i class="fas fa-file-invoice text-scantec-blue fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-scantec-gray uppercase tracking-widest">Global</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['cant_expedient']['cant_expediente'], 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Expedientes totales</p>
                <!-- <a href="<?php echo base_url(); ?>expedientes/indice_busqueda"
                        class="text-xs font-bold text-scantec-red hover:underline uppercase tracking-tighter">
                        Ver listado <i class="fas fa-arrow-right ml-1"></i>
                    </a> -->
            </div>

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-scantec-red">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-scantec-red/5 rounded-xl">
                        <i class="fas fa-sync-alt text-scantec-red fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-scantec-gray uppercase tracking-widest">Activo</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['lote_proceso']['lote_procesos'], 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Lotes en proceso</p>
                <!-- <a href="<?php echo base_url(); ?>lotes"
                        class="text-xs font-bold text-scantec-blue hover:underline uppercase tracking-tighter">
                        Gestionar lotes <i class="fas fa-arrow-right ml-1"></i>
                    </a> -->
            </div>

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-scantec-blue">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-scantec-blue/5 rounded-xl">
                        <i class="fas fa-copy text-scantec-blue fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-scantec-gray uppercase tracking-widest">Producción</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php 
                        $total_paginas = $data['pagina_procesada'][0]['paginas_procesadas'] ?? 0;
                        echo number_format($total_paginas, 0, ',', '.'); 
                    ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Páginas procesadas</p>
                <!-- <a href="<?php echo base_url(); ?>procesos"
                        class="text-xs font-bold text-scantec-red hover:underline uppercase tracking-tighter">
                        Ver detalles <i class="fas fa-arrow-right ml-1"></i>
                    </a> -->
            </div>

            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 transition-all hover:shadow-md border-b-4 border-b-green-500">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 rounded-xl">
                        <i class="fas fa-users text-green-600 fa-2x"></i>
                    </div>
                    <span class="text-[10px] font-bold text-green-600 uppercase tracking-widest">Online</span>
                </div>
                <h3 class="text-3xl font-montserrat font-bold text-scantec-blue">
                    <?php echo number_format($data['usuarios_activos']['cant_usuarios'], 0, ',', '.'); ?>
                </h3>
                <p class="text-sm text-scantec-gray font-medium mb-4">Usuarios en línea</p>
                <div class="text-[10px] font-bold text-scantec-gray uppercase">Monitoreo activo</div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Archivos por
                        tipo de documento</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="table1">
                            <thead>
                                <tr class="bg-scantec-blue text-white">
                                    <th class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tl-lg">Tipo
                                        Documento</th>
                                    <th
                                        class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tr-lg text-center">
                                        Cant. Archivos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($data['archiv_tipoDoc'] as $row): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-xs font-medium text-scantec-black">
                                        <?php echo $row['nombre_tipoDoc']; ?></td>
                                    <td class="p-3 text-xs font-bold text-scantec-blue text-center">
                                        <?php echo number_format($row['cantidad_archivos'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50">
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Páginas por
                        tipo de documento</h5>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="table2">
                            <thead>
                                <tr class="bg-scantec-blue text-white">
                                    <th class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tl-lg">Tipo
                                        Documento</th>
                                    <th
                                        class="p-3 text-[10px] uppercase tracking-widest font-bold rounded-tr-lg text-center">
                                        Cant. Páginas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($data['archiv_tipoDoc'] as $row): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="p-3 text-xs font-medium text-scantec-black">
                                        <?php echo $row['nombre_tipoDoc']; ?></td>
                                    <td class="p-3 text-xs font-bold text-scantec-blue text-center">
                                        <?php echo number_format($row['total_paginas'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide mb-6">
                    Productividad: Últimos 15 días</h5>
                <div id="columnchart" class="w-full" style="height: 400px;"></div>
            </div>

            <?php if ($_SESSION['id_rol'] == 1): ?>
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h5 class="text-sm font-montserrat font-bold text-scantec-blue uppercase tracking-wide">Logs del
                        encaminador (Umango)</h5>
                    <span
                        class="px-3 py-1 bg-scantec-red text-white text-[10px] font-bold rounded-full uppercase">Auditoría
                        Real-Time</span>
                </div>
                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered w-full text-xs" id="table">
                            <thead class="bg-scantec-blue text-white">
                                <tr>
                                    <th class="p-2 text-center">NRO LOTE</th>
                                    <th class="p-2 text-center">PAGINAS</th>
                                    <th class="p-2">ARCHIVO DE ORIGEN</th>
                                    <th class="p-2 text-center">FECHA INICIO</th>
                                    <th class="p-2">USUARIO</th>
                                    <th class="p-2 text-center">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['logs_uman'] as $row): ?>
                                <tr>
                                    <td class="p-2 text-center font-bold"><?php echo $row['id_lote']; ?></td>
                                    <td class="p-2 text-center"><?php echo $row['paginas_exportadas']; ?></td>
                                    <td class="p-2 truncate max-w-[200px]"><?php echo $row['archivo_origen']; ?></td>
                                    <td class="p-2 text-center"><?php echo $row['fecha_inicio']; ?></td>
                                    <td class="p-2 uppercase font-medium"><?php echo $row['usuario']; ?></td>
                                    <td class="p-2 text-center">
                                        <span
                                            class="px-2 py-1 rounded bg-green-100 text-green-700 font-bold text-[9px]">
                                            <?php echo $row['estado']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php pie(); ?>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
    google.charts.load('current', {
        'packages': ['corechart']
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Fecha', 'Total Archivos', {
                role: 'style'
            }, {
                role: 'tooltip',
                p: {
                    html: true
                }
            }],
            <?php if (!empty($data['archiv_tipoDoc2'])): ?>
            <?php foreach ($data['archiv_tipoDoc2'] as $row): ?>[
                '<?= $row['fecha_indexado'] ?>',
                <?= $row['total_archivos'] ?>,
                '<?= ($row['fecha_indexado'] === 'Sin Fecha') ? "#878787" : "#182541"; ?>',
                '<div class="p-2 font-sans"><b>Fecha:</b> <?= $row['fecha_indexado'] ?><br><b>Total:</b> <?= $row['total_archivos'] ?></div>'
            ],
            <?php endforeach; ?>
            <?php endif; ?>
        ]);

        var options = {
            bar: {
                groupWidth: '60%'
            },
            legend: {
                position: 'none'
            },
            tooltip: {
                isHtml: true
            },
            hAxis: {
                textStyle: {
                    color: '#878787',
                    fontSize: 10
                }
            },
            vAxis: {
                gridlines: {
                    color: '#f3f4f6'
                },
                textStyle: {
                    color: '#878787'
                }
            },
            chartArea: {
                width: '90%',
                height: '75%'
            },
            backgroundColor: 'transparent',
            colors: ['#182541']
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('columnchart'));
        chart.draw(data, options);

        // Hacer el gráfico responsivo
        window.addEventListener('resize', () => chart.draw(data, options));
    }
    </script>
</div>