<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main class="bg-gray-50/50 min-h-screen">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                <i class="fas fa-fingerprint text-xl"></i>
                            </div>
                            Marcaciones de Funcionarios
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Importa el archivo del reloj biométrico y visualiza las asistencias.</p>
                    </div>
                    
                    <form action="#" method="POST" enctype="multipart/form-data" class="flex items-center gap-3" onsubmit="event.preventDefault(); alert('Simulación: Archivo Excel importado correctamente.');">
                        <label class="block">
                            <span class="sr-only">Elegir archivo Excel</span>
                            <input type="file" name="archivo_excel" accept=".xlsx, .xls, .csv" required
                                class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2.5 file:px-4
                                file:rounded-xl file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 transition-all cursor-pointer shadow-sm"/>
                        </label>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-md shadow-blue-600/30 flex items-center gap-2">
                            <i class="fas fa-cloud-upload-alt"></i> Importar
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="table">
                        <thead>
                            <tr class="bg-slate-800 border-b border-slate-900">
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Funcionario</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">C.I.</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Hora</th>
                                <th class="px-6 py-4 text-xs font-bold text-slate-200 uppercase tracking-wider">Tipo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            // =========================================================
                            // 🚀 DATOS FICTICIOS PARA LA PRESENTACIÓN
                            // =========================================================
                            $datosFicticios = [
                                ['nombre' => 'Aldo Silva', 'ci' => '5305984', 'fecha' => '26/02/2026', 'hora' => '07:41:05', 'tipo' => 'FP'],
                                ['nombre' => 'María González', 'ci' => '4102938', 'fecha' => '26/02/2026', 'hora' => '07:45:12', 'tipo' => 'FP'],
                                ['nombre' => 'Juan Pérez', 'ci' => '3981245', 'fecha' => '26/02/2026', 'hora' => '07:50:33', 'tipo' => 'FP'],
                                ['nombre' => 'Laura Martínez', 'ci' => '4567891', 'fecha' => '26/02/2026', 'hora' => '08:05:10', 'tipo' => 'PWD'], // Llegada tardía con PIN
                                ['nombre' => 'Aldo Silva', 'ci' => '5305984', 'fecha' => '25/02/2026', 'hora' => '17:44:37', 'tipo' => 'FP'], // Salida día anterior
                                ['nombre' => 'María González', 'ci' => '4102938', 'fecha' => '25/02/2026', 'hora' => '17:35:00', 'tipo' => 'FP'],
                                ['nombre' => 'Carlos Giménez', 'ci' => '2345678', 'fecha' => '25/02/2026', 'hora' => '18:10:22', 'tipo' => 'FP'],
                            ];

                            foreach ($datosFicticios as $mar) { 
                                // Lógica de colores condicionales para la hora
                                // Si entra después de las 08:00, lo marcamos en rojo/naranja suave
                                $horaLlegada = strtotime($mar['hora']);
                                $horaLimite = strtotime('08:00:00');
                                $esLlegada = (strtotime($mar['hora']) < strtotime('12:00:00')); // Asumimos que am es llegada
                                
                                $colorHora = 'text-green-600';
                                if ($esLlegada && $horaLlegada > $horaLimite) {
                                    $colorHora = 'text-red-500'; // Llegada tardía
                                } elseif (!$esLlegada) {
                                    $colorHora = 'text-blue-600'; // Hora de salida
                                }
                            ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-800 whitespace-nowrap">
                                        <i class="fas fa-user-circle text-gray-400 mr-2"></i><?php echo $mar['nombre']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600 whitespace-nowrap">
                                        <?php echo $mar['ci']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap">
                                        <i class="far fa-calendar-alt mr-1 opacity-50 text-blue-500"></i> <?php echo $mar['fecha']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold <?php echo $colorHora; ?> whitespace-nowrap">
                                        <i class="far fa-clock mr-1 opacity-50"></i> <?php echo $mar['hora']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-gray-700" title="Tipo de registro">
                                            <?php 
                                            // Icono dependiendo del tipo (FP = Huella, PWD = Contraseña)
                                            if($mar['tipo'] == 'FP') {
                                                echo '<i class="fas fa-fingerprint text-blue-500 mr-1.5"></i> Huella';
                                            } else {
                                                echo '<i class="fas fa-keyboard text-gray-500 mr-1.5"></i> PIN';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
    <?php pie() ?>
</div>