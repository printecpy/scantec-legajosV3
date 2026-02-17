<?php
/**
 * Configuración Maestra de Estilos - Scantec DMS
 * Centraliza CSP, Fuentes, Colores y Tailwind CDN
 */
?>
<meta http-equiv="Content-Security-Policy" content="
    default-src 'self'; 
    script-src 'self' https://cdn.tailwindcss.com https://cdn.jsdelivr.net 'unsafe-inline' 'unsafe-eval' blob:; 
    style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com; 
    font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com;
    img-src 'self' data: blob: <?php echo base_url(); ?>;
    connect-src 'self' https://cdn.tailwindcss.com;
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

<script src="https://cdn.tailwindcss.com"></script>

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'scantec-blue': 'var(--scantec-blue)',
                    'scantec-red': 'var(--scantec-red)',
                    'scantec-white': 'var(--scantec-white)',
                    'scantec-gray': 'var(--scantec-gray)',
                    'scantec-black': 'var(--scantec-black)',
                },
                fontFamily: {
                    sans: ['Roboto', 'sans-serif'],
                    montserrat: ['Montserrat', 'sans-serif'],
                }
            }
        }
    }
</script>