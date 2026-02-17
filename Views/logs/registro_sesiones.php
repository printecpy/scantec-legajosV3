<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">           
            <div class="row">
                <div class="col-lg-12">
                <h5 class="text-center">Registros de sesiones Scantec</h5>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_sesionesPdf" class="btn btn-sm btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_sesionesExcel" class="btn btn-sm btn-success"><i
                            class="fas fa-file-excel"></i></a>               
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mx-auto" id="table">
                            <thead class="thead-dark">
                                <tr class="small">
                                    <th class="small">Fecha Inicio</th>
                                    <th class="small">Direccion IP</th>
                                    <th class="small">Nombre de HOST</th>
                                    <th class="small">Nombre del Usuario</th>
                                    <th class="small">Usuario</th>
                                    <th class="small">Fecha Cierre</th>                          
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['registro_sesiones'] as $registro_sesiones) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $registro_sesiones['fecha']; ?></td>
                                    <td class="small"><?php echo $registro_sesiones['ip']; ?></td>
                                    <td class="small"><?php echo $registro_sesiones['servidor']; ?></td>
                                    <td class="small"><?php echo $registro_sesiones['nombre']; ?></td>
                                    <td class="small"><?php echo $registro_sesiones['usuario']; ?></td>
                                    <td class="small"><?php echo $registro_sesiones['fecha_cierre']; ?></td>
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