<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Registro de Operadores</h4>
                    <button class="btn btn-primary" type="button" data-toggle="modal" data-target="#nuevoOpe"><i
                            class="fas fa-plus-circle"></i></button>
                    <a target="_blank" href="<?php echo base_url(); ?>operadores/pdf" class="btn btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>operadores/excel" class="btn btn-success"><i
                            class="fas fa-file-excel"></i></a>
                            <button class="btn btn-secondary" type="button" data-toggle="modal"
                        data-target="#nuevoPC">Registrar</button>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr>
                                    <!-- <th text-left small>ID </th> -->
                                    <th text-left small>Nombre</th>
                                    <th text-left small>Apellido</th>
                                    <th text-left small>Dirección</th>
                                    <th text-left small>Proyecto</th>
                                    <!-- <th text-left small>Estado</th> -->
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['operador'] as $operador) {
                                ?>
                                <tr>
                                    <!-- <td class="small"><?php echo $operador['id_operador']; ?></td> -->
                                    <td class="small"><?php echo $operador['nombre']; ?></td>
                                    <td class="small"><?php echo $operador['apellido']; ?></td>
                                    <td class="small"><?php echo $operador['direccion']; ?></td>
                                    <td class="small"><?php echo $operador['proyecto']; ?></td>
                                    <!--  <td class="small"><?php echo $operador['estado']; ?></td> -->
                                    <td>
                                        <a href="<?php echo base_url(); ?>operadores/editar?id_operador=<?php echo $operador['id_operador']; ?>"
                                            class="btn btn-primary"><i class="fas fa-edit"></i></a>
                                        <?php if ($operador['estado'] == 'ACTIVO') { ?>
                                        <form method="post" action="<?php echo base_url(); ?>operadores/inactivar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="id_operador"
                                                value="<?php echo $operador['id_operador']; ?>">
                                            <button class="btn btn-danger" type="submit"><i
                                                    class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php }  ?>
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
    <div id="nuevoOpe" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar Operador</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>operadores/registrar" method="post" id="frmOperadores"
                        class="row" autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre</label>
                                <input id="nombre" class="form-control" type="text" name="nombre" placeholder="Nombre">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">Apellido</label>
                                <input id="apellido" class="form-control" type="text" name="apellido"
                                    placeholder="Apellido">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input id="direccion" class="form-control" type="text" name="direccion"
                                    placeholder="Direccion">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="proyecto">Proyecto</label>
                                <input id="proyecto" class="form-control" type="text" name="proyecto"
                                    value="<?php echo $data['datos']['nombre'] ; ?>">
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
    <div id="nuevoPC" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="my-modal-title"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Registrar PC</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo base_url() ?>operadores/insertar" method="post" id="frmPc" class="row"
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_pc">Nombre PC</label>
                                <input id="nombre_pc" class="form-control" type="text" name="nombre_pc"
                                    placeholder="Nombre PC">
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