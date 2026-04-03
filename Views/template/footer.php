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

<script>
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

    const SCANTEC_SIDEBAR_STORAGE_KEY = 'scantec.sidebar.collapsed';

    function setSidebarState(collapsed) {
        const sidebar = document.getElementById('sidebar');
        const restoreButton = document.getElementById('sidebar-restore-button');
        const toggleButton = document.getElementById('sidebar-toggle-button');
        const contentShell = document.getElementById('app-shell-content');

        if (!sidebar) {
            return;
        }

        if (collapsed) {
            sidebar.classList.add('sidebar-collapsed');
            if (restoreButton) {
                restoreButton.classList.add('sidebar-restore-visible');
            }
            if (contentShell) {
                contentShell.classList.add('content-expanded');
            }
            if (toggleButton) {
                toggleButton.setAttribute('aria-label', 'Mostrar menú');
                toggleButton.setAttribute('title', 'Mostrar menú');
            }
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            if (restoreButton) {
                restoreButton.classList.remove('sidebar-restore-visible');
            }
            if (contentShell) {
                contentShell.classList.remove('content-expanded');
            }
            if (toggleButton) {
                toggleButton.setAttribute('aria-label', 'Ocultar menú');
                toggleButton.setAttribute('title', 'Ocultar menú');
            }
        }

        try {
            localStorage.setItem(SCANTEC_SIDEBAR_STORAGE_KEY, collapsed ? '1' : '0');
        } catch (error) {}
    }

    function toggleSidebar(forceOpen = false) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) {
            return;
        }

        const collapsed = sidebar.classList.contains('sidebar-collapsed');
        setSidebarState(forceOpen ? false : !collapsed);
    }

    document.addEventListener('DOMContentLoaded', function () {
        try {
            const savedState = localStorage.getItem(SCANTEC_SIDEBAR_STORAGE_KEY);
            if (savedState === '1') {
                setSidebarState(true);
            }
        } catch (error) {}
    });
</script>

<?php
    if (isset($_SESSION['ACTIVO']) && $_SESSION['ACTIVO'] == true) {
        $tiempo_restante = 0;
        if (isset($_SESSION['csrf_expiration'])) {
            $tiempo_restante = $_SESSION['csrf_expiration'] - time();
        }

        if ($tiempo_restante <= 0) {
            $tiempo_restante = 1;
        }
?>

    <script>
        var remainingSeconds = <?php echo $tiempo_restante; ?>;

        function forceLogoutExpired() {
            if (typeof Swal !== 'undefined') {
                let timerInterval;
                Swal.fire({
                    title: 'Sesión expirada',
                    html: 'Su sesión se cerrará automáticamente en <b></b> milisegundos.',
                    timer: 3000,
                    timerProgressBar: true,
                    icon: 'warning',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'Cerrar ahora',
                    confirmButtonColor: '#182541',
                    didOpen: () => {
                        Swal.showLoading();
                        const b = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            b.textContent = Swal.getTimerLeft();
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(() => {
                    window.location.href = '<?= base_url(); ?>usuarios/salir';
                });
            } else {
                window.location.href = '<?= base_url(); ?>usuarios/salir';
            }
        }

        if (remainingSeconds > 0) {
            console.log('Sesión expira en: ' + remainingSeconds + ' segundos.');
            setTimeout(function() {
                forceLogoutExpired();
            }, remainingSeconds * 1000);
        } else {
            forceLogoutExpired();
        }
    </script>
<?php } ?>