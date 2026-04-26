<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Acceso</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?php echo base_url(); ?>manifest.json">
    <meta name="theme-color" content="#182541">
    <link rel="apple-touch-icon" href="<?php echo base_url(); ?>Assets/img/icoScantec2.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo base_url(); ?>sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: <?php echo json_encode((string)($_SESSION['alert']['type'] ?? 'info')); ?>,
                    title: 'Atención',
                    text: <?php echo json_encode((string)($_SESSION['alert']['message'] ?? '')); ?>,
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-7">
            <div class="flex justify-center mb-8">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Scantec Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-2xl font-montserrat font-bold text-scantec-blue text-center mb-2 uppercase tracking-wide">
                Acceso al Sistema</h2>
            <p class="text-scantec-gray text-center text-sm mb-8">Por favor, ingrese sus credenciales para continuar.</p>

            <?php if (!empty($data['licencia_estado']) && empty($data['licencia_estado']['status'])): ?>
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <p class="mb-1 text-[11px] font-bold uppercase tracking-wide text-amber-800">Estado de licencia</p>
                    <p><?php echo htmlspecialchars((string) ($data['licencia_estado']['msg'] ?? 'No se pudo validar la licencia.'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mt-1 text-[12px] text-amber-700">La pagina de acceso sigue disponible, pero el ingreso al sistema permanece bloqueado hasta corregir la licencia.</p>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="<?php echo base_url(); ?>Usuarios/login" method="POST" class="space-y-6">
                <input type="hidden" name="fuente_registro" value="scantec">

                <div>
                    <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Usuario</label>
                    <input type="text" name="usuario" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                        placeholder="Ej: admin">
                </div>

                <div>
                    <label class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Contraseña</label>
                    <input type="password" name="clave" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                        placeholder="********">
                </div>

                <div class="flex items-center justify-between gap-4">
                    <a href="<?php echo base_url(); ?>home/registrarse"
                        class="text-scantec-blue hover:underline font-bold text-xs uppercase">
                        Registrarse
                    </a>
                    <a href="<?php echo base_url(); ?>home/restablecer_pw"
                        class="text-scantec-red hover:underline font-bold text-xs uppercase">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit"
                    class="w-full bg-scantec-blue hover:bg-scantec-red text-white font-montserrat font-bold py-4 rounded-xl transition-all shadow-lg tracking-widest mt-2">
                    ACCEDER
                </button>
            </form>
        </div>

        <div class="bg-scantec-blue/5 py-3 text-center border-t border-gray-100">
            <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-[0.2em]">
                © 2023 - 2026 PRINTEC SA | SOFTWARE DE GESTIÓN DOCUMENTAL
            </p>
        </div>
    </div>
</body>

</html>

