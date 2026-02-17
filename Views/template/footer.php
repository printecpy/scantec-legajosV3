</main> 
        <footer class="bg-white border-t border-gray-100 py-4 mt-auto" id="footer">
            <div class="container-fluid px-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-[10px] font-bold text-scantec-gray uppercase tracking-[0.2em]">
                        &copy; Printec SA 2023 - <?php echo date("Y"); ?> | <span class="text-scantec-blue font-black">SCANTEC</span>
                    </div>
                </div>
            </div>
        </footer>
    </div> 
</div> 

<script src="<?php echo base_url(); ?>Assets/js/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/bootstrap.bundle.min.js"></script>
 <script src="<?php echo base_url(); ?>Assets/js/select2.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/dataTables.bootstrap4.min.js"></script> 

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo base_url(); ?>Assets/js/Funciones.js"></script>
<script src="<?php echo base_url(); ?>Assets/js/tables.js"></script>

<style>
    
</style>
<script>
    // Función para desplegar submenús
    function toggleMenu(id) {
        const menu = document.getElementById(id);
        const icon = document.getElementById('icon-' + id);
        
        if (menu) {
            menu.classList.toggle('hidden');
        }
        
        if (icon) {
            icon.classList.toggle('rotate-180');
        }
    }

    // Función para ocultar/mostrar el Sidebar completo
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('w-64')) {
            sidebar.classList.replace('w-64', 'w-0');
        } else {
            sidebar.classList.replace('w-0', 'w-64');
        }
    }
</script>
<?php 
    // Solo activamos el temporizador si hay una sesión real
    if (isset($_SESSION['ACTIVO']) && $_SESSION['ACTIVO'] == true) {
        
        $tiempo_restante = 0;
        // Calculamos tiempo restante basado en la expiración del token (30 min)
        if (isset($_SESSION['csrf_expiration'])) {
            $tiempo_restante = $_SESSION['csrf_expiration'] - time();
        }

        // Si el tiempo ya venció, forzamos a 1 segundo para que el JS actúe de inmediato
        if ($tiempo_restante <= 0) { $tiempo_restante = 1; }
    ?>

    <script>
        // Recibimos el tiempo de PHP
        var remainingSeconds = <?php echo $tiempo_restante; ?>;
        
        // Función que ejecuta el cierre
        function forceLogoutExpired() {
            
            // OPCIÓN A: Con SweetAlert2 (Estilo Scantec)
            if (typeof Swal !== 'undefined') {
                let timerInterval;
                Swal.fire({
                    title: 'Sesión Expirada',
                    html: 'Su sesión se cerrará automáticamente en <b></b> milisegundos.',
                    timer: 3000, // Le damos 3 segs al usuario para leer
                    timerProgressBar: true,
                    icon: 'warning',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Cerrar ahora',
                    confirmButtonColor: '#182541', // Scantec Blue
                    didOpen: () => {
                        // Opcional: Mostrar cuenta regresiva visual
                        Swal.showLoading();
                        const b = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            b.textContent = Swal.getTimerLeft();
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then((result) => {
                    // AL CERRARSE LA ALERTA (Automático o Click) -> REDIRECCIONAMOS
                    // IMPORTANTE: NO borramos cookies aquí. PHP lo hará.
                    window.location.href = '<?= base_url(); ?>usuarios/salir';
                });
            } 
            // OPCIÓN B: Fallback si no carga SweetAlert
            else {
                window.location.href = '<?= base_url(); ?>usuarios/salir';
            }
        }

        // Iniciamos el conteo
        if (remainingSeconds > 0) {
            console.log("Sesión expira en: " + remainingSeconds + " segundos.");
            setTimeout(function() {
                forceLogoutExpired();
            }, remainingSeconds * 1000); // Convertir a milisegundos
        } else {
            // Si ya cargó la página vencida
            forceLogoutExpired();
        }
    </script>
<?php } ?>