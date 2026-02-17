<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mt-3">
                        <div class="card-header">
                            Servidor LDAP
                            <div class="row mt-3">
                                <a class="btn-icon btn-icon-volver" title="Volver" href="#"
                                    onclick="window.history.back(); return false;"><i class="fas fa-arrow-left"></i></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="ldapForm" action="<?php echo base_url(); ?>configuracion/probar_conexionAD"
                                method="post">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="row">
                                    <div class="col-lg-10">
                                        <label for="ldapHost">Dirección Host LDAP</label>
                                        <input type="text" id="ldapHost" name="ldapHost" required
                                            style="font-size: smaller;" value="<?= $_SESSION['ldap_data']['ldapHost'] ?? '' ?>">
                                        <label class="text-small"
                                            style="font-style:italic; font-size: smaller;">Ejemplo:
                                            "ldap://direccion_ip_del_servidor"</label>
                                    </div>

                                    <div class="col-lg-8">
                                        <label for="ldapPort">Puerto LDAP</label>
                                        <input type="number" id="ldapPort" name="ldapPort" required
                                            style="font-size: smaller;" value="<?= $_SESSION['ldap_data']['ldapPort'] ?? '' ?>">
                                        <label class="text-small" style="font-style:italic; font-size: smaller;">Puerto
                                            predeterminado "389"</label>
                                    </div>

                                    <div class="col-lg-8">
                                        <label for="ldapUser">Usuario LDAP</label>
                                        <input type="text" id="ldapUser" name="ldapUser" required
                                            style="font-size: smaller;" value="<?= $_SESSION['ldap_data']['ldapUser'] ?? '' ?>">
                                        <label class="text-small" style="font-style:italic; font-size: smaller;">Usuario
                                            para sincronizar con servidor</label>
                                    </div>

                                    <div class="col-lg-8">
                                        <label for="ldapPass">Contraseña LDAP</label>
                                        <input type="password" id="ldapPass" name="ldapPass" required
                                            style="font-size: smaller;" value="<?= $_SESSION['ldap_data']['ldapPass'] ?? '' ?>">
                                        <label class="text-small"
                                            style="font-style:italic; font-size: smaller;">Contraseña del usuario para
                                            sincronizar con servidor</label>
                                    </div>

                                    <div class="col-lg-8">
                                        <label for="ldapBaseDn">Base DN LDAP</label>
                                        <input type="text" id="ldapBaseDn" name="ldapBaseDn" required
                                            style="font-size: smaller;" value="<?= $_SESSION['ldap_data']['ldapBaseDn'] ?? '' ?>">
                                        <label class="text-small"
                                            style="font-style:italic; font-size: smaller;">Ejemplo:
                                            "OU=dato,dc=dato,dc=dato"</label>
                                    </div>

                                    <div class="col-lg-4 mt-3">
                                        <button type="button" id="probarConexion" class="btn btn-sm"
                                            style="background-color: #878787;">
                                            Probar Conexión
                                        </button>
                                        <button type="submit" id="registrarLdap" class="btn btn-sm"
                                            style="background-color: #878787;">
                                            Registrar
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="col-lg-5 mb-2">
                                <form action="<?php echo base_url(); ?>usuarios/sincronizarAD" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <div class="mb-2">
                                        <label for="id">Seleccionar configuracion LDAP</label>
                                        <select id="id" class="form-control" name="id">
                                            <?php foreach ($data['LDAP_datos'] as $LDAP_datos) { ?>
                                            <option value="<?php echo $LDAP_datos['id']; ?>">
                                                <?php echo $LDAP_datos['ldapHost'] . " - " . $LDAP_datos['ldapBaseDn']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button class="btn-icon btn-icon-modificar mb-2" type="submit">Sincronizar
                                        LDAP</button>
                                </form>
                            </div>
                        </div>
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
        // Estilo específico por tipo de alerta
        $alertTitleClass = 'swal-title';
        $alertIconClass = "swal-icon-$alertType";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$alertType',
                    title: '$alertMessage',
                    showConfirmButton: true,
                    timer: 5000,
                    customClass: {
                        title: '$alertTitleClass',
                        icon: '$alertIconClass'
                    }
                });
            });
        </script>";
        unset($_SESSION['alert']); 
    }
    ?>
    <script>
    document.getElementById("probarConexion").addEventListener("click", function() {
        document.getElementById("ldapForm").action = "<?php echo base_url(); ?>configuracion/probar_conexionAD";
        document.getElementById("ldapForm").submit();
    });

    document.getElementById("registrarLdap").addEventListener("click", function() {
        document.getElementById("ldapForm").action = "<?php echo base_url(); ?>configuracion/saveLDAP_server";
        document.getElementById("ldapForm").submit();
    });
    </script>