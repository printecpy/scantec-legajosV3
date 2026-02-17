<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-search mr-3"></i> Resultados de Búsqueda
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Expedientes encontrados para el término: <strong><?php echo $data['termino']; ?></strong>
                </p>
            </div>

            <div class="flex flex-wrap gap-2 justify-end">
                <a href="<?php echo base_url(); ?>expedientes/pdf" target="_blank" 
                   class="group w-10 h-10 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 hover:shadow-md flex items-center justify-center transition-all" 
                   title="Exportar a PDF">
                    <i class="fas fa-file-pdf"></i>
                </a>
                
                <a href="<?php echo base_url(); ?>expedientes/excel" target="_blank" 
                   class="group w-10 h-10 rounded-xl bg-white border border-green-200 text-green-600 hover:bg-green-50 hover:shadow-md flex items-center justify-center transition-all" 
                   title="Exportar a Excel">
                    <i class="fas fa-file-excel"></i>
                </a>

                <a href="<?php echo base_url(); ?>expedientes/pdf_email" target="_blank" 
                   class="group w-10 h-10 rounded-xl bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 hover:shadow-md flex items-center justify-center transition-all" 
                   title="Enviar por Correo">
                    <i class="fas fa-envelope"></i>
                </a>

                <a href="<?php echo base_url(); ?>expedientes/reporte" 
                   class="group w-10 h-10 rounded-xl bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-50 hover:shadow-md flex items-center justify-center transition-all" 
                   title="Ver Gráficos/Reporte">
                    <i class="fas fa-chart-bar"></i>
                </a>

                <a href="#" onclick="window.history.back(); return false;" 
                   class="group px-4 h-10 rounded-xl bg-scantec-blue text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm" 
                   title="Volver atrás">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h5 class="font-bold text-slate-800">Listado de Archivos</h5>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold border border-blue-200">
                    Coincidencias: <?php echo count($data['busqueda']); ?>
                </span>
            </div>

            <div class="table-container">
                <table class="scantec-table" id="table">
                    <thead>
                        <tr>
                            <th>Tipo Documento</th>
                            <th>Índice 1</th>
                            <th>Índice 2</th>
                            <th>Índice 3</th>
                            <th>Índice 4</th>
                            <th>Índice 5</th>
                            <th>Índice 6</th>
                            <th class="text-center">Reg.</th>
                            <th class="text-right">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $termino = $data['termino'];
                        foreach ($data['busqueda'] as $busqueda) { 
                        ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="font-bold text-gray-800">
                                <div class="flex items-center">
                                    <i class="far fa-file-alt mr-2 text-gray-400"></i>
                                    <?php echo $busqueda['nombre_tipoDoc']; ?>
                                </div>
                            </td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_01']; ?></td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_02']; ?></td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_03']; ?></td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_04']; ?></td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_05']; ?></td>
                            <td class="text-gray-600 text-xs"><?php echo $busqueda['indice_06']; ?></td>
                            
                            <td class="text-center">
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-bold border border-gray-200">
                                    <?php echo $busqueda['cant_documentos']; ?>
                                </span>
                            </td>

                            <td class="text-right">
                                <a href="<?php echo base_url(); ?>expedientes/mostrar_registros?indice_01=<?php echo $busqueda['indice_01']; ?>&nombre_tipoDoc=<?php echo $busqueda['nombre_tipoDoc']; ?>&termino=<?php echo $termino; ?>" 
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-50 text-yellow-600 border border-yellow-200 hover:bg-yellow-100 hover:shadow-sm transition-all"
                                   title="Abrir Carpeta">
                                    <i class="fas fa-folder-open text-xs"></i>
                                </a>
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