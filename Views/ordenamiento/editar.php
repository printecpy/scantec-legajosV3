<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-7 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>ordenamiento/modificar" method="post" id="frmLotes"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="id">Código caja</label>
                                            <input type="hidden" name="id"
                                                value="<?php echo $data['ordenamiento']['id'] ; ?>">
                                            <input id="codigo_caja" class="form-control" type="text" name="codigo_caja"                                                
                                                value="<?php echo $data['ordenamiento']['codigo_caja'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="descripcion">Terminación</label>
                                            <input id="descripcion" class="form-control" type="text" name="descripcion"
                                                
                                                value="<?php echo $data['ordenamiento']['descripcion'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="ubicacion">Ubicación</label>
                                            <input id="ubicacion" class="form-control" type="text" name="ubicacion"
                                                value="<?php echo $data['ordenamiento']['ubicacion'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha_almacenamiento">Fecha de almacenamiento</label>
                                            <input id="fecha_almacenamiento" class="form-control" type="date"
                                                name="fecha_almacenamiento" value="<?php echo $data['ordenamiento']['fecha_almacenamiento'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="observaciones">Observaciones</label>
                                            <input id="observaciones" class="form-control" type="text"
                                                name="observaciones"
                                                value="<?php echo $data['ordenamiento']['observaciones'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="tipo">Tipo</label>
                                            <input id="tipo" type="text" name="tipo"
                                                class="form-control"
                                                value="<?php echo $data['ordenamiento']['tipo']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit">Modificar</button>
                                            <a class="btn-icon btn-icon-cancelar" href="<?php echo base_url(); ?>ordenamiento">Cancelar</a>
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