<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-5 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>reagrupado/modificar" method="post" id="frmLotes"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_reagrup">ID</label>
                                            <input type="hidden" name="id_reagrup"
                                                value="<?php echo $data['reagrupar']['id_reagrup'] ; ?>">
                                            <input id="id_reagrup" class="form-control" type="text" name="id_reagrup"
                                                value="<?php echo $data['reagrupar']['id_reagrup'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input id="fecha" class="form-control" type="date" name="fecha"
                                                value="<?php echo $data['reagrupar']['fecha'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="solicitado">Solicitado</label>
                                            <input id="solicitado" class="form-control" type="text"
                                                name="solicitado"
                                                value="<?php echo $data['reagrupar']['solicitado'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cant_cajas">Cant. cajas</label>
                                            <input id="cant_cajas" class="form-control" type="text  " name="cant_cajas"
                                                value="<?php echo $data['reagrupar']['cant_cajas'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="observaciones">Cant. expedientes</label>
                                            <input id="observaciones" class="form-control" type="text"
                                                name="observaciones"
                                                value="<?php echo $data['reagrupar']['observaciones'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="id_operador">Seleccione Operador</label><br>
                                            <select name="id_operador" id="id_operador" class="form-control">
                                                <?php foreach ($data['operador'] as $operador) { ?>
                                                <option <?php if ($operador['id_operador'] == $data['reagrupar']['id_operador']) {
                                                echo 'selected';
                                            } ?> value="<?php echo $operador['id_operador']; ?>">
                                                    <?php echo $operador['nombre'] . " " . $operador['apellido']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit">Modificar</button>
                                            <a class="btn-icon btn-icon-cancelar" href="<?php echo base_url(); ?>reagrupado">Cancelar</a>
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