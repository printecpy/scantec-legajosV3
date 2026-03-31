<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-10 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="mb-6 flex items-center text-sm text-gray-500">
            <a href="<?php echo base_url(); ?>usuarios/listar" class="hover:text-scantec-blue transition-colors">
                <i class="fas fa-users mr-1"></i> Usuarios
            </a>
            <span class="mx-2">/</span>
            <span class="text-scantec-blue font-bold">Editar Usuario</span>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                
                <div class="bg-scantec-blue px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">
                            <i class="fas fa-user-edit mr-2"></i> Modificar Usuario
                        </h2>
                        <p class="text-blue-100 text-xs mt-1">Actualice la información y permisos.</p>
                    </div>
                    
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                        <?php 
                            $nombreUser = $data['usuario']['nombre'] ?? '??';
                            echo strtoupper(substr($nombreUser, 0, 2)); 
                        ?>
                    </div>
                </div>

                <form method="post" action="<?php echo base_url(); ?>Usuarios/actualizar" autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    <input type="hidden" name="id" value="<?php echo $data['usuario']['id']; ?>">
                    <input type="hidden" name="id_grupo" value="<?php echo intval($data['usuario']['id_grupo'] ?? 0); ?>">

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="col-span-1 md:col-span-2 mb-2">
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">
                                    Información General
                                </h3>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-scantec-blue uppercase mb-2">Nombre Completo</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-id-card"></i></div>
                                    <input type="text" id="nombre" name="nombre" 
                                        value="<?php echo $data['usuario']['nombre']; ?>"
                                        class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-gray-50/30" required>
                                </div>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Departamento</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-building"></i></div>
                                    <select id="id_departamento" name="id_departamento" class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white appearance-none cursor-pointer" required>
                                        <option value="" disabled>Seleccione un departamento</option>
                                        <?php foreach (($data['departamentos'] ?? []) as $departamento): ?>
                                            <option value="<?php echo intval($departamento['id_departamento']); ?>" <?php echo intval($departamento['id_departamento']) === intval($data['usuario']['id_departamento'] ?? 0) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string)($departamento['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Usuario (Login)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-user"></i></div>
                                    <input type="text" id="usuario" name="usuario" 
                                        value="<?php echo $data['usuario']['usuario']; ?>"
                                        class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-gray-50/30">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Correo Electrónico</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-envelope"></i></div>
                                    <input type="email" id="email" name="email" 
                                        value="<?php echo $data['usuario']['email']; ?>"
                                        class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-gray-50/30">
                                </div>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nueva Contraseña</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-key"></i></div>
                                    <input type="password" id="clave" name="clave"
                                        class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none transition-all bg-gray-50/30"
                                        placeholder="Dejar en blanco para mantener la actual">
                                </div>
                                <p class="mt-2 text-xs text-red-500">Si la completas, se reemplazará la contraseña actual del usuario.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Estado</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-toggle-on"></i></div>
                                    <select name="estado_usuario" id="estado_usuario" class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white appearance-none cursor-pointer">
                                        <?php $estadoActual = strtoupper((string)($data['usuario']['estado_usuario'] ?? 'ACTIVO')); ?>
                                        <option value="ACTIVO" <?php echo $estadoActual === 'ACTIVO' ? 'selected' : ''; ?>>Activo</option>
                                        <option value="INACTIVO" <?php echo $estadoActual === 'INACTIVO' ? 'selected' : ''; ?>>Inactivo</option>
                                        <option value="BLOQUEADO" <?php echo $estadoActual === 'BLOQUEADO' ? 'selected' : ''; ?>>Bloqueado</option>
                                        <option value="PENDIENTE" <?php echo $estadoActual === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['id_rol']) && in_array(intval($_SESSION['id_rol']), [1, 2, 5], true)) { ?>
                                <div class="col-span-1 md:col-span-2 mt-4 mb-2">
                                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">
                                        Permisos y Accesos
                                    </h3>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rol</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-user-tag"></i></div>
                                        <select name="id_rol" id="id_rol" class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white appearance-none cursor-pointer">
                                            <?php foreach ($data['rol'] as $rol) { ?>
                                                <option <?php if ($rol['id_rol'] == $data['usuario']['id_rol']) { echo 'selected'; } ?> value="<?php echo $rol['id_rol']; ?>">
                                                    <?php echo $rol['descripcion']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><i class="fas fa-chevron-down text-xs"></i></div>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                        <a href="<?php echo base_url();?>usuarios/listar" 
                           class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-white transition-all">
                            Cancelar
                        </a>
                        <button type="submit" 
                            class="px-8 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm shadow-md hover:bg-gray-800 transition-all flex items-center">
                            <i class="fas fa-save mr-2"></i> Actualizar
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</main>

<?php pie() ?>
