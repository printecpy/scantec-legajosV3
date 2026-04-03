<?php encabezado($data); ?>

<?php
$roles = $data['roles'] ?? [];
$rolActual = $data['rol_actual'] ?? [];
$idRolActual = intval($data['id_rol_actual'] ?? 0);
$idDepartamentoActual = intval($data['id_departamento_actual'] ?? 0);
$accesosDisponibles = $data['accesos_disponibles'] ?? [];
$accesosActuales = $data['accesos_actuales'] ?? [];
$accesosFinalesEspeciales = [];

$grupos = [];
foreach ($accesosDisponibles as $clave => $info) {
    if ($clave === 'tipos_relacion_archivos') {
        $info['etiqueta'] = 'Legajos - Datos generales';
    }
    if ($clave === 'metodos_actualizacion_archivos') {
        $info['etiqueta'] = 'Métodos de Actualización de Archivos';
    }
    if (in_array($clave, ['tipos_relacion_archivos', 'metodos_actualizacion_archivos'], true)) {
        $accesosFinalesEspeciales[] = [$clave, $info];
        continue;
    }

    $grupo = $info['grupo'] ?? 'General';
    if (!isset($grupos[$grupo])) {
        $grupos[$grupo] = [];
    }
    $grupos[$grupo][$clave] = $info;
}

$ordenGrupos = [
    'Dashboard' => 1,
    'Legajos' => 2,
    'Administración' => 3,
    'Seguridad' => 4,
    'Funcionalidades' => 5,
    'Auditoría' => 6,
    'Archivos' => 7,
    'General' => 99,
];

uksort($grupos, static function ($a, $b) use ($ordenGrupos) {
    $ordenA = $ordenGrupos[$a] ?? 50;
    $ordenB = $ordenGrupos[$b] ?? 50;

    if ($ordenA === $ordenB) {
        return strcasecmp($a, $b);
    }

    return $ordenA <=> $ordenB;
});
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-user-lock mr-3"></i> Accesos por Rol y Departamento
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Define qué ventanas y funciones quedan activas para cada rol según su departamento asociado.
                </p>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-scantec-blue p-4 mb-6 rounded-r-lg">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-circle-info text-scantec-blue text-xl"></i>
                </div>
                <div>
                    <h4 class="text-scantec-blue font-bold mb-1">Control adicional de acceso</h4>
                    <p class="text-gray-700 text-sm">
                        Esta configuración se suma a los módulos globales. Si una ventana se desactiva aquí, también se bloquea el acceso directo por URL para ese rol en su departamento asociado.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                <h5 class="font-bold text-white flex items-center">
                    <i class="fas fa-filter mr-2 text-white"></i> Filtros de configuración
                </h5>
            </div>

            <form action="<?php echo base_url(); ?>funcionalidades/accesos" method="GET" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Rol</label>
                    <select name="id_rol" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 focus:border-scantec-blue focus:outline-none" onchange="this.form.submit()">
                        <?php foreach ($roles as $rol): ?>
                            <?php $idRol = intval($rol['id_rol'] ?? 0); ?>
                            <option value="<?php echo $idRol; ?>" <?php echo $idRol === $idRolActual ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rol['descripcion'] ?? ('Rol ' . $idRol)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 mb-2">Departamento</label>
                    <div class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700">
                        <?php if ($idDepartamentoActual > 0): ?>
                            <?php echo htmlspecialchars($rolActual['departamento_nombre'] ?? ('Departamento ' . $idDepartamentoActual)); ?>
                        <?php else: ?>
                            <span class="text-amber-700">Sin departamento asociado</span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">El departamento se toma automáticamente del rol seleccionado.</p>
                </div>
            </form>
        </div>

        <form action="<?php echo base_url(); ?>funcionalidades/guardar_accesos" method="POST" id="frmAccesosRolDepartamento">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id_rol" value="<?php echo $idRolActual; ?>">
            <input type="hidden" name="id_departamento" value="<?php echo $idDepartamentoActual; ?>">

            <?php foreach ($grupos as $grupo => $items): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue flex items-center justify-between gap-4">
                        <h5 class="font-bold text-white flex items-center">
                            <i class="fas fa-layer-group mr-2 text-white"></i> <?php echo htmlspecialchars($grupo); ?>
                        </h5>
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-white/90 cursor-pointer">
                            <input type="checkbox" class="rounded border-white/40 text-white group-toggle" data-group="<?php echo md5($grupo); ?>" checked>
                            Seleccionar todo
                        </label>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-20">Activo</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Ventana o función</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Ruta</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $clave => $info): ?>
                                    <?php $habilitado = intval($accesosActuales[$clave] ?? 1) === 1; ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-4 text-center">
                                            <input type="checkbox"
                                                name="accesos[<?php echo htmlspecialchars($clave); ?>]"
                                                value="1"
                                                class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue item-toggle item-group-<?php echo md5($grupo); ?>"
                                                <?php echo $habilitado ? 'checked' : ''; ?>>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($info['etiqueta'] ?? $clave); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                            <?php echo htmlspecialchars($info['ruta'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?php echo htmlspecialchars($info['descripcion'] ?? ''); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php foreach ($accesosFinalesEspeciales as [$claveEspecial, $infoEspecial]): ?>
                <?php $habilitadoEspecial = intval($accesosActuales[$claveEspecial] ?? 1) === 1; ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue flex items-center justify-between gap-4">
                        <h5 class="font-bold text-white flex items-center">
                            <i class="fas fa-layer-group mr-2 text-white"></i> <?php echo htmlspecialchars($infoEspecial['etiqueta'] ?? $claveEspecial); ?>
                        </h5>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-20">Activo</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Ventana o función</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Ruta</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4 text-center">
                                        <input type="checkbox"
                                            name="accesos[<?php echo htmlspecialchars($claveEspecial); ?>]"
                                            value="1"
                                            class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue"
                                            <?php echo $habilitadoEspecial ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($infoEspecial['etiqueta'] ?? $claveEspecial); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                        <?php echo htmlspecialchars($infoEspecial['ruta'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?php echo htmlspecialchars($infoEspecial['descripcion'] ?? ''); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-end gap-3">
                <a href="<?php echo base_url(); ?>funcionalidades/listar"
                    class="px-6 py-2.5 border border-gray-300 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
                <button type="button" id="btnGuardarAccesos"
                    class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2.5 px-8 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar accesos
                </button>
            </div>
        </form>
    </div>
</main>

<?php pie(); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.group-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            const group = this.getAttribute('data-group');
            document.querySelectorAll('.item-group-' + group).forEach(function (item) {
                item.checked = toggle.checked;
            });
        });
    });

    const btnGuardar = document.getElementById('btnGuardarAccesos');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', function () {
            Swal.fire({
                title: 'Guardar accesos',
                text: 'Se actualizarán las ventanas activas para el rol seleccionado en su departamento asociado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#182541',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('frmAccesosRolDepartamento').submit();
                }
            });
        });
    }
});
</script>