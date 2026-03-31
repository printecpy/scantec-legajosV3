<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-10 font-sans">
    <div class="container mx-auto px-4">

        <div class="mb-6 flex items-center text-sm text-gray-500">
            <a href="<?php echo base_url(); ?>usuarios/listar" class="hover:text-scantec-blue transition-colors">
                <i class="fas fa-users mr-1"></i> Usuarios
            </a>
            <span class="mx-2">/</span>
            <span class="text-scantec-blue font-bold">Detalle de Usuario</span>
        </div>

        <div class="flex justify-center">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">

                <div class="bg-scantec-blue px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-wide">
                            <i class="fas fa-id-badge mr-2"></i> Detalle de Usuario
                        </h2>
                        <p class="text-blue-100 text-xs mt-1">Información ampliada del usuario seleccionado.</p>
                    </div>

                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                        <?php
                            $nombreUser = $data['usuario']['nombre'] ?? '??';
                            echo strtoupper(substr($nombreUser, 0, 2));
                        ?>
                    </div>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="col-span-1 md:col-span-2 mb-2">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">
                                Información General
                            </h3>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nombre</p>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 font-semibold">
                                <?php echo htmlspecialchars($data['usuario']['nombre'] ?? '-'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Usuario</p>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 font-mono text-sm">
                                <?php echo htmlspecialchars($data['usuario']['usuario'] ?? '-'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Departamento</p>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 font-semibold">
                                <?php echo htmlspecialchars($data['usuario']['departamento'] ?? '-'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Correo Electrónico</p>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 font-semibold">
                                <?php echo htmlspecialchars($data['usuario']['email'] ?? '-'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Rol</p>
                            <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-blue-700 font-bold">
                                <?php echo htmlspecialchars($data['nombre_rol'] ?? 'Sin rol'); ?>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Estado</p>
                            <div class="rounded-xl border px-4 py-3 font-bold <?php echo ($data['usuario']['estado_usuario'] ?? '') === 'ACTIVO' ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'; ?>">
                                <?php echo htmlspecialchars($data['usuario']['estado_usuario'] ?? '-'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end space-x-3">
                    <a href="<?php echo base_url();?>usuarios/listar"
                       class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-white transition-all">
                        Volver
                    </a>
                    <a href="<?php echo base_url();?>usuarios/editar?id=<?php echo intval($data['usuario']['id'] ?? 0); ?>"
                       class="px-8 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm shadow-md hover:bg-gray-800 transition-all flex items-center">
                        <i class="fas fa-pencil-alt mr-2"></i> Editar
                    </a>
                </div>

            </div>
        </div>
    </div>
</main>

<?php pie() ?>

