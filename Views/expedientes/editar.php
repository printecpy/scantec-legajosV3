<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-edit mr-3"></i> Modificar Expediente
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Actualización de metadatos del documento registrado.
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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl mx-auto">
            
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h5 class="font-bold text-slate-800 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-scantec-blue"></i> Información del Documento
                </h5>
            </div>

            <div class="p-8">
                <form action="<?php echo base_url() ?>expedientes/modificar" method="post" id="frmExpedientes" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id_expediente" value="<?php echo $data['expediente']['id_expediente']; ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="md:col-span-2">
                            <label for="id_proceso" class="block text-xs font-bold text-gray-500 uppercase mb-2">ID Proceso (Sistema)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-fingerprint text-gray-400"></i>
                                </div>
                                <input id="id_proceso" type="text" name="id_proceso" 
                                       value="<?php echo $data['expediente']['id_proceso'] ?>" 
                                       class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-200 bg-gray-100 text-gray-600 focus:outline-none cursor-not-allowed font-mono" 
                                       readonly>
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-gray-100 my-2"></div>

                        <div>
                            <label for="indice_01" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 1</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_01" type="text" name="indice_01" value="<?php echo $data['expediente']['indice_01'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="indice_02" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 2</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_02" type="text" name="indice_02" value="<?php echo $data['expediente']['indice_02'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="indice_03" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 3</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_03" type="text" name="indice_03" value="<?php echo $data['expediente']['indice_03'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="indice_04" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 4</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_04" type="text" name="indice_04" value="<?php echo $data['expediente']['indice_04'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="indice_05" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 5</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_05" type="text" name="indice_05" value="<?php echo $data['expediente']['indice_05'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="indice_06" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 6</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_06" type="text" name="indice_06" value="<?php echo $data['expediente']['indice_06'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-gray-100 my-2"></div>

                        <div class="md:col-span-2">
                            <label for="ubicacion" class="block text-xs font-bold text-gray-500 uppercase mb-2">Ubicación Física</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-map-marker-alt text-gray-400"></i></div>
                                <input id="ubicacion" type="text" name="ubicacion" value="<?php echo $data['expediente']['ubicacion'] ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:col-span-2">
                            
                            <div>
                                <label for="firma_digital" class="block text-xs font-bold text-gray-500 uppercase mb-2">Firma Digital</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-signature text-gray-400"></i></div>
                                    <input id="firma_digital" type="text" name="firma_digital" value="<?php echo $data['expediente']['firma_digital']; ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="version" class="block text-xs font-bold text-gray-500 uppercase mb-2">Versión</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-code-branch text-gray-400"></i></div>
                                    <input id="version" type="text" name="version" value="<?php echo $data['expediente']['version']; ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                                </div>
                            </div>

                            <div>
                                <label for="paginas" class="block text-xs font-bold text-gray-500 uppercase mb-2">Páginas</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-copy text-gray-400"></i></div>
                                    <input id="paginas" type="number" name="paginas" value="<?php echo $data['expediente']['paginas']; ?>" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 flex flex-col md:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-scantec-blue text-white font-bold hover:bg-gray-800 shadow-md hover:shadow-lg transition-all flex items-center justify-center">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<?php pie() ?>
<?php
if (isset($_SESSION['alert'])) {
    $alertType = $_SESSION['alert']['type'];
    $alertMessage = $_SESSION['alert']['message'];

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$alertType',
                title: '$alertMessage',
                showConfirmButton: true,
                confirmButtonColor: '#1d4ed8',
                timer: 5000,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    title: 'font-sans text-lg'
                }
            });
        });
    </script>";
    unset($_SESSION['alert']);
}
?>