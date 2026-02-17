<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-6 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>procesos/modificar" method="post" id="frmProcesos"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="titulo">ID Proceso</label>
                                            <input type="hidden" name="id_proceso"
                                                value="<?php echo $data['proceso']['id_proceso']; ?>">
                                            <input id="id_proceso" class="form-control" type="text" name="id_proceso"disabled
                                                value="<?php echo $data['proceso']['id_proceso']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="id_tipo_proceso">Seleccione proceso</label><br>
                                            <select name="id_tipo_proceso" id="id_tipo_proceso" class="form-control">
                                                <<?php foreach ($data['tipo_proceso'] as $tipo_proceso) { ?> <option <?php if ($tipo_proceso['id_tipo_proceso'] == $data['proceso']['id_tipo_proceso']) {
                                                echo 'selected';
                                            } ?> value="<?php echo $tipo_proceso['id_tipo_proceso']; ?>">
                                                    <?php echo $tipo_proceso['tipo_proceso']; ?></option>
                                                    <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="desde">Desde</label>
                                            <input id="desde" class="form-control" type="text" name="desde"
                                                value="<?php echo $data['proceso']['desde']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="hasta">Hasta</label>
                                            <input id="hasta" class="form-control" type="text" name="hasta"
                                                value="<?php echo $data['proceso']['hasta']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="fecha_proceso">Fecha proceso</label>
                                            <input id="fecha_proceso" class="form-control" type="date"
                                                name="fecha_proceso"
                                                value="<?php echo $data['proceso']['fecha_proceso']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="id">Usuario</label><br>
                                        <select name="id" id="id" class="form-control">
                                            <?php foreach ($data['usuario'] as $usuario) { ?>
                                            <option <?php if ($usuario['id'] == $data['proceso']['id']) {
                                                echo 'selected';
                                            } ?> value="<?php echo $usuario['id']; ?>">
                                                <?php echo $usuario['nombre']; ?></option>
                                            <?php } ?>
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nro_caja">Nro Caja</label>
                                            <input id="nro_caja" class="form-control" type="text" name="nro_caja"
                                            value="<?php echo $data['proceso']['nro_caja']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="observacion">Observación</label>
                                            <input id="observacion" class="form-control" type="text" name="observacion"
                                                rows="5" value="<?php echo $data['proceso']['observacion']; ?>">
                                        </div>
                                    </div>
                                     <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn-icon btn-icon-modificar" type="submit">Modificar</button>
                                            <a class="btn-icon btn-icon-cancelar"
                                                href="<?php echo base_url(); ?>procesos">Cancelar</a>
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