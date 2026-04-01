<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-folder-open mr-3"></i> Gestión de Archivos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Visualización y control de expedientes digitalizados.
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
                    title="Ver Reporte Gráfico">
                    <i class="fas fa-chart-bar"></i>
                </a>

                <div class="w-px h-10 bg-gray-300 mx-2"></div>

                <a href="#" onclick="window.history.back(); return false;"
                    class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                    title="Volver atrás">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h5 class="font-bold text-slate-800 flex items-center">
                    <i class="fas fa-list mr-2 text-scantec-blue"></i> Listado de Documentos
                </h5>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold border border-blue-200">
                    Total: <?php echo count($data['mostrar_registros']); ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="table">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo
                                Documento</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 1</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 2</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 3</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 4</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 5</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Índice 6</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                Págs.</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                Ubicación</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                Fecha</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                Ver.</th>
                            <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                Acciones</th>
                            <?php if ($_SESSION['id_rol'] == 1) { ?>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                    Admin</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($data['mostrar_registros'] as $mostrar_registros) { ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 font-bold text-gray-800 text-sm">
                                    <?php echo $mostrar_registros['nombre_tipoDoc']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_01']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_02']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_03']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_04']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_05']; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs"><?php echo $mostrar_registros['indice_06']; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-bold border border-gray-200">
                                        <?php echo $mostrar_registros['paginas']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    <?php echo $mostrar_registros['ubicacion']; ?>
                                </td>
                                <td class="px-4 py-3 text-center text-xs text-gray-500">
                                    <?php echo $mostrar_registros['fecha_indexado']; ?>
                                </td>
                                <td class="px-4 py-3 text-center text-xs font-mono text-gray-500">
                                    <?php echo $mostrar_registros['version']; ?>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <?php
                                    $fecha_carga = new DateTime($mostrar_registros['fecha_indexado']);
                                    $fecha_licencia = new DateTime(LICENCIA_EXPIRA);
                                    $licencia_expirada = $fecha_carga > $fecha_licencia;

                                    if ($licencia_expirada): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-red-50 text-red-700 text-xs font-medium border border-red-100">
                                            <i class="fas fa-lock mr-1"></i> Expirado
                                        </span>
                                    <?php else: ?>
                                        <div class="px-4 py-3 text-center">
                                            <button
                                                onclick="mostrarPDFModal('<?php echo base_url(); ?>expedientes/expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>')"
                                                title="Ver con marca de agua"
                                                class="text-[#182541] hover:text-[#dc153d] transition-colors mr-3 p-1">
                                                <i class="fas fa-eye fa-lg"></i>
                                            </button>

                                            <button
                                                onclick="mostrarPDFModal('<?php echo base_url(); ?>expedientes/ver_expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>')"
                                                title="Ver Original"
                                                class="text-[#878787] hover:text-[#182541] transition-colors mr-3 p-1">
                                                <i class="fas fa-file-alt fa-lg"></i>
                                            </button>

                                            <a href="<?php echo base_url() . 'expedientes/ver_expediente?ruta=' . urlencode($mostrar_registros['ruta_original']) . '&id_expediente=' . $mostrar_registros['id_expediente'] . '&return_url=' . urlencode($_SERVER['REQUEST_URI']); ?>"
                                                target="_blank"
                                                title="Abrir en Nueva Pestaña"
                                                class="text-[#dc153d] hover:text-[#182541] transition-colors mr-3 p-1">
                                                <i class="fas fa-external-link-alt fa-lg"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <?php if ($_SESSION['id_rol'] == 1) { ?>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="<?php echo base_url(); ?>expedientes/editar?id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>"
                                                class="p-1.5 rounded-lg text-yellow-600 hover:bg-yellow-50 hover:text-yellow-700 transition-colors"
                                                title="Modificar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" action="<?php echo base_url(); ?>expedientes/eliminar"
                                                class="d-inline eliminar">
                                                <input type="hidden" name="token"
                                                    value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="id_expediente"
                                                    value="<?php echo $mostrar_registros['id_expediente']; ?>">
                                                <button
                                                    class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors"
                                                    title="Anular" type="submit">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                            <button
                                                class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-700 transition-colors"
                                                onclick="cargarMetadatos('<?php echo $mostrar_registros['ruta_original']; ?>')"
                                                title="Info Metadatos">
                                                <i class="fas fa-info-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div id="pdf_preview_container" style="display:none;"
                class="border-t border-gray-200 bg-gray-100 p-4 relative">
                <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden relative">
                    <div class="bg-gray-800 text-white px-4 py-2 flex justify-between items-center">
                        <span class="font-bold text-sm"><i class="fas fa-file-pdf mr-2"></i> Vista Previa</span>
                        <button type="button" onclick="cerrarPDF()"
                            class="text-gray-300 hover:text-white transition-colors">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>
                    <iframe id="pdf_preview" src="" width="100%" height="600px" style="border:none;"></iframe>
                </div>
            </div>

        </div>
    </div>
</main>

<div id="metadatosModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"
        onclick="toggleModal('metadatosModal')"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-2xl border border-gray-100">

                <div class="bg-scantec-blue px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold leading-6 text-white tracking-wide">
                        <i class="fas fa-info-circle mr-2"></i> Detalles del Archivo
                    </h3>
                    <button type="button" onclick="toggleModal('metadatosModal')"
                        class="text-white hover:text-gray-200 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-6 bg-gray-50 max-h-[70vh] overflow-y-auto">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" id="contenidoMetadatos">
                    </div>
                </div>

                <div class="bg-gray-100 px-6 py-3 flex justify-end">
                    <button type="button" onclick="toggleModal('metadatosModal')"
                        class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 transition-all">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pdfModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-90 transition-opacity backdrop-blur-md"
        onclick="toggleModal('pdfModal')"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-2 text-center sm:p-0">
            <div
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-7xl h-[90vh] flex flex-col border border-gray-600">

                <div class="bg-gray-900 px-4 py-3 flex justify-between items-center shrink-0">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <i class="fas fa-file-pdf text-red-500 mr-2"></i> Visor de Documento
                    </h3>
                    <div class="flex gap-3">
                        <button type="button" onclick="toggleModal('pdfModal')"
                            class="text-gray-400 hover:text-white transition-colors">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 bg-gray-200 relative w-full h-full">
                    <iframe id="pdf_preview_modal" class="absolute inset-0 w-full h-full border-none" src=""></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php pie() ?>

<script>
    const baseURL = "<?php echo base_url(); ?>";

    // --- LÓGICA DE MODALES TAILWIND ---
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (modal) {
            // Alternar clase hidden y flex
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
                modal.classList.add('flex'); // Para centrar con flexbox
                document.body.style.overflow = 'hidden'; // Evitar scroll en el fondo
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto'; // Restaurar scroll
            }
        }
    }

    // --- FUNCIÓN PARA ABRIR PDF EN MODAL ---
    function mostrarPDFModal(url) {
        // 1. Asignar URL al iframe
        const iframe = document.getElementById('pdf_preview_modal');
        iframe.src = url;

        // 2. Abrir el modal usando la nueva lógica
        toggleModal('pdfModal');
    }

    // --- FUNCIÓN PARA CARGAR METADATOS (AJAX) ---
    function cargarMetadatos(ruta) {
        const contenedor = document.getElementById('contenidoMetadatos');

        // Spinner de carga
        contenedor.innerHTML = `
            <div class="flex flex-col items-center justify-center py-8">
                <i class="fas fa-circle-notch fa-spin text-4xl text-scantec-blue mb-3"></i>
                <p class="text-gray-500 font-medium">Leyendo información del archivo...</p>
            </div>`;

        toggleModal('metadatosModal');

        // Construcción segura de la URL
        // Aseguramos que haya una barra entre base y controlador
        let urlDestino = baseURL.endsWith('/') ? baseURL + "expedientes/metadatos" : baseURL + "/expedientes/metadatos";

        const formData = new FormData();
        formData.append('ruta', ruta);
        // Agregamos el token CSRF si tu sistema lo requiere en todas las peticiones POST
        // formData.append('token', '<?php echo $_SESSION['csrf_token']; ?>'); 

        fetch(urlDestino, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la red: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                contenedor.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                contenedor.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 p-4 text-center">
                    <p class="text-red-700 font-bold"><i class="fas fa-exclamation-triangle"></i> Error</p>
                    <p class="text-sm text-red-600">No se pudieron cargar los metadatos.</p>
                    <p class="text-xs text-gray-500 mt-1">${error.message}</p>
                </div>`;
            });
    }

    // --- VISOR EMBEBIDO (VISTA RÁPIDA) ---
    function mostrarPDFServidor(pdfUrl) {
        const preview = document.getElementById('pdf_preview');
        const container = document.getElementById('pdf_preview_container');

        // AGREGAMOS PARÁMETROS DE VISUALIZACIÓN AL FINAL DE LA URL
        // #view=FitH (Ajustar al ancho) o #view=Fit (Ajustar a página completa)
        // scrollbar=1 (Permitir scroll)
        // toolbar=1 (Mostrar herramientas)
        const urlAjustada = pdfUrl + "#view=FitH&toolbar=1&navpanes=0";

        preview.src = urlAjustada;

        // Usar clases Tailwind para mostrar en lugar de style.display
        container.style.display = 'block';
        container.classList.remove('hidden');

        container.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // --- FUNCIÓN PARA ABRIR PDF EN MODAL ---
    function mostrarPDFModal(url) {
        const iframe = document.getElementById('pdf_preview_modal');
        const modal = document.getElementById('pdfModal');

        // Ajuste para modal: Fit (página completa) suele verse mejor en pantalla completa
        const urlAjustada = url + "#view=FitH&toolbar=1&navpanes=0";

        iframe.src = urlAjustada;

        // Mostrar modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    // Función cerrar modal (necesaria si usas la lógica manual anterior)
    function cerrarModalPDF() {
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdf_preview_modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        iframe.src = ""; // Limpiar iframe para detener carga/memoria
        document.body.style.overflow = 'auto';
    }
</script>

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