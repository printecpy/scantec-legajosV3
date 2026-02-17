<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Acceso</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    // Los datos ya vienen limpios gracias a htmlspecialchars en setAlert
                    icon: '<?php echo $_SESSION['alert']['type']; ?>',
                    title: 'Atención',
                    text: '<?php echo $_SESSION['alert']['message']; ?>',
                    confirmButtonColor: '#182541',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-8">
            <div class="flex justify-center mb-8">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Scantec Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-2xl font-montserrat font-bold text-scantec-blue text-center mb-2 uppercase tracking-wide">
                Acceso al Sistema</h2>
            <p class="text-scantec-gray text-center text-sm mb-8">Por favor, ingrese sus credenciales para continuar.
            </p>

            <form id="loginForm" action="<?php echo base_url(); ?>Usuarios/login" method="POST" class="space-y-6">
                <div>
                    <label
                        class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Usuario</label>
                    <input type="text" name="usuario" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                        placeholder="Ej: admin">
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-scantec-blue uppercase tracking-widest mb-2 px-1">Contraseña</label>
                    <input type="password" name="clave" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-scantec-blue focus:border-transparent outline-none transition-all bg-gray-50/50"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-end">
                    <a href="<?php echo base_url(); ?>home/restablecer_pw"
                        class="text-scantec-red hover:underline font-bold text-xs uppercase">¿Olvidaste tu
                        contraseña?</a>
                </div>

                <button type="submit"
                    class="w-full bg-scantec-blue hover:bg-scantec-red text-white font-montserrat font-bold py-4 rounded-xl transition-all shadow-lg tracking-widest">
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