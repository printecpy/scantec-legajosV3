<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Indexados de expedientes</h4>
                    <button title="Registrar" class="btn-icon btn-icon-new" type="button" data-toggle="modal"
                        data-target="#nuevoIndex"><i class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>indexar/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>indexar/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>
                    <a href="<?php echo base_url(); ?>indexar/reporte" title="Ver Reporte"
                        class="btn-icon btn-icon-reporte"><i class="fas fa-chart-bar"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr class="small">
                                    <th>Fecha</th>
                                    <th>Pag. Index.</th>
                                    <th>Exped. Indexado</th>
                                    <th>PC</th>
                                    <th>Usuario</th>
                                    <th>Operador</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['indexado'] as $indexado) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $indexado['fecha']; ?></td>
                                    <td class="small"><?php echo number_format($indexado['pag_index'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="small"><?php echo $indexado['exp_index']; ?></td>
                                    <td class="small"><?php echo $indexado['nombre_pc']; ?></td>
                                    <td class="small"><?php echo $indexado['nombre']; ?></td>
                                    <td class="small"><?php echo $indexado['operador']; ?></td>
                                    <td class="small"><?php echo $indexado['estado']; ?></td>
                                    <td>
                                        <?php if ($indexado['estado'] == 'ACTIVO') { ?>
                                        <a href="<?php echo base_url(); ?>indexar/editar?id_index=<?php echo $indexado['id_index']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form method="post" action="<?php echo base_url(); ?>indexar/inactivar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="id_index"
                                                value="<?php echo $indexado['id_index']; ?>">
                                            <button class="btn-icon btn-icon-delete" title="Anular registro"
                                                type="submit"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php } else { ?>
                                        <form method="post" action="<?php echo base_url(); ?>indexar/reingresar"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="id_index"
                                                value="<?php echo $indexado['id_index']; ?>">
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
    <div id="nuevoIndex" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar datos</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>indexar/registrar" method="post" id="frmIndexado" class="row"
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha">Fecha</label>
                                <input id="fecha" class="form-control" type="date" name="fecha" placeholder="Fecha"
                                    value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="pag_index">Paginas indexados</label>
                                <input id="pag_index" class="form-control" type="number" name="pag_index"
                                    placeholder="Pag. indexados">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="exp_index">Expedientes indexados</label>
                                <input id="exp_index" class="form-control" type="number" name="exp_index"
                                    placeholder="Exped. indexados">
                            </div>
                        </div>
                        <div class="col-md-7">
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