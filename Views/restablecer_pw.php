<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Restablecer Contraseña</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?php echo base_url(); ?>manifest.json">
    <meta name="theme-color" content="#182541">
    <link rel="apple-touch-icon" href="<?php echo base_url(); ?>Assets/img/icoScantec2.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo base_url(); ?>sw.js')
                    .then(function (registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, function (err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <script src="<?php echo base_url(); ?>Assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: <?php echo json_encode($_SESSION['alert']['type']); ?>,
                    title: 'Atención',
                    text: <?php echo json_encode($_SESSION['alert']['message']); ?>,
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'ENTENDIDO'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div
        class="max-w-md w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20">
        <div class="p-8">
            <div class="flex justify-center mb-6">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-xl font-montserrat font-bold text-scantec-blue text-center mb-1 uppercase tracking-tight">
                Restablecer Contraseña</h2>
            <p class="text-scantec-gray text-center text-xs mb-8 font-medium">Valide su identidad para generar una nueva
                clave</p>

            <form action="<?php echo base_url(); ?>home/restaurarPass" method="POST" class="space-y-4">
                <h2
                    class="text-xl font-montserrat font-bold text-scantec-blue text-center mb-1 uppercase tracking-tight">
                    Restablecer Contraseña</h2>
                <p class="text-scantec-gray text-center text-sm mb-8 font-medium">
                    Por seguridad, el restablecimiento de contraseña lo realiza el administrador del sistema.
                    Comuníquese con el administrador del sistema.
                </p>
                <p class="text-scantec-gray text-center text-xs mb-6">
                    Si su usuario es de dominio (Active Directory), la contraseña se cambia desde Windows o con mesa
                    de ayuda.
                </p>
                <div class="text-center">
                    <a href="<?php echo base_url(); ?>"
                        class="text-scantec-gray hover:text-scantec-blue text-[10px] font-bold uppercase tracking-widest transition-colors inline-flex items-center gap-2">
                        <span>←</span> Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-scantec-blue/5 py-3 text-center border-t border-gray-100">
            <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-[0.2em]">
                © 2023 - 2026 PRINTEC SA | SEGURIDAD ACTIVA
            </p>
        </div>
    </div>
</body>

</html>