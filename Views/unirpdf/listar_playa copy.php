<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row mt-2">
                <div class="col-lg-9 m-auto">
                    <div class="card-header">
                        <h3 class="text-center">Unir documentos (PDF + Imágenes)</h3>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <form action="<?php echo base_url(); ?>unirpdf/procesar_pdf" method="POST"
                                enctype="multipart/form-data">
                                <div class="row">
                                    <?php for ($i = 1; $i <= 13; $i++): ?>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="pdf<?= $i ?>">Documento <?= $i ?> (PDF o Imagen)</label>
                                                <input id="pdf<?= $i ?>" class="form-control" type="file"
                                                    accept=".pdf,image/jpeg,image/png"
                                                    name="pdf<?= $i ?>">
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombre_doc">Ingrese nombre del documento</label>
                                            <input id="nombre_doc" class="form-control" type="text" name="nombre_doc"
                                                value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input class="btn btn-info" type="submit" value="Crear PDF unificado">
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
