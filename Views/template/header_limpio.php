<!DOCTYPE html>
<html lang="<?php echo defined('APP_LANG') ? htmlspecialchars(APP_LANG, ENT_QUOTES, 'UTF-8') : 'es'; ?>">
<head>
    <meta charset="<?php echo defined('APP_CHARSET') ? htmlspecialchars(APP_CHARSET, ENT_QUOTES, 'UTF-8') : 'UTF-8'; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec DMS - Gestión Documental</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <link rel="icon" href="<?php echo base_url(); ?>Assets/img/icoScantec-copia2.ico">
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/vendor/fontawesome/css/all.min.css">
    <script src="<?php echo base_url(); ?>Assets/vendor/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/estilo.css">
    <style>html, body { margin: 0 !important; padding: 0 !important; background: #f3f4f6; }</style>
</head>

<body class="m-0 p-0 bg-gray-100 font-sans antialiased text-scantec-black">
<div class="flex min-h-screen flex-col">
    <header class="h-14 bg-white border-b flex items-center justify-between px-6 shadow-sm">
        <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" class="h-9" alt="Scantec">
        <div class="text-right leading-tight">
            <p class="text-[11px] font-bold text-scantec-blue uppercase tracking-tighter"><?php echo escapar($_SESSION['nombre'] ?? ''); ?></p>
            <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-widest mt-0.5">Scantec Auth</p>
        </div>
    </header>
    <div class="flex-1 flex flex-col min-w-0"></div>