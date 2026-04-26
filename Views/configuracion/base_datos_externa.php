<?php
$config = is_array($data['db_usuarios'] ?? null) ? $data['db_usuarios'] : [];
$resultado = is_array($data['db_usuarios_resultado'] ?? null) ? $data['db_usuarios_resultado'] : null;
$habilitada = ($config['enabled'] ?? '0') === '1';
encabezado();
?>

<main class="app-content bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-database mr-2"></i> Base de Datos Externa
                </h1>
                <p class="text-sm text-gray-500 mt-1">Configure la conexion de usuarios externos, pruebe la tabla y guarde los parametros directamente en <code>.env</code>.</p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="text-gray-400 hover:text-scantec-blue transition-colors">
                <i class="fa fa-arrow-left text-xl"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Parametros de Conexion y Mapeo</h3>
                        <?php if ($habilitada): ?>
                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded border border-green-200 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span> HABILITADA
                        </span>
                        <?php else: ?>
                        <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-0.5 rounded border border-gray-200 flex items-center">
                            <span class="w-2 h-2 bg-gray-400 rounded-full mr-1.5"></span> DESHABILITADA
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <form id="dbUsuariosForm" action="<?php echo base_url(); ?>configuracion/guardar_base_datos_externa" method="post">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                            <input type="hidden" name="enabled" value="0">

                            <div class="mb-6">
                                <label class="inline-flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-300 bg-white cursor-pointer select-none">
                                    <input type="checkbox" name="enabled" value="1" class="w-4 h-4 rounded border-gray-300 text-scantec-blue focus:ring-scantec-blue"
                                        <?php echo $habilitada ? 'checked' : ''; ?>>
                                    <span class="text-sm font-semibold text-gray-700">Usar base de datos externa para Legajos</span>
                                </label>
                                <p class="text-[11px] text-gray-500 mt-2">Si la desactiva, Legajos vuelve a la fuente interna sin borrar la configuracion del <code>.env</code>.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Host</label>
                                    <div class="relative">
                                        <i class="fa fa-server absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="text" name="host" value="<?php echo htmlspecialchars($config['host'] ?? 'localhost'); ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                            placeholder="localhost o IP del servidor">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Puerto</label>
                                    <input type="number" name="port" min="1" max="65535" value="<?php echo htmlspecialchars($config['port'] ?? '3306'); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Base de datos</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($config['name'] ?? ''); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                        placeholder="usuarios">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Tabla</label>
                                    <input type="text" name="table" value="<?php echo htmlspecialchars($config['table'] ?? 'usuarios_datos'); ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                        placeholder="usuarios_datos">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Usuario</label>
                                    <div class="relative">
                                        <i class="fa fa-user absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="text" name="user" value="<?php echo htmlspecialchars($config['user'] ?? ''); ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Contrasena</label>
                                    <div class="relative">
                                        <i class="fa fa-key absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="password" name="password"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-1">Si deja este campo vacio, se usa la clave actual guardada en <code>.env</code>.</p>
                                </div>
                            </div>

                            <div class="bg-blue-50/60 rounded-xl p-5 border border-blue-100 mb-6">
                                <h4 class="text-xs font-bold text-scantec-blue uppercase mb-4 border-b border-blue-200 pb-2">
                                    <i class="fa fa-random mr-1"></i> Mapeo de Campos
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo ID</label>
                                        <input type="text" name="field_id" value="<?php echo htmlspecialchars($config['field_id'] ?? 'id'); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo CI</label>
                                        <input type="text" name="field_ci" value="<?php echo htmlspecialchars($config['field_ci'] ?? 'nro_cedula'); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo Nombre</label>
                                        <input type="text" name="field_nombre" value="<?php echo htmlspecialchars($config['field_nombre'] ?? 'nombre'); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo Apellido</label>
                                        <input type="text" name="field_apellido" value="<?php echo htmlspecialchars($config['field_apellido'] ?? 'apellido'); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo Nombre completo</label>
                                        <input type="text" name="field_nombre_completo" value="<?php echo htmlspecialchars($config['field_nombre_completo'] ?? ''); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                            placeholder="Dejar vacio si se arma con apellido + nombre">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Campo Solicitud</label>
                                        <input type="text" name="field_solicitud" value="<?php echo htmlspecialchars($config['field_solicitud'] ?? ''); ?>"
                                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                            placeholder="Opcional">
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                <button type="button" id="btnProbarDbUsuarios"
                                    class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                                    <i class="fa fa-plug mr-2"></i> PROBAR CONEXION
                                </button>

                                <button type="button" id="btnGuardarDbUsuarios"
                                    class="px-8 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm hover:bg-blue-800 transition-all shadow-md hover:shadow-lg">
                                    <i class="fa fa-save mr-2"></i> GUARDAR EN .ENV
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-100">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">
                            <i class="fa fa-stethoscope mr-2 text-gray-500"></i> Estado de la Conexion
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php if ($resultado): ?>
                        <div class="rounded-xl border px-4 py-4 text-sm <?php echo !empty($resultado['ok']) ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
                            <div class="font-bold uppercase text-xs tracking-wider mb-2"><?php echo !empty($resultado['ok']) ? 'Resultado OK' : 'Resultado con Error'; ?></div>
                            <p class="leading-relaxed"><?php echo htmlspecialchars((string)($resultado['message'] ?? '')); ?></p>
                        </div>
                        <?php else: ?>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600">
                            Use <strong>Probar conexion</strong> para validar acceso a la base, existencia de la tabla y nombres de columnas.
                        </div>
                        <?php endif; ?>

                        <div class="mt-5 text-xs text-gray-500 space-y-2">
                            <p><strong class="text-gray-700">Importante:</strong> si no existe un campo de nombre completo, deje ese valor vacio y complete Nombre + Apellido.</p>
                            <p><strong class="text-gray-700">Legajos</strong> siempre busca por CI y arma el nombre con <code>apellido + nombre</code> cuando corresponde.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
                    <h4 class="font-bold text-scantec-blue text-sm mb-3">Claves que se actualizan</h4>
                    <ul class="text-xs text-gray-600 space-y-2 list-disc pl-4">
                        <li><code>SCANTEC_DB_USUARIOS_ENABLED</code></li>
                        <li><code>SCANTEC_DB_USUARIOS_HOST</code>, <code>PORT</code>, <code>NAME</code>, <code>USER</code>, <code>PASSWORD</code></li>
                        <li><code>SCANTEC_DB_USUARIOS_TABLE</code> y los campos de mapeo para ID, CI, Nombre, Apellido, Nombre completo y Solicitud</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php pie() ?>

<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: '<?php echo $_SESSION['alert']['type']; ?>',
        title: '<?php echo $_SESSION['alert']['message']; ?>',
        confirmButtonColor: '#1d4ed8'
    });
});
</script>
<?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<script>
document.getElementById('btnProbarDbUsuarios').addEventListener('click', function() {
    const form = document.getElementById('dbUsuariosForm');
    form.action = "<?php echo base_url(); ?>configuracion/probar_base_datos_externa";
    if (form.reportValidity()) {
        form.submit();
    }
});

document.getElementById('btnGuardarDbUsuarios').addEventListener('click', function() {
    const form = document.getElementById('dbUsuariosForm');
    form.action = "<?php echo base_url(); ?>configuracion/guardar_base_datos_externa";
    if (form.reportValidity()) {
        form.submit();
    }
});
</script>
