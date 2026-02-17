<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="text-center"> Bitácora del Motor de Documentos</h5>
                    <a target="_blank" href="<?php echo base_url(); ?>logsumango/pdf" class="btn btn-sm btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>logsumango/excel" class="btn btn-sm btn-success"><i
                            class="fas fa-file-excel"></i></a>
                    <a href="<?php echo base_url(); ?>logsumango/reporte" style="background-color: #878787;" class="btn btn-xs">Reporte</a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr class="small">
                                    <th>ID proceso umango</th>
                                    <th>Nro lote</th>
                                    <th>Fuente captura</th>
                                    <th>Archivo Orig.</th>
                                    <th>Orden Doc.</th>
                                    <th>Pág.</th>
                                    <th>Fecha inicio</th>
                                    <th>Fecha fin</th>
                                    <th>Creador</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Nombre Host</th>
                                    <th>Dirección IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $logsumango) {
                                ?>
                                    <tr class="small">
                                        <td class="small"><?php echo $logsumango['id_proceso_umango']; ?></td>
                                        <td class="small"><?php echo $logsumango['id_lote']; ?></td>
                                        <td class="small"><?php echo $logsumango['fuente_captura']; ?></td>
                                        <td class="small"><?php echo $logsumango['archivo_origen']; ?></td>
                                        <td class="small"><?php echo $logsumango['orden_documento']; ?></td>
                                        <td class="small"><?php echo $logsumango['paginas_exportadas']; ?></td>
                                        <td class="small"><?php echo $logsumango['fecha_inicio']; ?></td>
                                        <td class="small"><?php echo $logsumango['fecha_finalizacion']; ?></td>
                                        <td class="small"><?php echo $logsumango['creador']; ?></td>
                                        <td class="small"><?php echo $logsumango['usuario']; ?></td>
                                        <td class="small"><?php echo $logsumango['estado']; ?></td>
                                        <td class="small"><?php echo $logsumango['nombre_host']; ?></td>
                                        <td class="small"><?php echo $logsumango['ip_host']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>