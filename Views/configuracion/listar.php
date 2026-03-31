<?php 
    // 1. LÓGICA DE EXTRACCIÓN DE DATOS
    $config = [];
    if (!empty($data) && isset($data[0])) {
        $config = $data[0];
    } else {
        $config = $data; 
    }    
    // 2. LÓGICA DE LICENCIA Y USO
    // Obtenemos usuarios activos (del ajuste en el modelo) o 0 si no existe
    $usuariosActivos = isset($config['total_usuarios']) ? $config['total_usuarios'] : 0;    
    // Obtenemos el límite del archivo de licencia (o 10 por defecto en DEV)
    $limiteUsuarios = defined('LICENCIA_MAX_USUARIOS') ? LICENCIA_MAX_USUARIOS : 10;
    // Cálculo de porcentaje para la barra visual
    $porcentajeUso = 0;
    if ($limiteUsuarios > 0) {
        $porcentajeUso = ($usuariosActivos / $limiteUsuarios) * 100;
    }
    // Limitamos visualmente al 100% para que no se salga la barra
    $anchoBarra = ($porcentajeUso > 100) ? 100 : $porcentajeUso;
    // Color semántico: Verde si hay espacio, Rojo si está lleno (>90%)
    $colorBarra = ($porcentajeUso > 90) ? 'bg-red-500' : 'bg-green-400';
    $departamentos = is_array($config['departamentos'] ?? null) ? $config['departamentos'] : [];
    encabezado($data); 
