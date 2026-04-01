<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>control/modificar" method="post" id="frmControl"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_cont">ID</label>
                                            <input type="hidden" name="id_cont"
                                                value="<?php echo $data['controlar']['id_cont'] ; ?>">
                                            <input id="id_cont" class="form-control" type="text" name="id_cont"
                                                value="<?php echo $data['controlar']['id_cont'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input id="fecha" class="form-control" type="date" name="fecha"
                                                value="<?php echo $data['controlar']['fecha'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pag_control">Pag. controlados</label>
                                            <input id="pag_control" class="form-control" type="number"
                                                name="pag_control"
                                                value="<?php echo $data['controlPag']['pag_control'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exp_control">Expedientes controlados</label>
                                            <input id="exp_control" class="form-control" type="number"
                                                name="exp_control"
                                                value="<?php echo $data['controlExp']['exp_control'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="solicitado">Solicitado</label>
                                            <input id="solicitado" class="form-control" type="number" name="solicitado"
                                                value="<?php echo $data['controlar']['solicitado'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exp_reescaneo">Reescaneo</label>
                                            <input id="exp_reescaneo" class="form-control" type="number"
                                                name="exp_reescaneo"
                                                value="<?php echo $data['controlar']['exp_reescaneo'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre_pc">PC</label>
                                            <select name="id_est" id="id_est" class="form-control">
                                                <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                                <option <?php if ($estTrabajo['id_est'] == $data['controlar']['id_est']) {
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
                                                <option <?php if ($operador['id_operador'] == $data['controlar']['id_operador']) {
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
                                            <button class="btn-icon btn-icon-edit" title="Modificar registro"
                                                type="submit"><i class="fas fa-edit"></i> Actualizar</button>
                                            <a class="btn-icon btn-icon-delete" title="Cancelar" href="<?php echo base_url(); ?>control"><i
                                                    class="fas fa-ban"></i> Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <form action="<?php echo base_url(); ?>control/importar" method="POST"
                        enctype="multipart/form-data">
                        <h5 class="text-center">Detalles de los archivos controlados</h5>
                        <input type="file" name="file" accept=".xls, .xlsx" required value="Seleccione archivo">
                        <input type="hidden" name="id_cont" id="id_cont"
                            value="<?php echo $data['controlar']['id_cont']; ?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button class="btn-icon btn-icon-excel" type="submit">Importar registros</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr>
                                    <th>Nombre archivo</th>
                                    <th>Paginas</th>
                                    <th>Fecha Creación</th>
                                    <th>Fecha Modificación</th>
                                    <th>Ruta archivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detControl'] as $detControl) {
                                ?>
                                <tr>
                                    <td class="small"><?php echo $detControl['nombre_archivo']; ?></td>
                                    <td class="small"><?php echo number_format($detControl['num_pag'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="small"><?php echo $detControl['fecha_creacion'] ?></td>
                                    <td class="small"><?php echo $detControl['fecha_modificacion']; ?></td>
                                    <td class="small"><?php echo $detControl['ruta_archivo']; ?></td>
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