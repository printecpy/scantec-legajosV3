<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                        <a href="<?php echo base_url(); ?>alerta/listar" class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-800 transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        Editar Alerta y Destinatarios
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Modifique los parámetros o agregue a quiénes se les enviará esta alerta.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-gray-50/80 px-6 py-4 border-b border-gray-100">
                    <h5 class="font-bold text-gray-800 text-lg"><i class="fas fa-edit text-gray-400 mr-2"></i> Parámetros de la Alerta</h5>
                </div>
                <form action="<?php echo base_url() ?>Alerta/modificar" method="post" autocomplete="off">
                    <div class="p-6">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id_tarea" value="<?php echo $data['tarea']['id']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nombre de Tarea</label>
                                <input class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 focus:border-transparent outline-none text-gray-700 text-sm" 
                                       type="text" name="nombre_tarea" value="<?php echo $data['tarea']['nombre_tarea']; ?>" required>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tipo de Informe</label>
                                <div class="relative">
                                    <select name="tipo_informe" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 outline-none text-gray-700 text-sm appearance-none">
                                        <option value="VENC_5_DIAS" <?php echo ($data['tarea']['tipo_informe'] == 'VENC_5_DIAS') ? 'selected' : ''; ?>>Documentos por vencer (5 días)</option>
                                        <option value="VENC_15_DIAS" <?php echo ($data['tarea']['tipo_informe'] == 'VENC_15_DIAS') ? 'selected' : ''; ?>>Documentos por vencer (15 días)</option>
                                        <option value="VENC_1_MES" <?php echo ($data['tarea']['tipo_informe'] == 'VENC_1_MES') ? 'selected' : ''; ?>>Documentos por vencer (1 Mes)</option>
                                        <option value="VENC_3_MESES" <?php echo ($data['tarea']['tipo_informe'] == 'VENC_3_MESES') ? 'selected' : ''; ?>>Documentos por vencer (3 Meses)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Frecuencia</label>
                                <div class="relative">
                                    <select name="frecuencia" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-gray-800 outline-none text-gray-700 text-sm appearance-none">
                                        <option value="DIARIA" <?php echo ($data['tarea']['frecuencia'] == 'DIARIA') ? 'selected' : ''; ?>>Diaria</option>
                                        <option value="SEMANAL" <?php echo ($data['tarea']['frecuencia'] == 'SEMANAL') ? 'selected' : ''; ?>>Semanal</option>
                                        <option value="MENSUAL" <?php echo ($data['tarea']['frecuencia'] == 'MENSUAL') ? 'selected' : ''; ?>>Mensual</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50/80 px-6 py-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-gray-800 text-white font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-sync-alt"></i> Actualizar Alerta
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-blue-50/50 px-6 py-4 border-b border-blue-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h5 class="font-bold text-blue-800 text-lg flex items-center gap-2">
                        <i class="fas fa-users text-blue-400"></i> Destinatarios de esta alerta
                    </h5>
                    
                    <form action="<?php echo base_url(); ?>Alerta/agregarDestinatario" method="POST" class="w-full sm:w-auto flex gap-2">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id_tarea" value="<?php echo $data['tarea']['id']; ?>">
                        <input type="email" name="correo_destino" required placeholder="ejemplo@printec.com.py" class="w-full sm:w-64 px-4 py-2 bg-white border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm text-gray-700">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors shadow-sm whitespace-nowrap">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 border-b border-gray-100">
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
                                <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center w-32">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            if (!empty($data['destinatarios'])) {
                                foreach ($data['destinatarios'] as $destinatario) { ?>
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-3 font-medium text-gray-700"><?php echo $destinatario['correo_destino']; ?></td>
                                <td class="px-6 py-3 text-center">
                                    <form action="<?php echo base_url() ?>Alerta/eliminarDestinatario" method="post" class="inline-block">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id_destinatario" value="<?php echo $destinatario['id']; ?>">
                                        <input type="hidden" name="id_tarea" value="<?php echo $data['tarea']['id']; ?>">
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar destinatario">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='2' class='px-6 py-8 text-center text-gray-400 font-medium'><i class='fas fa-envelope-open text-2xl mb-2 block opacity-50'></i>No hay destinatarios asignados a esta alerta.</td></tr>";
                            }
                            ?>
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