<?php encabezado() ?>

<?php
$termino = $data['termino'] ?? '';
$resultados = $data['resultados'] ?? [];
$busqueda_ejecutada = $data['busqueda_ejecutada'] ?? false;
$estado_legajo = $data['estado_legajo'] ?? '';
$id_tipo_legajo = intval($data['id_tipo_legajo'] ?? 0);
$filtro_documentos = $data['filtro_documentos'] ?? '';
$tipos_legajo = $data['tipos_legajo'] ?? [];
$formatearCi = static function ($valor) {
    $digitos = preg_replace('/\D+/', '', (string) $valor);
    return $digitos === '' ? '' : preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', $digitos);
};
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-search mr-3"></i> Búsqueda de Legajos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Busque legajos por CI, nombre completo o número de solicitud. Use `*.*` para listar todos.
                </p>
            </div>

            <div><a href="#" onclick="window.history.back(); return false;"
                    class="group px-4 h-10 rounded-xl bg-gray-600 text-white hover:bg-gray-800 shadow-md flex items-center justify-center transition-all font-bold text-sm"
                    title="Volver atrás">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h5 class="font-bold text-slate-800 flex items-center">
                    <i class="fas fa-filter mr-2 text-scantec-blue"></i> Criterios de Búsqueda
                </h5>
            </div>

            <div class="p-6">
                <form action="<?php echo base_url(); ?>legajos/buscar_legajos" method="get" autocomplete="off">
                    <?php if ($filtro_documentos !== ''): ?>
                    <input type="hidden" name="filtro_documentos" value="<?php echo htmlspecialchars($filtro_documentos); ?>">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                        <div class="md:col-span-12">
                            <label for="termino" class="block text-sm font-bold text-gray-700 mb-2">Dato básico del legajo:</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input id="termino" class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                    type="text" name="termino" value="<?php echo htmlspecialchars($termino); ?>"
                                    placeholder="Ej: 1234567, Juan Perez, 56584 o *.*">
                            </div>
                        </div>

                        <div class="md:col-span-4">
                            <label for="estado_legajo" class="block text-sm font-bold text-gray-700 mb-2">Estado de legajo:</label>
                            <select id="estado_legajo" name="estado_legajo"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-700 transition-shadow">
                                <option value="">Todos los estados</option>
                                <option value="Proceso" <?php echo $estado_legajo === 'Proceso' ? 'selected' : ''; ?>>Proceso</option>
                                <option value="Incompleto" <?php echo $estado_legajo === 'Incompleto' ? 'selected' : ''; ?>>Incompleto</option>
                                <option value="Vencido" <?php echo $estado_legajo === 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                                <option value="Completado" <?php echo $estado_legajo === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                                <option value="Generado" <?php echo $estado_legajo === 'Generado' ? 'selected' : ''; ?>>Generado</option>
                                <option value="Verificación rechazada" <?php echo $estado_legajo === 'Verificación rechazada' ? 'selected' : ''; ?>>Verificación rechazada</option>
                                <option value="Verificado" <?php echo $estado_legajo === 'Verificado' ? 'selected' : ''; ?>>Verificado</option>
                                <option value="Cerrado" <?php echo $estado_legajo === 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label for="id_tipo_legajo" class="block text-sm font-bold text-gray-700 mb-2">Tipo de legajo:</label>
                            <select id="id_tipo_legajo" name="id_tipo_legajo"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-700 transition-shadow">
                                <option value="0">Todos los tipos</option>
                                <?php foreach ($tipos_legajo as $tipo_legajo): ?>
                                <option value="<?php echo intval($tipo_legajo['id_tipo_legajo']); ?>" <?php echo intval($tipo_legajo['id_tipo_legajo']) === $id_tipo_legajo ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo_legajo['nombre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <button type="submit"
                                class="w-full py-2.5 px-4 bg-scantec-blue text-white font-bold rounded-xl shadow-md hover:bg-blue-800 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all flex items-center justify-center">
                                <i class="fas fa-search mr-2"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h5 class="font-bold text-slate-800 text-sm uppercase tracking-wider">
                    <i class="fas fa-folder-open mr-2 text-gray-400"></i> Resultados
                </h5>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">CI</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre Completo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Legajo</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Nro. Solicitud</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (!empty($resultados)): ?>
                            <?php foreach ($resultados as $resultado): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-3 font-bold text-gray-800 text-sm">
                                        <?php echo htmlspecialchars($formatearCi($resultado['ci_socio'] ?? '')); ?>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 text-sm">
                                        <?php echo htmlspecialchars($resultado['nombre_completo'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 text-sm">
                                        <?php echo htmlspecialchars($resultado['nombre_tipo_legajo'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600 text-sm">
                                        <?php echo htmlspecialchars($resultado['nro_solicitud'] ?? ''); ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm">
                                        <?php
                                        $estadoTexto = $resultado['estado_legajo_texto'] ?? ucfirst($resultado['estado'] ?? '');
                                        $claseEstado = 'bg-gray-100 text-gray-700';
                                        if ($estadoTexto === 'Cerrado') {
                                            $claseEstado = 'bg-slate-200 text-slate-800';
                                        } elseif ($estadoTexto === 'Verificación rechazada') {
                                            $claseEstado = 'bg-amber-100 text-amber-800';
                                        } elseif ($estadoTexto === 'Proceso') {
                                            $claseEstado = 'bg-indigo-100 text-indigo-800';
                                        } elseif ($estadoTexto === 'Generado') {
                                            $claseEstado = 'bg-sky-100 text-sky-800';
                                        } elseif ($estadoTexto === 'Completado') {
                                            $claseEstado = 'bg-green-100 text-green-800';
                                        } elseif ($estadoTexto === 'Verificado') {
                                            $claseEstado = 'bg-cyan-100 text-cyan-800';
                                        } elseif ($estadoTexto === 'Vencido') {
                                            $claseEstado = 'bg-red-100 text-red-800';
                                        } elseif ($estadoTexto === 'Incompleto') {
                                            $claseEstado = 'bg-red-100 text-red-800';
                                        }
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs font-bold rounded-full <?php echo $claseEstado; ?>">
                                            <?php echo htmlspecialchars($estadoTexto); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <?php $puedeVerPdfFinal = !empty($resultado['pdf_final_disponible']); ?>
                                        <?php $puedeDuplicar = !empty($resultado['requiere_nro_solicitud']); ?>
                                        <div class="flex justify-end gap-2">
                                            <?php if ($puedeVerPdfFinal): ?>
                                            <a href="<?php echo base_url(); ?>legajos/ver_pdf_final/<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                class="w-10 h-10 bg-red-700 text-white rounded-lg font-bold text-xs hover:bg-red-900 transition-all inline-flex items-center justify-center"
                                                title="Ver PDF"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="<?php echo base_url(); ?>legajos/armar_legajo?id_legajo=<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                class="w-10 h-10 bg-scantec-blue text-white rounded-lg font-bold text-xs hover:bg-blue-800 transition-all inline-flex items-center justify-center"
                                                title="Abrir">
                                                <i class="fas fa-folder-open"></i>
                                            </a>
                                            <?php if ($puedeDuplicar): ?>
                                            <a href="<?php echo base_url(); ?>legajos/armar_legajo?duplicar_desde=<?php echo intval($resultado['id_legajo'] ?? 0); ?>"
                                                class="w-10 h-10 bg-gray-700 text-white rounded-lg font-bold text-xs hover:bg-gray-900 transition-all inline-flex items-center justify-center"
                                                title="Duplicar">
                                                <i class="fas fa-copy"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                                    <?php
                                    if (!$busqueda_ejecutada) {
                                        echo 'Ingrese un dato básico, seleccione filtros o use *.* para listar todos.';
                                    } elseif ($termino !== '' || $estado_legajo !== '' || $id_tipo_legajo > 0) {
                                        echo 'No se encontraron legajos para la búsqueda realizada.';
                                    } else {
                                        echo 'No hay legajos para mostrar.';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php pie() ?>
