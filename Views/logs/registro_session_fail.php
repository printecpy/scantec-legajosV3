<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">           
            <div class="row">
                <div class="col-lg-12">
                <h5 class="text-center">Registros de sesiones fallidas / usuarios bloqueados</h5>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_session_failPdf" class="btn btn-sm btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/registro_session_failExcel" class="btn btn-sm btn-success"><i
                            class="fas fa-file-excel"></i></a>               
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mx-auto" id="table">
                            <thead class="thead-dark">
                                <tr class="small">
                                    <th class="small">Usuario</th>
                                    <th class="small">Nombre HOST</th>
                                    <th class="small">Direccion IP</th>
                                    <th class="small">Fecha y Hora</th>  
                                    <th class="small">Motivo</th>                         
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['registro_session_fail'] as $registro_session_fail) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $registro_session_fail['usuario']; ?></td>
                                    <td class="small"><?php echo $registro_session_fail['nombre_pc']; ?></td>                                    
                                    <td class="small"><?php echo $registro_session_fail['direccion_ip']; ?></td>
                                    <td class="small"><?php echo $registro_session_fail['timestamp']; ?></td>
                                    <td class="small"><?php echo $registro_session_fail['motivo']; ?></td>
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