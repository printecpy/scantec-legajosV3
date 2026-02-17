<footer class="py-4 bg-light mt-auto">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-dark">Derechos reservados &copy; Printec SA 2022</div>
        </div>
    </div>
</footer>

<link href="<?php echo base_url(); ?>Assets/datatables/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link rel="stylesheet"
    href="<?php echo base_url(); ?>Assets/datatables/DataTables-1.13.1/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="<?php echo base_url(); ?>Assets/datatables/DataTables-1.13.1/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>Assets/datatables/estilo.css">


<!--  jquery y bootstrap      -->
<script src="Assets/datatables/jquery-3.5.1"></script>
<script src="Assets/datatables/bootstrap@5.0.0-beta2-bootstrap.bundle.min.js"></script>

<!--  datatables con bootstrap      -->
<script src="Assets/datatables/jquery.dataTables.min.js"></script>
<script src="Assets/datatables/dataTables.bootstrap5.min.js"></script>

<!-- para usar los botones    -->
<script src="Assets/datatables/buttons.dataTables.min 1.6.5.js"></script>
<script src="Assets/datatables/jszip.min 3.1.3.js"></script>
<script src="Assets/datatables/buttons.html5.min 1.6.5.js"></script>

<!-- para los estilos de excel    -->
<script src="Assets/datatables/buttons.html5.styles.min 1.1.1.js"></script>
<script src="Assets/datatables/buttons.html5.styles.templates.min1.1.1.js"></script>

<script>
$(document).ready(function() {
    $('#tabla').DataTable({
        dom: "Bfrtip",
        buttons: [{
            extend: "excel", // Extend the excel button
            excelStyles: { // Add an excelStyles definition
                cells: "2", // to row 2
                style: { // The style block
                    font: { // Style the font
                        name: "Arial", // Font name
                        size: "14", // Font size
                        color: "FFFFFF", // Font Color
                        b: false, // Remove bolding from header row
                    },
                    fill: { // Style the cell fill (background)
                        pattern: { // Type of fill (pattern or gradient)
                            color: "457B9D", // Fill color
                        }
                    }
                }
            },
        }, ],
    });

});
/* $("#table").DataTable({
    
}); */
</script>