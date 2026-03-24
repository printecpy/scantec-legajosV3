<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-chart-line mr-3"></i> Reportes de Expedientes
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Generación de informes y notificaciones automáticas.
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="space-y-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h5 class="font-bold text-slate-800 flex items-center">
                            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Exportar a PDF
                        </h5>
                    </div>
                    <div class="p-6">
                        <form method="post" action="<?php echo base_url();?>expedientes/pdf_filtroFecha" target="_blank" autocomplete="off" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="desde_pdf" class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="far fa-calendar-alt text-gray-400"></i></div>
                                        <input id="desde_pdf" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition-all" type="date" name="desde" value="<?php echo date("Y-m-d"); ?>" required>
                                    </div>
                                </div>
                                <div>
                                    <label for="hasta_pdf" class="block text-xs font-bold text-gray-500 uppercase mb-2">Hasta</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="far fa-calendar-alt text-gray-400"></i></div>
                                        <input id="hasta_pdf" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition-all" type="date" name="hasta" value="<?php echo date("Y-m-d"); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-bold py-2.5 rounded-xl transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i> Generar PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                        <h5 class="font-bold text-slate-800 flex items-center">
                            <i class="fas fa-file-excel mr-2 text-green-500"></i> Exportar a Excel
                        </h5>
                    </div>
                    <div class="p-6">
                        <form method="post" action="<?php echo base_url();?>expedientes/excel_filtroFecha" target="_blank" autocomplete="off" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="desde_excel" class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="far fa-calendar-alt text-gray-400"></i></div>
                                        <input id="desde_excel" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all" type="date" name="desde" value="<?php echo date("Y-m-d"); ?>" required>
                                    </div>
                                </div>
                                <div>
                                    <label for="hasta_excel" class="block text-xs font-bold text-gray-500 uppercase mb-2">Hasta</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="far fa-calendar-alt text-gray-400"></i></div>
                                        <input id="hasta_excel" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all" type="date" name="hasta" value="<?php echo date("Y-m-d"); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="mt-4 w-full bg-green-50 text-green-600 border border-green-200 hover:bg-green-100 font-bold py-2.5 rounded-xl transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-file-excel mr-2"></i> Generar Excel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <h5 class="font-bold text-slate-800">Registros Duplicados</h5>
                            <p class="text-xs text-gray-500">Analizar integridad de datos</p>
                        </div>
                        <form method="post" action="<?php echo base_url();?>expedientes/excel_filtroDuplic" target="_blank" autocomplete="off">
                            <button type="submit" class="bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 font-bold py-2 px-4 rounded-xl transition-all flex items-center shadow-sm text-sm">
                                <i class="fas fa-clone mr-2"></i> Exportar Reporte
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- <div class="space-y-8">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-indigo-50 flex items-center justify-between">
                        <h5 class="font-bold text-indigo-900 flex items-center">
                            <i class="fas fa-envelope-open-text mr-2"></i> Notificar Documentos a Vencer
                        </h5>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="<?= base_url(); ?>Expedientes/pdf_emails">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label for="desde_email1" class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde Fecha</label>
                                    <input id="desde_email1" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" type="date" name="desde" value="<?php echo date("Y-m-d"); ?>" required>
                                </div>
                                <div>
                                    <label for="dias1" class="block text-xs font-bold text-gray-500 uppercase mb-2">Días Proyección</label>
                                    <input id="dias1" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" type="text" name="dias" placeholder="Ej: 30" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="emails1" class="block text-xs font-bold text-gray-500 uppercase mb-2">Destinatarios (Separar por comas)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 pt-3 pointer-events-none"><i class="fas fa-at text-gray-400"></i></div>
                                    <textarea id="emails1" name="emails" rows="2" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="correo1@empresa.com, correo2@empresa.com" required></textarea>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="nombres1" class="block text-xs font-bold text-gray-500 uppercase mb-2">Nombres Destinatarios</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user-friends text-gray-400"></i></div>
                                    <input type="text" id="nombres1" name="nombres" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Juan, Maria" required>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 text-white hover:bg-indigo-700 font-bold py-2.5 rounded-xl transition-all flex items-center justify-center shadow-md">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar Notificación
                            </button>
                        </form>
                    </div>
                </div>

                <!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-blue-50 flex items-center justify-between">
                        <h5 class="font-bold text-blue-900 flex items-center">
                            <i class="fas fa-file-signature mr-2"></i> Docs. Firmados a Vencer
                        </h5>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="<?= base_url(); ?>Expedientes/pdf_emails">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="md:col-span-2">
                                    <label for="desde_email2" class="block text-xs font-bold text-gray-500 uppercase mb-2">Desde Fecha</label>
                                    <input id="desde_email2" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all" type="date" name="desde" value="<?php echo date("Y-m-d"); ?>" required>
                                </div>
                                <div>
                                    <label for="dias2" class="block text-xs font-bold text-gray-500 uppercase mb-2">Días Proyección</label>
                                    <input id="dias2" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all" type="text" name="dias" placeholder="Ej: 15" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="emails2" class="block text-xs font-bold text-gray-500 uppercase mb-2">Destinatarios</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 pt-3 pointer-events-none"><i class="fas fa-at text-gray-400"></i></div>
                                    <textarea id="emails2" name="emails" rows="2" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="correo1@empresa.com" required></textarea>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="nombres2" class="block text-xs font-bold text-gray-500 uppercase mb-2">Nombres</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user-friends text-gray-400"></i></div>
                                    <input type="text" id="nombres2" name="nombres" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 outline-none transition-all" placeholder="Director, Gerente" required>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-blue-600 text-white hover:bg-blue-700 font-bold py-2.5 rounded-xl transition-all flex items-center justify-center shadow-md">
                                <i class="fas fa-paper-plane mr-2"></i> Enviar Informe Firmados
                            </button>
                        </form>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</main>

<?php pie() ?>