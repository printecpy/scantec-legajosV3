<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-7 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>operadores/modificar" method="post" id="frmoperadores"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="id_operador">ID</label>
                                            <input type="hidden" name="id_operador"
                                                value="<?php echo $data['operador']['id_operador'] ; ?>">
                                            <input id="id_operador" class="form-control" type="text" name="id_operador"                                                
                                                value="<?php echo $data['operador']['id_operador'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input id="nombre" class="form-control" type="text" name="nombre"                                                
                                                value="<?php echo $data['operador']['nombre'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="apellido">Apellido</label>
                                            <input id="apellido" class="form-control" type="text" name="apellido"
                                                value="<?php echo $data['operador']['apellido'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="direccion">Direccion</label>
                                            <input id="direccion" class="form-control" type="text"
                                                name="direccion"
                                                value="<?php echo $data['operador']['direccion'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="proyecto">Proyecto</label>
                                            <input id="proyecto" type="text" name="proyecto"
                                                class="form-control"
                                                value="<?php echo $data['operador']['proyecto']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit">Modificar</button>
                                            <a class="btn-icon btn-icon-cancelar" href="<?php echo base_url(); ?>operadores">Cancelar</a>
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