<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec - Página no encontrada</title>

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
</head>

<body class="bg-login-custom font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <div class="p-8">
            <div class="flex justify-center mb-6">
                <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" alt="Scantec Logo"
                    class="h-16 w-auto object-contain">
            </div>

            <h2 class="text-4xl font-montserrat font-bold text-scantec-red text-center mb-2 tracking-wide">
                404
            </h2>
            
            <h3 class="text-lg font-bold text-scantec-blue text-center mb-4 uppercase tracking-widest">
                Página no encontrada
            </h3>

            <div class="flex justify-center mb-6">
                 <img src="<?php echo base_url(); ?>Assets/img/error.png" alt="Error 404" 
                      class="w-32 h-auto opacity-80 hover:scale-105 transition-transform duration-300">
            </div>

            <p class="text-scantec-gray text-center text-sm mb-8 px-4">
                Lo sentimos, la dirección URL que intentas buscar no existe, fue movida o no tienes permisos para acceder.
            </p>

            <a href="<?php echo base_url(); ?>" 
               class="block w-full text-center bg-scantec-blue hover:bg-scantec-red text-white font-montserrat font-bold py-4 rounded-xl transition-all shadow-lg tracking-widest uppercase text-sm">
                Volver al Inicio
            </a>
        </div>

        <div class="bg-scantec-blue/5 py-3 text-center border-t border-gray-100">
            <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-[0.2em]">
                © 2023 - <?php echo date("Y"); ?> PRINTEC SA | SCANTEC DMS
            </p>
        </div>
    </div>

</body>
</html>