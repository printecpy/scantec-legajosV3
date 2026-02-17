<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Reagrupado de expedientes</h4>
                    <button title="Registrar" class="btn-icon btn-icon-new" type="button" data-toggle="modal"
                        data-target="#nuevoReagrupado"><i class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>reagrupado/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>reagrupado/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>
                    <a href="<?php echo base_url(); ?>reagrupado/reporte" title="Ver Reporte"
                        class="btn-icon btn-icon-reporte"><i class="fas fa-chart-bar"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr class="small">
                                    <th>Fecha</th>
                                    <th>Solicitado</th>
                                    <th>Cant. cajas</th>
                                    <th>Observaciones</th>
                                    <th>Usuario</th>
                                    <th>Operador</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['reagrupar'] as $reagrupar) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $reagrupar['fecha']; ?></td>
                                    <td class="small"><?php echo $reagrupar['solicitado']; ?></td>
                                    <td class="small"><?php echo $reagrupar['cant_cajas']; ?></td>
                                    <td class="small"><?php echo $reagrupar['observaciones']; ?></td>
                                    <td class="small"><?php echo $reagrupar['nombre']; ?></td>
                                    <td class="small"><?php echo $reagrupar['operador']; ?></td>
                                    <td class="small"><?php echo $reagrupar['estado']; ?></td>
                                    <td>
                                        <?php if ($reagrupar['estado'] == 'ACTIVO') { ?>
                                        <a href="<?php echo base_url(); ?>reagrupado/editar?id_reagrup=<?php echo $reagrupar['id_reagrup']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form method="post" action="<?php echo base_url(); ?>reagrupado/inactivar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="id_reagrup"
                                                value="<?php echo $reagrupar['id_reagrup']; ?>">
                                            <button class="btn-icon btn-icon-delete" title="Anular registro"
                                                type="submit"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php } else { ?>
                                        <form method="post" action="<?php echo base_url(); ?>reagrupado/reingresar"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="id_reagrup"
                                                value="<?php echo $reagrupar['id_reagrup']; ?>">
                                            <button class="btn-icon btn-icon-reingres" title="Reingresar registro"
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
    <div id="nuevoReagrupado" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar cajas reagrupadas</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>reagrupado/registrar" method="post" id="frmReagrupados"
                        class="row" autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha">Fecha</label>
                                <input id="fecha" class="form-control" type="date" name="fecha" placeholder="Fecha"
                                    value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="solicitado">Solicitado</label>
                                <input id="solicitado" class="form-control" type="number" name="solicitado"
                                    placeholder="Solicitado" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cant_cajas">Cant. cajas</label>
                                <input id="cant_cajas" class="form-control" type="text" name="cant_cajas"
                                    placeholder="Cantidad cajas" required> 
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <input id="observaciones" class="form-control" type="text" name="observaciones"
                                    placeholder="Observaciones" required>
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
                                <button class="btn-icon btn-icon-cancelar" data-dismiss="modal" type="button">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php pie() ?>