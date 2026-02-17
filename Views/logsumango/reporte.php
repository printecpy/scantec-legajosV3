<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <div class="card-header bg-light">
                        <h4 class="title text-black text-center">Reporte logs de Umango</h4>
                    </div>
                    <a class="btn btn-info" href="<?php echo base_url(); ?>logsumango">Volver </a>
                    <form method="post" action="<?php echo base_url();?>logsumango/excel_fecha" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-2 lg-2 m-1">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <div class="form-group">
                                            <input id="fecha" class="form-control" type="date" name="desde"
                                                placeholder="Fecha" value="<?php echo date("Y-m-d"); ?>" required>
                                        </div>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-4">
                                    <div class="form-group">
                                        <button class="btn btn-success" type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar</button>
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