<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-8 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>preparado/modificar" method="post" id="frmPreparados"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_prep">ID</label>
                                            <input type="hidden" name="id_prep"
                                                value="<?php echo $data['preparar']['id_prep'] ; ?>">
                                            <input id="id_prep" class="form-control" type="text" name="id_prep"
                                                value="<?php echo $data['preparar']['id_prep'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input id="fecha" class="form-control" type="date" name="fecha"
                                                value="<?php echo $data['preparar']['fecha'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="cant_expediente">Cant. expediente</label>
                                            <input id="cant_expediente" class="form-control" type="text"
                                                name="cant_expediente"
                                                value="<?php echo $data['preparar']['cant_expediente'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="cant_cajas">Cant. cajas</label>
                                            <input id="cant_cajas" class="form-control" type="text  " name="cant_cajas"
                                                value="<?php echo $data['preparar']['cant_cajas'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="observaciones">Observaciones</label>
                                            <input id="observaciones" class="form-control" type="text"
                                                name="observaciones"
                                                value="<?php echo $data['preparar']['observaciones'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_operador">Seleccione Operador</label><br>
                                            <select name="id_operador" id="id_operador" class="form-control">
                                                <?php foreach ($data['operador'] as $operador) { ?>
                                                <option <?php if ($operador['id_operador'] == $data['preparar']['id_operador']) {
                                                echo 'selected';
                                            } ?> value="<?php echo $operador['id_operador']; ?>">
                                                    <?php echo $operador['nombre'] . " " . $operador['apellido']; ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit"><i
                                                    class="fas fa-edit"></i> Actualizar</button>
                                            <a class="btn-icon btn-icon-cancelar"
                                                href="<?php echo base_url(); ?>preparado"><i class="fas fa-ban"></i>
                                                Cancelar</a>
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