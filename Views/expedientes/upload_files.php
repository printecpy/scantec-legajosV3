<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-cloud-upload-alt mr-3"></i> Carga de Documentos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">Subir archivos al repositorio.</p>
            </div>
            <div>
                <a href="#" onclick="window.history.back(); return false;" class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden max-w-5xl mx-auto">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h5 class="font-bold text-slate-800"><i class="fas fa-pen-square mr-2 text-scantec-blue"></i> Datos del Expediente</h5>
            </div>

            <div class="p-8">
                <form action="<?php echo base_url(); ?>expedientes/subir" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tipo de documento</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-tags text-gray-400"></i></div>
                                
                                <select id="tipo_documento" name="id_tipoDoc" onchange="actualizarEtiquetas()"
                                        class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white transition-all" required>
                                    
                                    <?php foreach ($data['tipos_documentos'] as $doc): ?>
                                        <option value="<?= $doc['id_tipoDoc']; ?>"
                                            data-ind1="<?= $doc['indice_1']; ?>"
                                            data-ind2="<?= $doc['indice_2']; ?>"
                                            data-ind3="<?= $doc['indice_3']; ?>"
                                            data-ind4="<?= $doc['indice_4']; ?>"
                                            data-ind5="<?= $doc['indice_5']; ?>"
                                            data-ind6="<?= $doc['indice_6']; ?>">
                                            <?= $doc['nombre_tipoDoc']; ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Archivo (PDF)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-file-upload text-gray-400"></i></div>
                                <input id="file_pdf" type="file" name="file_pdf" accept=".pdf" class="pl-10 w-full px-4 py-1.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-gray-100 my-2"></div>

                        <div>
                            <label id="lbl_ind1" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 1</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_01" type="text" name="indice_01" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label id="lbl_ind2" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 2</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_02" type="text" name="indice_02" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label id="lbl_ind3" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 3</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_03" type="text" name="indice_03" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label id="lbl_ind4" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 4</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_04" type="text" name="indice_04" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label id="lbl_ind5" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 5</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_05" type="text" name="indice_05" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label id="lbl_ind6" class="block text-xs font-bold text-gray-500 uppercase mb-2">Índice 6</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-pen text-gray-400"></i></div>
                                <input id="indice_06" type="text" name="indice_06" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Cantidad de Páginas</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-copy text-gray-400"></i></div>
                                <input type="number" name="paginas" value="1" min="1" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" required>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-scantec-blue text-white hover:bg-gray-800 font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar Documento
                        </button>
                    </div>
                </form>

                <div id="pdf_preview_container" class="mt-8 hidden border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-gray-100 px-4 py-2 border-b border-gray-200 flex items-center">
                        <i class="fas fa-eye text-gray-500 mr-2"></i>
                        <h6 class="text-sm font-bold text-gray-700 m-0">Vista previa</h6>
                    </div>
                    <iframe id="pdf_preview" class="w-full h-[600px] border-none"></iframe>
                </div>
            </div>
        </div>
    </div>
</main>

<?php pie() ?>

<script>
    // Definimos la función globalmente por si la llamas desde el HTML (onchange)
    function actualizarEtiquetas() {
        const select = document.getElementById('tipo_documento');
        if (!select) return; // Seguridad si no existe el select

        const opcion = select.options[select.selectedIndex];

        // 1. Extraer los datos guardados en data-ind1, data-ind2...
        const labels = [
            opcion.getAttribute('data-ind1') || 'Índice 1',
            opcion.getAttribute('data-ind2') || 'Índice 2',
            opcion.getAttribute('data-ind3') || 'Índice 3',
            opcion.getAttribute('data-ind4') || 'Índice 4',
            opcion.getAttribute('data-ind5') || 'Índice 5',
            opcion.getAttribute('data-ind6') || 'Índice 6'
        ];

        // 2. Actualizar las etiquetas (Labels) y los Placeholders
        for (let i = 0; i < 6; i++) {
            const num = i + 1;
            const labelElement = document.getElementById('lbl_ind' + num);
            const inputElement = document.getElementById('indice_0' + num);

            if (labelElement && inputElement) {
                // Cambiar texto de la etiqueta
                labelElement.innerText = labels[i];

                // Cambiar placeholder
                inputElement.placeholder = "Ingrese " + labels[i];

                // 3. LÓGICA DE OCULTAR/MOSTRAR (Tu código original)
                // Buscamos el contenedor padre para ocultar todo el bloque (Label + Input)
                // Estructura: div > div relative > input. Subimos 2 niveles.
                const contenedorPadre = inputElement.parentElement.parentElement;

                if (labels[i] === '' || labels[i] === 'No aplica' || labels[i] === 'Sin uso') {
                    // Ocultar
                    contenedorPadre.classList.add('hidden'); // Usa clase Tailwind
                    // Alternativa si prefieres estilo directo: contenedorPadre.style.display = 'none';
                    inputElement.removeAttribute('required');
                    inputElement.value = ''; // Limpiar valor por si acaso
                } else {
                    // Mostrar
                    contenedorPadre.classList.remove('hidden');
                    // Alternativa: contenedorPadre.style.display = 'block';
                    inputElement.setAttribute('required', 'required');
                }
            }
        }
    }

    // --- BLOQUE PRINCIPAL DE INICIALIZACIÓN ---
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Inicializar etiquetas al cargar la página
        actualizarEtiquetas();

        // 2. Configurar el evento Change del Select (por si borras el onchange del HTML)
        const selectDoc = document.getElementById('tipo_documento');
        if(selectDoc) {
            selectDoc.addEventListener('change', actualizarEtiquetas);
        }

        // 3. Lógica del Previsualizador PDF (Blindada)
        const fileInput = document.getElementById('file_pdf');
        const previewContainer = document.getElementById('pdf_preview_container');
        const iframePreview = document.getElementById('pdf_preview');

        if (fileInput && previewContainer && iframePreview) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                // Limpieza previa
                iframePreview.src = "";
                previewContainer.classList.add('hidden');

                if (file) {
                    // Validar tipo MIME
                    if (file.type === 'application/pdf') {
                        const fileURL = URL.createObjectURL(file);
                        iframePreview.src = fileURL;
                        previewContainer.classList.remove('hidden');
                    } else {
                        // Alerta de error
                        Swal.fire({
                            icon: 'error',
                            title: 'Archivo no válido',
                            text: 'Por favor seleccione únicamente archivos PDF.',
                            confirmButtonColor: '#1d4ed8'
                        });
                        fileInput.value = ''; // Borrar input
                    }
                }
            });
        }
    });
</script>