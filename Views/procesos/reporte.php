<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-lg-8 m-auto">
                    <div class="card-header bg-secondary">
                        <h4 class="title text-white text-center">Reporte Procesos</h4>
                    </div>
                    <form method="post" action="<?php echo base_url();?>procesos/pdf_filtro" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="card-header bg-light">
                                <h5 class="title text-dark">Inicio de lotes</h5>
                            </div>
                            <!--  contenedor para filtro por nro de caja      -->
                            <div class="row">
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-select">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['nro_caja'];?>
                                                value="<?php echo $proceso['nro_caja']; ?>">
                                                <?php echo $proceso['nro_caja'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-select">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['nro_caja'];?>
                                                value="<?php echo $proceso['nro_caja']; ?>">
                                                <?php echo $proceso['nro_caja'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button class="btn btn-danger" type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar a pdf</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>procesos/excel_filtro" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-select">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['nro_caja'];?>
                                                value="<?php echo $proceso['nro_caja']; ?>">
                                                <?php echo $proceso['nro_caja'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-select">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['nro_caja'];?>
                                                value="<?php echo $proceso['nro_caja']; ?>">
                                                <?php echo $proceso['nro_caja'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button class="btn btn-success" type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar a excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!--  contenedor para filtro por fecha      -->
                    <form method="post" action="<?php echo base_url();?>procesos/pdf_filtroFecha" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="card-header bg-light">
                                <h5 class="title text-dark">Fecha recibido</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-input">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['fecha_proceso'];?>
                                                value="<?php echo $proceso['fecha_proceso']; ?>">
                                                <?php echo $proceso['fecha_proceso'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-input">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['fecha_proceso'];?>
                                                value="<?php echo $proceso['fecha_proceso']; ?>">
                                                <?php echo $proceso['fecha_proceso'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button class="btn btn-danger" type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar a pdf</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>procesos/excel_filtroFecha" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-input">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['fecha_proceso'];?>
                                                value="<?php echo $proceso['fecha_proceso']; ?>">
                                                <?php echo $proceso['fecha_proceso'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-input">
                                            <?php foreach ($data as $proceso) { ?>
                                            <option <?php  echo $proceso['fecha_proceso'];?>
                                                value="<?php echo $proceso['fecha_proceso']; ?>">
                                                <?php echo $proceso['fecha_proceso'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button class="btn btn-success" type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar a excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>