?>
<main class="app-content bg-gray-50 min-h-screen py-8">
    
    <div class="container mx-auto px-4">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-cogs mr-2"></i> Configuración del Sistema
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestiona los datos generales de la empresa y parámetros globales.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue flex justify-between items-center">
                        <h3 class="font-bold text-white text-sm uppercase tracking-wider">Datos de la Empresa</h3>
                        <span class="text-xs text-scantec-blue bg-white px-2 py-1 rounded border border-white/80">
                            ID: <?php echo $config['id'] ?? '---'; ?>
                        </span>
                    </div>

                    <div class="p-6">
                        <form action="<?php echo base_url(); ?>configuracion/actualizar" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $config['id'] ?? ''; ?>">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2">Nombre / Razón Social</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-building text-gray-400"></i>
                                        </div>
                                        <input type="text" name="nombre" 
                                            value="<?php echo $config['nombre'] ?? ''; ?>" required
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/30 text-gray-700"
                                            placeholder="Nombre de la Empresa">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Teléfono</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-phone text-gray-400"></i>
                                        </div>
                                        <input type="text" name="telefono" 
                                            value="<?php echo $config['telefono'] ?? ''; ?>" required
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700"
                                            placeholder="Ej: 0981...">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Correo Electrónico</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-envelope text-gray-400"></i>
                                        </div>
                                        <input type="email" name="correo" 
                                            value="<?php echo $config['correo'] ?? ''; ?>" required
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700"
                                            placeholder="contacto@empresa.com">
                                    </div>
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Dirección Física</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-map-marker text-gray-400"></i>
                                        </div>
                                        <input type="text" name="direccion" 
                                            value="<?php echo $config['direccion'] ?? ''; ?>" required
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700"
                                            placeholder="Calle Principal 123">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Logo</label>
                                    <input type="file" name="logo_empresa" accept="image/*"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 bg-white">
                                    <?php if (!empty($config['logo_empresa_url'])): ?>
                                    <div class="mt-3 p-3 rounded-xl border border-gray-200 bg-gray-50">
                                        <img src="<?php echo htmlspecialchars($config['logo_empresa_url']); ?>" alt="Logo empresa" class="h-16 w-auto object-contain">
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Logo Reducido</label>
                                    <input type="file" name="logo_empresa_reducido" accept="image/*"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 bg-white">
                                    <?php if (!empty($config['logo_empresa_reducido_url'])): ?>
                                    <div class="mt-3 p-3 rounded-xl border border-gray-200 bg-gray-50">
                                        <img src="<?php echo htmlspecialchars($config['logo_empresa_reducido_url']); ?>" alt="Logo reducido" class="h-16 w-auto object-contain">
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- <div>
                                    <label class="block text-xs font-bold text-scantec-red uppercase tracking-widest mb-2">Límite Páginas / Lote</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-file-text-o text-gray-400"></i>
                                        </div>
                                        <input type="number" name="total_pag" 
                                            value="<?php echo $config['total_pag'] ?? ''; ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-red focus:border-transparent outline-none transition-all text-gray-700"
                                            placeholder="0">
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 ml-1">Para control de digitalización.</p>
                                </div> -->
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-100">
                                <button type="submit" class="bg-scantec-blue hover:bg-gray-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-all flex items-center">
                                    <i class="fa fa-save mr-2"></i> ACTUALIZAR DATOS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                    <div class="px-6 py-4 border-b border-blue-800 bg-scantec-blue flex justify-between items-center gap-4">
                        <div>
                            <h3 class="font-bold text-white text-sm uppercase tracking-wider">Departamentos</h3>
                            <p class="text-xs text-white/80 mt-1">Administra los departamentos disponibles para identificar usuarios.</p>
                        </div>
                        <button type="button" onclick="abrirModalNuevoDepartamento()" class="bg-white/95 hover:bg-white text-scantec-blue font-bold py-2 px-3 rounded-lg shadow transition-all inline-flex items-center text-xs uppercase tracking-wide">
                            <i class="fa fa-plus mr-2"></i>Nuevo
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600">Departamento</th>
                                    <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-32">Estado</th>
                                    <th class="text-center px-4 py-3 font-bold text-xs uppercase tracking-wider text-gray-600 w-32">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($departamentos)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-6 text-gray-500">No hay departamentos registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($departamentos as $departamento): ?>
                                        <?php
                                            $idDepto = intval($departamento['id_departamento'] ?? 0);
                                            $estadoDepto = strtoupper(trim((string)($departamento['estado'] ?? 'ACTIVO')));
                                            $esActivoDepto = $estadoDepto === 'ACTIVO';
                                            $totalUsuariosDepto = intval($departamento['total_usuarios'] ?? 0);
                                            $puedeEliminarDepto = $totalUsuariosDepto === 0;
                                        ?>
                                        <tr class="border-b border-gray-100 transition-colors <?php echo $esActivoDepto ? 'hover:bg-gray-50' : 'bg-gray-50/80'; ?>">
                                            <td class="px-4 py-3 align-middle">
                                                <div class="flex items-center min-h-[36px] font-bold <?php echo $esActivoDepto ? 'text-gray-800' : 'text-gray-400'; ?>">
                                                    <div class="w-8 h-8 rounded-full <?php echo $esActivoDepto ? 'bg-blue-50 text-scantec-blue border-blue-100' : 'bg-gray-100 text-gray-300 border-gray-200'; ?> border flex items-center justify-center mr-3 flex-shrink-0 <?php echo $esActivoDepto ? '' : 'opacity-70'; ?>">
                                                        <i class="fas fa-building text-xs"></i>
                                                    </div>
                                                    <div class="flex items-center gap-2 <?php echo $esActivoDepto ? '' : 'opacity-70'; ?>">
                                                        <span><?php echo htmlspecialchars($departamento['nombre'] ?? ''); ?></span>
                                                        <?php if (!$esActivoDepto): ?>
                                                        <span class="px-2 py-1 text-[10px] font-bold rounded bg-gray-200 text-gray-500 uppercase tracking-wide">Inactivo</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-center">
                                                <?php if ($esActivoDepto): ?>
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">Activo</span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200 opacity-70">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-center">
                                                <div class="flex items-center justify-center gap-1.5 h-8 <?php echo $esActivoDepto ? '' : 'opacity-55'; ?>">
                                                    <div class="h-8 flex items-center">
                                                        <button type="button"
                                                            onclick="abrirModalEditarDepartamento(<?php echo $idDepto; ?>, '<?php echo htmlspecialchars($departamento['nombre'] ?? '', ENT_QUOTES); ?>', '<?php echo $estadoDepto; ?>')"
                                                            class="btn-action btn-action-primary"
                                                            title="Editar Departamento">
                                                            <i class="fas fa-pen"></i>
                                                        </button>
                                                    </div>
                                                    <form action="<?php echo base_url(); ?>configuracion/eliminar_departamento" method="post" onsubmit="return confirmarAccionDepartamento(this);" class="h-8 flex items-center m-0">
                                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                                        <input type="hidden" name="id_departamento" value="<?php echo $idDepto; ?>">
                                                        <input type="hidden" name="accion_departamento" value="desactivar">
                                                        <button type="submit" class="btn-action btn-action-danger" title="Eliminar o desactivar departamento">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
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

            <div class="lg:col-span-1 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4 border-b pb-2">Conectividad</h3>
                    
                    <div class="space-y-3">
                        <a href="<?php echo base_url(); ?>configuracion/servidor_AD" 
                           class="flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-scantec-blue hover:bg-blue-50 transition-all group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gray-100 group-hover:bg-blue-200 flex items-center justify-center mr-3 text-gray-600 group-hover:text-scantec-blue transition-colors">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700">Servidor LDAP</h4>
                                    <p class="text-xs text-gray-500">Active Directory</p>
                                </div>
                            </div>
                            <i class="fa fa-chevron-right text-gray-300 group-hover:text-scantec-blue"></i>
                        </a>

                        <a href="<?php echo base_url(); ?>configuracion/servidor_smtp" 
                           class="flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-scantec-red hover:bg-red-50 transition-all group">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gray-100 group-hover:bg-red-200 flex items-center justify-center mr-3 text-gray-600 group-hover:text-scantec-red transition-colors">
                                    <i class="fa fa-envelope-o"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700">Servidor SMTP</h4>
                                    <p class="text-xs text-gray-500">Configuración de Correo</p>
                                </div>
                            </div>
                            <i class="fa fa-chevron-right text-gray-300 group-hover:text-scantec-red"></i>
                        </a>
                    </div>
                </div>

                <div class="bg-scantec-blue text-white rounded-2xl shadow-lg p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full"></div>
                    
                    <h3 class="font-bold text-sm uppercase tracking-wider mb-4 relative z-10 border-b border-white/20 pb-2">
                        <i class="fa fa-server mr-2"></i> Estado del Servicio
                    </h3>
                    
                    <div class="space-y-4 relative z-10 text-sm">            
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="opacity-80 text-xs uppercase">Usuarios Activos</span>
                                <span class="font-bold text-xs">
                                    <?php echo $usuariosActivos; ?> / <?php echo $limiteUsuarios; ?>
                                </span>
                            </div>
                            <div class="w-full bg-black/30 rounded-full h-2 overflow-hidden">
                                <div class="<?php echo $colorBarra; ?> h-2 rounded-full transition-all duration-1000 ease-out" 
                                     style="width: <?php echo $anchoBarra; ?>%"></div>
                            </div>
                            <p class="text-[10px] text-right mt-1 opacity-60">Consumo de licencia</p>
                        </div>

                        <div class="flex justify-between border-b border-white/20 pb-2">
                            <span class="opacity-80">Ambiente:</span>
                            <span class="font-bold bg-white/20 px-2 rounded-sm text-xs">
                                <?php echo defined('LICENCIA_AMBIENTE') ? LICENCIA_AMBIENTE : 'DEV'; ?>
                            </span>
                        </div>

                        <div class="flex justify-between items-center pt-1">
                            <span class="opacity-80">Licencia:</span>
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse mr-2"></span>
                                <span class="font-bold text-green-300 tracking-wide">ACTIVA</span>
                            </div>
                        </div>
                        
                        <div class="text-right mt-2">
                            <span class="text-[10px] opacity-60 block">Vence el:</span>
                            <span class="text-xs font-mono">
                                <?php echo defined('LICENCIA_EXPIRA') ? date("d/m/Y", strtotime(LICENCIA_EXPIRA)) : '---'; ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<div id="modal-editar-departamento" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" style="backdrop-filter: blur(2px);">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-md transform transition-all p-6 relative">
        <button type="button" onclick="cerrarModalEditarDepartamento()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times fa-lg"></i>
        </button>

        <h3 class="text-xl font-bold text-slate-800 mb-2">Editar Departamento</h3>
        <p class="text-xs text-gray-500 mb-6">Actualiza el nombre visible del departamento seleccionado y su estado.</p>

        <form action="<?php echo base_url(); ?>configuracion/actualizar_departamento" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="id_departamento" id="editar_id_departamento" value="">

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Departamento <span class="text-red-500">*</span></label>
                <input type="text" name="nombre_departamento" id="editar_nombre_departamento" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow" placeholder="Ej. Recursos Humanos">
            </div>

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Estado</label>
                <select name="estado_departamento" id="editar_estado_departamento" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">Si el departamento está inactivo, al volver a marcarlo como activo quedará habilitado nuevamente.</p>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="cerrarModalEditarDepartamento()" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-scantec-blue hover:bg-gray-800 text-white font-bold rounded-xl shadow transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-nuevo-departamento" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden" style="backdrop-filter: blur(2px);">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-md transform transition-all p-6 relative">
        <button type="button" onclick="cerrarModalNuevoDepartamento()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 focus:outline-none">
            <i class="fas fa-times fa-lg"></i>
        </button>

        <h3 class="text-xl font-bold text-slate-800 mb-2">Nuevo Departamento</h3>
        <p class="text-xs text-gray-500 mb-6">Agrega un nuevo departamento disponible para el sistema.</p>

        <form action="<?php echo base_url(); ?>configuracion/guardar_departamento" method="POST">
            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nombre del Departamento <span class="text-red-500">*</span></label>
                <input type="text" name="nombre_departamento" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-scantec-blue focus:border-scantec-blue outline-none transition-shadow" placeholder="Ej. Recursos Humanos">
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="cerrarModalNuevoDepartamento()" class="px-5 py-2 text-gray-600 font-bold hover:bg-gray-100 rounded-xl transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2 bg-scantec-blue hover:bg-gray-800 text-white font-bold rounded-xl shadow transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i> Guardar
                </button>
            </div>
        </form>
    </div>
