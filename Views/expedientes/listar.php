<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Archivos</h4>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf">
                        <i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/excel"
                        title="Generar informe en excel" class="btn-icon btn-icon-excel"><i
                            class="fas fa-file-excel"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/pdf_email"
                        title="Enviar reporte por correo" class="btn-icon btn-icon-email"><i
                            class="fas fa-envelope"></i></a>
                    <a href="<?php echo base_url(); ?>expedientes/reporte" title="Ver Reporte"
                        class="btn-icon btn-icon-reporte"><i class="fas fa-chart-bar"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr class="small">
                                    <th>Tipo documento</th>
                                    <th>Indice 1</th>
                                    <th>Indice 2</th>
                                    <th>Indice 3</th>
                                    <th>Indice 4</th>
                                    <th>Indice 5</th>
                                    <th>Indice 6</th>
                                    <th>Registros</th>
                                    <th>Archivos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['expediente'] as $expediente) {
                                    
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $expediente['nombre_tipoDoc']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_01']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_02']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_03']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_04']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_05']; ?></td>
                                    <td class="small"><?php echo $expediente['indice_06']; ?></td>
                                    <td class="text-center small"><?php echo $expediente['cant_documentos']; ?></td>
                                    <td>
                                        <a class="btn btn-sm"
                                            href="<?php echo base_url(); ?>expedientes/mostrar_registros?indice_01=<?php echo 
                                            $expediente['indice_01']; ?>&nombre_tipoDoc=<?php echo $expediente['nombre_tipoDoc']; ?>">
                                            <i class="fas fa-folder-open text-warning"></i>
                                        </a>
                                    </td>
                                    <!-- <td>
                                        <?php if ($_SESSION['id_rol'] == 1) { ?>
                                        <a href="<?php echo base_url(); ?>expedientes/editar?id_expediente=<?php echo $expediente['id_expediente']; ?>"
                                            class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                                        <?php } ?>
                                    </td> -->
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div id="nuevoLibro" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registro Expediente</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>expedientes/registrar" method="post" id="frmExpedientes"
                        class="row" autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="titulo">Título</label>
                                <input id="titulo" class="form-control" type="text" name="titulo"
                                    placeholder="Título del libro">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="autor">Autor</label>
                                <select id="autor" class="form-control" name="autor">
                                    <?php foreach ($data['autores'] as $autor) { ?>
                                    <option value="<?php echo $autor['id']; ?>"><?php echo $autor['autor']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editorial">Editorial</label>
                                <select id="editorial" class="form-control" name="editorial">
                                    <?php foreach ($data['editoriales'] as $editorial) { ?>
                                    <option value="<?php echo $editorial['id']; ?>">
                                        <?php echo $editorial['editorial']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="materia">Materia</label>
                                <select id="materia" class="form-control" name="materia">
                                    <?php foreach ($data['materias'] as $materia) { ?>
                                    <option value="<?php echo $materia['id']; ?>"><?php echo $materia['materia']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cantidad">Cantidad</label>
                                <input id="cantidad" class="form-control" type="text" name="cantidad"
                                    placeholder="Cantidad">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="num_pagina">Cantidad de página</label>
                                <input id="num_pagina" class="form-control" type="number" name="num_pagina"
                                    placeholder="Cantidad Página">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="anio_edicion">Año Edición</label>
                                <input id="anio_edicion" class="form-control" type="date" name="anio_edicion"
                                    value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea id="descripcion" class="form-control" name="descripcion"
                                        rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="imagen">Foto</label>
                                <input id="imagen" class="form-control" type="file" name="imagen">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit">Registrar</button>
                                <button class="btn btn-danger" data-dismiss="modal" type="button">Cancelar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php pie() ?>