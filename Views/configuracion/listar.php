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
    encabezado($data); 
?>
<main class="app-content bg-gray-50 min-h-screen py-8">
    
    <div class="container mx-auto px-4">
        
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-cogs mr-2"></i> Configuración del Sistema
                </h1>
                <p class="text-sm text-gray-500 mt-1">Gestione los datos generales de la empresa y parámetros globales.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Datos de la Empresa</h3>
                        <span class="text-xs text-scantec-blue bg-blue-50 px-2 py-1 rounded border border-blue-100">
                            ID: <?php echo $config['id'] ?? '---'; ?>
                        </span>
                    </div>

                    <div class="p-6">
                        <form action="<?php echo base_url(); ?>configuracion/actualizar" method="post">
                            <input type="hidden" name="id" value="<?php echo $config['id'] ?? ''; ?>">

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

<?php pie() ?>