</div>


<?php pie() ?>

<script>
function abrirModalEditarDepartamento(idDepartamento, nombre, estado) {
    document.getElementById('editar_id_departamento').value = idDepartamento;
    document.getElementById('editar_nombre_departamento').value = nombre;
    document.getElementById('editar_estado_departamento').value = estado || 'ACTIVO';
    document.getElementById('modal-editar-departamento').classList.remove('hidden');
    document.getElementById('editar_nombre_departamento').focus();
}

function cerrarModalEditarDepartamento() {
    document.getElementById('modal-editar-departamento').classList.add('hidden');
    document.getElementById('editar_id_departamento').value = '';
    document.getElementById('editar_nombre_departamento').value = '';
    document.getElementById('editar_estado_departamento').value = 'ACTIVO';
}

function abrirModalNuevoDepartamento() {
    document.getElementById('modal-nuevo-departamento').classList.remove('hidden');
}

function cerrarModalNuevoDepartamento() {
    document.getElementById('modal-nuevo-departamento').classList.add('hidden');
}

function confirmarAccionDepartamento(formElement) {
    Swal.fire({
        title: 'Departamento',
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
            const inputAccion = formElement.querySelector('input[name="accion_departamento"]');
            if (inputAccion) {
                inputAccion.value = result.isConfirmed ? 'eliminar' : 'desactivar';
            }
            formElement.submit();
        }
    });

    return false;
}

</script>

















