<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">           
            <div class="row">
                <div class="col-lg-12">
                <h5 class="text-center">Registros de visualizaciones de archivos</h5>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_viewsPdf" class="btn btn-sm btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_viewExcel" class="btn btn-sm btn-success"><i
                            class="fas fa-file-excel"></i></a>               
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mx-auto" id="table">
                            <thead class="thead-dark">
                                <tr class="small">
                                    <th class="small">Usuario</th>
                                    <th class="small">Archivo</th>
                                    <th class="small">Nombre HOST</th>
                                    <th class="small">Direccion IP</th>
                                    <th class="small">Fecha y Hora</th>                          
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['registro_views'] as $registro_views) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $registro_views['usuario']; ?></td>
                                    <td class="small"><?php echo $registro_views['nombre_expediente']; ?></td>
                                    <td class="small"><?php echo $registro_views['nombre_pc']; ?></td>                                    
                                    <td class="small"><?php echo $registro_views['direccion_ip']; ?></td>
                                    <td class="small"><?php echo $registro_views['fecha']; ?></td>
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