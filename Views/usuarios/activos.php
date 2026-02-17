<?php encabezado($data); ?>

<main class="app-content bg-gray-50 min-h-screen py-8 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-scantec-blue uppercase tracking-wide flex items-center">
                    <i class="fas fa-users-cog mr-3"></i> Monitor de Sesiones
                </h1>
                <p class="text-sm text-gray-500 mt-1 ml-1">Vista en tiempo real de usuarios conectados al sistema.</p>
            </div>
            
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center">
                <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse mr-2"></span>
                <span class="text-sm font-bold text-gray-600">
                    <?php echo count($data['activos']); ?> Conectados
                </span>
            </div>
        </div>

        <div class="table-container">
            <table class="scantec-table" id="table">
                <thead>
                    <tr>
                        <th class="text-center">Inicio de Sesión</th>
                        <th class="pl-6">Usuario</th>
                        <th class="text-center">Dirección IP</th>
                        <th class="text-center">Nombre Host</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['activos'] as $activo) { 
                        // Formato de fecha amigable
                        $fecha = date_create($activo['fecha']);
                        $fechaFormat = date_format($fecha, "d/m/Y");
                        $horaFormat = date_format($fecha, "H:i:s");
                        
                        // Iniciales para avatar
                        $iniciales = strtoupper(substr($activo['nombre'], 0, 2));
                    ?>
                    <tr>
                        <td class="text-center">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700 text-xs"><?php echo $fechaFormat; ?></span>
                                <span class="text-gray-400 text-[10px]"><?php echo $horaFormat; ?></span>
                            </div>
                        </td>

                        <td class="pl-6">
                            <div class="flex items-center">
                                <div class="avatar-circle bg-blue-50 text-scantec-blue border border-blue-100 mr-3">
                                    <?php echo $iniciales; ?>
                                </div>
                                <span class="font-bold text-gray-800 text-sm"><?php echo $activo['nombre']; ?></span>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-600 border border-gray-200">
                                <?php echo $activo['ip']; ?>
                            </span>
                        </td>

                        <td class="text-center text-xs text-gray-500 font-medium">
                            <?php echo $activo['servidor']; ?>
                        </td>

                        <td class="text-center">
                            <a href="<?= base_url(); ?>Usuarios/fin_session?id_visita=<?= $activo['id_visita']; ?>&id=<?= $activo['id']; ?>"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 border border-red-100 hover:bg-red-600 hover:text-white hover:shadow-md transition-all duration-300"
                               title="Forzar Cierre de Sesión"
                               onclick="return confirm('¿Estás seguro de que deseas desconectar a este usuario?')">
                                <i class="fas fa-power-off text-xs"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php pie() ?>