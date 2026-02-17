<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">          
            <h4 class="text-center">Historial de Envíos</h4>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>usuarios/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>             
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr class="small">
                                    <th>Nombre</th>
                                    <th>Usuario</th>
                                    <th>Grupo</th>
                                    <th>Rol</th>
                                    <th>Email</th>
                                    <th>Origen registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['usuario'] as $usuario) {
                                     $nombreRol = '';
                                     foreach ($data['roles'] as $rol) {
                                         if ($rol['id_rol'] == $usuario['id_rol']) {
                                             $nombreRol = $rol['descripcion'];
                                             break;
                                         }
                                     }
                                     $nombreGrupo = '';
                                     foreach ($data['grupos'] as $grupo) {
                                         if ($grupo['id_grupo'] == $usuario['id_grupo']) {
                                             $nombreGrupo = $grupo['descripcion'];
                                             break;
                                         }
                                     }
                                     ?>
                                <tr class="small">
                                    <td class="small"><?php echo $usuario['nombre']; ?></td>
                                    <td class="small"><?php echo $usuario['usuario']; ?></td>
                                    <td class="small"><?php echo $nombreGrupo; ?></td>
                                    <td class="small"><?php echo $nombreRol; ?></td>
                                    <td class="small"><?php echo $usuario['email']; ?></td>
                                    <td class="small"><?php echo $usuario['fuente_registro']; ?></td>
                                    <td>
                                        <?php if ($usuario['estado_usuario'] == 'ACTIVO'): ?>
                                        <a href="<?php echo base_url() ?>Usuarios/editar?id=<?php echo $usuario['id']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form action="<?php echo base_url() ?>Usuarios/eliminar" method="post"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" class="btn-icon btn-icon-delete"
                                                title="Anular registro"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php else: ?>
                                        <form action="<?php echo base_url() ?>Usuarios/reingresar" method="post"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
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