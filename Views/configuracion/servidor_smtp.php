<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-envelope mr-2"></i> Servidor de Correo (SMTP)
                </h1>
                <p class="text-sm text-gray-500 mt-1">Configuración del servidor de salida para alertas y
                    notificaciones.</p>
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
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Parámetros de Conexión</h3>

                        <?php 
                            $estado_smtp = '';
                            if (isset($data['smtp_datos']['estado'])) {
                                $estado_smtp = $data['smtp_datos']['estado'];
                            } elseif (isset($data['smtp_datos'][0]['estado'])) {
                                $estado_smtp = $data['smtp_datos'][0]['estado'];
                            }
                            $estado_smtp = strtolower(trim($estado_smtp));
                        ?>

                        <?php if($estado_smtp === 'activo'): ?>
                        <span
                            class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded border border-green-200 flex items-center">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span> ACTIVO
                        </span>
                        <?php else: ?>
                        <span
                            class="bg-red-100 text-red-800 text-xs font-bold px-2.5 py-0.5 rounded border border-red-200 flex items-center">
                            <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span> INACTIVO
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="p-6">
                        <form id="smtpForm" action="<?php echo base_url(); ?>configuracion/guardarServCorreo"
                            method="post">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <?php 
                                $valHost = $_SESSION['smtp_temp']['host'] ?? $data['smtp_datos']['host'] ?? '';
                                $valPort = $_SESSION['smtp_temp']['port'] ?? $data['smtp_datos']['port'] ?? '587';
                                $valUser = $_SESSION['smtp_temp']['username'] ?? $data['smtp_datos']['username'] ?? '';
                                $valPass = $_SESSION['smtp_temp']['password'] ?? $data['smtp_datos']['password'] ?? '';
                                $valSec  = $_SESSION['smtp_temp']['smtpsecure'] ?? $data['smtp_datos']['smtpsecure'] ?? '';
                                $valRem  = $_SESSION['smtp_temp']['remitente'] ?? $data['smtp_datos']['remitente'] ?? '';
                                $valNom  = $_SESSION['smtp_temp']['nombre_remitente'] ?? $data['smtp_datos']['nombre_remitente'] ?? '';
                            ?>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Host</label>
                                    <div class="relative">
                                        <i class="fa fa-server absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="text" name="host" required value="<?php echo $valHost; ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700"
                                            placeholder="mail.dominio.com">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Puerto</label>
                                    <input type="number" name="port" required value="<?php echo $valPort; ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Usuario
                                        (Login)</label>
                                    <div class="relative">
                                        <i class="fa fa-user absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="text" name="username" required value="<?php echo $valUser; ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Contraseña</label>
                                    <div class="relative">
                                        <i class="fa fa-key absolute left-3 top-3.5 text-gray-400"></i>
                                        <input type="password" name="password" required value="<?php echo $valPass; ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-8">
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Seguridad</label>
                                <select name="smtpsecure"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-scantec-blue outline-none text-sm text-gray-700">
                                    <option value="ssl" <?php echo ($valSec == 'ssl') ? 'selected' : ''; ?>>SSL (Puerto
                                        465)</option>
                                    <option value="tls" <?php echo ($valSec == 'tls') ? 'selected' : ''; ?>>TLS (Puerto
                                        587)</option>
                                    <option value="" <?php echo ($valSec == '') ? 'selected' : ''; ?>>Ninguna (Puerto
                                        25)</option>
                                </select>
                            </div>

                            <div class="bg-blue-50/50 rounded-xl p-5 border border-blue-100 mb-6">
                                <h4
                                    class="text-xs font-bold text-scantec-blue uppercase mb-4 border-b border-blue-200 pb-2">
                                    <i class="fa fa-id-card-alt mr-1"></i> Configuración de Remitente
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Correo
                                            Remitente</label>
                                        <input type="email" name="remitente" value="<?php echo $valRem; ?>"
                                            class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm"
                                            placeholder="Ej: alertas@printec.com.py">
                                        <p class="text-[10px] text-gray-500 mt-1">Lo que ven los clientes (From Email).
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nombre
                                            Remitente</label>
                                        <input type="text" name="nombre_remitente" value="<?php echo $valNom; ?>"
                                            class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue outline-none text-sm"
                                            placeholder="Ej: SCANTEC Alertas">
                                        <p class="text-[10px] text-gray-500 mt-1">Nombre visible (From Name).</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">

                                <?php if(isset($data['smtp_datos']['id'])): ?>
                                <a href="<?php echo base_url(); ?>configuracion/desactivar_servicio_smtp"
                                    class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wider flex items-center transition-colors confirm-delete">
                                    <i class="fa fa-power-off mr-1"></i> Desactivar Servicio
                                </a>
                                <?php else: ?>
                                <div></div>
                                <?php endif; ?>

                                <div class="flex space-x-3">
                                    <button type="button" id="btnProbarConexion"
                                        class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-all shadow-sm">
                                        <i class="fa fa-plug mr-2"></i> TEST CONEXIÓN
                                    </button>

                                    <button type="button" id="btnGuardarConfig"
                                        class="px-8 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm hover:bg-blue-800 transition-all shadow-md hover:shadow-lg">
                                        <i class="fa fa-save mr-2"></i> GUARDAR
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-100">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">
                            <i class="fa fa-paper-plane mr-2 text-gray-500"></i> Test de Envío Real
                        </h3>
                    </div>

                    <div class="p-6">
                        <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                            Prueba el envío usando la configuración <b>ACTIVA</b> guardada en base de datos.
                        </p>

                        <form action="<?php echo base_url(); ?>configuracion/enviarCorreo" method="post">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Destinatario</label>
                                    <input type="email" name="destinatario" required
                                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-gray-400 outline-none text-sm"
                                        placeholder="tu.correo@empresa.com">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Asunto</label>
                                    <input type="text" name="asunto" required value="Prueba Scantec"
                                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-gray-400 outline-none text-sm">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Mensaje</label>
                                    <textarea name="mensaje" rows="3" required
                                        class="w-full px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-gray-400 outline-none text-sm">Este es un correo de prueba enviado desde Scantec.</textarea>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full mt-6 py-3 rounded-xl bg-gray-600 text-white font-bold text-sm hover:bg-gray-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fa fa-rocket mr-2"></i> ENVIAR PRUEBA REAL
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<?php pie() ?>

<?php 
unset($_SESSION['smtp_temp']); 
if (isset($_SESSION['alert'])): ?>
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
// Confirmación para desactivar
document.querySelectorAll('.confirm-delete').forEach(function(element) {
    element.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "El sistema dejará de enviar correos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});

document.getElementById("btnProbarConexion").addEventListener("click", function() {
    const form = document.getElementById("smtpForm");
    if (form.reportValidity()) {
        form.action = "<?php echo base_url(); ?>configuracion/probar_smtp";
        form.submit();
    }
});

document.getElementById("btnGuardarConfig").addEventListener("click", function() {
    const form = document.getElementById("smtpForm");
    if (form.reportValidity()) {
        form.action = "<?php echo base_url(); ?>configuracion/guardarServCorreo";
        form.submit();
    }
});
</script>