<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Sesión Activa</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-7">

            <div class="flex justify-center mb-6">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Scantec Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <div class="flex justify-center mb-4">
                <div class="bg-yellow-100 rounded-full p-4">
                    <svg class="h-10 w-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-xl font-montserrat font-bold text-scantec-blue text-center mb-2 uppercase tracking-wide">
                Sesión ya iniciada
            </h2>

            <p class="text-scantec-gray text-center text-sm mb-2">
                El usuario <strong class="text-scantec-blue"><?php echo $data['nombre_usuario'] ?? 'Usuario'; ?></strong>
                ya tiene una sesión activa en otro dispositivo o navegador.
            </p>

            <p class="text-scantec-gray text-center text-sm mb-6">
                ¿Qué desea hacer?
            </p>

            <form action="<?php echo base_url(); ?>usuarios/confirmar_sesion" method="POST" class="space-y-3">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">

                <button type="submit" name="accion" value="cerrar_anterior"
                    class="w-full bg-scantec-blue hover:bg-gray-800 text-white font-montserrat font-bold py-3 rounded-xl transition-all tracking-wide flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-sync-alt"></i>
                    Cerrar sesión anterior y continuar
                </button>

                <button type="submit" name="accion" value="cancelar"
                    class="w-full bg-white hover:bg-gray-50 text-scantec-blue border-2 border-scantec-blue font-montserrat font-bold py-3 rounded-xl transition-all tracking-wide flex items-center justify-center gap-2">
                    <i class="fas fa-times-circle"></i>
                    Cancelar inicio de sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
