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
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '<?php echo $_SESSION['alert']['type']; ?>',
                    title: 'Atención',
                    text: '<?php echo $_SESSION['alert']['message']; ?>',
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'ENTENDIDO'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="max-w-md w-full bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden border border-white/20">
        <div class="p-8">
            <div class="flex justify-center mb-6">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Logo" class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-xl font-montserrat font-bold text-scantec-blue text-center mb-1 uppercase tracking-tight">Restablecer Contraseña</h2>
            <p class="text-scantec-gray text-center text-xs mb-8 font-medium">Valide su identidad para generar una nueva clave</p>

            <form action="<?php echo base_url(); ?>home/restaurarPass" method="POST" class="space-y-4">
                <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <div>
                    <label class="block text-[10px] font-bold text-scantec-blue uppercase tracking-widest mb-1 px-1">Nombre Completo</label>
                    <input type="text" name="nombre" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50 focus:bg-white"
                        placeholder="Ej: Juan Pérez">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-scantec-blue uppercase tracking-widest mb-1 px-1">Nombre de Usuario</label>
                    <input type="text" name="usuario" required
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50 focus:bg-white"
                        placeholder="Usuario del sistema">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-scantec-blue uppercase tracking-widest mb-1 px-1">Nueva Contraseña</label>
                    <input type="password" name="nueva" required
                        pattern="[a-zA-Z0-9$@.-]{7,50}"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50 focus:bg-white"
                        placeholder="Mínimo 7 caracteres">
                </div>

                <div class="pt-2 space-y-4">
                    <button type="submit" class="w-full bg-scantec-blue hover:bg-scantec-red text-white font-montserrat font-bold py-4 rounded-xl transition-all shadow-lg tracking-widest uppercase text-xs active:scale-95">
                        Actualizar Contraseña
                    </button>
                    
                    <div class="text-center">
                        <a href="<?php echo base_url(); ?>" class="text-scantec-gray hover:text-scantec-blue text-[10px] font-bold uppercase tracking-widest transition-colors inline-flex items-center gap-2">
                            <span>←</span> Volver al inicio de sesión
                        </a>
                    </div>
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