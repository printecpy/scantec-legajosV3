<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>escaneos/modificar" method="post" id="frmEscaneos"
                                class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_esc">ID</label>
                                            <input type="hidden" name="id_esc"
                                                value="<?php echo $data['escaneo']['id_esc'] ; ?>">
                                            <input id="id_esc" class="form-control" type="text" name="id_esc"
                                                value="<?php echo $data['escaneo']['id_esc'] ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input id="fecha" class="form-control" type="date" name="fecha"
                                                value="<?php echo $data['escaneo']['fecha'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="pag_esc">Pag. Escaneadas</label>
                                            <input id="pag_esc" class="form-control" type="number" name="pag_esc"
                                                value="<?php echo $data['escaneoPag']['pag_esc'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="cant_exp">Exp. escaneados</label>
                                            <input id="cant_exp" class="form-control" type="number" name="cant_exp"
                                                value="<?php echo $data['escaneoExp']['cant_exp'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nombre_pc">PC</label>
                                            <select name="id_est" id="id_est" class="form-control">
                                                <?php foreach ($data['estTrabajo'] as $estTrabajo) { ?>
                                                <option <?php if ($estTrabajo['id_est'] == $data['escaneo']['id_est']) {
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
                                                <option <?php if ($operador['id_operador'] == $data['escaneo']['id_operador']) {
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
                                            <button class="btn-icon btn-icon-edit" title="Modificar registro" type="submit">Actualizar</button>
                                            <a class="btn-icon btn-icon-delete" title="Cancelar"
                                                href="<?php echo base_url(); ?>escaneos">Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <form action="<?php echo base_url(); ?>escaneos/importar" method="POST"
                        enctype="multipart/form-data">   
                        <h5 class="text-center">Detalles de los archivos escaneados</h5>                     
                        <input type="file" name="file" accept=".xls, .xlsx"
                        required value="Seleccione archivo">
                        <input type="hidden" name="id_esc" id="id_esc" value="<?php echo $data['escaneo']['id_esc']; ?>">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button class="btn-icon btn-icon-excel" type="submit">Importar registros</button>  
                    </form> 
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr>
                                    
                                    <th>Nombre archivo</th>
                                    <th>Paginas</th>
                                    <th>Fecha Creación</th>
                                    <th>Fecha Modificacion</th>
                                    <th>Ruta archivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detEscaneo'] as $detEscaneo) {
                                ?>
                                <tr>                                    
                                    <td class="small"><?php echo $detEscaneo['nombre_archivo']; ?></td>
                                    <td class="small"><?php echo number_format($detEscaneo['num_pag'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="small"><?php echo $detEscaneo['fecha_creacion'] ?></td>
                                    <td class="small"><?php echo $detEscaneo['fecha_modificacion']; ?></td>
                                    <td class="small"><?php echo $detEscaneo['ruta_archivo']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </main>
    <?php pie() ?>