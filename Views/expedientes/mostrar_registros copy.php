<?php encabezado() ?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid div-main2">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="text-center">Archivos</h4>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/pdf" title="Generar informe en pdf"
                        class="btn-icon btn-icon-pdf">
                        <i class="fas fa-file-pdf"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/excel"
                        title="Generar informe en excel" class="btn-icon btn-icon-excel"><i
                            class="fas fa-file-excel"></i></a>
                    <a target="_blank" href="<?php echo base_url(); ?>expedientes/pdf_email"
                        title="Enviar reporte por correo" class="btn-icon btn-icon-email"><i
                            class="fas fa-envelope"></i></a>
                    <a href="<?php echo base_url(); ?>expedientes/reporte" title="Ver Reporte"
                        class="btn-icon btn-icon-reporte"><i class="fas fa-chart-bar"></i></a>
                    <a class="btn-icon btn-icon-volver" title="Volver" href="#"
                        onclick="window.history.back(); return false;"><i class="fas fa-arrow-left"></i></a>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table">
                            <thead class="thead">
                                <tr class="small">
                                    <th>Tipo documento</th>
                                    <th>Indice 1</th>
                                    <th>Indice 2</th>
                                    <th>Indice 3</th>
                                    <th>Indice 4</th>
                                    <th>Indice 5</th>
                                    <th>Indice 6</th>
                                    <th class="text-center">Cant pag.</th>
                                    <th class="text-center">Ubicacion</th>
                                    <th class="text-center">Fecha carga</th>
                                    <th class="text-center">Version</th>
                                    <th>Archivos</th>
                                    <?php if ($_SESSION['id_rol'] == 1) { ?>
                                    <th></th>
                                    <?php } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['mostrar_registros'] as $mostrar_registros) {
                                ?>
                                <tr class="small">
                                    <td class="small"><?php echo $mostrar_registros['nombre_tipoDoc']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_01']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_02']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_03']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_04']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_05']; ?></td>
                                    <td class="small"><?php echo $mostrar_registros['indice_06']; ?></td>
                                    <td class="text-center small"><?php echo $mostrar_registros['paginas']; ?></td>
                                    <td class="text-center small"><?php echo $mostrar_registros['ubicacion']; ?></td>
                                    <td class="text-center small"><?php echo $mostrar_registros['fecha_indexado']; ?>
                                    </td>
                                    <td class="text-center small"><?php echo $mostrar_registros['version']; ?></td>
                                    <!-- <td class="text-center small"><?php echo $mostrar_registros['fecha_vencimiento']; ?> -->
                                    </td>
                                    <!--  <td>
                                        <a class="small" href="<?php echo base_url(); ?>expedientes/ver_expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php 
                                                     echo $mostrar_registros['id_expediente']; ?>" target="_blank">Ver
                                            original</a>
                                        <a class="small" href="<?php echo base_url(); ?>expedientes/expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php 
                                                     echo $mostrar_registros['id_expediente']; ?>"
                                            target="_blank">Visualizar</a>
                                    </td> -->
                                    <td>
                                        <?php
                                        $fecha_carga = new DateTime($mostrar_registros['fecha_indexado']);
                                        $fecha_licencia = new DateTime(LICENCIA_EXPIRA);
                                        $hoy = new DateTime();

                                        // Evaluar si ya se venció la licencia
                                        $licencia_expirada = $fecha_carga > $fecha_licencia;

                                        if ($licencia_expirada): ?>
                                            <span class="badge bg-danger mb-1"><i class="fas fa-lock"></i> Licencia expirada</span><br>
                                            <span class="small text-muted d-block">Ver original deshabilitado</span>
                                            <span class="small text-muted d-block">Visualización deshabilitada</span>
                                        <?php else: ?>
                                            <a class="small" href="javascript:void(0);"
                                                onclick="mostrarPDFServidor('<?= base_url(); ?>expedientes/expediente?ruta=<?= urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?= $mostrar_registros['id_expediente']; ?>')">
                                                Visualizar (con iframe)
                                            </a>
                                            <a class="small"  href="javascript:void(0);"
                                             onclick="mostrarPDFModal('<?php echo base_url(); ?>expedientes/expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>')">Ver con modal</a>
                                            </a><br>
                                           <!--  <a class="small" href="javascript:void(0);" 
   onclick="mostrarDocumentoModal('<?php echo base_url(); ?>expedientes/expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>')">
   Ver con modal2
