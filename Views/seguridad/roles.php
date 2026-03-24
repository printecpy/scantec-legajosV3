<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4 max-w-4xl">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-user-tag mr-3"></i> Seguridad — Roles del Sistema
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Administra los roles del sistema que se utilizarán para asignar permisos a los usuarios.
                </p>
            </div>
            <button onclick="document.getElementById('modal-nuevo-rol').classList.remove('hidden')" class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2 px-5 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                <i class="fas fa-plus mr-2"></i> Nuevo Rol
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-24">ID Rol</th>
                            <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Descripción / Nombre del Rol</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-32">Estado</th>
                            <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['roles'])): ?>
                            <tr>
                                <td colspan="4" class="text-center py-6 text-gray-500">No hay roles registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($data['roles'] as $rol):
                                $idRol = intval($rol['id_rol']);
                                $estado = strtolower($rol['estado'] ?? 'activo');
                                $esActivo = ($estado === 'activo');
                                $esRolPropio = $idRol === intval($_SESSION['id_rol'] ?? 0);
                            ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors <?php echo !$esActivo ? 'opacity-70 bg-gray-50/50' : ''; ?>">
                                    <td class="text-center px-4 py-3 font-medium text-gray-600">
                                        <?php echo $idRol; ?>
                                    </td>
                                    <td class="text-left px-4 py-3 font-bold text-gray-800 flex items-center">
                                        <div class="w-8 h-8 rounded-full <?php echo $esActivo ? 'bg-blue-50 text-scantec-blue border-blue-100' : 'bg-gray-100 text-gray-400 border-gray-200'; ?> flex items-center justify-center mr-3 border flex-shrink-0">
                                            <i class="fas <?php echo $idRol === 1 ? 'fa-user-shield text-amber-500' : 'fa-user'; ?> text-xs"></i>
                                        </div>
                                        <?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?>
                                    </td>
                                    <td class="text-center px-4 py-3">
                                        <?php if ($esActivo): ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">Activo</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                onclick="abrirModalEditarRol(<?php echo $idRol; ?>, '<?php echo htmlspecialchars($rol['descripcion'] ?? '', ENT_QUOTES); ?>')"
                                                class="text-blue-500 hover:text-blue-700 p-2 rounded hover:bg-blue-50 transition-colors"
                                                title="Editar Rol">
                                                <i class="fas fa-pen fa-lg"></i>
                                            </button>
                                            <?php $nuevoEstado = $esActivo ? 'inactivo' : 'activo'; ?>
                                            <button onclick="<?php echo $esRolPropio ? 'return false;' : "cambiarEstadoRol($idRol, '$nuevoEstado')"; ?>" class="<?php echo $esRolPropio ? 'text-gray-300 cursor-not-allowed' : ($esActivo ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50' : 'text-green-500 hover:text-green-700 hover:bg-green-50'); ?> p-2 rounded transition-colors" title="<?php echo $esRolPropio ? 'No puedes cambiar el estado de tu propio rol' : ($esActivo ? 'Desactivar Rol' : 'Activar Rol'); ?>">
                                                <i class="fas <?php echo $esActivo ? 'fa-toggle-on' : 'fa-toggle-off'; ?> fa-lg"></i>
                                            </button>
                                            <button onclick="<?php echo $esRolPropio ? 'return false;' : "confirmarEliminarRol($idRol, '" . htmlspecialchars($rol['descripcion'] ?? '', ENT_QUOTES) . "')"; ?>" class="<?php echo $esRolPropio ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700 hover:bg-red-50'; ?> p-2 rounded transition-colors" title="<?php echo $esRolPropio ? 'No puedes eliminar tu propio rol' : 'Eliminar Rol'; ?>">
                                                <i class="fas fa-trash-alt fa-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- Modal Nuevo Rol -->
