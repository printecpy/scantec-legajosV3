<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-layer-group mr-3"></i> Armado de Legajo Digital
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    El <strong>Índice 4</strong> definirá el nombre del archivo PDF final.
                </p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                title="Volver atrás">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <form action="<?php echo base_url(); ?>unirpdf/procesar_pdf" method="POST" enctype="multipart/form-data"
            id="formArmado" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-800 px-6 py-3 border-b border-gray-700 flex justify-between items-center">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-file-upload mr-2 text-yellow-500"></i> Documentos a Unir
                            </h5>
                            <div class="flex items-center gap-2 bg-gray-700 p-1 rounded-lg">
                                <input type="number" id="cantidad_filas" value="1" min="1" max="20"
                                    class="w-12 px-1 py-1 text-xs rounded text-gray-900 border-none focus:ring-0 text-center font-bold bg-white">
                                <button type="button" onclick="agregarFilasMasivas()"
                                    class="text-xs bg-scantec-blue hover:bg-blue-600 text-white px-3 py-1 rounded transition font-bold shadow-sm flex items-center">
                                    <i class="fas fa-plus mr-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                        <div class="p-6 bg-gray-50 min-h-[400px]">
                            <div id="lista-adjuntos" class="space-y-3"></div>
                            <div id="mensaje-vacio"
                                class="text-center py-12 border-2 border-dashed border-gray-300 rounded-xl mt-4 bg-white hidden">
                                <i class="fas fa-cloud-upload-alt text-3xl text-scantec-blue mb-3 block"></i>
                                <h6 class="text-gray-600 font-bold">Lista vacía</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-4">
                        <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-tags mr-2"></i> Índices de Búsqueda
                            </h5>
                        </div>

                        <div class="p-6 space-y-5">

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo
                                    Documento</label>
                                <div class="relative">
                                    <select name="id_tipoDoc" id="id_tipoDoc" onchange="actualizarEtiquetas()"
                                        class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white font-bold text-gray-700 appearance-none cursor-pointer shadow-sm transition-all">
                                        <?php if (!empty($data['tipos_documento'])): ?>
                                            <?php foreach ($data['tipos_documento'] as $tipo): ?>
                                                <option value="<?php echo $tipo['id_tipoDoc']; ?>"
                                                    data-nombre="<?php echo $tipo['nombre_tipoDoc']; ?>"
                                                    data-ind1="<?php echo $tipo['indice_01']; ?>"
                                                    data-ind2="<?php echo $tipo['indice_02']; ?>"
                                                    data-ind3="<?php echo $tipo['indice_03']; ?>"
                                                    data-ind4="<?php echo $tipo['indice_04']; ?>"
                                                    data-ind5="<?php echo $tipo['indice_05']; ?>"
                                                    data-ind6="<?php echo $tipo['indice_06']; ?>">
                                                    <?php echo $tipo['nombre_tipoDoc']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No hay tipos cargados</option>
                                        <?php endif; ?>
                                    </select>
                                    <input type="hidden" name="nombre_tipo_doc" id="nombre_tipo_doc" value="">
                                    <div
                                        class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-scantec-blue">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100">

                            <div class="space-y-4" id="contenedor-indices">

                                <?php for ($i = 1; $i <= 6; $i++): ?>

                                    <?php
                                    // El índice 4 tiene un diseño especial (azul) porque es el Nombre
                                    $clase_extra = ($i == 4) ? "bg-blue-50 p-3 rounded-xl border border-blue-200" : "";
                                    $clase_label = ($i == 4) ? "text-scantec-blue flex justify-between" : "text-gray-500";
                                    ?>

                                    <div id="bloque_indice_0<?php echo $i; ?>" class="<?php echo $clase_extra; ?>">
                                        <label id="lbl_indice_0<?php echo $i; ?>"
                                            class="block text-xs font-bold uppercase mb-1 <?php echo $clase_label; ?>">
                                            <?php echo ($i == 4) ? '<span>Índice 4 (Nombre)</span> <i class="fas fa-file-signature"></i>' : "Índice $i"; ?>
                                        </label>

                                        <input type="text" name="indice_0<?php echo $i; ?>" id="indice_0<?php echo $i; ?>"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all focus:bg-blue-50">

                                        <?php if ($i == 4): ?>
                                            <p class="text-[10px] text-blue-500 mt-1 italic">
                                                * Nombre del archivo final.
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                <?php endfor; ?>
                            </div>

                            <div class="pt-4 mt-6 border-t border-gray-100">
                                <button type="submit"
                                    class="w-full py-3.5 bg-scantec-blue text-white rounded-xl font-bold shadow-lg hover:bg-blue-800 transition-all flex justify-center items-center group">
                                    <i class="fas fa-cog mr-2 group-hover:rotate-90 transition-transform"></i> Procesar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        agregarFila(); // Una fila por defecto
        actualizarEtiquetas(); // Ejecutar lógica visual al cargar
    });

    // --- 1. LÓGICA DE FILAS (Archivos) ---
    let contadorFiles = 0;
    function agregarFilasMasivas() {
        const cant = document.getElementById('cantidad_filas').value;
        for (let i = 0; i < cant; i++) agregarFila();
    }
    function agregarFila() {
        contadorFiles++;
        document.getElementById('mensaje-vacio').classList.add('hidden');
        const div = document.createElement('div');
        div.className = "flex items-center gap-3 p-3 bg-white rounded-xl border border-gray-200 shadow-sm animate-fade-in-down";
        div.id = `fila-${contadorFiles}`;
        div.innerHTML = `
            <div class="flex-none flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-scantec-blue font-bold text-xs">${contadorFiles}</div>
            <div class="flex-1"><input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-scantec-blue hover:file:text-white transition-all cursor-pointer"></div>
            <div class="flex-none"><button type="button" onclick="eliminarFila(${contadorFiles})" class="text-gray-300 hover:text-red-500 w-8 h-8 rounded-full hover:bg-red-50"><i class="fas fa-trash-alt"></i></button></div>
        `;
        document.getElementById('lista-adjuntos').appendChild(div);
    }
    function eliminarFila(id) {
        document.getElementById(`fila-${id}`).remove();
        if (document.getElementById('lista-adjuntos').children.length === 0)
            document.getElementById('mensaje-vacio').classList.remove('hidden');
    }

    // --- 2. LÓGICA DE ETIQUETAS (Visual igual a Subir Archivo) ---
    function actualizarEtiquetas() {
        const select = document.getElementById('id_tipoDoc');
        const inputNombre = document.getElementById('nombre_tipo_doc');
        if (!select || select.selectedIndex < 0) return;

        const opcion = select.options[select.selectedIndex];
        const nombreDoc = opcion.getAttribute('data-nombre');
        if(inputNombre) {
            inputNombre.value = nombreDoc;
        }
        // Obtenemos los valores de los data-attributes
        const labels = [
            opcion.getAttribute('data-ind1') || 'Índice 1',
            opcion.getAttribute('data-ind2') || 'Índice 2',
            opcion.getAttribute('data-ind3') || 'Índice 3',
            opcion.getAttribute('data-ind4') || 'Índice 4',
            opcion.getAttribute('data-ind5') || 'Índice 5',
            opcion.getAttribute('data-ind6') || 'Índice 6'
        ];

        // Recorremos los 6 campos
        for (let i = 0; i < 6; i++) {
            const num = i + 1;
            const bloque = document.getElementById('bloque_indice_0' + num); // El DIV contenedor
            const label = document.getElementById('lbl_indice_0' + num);     // El Label texto
            const input = document.getElementById('indice_0' + num);         // El Input

            if (bloque && label && input) {

                const textoLabel = labels[i].trim();

                // CONDICIÓN DE OCULTAR:
                // Si viene vacío o dice "no aplica" o "sin uso" -> OCULTAR
                const debeOcultar = (textoLabel === '' ||
                    textoLabel.toLowerCase() === 'no aplica' ||
                    textoLabel.toLowerCase() === 'sin uso');

                // EXCEPCIÓN: EL ÍNDICE 4 (Nombre) NUNCA SE OCULTA EN ESTE MÓDULO
                // Porque necesitamos un nombre para el archivo final obligatoriamente.
                if (num === 4) {
                    // Si viene vacío de BD, le ponemos un nombre por defecto visual
                    label.innerHTML = `<span>${(debeOcultar ? 'Nombre Archivo' : textoLabel)}</span> <i class="fas fa-file-signature"></i>`;
                    input.placeholder = "Ingrese nombre del archivo";
                    bloque.classList.remove('hidden');
                    input.setAttribute('required', 'required');
                }
                else {
                    // Lógica normal para los otros índices
                    if (debeOcultar) {
                        bloque.classList.add('hidden');       // Ocultar visualmente
                        input.removeAttribute('required');    // Quitar requerido
                        input.value = '';                     // Limpiar valor
                    } else {
                        bloque.classList.remove('hidden');    // Mostrar
                        label.textContent = textoLabel;       // Actualizar texto
                        input.placeholder = "Ingrese " + textoLabel;
                        // Opcional: input.setAttribute('required', 'required');
                    }
                }
            }
        }
    }
</script>

<style>
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-down {
        animation: fadeInDown 0.25s ease-out forwards;
    }
</style>

<?php pie() ?>