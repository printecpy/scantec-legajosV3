<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-cogs mr-3"></i> Configuración del Motor de Legajos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Administre el catálogo global de documentos y las reglas de los checklists.
                </p>
            </div>
        </div>

        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button onclick="cambiarPestana('catalogo')" id="tab-catalogo" class="border-scantec-blue text-scantec-blue border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-book mr-2"></i> 1. Catálogo Maestro de Documentos
                </button>
                <button onclick="cambiarPestana('matriz')" id="tab-matriz" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-project-diagram mr-2"></i> 2. Matriz de Requisitos (Checklists)
                </button>
            </nav>
        </div>

        <div id="seccion-catalogo" class="block animate-fade-in-down">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        Listado de Documentos Universales
                    </h5>
                    <button class="bg-white text-scantec-blue px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-gray-100">
                        <i class="fas fa-plus mr-1"></i> Nuevo Documento
                    </button>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre del Documento</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">¿Vence?</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Días Vigencia</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aviso Previo</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">Cédula de Identidad</td>
                                <td class="px-4 py-4 text-sm text-gray-500 font-mono">CEDULA</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">SÍ</span>
                                </td>
                                <td class="px-4 py-4 text-center text-sm text-gray-700">3650 días</td>
                                <td class="px-4 py-4 text-center text-sm text-gray-700">30 días</td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-blue-600 hover:text-blue-900 mx-2"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-500 hover:text-red-700 mx-2"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">Factura de ANDE</td>
                                <td class="px-4 py-4 text-sm text-gray-500 font-mono">FACT_ANDE</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">NO</span>
                                </td>
                                <td class="px-4 py-4 text-center text-sm text-gray-400">-</td>
                                <td class="px-4 py-4 text-center text-sm text-gray-400">-</td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-blue-600 hover:text-blue-900 mx-2"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-500 hover:text-red-700 mx-2"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="seccion-matriz" class="hidden animate-fade-in-down">
            
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Seleccione el Tipo de Legajo a Configurar</label>
                    <select class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none font-bold text-gray-700 cursor-pointer shadow-sm">
                        <option value="">Seleccione un tipo de expediente...</option>
                        <option value="1" selected>Carpeta de Crédito (Cooperativa)</option>
                        <option value="2">Legajo de Recursos Humanos</option>
                    </select>
                </div>
                <button class="px-6 py-2.5 bg-gray-800 text-white rounded-lg font-bold shadow-sm hover:bg-black transition-all">
                    Cargar Matriz
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        <i class="fas fa-list-check mr-2 text-yellow-500"></i> Reglas para: Carpeta de Crédito
                    </h5>
                    <button class="bg-scantec-blue text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-blue-600">
                        <i class="fas fa-plus mr-1"></i> Agregar Regla
                    </button>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento Maestro</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Rol Vinculado</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Obligatorio</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Reemplazable</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-center">
                                    <input type="number" value="1" class="w-16 px-2 py-1 text-center border rounded text-sm font-bold">
                                </td>
                                <td class="px-6 py-4">
                                    <select class="w-full px-2 py-1.5 border rounded text-sm text-gray-700 font-bold">
                                        <option value="1" selected>Cédula de Identidad</option>
                                        <option value="2">Certificado de Trabajo</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <select class="w-full px-2 py-1.5 border rounded text-sm text-gray-700">
                                        <option value="TITULAR" selected>TITULAR</option>
                                        <option value="CONYUGE">CÓNYUGE</option>
                                        <option value="CODEUDOR">CODEUDOR</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-red-500 hover:text-red-700 font-bold text-xs"><i class="fas fa-times-circle mr-1"></i> Quitar</button>
                                </td>
                            </tr>

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-center">
                                    <input type="number" value="2" class="w-16 px-2 py-1 text-center border rounded text-sm font-bold">
                                </td>
                                <td class="px-6 py-4">
                                    <select class="w-full px-2 py-1.5 border rounded text-sm text-gray-700 font-bold">
                                        <option value="1" selected>Cédula de Identidad</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <select class="w-full px-2 py-1.5 border rounded text-sm text-gray-700">
                                        <option value="TITULAR">TITULAR</option>
                                        <option value="CONYUGE" selected>CÓNYUGE</option>
                                    </select>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                                    </label>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-red-500 hover:text-red-700 font-bold text-xs"><i class="fas fa-times-circle mr-1"></i> Quitar</button>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                        <button class="bg-scantec-blue text-white px-6 py-2.5 rounded-lg font-bold shadow-lg hover:bg-blue-800 transition-colors">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios de Matriz
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<script>
    // Lógica simple para cambiar de pestañas visualmente
    function cambiarPestana(id) {
        // Ocultar ambas secciones
        document.getElementById('seccion-catalogo').classList.add('hidden');
        document.getElementById('seccion-matriz').classList.add('hidden');
        
        // Resetear estilos de los botones
        document.getElementById('tab-catalogo').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
        document.getElementById('tab-matriz').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";

        // Mostrar la sección activa y pintar la pestaña de azul
        document.getElementById('seccion-' + id).classList.remove('hidden');
        document.getElementById('tab-' + id).className = "border-scantec-blue text-scantec-blue border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
    }
</script>

<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.25s ease-out forwards;
    }
</style>

<?php pie() ?>