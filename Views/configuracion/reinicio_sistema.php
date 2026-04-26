<?php include "Views/template/header.php"; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="border-b border-red-100 bg-red-50/50 p-6">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Reinicio del Sistema</h2>
                            <p class="text-sm text-gray-500 mt-1">Esta acción es destructiva e irreversible. Permite purgar información de la base de datos y archivos físicos seleccionados.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <form id="form-reinicio" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Legajos Armados -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-red-500 hover:border-red-300">
                                <input type="checkbox" name="modulos[]" value="legajos" class="sr-only peer">
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="peer-checked:bg-red-500 peer-checked:text-white flex h-10 w-10 items-center justify-center rounded-lg border text-gray-400 bg-gray-50 transition-colors">
                                            <i class="fas fa-folder-open"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Legajos Armados</p>
                                            <p class="text-xs text-gray-500">Elimina legajos creados, documentos y PDFs físicos.</p>
                                        </div>
                                    </div>
                                    <div class="text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Matriz -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-red-500 hover:border-red-300">
                                <input type="checkbox" name="modulos[]" value="matriz" class="sr-only peer">
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="peer-checked:bg-red-500 peer-checked:text-white flex h-10 w-10 items-center justify-center rounded-lg border text-gray-400 bg-gray-50 transition-colors">
                                            <i class="fas fa-sitemap"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Matriz de Configuración</p>
                                            <p class="text-xs text-gray-500">Elimina tipos de legajos, requisitos y catálogo.</p>
                                        </div>
                                    </div>
                                    <div class="text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Personas -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-red-500 hover:border-red-300">
                                <input type="checkbox" name="modulos[]" value="personas" class="sr-only peer">
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="peer-checked:bg-red-500 peer-checked:text-white flex h-10 w-10 items-center justify-center rounded-lg border text-gray-400 bg-gray-50 transition-colors">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Personas / Titulares</p>
                                            <p class="text-xs text-gray-500">Elimina todo el listado de personas registradas.</p>
                                        </div>
                                    </div>
                                    <div class="text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Auditoria -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-red-500 hover:border-red-300">
                                <input type="checkbox" name="modulos[]" value="auditoria" class="sr-only peer">
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="peer-checked:bg-red-500 peer-checked:text-white flex h-10 w-10 items-center justify-center rounded-lg border text-gray-400 bg-gray-50 transition-colors">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Auditoría y Logs</p>
                                            <p class="text-xs text-gray-500">Elimina historiales, logs de sistema y visitas.</p>
                                        </div>
                                    </div>
                                    <div class="text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Usuarios -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus-within:ring-2 focus-within:ring-red-500 hover:border-red-300">
                                <input type="checkbox" name="modulos[]" value="usuarios" class="sr-only peer">
                                <div class="flex w-full items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="peer-checked:bg-red-500 peer-checked:text-white flex h-10 w-10 items-center justify-center rounded-lg border text-gray-400 bg-gray-50 transition-colors">
                                            <i class="fas fa-user-shield"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-sm">Usuarios y Roles</p>
                                            <p class="text-xs text-gray-500">Elimina usuarios (excepto Admin) y permisos.</p>
                                        </div>
                                    </div>
                                    <div class="text-red-500 opacity-0 peer-checked:opacity-100 transition-opacity">
                                        <i class="fas fa-check-circle text-xl"></i>
                                    </div>
                                </div>
                                <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none transition-colors"></div>
                            </label>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mt-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirmación de Seguridad</label>
                            <p class="text-xs text-gray-500 mb-3">Para evitar borrados accidentales, por favor escriba la palabra <strong class="text-red-600">CONFIRMAR</strong> en la siguiente casilla.</p>
                            <input type="text" id="input-confirmar" class="w-full sm:w-1/2 p-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-red-500 focus:border-red-500 outline-none" placeholder="Escriba CONFIRMAR" autocomplete="off">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50" onclick="marcarTodos()">Marcar Todos</button>
                            <button type="button" id="btn-procesar" class="px-5 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-sm flex items-center gap-2">
                                <i class="fas fa-trash-alt"></i> Ejecutar Limpieza
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function marcarTodos() {
        const checkboxes = document.querySelectorAll('input[name="modulos[]"]');
        let todosMarcados = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !todosMarcados);
    }

    document.getElementById('btn-procesar').addEventListener('click', function(e) {
        e.preventDefault();
        
        const modulos = Array.from(document.querySelectorAll('input[name="modulos[]"]:checked')).map(cb => cb.value);
        if (modulos.length === 0) {
            Swal.fire('Atención', 'Debe seleccionar al menos un módulo para limpiar.', 'warning');
            return;
        }

        const confirmacion = document.getElementById('input-confirmar').value;
        if (confirmacion !== 'CONFIRMAR') {
            Swal.fire('Confirmación Incorrecta', 'Debe escribir la palabra CONFIRMAR (en mayúsculas) para proceder.', 'error');
            return;
        }

        Swal.fire({
            title: '¿Está completamente seguro?',
            text: "Esta acción es IRREVERSIBLE. Se perderán todos los datos y archivos de los módulos seleccionados permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, borrar definitivamente',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('btn-procesar');
                const btnContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Limpiando...';
                btn.disabled = true;

                const formData = new FormData();
                modulos.forEach(m => formData.append('modulos[]', m));

                fetch('<?php echo base_url(); ?>configuracion/procesar_reinicio', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Limpieza Completada',
                            text: data.msg,
                            icon: 'success',
                            confirmButtonColor: '#182541'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.msg || 'Ha ocurrido un error inesperado.', 'error');
                        btn.innerHTML = btnContent;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo procesar la solicitud. Revise la consola para más detalles.', 'error');
                    btn.innerHTML = btnContent;
                    btn.disabled = false;
                });
            }
        });
    });
</script>

<?php include "Views/template/footer.php"; ?>
