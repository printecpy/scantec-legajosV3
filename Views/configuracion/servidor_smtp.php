<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mt-3">
                        <div class="card-header">
                            Servidor SMTP
                            <div class="row mt-3">
                                <a class="btn-icon btn-icon-volver" title="Volver" href="#"
                                    onclick="window.history.back(); return false;"><i class="fas fa-arrow-left"></i></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo base_url(); ?>configuracion/guardarServCorreo" method="post">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="host">Servidor de Correo (Host):</label><br>
                                            <input value="<?php echo $data['smtp_datos']['host']; ?>" type="text"
                                                id="host" name="host" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="username">Nombre de Usuario:</label><br>
                                            <input type="text" id="username" name="username" required
                                                value="<?php echo $data['smtp_datos']['username']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="password">Contraseña:</label><br>
                                            <input type="password" id="password" name="password" required
                                                value="<?php echo $data['smtp_datos']['password']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="smtpsecure">Seguridad SMTP:</label><br>
                                            <select id="smtpsecure" name="smtpsecure" required>
                                                <option value="tls">TLS</option>
                                                <option value="ssl">SSL</option>
                                                <option value="">Ninguna</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="port">Puerto:</label><br>
                                            <input type="text" id="port" name="port" required
                                                value="<?php echo $data['smtp_datos']['port']; ?>">
                                        </div>
                                    </div>

                                </div>
                                <input type="submit" style="background-color: #878787;" value="Guardar">
                            </form>
                            <form action="<?php echo base_url(); ?>configuracion/enviarCorreo" method="post">
                                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="mensaje">Mensaje:</label><br>
                                            <input type="text" id="mensaje" name="mensaje" required
                                                placeholder="mensaje">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="isSMTP">Asunto:</label><br>
                                            <input type="text" id="asunto" name="asunto" required placeholder="asunto">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label for="isSMTP">Destinatario:</label><br>
                                            <input type="text" id="destinatario" name="destinatario" required
                                                placeholder="destinatario">
                                        </div>
                                    </div>
                                </div>
                                <input type="submit" style="background-color: #878787;" value="Probar correo">
                            </form>

                        </div>
                    </div>
                </div>
            </div>
    </main>

    <?php pie() ?>