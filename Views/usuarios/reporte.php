<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-lg-6 m-auto">
                    <form method="post" action="<?php echo base_url();?>usuarios/pdf_filtro" target="_blank"
                        autocomplete="off">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-select">
                                            <?php foreach ($data as $usuario) { ?>
                                            <option <?php  echo $usuario['nombre'];?>
                                                value="<?php echo $usuario['nombre']; ?>">
                                                <?php echo $usuario['nombre'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-select">
                                            <?php foreach ($data as $usuario) { ?>
                                            <option <?php  echo $usuario['nombre'];?>
                                                value="<?php echo $usuario['nombre']; ?>">
                                                <?php echo $usuario['nombre'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button class="btn btn-danger" type="submit">
                                            <i class="fas fa-file-pdf"></i> Exportar a pdf</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="<?php echo base_url();?>usuarios/excel_filtro" target="_blank"
                        autocomplete="off">
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 m-auto">
                                    <div class="form-group">
                                        <label for="desde">Desde</label><br>
                                        <select name="desde" id="desde" class="form-select">
                                            <?php foreach ($data as $usuario) { ?>
                                            <option <?php  echo $usuario['nombre'];?>
                                                value="<?php echo $usuario['nombre']; ?>">
                                                <?php echo $usuario['nombre'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 m-auto">
                                    <div class="form-group">
                                        <label for="hasta">Hasta</label><br>
                                        <select name="hasta" id="hasta" class="form-select">
                                            <?php foreach ($data as $usuario) { ?>
                                            <option <?php  echo $usuario['nombre'];?>
                                                value="<?php echo $usuario['nombre']; ?>">
                                                <?php echo $usuario['nombre'];?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-4">
                                    <div class="form-group">
                                        <button href="<?php echo base_url();?>usuarios/listar" class="btn btn-success"
                                            type="submit"><i class="fas fa-file-excel"></i> Exportar a excel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php pie() ?>