<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mt-3">
                        <div class="card-header">
                            <h3 class="card-title text-center">Copia de seguridad y restaurar Base de Datos</h3>
                        </div>
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-4 mt-2">
                                                    <h5>Exportar Base de datos</h5>
                                                    <form method="post"
                                                        action="<?php echo base_url() ?>configuracion/backup">
                                                        <button type="submit"
                                                            class="btn-icon btn-icon-registrar">Respaldar BD</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6 mt-2">
                                                    <h5>Importar Base de datos</h5>
                                                    <form method="post"
                                                        action="<?php echo base_url() ?>configuracion/restore">
                                                        <label for="sqlFile">Selecciona un archivo .sql:</label>
                                                        <input type="file" name="sqlFile" accept=".sql" required>
                                                        <button type="submit"
                                                            class="btn-icon btn-icon-registrar">Restaurar BD</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-4 mt-2">
                                                    <h5>Respaldo de archivos</h5>
                                                    <form action="<?php echo base_url(); ?>configuracion/respaldo_archivos"
                                                        method="post">
                                                        <button type="submit"
                                                            class="btn-icon btn-icon-registrar">Ejecutar
                                                            Respaldo</button>
                                                    </form>
                                                    <label class="small">(Se ejecutará la tarea de respaldo en
                                                        segundo plano)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
    </main>

    <?php pie() ?>