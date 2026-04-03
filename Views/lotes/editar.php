<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-5 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>lotes/modificar" method="post" id="frmLotes"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_registro">ID Lote</label>
                                            <input id="id_registro" class="form-control" type="text" name="id_registro" disabled                                                
                                                value="<?php echo $data['lote']['id_registro'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="inicio_lote">Inicio Lote</label>
                                            <input id="inicio_lote" class="form-control" type="text" name="inicio_lote"
                                                
                                                value="<?php echo $data['lote']['inicio_lote'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fin_lote">Fin Lote</label>
                                            <input id="fin_lote" class="form-control" type="text" name="fin_lote"
                                                value="<?php echo $data['lote']['fin_lote'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_entregado">Fecha de entrega</label>
                                            <input id="fecha_entregado" class="form-control" type="date"
                                                name="fecha_entregado" value="<?php echo date("Y-m-d"); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="cant_expediente">Cant. expedientes</label>
                                            <input id="cant_expediente" class="form-control" type="text"
                                                name="cant_expediente"
                                                value="<?php echo $data['cant_exped']['cant_expediente'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="total_paginas">Total de páginas</label>
                                            <input id="total_paginas" type="text" name="total_paginas"
                                                class="form-control"
                                                value="<?php echo $data['total_pag']['total_paginas']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit">Modificar</button>
                                            <a class="btn-icon btn-icon-cancelar" href="<?php echo base_url(); ?>lotes">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>