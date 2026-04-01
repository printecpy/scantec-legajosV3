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
$requisito_editar = $data['requisito_editar'] ?? null;
$relacion_editar = $data['relacion_editar'] ?? null;
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
                    <i class="fas fa-project-diagram mr-2"></i> 3. Matriz de Requisitos
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
                            <button type="submit" class="bg-scantec-blue text-white px-4 py-2 rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
                                <?php echo !empty($documento_editar) ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                        <div class="md:col-span-6 flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-col gap-1">
                                <label class="inline-flex items-center gap-2 font-medium text-gray-700">
                                    <input type="checkbox" name="activo" value="1" <?php echo !isset($documento_editar) || !empty($documento_editar['activo']) ? 'checked' : ''; ?>>
                                    Activo
                                </label>
                                <?php if (!empty($documento_editar) && empty($documento_editar['activo'])): ?>
                                <span class="text-xs font-medium text-amber-700">Al marcar esta opción, el documento vuelve a quedar activo.</span>
                                <?php endif; ?>
                            </div>
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
                            <?php $documentoActivo = !empty($documento['activo']); ?>
                            <tr class="<?php echo $documentoActivo ? 'hover:bg-gray-50' : 'bg-gray-50/70 opacity-60'; ?> transition-all">
                                <td class="px-6 py-4 text-sm font-bold <?php echo $documentoActivo ? 'text-gray-900' : 'text-gray-500'; ?>">
                                    <div class="flex items-center gap-2">
                                        <span><?php echo htmlspecialchars($documento['nombre']); ?></span>
                                        <?php if (!$documentoActivo): ?>
                                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-200 text-gray-600 uppercase tracking-wide">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm font-mono <?php echo $documentoActivo ? 'text-gray-500' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($documento['codigo_interno'] ?? '-'); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($documento['tiene_vencimiento'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded <?php echo $documentoActivo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-500'; ?>">SÍ</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm <?php echo $documentoActivo ? 'text-gray-700' : 'text-gray-400'; ?>">
                                    <?php echo !empty($documento['dias_vigencia_base']) ? intval($documento['dias_vigencia_base']) . ' días' : '-'; ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm <?php echo $documentoActivo ? 'text-gray-700' : 'text-gray-400'; ?>">
                                    <?php echo !empty($documento['dias_alerta_previa']) ? intval($documento['dias_alerta_previa']) . ' días' : '-'; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                    <a class="btn-action btn-action-primary" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=catalogo&editar_documento=<?php echo intval($documento['id_documento_maestro']); ?>" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_documento_legajo" class="inline-flex" onsubmit="return confirmarAccionDocumentoCatalogo(this);">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_documento_maestro" value="<?php echo intval($documento['id_documento_maestro']); ?>">
                                        <input type="hidden" name="accion_catalogo" value="desactivar">
                                        <button class="btn-action btn-action-danger" type="submit" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
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
                    <?php if (!empty($filtrar_tipos_por_departamento) && intval($id_departamento_actual ?? 0) > 0): ?>
                    <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-medium text-blue-800">
                        Se muestran solo los tipos de legajo del departamento asociado a este usuario.
                    </div>
                    <?php endif; ?>
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
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Requiere Nro de solicitud</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="requiere_nro_solicitud" value="1" <?php echo !empty($tipo_legajo_editar['requiere_nro_solicitud']) ? 'checked' : ''; ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 rounded-full transition-colors peer-checked:bg-scantec-blue after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-transform peer-checked:after:translate-x-full"></div>
                                <span class="ml-3 text-sm font-bold text-gray-700"><?php echo !empty($tipo_legajo_editar['requiere_nro_solicitud']) ? 'Si' : 'No'; ?></span>
                            </label>
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
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
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Requiere Nro. de Solicitud</th>
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
                            <?php $tipoActivo = !isset($tipo_documento['activo']) || !empty($tipo_documento['activo']); ?>
                            <tr class="<?php echo $tipoActivo ? 'hover:bg-gray-50' : 'bg-gray-50/70 opacity-60'; ?> transition-all">
                                <td class="px-6 py-4 text-sm font-bold <?php echo $tipoActivo ? 'text-gray-900' : 'text-gray-500'; ?>">
                                    <div class="flex items-center gap-2">
                                        <span><?php echo htmlspecialchars($tipo_documento['nombre_tipoDoc']); ?></span>
                                        <?php if (!$tipoActivo): ?>
                                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-200 text-gray-600 uppercase tracking-wide">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm <?php echo $tipoActivo ? 'text-gray-700' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($tipo_documento['descripcion'] ?? '-'); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($tipo_documento['requiere_nro_solicitud'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded <?php echo $tipoActivo ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-500'; ?>">SI</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php if ($tipoActivo): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 <?php echo $tipoActivo ? '' : 'opacity-75'; ?>">
                                    <a class="btn-action btn-action-primary" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=tipos&editar_tipo_legajo=<?php echo intval($tipo_documento['id_tipoDoc']); ?>" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_tipo_legajo" class="inline-flex" onsubmit="return confirmarAccionTipoLegajo(this);">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_tipo_legajo" value="<?php echo intval($tipo_documento['id_tipoDoc']); ?>">
                                        <input type="hidden" name="accion_tipo_legajo" value="desactivar">
                                        <button class="btn-action btn-action-danger" type="submit" title="Eliminar o desactivar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
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
                    <button class="px-6 py-2.5 bg-gray-800 text-white rounded-xl font-bold shadow-md hover:bg-black transition-all flex items-center justify-center" type="submit">
                        Cargar Matriz
                    </button>
                </form>
                    <button type="button" onclick="togglePanel('panel-nuevo-tipo-legajo')"
                    class="hidden px-3 py-2.5 bg-scantec-blue text-white rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
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
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descripción</label>
                        <input type="text" name="descripcion_tipo_legajo"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
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
                        class="bg-scantec-blue text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-md hover:bg-blue-800 transition-all">
                        <i class="fas fa-plus mr-1"></i> Agregar Regla
                    </button>
                </div>

                <?php
                $requisitoEditarValido = is_array($requisito_editar) && !empty($requisito_editar);
                $requisitoEditarId = intval($requisito_editar['id_requisito'] ?? 0);
                $requisitoEditarDocumento = intval($requisito_editar['id_documento_maestro'] ?? 0);
                $requisitoEditarRol = $requisito_editar['rol_vinculado'] ?? 'TITULAR';
                $requisitoEditarOrden = intval($requisito_editar['orden_visual'] ?? (count($matriz_requisitos) + 1));
                $requisitoEditarObligatorio = $requisitoEditarValido ? !empty($requisito_editar['es_obligatorio']) : true;
                $requisitoEditarActivo = $requisitoEditarValido ? !empty($requisito_editar['activo']) : true;
                $politicaReglaEditar = strtoupper(trim((string)($requisito_editar['politica_actualizacion'] ?? 'REEMPLAZAR')));
                ?>

                <div id="panel-nueva-regla" class="<?php echo $requisitoEditarValido ? '' : 'hidden '; ?>p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo $requisitoEditarValido ? base_url() . 'configuracion/actualizar_matriz_legajo' : base_url() . 'configuracion/guardar_matriz_legajo'; ?>" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="hidden" name="id_tipoDoc" value="<?php echo intval($id_tipoDoc_actual); ?>">
                        <?php if ($requisitoEditarValido): ?>
                        <input type="hidden" name="id_requisito" value="<?php echo $requisitoEditarId; ?>">
                        <?php endif; ?>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Orden</label>
                            <input type="number" name="orden_visual" min="1" value="<?php echo $requisitoEditarOrden; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Documento Maestro</label>
                            <select name="id_documento_maestro" required class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <option value="">Seleccione...</option>
                                <?php foreach ($catalogo_documentos as $documento): ?>
                                    <?php if (!empty($documento['activo']) || intval($documento['id_documento_maestro']) === $requisitoEditarDocumento): ?>
                                    <option value="<?php echo intval($documento['id_documento_maestro']); ?>" <?php echo intval($documento['id_documento_maestro']) === $requisitoEditarDocumento ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($documento['nombre']); ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rol</label>
                            <select name="rol_vinculado" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <?php foreach ($relaciones as $rel): ?>
                                <option value="<?php echo htmlspecialchars($rel['nombre']); ?>" <?php echo $requisitoEditarRol === $rel['nombre'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($rel['nombre']); ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($relaciones)): ?>
                                <option value="TITULAR">TITULAR</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Al cargar otro archivo</label>
                            <select name="politica_actualizacion" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <?php foreach ($politicas_actualizacion as $pol): ?>
                                <option value="<?php echo htmlspecialchars($pol['clave']); ?>" <?php echo $politicaReglaEditar === $pol['clave'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($pol['etiqueta']); ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($politicas_actualizacion)): ?>
                                <option value="REEMPLAZAR" selected>Solo reemplazar</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="md:col-span-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm pt-1">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="es_obligatorio" value="1" <?php echo $requisitoEditarObligatorio ? 'checked' : ''; ?>>
                                Obligatorio
                            </label>
                            <label class="inline-flex items-center gap-2 text-gray-700">
                                <input type="checkbox" name="activo" value="1" <?php echo $requisitoEditarActivo ? 'checked' : ''; ?>>
                                Activo
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
                                <?php echo $requisitoEditarValido ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                        <?php if ($requisitoEditarValido): ?>
                        <div class="md:col-span-6 text-right">
                            <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=<?php echo intval($id_tipoDoc_actual); ?>" class="text-sm text-gray-600 hover:text-gray-900 font-bold">Cancelar edición</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Orden</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Documento Maestro</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Relación</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Obligatorio</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Actualización</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($matriz_requisitos)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No hay reglas cargadas para este tipo.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($matriz_requisitos as $requisito): ?>
                            <?php $requisitoActivo = !isset($requisito['activo']) || !empty($requisito['activo']); ?>
                            <?php
                                $politicaActualizacion = strtoupper(trim((string)($requisito['politica_actualizacion'] ?? '')));
                                if ($politicaActualizacion === '') {
                                    $politicaActualizacion = !empty($requisito['permite_reemplazo']) ? 'REEMPLAZAR' : 'NO_PERMITIR';
                                }
                                $etiquetaPolitica = $politicaActualizacion;
                                foreach ($politicas_actualizacion as $pol) {
                                    if (($pol['clave'] ?? '') === $politicaActualizacion) {
                                        $etiquetaPolitica = $pol['etiqueta'];
                                        break;
                                    }
                                }
                            ?>
                            <tr class="<?php echo $requisitoActivo ? 'hover:bg-gray-50' : 'bg-gray-50/70 opacity-60'; ?> transition-all">
                                <td class="px-4 py-4 text-center text-sm font-bold <?php echo $requisitoActivo ? 'text-gray-700' : 'text-gray-400'; ?>"><?php echo intval($requisito['orden_visual']); ?></td>
                                <td class="px-6 py-4 text-sm font-bold <?php echo $requisitoActivo ? 'text-gray-900' : 'text-gray-500'; ?>">
                                    <div class="flex items-center gap-2">
                                        <span><?php echo htmlspecialchars($requisito['documento_nombre'] ?? ''); ?></span>
                                        <?php if (!$requisitoActivo): ?>
                                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-200 text-gray-600 uppercase tracking-wide">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm <?php echo $requisitoActivo ? 'text-gray-700' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($requisito['rol_vinculado'] ?? 'TITULAR'); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <?php if (!empty($requisito['es_obligatorio'])): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded <?php echo $requisitoActivo ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-500'; ?>">Sí</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">No</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-center text-sm <?php echo $requisitoActivo ? 'text-gray-700' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($etiquetaPolitica); ?></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 <?php echo $requisitoActivo ? '' : 'opacity-75'; ?>">
                                        <a class="btn-action btn-action-primary" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=<?php echo intval($id_tipoDoc_actual); ?>&editar_requisito=<?php echo intval($requisito['id_requisito']); ?>" title="Editar">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_matriz_legajo" class="inline-flex" onsubmit="return confirmarAccionMatrizLegajo(this);">
                                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                            <input type="hidden" name="id_tipoDoc" value="<?php echo intval($id_tipoDoc_actual); ?>">
                                            <input type="hidden" name="id_requisito" value="<?php echo intval($requisito['id_requisito']); ?>">
                                            <input type="hidden" name="accion_matriz" value="desactivar">
                                            <button class="btn-action btn-action-danger" type="submit" title="Eliminar o desactivar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

                <?php
                $relacionEditarValida = is_array($relacion_editar) && !empty($relacion_editar);
                $relacionEditarId = intval($relacion_editar['id_relacion'] ?? 0);
                $relacionEditarNombre = $relacion_editar['nombre'] ?? '';
                $relacionEditarOrden = intval($relacion_editar['orden'] ?? (count($todas_relaciones) + 1));
                $relacionEditarActiva = $relacionEditarValida ? !empty($relacion_editar['activo']) : true;
                ?>

                <div id="panel-nueva-relacion" class="<?php echo $relacionEditarValida ? '' : 'hidden '; ?>p-6 border-b border-gray-100 bg-gray-50">
                    <form method="POST" action="<?php echo $relacionEditarValida ? base_url() . 'configuracion/actualizar_relacion' : base_url() . 'configuracion/guardar_relacion'; ?>"
                        class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <?php if ($relacionEditarValida): ?>
                        <input type="hidden" name="id_relacion" value="<?php echo $relacionEditarId; ?>">
                        <?php endif; ?>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre de la relación</label>
                            <input type="text" name="nombre_relacion" required placeholder="Ej: GARANTE, AVAL, FIADOR..." value="<?php echo htmlspecialchars($relacionEditarNombre); ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none uppercase">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Orden</label>
                            <input type="number" name="orden_relacion" min="1" value="<?php echo $relacionEditarOrden; ?>"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none">
                        </div>
                        <div class="md:col-span-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm pt-1">
                            <label class="inline-flex items-center gap-2 text-gray-700">
                                <input type="checkbox" name="activo_relacion" value="1" <?php echo $relacionEditarActiva ? 'checked' : ''; ?>>
                                Activo
                            </label>
                        </div>
                        <div class="md:col-span-1">
                            <button type="submit" class="w-full bg-scantec-blue text-white px-4 py-2 rounded-xl font-bold shadow-md hover:bg-blue-800 transition-all">
                                <?php echo $relacionEditarValida ? 'Actualizar' : 'Guardar'; ?>
                            </button>
                        </div>
                        <?php if ($relacionEditarValida): ?>
                        <div class="md:col-span-6 text-right">
                            <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=datos" class="text-sm text-gray-600 hover:text-gray-900 font-bold">Cancelar edición</a>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="p-0 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-500 uppercase w-20">Orden</th>
                                <th class="px-6 py-2.5 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                                <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-500 uppercase w-28">Estado</th>
                                <th class="px-6 py-2.5 text-right text-xs font-bold text-gray-500 uppercase w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php if (empty($todas_relaciones)): ?>
                            <tr class="hover:bg-gray-50">
                                <td colspan="4" class="px-6 py-3 text-sm text-gray-500 text-center">No hay relaciones configuradas.</td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($todas_relaciones as $relacion): ?>
                            <?php $relacionActiva = !empty($relacion['activo']); ?>
                            <tr class="<?php echo $relacionActiva ? 'hover:bg-gray-50' : 'bg-gray-50/70 opacity-60'; ?> transition-all">
                                <td class="px-4 py-2.5 text-center text-sm font-bold <?php echo $relacionActiva ? 'text-gray-700' : 'text-gray-400'; ?>">
                                    <?php echo intval($relacion['orden']); ?>
                                </td>
                                <td class="px-6 py-2.5 text-sm font-bold <?php echo $relacionActiva ? 'text-gray-900' : 'text-gray-500'; ?>">
                                    <div class="flex items-center gap-2">
                                        <span><?php echo htmlspecialchars($relacion['nombre']); ?></span>
                                        <?php if (!$relacionActiva): ?>
                                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-200 text-gray-600 uppercase tracking-wide">Inactivo</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <?php if ($relacionActiva): ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800">ACTIVO</span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-600">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-2.5 text-right">
                                    <div class="flex justify-end gap-2 <?php echo $relacionActiva ? '' : 'opacity-75'; ?>">
                                    <a class="btn-action btn-action-primary" href="<?php echo base_url(); ?>configuracion/configuracion_legajos?tab=datos&editar_relacion=<?php echo intval($relacion['id_relacion']); ?>" title="Editar">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/eliminar_relacion" class="inline-flex" onsubmit="return confirmarAccionRelacion(this);">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_relacion" value="<?php echo intval($relacion['id_relacion']); ?>">
                                        <input type="hidden" name="accion_relacion" value="desactivar">
                                        <button class="btn-action btn-action-danger" type="submit" title="Eliminar o desactivar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
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
                                    <form method="POST" action="<?php echo base_url(); ?>configuracion/cambiar_estado_politica" class="inline-flex">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                        <input type="hidden" name="id_politica" value="<?php echo intval($politica['id_politica']); ?>">
                                        <input type="hidden" name="activo" value="<?php echo !empty($politica['activo']) ? 0 : 1; ?>">
                                        <button class="<?php echo !empty($politica['activo']) ? 'btn-action btn-action-warning' : 'btn-action btn-action-success'; ?>" type="submit" title="<?php echo !empty($politica['activo']) ? 'Desactivar' : 'Activar'; ?>">
                                            <i class="fas <?php echo !empty($politica['activo']) ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
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

    function confirmarAccionDocumentoCatalogo(formElement) {
        Swal.fire({
            title: 'Documento universal',
            text: 'Puedes desactivarlo para conservar la integridad del sistema o eliminarlo definitivamente si ya no se usa.',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#b91c1c',
            denyButtonColor: '#b45309',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar definitivamente',
            denyButtonText: 'Desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                const inputAccion = formElement.querySelector('input[name=\"accion_catalogo\"]');
                if (inputAccion) {
                    inputAccion.value = result.isConfirmed ? 'eliminar' : 'desactivar';
                }
                formElement.submit();
            }
        });
        return false;
    }

    function confirmarAccionTipoLegajo(formElement) {
        Swal.fire({
            title: 'Tipo de legajo',
            text: 'Puedes desactivarlo para conservar la integridad del sistema o eliminarlo definitivamente si ya no se usa.',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#b91c1c',
            denyButtonColor: '#b45309',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar definitivamente',
            denyButtonText: 'Desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                const inputAccion = formElement.querySelector('input[name=\"accion_tipo_legajo\"]');
                if (inputAccion) {
                    inputAccion.value = result.isConfirmed ? 'eliminar' : 'desactivar';
                }
                formElement.submit();
            }
        });
        return false;
    }

    function confirmarAccionMatrizLegajo(formElement) {
        Swal.fire({
            title: 'Regla de matriz',
            text: 'Puedes desactivarla para conservar la integridad del sistema o eliminarla definitivamente si ya no se usa.',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#b91c1c',
            denyButtonColor: '#b45309',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar definitivamente',
            denyButtonText: 'Desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                const inputAccion = formElement.querySelector('input[name=\"accion_matriz\"]');
                if (inputAccion) {
                    inputAccion.value = result.isConfirmed ? 'eliminar' : 'desactivar';
                }
                formElement.submit();
            }
        });
        return false;
    }

    function confirmarAccionRelacion(formElement) {
        Swal.fire({
            title: 'Tipo de relación',
            text: 'Puedes desactivarla para conservar la integridad del sistema o eliminarla definitivamente si ya no se usa.',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonColor: '#b91c1c',
            denyButtonColor: '#b45309',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Eliminar definitivamente',
            denyButtonText: 'Desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed || result.isDenied) {
                const inputAccion = formElement.querySelector('input[name=\"accion_relacion\"]');
                if (inputAccion) {
                    inputAccion.value = result.isConfirmed ? 'eliminar' : 'desactivar';
                }
                formElement.submit();
            }
        });
        return false;
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








