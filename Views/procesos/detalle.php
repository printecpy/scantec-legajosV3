<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-6 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>procesos/detalle" method="post" id="frmDetalles"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="table-responsive">
                        <table class="table table-light mt-4" id="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID </th>                                                                 
                                    <th>Documento</th>
                                    <th>Total pag.</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detalleProceso'] as $detalleProceso) {
                                ?>
                                <tr>
                                    <td><?php echo $detalleProceso['id_detalle_proceso']; ?></td>
                                    <td><?php echo $detalleProceso['documento']; ?></td>                              
                                    <td><?php echo $detalleProceso['total_pag']; ?></td>
                                   
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
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