<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-folder-plus mr-3"></i> Crear y Armar Legajo
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Complete los datos base para habilitar el checklist de documentos requeridos.
                </p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                title="Volver atrás">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>

        <form action="<?php echo base_url(); ?>unirpdf/procesar_legajo" method="POST" enctype="multipart/form-data"
            id="formArmadoLegajo" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-4">
                        <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-user-tag mr-2"></i> 1. Datos Base
                            </h5>
                        </div>

                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo de Legajo *</label>
                                <select name="tipo_legajo" id="tipo_legajo" required
                                    class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white font-bold text-gray-700 cursor-pointer shadow-sm transition-all">
                                    <option value="">Seleccione...</option>
                                    <option value="1" selected>Carpeta de Crédito</option>
                                    <option value="2">Legajo de RRHH</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CI del Socio *</label>
                                <input type="text" name="ci_socio" value="4.500.200" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all focus:bg-blue-50">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo *</label>
                                <input type="text" name="nombre_socio" value="Juan Pérez" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all focus:bg-blue-50">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nro. Solicitud</label>
                                <input type="text" name="nro_solicitud" value="SOL-2026-999"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all">
                            </div>

                            <hr class="border-gray-100">

                            <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-scantec-blue">Progreso Obligatorios</span>
                                    <span class="text-xs font-bold text-scantec-blue">1/3</span>
                                </div>
                                <div class="w-full bg-blue-200 rounded-full h-2">
                                    <div class="bg-scantec-blue h-2 rounded-full" style="width: 33%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                            <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                                <i class="fas fa-tasks mr-2 text-yellow-500"></i> 2. Checklist de Documentos Requeridos
                            </h5>
                            <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold">
                                🔴 Faltan Obligatorios
                            </span>
                        </div>

                        <div class="p-0 bg-white overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Rol</th>
                                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">

                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">Cédula de Identidad</div>
                                            <div class="text-xs text-red-500 font-bold">* Obligatorio</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700 font-semibold">TITULAR</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-gray-100 text-gray-600">
                                                PENDIENTE
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="file" name="doc_titular_cedula" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-blue-50 file:text-scantec-blue hover:file:bg-scantec-blue hover:file:text-white transition-all cursor-pointer">
                                        </td>
                                    </tr>

                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">Certificado de Trabajo</div>
                                            <div class="text-xs text-red-500 font-bold">* Obligatorio</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700 font-semibold">TITULAR</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-gray-100 text-gray-600">
                                                PENDIENTE
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="file" name="doc_titular_cert" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-blue-50 file:text-scantec-blue hover:file:bg-scantec-blue hover:file:text-white transition-all cursor-pointer">
                                        </td>
                                    </tr>

                                    <tr class="bg-green-50 hover:bg-green-100 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">Cédula de Identidad</div>
                                            <div class="text-xs text-gray-500 font-bold">Opcional</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700 font-semibold">CÓNYUGE</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-green-200 text-green-800">
                                                ✅ CARGADO
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <button type="button" class="text-scantec-blue hover:text-blue-900 mr-3" title="Ver Documento"><i class="fas fa-eye"></i> Ver</button>
                                            <button type="button" class="text-gray-500 hover:text-gray-900" title="Actualizar"><i class="fas fa-sync-alt"></i> Reemplazar</button>
                                        </td>
                                    </tr>

                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">Cédula de Identidad</div>
                                            <div class="text-xs text-red-500 font-bold">* Obligatorio</div>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-700 font-semibold">CODEUDOR 1</td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-gray-100 text-gray-600">
                                                PENDIENTE
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="file" name="doc_codeudor_cedula" accept=".pdf" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:font-bold file:bg-blue-50 file:text-scantec-blue hover:file:bg-scantec-blue hover:file:text-white transition-all cursor-pointer">
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-4">
                        <button type="button" class="px-6 py-3.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold shadow-sm hover:bg-gray-50 transition-all">
                            Guardar Borrador
                        </button>
                        <button type="submit" class="px-8 py-3.5 bg-scantec-blue text-white rounded-xl font-bold shadow-lg hover:bg-blue-800 transition-all flex items-center group">
                            <i class="fas fa-layer-group mr-2 group-hover:scale-110 transition-transform"></i> Unir PDFs y Finalizar Legajo
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</main>

<style>
    /* Suaviza los inputs file de Tailwind */
    input[type=file]::file-selector-button {
        transition: all 0.2s ease-in-out;
    }
</style>

<?php pie() ?>