<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Registro de cajas (con archivos)</h4>
                    <button class="btn-icon btn-icon-new" type="button" data-toggle="modal" data-target="#nuevoProceso"><i
                            class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>procesos/pdf" class="btn-icon btn-icon-pdf"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>procesos/excel" class="btn-icon btn-icon-excel"><i
                            class="fas fa-file-excel"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr class="small">
                                    <th>Nro lote</th>
                                    <th>Fecha Proceso</th>
                                    <th>Nro Caja</th>
                                    <th>Desde</th>
                                    <th>Hasta</th>
                                    <th>Usuario</th>
                                    <th>Proceso</th>
                                    <th>Observación</th>
                                    <th>Actualizar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['proceso'] as $proceso) {
                                ?>
                                <tr class="small">
                                    <td><?php echo $proceso['id_registro']; ?></td>
                                    <td><?php echo $proceso['fecha_proceso']; ?></td>
                                    <td><?php echo $proceso['nro_caja']; ?></td>
                                    <td><?php echo $proceso['desde']; ?></td>
                                    <td><?php echo $proceso['hasta']; ?></td>
                                    <td><?php echo $proceso['nombre']; ?></td>
                                    <td><?php echo $proceso['tipo_proceso']; ?></td>
                                    <td><?php echo $proceso['observacion']; ?></td>
                                    <td>
                                        <a href="<?php echo base_url(); ?>procesos/editar?id_proceso=<?php echo $proceso['id_proceso']; ?>"
                                        class="btn-icon btn-icon-edit" title="Modificar registro"><i class="fas fa-edit"></i></a>
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
    <div id="nuevoProceso" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar Proceso</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>procesos/registrar" method="post" id="frmProcesos" class="row"
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lote">Lote</label><br>
                                <select id="lote" class="form-control" name="lote">
                                    <?php foreach ($data['lote'] as $lote) { ?>
                                    <option value="<?php echo $lote['id_registro']; ?>">
                                        <?php echo $lote['id_registro'] . " - " .
                                     $lote['fecha_recibido']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="usuario">Usuario</label><br>
                                <select name="usuario" id="usuario" class="form-control">
                                    <?php foreach ($data['usuario'] as $usuario) { ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo $usuario['nombre']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="tipo_proceso">Seleccione proceso</label><br>
                                <select name="tipo_proceso" id="tipo_proceso" class="form-control">
                                    <?php foreach ($data['tipo_proceso'] as $tipo_proceso) { ?>
                                    <option value="<?php echo $tipo_proceso['id_tipo_proceso']; ?>">
                                        <?php echo $tipo_proceso['tipo_proceso']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nro_caja">Nro Caja</label>
                                <input id="nro_caja" class="form-control" type="text" name="nro_caja"
                                    placeholder="Nro Caja">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="desde">Desde</label>
                                <input id="desde" class="form-control" type="text" name="desde" placeholder="Desde">
                            </div>
                            <div class="form-group">
                                <label for="hasta">Hasta</label>
                                <input id="hasta" class="form-control" type="text" name="hasta" placeholder="Hasta">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="fecha_proceso">Fecha proceso</label>
                                <input id="fecha_proceso" class="form-control" type="date" name="fecha_proceso"
                                    value="<?php echo date("Y-m-d"); ?>">
                                <div class="form-group">
                                    <label for="observacion">Observación</label>
                                    <textarea id="observacion" class="form-control" name="observacion" rows="5"
                                        value=" "></textarea>
                                </div>
                            </div>
                            <button class="btn-icon btn-icon-registrar" type="submit">Registrar</button>
                            <button class="btn-icon btn-icon-cancelar" data-dismiss="modal" type="button">Cancelar</button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php pie() ?>