    </div>
    <footer class="bg-white border-t border-gray-100 py-3 mt-auto">
        <div class="px-6 text-[10px] font-bold text-scantec-gray uppercase tracking-[0.2em]">
            &copy; Printec SA 2023 - <?php echo date("Y"); ?> | <span class="text-scantec-blue font-black">SCANTEC</span>
        </div>
    </footer>
</div>

<script src="<?php echo base_url(); ?>Assets/js/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/select2.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/Funciones.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/tables.js"></script>

<?php
    // Igual que footer.php: cierra la sesion cuando vence.
    if (isset($_SESSION['ACTIVO']) && $_SESSION['ACTIVO'] == true) {
        $tiempo_restante = isset($_SESSION['csrf_expiration']) ? $_SESSION['csrf_expiration'] - time() : 0;
        if ($tiempo_restante <= 0) { $tiempo_restante = 1; }
?>
<script>
    var remainingSeconds = <?php echo (int) $tiempo_restante; ?>;
    function forceLogoutExpired() {
        var salir = '<?php echo base_url(); ?>usuarios/salir';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sesión expirada', text: 'Su sesión se cerrará automáticamente.', timer: 3000, timerProgressBar: true,
                icon: 'warning', allowOutsideClick: false, allowEscapeKey: false,
                confirmButtonText: 'Cerrar ahora', confirmButtonColor: '#182541'
            }).then(function () { window.location.href = salir; });
        } else { window.location.href = salir; }
    }
    setTimeout(forceLogoutExpired, remainingSeconds * 1000);
</script>
<?php } ?>
</body>
</html>