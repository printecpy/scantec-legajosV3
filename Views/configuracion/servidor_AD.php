<?php encabezado() ?>

<main class="app-content bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-montserrat font-bold text-scantec-blue uppercase tracking-wide">
                    <i class="fa fa-users mr-2"></i> Servidor LDAP / Active Directory
                </h1>
                <p class="text-sm text-gray-500 mt-1">Configure la conexión para autenticación centralizada de usuarios.
                </p>
            </div>
            <a href="#" onclick="window.history.back(); return false;"
                class="text-gray-400 hover:text-scantec-blue transition-colors">
                <i class="fa fa-arrow-left text-xl"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider">Parámetros de Conexión</h3>
                    </div>

                    <div class="p-6">
                        <form id="ldapForm" action="<?php echo base_url(); ?>configuracion/probar_conexionAD"
                            method="post">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="mb-5">
                                <label for="ldapHost"
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Dirección
                                    Host (IP o Dominio)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa fa-server text-gray-400"></i>
                                    </div>
                                    <input type="text" id="ldapHost" name="ldapHost" required
                                        value="<?= $_SESSION['ldap_data']['ldapHost'] ?? '' ?>"
                                        class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm"
                                        placeholder="Ej: ldap://192.168.1.5">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 ml-1">Use el prefijo <code>ldap://</code> o
                                    <code>ldaps://</code> seguido de la IP.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                                <div>
                                    <label for="ldapPort"
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Puerto</label>
                                    <input type="number" id="ldapPort" name="ldapPort" required
                                        value="<?= $_SESSION['ldap_data']['ldapPort'] ?? '389' ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm">
                                    <p class="text-[10px] text-gray-400 mt-1 ml-1">Default: 389 (Sin SSL) o 636 (SSL).
                                    </p>
                                </div>

                                <div>
                                    <label for="ldapBaseDn"
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Base
                                        DN</label>
                                    <input type="text" id="ldapBaseDn" name="ldapBaseDn" required
                                        value="<?= $_SESSION['ldap_data']['ldapBaseDn'] ?? '' ?>"
                                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm"
                                        placeholder="DC=empresa,DC=local">
                                    <p class="text-[10px] text-gray-400 mt-1 ml-1">Ruta base para buscar usuarios.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <label for="ldapUser"
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Usuario
                                        Administrador</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-user text-gray-400"></i>
                                        </div>
                                        <input type="text" id="ldapUser" name="ldapUser" required
                                            value="<?= $_SESSION['ldap_data']['ldapUser'] ?? '' ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm"
                                            placeholder="usuario@dominio.local">
                                    </div>
                                </div>

                                <div>
                                    <label for="ldapPass"
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Contraseña</label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa fa-key text-gray-400"></i>
                                        </div>
                                        <input type="password" id="ldapPass" name="ldapPass" required
                                            value="<?= $_SESSION['ldap_data']['ldapPass'] ?? '' ?>"
                                            class="pl-10 w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                                <button type="button" id="probarConexion"
                                    class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 hover:text-gray-800 transition-all shadow-sm">
                                    <i class="fa fa-plug mr-2"></i> Probar Conexión
                                </button>

                                <button type="button" id="registrarLdap"
                                    class="px-6 py-2.5 rounded-xl bg-scantec-blue text-white font-bold text-sm hover:bg-blue-800 transition-all shadow-md hover:shadow-lg">
                                    <i class="fa fa-save mr-2"></i> GUARDAR CONFIGURACIÓN
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4 border-b pb-2">
                        <i class="fa fa-refresh mr-2 text-scantec-blue"></i> Sincronización
                    </h3>

                    <p class="text-xs text-gray-500 mb-4">
                        Seleccione una configuración guardada para importar usuarios del directorio activo al sistema
                        local.
                    </p>

                    <form action="<?php echo base_url(); ?>usuarios/sincronizarAD" method="POST">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Configuración
                                Activa</label>
                            <select id="id" name="id"
                                class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 text-sm text-gray-700 focus:ring-2 focus:ring-scantec-blue outline-none">
                                <?php if (!empty($data['LDAP_datos'])) {
                                    foreach ($data['LDAP_datos'] as $LDAP_datos) { ?>

                                        <option value="<?php echo $LDAP_datos['id']; ?>">
                                            <?php
                                            echo $LDAP_datos['ldapHost'] . " (" . $LDAP_datos['ldapBaseDn'] . ")";
                                            ?>
                                        </option>

                                    <?php }
                                } else { ?>
                                    <option disabled selected>No hay servidores configurados</option>
                                <?php } ?>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md flex items-center justify-center">
                            <i class="fa fa-cloud-download mr-2"></i> SINCRONIZAR AHORA
                        </button>
                    </form>
                </div>

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-700 text-sm uppercase tracking-wider mb-4 border-b pb-2">
                        <i class="fa fa-id-badge mr-2 text-scantec-blue"></i> Formato de usuario (opcional)
                    </h3>

                    <p class="text-xs text-gray-500 mb-4">
                        Cómo se arma el nombre que se envía al servidor cuando alguien inicia sesión con su usuario del
                        directorio. Si se deja vacío, se usa <code>DOMINIO\usuario</code>, tomando el primer
                        <code>DC=</code> del Base DN como dominio.
                    </p>

                    <?php if (!empty($data['LDAP_datos'])) { ?>
                        <form action="<?php echo base_url(); ?>configuracion/guardar_formato_ldap" method="POST">
                            <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="mb-4">
                                <label
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Servidor</label>
                                <select id="idFormatoLdap" name="id"
                                    class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 text-sm text-gray-700 focus:ring-2 focus:ring-scantec-blue outline-none">
                                    <?php foreach ($data['LDAP_datos'] as $i => $LDAP_fila) { ?>
                                        <option value="<?= intval($LDAP_fila['id']); ?>"
                                            data-formato="<?= htmlspecialchars((string) ($LDAP_fila['ldapFormatoUsuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($LDAP_fila['ldapHost'] . ' (' . $LDAP_fila['ldapBaseDn'] . ')', ENT_QUOTES, 'UTF-8'); ?><?= $i === 0 ? ' - usado para iniciar sesión' : ''; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label for="ldapFormatoUsuario"
                                    class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Formato</label>
                                <input type="text" id="ldapFormatoUsuario" name="ldapFormatoUsuario" maxlength="255"
                                    value="<?= htmlspecialchars((string) ($data['LDAP_datos'][0]['ldapFormatoUsuario'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all text-gray-700 text-sm"
                                    placeholder="Ej: Nombre NetBios del dominio\{usuario}">
                            </div>
                            <p class="text-[10px] text-gray-400 mb-4 ml-1">
                                Use <code>{usuario}</code> donde va el usuario. Ejemplos:
                                <code>Dominio\{usuario}</code> (AD con nombre NetBIOS),
                                <code>{usuario}</code> (solo el usuario),
                                <code>{usuario}@empresa.com</code> (UPN),
                                <code>uid={usuario_dn},ou=personas,dc=empresa,dc=com</code> (LDAP que no es AD).
                            </p>

                            <button type="submit"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md flex items-center justify-center">
                                <i class="fa fa-save mr-2"></i> GUARDAR FORMATO
                            </button>
                        </form>
                    <?php } else { ?>
                        <p class="text-xs text-gray-400">Guarde primero la configuración de un servidor.</p>
                    <?php } ?>
                </div>

                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
                    <h4 class="font-bold text-scantec-blue text-sm mb-2">¿Cómo funciona?</h4>
                    <ul class="text-xs text-gray-600 space-y-2 list-disc pl-4">
                        <li>Asegúrese de que el servidor web tenga acceso al puerto del AD (389/636).</li>
                        <li>El usuario administrador debe tener permisos de lectura en el árbol del directorio.</li>
                        <li>Utilice <strong>Probar Conexión</strong> antes de guardar para verificar las credenciales.
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</main>

<?php pie() ?>

<?php
if (isset($_SESSION['alert'])) {
    $alertType = $_SESSION['alert']['type'];
    $alertMessage = $_SESSION['alert']['message'];

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '$alertType',
                title: '$alertMessage',
                showConfirmButton: true,
                confirmButtonColor: '#1d4ed8',
                timer: 5000,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    title: 'font-sans text-lg'
                }
            });
        });
    </script>";
    unset($_SESSION['alert']);
}
?>

<script>
    // Lógica para cambiar el action del formulario según el botón presionado
    document.getElementById("probarConexion").addEventListener("click", function () {
        const form = document.getElementById("ldapForm");
        form.action = "<?php echo base_url(); ?>configuracion/probar_conexionAD";
        // Validación HTML5 manual antes de enviar
        if (form.reportValidity()) {
            form.submit();
        }
    });

    document.getElementById("registrarLdap").addEventListener("click", function () {
        const form = document.getElementById("ldapForm");
        form.action = "<?php echo base_url(); ?>configuracion/saveLDAP_server";
        if (form.reportValidity()) {
            form.submit();
        }
    });
     // Al cambiar de servidor, se muestra el formato guardado para ese servidor
    var selFormatoLdap = document.getElementById("idFormatoLdap");
    if (selFormatoLdap) {
        selFormatoLdap.addEventListener("change", function () {
            document.getElementById("ldapFormatoUsuario").value = this.options[this.selectedIndex].getAttribute("data-formato") || "";
        });
    }
</script>