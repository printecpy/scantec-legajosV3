<?php encabezado() ?>
<div id="layoutSidenav_content">
    <?php if (isset($_GET['no_s'])) { ?>
    <div class="toast ml-auto mr-1 bg-danger text-white" id="alerta" role="alert" data-delay="3000"
        aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img src="<?php echo base_url(); ?>Assets/img/error.png" class="rounded mr-2" width="20">
            <strong class="mr-auto">Alerta</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            No hay expediente disponible intentelo en otro momento
        </div>
    </div>
    <?php } ?>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 p-2">
                    <?php if ($_SESSION['id_rol'] == 1) { ?>
                    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#prestar"><i
                            class="fas fa-plus-circle"></i></button>
                    <?php } ?>
                </div>
                <div class="col-md-12">
                    <table class="table" id="table">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Expediente</th>
                                <th>Funcionario</th>
                                <th>Especialidad</th>
                                <th>Fecha Salida</th>
                                <th>Fecha Devolución</th>
                             <!--    <th>Observación</th> -->
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['prestamo'] as $row) {
                                if ($row['estado'] == 1) {
                                    $estado = '<span class="badge-danger p-1 rounded">Prestado</span>';
                                } else {
                                    $estado = '<span class="badge-success p-1 rounded">Devuelto</span>';
                                }
                            ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['documento']; ?></td>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['especialidad']; ?></td>
                                <td><?php echo $row['fecha_prestamo']; ?></td>
                                <td><?php echo $row['fecha_devolucion']; ?></td>
                               <!--  <td><?php echo $row['observacion']; ?></td> -->
                                <td><?php echo $estado; ?></td>
                                <td>
                                    <?php if ($row['estado'] == 1 && $_SESSION['id_rol'] == 1) { ?>
                                    <form method="post" action="<?php echo base_url(); ?>admin/devolver"
                                        class="devolver">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button class="btn btn-primary" data-id="<?php echo $row['id']; ?>"
                                            type="submit"><i class="fas fa-plus-square"></i></button>
                                    </form>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="prestar" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="my-modal-title">Movimiento de expediente</h5>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="<?php echo base_url(); ?>admin/registrar">
                            <div class="form-group">
                                <label for="buscar_libro">Expediente</label><br>
                                <select id="buscar_libro" class="form-control" name="expediente">
                                    <?php foreach ($data['expediente'] as $expediente) { ?>
                                    <option value="<?php echo $expediente['id_expediente']; ?>">
                                        <?php echo $expediente['documento']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label for="funcionario">Funcionario</label><br>
                                        <select name="funcionario" id="funcionario">
                                            <?php foreach ($data['funcionario'] as $func) { ?>
                                            <option value="<?php echo $func['id']; ?>">
                                                <?php echo $func['documento'] . " - " . $func['nombre']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="especialidad">Especialidad</label><br>
                                        <select name="especialidad" id="especialidad">
                                            <?php foreach ($data['especialidad'] as $espec) { ?>
                                            <option value="<?php echo $espec['id_especialidad']; ?>">
                                                <?php echo $espec['id_especialidad'] . " - " . $espec['especialidad']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_prestamo">Fecha de Prestamo</label>
                                        <input id="fecha_prestamo" class="form-control" type="datetime-local"
                                            name="fecha_prestamo" value="<?php echo date("Y-m-d h-m-s"); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_devolucion">Fecha de Devolución</label>
                                        <input id="fecha_devolucion" class="form-control" type="date"
                                            name="fecha_devolucion" value=" ">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="observacion">Observación</label>
                                <textarea id="observacion" class="form-control" name="observacion" rows="3" value=" "></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">Registrar</button>
                            <button class="btn btn-danger" type="button" data-dismiss="modal">Cancelar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>