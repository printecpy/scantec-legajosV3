<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-6 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>procesos/modificar" method="post" id="frmProcesos"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_entregado">Fecha de entrega</label>
                                            <input id="fecha_entregado" class="form-control" type="date"
                                                name="fecha_entregado" value="<?php echo date("Y-m-d"); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="total_paginas">Total de paginas</label>
                                            <input type="hidden" name="id_registro"
                                                value="<?php echo $data['lote']['id_registro']; ?>">
                                            <input id="total_paginas" class="form-control" type="number"
                                                name="total_paginas"
                                                value="<?php echo $data['lote']['total_paginas']; ?>">
                                        </div>
                                    </div>
                                     <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Modificar</button>
                                            <a class="btn btn-danger" href="<?php echo base_url(); ?>procesos">Cancelar</a>
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