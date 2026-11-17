<?php
/**
 * Configuración Maestra de Estilos - Scantec DMS
 * Centraliza CSP, Fuentes y Colores. Tailwind va compilado (Assets/css/tailwind.css): no usa CDN ni internet.
 */
?>
<meta http-equiv="Content-Security-Policy" content="
    default-src 'self';
    script-src 'self' 'unsafe-inline' blob:;
    style-src 'self' 'unsafe-inline';
    font-src 'self' data:;
    img-src 'self' data: blob: <?php echo base_url(); ?>;
    connect-src 'self';
    frame-src 'self' blob: data:;">

<style>
    <?php include "Assets/css/fonts.css"; ?>

    :root {
        --scantec-blue: #182541;
        --scantec-red: #dc153d;
        --scantec-white: #e3e3e3;
        --scantec-gray: #878787;
        --scantec-black: #1d1d1b;
    }

    /* Clase global para el fondo de login */
    .bg-login-custom {
        background-image: linear-gradient(rgba(24, 37, 65, 0.55), rgba(24, 37, 65, 0.55)),
                          url('<?php echo base_url(); ?>Assets/img/fondo_login.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }
</style>

<link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/tailwind.css?v=<?php echo (int) @filemtime(ROOT_PATH . 'Assets/css/tailwind.css'); ?>">