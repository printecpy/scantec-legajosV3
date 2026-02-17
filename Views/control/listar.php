<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Control de expedientes</h4>
                    <button title="Registrar" class="btn-icon btn-icon-new" type="button" data-toggle="modal"
                        data-target="#nuevoControl"><i class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>control/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>control/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>
                    <a href="<?php echo base_url(); ?>control/reporte" title="Ver Reporte"
                        class="btn-icon btn-icon-reporte"><i class="fas fa-chart-bar"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Pag. Controladas</th>
                                    <th>Exp. Controladas</th>
                                    <th>Solicitado</th>
                                    <th>Reescaneo</th>
                                    <th>Est. de trabajo</th>
                                    <th>Usuario</th>
                                    <th>Operador</th>
                                    <th>Estado</th>
                                    <th> </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['controlar'] as $controlar) {
                                ?>
                                <tr>
                                    <td class="small"><?php echo $controlar['fecha']; ?></td>
                                    <td class="small">
                                        <?php echo number_format($controlar['pag_control'], 0, ',', '.'); ?></td>
                                    <td class="small"><?php echo $controlar['exp_control']; ?></td>
                                    <td class="small"><?php echo $controlar['solicitado']; ?></td>
                                    <td class="small"><?php echo $controlar['exp_reescaneo']; ?></td>
                                    <td class="small"><?php echo $controlar['nombre_pc']; ?></td>
                                    <td class="small"><?php echo $controlar['nombre']; ?></td>
                                    <td class="small"><?php echo $controlar['operador']; ?></td>
                                    <td class="small"><?php echo $controlar['estado']; ?></td>
                                    <td>
                                        <?php if ($controlar['estado'] == 'ACTIVO') { ?>
                                        <a href="<?php echo base_url(); ?>control/editar?id_cont=<?php echo $controlar['id_cont']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form method="post" action="<?php echo base_url(); ?>control/inactivar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="id_cont"
                                                value="<?php echo $controlar['id_cont']; ?>">
                                            <button class="btn-icon btn-icon-delete" title="Anular registro"
                                                type="submit"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php } else { ?>
                                        <form method="post" action="<?php echo base_url(); ?>control/reingresar"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="id_cont"
                                                value="<?php echo $controlar['id_cont']; ?>">
                                            <button class="btn-icon btn-icon-reingres" title="Reactivar registro"
                                                type="submit"><i class="fas fa-audio-description"></i></button>
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
        </div>
    </main>
    <div id="nuevoControl" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar Control de expedientes</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>control/registrar" method="post" id="frmLotes" class="row"
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha">Fecha</label>
                                <input id="fecha" class="form-control" type="date" name="fecha" placeholder="Fecha"
                                    value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pag_control">Pag. controlados</label>
                                <input id="pag_control" class="form-control" type="number" name="pag_control"
                                    placeholder="Pag. controlados" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exp_control">Expedientes controlados</label>
                                <input id="exp_control" class="form-control" type="number" name="exp_control"
                                    placeholder="Expedientes controlados" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="solicitado">Solicitado</label>
                                <input id="solicitado" class="form-control" type="number" name="solicitado"
                                    placeholder="Solicitado" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="exp_reescaneo">Reescaneo</label>
                                <input id="exp_reescaneo" class="form-control" type="number" name="exp_reescaneo"
                                    placeholder="Reescaneos" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre_pc">PC</label><br>
                                <select name="id_est" id="id_est" class="form-input">
                                    <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                    <option <?php  echo $estTrabajo['nombre_pc'];?>
                                        value="<?php echo $estTrabajo['id_est']; ?>">
                                        <?php echo $estTrabajo['nombre_pc'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id">Usuario</label>
                                <input type="hidden" name="id" value="<?php echo $_SESSION['id']; ?>">
                                <input id="id" class="form-control" type="text" name="id"
                                    value="<?php echo $_SESSION['id'] . " - " . $_SESSION['nombre']; ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_operador">Operador</label><br>
                                <select name="id_operador" id="id_operador">
                                    <?php foreach ($data['operador'] as $operador) { ?>
                                    <option value="<?php echo $operador['id_operador']; ?>">
                                        <?php echo $operador['nombre'] . " " . $operador['apellido']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn-icon btn-icon-registrar" type="submit">Registrar</button>
                                <button class="btn-icon btn-icon-cancelar" data-dismiss="modal"
                                    type="button">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php pie() ?>
    <!-- 
    <td><img src="<?php echo base_url() ?>Assets/images/libros/<?php echo $expediente['imagen']; ?>" width="150" class="img-thumbnail"></td> -->