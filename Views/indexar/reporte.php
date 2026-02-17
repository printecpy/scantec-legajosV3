<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-10 m-auto">
                    <div class="card-header">
                        <h4 class="title text-center">Reporte indexado de expedientes</h4>
                    </div>
                    <a class="btn-icon btn-icon-volver" title="Volver" href="#"
                        onclick="window.history.back(); return false;"><i class="fas fa-arrow-left"></i></a>
                    <form method="post" action="<?php echo base_url();?>indexar/pdf_filtroFecha" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="card-header">
                                <h5 class="title text-dark">Por fecha</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-2 lg-3 m-1">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <div class="form-group">
                                            <input id="desde" class="form-control" type="date" name="desde"
                                                placeholder="Fecha" value="<?php echo date("Y-m-d"); ?>" required>
                                        </div>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <div class="form-group">
                                            <input id="hasta" class="form-control" type="date" name="hasta"
                                                placeholder="Fecha" value="<?php echo date("Y-m-d"); ?>" required>
                                        </div>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-4">
                                    <div class="form-group">
                                        <button title="Generar informe en pdf" class="btn-icon btn-icon-pdf"
                                            type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>indexar/excel_filtroFecha" target="_blank"
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
                                <div class="col-md-2 lg-2 m-1">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <div class="form-group">
                                            <input id="fecha" class="form-control" type="date" name="hasta"
                                                placeholder="Fecha" value="<?php echo date("Y-m-d"); ?>" required>
                                        </div>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-4">
                                    <div class="form-group">
                                        <button title="Generar informe en excel" class="btn-icon btn-icon-excel"
                                            type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="<?php echo base_url(); ?>indexar/pdf_filtroTotal" target="_blank"
                        autocomplete="off" onsubmit="return validarFormulario()">
                        <div class="modal-body">
                            <div class="card-header">
                                <h5 class="title">Por mes (Total)</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="mes_desde">Desde</label><br>
                                    <div class="form-group">
                                        <select name="mes_desde" id="mes_desde" class="form-control" required>
                                            <option value="">MES</option>
                                            <option value="1">ENERO</option>
                                            <option value="2">FEBRERO</option>
                                            <option value="3">MARZO</option>
                                            <option value="4">ABRIL</option>
                                            <option value="5">MAYO</option>
                                            <option value="6">JUNIO</option>
                                            <option value="7">JULIO</option>
                                            <option value="8">AGOSTO</option>
                                            <option value="9">SEPTIEMBRE</option>
                                            <option value="10">OCTUBRE</option>
                                            <option value="11">NOVIEMBRE</option>
                                            <option value="12">DICIEMBRE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="anio_desde">Desde</label><br>
                                    <div class="form-group">
                                        <input type="number" name="anio_desde" id="anio_desde" class="form-control"
                                            value="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="mes_hasta">Hasta</label><br>
                                    <div class="form-group">
                                        <select name="mes_hasta" id="mes_hasta" class="form-control" required>
                                            <option value="">MES</option>
                                            <option value="1">ENERO</option>
                                            <option value="2">FEBRERO</option>
                                            <option value="3">MARZO</option>
                                            <option value="4">ABRIL</option>
                                            <option value="5">MAYO</option>
                                            <option value="6">JUNIO</option>
                                            <option value="7">JULIO</option>
                                            <option value="8">AGOSTO</option>
                                            <option value="9">SEPTIEMBRE</option>
                                            <option value="10">OCTUBRE</option>
                                            <option value="11">NOVIEMBRE</option>
                                            <option value="12">DICIEMBRE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="anio_hasta">Hasta</label><br>
                                    <div class="form-group">
                                        <input type="number" name="anio_hasta" id="anio_hasta" class="form-control"
                                            value="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-4">
                                    <div class="form-group">
                                        <button title="Generar informe en pdf" class="btn-icon btn-icon-pdf"
                                            type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="<?php echo base_url(); ?>indexar/excel_filtroTotal" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="mes_desde">Desde</label><br>
                                    <div class="form-group">
                                        <select name="mes_desde" id="mes_desde" class="form-control" required>
                                            <option value="">MES</option>
                                            <option value="1">ENERO</option>
                                            <option value="2">FEBRERO</option>
                                            <option value="3">MARZO</option>
                                            <option value="4">ABRIL</option>
                                            <option value="5">MAYO</option>
                                            <option value="6">JUNIO</option>
                                            <option value="7">JULIO</option>
                                            <option value="8">AGOSTO</option>
                                            <option value="9">SEPTIEMBRE</option>
                                            <option value="10">OCTUBRE</option>
                                            <option value="11">NOVIEMBRE</option>
                                            <option value="12">DICIEMBRE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="anio_desde">Desde</label><br>
                                    <div class="form-group">
                                        <input type="number" name="anio_desde" id="anio_desde" class="form-control"
                                            value="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="mes_hasta">Hasta</label><br>
                                    <div class="form-group">
                                        <select name="mes_hasta" id="mes_hasta" class="form-control" required>
                                            <option value="">MES</option>
                                            <option value="1">ENERO</option>
                                            <option value="2">FEBRERO</option>
                                            <option value="3">MARZO</option>
                                            <option value="4">ABRIL</option>
                                            <option value="5">MAYO</option>
                                            <option value="6">JUNIO</option>
                                            <option value="7">JULIO</option>
                                            <option value="8">AGOSTO</option>
                                            <option value="9">SEPTIEMBRE</option>
                                            <option value="10">OCTUBRE</option>
                                            <option value="11">NOVIEMBRE</option>
                                            <option value="12">DICIEMBRE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-1">
                                    <label for="anio_hasta">Hasta</label><br>
                                    <div class="form-group">
                                        <input type="number" name="anio_hasta" id="anio_hasta" class="form-control"
                                            value="<?php echo date('Y'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-2 lg-2 m-4">
                                    <div class="form-group">
                                        <button title="Generar informe en excel" class="btn-icon btn-icon-excel"
                                            type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="<?php echo base_url();?>indexar/pdf_filtroOperador" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="card-header">
                                <h5 class="title">Por Operador</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-3 lg-1 m-2">
                                    <div class="form-group">
                                        <label for="id_operador"></label><br>
                                        <select name="id_operador" id="id_operador">
                                            <?php foreach ($data['operador'] as $operador) { ?>
                                            <option value="<?php echo $operador['id_operador']; ?>">
                                                <?php echo $operador['nombre'] . " " . $operador['apellido']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <div class="form-group">
                                        <button title="Generar informe en pdf" class="btn-icon btn-icon-pdf"
                                            type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>indexar/excel_filtroOperador" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3 lg-1 m-2">
                                    <div class="form-group">
                                        <label for="id_operador"></label><br>
                                        <select name="id_operador" id="id_operador">
                                            <?php foreach ($data['operador'] as $operador) { ?>
                                            <option value="<?php echo $operador['id_operador']; ?>">
                                                <?php echo $operador['nombre'] . " " . $operador['apellido']; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 mt-4">
                                    <div class="form-group">
                                        <button title="Generar informe en excel" class="btn-icon btn-icon-excel"
                                            type="submit"><i class="fas fa-file-excel"></i>
                                            Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form method="post" action="<?php echo base_url();?>indexar/pdf_filtroPC" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="card-header">
                                <h5 class="title">Por PC</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-2 lg-1 m-2">
                                    <div class="form-group">
                                        <label for="id_est"></label><br>
                                        <select name="id_est" id="id_est" class="form-input">
                                            <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                            <option <?php  echo $estTrabajo['nombre_pc'];?>
                                                value="<?php echo $estTrabajo['id_est']; ?>">
                                                <?php echo $estTrabajo['nombre_pc'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-4">
                                    <div class="form-group">
                                        <button title="Generar informe en pdf" class="btn-icon btn-icon-pdf"
                                            type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>indexar/excel_filtroPC" target="_blank"
                        autocomplete="off">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-2 lg-1 m-2">
                                    <div class="form-group">
                                        <label for="id_est"></label><br>
                                        <select name="id_est" id="id_est" class="form-input">
                                            <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                            <option <?php  echo $estTrabajo['nombre_pc'];?>
                                                value="<?php echo $estTrabajo['id_est']; ?>">
                                                <?php echo $estTrabajo['nombre_pc'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-4">
                                    <div class="form-group">
                                        <button title="Generar informe en excel" class="btn-icon btn-icon-excel"
                                            type="submit"><i class="fas fa-file-excel"></i>
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