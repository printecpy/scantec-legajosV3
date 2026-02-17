<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <h5 class="text-center"> Bitácora Scantec</h5>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/pdf" class="btn btn-sm btn-danger"><i
                            class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>logs/excel" class="btn btn-sm btn-success"><i
                            class="fas fa-file-excel"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead" style="background-color: #182541;">
                                <tr class="small">
                                    <th>Fecha</th>
                                    <th>Execute SQL</th>
                                    <th>Reverse SQL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $logsumango) {
                                ?>
                                    <tr class="small">
                                        <td><?php echo $logsumango['fecha']; ?></td>
                                        <td><?php echo $logsumango['executedSQL']; ?></td>
                                        <td><?php echo $logsumango['reverseSQL']; ?></td>
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