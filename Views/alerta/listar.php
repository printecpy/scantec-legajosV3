<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">          
            <h4 class="text-center">Gestor de Alertas Programadas</h4>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <button title="Registrar" class="btn-icon btn-icon-new" type="button" data-toggle="modal"
                        data-target="#nuevo_user"><i class="fas fa-tasks"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>                   
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <h5 class="text-center">Alertas activas</h5>
                            <thead class="thead">
                                <tr class="small">
                                    <th class="small">Nombre</th>
                                    <th class="small">Usuario</th>
                                    <th class="small">Email</th>
                                    <th class="small">Origen registro</th>
                                    <th class="small">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['alerts'] as $alerta) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $alerta['nombre_tarea']; ?></td>
                                    <td class="small"><?php echo $alerta['tipo_informe']; ?></td>
                                    <td class="small"><?php echo $alerta['frecuencia']; ?></td>
                                    <td class="small"><?php echo $alerta['fecha_proxima_ejecucion']; ?></td>
                                    <td>
                                        <?php if ($alerta['estado'] == 'activo'): ?>
                                        <a href="<?php echo base_url() ?>Usuarios/editar?id=<?php echo $alerta['id']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form action="<?php echo base_url() ?>Usuarios/eliminar" method="post"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $alerta['id']; ?>">
                                            <button type="submit" class="btn-icon btn-icon-delete"
                                                title="Anular registro"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php else: ?>
                                        <form action="<?php echo base_url() ?>Usuarios/reingresar" method="post"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $alerta['id']; ?>">
                                            <button type="submit" class="btn-icon btn-icon-reingres"
                                                title="Reingresar registro"><i
                                                    class="fas fa-audio-description"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div id="nuevo_user" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Nuevo Usuario</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="registroForm" method="post" action="<?php echo base_url(); ?>Usuarios/insertar"
                    autocomplete="off">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nombre">Nombre</label>
                            <input id="nombre" class="form-control" type="text" name="nombre" placeholder="Nombre"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="usuario">Usuario</label>
                                    <input id="usuario" class="form-control" type="text" name="usuario"
                                        placeholder="Usuario" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="clave">Contraseña</label>
                                    <input id="clave" class="form-control" type="password" name="clave"
                                        placeholder="Contraseña" required>
                                    <div id="passwordError" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="claveConfirm">Confirmar Contraseña</label>
                                    <input id="claveConfirm" class="form-control" type="password" name="claveConfirm"
                                        placeholder="Confirmar Contraseña" required>
                                    <div id="passwordConfirmError" class="text-danger"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label for="id_grupo">Grupo</label>
                                <select id="id_grupo" class="form-control" name="id_grupo">
                                    <?php foreach ($data['grupos'] as $grupo) { ?>
                                    <option value="<?php echo $grupo['id_grupo']; ?>">
                                        <?php echo $grupo['id_grupo'] . " - " . $grupo['descripcion']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="id_rol">Rol</label>
                                <select id="id_rol" class="form-control" name="id_rol">
                                    <?php foreach ($data['roles'] as $roles) { ?>
                                    <option value="<?php echo $roles['id_rol']; ?>">
                                        <?php echo $roles['id_rol'] . " - " . $roles['descripcion']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="email">Correo</label>
                                    <input id="email" class="form-control" type="text" name="email"
                                        placeholder="Ingrese su email" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-icon btn-icon-registrar" id="btnEnviar" type="submit">Registrar</button>
                        <button class="btn-icon btn-icon-cancelar" type="button" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
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