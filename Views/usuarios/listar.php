<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-users mr-3"></i> Gestión de Usuarios
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">Administración de accesos y roles del sistema.</p>
            </div>
            
            <button onclick="toggleModal('modalNuevoUsuario')" 
                class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-2 px-6 rounded-xl shadow-lg transition-all transform hover:scale-105 flex items-center">
                <i class="fas fa-user-plus mr-2"></i> Nuevo Usuario
            </button>
        </div>

        <?php
            $totalUsuarios = is_array($data['usuario'] ?? null) ? count($data['usuario']) : 0;
            $totalPendientes = 0;
            $totalActivos = 0;
            $totalInactivos = 0;
            $importPreview = $_SESSION['usuarios_import_preview'] ?? null;
            $importUsuarios = is_array($importPreview['usuarios'] ?? null) ? $importPreview['usuarios'] : [];
            $importErrores = is_array($importPreview['errores'] ?? null) ? $importPreview['errores'] : [];
            $importArchivo = (string)($importPreview['archivo'] ?? '');
            foreach (($data['usuario'] ?? []) as $usuarioResumen) {
                $estadoResumen = strtoupper((string)($usuarioResumen['estado_usuario'] ?? ''));
                $esActivoResumen = ($estadoResumen === 'ACTIVO');
                $esPendienteResumen = $estadoResumen === 'PENDIENTE' || (!$esActivoResumen && strtolower((string)($usuarioResumen['fuente_registro'] ?? '')) === 'scantec');
                if ($esActivoResumen) {
                    $totalActivos++;
                } elseif ($esPendienteResumen) {
                    $totalPendientes++;
                } else {
                    $totalInactivos++;
                }
            }
        ?>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Exportar:</span>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/excel" 
                       class="flex items-center px-3 py-1.5 border border-green-200 text-green-600 rounded-lg hover:bg-green-50 transition-colors text-sm font-bold">
                       <i class="fas fa-file-excel mr-2"></i> Excel
                    </a>
                </div>
                
                <button type="button" onclick="toggleModal('modalImportarUsuarios', true)"
                    class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-200 bg-white text-scantec-blue font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-file-import mr-2"></i> Importar
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Filtro rápido</p>
                    <div class="flex flex-wrap gap-2" id="filtrosUsuarios">
                        <button type="button" data-filter="todos" class="btn-filtro-usuarios px-4 py-2 rounded-xl bg-scantec-blue text-white text-xs font-bold uppercase tracking-wider shadow-sm">
                            Todos (<?php echo $totalUsuarios; ?>)
                        </button>
                        <button type="button" data-filter="pendiente" class="btn-filtro-usuarios px-4 py-2 rounded-xl border border-amber-200 text-amber-700 bg-amber-50 text-xs font-bold uppercase tracking-wider">
                            Pendientes (<?php echo $totalPendientes; ?>)
                        </button>
                        <button type="button" data-filter="activo" class="btn-filtro-usuarios px-4 py-2 rounded-xl border border-green-200 text-green-700 bg-green-50 text-xs font-bold uppercase tracking-wider">
                            Activos (<?php echo $totalActivos; ?>)
                        </button>
                        <button type="button" data-filter="inactivo" class="btn-filtro-usuarios px-4 py-2 rounded-xl border border-red-200 text-red-700 bg-red-50 text-xs font-bold uppercase tracking-wider">
                            Inactivos (<?php echo $totalInactivos; ?>)
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span id="seleccionResumen" class="text-xs font-bold uppercase tracking-wider text-gray-400">0 seleccionados</span>
                    <button type="button" id="btnActivarSeleccionados" class="px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-bold uppercase tracking-wider shadow-sm hover:bg-green-700 transition-all disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                        Activar seleccionados
                    </button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <form id="formActivacionMasiva" action="<?php echo base_url(); ?>Usuarios/reingresar_masivo" method="post">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <table class="scantec-table" id="table">
                <thead>
                    <tr>
                        <th class="text-center w-12">
                            <input type="checkbox" id="seleccionarTodos" class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue">
                        </th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['usuario'] as $usuario) { 
                        // Lógica PHP original intacta
                        $nombreRol = '---';
                        $rolesCatalogo = $data['roles_catalogo'] ?? ($data['roles'] ?? []);
                        foreach ($rolesCatalogo as $rol) { if ($rol['id_rol'] == $usuario['id_rol']) { $nombreRol = $rol['descripcion']; break; } }
                        $estadoUsuario = strtoupper((string)($usuario['estado_usuario'] ?? ''));
                        $estadoActivo = ($estadoUsuario === 'ACTIVO');
                        $registroPendiente = $estadoUsuario === 'PENDIENTE' || (!$estadoActivo && strtolower((string)($usuario['fuente_registro'] ?? '')) === 'scantec');
                        ?>
                    <tr data-estado="<?php echo $registroPendiente ? 'pendiente' : ($estadoActivo ? 'activo' : 'inactivo'); ?>">
                        <td class="text-center">
                            <?php if (!$estadoActivo): ?>
                                <input type="checkbox" name="usuarios_ids[]" value="<?php echo intval($usuario['id']); ?>" class="selector-usuario rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue">
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="flex items-center">                                
                                <div>
                                    <span class="block font-bold text-gray-800"><?php echo $usuario['nombre']; ?></span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="font-mono text-xs"><?php echo $usuario['usuario']; ?></td>
                        
                        <td><span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs border border-blue-100 font-bold"><?php echo $nombreRol; ?></span></td>

                        <td class="text-center">
                            <?php if ($estadoActivo): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">Activo</span>
                            <?php elseif ($registroPendiente): ?>
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold border border-amber-200">Pendiente</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <div class="flex justify-end gap-2 min-w-[88px]">
                                <?php if ($estadoActivo): ?>
                                    <a href="<?php echo base_url() ?>usuarios/detalle?id=<?php echo $usuario['id']; ?>" 
                                       class="btn-action btn-action-primary"
                                       title="Detalle">
                                       <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="<?php echo base_url() ?>Usuarios/eliminar" method="post" class="inline-flex items-center eliminar">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn-action btn-action-danger" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="<?php echo base_url() ?>usuarios/detalle?id=<?php echo $usuario['id']; ?>" 
                                       class="btn-action btn-action-primary"
                                       title="Detalle">
                                       <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="<?php echo base_url() ?>Usuarios/eliminar" method="post" class="inline-flex items-center eliminar">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="btn-action btn-action-danger" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </form>
        </div>
    </div>
</main>

<div id="modalImportarUsuarios" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-importar-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-5xl border border-gray-100">
            <div class="bg-scantec-blue px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold leading-6 text-white tracking-wide" id="modal-importar-title">
                    <i class="fas fa-file-import mr-2"></i> Importar usuarios
                </h3>
                <button type="button"
                    onclick="<?php echo $importPreview ? "window.location.href='" . base_url() . "usuarios/cancelar_importacion'" : "toggleModal('modalImportarUsuarios', false)"; ?>"
                    class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="px-6 py-6 bg-gray-50 space-y-6">
                <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Paso 1</p>
                            <h4 class="text-lg font-bold text-scantec-blue">Descargar plantilla y cargar archivo</h4>
                            <p class="text-sm text-gray-500 mt-1">Descargue la plantilla de Excel, complete los datos y luego súbala para revisar la importación. El departamento se puede cargar por nombre. Formato: <span class="font-semibold text-gray-700">nombre, departamento, usuario, clave, rol, email</span>.</p>
                        </div>
                        <a href="<?php echo base_url(); ?>usuarios/usuario_muestra"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-blue-200 text-blue-700 bg-blue-50 font-bold text-sm hover:bg-blue-100 transition-all">
                            <i class="fas fa-download mr-2"></i> Descargar plantilla Excel
                        </a>
                    </div>

                    <form action="<?php echo base_url(); ?>usuarios/importar" method="POST" enctype="multipart/form-data" class="mt-5">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-4 items-end">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Archivo de importación</label>
                                <label for="fileInputImport" class="flex items-center justify-between gap-3 w-full px-4 py-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 cursor-pointer hover:border-scantec-blue hover:bg-blue-50 transition-all">
                                    <span id="fileNameImport" class="text-sm text-gray-500 truncate">Seleccione un archivo CSV, XLS o XLSX</span>
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-scantec-blue text-xs font-bold">
                                        <i class="fas fa-paperclip mr-2"></i> Seleccionar
                                    </span>
                                </label>
                                <input type="file" name="file" class="hidden" id="fileInputImport" accept=".csv,.xls,.xlsx" required onchange="updateImportFileName(this)">
                            </div>

                            <button type="submit" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-scantec-blue text-white font-bold text-sm hover:bg-gray-800 transition-all shadow-sm">
                                <i class="fas fa-upload mr-2"></i> Cargar y revisar
                            </button>
                        </div>
                    </form>
                </div>

                <?php if ($importPreview): ?>
                    <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Paso 2</p>
                                <h4 class="text-lg font-bold text-scantec-blue">Confirmar importación</h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Archivo cargado:
                                    <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($importArchivo, ENT_QUOTES, 'UTF-8'); ?></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400">
                                <span><?php echo count($importUsuarios); ?> registros válidos</span>
                                <span class="text-gray-300">|</span>
                                <span><?php echo count($importErrores); ?> observaciones</span>
                            </div>
                        </div>

                        <?php if (!empty($importErrores)): ?>
                            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <h5 class="text-sm font-bold text-amber-800 mb-3">Observaciones a corregir antes de importar</h5>
                                <ul class="space-y-2 text-sm text-amber-700">
                                    <?php foreach ($importErrores as $errorImportacion): ?>
                                        <li><?php echo htmlspecialchars($errorImportacion, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($importUsuarios)): ?>
                            <form action="<?php echo base_url(); ?>usuarios/confirmar_importacion" method="POST" class="mt-5 space-y-5">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <input type="checkbox" id="seleccionarTodosImportacion" class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue" checked>
                                        <span class="text-sm font-semibold text-gray-700">Seleccionar todos los usuarios de la vista previa</span>
                                    </label>

                                    <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                        <input type="checkbox" name="importar_activos" value="1" class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue" checked>
                                        <span class="text-sm font-semibold text-gray-700">Importar los seleccionados como activos por defecto</span>
                                    </label>
                                </div>

                                <div class="overflow-x-auto border border-gray-100 rounded-2xl">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500 w-14">
                                                <input type="checkbox" id="seleccionarTodosImportacionTabla" class="rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue" checked>
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Nombre</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Departamento</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Usuario</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Rol</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Correo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <?php foreach ($importUsuarios as $indiceImportacion => $usuarioImportado): ?>
                                            <tr>
                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" name="usuarios_importar[]" value="<?php echo $indiceImportacion; ?>" class="selector-importacion rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue" checked>
                                                </td>
                                                <td class="px-4 py-3 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($usuarioImportado['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($usuarioImportado['departamento'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-4 py-3 text-sm font-mono text-gray-600"><?php echo htmlspecialchars($usuarioImportado['usuario'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($usuarioImportado['rol_descripcion'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($usuarioImportado['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>

                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <span id="resumenSeleccionImportacion" class="text-sm font-semibold text-gray-600"><?php echo count($importUsuarios); ?> seleccionados</span>

                                    <button type="submit"
                                        id="btnConfirmarImportacion"
                                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-green-600 text-white font-bold text-sm hover:bg-green-700 transition-all shadow-sm disabled:opacity-40 disabled:cursor-not-allowed"
                                        <?php echo !empty($importErrores) || empty($importUsuarios) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check mr-2"></i> Confirmar importación
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <div class="mt-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div></div>
                            <a href="<?php echo base_url(); ?>usuarios/cancelar_importacion"
                               class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-gray-200 bg-white text-gray-600 font-bold text-sm hover:bg-gray-50 transition-all">
                                <i class="fas fa-times mr-2"></i> Cancelar
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="modalNuevoUsuario" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-2xl border border-gray-100">
            
            <div class="bg-scantec-blue px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold leading-6 text-white tracking-wide" id="modal-title">
                    <i class="fas fa-user-plus mr-2"></i> Nuevo Usuario
                </h3>
                <button type="button" onclick="toggleModal('modalNuevoUsuario')" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="registroForm" method="post" action="<?php echo base_url(); ?>Usuarios/insertar" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="px-6 py-6 bg-gray-50">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Nombre Completo</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-id-card text-gray-400"></i></div>
                                    <input type="text" id="nombre" name="nombre" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all" placeholder="Nombre y Apellido" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Usuario (Login)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user text-gray-400"></i></div>
                                    <input type="text" id="usuario" name="usuario" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" placeholder="Ej: admin" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-envelope text-gray-400"></i></div>
                                    <input type="email" id="email" name="email" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all" placeholder="correo@empresa.com" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Departamento</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-building text-gray-400"></i></div>
                                    <select id="id_departamento" name="id_departamento" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white" required>
                                        <option value="" selected disabled>Seleccione un departamento</option>
                                        <?php foreach (($data['departamentos'] ?? []) as $departamento): ?>
                                            <option value="<?php echo intval($departamento['id_departamento']); ?>">
                                                <?php echo htmlspecialchars((string)($departamento['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rol</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user-tag text-gray-400"></i></div>
                                    <select id="id_rol" name="id_rol" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white" required>
                                        <option value="" selected disabled>Seleccione un rol</option>
                                        <?php foreach ($data['roles'] as $roles) { ?>
                                            <option value="<?php echo $roles['id_rol']; ?>"><?php echo $roles['descripcion']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-span-1 md:col-span-2 border-t border-gray-200 my-2"></div>

                            <div>
                                <label class="block text-xs font-bold text-red-500 uppercase mb-2">Contraseña</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-lock text-gray-400"></i></div>
                                    <input type="password" id="clave" name="clave" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" placeholder="********" required>
                                </div>
                                <p class="text-red-500 text-xs mt-1">Debe tener al menos 7 caracteres, una mayúscula y un símbolo.</p>
                                <p id="passwordError" class="text-red-500 text-xs mt-1 font-bold"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-red-500 uppercase mb-2">Repetir Contraseña</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-lock text-gray-400"></i></div>
                                    <input type="password" id="claveConfirm" name="claveConfirm" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" placeholder="********" required>
                                </div>
                                <p id="passwordConfirmError" class="text-red-500 text-xs mt-1 font-bold"></p>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent bg-scantec-blue px-6 py-2 text-base font-bold text-white shadow-sm hover:bg-gray-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Registrar
                    </button>
                    <button type="button" onclick="toggleModal('modalNuevoUsuario')" class="mt-3 inline-flex w-full justify-center rounded-lg border border-gray-300 bg-white px-6 py-2 text-base font-bold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php pie() ?>

<script>
    function resetNuevoUsuarioForm() {
        const form = document.getElementById('registroForm');
        if (!form) {
            return;
        }

        form.reset();

        ['id_departamento', 'id_rol'].forEach(function(id) {
            const field = document.getElementById(id);
            if (field) {
                field.selectedIndex = 0;
            }
        });

        const passwordError = document.getElementById('passwordError');
        const passwordConfirmError = document.getElementById('passwordConfirmError');
        if (passwordError) {
            passwordError.textContent = '';
        }
        if (passwordConfirmError) {
            passwordConfirmError.textContent = '';
        }
    }

    // Función para abrir/cerrar modal Tailwind
    function toggleModal(modalID, show) {
        const modal = document.getElementById(modalID);
        if (!modal) {
            return;
        }

        if (typeof show === 'boolean') {
            modal.classList.toggle('hidden', !show);
            if (modalID === 'modalNuevoUsuario' && show) {
                resetNuevoUsuarioForm();
            }
            return;
        }

        modal.classList.toggle("hidden");
        if (modalID === 'modalNuevoUsuario' && !modal.classList.contains('hidden')) {
            resetNuevoUsuarioForm();
        }
    }

    function updateImportFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : "Seleccione un archivo CSV, XLS o XLSX";
        const target = document.getElementById('fileNameImport');
        if (target) {
            target.innerText = fileName;
        }
    }

    function aplicarFiltroUsuarios(filter) {
        const rows = document.querySelectorAll('#table tbody tr[data-estado]');
        const buttons = document.querySelectorAll('.btn-filtro-usuarios');

        rows.forEach(function(row) {
            const estado = row.getAttribute('data-estado');
            row.style.display = (filter === 'todos' || estado === filter) ? '' : 'none';
        });

        buttons.forEach(function(button) {
            const tipo = button.getAttribute('data-filter');
            const activo = tipo === filter;

            button.classList.remove('bg-scantec-blue', 'text-white', 'shadow-sm', 'border', 'border-amber-200', 'text-amber-700', 'bg-amber-50', 'border-green-200', 'text-green-700', 'bg-green-50', 'border-red-200', 'text-red-700', 'bg-red-50', 'border-gray-200', 'text-scantec-blue', 'bg-white');

            if (activo) {
                button.classList.add('bg-scantec-blue', 'text-white', 'shadow-sm');
            } else if (tipo === 'pendiente') {
                button.classList.add('border', 'border-amber-200', 'text-amber-700', 'bg-amber-50');
            } else if (tipo === 'activo') {
                button.classList.add('border', 'border-green-200', 'text-green-700', 'bg-green-50');
            } else if (tipo === 'inactivo') {
                button.classList.add('border', 'border-red-200', 'text-red-700', 'bg-red-50');
            } else {
                button.classList.add('border', 'border-gray-200', 'text-scantec-blue', 'bg-white');
            }
        });

        actualizarSeleccionMasiva();
    }

    function actualizarSeleccionMasiva() {
        const visibles = Array.from(document.querySelectorAll('#table tbody tr[data-estado]'))
            .filter(function(row) { return row.style.display !== 'none'; });
        const checkboxesVisibles = visibles
            .map(function(row) { return row.querySelector('.selector-usuario'); })
            .filter(Boolean);
        const seleccionados = document.querySelectorAll('.selector-usuario:checked').length;
        const btn = document.getElementById('btnActivarSeleccionados');
        const resumen = document.getElementById('seleccionResumen');
        const seleccionarTodos = document.getElementById('seleccionarTodos');

        if (resumen) {
            resumen.textContent = seleccionados + ' seleccionados';
        }

        if (btn) {
            btn.disabled = seleccionados === 0;
        }

        if (seleccionarTodos) {
            seleccionarTodos.checked = checkboxesVisibles.length > 0 && checkboxesVisibles.every(function(cb) { return cb.checked; });
            seleccionarTodos.indeterminate = checkboxesVisibles.some(function(cb) { return cb.checked; }) && !seleccionarTodos.checked;
        }
    }

    function actualizarSeleccionImportacion() {
        const checkboxes = Array.from(document.querySelectorAll('.selector-importacion'));
        const seleccionados = checkboxes.filter(function(checkbox) { return checkbox.checked; }).length;
        const total = checkboxes.length;
        const resumen = document.getElementById('resumenSeleccionImportacion');
        const boton = document.getElementById('btnConfirmarImportacion');
        const toggleSuperior = document.getElementById('seleccionarTodosImportacion');
        const toggleTabla = document.getElementById('seleccionarTodosImportacionTabla');

        if (resumen) {
            resumen.textContent = seleccionados + ' seleccionados';
        }

        if (boton && !boton.hasAttribute('data-disabled-by-errors')) {
            boton.disabled = seleccionados === 0;
        }

        [toggleSuperior, toggleTabla].forEach(function(toggle) {
            if (!toggle) {
                return;
            }
            toggle.checked = total > 0 && seleccionados === total;
            toggle.indeterminate = seleccionados > 0 && seleccionados < total;
        });
    }

    function marcarTodosImportacion(checked) {
        document.querySelectorAll('.selector-importacion').forEach(function(checkbox) {
            checkbox.checked = checked;
        });
        actualizarSeleccionImportacion();
    }

    // Validación de contraseña
    document.getElementById('registroForm').addEventListener('submit', function(event) {
        var password = document.getElementById('clave').value;
        var passwordConfirm = document.getElementById('claveConfirm').value;
        var regex = /^(?=.*[A-Z])(?=.*[!@#$%^&*\-_.#])(?=.*[a-z\d])(?=.{7,})/;

        if (!regex.test(password)) {
            event.preventDefault();
            document.getElementById('passwordError').textContent = 'Mínimo 7 caracteres, 1 mayúscula y 1 símbolo';
        } else {
            document.getElementById('passwordError').textContent = '';
        }

        if (password !== passwordConfirm) {
            event.preventDefault();
            document.getElementById('passwordConfirmError').textContent = 'Las contraseñas no coinciden.';
        } else {
            document.getElementById('passwordConfirmError').textContent = '';
        }
    });

    document.querySelectorAll('.btn-filtro-usuarios').forEach(function(button) {
        button.addEventListener('click', function() {
            aplicarFiltroUsuarios(this.getAttribute('data-filter'));
        });
    });

    document.querySelectorAll('.selector-usuario').forEach(function(checkbox) {
        checkbox.addEventListener('change', actualizarSeleccionMasiva);
    });

    const seleccionarTodos = document.getElementById('seleccionarTodos');
    if (seleccionarTodos) {
        seleccionarTodos.addEventListener('change', function() {
            Array.from(document.querySelectorAll('#table tbody tr[data-estado]'))
                .filter(function(row) { return row.style.display !== 'none'; })
                .forEach(function(row) {
                    const checkbox = row.querySelector('.selector-usuario');
                    if (checkbox) {
                        checkbox.checked = seleccionarTodos.checked;
                    }
                });
            actualizarSeleccionMasiva();
        });
    }

    const btnActivarSeleccionados = document.getElementById('btnActivarSeleccionados');
    if (btnActivarSeleccionados) {
        btnActivarSeleccionados.addEventListener('click', function() {
            if (document.querySelectorAll('.selector-usuario:checked').length === 0) {
                return;
            }
            document.getElementById('formActivacionMasiva').submit();
        });
    }

    const btnConfirmarImportacion = document.getElementById('btnConfirmarImportacion');
    if (btnConfirmarImportacion && btnConfirmarImportacion.disabled) {
        btnConfirmarImportacion.setAttribute('data-disabled-by-errors', '1');
    }

    const seleccionarTodosImportacion = document.getElementById('seleccionarTodosImportacion');
    if (seleccionarTodosImportacion) {
        seleccionarTodosImportacion.addEventListener('change', function() {
            marcarTodosImportacion(this.checked);
        });
    }

    const seleccionarTodosImportacionTabla = document.getElementById('seleccionarTodosImportacionTabla');
    if (seleccionarTodosImportacionTabla) {
        seleccionarTodosImportacionTabla.addEventListener('change', function() {
            marcarTodosImportacion(this.checked);
        });
    }

    document.querySelectorAll('.selector-importacion').forEach(function(checkbox) {
        checkbox.addEventListener('change', actualizarSeleccionImportacion);
    });

    actualizarSeleccionMasiva();
    actualizarSeleccionImportacion();

    <?php if ($importPreview): ?>
    toggleModal('modalImportarUsuarios', true);
    <?php endif; ?>
</script>

<?php if (isset($_SESSION['alert'])) { ?>
<?php
    $alertType = $_SESSION['alert']['type'] ?? 'info';
    $alertMessage = $_SESSION['alert']['message'] ?? '';
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: <?php echo json_encode($alertType, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            title: <?php echo json_encode($alertMessage, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            showConfirmButton: true,
            confirmButtonColor: '#182541',
            timer: 5000
        });
    });
</script>
<?php unset($_SESSION['alert']); } ?>



