<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-6 m-auto">
                <div class="card-header">
                        Renombrar expediente
                </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url() ?>expedientes/modificar_nombre" method="post"
                                id="frmExpedientes" class="row" autocomplete="off" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="id_expediente">ID</label>
                                            <input id="id_expediente" class="form-control" type="text"  name="id_expediente"
                                                value="<?php echo $data['expediente']['id_expediente'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="indice_01">Indice 1</label>
                                            <input id="indice_01" class="form-control" type="text" name="indice_01"
                                                value="<?php echo $data['expediente']['indice_01'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="indice_02">Indice 2</label>
                                            <input id="indice_02" class="form-control" type="text" name="indice_02"
                                                value="<?php echo $data['expediente']['indice_02'] ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="indice_05">Indice 5</label>
                                            <input id="indice_05" class="form-control" type="text" name="indice_05"
                                                value="<?php echo $data['expediente']['indice_05'] ?>">
                                        </div>
                                    </div>    
                                   <!--  <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="ruta_original">Indice 5</label>
                                            <input name="subir_archivo" type="text"  value="<?php echo $data['expediente']['nombre_archivo'] ?>"/>
                                        </div>
                                    </div>        -->                                                                                              
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-info" type="submit">Renombrar</button>
                                            <a class="btn btn-danger"
                                                href="<?php echo base_url(); ?>expedientes">Cancelar</a>
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