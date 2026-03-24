<?php encabezado() ?>

<?php
$catalogo_documentos = $data['catalogo_documentos'] ?? [];
$tipos_documento = $data['tipos_documento'] ?? [];
$matriz_requisitos = $data['matriz_requisitos'] ?? [];
$id_tipoDoc_actual = $data['id_tipoDoc_actual'] ?? 0;
$tipo_documento_actual = $data['tipo_documento_actual'] ?? null;
$tab_actual = $data['tab_actual'] ?? 'catalogo';
$documento_editar = $data['documento_editar'] ?? null;
$tipo_legajo_editar = $data['tipo_legajo_editar'] ?? null;
$relaciones = $data['relaciones'] ?? [];
$politicas_actualizacion = $data['politicas_actualizacion'] ?? [];
$todas_relaciones = $data['todas_relaciones'] ?? [];
$todas_politicas = $data['todas_politicas'] ?? [];
?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-cogs mr-3"></i> Configuración del Motor de Legajos
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">
                    Administre el catálogo global de documentos y las reglas de los checklists.
                </p>
            </div>
        </div>

        <div class="border-b border-gray-200 mb-6 overflow-x-auto">
            <nav class="flex space-x-6 min-w-max" aria-label="Tabs">
                <button onclick="cambiarPestana('catalogo')" id="tab-catalogo" class="border-scantec-blue text-scantec-blue border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-book mr-2"></i> 1. Catálogo Maestro de Documentos
                </button>
                <button onclick="cambiarPestana('tipos')" id="tab-tipos" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-folder-tree mr-2"></i> 2. Tipos de Legajos
                </button>
                <button onclick="cambiarPestana('matriz')" id="tab-matriz" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-project-diagram mr-2"></i> 3. Matriz de Requisitos (Checklists)
                </button>
                <button onclick="cambiarPestana('datos')" id="tab-datos" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors">
                    <i class="fas fa-database mr-2"></i> 4. Datos generales
                </button>
            </nav>
        </div>

        <div id="seccion-catalogo" class="<?php echo $tab_actual === 'catalogo' ? 'block' : 'hidden'; ?> animate-fade-in-down">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        Listado de Documentos Universales
                    </h5>
                    <button type="button" onclick="togglePanel('panel-nuevo-documento')"
                        class="bg-white text-scantec-blue px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-gray-100">
                        <i class="fas fa-plus mr-1"></i> Nuevo Documento
                    </button>
                </div>

                <div id="panel-nuevo-documento" class="<?php echo !empty($documento_editar) ? '' : 'hidden '; ?>p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo !empty($documento_editar) ? base_url() . 'configuracion/actualizar_catalogo_legajo' : base_url() . 'configuracion/guardar_catalogo_legajo'; ?>"
                        class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <?php if (!empty($documento_editar)): ?>
                        <input type="hidden" name="id_documento_maestro" value="<?php echo intval($documento_editar['id_documento_maestro']); ?>">
                        <?php endif; ?>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre</label>
                            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($documento_editar['nombre'] ?? ''); ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Código</label>
                            <input type="text" name="codigo_interno" value="<?php echo htmlspecialchars($documento_editar['codigo_interno'] ?? ''); ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">¿Vence?</label>
                            <select name="tiene_vencimiento" onchange="toggleVencimientoFields(this)"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <option value="1" <?php echo !isset($documento_editar) || !empty($documento_editar['tiene_vencimiento']) ? 'selected' : ''; ?>>Sí</option>
                                <option value="0" <?php echo isset($documento_editar) && empty($documento_editar['tiene_vencimiento']) ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Días Vigencia</label>
                            <input type="number" name="dias_vigencia_base" min="1" value="<?php echo htmlspecialchars($documento_editar['dias_vigencia_base'] ?? ''); ?>"
                                <?php echo isset($documento_editar) && empty($documento_editar['tiene_vencimiento']) ? 'disabled' : ''; ?>
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Aviso Previo</label>
                            <input type="number" name="dias_alerta_previa" min="1" value="<?php echo htmlspecialchars($documento_editar['dias_alerta_previa'] ?? 30); ?>"
                                <?php echo isset($documento_editar) && empty($documento_editar['tiene_vencimiento']) ? 'disabled' : ''; ?>
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div class="flex flex-col justify-end gap-2">
                            <button type="submit" class="bg-scantec-blue text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-800">
                                <?php echo !empty($documento_editar) ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                        <div class="md:col-span-6 flex gap-6 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="activo" value="1" <?php echo !isset($documento_editar) || !empty($documento_editar['activo']) ? 'checked' : ''; ?>>
                                Activo
                            </label>
                            <?php if (!empty($documento_editar)): ?>
                            <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=catalogo" class="text-sm text-gray-600 hover:text-gray-900 font-bold">
                                Cancelar edición
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre del Documento</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Código</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">¿Vence?</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Días Vigencia</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Aviso Previo</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($catalogo_documentos)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No hay documentos configurados.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($catalogo_documentos as $documento): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900"><?php echo htmlspecialchars($documento['nombre']); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($documento['codigo_interno'] ?? '-'); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($documento['tiene_vencimiento'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">SÍ</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm text-gray-700">
                                    <?php echo !empty($documento['dias_vigencia_base']) ? intval($documento['dias_vigencia_base']) . ' días' : '-'; ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm text-gray-700">
                                    <?php echo !empty($documento['dias_alerta_previa']) ? intval($documento['dias_alerta_previa']) . ' días' : '-'; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="text-blue-600 hover:text-blue-900 mx-2" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=catalogo&editar_documento=<?php echo intval($documento['id_documento_maestro']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/cambiar_estado_catalogo_legajo" class="inline">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_documento_maestro" value="<?php echo intval($documento['id_documento_maestro']); ?>">
                                        <input type="hidden" name="activo" value="<?php echo !empty($documento['activo']) ? 0 : 1; ?>">
                                        <button class="text-red-500 hover:text-red-700 mx-2" type="submit">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="seccion-tipos" class="<?php echo $tab_actual === 'tipos' ? 'block' : 'hidden'; ?> animate-fade-in-down">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        Listado de Tipos de Legajos
                    </h5>
                    <button type="button" onclick="togglePanel('panel-tipo-legajo')"
                        class="bg-white text-scantec-blue px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-gray-100">
                        <i class="fas fa-plus mr-1"></i> Nuevo Tipo
                    </button>
                </div>

                <div id="panel-tipo-legajo" class="<?php echo !empty($tipo_legajo_editar) ? '' : 'hidden '; ?>p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo !empty($tipo_legajo_editar) ? base_url() . 'configuracion/actualizar_tipo_legajo' : base_url() . 'configuracion/guardar_tipo_legajo'; ?>"
                        class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <?php if (!empty($tipo_legajo_editar)): ?>
                        <input type="hidden" name="id_tipo_legajo" value="<?php echo intval($tipo_legajo_editar['id_tipo_legajo']); ?>">
                        <?php endif; ?>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre del tipo de legajo</label>
                            <input type="text" name="nombre_tipo_legajo" required value="<?php echo htmlspecialchars($tipo_legajo_editar['nombre'] ?? ''); ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descripción</label>
                            <input type="text" name="descripcion_tipo_legajo" value="<?php echo htmlspecialchars($tipo_legajo_editar['descripcion'] ?? ''); ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Requiere Nro solicitud</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="requiere_nro_solicitud" value="1" <?php echo !empty($tipo_legajo_editar['requiere_nro_solicitud']) ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full transition-colors peer-checked:bg-scantec-blue after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-full"></div>
                                <span class="ml-3 text-sm font-bold text-gray-700"><?php echo !empty($tipo_legajo_editar['requiere_nro_solicitud']) ? 'Si' : 'No'; ?></span>
                            </label>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-800">
                                <?php echo !empty($tipo_legajo_editar) ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                        <div class="md:col-span-5 flex gap-6 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="activo_tipo_legajo" value="1" <?php echo !isset($tipo_legajo_editar) || !empty($tipo_legajo_editar['activo']) ? 'checked' : ''; ?>>
                                Activo
                            </label>
                            <?php if (!empty($tipo_legajo_editar)): ?>
                            <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=tipos" class="text-sm text-gray-600 hover:text-gray-900 font-bold">
                                Cancelar edición
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Descripción</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Requiere Nro. Solicitud</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($tipos_documento)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No hay tipos de legajo configurados.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($tipos_documento as $tipo_documento): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-bold text-gray-900"><?php echo htmlspecialchars($tipo_documento['nombre_tipoDoc']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($tipo_documento['descripcion'] ?? '-'); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($tipo_documento['requiere_nro_solicitud'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-blue-100 text-blue-800">SI</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!isset($tipo_documento['activo']) || !empty($tipo_documento['activo'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a class="text-blue-600 hover:text-blue-900 mx-2" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=tipos&editar_tipo_legajo=<?php echo intval($tipo_documento['id_tipoDoc']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_tipo_legajo" class="inline">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_tipo_legajo" value="<?php echo intval($tipo_documento['id_tipoDoc']); ?>">
                                        <button class="text-red-500 hover:text-red-700 mx-2" type="submit">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="seccion-matriz" class="<?php echo $tab_actual === 'matriz' ? 'block' : 'hidden'; ?> animate-fade-in-down">

            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 flex items-end gap-4">
                <form method="GET" action="<?php echo base_url(); ?>configuracion/configuracion_legajos" class="flex-1 flex items-end gap-4">
                    <input type="hidden" name="tab" value="matriz">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Seleccione el Tipo de Legajo a Configurar</label>
                        <select name="id_tipoDoc" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none font-bold text-gray-700 cursor-pointer shadow-sm">
                            <option value="">Seleccione un tipo de expediente...</option>
                            <?php foreach ($tipos_documento as $tipo_documento): ?>
                            <option value="<?php echo intval($tipo_documento['id_tipoDoc']); ?>" <?php echo intval($tipo_documento['id_tipoDoc']) === intval($id_tipoDoc_actual) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo_documento['nombre_tipoDoc']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="px-6 py-2.5 bg-gray-800 text-white rounded-lg font-bold shadow-sm hover:bg-black transition-all" type="submit">
                        Cargar Matriz
                    </button>
                </form>
                <button type="button" onclick="togglePanel('panel-nuevo-tipo-legajo')"
                    class="hidden px-3 py-2.5 bg-scantec-blue text-white rounded-lg font-bold shadow-sm hover:bg-blue-800 transition-all">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div id="panel-nuevo-tipo-legajo" class="hidden bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6">
                <form method="POST" action="<?php echo base_url(); ?>configuracion/guardar_tipo_legajo" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre del tipo de legajo</label>
                        <input type="text" name="nombre_tipo_legajo" required
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">DescripciÃ³n</label>
                        <input type="text" name="descripcion_tipo_legajo"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-800">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        <i class="fas fa-list-check mr-2 text-yellow-500"></i> Reglas para: <?php echo htmlspecialchars($tipo_documento_actual['nombre_tipoDoc'] ?? 'Sin selección'); ?>
                    </h5>
                    <button type="button" onclick="togglePanel('panel-nueva-regla')"
                        class="bg-scantec-blue text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-blue-600">
                        <i class="fas fa-plus mr-1"></i> Agregar Regla
                    </button>
                </div>

                <div id="panel-nueva-regla" class="hidden p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo base_url(); ?>configuracion/guardar_matriz_legajo" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="id_tipoDoc" value="<?php echo intval($id_tipoDoc_actual); ?>">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Documento Maestro</label>
                            <select name="id_documento_maestro" required class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogo_documentos as $documento): ?>
                                    <?php if (!empty($documento['activo'])): ?>
                                    <option value="<?php echo intval($documento['id_documento_maestro']); ?>">
                                        <?php echo htmlspecialchars($documento['nombre']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rol</label>
                            <select name="rol_vinculado" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <?php foreach ($relaciones as $rel): ?>
                                <option value="<?php echo htmlspecialchars($rel['nombre']); ?>"><?php echo htmlspecialchars($rel['nombre']); ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($relaciones)): ?>
                                <option value="TITULAR">TITULAR</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Orden</label>
                            <input type="number" name="orden_visual" min="1" value="<?php echo count($matriz_requisitos) + 1; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div class="text-sm space-y-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="es_obligatorio" value="1" checked>
                                Obligatorio
                            </label>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Al cargar otro archivo</label>
                            <select name="politica_actualizacion" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <?php foreach ($politicas_actualizacion as $pol): ?>
                                <option value="<?php echo htmlspecialchars($pol['clave']); ?>"><?php echo htmlspecialchars($pol['etiqueta']); ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($politicas_actualizacion)): ?>
                                <option value="REEMPLAZAR" selected>Solo reemplazar</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-800">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>

                <form method="POST" action="<?php echo base_url(); ?>configuracion/guardar_cambios_matriz_legajo">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="id_tipoDoc" value="<?php echo intval($id_tipoDoc_actual); ?>">
                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento Maestro</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Relación</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Obligatorio</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Actualización</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($matriz_requisitos)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No hay reglas cargadas para este tipo.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($matriz_requisitos as $requisito): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-center">
                                    <input type="number" name="reglas[<?php echo intval($requisito['id_requisito']); ?>][orden_visual]" value="<?php echo intval($requisito['orden_visual']); ?>" class="w-16 px-2 py-1 text-center border rounded text-sm font-bold">
                                </td>
                                <td class="px-6 py-4">
                                    <select name="reglas[<?php echo intval($requisito['id_requisito']); ?>][id_documento_maestro]" class="w-full px-2 py-1.5 border rounded text-sm text-gray-700 font-bold">
                                        <?php foreach ($catalogo_documentos as $documento): ?>
                                        <option value="<?php echo intval($documento['id_documento_maestro']); ?>" <?php echo intval($documento['id_documento_maestro']) === intval($requisito['id_documento_maestro']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($documento['nombre']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-4">
                                    <select name="reglas[<?php echo intval($requisito['id_requisito']); ?>][rol_vinculado]" class="w-full px-2 py-1.5 border rounded text-sm text-gray-700">
                                        <?php foreach ($relaciones as $rel): ?>
                                        <option value="<?php echo htmlspecialchars($rel['nombre']); ?>" <?php echo $requisito['rol_vinculado'] === $rel['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($rel['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <input type="hidden" name="reglas[<?php echo intval($requisito['id_requisito']); ?>][es_obligatorio]" value="0">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="reglas[<?php echo intval($requisito['id_requisito']); ?>][es_obligatorio]" value="1" <?php echo !empty($requisito['es_obligatorio']) ? 'checked' : ''; ?> class="sr-only peer">
                                        <div class="w-9 h-5 bg-gray-200 rounded-full transition-colors peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-transform peer-checked:after:translate-x-full"></div>
                                    </label>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php
                                    $politicaActualizacion = strtoupper(trim((string)($requisito['politica_actualizacion'] ?? '')));
                                    if ($politicaActualizacion === '') {
                                        $politicaActualizacion = !empty($requisito['permite_reemplazo']) ? 'REEMPLAZAR' : 'NO_PERMITIR';
                                    }
                                    ?>
                                    <select name="reglas[<?php echo intval($requisito['id_requisito']); ?>][politica_actualizacion]" class="w-full px-2 py-1.5 border rounded text-sm text-gray-700">
                                        <?php foreach ($politicas_actualizacion as $pol): ?>
                                        <option value="<?php echo htmlspecialchars($pol['clave']); ?>" <?php echo $politicaActualizacion === $pol['clave'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pol['etiqueta']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button class="text-red-500 hover:text-red-700 font-bold text-xs" type="submit"
                                        formaction="<?php echo base_url(); ?>configuracion/eliminar_matriz_legajo"
                                        formmethod="POST"
                                        name="id_requisito"
                                        value="<?php echo intval($requisito['id_requisito']); ?>">
                                        <i class="fas fa-times-circle mr-1"></i> Quitar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                        <button class="bg-scantec-blue text-white px-6 py-2.5 rounded-lg font-bold shadow-lg hover:bg-blue-800 transition-colors" type="submit">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios de Matriz
                        </button>
                    </div>

                </div>
                </form>
            </div>
        </div>

        <div id="seccion-datos" class="<?php echo $tab_actual === 'datos' ? 'block' : 'hidden'; ?> animate-fade-in-down">
            <!-- ============================================ -->
            <!-- SECCIÓN: TIPOS DE RELACIÓN                   -->
            <!-- ============================================ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="bg-scantec-blue px-6 py-4 border-b border-blue-800 flex justify-between items-center">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        <i class="fas fa-users mr-2"></i> Tipos de Relación
                    </h5>
                    <button type="button" onclick="togglePanel('panel-nueva-relacion')"
                        class="bg-white text-scantec-blue px-3 py-1.5 rounded-lg text-xs font-bold shadow hover:bg-gray-100 transition-colors">
                        <i class="fas fa-plus mr-1"></i> Nueva Relación
                    </button>
                </div>

                <div id="panel-nueva-relacion" class="hidden p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo base_url(); ?>configuracion/guardar_relacion"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre de la relación</label>
                            <input type="text" name="nombre_relacion" required placeholder="Ej: GARANTE, AVAL, FIADOR..."
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none uppercase">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Orden</label>
                            <input type="number" name="orden_relacion" min="1" value="<?php echo count($todas_relaciones) + 1; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-blue-800 transition-colors">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-20">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-28">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($todas_relaciones)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="4" class="px-6 py-4 text-sm text-gray-500 text-center">No hay relaciones configuradas.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($todas_relaciones as $relacion): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-center text-sm font-bold text-gray-700">
                                    <?php echo intval($relacion['orden']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    <?php echo htmlspecialchars($relacion['nombre']); ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($relacion['activo'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/cambiar_estado_relacion" class="inline">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_relacion" value="<?php echo intval($relacion['id_relacion']); ?>">
                                        <input type="hidden" name="activo" value="<?php echo !empty($relacion['activo']) ? 0 : 1; ?>">
                                        <button class="<?php echo !empty($relacion['activo']) ? 'text-green-600 hover:text-green-800' : 'text-red-500 hover:text-red-700'; ?> mx-1" type="submit" title="<?php echo !empty($relacion['activo']) ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas <?php echo !empty($relacion['activo']) ? 'fa-toggle-on' : 'fa-toggle-off'; ?> fa-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_relacion" class="inline">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_relacion" value="<?php echo intval($relacion['id_relacion']); ?>">
                                        <button class="text-red-500 hover:text-red-700 mx-1" type="submit" title="Eliminar">
                                            <i class="fas fa-trash fa-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- SECCIÓN: MÉTODOS DE ACTUALIZACIÓN            -->
            <!-- ============================================ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-800 px-6 py-4 border-b border-gray-700">
                    <h5 class="font-bold text-white flex items-center text-sm uppercase tracking-wide">
                        <i class="fas fa-sync-alt mr-2 text-yellow-400"></i> Métodos de Actualización de Archivos
                    </h5>
                    <p class="text-xs text-gray-400 mt-1">Active o desactive los métodos disponibles al cargar un archivo sobre uno ya existente.</p>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-20">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Descripción</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-28">Estado</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase w-24">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($todas_politicas)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No hay métodos configurados.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($todas_politicas as $politica): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-center text-sm font-bold text-gray-700">
                                    <?php echo intval($politica['orden']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                    <?php echo htmlspecialchars($politica['etiqueta']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($politica['descripcion'] ?? ''); ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($politica['activo'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/cambiar_estado_politica" class="inline">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_politica" value="<?php echo intval($politica['id_politica']); ?>">
                                        <input type="hidden" name="activo" value="<?php echo !empty($politica['activo']) ? 0 : 1; ?>">
                                        <button class="<?php echo !empty($politica['activo']) ? 'text-green-600 hover:text-green-800' : 'text-red-500 hover:text-red-700'; ?>" type="submit" title="<?php echo !empty($politica['activo']) ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas <?php echo !empty($politica['activo']) ? 'fa-toggle-on' : 'fa-toggle-off'; ?> fa-lg"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                Estos catálogos alimentan los selectores de la matriz de requisitos. Solo las opciones <strong>activas</strong> aparecerán disponibles al crear o editar reglas.
            </div>

        </div>

    </div>
</main>

<script>
    function cambiarPestana(id) {
        document.getElementById('seccion-catalogo').classList.add('hidden');
        document.getElementById('seccion-tipos').classList.add('hidden');
        document.getElementById('seccion-matriz').classList.add('hidden');
        document.getElementById('seccion-datos').classList.add('hidden');
        
        document.getElementById('tab-catalogo').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
        document.getElementById('tab-tipos').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
        document.getElementById('tab-matriz').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
        document.getElementById('tab-datos').className = "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";

        document.getElementById('seccion-' + id).classList.remove('hidden');
        document.getElementById('tab-' + id).className = "border-scantec-blue text-scantec-blue border-b-2 py-4 px-1 font-bold text-sm flex items-center transition-colors";
    }

    function togglePanel(id) {
        const panel = document.getElementById(id);
        if (panel) {
            panel.classList.toggle('hidden');
        }
    }

    function toggleVencimientoFields(selectElement) {
        const form = selectElement.closest('form');
        if (!form) {
            return;
        }

        const inputDias = form.querySelector('input[name="dias_vigencia_base"]');
        const inputAviso = form.querySelector('input[name="dias_alerta_previa"]');
        if (!inputDias || !inputAviso) {
            return;
        }

        if (selectElement.value === '0') {
            inputDias.value = '';
            inputDias.disabled = true;
            inputAviso.value = '';
            inputAviso.disabled = true;
        } else {
            inputDias.disabled = false;
            inputAviso.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        cambiarPestana('<?php echo in_array($tab_actual, ['catalogo', 'tipos', 'matriz', 'datos'], true) ? $tab_actual : 'catalogo'; ?>');
        document.querySelectorAll('select[name="tiene_vencimiento"]').forEach(function(selectElement) {
            toggleVencimientoFields(selectElement);
        });
    });
</script>

<style>
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.25s ease-out forwards;
    }
</style>

<?php pie() ?>
