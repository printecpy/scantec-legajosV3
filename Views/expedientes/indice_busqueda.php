<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-search-plus mr-3"></i> Búsqueda Avanzada
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Filtre archivos por tipo y contenido específico.
                </p>
            </div>
            
            <div>
                <a href="#" onclick="window.history.back(); return false;" 
                   class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm" 
                   title="Volver atrás">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h5 class="font-bold text-slate-800 flex items-center">
                    <i class="fas fa-filter mr-2 text-scantec-blue"></i> Criterios de Búsqueda
                </h5>
            </div>

            <div class="p-6">
                <form action="<?php echo base_url() ?>expedientes/busqueda" method="get" id="frmExpedientes" autocomplete="off" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        
                        <div class="md:col-span-4">
                            <label for="tipo_documento" class="block text-sm font-bold text-gray-700 mb-2">Tipo de Documento:</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="far fa-file-alt text-gray-400"></i>
                                </div>
                                <select id="tipo_documento" name="id_tipoDoc" 
                                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-700 transition-shadow">
                                    <option value="0">Todos los documentos</option>
                                    <?php foreach ($data['tipos_documentos'] as $tipos_documentos) { ?>
                                    <option value="<?php echo $tipos_documentos['id_tipoDoc']; ?>">
                                        <?php echo $tipos_documentos['nombre_tipoDoc']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-6">
                            <label for="termino" class="block text-sm font-bold text-gray-700 mb-2">Término de búsqueda (Cualquier índice):</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-keyboard text-gray-400"></i>
                                </div>
                                <input id="termino" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow" 
                                       type="text" name="termino" placeholder="Ej: Factura 001, Juan Perez, etc.">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" 
                                    class="w-full py-2.5 px-4 bg-scantec-blue text-white font-bold rounded-xl shadow-md hover:bg-blue-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                                <i class="fas fa-search mr-2"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h5 class="font-bold text-slate-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-info-circle mr-2 text-gray-400"></i> Referencia de Índices por Documento
                </h5>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Documento</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 1</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 2</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 3</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 4</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 5</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 6</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($data['tipos_documentos'] as $tipos_documentos) { ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 font-bold text-gray-800 text-sm">
                                <?php echo $tipos_documentos['nombre_tipoDoc']; ?>
                            </td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_1']; ?></td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_2']; ?></td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_3']; ?></td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_4']; ?></td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_5']; ?></td>
                            <td class="px-6 py-3 text-gray-600 text-xs"><?php echo $tipos_documentos['indice_6']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php pie() ?>

<?php
if (isset($_SESSION['alert'])) {
    $alertType = $_SESSION['alert']['type'];
    $alertMessage = $_SESSION['alert']['message'];
    
    // Configuración de SweetAlert
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$alertType',
                title: '$alertMessage',
                showConfirmButton: true,
                confirmButtonColor: '#1d4ed8', // Color azul Scantec aproximado
                timer: 5000,
                customClass: {
                    popup: 'rounded-2xl shadow-xl', // Estilo redondeado para SweetAlert también
                    title: 'font-sans text-lg'
                }
            });
        });
    </script>";
    unset($_SESSION['alert']); 
}
?>