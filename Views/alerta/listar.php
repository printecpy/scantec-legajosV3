<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        Gestor de Alertas
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Administra las notificaciones y tareas programadas del
                        sistema.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="<?php echo base_url(); ?>alerta/historial"
                        class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm flex items-center gap-2">
                        <i class="fas fa-history"></i> Historial
                    </a>
                    <button type="button" onclick="openModal('new_task')"
                        class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-xl transition-all shadow-lg shadow-gray-800/30 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nueva Alerta
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre de
                                    Tarea</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de
                                    Informe</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Frecuencia</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Próxima
                                    Ejecución</th>
                                <th
                                    class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($data['alerts'] as $alerta) { ?>
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-800"><?php echo $alerta['nombre_tarea']; ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                            <?php echo str_replace('_', ' ', $alerta['tipo_informe']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center text-sm text-gray-600 font-medium">
                                            <i class="fas fa-sync-alt mr-2 text-gray-400"></i>
                                            <?php echo $alerta['frecuencia']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                            <?php echo $alerta['fecha_proxima_ejecucion']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center justify-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <?php if ($alerta['estado'] == 'activo'): ?>
                                                <a href="<?php echo base_url() ?>alerta/editar?id=<?php echo $alerta['id']; ?>"
                                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                                    title="Modificar">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </a>
                                                <form action="<?php echo base_url() ?>alerta/eliminar" method="post"
                                                    class="inline-block eliminar">
                                                    <input type="hidden" name="token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $alerta['id']; ?>">
                                                    <button type="submit"
                                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Anular">
                                                        <i class="fas fa-trash-alt text-lg"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?php echo base_url() ?>alerta/reingresar" method="post"
                                                    class="inline-block reingresar">
                                                    <input type="hidden" name="token"
                                                        value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="id" value="<?php echo $alerta['id']; ?>">
                                                    <button type="submit"
                                                        class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                        title="Reactivar">
                                                        <i class="fas fa-check-circle text-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="new_task" class="fixed inset-0 z-[1050] hidden bg-gray-900/60 backdrop-blur-sm overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeModal('new_task')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100 relative z-10">
                
                <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h5 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <i class="fas fa-plus-circle text-gray-500"></i> Nueva Tarea Programada
                    </h5>
                    <button type="button" onclick="closeModal('new_task')" class="text-gray-400 hover:text-red-500 transition-colors outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form id="formNuevaTarea" method="post" action="<?php echo base_url(); ?>Alerta/insertar" autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="p-6 space-y-5">
                        <div>
                            <label for="nombre_tarea" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nombre de la Alerta</label>
                            <input type="text" id="nombre_tarea" name="nombre_tarea" required placeholder="Ej: Reporte de Vencimientos Semanal" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 focus:border-transparent transition-all outline-none text-gray-700 text-sm">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="tipo_informe" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tipo de Informe</label>
                                <div class="relative">
                                    <select id="tipo_informe" name="tipo_informe" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 focus:border-transparent transition-all outline-none text-gray-700 text-sm appearance-none">
                                        <option value="" disabled selected>Seleccione un reporte...</option>
                                        <option value="VENC_5_DIAS">Documentos por vencer (5 días)</option>
                                        <option value="VENC_15_DIAS">Documentos por vencer (15 días)</option>
                                        <option value="VENC_1_MES">Documentos por vencer (1 Mes)</option>
                                        <option value="VENC_3_MESES">Documentos por vencer (3 Meses)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>
                            <div>
                                <label for="frecuencia" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Frecuencia de Envío</label>
                                <div class="relative">
                                    <select id="frecuencia" name="frecuencia" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 focus:border-transparent transition-all outline-none text-gray-700 text-sm appearance-none">
                                        <option value="" disabled selected>Seleccione la frecuencia...</option>
                                        <option value="DIARIA">Diaria (Todos los días)</option>
                                        <option value="SEMANAL">Semanal (1 vez a la semana)</option>
                                        <option value="MENSUAL">Mensual (1 vez al mes)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex gap-3 mt-4">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                            <p class="text-xs text-blue-700 leading-relaxed">
                                Una vez creada la alerta, deberá asignarle destinatarios (correos) editando la tarea en la tabla principal. La primera ejecución se programará automáticamente.
                            </p>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex flex-col-reverse sm:flex-row justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="closeModal('new_task')" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Guardar Alerta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php pie() ?>
    <script>
    // Mostrar modal
    function openModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.remove('hidden'); // Quita el display:none
        document.body.classList.add('overflow-hidden'); // Evita que la página de fondo haga scroll
    }

    // Ocultar modal
    function closeModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.add('hidden'); // Vuelve a poner el display:none
        document.body.classList.remove('overflow-hidden'); // Devuelve el scroll a la página
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
                    timer: 5000,
                    customClass: {
                        popup: 'rounded-2xl shadow-xl border border-gray-100',
                        confirmButton: 'bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900'
                    }
                });
            });
        </script>";
        unset($_SESSION['alert']);
    }
    ?>
</div>