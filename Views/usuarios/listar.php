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

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Exportar:</span>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/pdf" 
                       class="flex items-center px-3 py-1.5 border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-bold">
                       <i class="fas fa-file-pdf mr-2"></i> PDF
                    </a>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/excel" 
                       class="flex items-center px-3 py-1.5 border border-green-200 text-green-600 rounded-lg hover:bg-green-50 transition-colors text-sm font-bold">
                       <i class="fas fa-file-excel mr-2"></i> Excel
                    </a>
                </div>
                
                <form action="<?php echo base_url(); ?>usuarios/importar" method="POST" enctype="multipart/form-data" class="flex items-center w-full md:w-auto">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="relative flex items-center w-full">
                        <label class="cursor-pointer bg-gray-100 text-gray-500 px-4 py-2 rounded-l-lg border border-r-0 border-gray-200 text-sm font-medium hover:bg-gray-200 transition-colors whitespace-nowrap">
                            <i class="fas fa-folder-open mr-2"></i> CSV
                            <input type="file" name="file" class="hidden" id="fileInput" accept=".csv, application/vnd.ms-excel" required onchange="updateFileName(this)">
                        </label>
                        <span id="fileName" class="px-4 py-2 border border-gray-200 text-gray-400 text-sm w-48 truncate bg-white">Seleccionar archivo...</span>
                        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-r-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-upload"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-container">
            <table class="scantec-table" id="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Grupo</th>
                        <th>Rol</th>
                        <th>Email</th>
                        <th class="text-center">Fuente</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['usuario'] as $usuario) { 
                        // Lógica PHP original intacta
                        $nombreRol = '---';
                        foreach ($data['roles'] as $rol) { if ($rol['id_rol'] == $usuario['id_rol']) { $nombreRol = $rol['descripcion']; break; } }
                        $nombreGrupo = '---';
                        foreach ($data['grupos'] as $grupo) { if ($grupo['id_grupo'] == $usuario['id_grupo']) { $nombreGrupo = $grupo['descripcion']; break; } }
                        $estadoActivo = ($usuario['estado_usuario'] == 'ACTIVO');
                        ?>
                    <tr>
                        <td>
                            <div class="flex items-center">                                
                                <div>
                                    <span class="block font-bold text-gray-800"><?php echo $usuario['nombre']; ?></span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="font-mono text-xs"><?php echo $usuario['usuario']; ?></td>
                        
                        <td><span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs border border-gray-200"><?php echo $nombreGrupo; ?></span></td>
                        
                        <td><span class="px-2 py-1 bg-blue-50 text-blue-600 rounded text-xs border border-blue-100 font-bold"><?php echo $nombreRol; ?></span></td>
                        
                        <td class="text-xs text-gray-500"><?php echo $usuario['email']; ?></td>

                        <td class="text-center">
                            <div class="flex flex-col items-center justify-center opacity-60">
                                <i class="fas fa-database text-[10px] mb-1"></i>
                                <span class="text-[9px] font-bold uppercase tracking-widest"><?php echo $usuario['fuente_registro']; ?></span>
                            </div>
                        </td>

                        <td class="text-center">
                            <?php if ($estadoActivo): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">Activo</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold border border-red-200">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <?php if ($estadoActivo): ?>
                                    <a href="<?php echo base_url() ?>Usuarios/editar?id=<?php echo $usuario['id']; ?>" 
                                       class="w-8 h-8 rounded-full bg-gray-50 text-scantec-blue flex items-center justify-center border border-gray-200 hover:bg-white hover:shadow-md transition-all" title="Editar">
                                       <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <form action="<?php echo base_url() ?>Usuarios/eliminar" method="post" class="inline eliminar">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center border border-red-100 hover:bg-white hover:shadow-md transition-all" title="Eliminar">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo base_url() ?>Usuarios/reingresar" method="post" class="inline reingresar">
                                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center border border-green-100 hover:bg-white hover:shadow-md transition-all" title="Reactivar">
                                            <i class="fas fa-undo text-xs"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

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
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Grupo</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-users text-gray-400"></i></div>
                                    <select id="id_grupo" name="id_grupo" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white">
                                        <?php foreach ($data['grupos'] as $grupo) { ?>
                                            <option value="<?php echo $grupo['id_grupo']; ?>"><?php echo $grupo['descripcion']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Rol</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-user-tag text-gray-400"></i></div>
                                    <select id="id_rol" name="id_rol" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none bg-white">
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
                                    <input type="password" id="clave" name="clave" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" placeholder="•••••••" required>
                                </div>
                                <p id="passwordError" class="text-red-500 text-xs mt-1 font-bold"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-red-500 uppercase mb-2">Repetir Contraseña</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i class="fas fa-lock text-gray-400"></i></div>
                                    <input type="password" id="claveConfirm" name="claveConfirm" class="pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 outline-none" placeholder="•••••••" required>
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
    // Función para abrir/cerrar modal Tailwind
    function toggleModal(modalID) {
        document.getElementById(modalID).classList.toggle("hidden");
    }

    // Actualizar nombre del archivo CSV
    function updateFileName(input) {
        const fileName = input.files[0] ? input.files[0].name : "Seleccionar archivo...";
        document.getElementById('fileName').innerText = fileName;
    }

    // Validación de Password (Igual que antes)
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
</script>

<?php if (isset($_SESSION['alert'])) { ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?php echo $_SESSION['alert']['type']; ?>',
            title: '<?php echo $_SESSION['alert']['message']; ?>',
            showConfirmButton: true,
            confirmButtonColor: '#182541',
            timer: 5000
        });
    });
</script>
<?php unset($_SESSION['alert']); } ?>