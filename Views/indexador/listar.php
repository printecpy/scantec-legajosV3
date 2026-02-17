<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-9 m-auto">
                    <div class="card-header">
                        <h3 class="text-center">Indexar documento</h3><label class="text-center small">(sin OCR)</label>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url(); ?>indexador/indexar_archivo" method="POST"
                                enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="pdf1">Archivo</label>
                                            <input id="pdf1" class="form-control" type="file" accept="application/pdf"
                                                name="pdf1">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombre_doc">Seleccione grupo de archivo</label>
                                            <input id="columna_01" class="form-control" type="text" name="columna_01"
                                                value="" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="columna_01">Ingrese indice 2</label>
                                            <input id="columna_02" class="form-control" type="text" name="columna_02"
                                                value="" required="">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="columna_02">Ingrese indice 3</label>
                                            <input id="columna_03" class="form-control" type="text" name="columna_03"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="columna_03">Ingrese indice 4</label>
                                            <input id="columna_04" class="form-control" type="text" name="columna_04"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="columna_03">Ingrese indice 5</label>
                                            <input id="columna_05" class="form-control" type="text" name="columna_05"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="columna_03">Ingrese indice 6</label>
                                            <input id="columna_06" class="form-control" type="text" name="columna_06"
                                                required="">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input class="btn btn-info" type="submit" value="Indexar archivo">
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