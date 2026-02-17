<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-5 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>indexar/modificar" method="post" id="frmIndexado"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_index">ID</label>
                                            <input type="hidden" name="id_index"
                                                value="<?php echo $data['indexado']['id_index'] ; ?>">
                                            <input id="id_index" class="form-control" type="text" name="id_index"
                                                value="<?php echo $data['indexado']['id_index'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input id="fecha" class="form-control" type="date" name="fecha"
                                                value="<?php echo $data['indexado']['fecha'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pag_index">Pag. controlados</label>
                                            <input id="pag_index" class="form-control" type="number"
                                                name="pag_index"
                                                value="<?php echo $data['indexado']['pag_index'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exp_index">Expedientes controlados</label>
                                            <input id="exp_index" class="form-control" type="number"
                                                name="exp_index"
                                                value="<?php echo $data['indexado']['exp_index'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                        <label for="nombre_pc">PC</label>
                                            <select name="id_est" id="id_est" class="form-control">
                                                <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                                <option <?php if ($estTrabajo['id_est'] == $data['indexado']['id_est']) {
                                                echo 'selected';
                                            } ?> value="<?php echo $estTrabajo['id_est']; ?>">
                                                    <?php echo $estTrabajo['nombre_pc']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="id_operador">Seleccione Operador</label><br>
                                            <select name="id_operador" id="id_operador" class="form-control">
                                                <?php foreach ($data['operador'] as $operador) { ?>
                                                <option <?php if ($operador['id_operador'] == $data['indexado']['id_operador']) {
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
                                            <a class="btn-icon btn-icon-cancelar"
                                                href="<?php echo base_url(); ?>indexar">Cancelar</a>
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