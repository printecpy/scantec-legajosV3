<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Registro de Lotes</h4>
                    <button title="Registrar" class="btn-icon btn-icon-new" type="button" data-toggle="modal"
                        data-target="#nuevoLote"><i class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>lotes/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf">
                        <i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>lotes/excel" title="Generar informe en excel"
                        class="btn-icon btn-icon-excel"><i class="fas fa-file-excel"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr class="small">
                                    <th>Inicio lote</th>
                                    <th>Fin lote</th>
                                    <th>Cant exped.</th>
                                    <th>Fecha recibido</th>
                                    <th>Fecha entregado</th>
                                    <th>Total pag.</th>
                                    <th>Estado</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['lote'] as $lote){
                                ?>
                                <tr class="small">
                                    <td><?php echo $lote['inicio_lote']; ?></td>
                                    <td><?php echo $lote['fin_lote']; ?></td>
                                    <td><?php echo $lote['cant_expediente']; ?></td>
                                    <td><?php echo $lote['fecha_recibido']; ?></td>
                                    <td><?php echo $lote['fecha_entregado']; ?></td>
                                    <td><?php echo $lote['total_paginas']; ?></td>
                                    <td><?php echo $lote['estado']; ?></td>
                                    <td>
                                        <?php if ($lote['estado'] == 'EN PROCESO') { ?>
                                        <a href="<?php echo base_url(); ?>lotes/editar?id_registro=<?php echo $lote['id_registro']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form method="post" action="<?php echo base_url(); ?>lotes/eliminar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="id_registro"
                                                value="<?php echo $lote['id_registro']; ?>">
                                            <button class="btn-icon btn-icon-delete" title="Anular registro"
                                                type="submit"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php } else { ?>
                                        <form method="post" action="<?php echo base_url(); ?>lotes/reingresar"
                                            class="d-inline reingresar">
                                            <input type="hidden" name="id_registro"
                                                value="<?php echo $lote['id_registro']; ?>">
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
    <div id="nuevoLote" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar lote</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>lotes/registrar" method="post" id="frmLotes" class="row"
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="titulo">Inicio Lote</label>
                                <input id="titulo" class="form-control" type="text" name="inicio_lote"
                                    placeholder="Inicio lote">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="titulo">Fin Lote</label>
                                <input id="titulo" class="form-control" type="text" name="fin_lote"
                                    placeholder="Fin lote">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cantidad">Cant. expediente</label>
                                <input id="cantidad" class="form-control" type="number" name="cant_expediente"
                                    placeholder="Cant. expediente">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fecha_recibido">Fecha recibido</label>
                                <input id="fecha_recibido" class="form-control" type="date" name="fecha_recibido"
                                    value="<?php echo date("Y-m-d"); ?>">
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