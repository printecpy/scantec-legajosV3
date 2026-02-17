<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Ordenamiento fisico</h4>
                    <a target="_blank" href="<?php echo base_url(); ?>ordenamiento/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf"><i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>ordenamiento/excel"
                        title="Generar informe en excel" class="btn-icon btn-icon-excel"><i
                            class="fas fa-file-excel"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr>
                                    <th>Codigo caja</th>
                                    <th>Terminacion</th>
                                    <th>Ubicacion</th>
                                    <th>Fecha</th>
                                    <th>Observaciones</th>
                                    <th>Tipo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['ordenamiento'] as $ordenamiento) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $ordenamiento['codigo_caja']; ?></td>
                                    <td class="small"><?php echo $ordenamiento['descripcion']; ?></td>
                                    <td class="small"><?php echo $ordenamiento['ubicacion']; ?></td>
                                    <td class="small"><?php echo $ordenamiento['fecha_almacenamiento']; ?></td>
                                    <td class="small"><?php echo $ordenamiento['observaciones']; ?></td>
                                    <td class="small"><?php echo $ordenamiento['tipo']; ?></td>
                                    <td>
                                        <a href="<?php echo base_url(); ?>ordenamiento/editar?id=<?php echo $ordenamiento['id']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                    </td>
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