<div id="modal-nuevo-rol" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" style="backdrop-filter: blur(2px);">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-md transform transition-all p-6 relative">
        <button onclick="document.getElementById('modal-nuevo-rol').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times fa-lg"></i>
        </button>
        
        <h3 class="text-xl font-bold text-slate-800 mb-2">Crear Nuevo Rol</h3>
        <p class="text-xs text-gray-500 mb-6">Ingresa un nombre descriptivo para el nuevo rol de usuario.</p>
        
        <form action="<?php echo base_url(); ?>seguridad/guardar_rol" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="mb-5">
                <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                <input type="text" name="descripcion" id="descripcion" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow" placeholder="Ej. Analista de Crédito">
            </div>

            <div class="mb-5">
                <label for="preset" class="block text-sm font-bold text-gray-700 mb-2">Permisos Iniciales</label>
                <select name="preset" id="preset" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow" onchange="actualizarDescripcionPreset()">
                    <?php
                    require_once 'Models/SeguridadLegajosModel.php';
                    $presets = SeguridadLegajosModel::getPresetsPermisos();
                    foreach ($presets as $clave => $info):
                    ?>
                        <option value="<?php echo $clave; ?>" <?php echo $clave === 'basico' ? 'selected' : ''; ?>>
                            <i class="<?php echo $info['icono']; ?>"></i> <?php echo htmlspecialchars($info['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p id="descripcion-preset" class="text-xs text-gray-500 mt-2 italic">Acceso completo a legajos con operaciones básicas</p>
            </div>
            
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="document.getElementById('modal-nuevo-rol').classList.add('hidden')" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-scantec-blue hover:bg-gray-800 text-white font-bold rounded-xl shadow transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Rol -->
<div id="modal-editar-rol" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" style="backdrop-filter: blur(2px);">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-md transform transition-all p-6 relative">
        <button onclick="cerrarModalEditarRol()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times fa-lg"></i>
        </button>

        <h3 class="text-xl font-bold text-slate-800 mb-2">Editar Rol</h3>
        <p class="text-xs text-gray-500 mb-6">Actualiza el nombre visible del rol seleccionado.</p>

        <form action="<?php echo base_url(); ?>seguridad/actualizar_rol" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id_rol" id="editar_id_rol" value="">

            <div class="mb-5">
                <label for="editar_descripcion" class="block text-sm font-bold text-gray-700 mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                <input type="text" name="descripcion" id="editar_descripcion" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow" placeholder="Ej. Analista de Crédito">
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="cerrarModalEditarRol()" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-scantec-blue hover:bg-gray-800 text-white font-bold rounded-xl shadow transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php pie() ?>

<script>
function actualizarDescripcionPreset() {
    const presets = {
        'solo_lectura': 'Acceso a vistas de consulta, sin capacidad de modificación',
        'basico': 'Acceso completo a legajos con operaciones básicas',
        'avanzado': 'Acceso completo a legajos incluyendo administración y logs',
        'vacio': 'Sin permisos (se asignarán manualmente)'
    };
    
    const select = document.getElementById('preset');
    const selectedPreset = select.value;
    const descripcion = document.getElementById('descripcion-preset');
    
    if (presets[selectedPreset]) {
        descripcion.textContent = presets[selectedPreset];
    }
}

function confirmarEliminarRol(idRol, descripcion) {
    Swal.fire({
        title: '¿Eliminar Rol?',
        html: `¿Estás seguro que deseas eliminar el rol <strong>${descripcion}</strong>?<br><br><span class="text-sm text-red-500 font-bold">Si este rol tiene usuarios asignados, no se podrá eliminar. Se recomienda desactivarlo en su lugar.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#182541',
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url(); ?>seguridad/eliminar_rol?id_rol=${idRol}&token=<?php echo $_SESSION['csrf_token']; ?>`;
        }
    });
}

function cambiarEstadoRol(idRol, nuevoEstado) {
    const accion = nuevoEstado === 'activo' ? 'activar' : 'desactivar';
    const btnColor = nuevoEstado === 'activo' ? '#10b981' : '#f59e0b';
    
    Swal.fire({
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} rol?`,
        text: `¿Estás seguro que deseas ${accion} este rol de usuario?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: btnColor,
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, ' + accion,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?php echo base_url(); ?>seguridad/cambiar_estado_rol?id_rol=${idRol}&estado=${nuevoEstado}&token=<?php echo $_SESSION['csrf_token']; ?>`;
        }
    });
}

function abrirModalEditarRol(idRol, descripcion) {
    document.getElementById('editar_id_rol').value = idRol;
    document.getElementById('editar_descripcion').value = descripcion;
    document.getElementById('modal-editar-rol').classList.remove('hidden');
    document.getElementById('editar_descripcion').focus();
}

function cerrarModalEditarRol() {
    document.getElementById('modal-editar-rol').classList.add('hidden');
    document.getElementById('editar_id_rol').value = '';
    document.getElementById('editar_descripcion').value = '';
}
</script>