</a> -->
                                            <!-- <a class="small" href="<?php echo base_url(); ?>expedientes/ver_expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente'];?>" target="_blank">Ver original</a><br> -->
                                             <a class="small" 
                                                href="<?php 
                                                    echo base_url() . 'expedientes/ver_expediente?ruta=' 
                                                            . urlencode($mostrar_registros['ruta_original']) 
                                                            . '&id_expediente=' . $mostrar_registros['id_expediente'] 
                                                            . '&return_url=' . urlencode($_SERVER['REQUEST_URI']); 
                                                ?>" 
                                                target="_blank">
                                                Ver original
                                                </a>
                                            <a class="small" href="<?php echo base_url(); ?>expedientes/expediente?ruta=<?php echo urlencode($mostrar_registros['ruta_original']); ?>&id_expediente=<?php echo $mostrar_registros['id_expediente']
                                            . '&return_url=' . urlencode($_SERVER['REQUEST_URI']);  ?>" target="_blank">Visualizar</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($_SESSION['id_rol'] == 1) { ?>
                                        <a href="<?php echo base_url(); ?>expedientes/editar?id_expediente=<?php echo $mostrar_registros['id_expediente']; ?>"
                                            class="btn-icon btn-icon-edit" title="Modificar registro"><i
                                                class="fas fa-edit"></i></a>
                                        <form method="post" action="<?php echo base_url(); ?>expedientes/eliminar"
                                            class="d-inline eliminar">
                                            <input type="hidden" name="token"
                                                value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id_expediente"
                                                value="<?php echo $mostrar_registros['id_expediente']; ?>">
                                            <button class="btn-icon btn-icon-delete" title="Anular registro"
                                                type="submit"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                        <?php } ?>
                                        <button class="btn-icon btn-icon-metadatos"
                                            onclick="cargarMetadatos('<?php echo $mostrar_registros['ruta_original']; ?>')"
                                            aria-label="Cargar metadatos"><i class="fas fa-info-circle"></i></button>
                                        <!--  <button class="btn btn-info btn-sm" onclick="openSignModal({
                                                    id: <?php echo $mostrar_registros['id_expediente']; ?>,
                                                    ruta: '<?php echo $mostrar_registros['ruta_original']; ?>'
                                                })">Firmar</button>
                                        <button class="btn btn-primary" onclick="openSignatureApp('<?php echo $mostrar_registros['id_expediente']; ?>', 
                                        '<?php echo $mostrar_registros['ruta_original']; ?>', '<?php echo $mostrar_registros['indice_04']; ?>')">Firmar Documento</button> -->
                                        <?php } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div id="pdf_preview_container" 
                            style="display:none; position:relative; border:1px solid #ccc; margin-top:1rem; padding:10px; text-align:center; border:none; ">
                            <!-- Botón de cerrar -->
                            <button type="button" onclick="cerrarPDF()" 
                                    style="position:absolute; top:0.5rem; right:9.5rem; background:#ff4d4d; color:#fff; 
                                    border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">
                                X
                            </button>
                            <!-- iFrame donde se carga el PDF -->
                            <iframe id="pdf_preview" 
                                    src="" 
                                    width="70%" 
                                    height="600px" 
                                    style="border:none; display:inline-block;">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="modal fade" id="metadatosModal" tabindex="-1" aria-labelledby="metadatosModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title custom-modal-title" id="metadatosModalLabel">Metadatos y datos
                            adicionales del PDF</h4>
                        <button class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="contenidoMetadatos">
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Modal Bootstrap -->
<div class="modal fade" id="pdfModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vista de Documento</h5>
        <button class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe id="pdf_preview_modal" width="100%" height="600px" style="border:none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
function mostrarPDFModal(url){
    document.getElementById('pdf_preview_modal').src = url;
    var modal = new bootstrap.Modal(document.getElementById('pdfModal'));
    modal.show();
}
/* function mostrarDocumentoModal(url) {
    const iframe = document.getElementById('pdf_preview_modal');
    let visorURL = '';

    // Buscar extensión desde el parámetro "ruta="
    const params = new URL(url, window.location.origin).searchParams;
    const rutaParam = params.get('ruta') || '';
    const extension = rutaParam.split('.').pop().toLowerCase();

    if (extension === 'pdf') {
        visorURL = url;
    } 
    else if (['doc', 'docx', 'ppt', 'pptx'].includes(extension)) {
        // Usa visor de Microsoft
        const archivoCompleto = `${window.location.origin}/${rutaParam}`;
        visorURL = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(archivoCompleto);
    } 
    else {
        alert('Formato no soportado para vista previa.');
        return;
    }

    iframe.src = visorURL;
    const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
    modal.show();
} */
</script>
<script>
/**
 * Muestra el PDF en el iframe
 * @param {string} pdfUrl - Ruta completa del PDF (generada desde PHP con base_url).
 */
function mostrarPDFServidor(pdfUrl) {
    const preview = document.getElementById('pdf_preview');
    const container = document.getElementById('pdf_preview_container');

    preview.src = pdfUrl;     // Le pasamos al iframe la ruta del PDF
    container.style.display = 'block';  // Mostramos el contenedor
}

/**
 * Cierra/oculta el visor PDF
 */
function cerrarPDF() {
    const preview = document.getElementById('pdf_preview');
    const container = document.getElementById('pdf_preview_container');

    preview.src = '';          // Limpia el src para liberar memoria
    container.style.display = 'none'; // Oculta el contenedor
}
</script>
<?php
    if (isset($_SESSION['alert'])) {
        $alertType = $_SESSION['alert']['type'];
        $alertMessage = $_SESSION['alert']['message'];
        // Estilo específico por tipo de alerta
        $alertTitleClass = 'swal-title';
        $alertIconClass = "swal-icon-$alertType";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '$alertType',
                    title: '$alertMessage',
                    showConfirmButton: true,
                    timer: 5000,
                    customClass: {
                        title: '$alertTitleClass',
                        icon: '$alertIconClass'
                    }
                });
            });
        </script>";
        unset($_SESSION['alert']); // Limpiar la alerta para que no se muestre nuevamente
    }
    ?>
    <?php pie() ?>
    <script>
    const baseURL = "<?php echo base_url(); ?>";
    </script>