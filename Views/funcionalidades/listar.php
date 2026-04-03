<?php
encabezado();

$estados = $data['estados'] ?? [];
$modulosItems = $data['modulos_items'] ?? [];
$itemsAgrupacion = $data['items_agrupacion'] ?? [];
$itemsModuloActual = $data['items_modulo_actual'] ?? [];
$grupos = $data['grupos'] ?? [];
$claveGrupoModulo = null;

if (empty($grupos) && !empty($data['secciones']) && is_array($data['secciones'])) {
    foreach ($data['secciones'] as $claveSeccion => $infoSeccion) {
        $nombreGrupo = $infoSeccion['grupo'] ?? 'General';
        if (!isset($grupos[$nombreGrupo])) {
            $grupos[$nombreGrupo] = [];
        }
        $grupos[$nombreGrupo][$claveSeccion] = $infoSeccion;
    }
}

foreach (['Módulo', 'Módulos', 'Modulo', 'Modulos'] as $claveModuloGrupo) {
    if (isset($grupos[$claveModuloGrupo]) && is_array($grupos[$claveModuloGrupo])) {
        $claveGrupoModulo = $claveModuloGrupo;
        break;
    }
}
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        <form id="frmFuncionalidades" action="<?php echo base_url(); ?>funcionalidades/guardar" method="POST" class="space-y-6">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <?php if ($claveGrupoModulo !== null && !empty($grupos[$claveGrupoModulo])): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                        <h5 class="font-bold text-white flex items-center">
                            <i class="fas fa-layer-group mr-2 text-white"></i> Módulo
                        </h5>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Sección</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Descripción</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-48">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grupos[$claveGrupoModulo] as $clave => $info): ?>
                                    <?php $habilitado = strval(intval($estados[$clave] ?? 1)); ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-50 text-scantec-blue flex items-center justify-center border border-blue-100">
                                                    <i class="<?php echo htmlspecialchars($info['icono'] ?? 'fas fa-puzzle-piece'); ?>"></i>
                                                </div>
                                                <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($info['etiqueta'] ?? $clave); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?php echo htmlspecialchars($info['descripcion'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <select
                                                name="funcionalidades[<?php echo htmlspecialchars($clave); ?>]"
                                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 focus:border-scantec-blue focus:outline-none">
                                                <option value="1" <?php echo $habilitado === '1' ? 'selected' : ''; ?>>Activa</option>
                                                <option value="0" <?php echo $habilitado === '0' ? 'selected' : ''; ?>>Desactivada</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                    <h5 class="font-bold text-white flex items-center">
                        <i class="fas fa-sitemap mr-2 text-white"></i> Agrupación de Vistas y Sub-vistas por Módulo
                    </h5>
                    <p class="text-xs text-white/80 mt-1">
                        Organiza cada vista y sub-vista dentro del módulo correspondiente: Archivos, Legajos o Sistema.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Vista o Sub-vista</th>
                                <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Ruta</th>
                                <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-56">Módulo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemsAgrupacion as $claveItem => $itemInfo): ?>
                                <?php $moduloActual = $itemsModuloActual[$claveItem] ?? ($itemInfo['modulo'] ?? 'sistema'); ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($itemInfo['etiqueta'] ?? $claveItem); ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 font-mono text-xs">
                                        <?php echo htmlspecialchars($itemInfo['ruta'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <select
                                            name="modulos_items[<?php echo htmlspecialchars($claveItem); ?>]"
                                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 focus:border-scantec-blue focus:outline-none">
                                            <?php foreach ($modulosItems as $claveModulo => $etiquetaModulo): ?>
                                                <option value="<?php echo htmlspecialchars($claveModulo); ?>" <?php echo $moduloActual === $claveModulo ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($etiquetaModulo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php foreach ($grupos as $grupo => $items): ?>
                <?php if ($grupo === $claveGrupoModulo || $grupo === 'Sistema') { continue; } ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue">
                        <h5 class="font-bold text-white flex items-center">
                            <i class="fas fa-layer-group mr-2 text-white"></i> <?php echo htmlspecialchars($grupo); ?>
                        </h5>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Sección</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Descripción</th>
                                    <th class="text-left px-6 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-48">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $clave => $info): ?>
                                    <?php $habilitado = strval(intval($estados[$clave] ?? 1)); ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-blue-50 text-scantec-blue flex items-center justify-center border border-blue-100">
                                                    <i class="<?php echo htmlspecialchars($info['icono'] ?? 'fas fa-puzzle-piece'); ?>"></i>
                                                </div>
                                                <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($info['etiqueta'] ?? $clave); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">
                                            <?php echo htmlspecialchars($info['descripcion'] ?? ''); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <select
                                                name="funcionalidades[<?php echo htmlspecialchars($clave); ?>]"
                                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 focus:border-scantec-blue focus:outline-none">
                                                <option value="1" <?php echo $habilitado === '1' ? 'selected' : ''; ?>>Activa</option>
                                                <option value="0" <?php echo $habilitado === '0' ? 'selected' : ''; ?>>Desactivada</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-end gap-3">
                <a href="<?php echo base_url(); ?>dashboard/dashboard_legajos"
                    class="px-6 py-2.5 border border-gray-300 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="button" id="btnGuardarFuncionalidades"
                    class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2.5 px-8 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</main>

<?php pie(); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('btnGuardarFuncionalidades');
    if (!btnGuardar) {
        return;
    }

    btnGuardar.addEventListener('click', function() {
        Swal.fire({
            title: 'Guardar funcionalidades',
            text: 'Se aplicarán los cambios en el menú, en el acceso directo por URL y en la agrupación por módulo.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#182541',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-save"></i> Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('frmFuncionalidades').submit();
            }
        });
    });
});
</script>