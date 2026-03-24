<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-shield-alt mr-3"></i> Seguridad — Permisos de Legajos y Roles
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Define qué acciones y vistas puede utilizar cada rol de usuarios en los módulos de legajos y seguridad.
                </p>
            </div>
        </div>

        <!-- Advertencia de Prevención de Auto-bloqueo -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-lock text-yellow-600 text-xl"></i>
                </div>
                <div>
                    <h4 class="text-yellow-800 font-bold mb-1">Protección contra Auto-bloqueo</h4>
                    <p class="text-yellow-700 text-sm">
                        El sistema previene automáticamente que te bloquees a ti mismo. Si intentas quitarte los permisos críticos 
                        <strong>"Gestionar Permisos"</strong>, <strong>"Gestionar Roles"</strong> o <strong>"Permisos de Legajos"</strong> 
                        en tu propio rol, estos se mantendrán habilitados para tu seguridad.
                    </p>
                </div>
            </div>
        </div>

        <form action="<?php echo base_url(); ?>seguridad/guardar_permisos_legajos" method="POST" id="frmPermisosLegajos">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center">
                        <i class="fas fa-th-list mr-2 text-white"></i> Matriz de Permisos por Rol
                    </h5>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2 text-xs text-white/80">
                            <span class="inline-block w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Vistas
                            <span class="inline-block w-3 h-3 rounded bg-amber-100 border border-amber-200 ml-2"></span> Acciones
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="tablaPermisos">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 sticky left-0 bg-gray-50 z-10 min-w-[180px]">
                                    Rol
                                </th>
                                <?php
                                $acciones = $data['acciones'];
                                foreach ($acciones as $clave => $info):
                                    $bgColor = $info['tipo'] === 'vista' ? 'bg-blue-50' : 'bg-amber-50';
                                    $borderColor = $info['tipo'] === 'vista' ? 'border-blue-100' : 'border-amber-100';
                                ?>
                                    <th class="text-center px-2 py-3 font-bold text-[10px] uppercase tracking-wider text-gray-600 min-w-[90px] <?php echo $bgColor; ?> border-l <?php echo $borderColor; ?>">
                                        <div class="flex flex-col items-center gap-1">
                                            <i class="<?php echo $info['icono']; ?> text-gray-400 text-xs"></i>
                                            <span class="leading-tight"><?php echo $info['etiqueta']; ?></span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                                <th class="text-center px-3 py-3 font-bold text-[10px] uppercase tracking-wider text-gray-500 bg-gray-100 border-l border-gray-200 min-w-[80px]">
                                    Todos
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $roles = $data['roles'];
                            $permisos = $data['permisos'];
                            foreach ($roles as $rol):
                                $idRol = intval($rol['id_rol']);
                                $permisosRol = $permisos[$idRol] ?? [];
                            ?>
                                <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition-colors rol-row" data-rol="<?php echo $idRol; ?>">
                                    <td class="px-4 py-3 font-bold text-gray-800 sticky left-0 bg-white z-10 border-r border-gray-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-blue-50 text-scantec-blue flex items-center justify-center mr-3 border border-blue-100 flex-shrink-0">
                                                <i class="fas fa-user-tag text-xs"></i>
                                            </div>
                                            <span class="truncate"><?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?></span>
                                        </div>
                                    </td>
                                    <?php foreach ($acciones as $clave => $info):
                                        $checked = !empty($permisosRol[$clave]) ? 'checked' : '';
                                        $bgColor = $info['tipo'] === 'vista' ? 'bg-blue-50/20' : 'bg-amber-50/20';
                                        
                                        // Identificar permisos críticos
                                        $esPermisoCritico = in_array($clave, ['gestionar_permisos', 'gestionar_roles', 'permisos_legajos']);
                                        $claseCritico = $esPermisoCritico ? 'permiso-critico' : '';
                                        $tituloTooltip = $esPermisoCritico ? 'Permiso crítico: No se puede desactivar en tu propio rol' : '';
                                    ?>
                                        <td class="text-center px-2 py-3 border-l border-gray-50 <?php echo $bgColor; ?>">
                                            <label class="inline-flex items-center cursor-pointer group relative" title="<?php echo htmlspecialchars($tituloTooltip); ?>">
                                                <input type="checkbox"
                                                    name="permisos[<?php echo $idRol; ?>][<?php echo $clave; ?>]"
                                                    value="1"
                                                    class="permiso-check <?php echo $claseCritico; ?> w-5 h-5 text-scantec-blue bg-white border-2 border-gray-300 rounded focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer hover:border-scantec-blue checked:border-scantec-blue checked:bg-scantec-blue"
                                                    data-rol="<?php echo $idRol; ?>"
                                                    data-accion="<?php echo $clave; ?>"
                                                    data-critico="<?php echo $esPermisoCritico ? '1' : '0'; ?>"
                                                    <?php echo $checked; ?>>
                                                <?php if ($esPermisoCritico): ?>
                                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-red-100 text-red-800 text-xs px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                                        <i class="fas fa-exclamation-circle mr-1"></i>Crítico
                                                    </span>
                                                <?php endif; ?>
                                            </label>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center px-3 py-3 bg-gray-50/50 border-l border-gray-200 border-r shadow-[-2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="toggle-all-rol w-5 h-5 text-green-500 bg-white border-2 border-gray-300 rounded focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer hover:border-green-500 checked:border-green-500 checked:bg-green-500" data-rol="<?php echo $idRol; ?>">
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                    <h5 class="font-bold text-white flex items-center">
                        <i class="fas fa-eye mr-2 text-white"></i> Visibilidad de Legajos entre Usuarios
                    </h5>
                    <p class="text-xs text-white/80 mt-1">
                        Define si los usuarios no administradores de cada rol pueden ver legajos creados por otros usuarios.
                    </p>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="text-left py-3 pr-4 font-bold text-xs uppercase tracking-wider text-gray-600">Rol</th>
                                <th class="text-left py-3 px-4 font-bold text-xs uppercase tracking-wider text-gray-600">Puede ver legajos de otros usuarios</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $visibilidadLegajosOtros = $data['visibilidad_legajos_otros'] ?? [];
                            foreach ($roles as $rol):
                                $idRol = intval($rol['id_rol']);
                                $esAdmin = $idRol <= 2;
                                $valorVisibilidad = $esAdmin ? '1' : strval(intval($visibilidadLegajosOtros[$idRol] ?? 1));
                            ?>
                                <tr class="border-b border-gray-50">
                                    <td class="py-4 pr-4">
                                        <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?></div>
                                        <?php if ($esAdmin): ?>
                                            <div class="text-xs text-gray-500 mt-1">Los roles administradores conservan acceso total.</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <select
                                            name="visibilidad_legajos_otros[<?php echo $idRol; ?>]"
                                            class="w-full md:w-44 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 focus:border-scantec-blue focus:outline-none"
                                            <?php echo $esAdmin ? 'disabled' : ''; ?>>
                                            <option value="1" <?php echo $valorVisibilidad === '1' ? 'selected' : ''; ?>>Si</option>
                                            <option value="0" <?php echo $valorVisibilidad === '0' ? 'selected' : ''; ?>>No</option>
                                        </select>
                                        <?php if ($esAdmin): ?>
                                            <input type="hidden" name="visibilidad_legajos_otros[<?php echo $idRol; ?>]" value="1">
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                    <h5 class="font-bold text-white flex items-center">
                        <i class="fas fa-table-columns mr-2 text-white"></i> Tarjetas del Dashboard por Rol
                    </h5>
                    <p class="text-xs text-white/80 mt-1">
                        Selecciona qué tarjetas del dashboard puede visualizar cada rol.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 min-w-[180px]">Rol</th>
                                <?php foreach (($data['dashboard_cards'] ?? []) as $claveCard => $cardInfo): ?>
                                    <th class="text-center px-3 py-3 font-bold text-[10px] uppercase tracking-wider text-gray-600 min-w-[120px]">
                                        <div class="flex flex-col items-center gap-1">
                                            <i class="<?php echo htmlspecialchars($cardInfo['icono'] ?? 'fas fa-square'); ?> text-gray-400 text-xs"></i>
                                            <span class="leading-tight"><?php echo htmlspecialchars($cardInfo['etiqueta'] ?? $claveCard); ?></span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dashboardCardsPorRol = $data['dashboard_cards_por_rol'] ?? [];
                            foreach ($roles as $rol):
                                $idRol = intval($rol['id_rol']);
                                $cardsRol = $dashboardCardsPorRol[$idRol] ?? [];
                            ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-800">
                                        <?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?>
                                    </td>
                                    <?php foreach (($data['dashboard_cards'] ?? []) as $claveCard => $cardInfo): ?>
                                        <?php $checkedCard = intval($cardsRol[$claveCard] ?? 1) === 1; ?>
                                        <td class="text-center px-3 py-3">
                                            <input type="checkbox"
                                                name="dashboard_cards[<?php echo $idRol; ?>][<?php echo htmlspecialchars($claveCard); ?>]"
                                                value="1"
                                                class="w-5 h-5 text-scantec-blue bg-white border-2 border-gray-300 rounded focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer hover:border-scantec-blue checked:border-scantec-blue checked:bg-scantec-blue"
                                                <?php echo $checkedCard ? 'checked' : ''; ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                    <h5 class="font-bold text-white flex items-center">
                        <i class="fas fa-layer-group mr-2 text-white"></i> Tipos de Legajo Visibles por Rol
                    </h5>
                    <p class="text-xs text-white/80 mt-1">
                        Esto define qué tipos de legajo puede consultar cada rol. Si un rol no tiene acceso a un tipo, no podrá verlo en búsquedas, dashboard ni acciones directas.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 min-w-[180px]">Rol</th>
                                <?php foreach (($data['tipos_legajo'] ?? []) as $tipoLegajo): ?>
                                    <th class="text-center px-3 py-3 font-bold text-[10px] uppercase tracking-wider text-gray-600 min-w-[120px]">
                                        <?php echo htmlspecialchars($tipoLegajo['nombre'] ?? 'Sin tipo'); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tiposLegajoPorRol = $data['tipos_legajo_por_rol'] ?? [];
                            foreach ($roles as $rol):
                                $idRol = intval($rol['id_rol']);
                                $tiposRol = $tiposLegajoPorRol[$idRol] ?? [];
                                $esAdminRol = $idRol <= 2;
                            ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-800">
                                        <?php echo htmlspecialchars($rol['descripcion'] ?? ''); ?>
                                    </td>
                                    <?php foreach (($data['tipos_legajo'] ?? []) as $tipoLegajo): ?>
                                        <?php
                                        $idTipoLegajo = intval($tipoLegajo['id_tipo_legajo'] ?? 0);
                                        $checkedTipo = $esAdminRol ? true : intval($tiposRol[$idTipoLegajo] ?? 1) === 1;
                                        ?>
                                        <td class="text-center px-3 py-3">
                                            <input type="checkbox"
                                                name="tipos_legajo_visibles[<?php echo $idRol; ?>][<?php echo $idTipoLegajo; ?>]"
                                                value="1"
                                                class="w-5 h-5 text-scantec-blue bg-white border-2 border-gray-300 rounded focus:ring-0 focus:ring-offset-0 transition-all cursor-pointer hover:border-scantec-blue checked:border-scantec-blue checked:bg-scantec-blue"
                                                <?php echo $checkedTipo ? 'checked' : ''; ?>
                                                <?php echo $esAdminRol ? 'disabled' : ''; ?>>
                                            <?php if ($esAdminRol): ?>
                                                <input type="hidden" name="tipos_legajo_visibles[<?php echo $idRol; ?>][<?php echo $idTipoLegajo; ?>]" value="1">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?php echo base_url(); ?>dashboard/listar"
                    class="px-6 py-2.5 border border-gray-300 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="button" id="btnGuardarPermisos"
                    class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2.5 px-8 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar Permisos
                </button>
            </div>
        </form>

    </div>
</main>

<?php pie() ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Obtener el rol del usuario actual desde la sesión
    const rolUsuarioActual = <?php echo intval($_SESSION['id_rol'] ?? 0); ?>;
    const permisosCriticos = ['gestionar_permisos', 'gestionar_roles', 'permisos_legajos'];

    // Toggle all checkboxes for a rol
    document.querySelectorAll('.toggle-all-rol').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const rolId = this.dataset.rol;
            const checked = this.checked;
            document.querySelectorAll('.permiso-check[data-rol="' + rolId + '"]').forEach(function(cb) {
                // Si es el rol del usuario actual y permisos críticos, no permitir desmarque
                if (!checked && rolId === String(rolUsuarioActual) && cb.dataset.critico === '1') {
                    cb.checked = true; // Mantener marcado
                    return;
                }
                cb.checked = checked;
            });
        });
    });

    // Validación de desmarque de permisos críticos
    document.querySelectorAll('.permiso-check').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const rolId = this.dataset.rol;
            const accion = this.dataset.accion;
            const esCritico = this.dataset.critico === '1';

            // Si el usuario intenta desmarcar un permiso crítico en su propio rol
            if (!this.checked && rolId === String(rolUsuarioActual) && esCritico) {
                Swal.fire({
                    title: 'Acción bloqueada',
                    text: 'No puedes desactivar el permiso "' + accion + '" en tu propio rol. El sistema previene auto-bloqueos de seguridad.',
                    icon: 'warning',
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'Entendido'
                });
                this.checked = true; // Re-marcar el checkbox
                return;
            }

            const total = document.querySelectorAll('.permiso-check[data-rol="' + rolId + '"]').length;
            const checked = document.querySelectorAll('.permiso-check[data-rol="' + rolId + '"]:checked').length;
            const toggleAll = document.querySelector('.toggle-all-rol[data-rol="' + rolId + '"]');
            if (toggleAll) {
                toggleAll.checked = (checked === total);
            }
        });
    });

    // Initialize "Todos" state on page load
    document.querySelectorAll('.toggle-all-rol').forEach(function(toggle) {
        const rolId = toggle.dataset.rol;
        const total = document.querySelectorAll('.permiso-check[data-rol="' + rolId + '"]').length;
        const checked = document.querySelectorAll('.permiso-check[data-rol="' + rolId + '"]:checked').length;
        toggle.checked = (total > 0 && checked === total);
    });

    // Confirm before saving
    document.getElementById('btnGuardarPermisos').addEventListener('click', function() {
        Swal.fire({
            title: '¿Guardar los permisos?',
            text: 'Se actualizarán los permisos de legajos para todos los roles. Nota: El sistema previene auto-bloqueos automáticamente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#182541',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('frmPermisosLegajos').submit();
            }
        });
    });
});
</script>
