<?php
// Lógica de Alertas SweetAlert2 integrada
if (isset($_SESSION['alert'])) {
    $type = $_SESSION['alert']['type'];
    $msg = $_SESSION['alert']['message'];
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ 
                icon: '$type', 
                title: 'Notificación Scantec', 
                text: '$msg', 
                confirmButtonColor: '#182541' 
            });
        });
    </script>";
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec DMS - Gestión Documental</title>
    
    <?php include "Views/template/config_tailwind.php"; ?>

    <link rel="icon" href="<?php echo base_url(); ?>Assets/img/icoScantec-copia2.ico">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/estilo.css">

    <style>
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .rotate-icon { transition: transform 0.2s ease-in-out; }
        .rotate-180 { transform: rotate(180deg); }
        .menu-item-active { border-left: 4px solid var(--scantec-red); background: rgba(255,255,255,0.05); }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased text-scantec-black">

<div class="flex min-h-screen">
    <aside id="sidebar" class="sidebar-transition w-64 bg-scantec-blue text-white flex-shrink-0 flex flex-col shadow-2xl z-50">
        <div class="h-16 flex items-center justify-center border-b border-white/10 bg-black/10">
            <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" class="h-10 bg-white p-1 rounded" alt="Scantec Logo">
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar py-4 px-3 space-y-1">
            
            <a href="<?php echo base_url(); ?>dashboard/listar" class="flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-colors">
                <span class="w-8 text-center"><i class="fas fa-chart-line text-scantec-white"></i></span>
                <span class="ml-3 font-medium text-sm">Dashboard</span>
            </a>

            <div class="menu-group">
                <button onclick="toggleMenu('menu-archivos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-folder-open text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Archivos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-archivos"></i>
                </button>
                <div id="menu-archivos" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>expedientes/indice_busqueda" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">• Buscador</a>
                    <?php if ($_SESSION['id_rol'] <= 2): ?>
                        <a href="<?php echo base_url(); ?>expedientes/upload_files" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">• Subir archivos</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (in_array($_SESSION['id_rol'], [1, 2, 3])): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-pdf')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-file-pdf"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Unir PDF's</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-pdf"></i>
                </button>
                <div id="menu-pdf" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>unirpdf/unir_documentos" class="block py-2 text-xs text-white/60 hover:text-white">• Unir Documentos</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- <?php if (in_array($_SESSION['id_rol'], [1, 2, 3])): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-procesos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-cogs"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Procesos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-procesos"></i>
                </button>
                <div id="menu-procesos" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>lotes" class="block py-2 text-xs text-white/60 hover:text-white">• Registro de lotes</a>
                    <a href="<?php echo base_url(); ?>procesos" class="block py-2 text-xs text-white/60 hover:text-white">• Registro de cajas</a>
                    <a href="<?php echo base_url(); ?>ordenamiento" class="block py-2 text-xs text-white/60 hover:text-white">• Ordenamiento físico</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($_SESSION['id_rol'] == 1): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-operativo')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-tasks"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Operativo</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-operativo"></i>
                </button>
                <div id="menu-operativo" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>operadores" class="block py-2 text-xs text-white/60 hover:text-white">• Operadores</a>
                    <a href="<?php echo base_url(); ?>preparado" class="block py-2 text-xs text-white/60 hover:text-white">• Preparación</a>
                    <a href="<?php echo base_url(); ?>escaneos" class="block py-2 text-xs text-white/60 hover:text-white">• Escaneos</a>
                    <a href="<?php echo base_url(); ?>control" class="block py-2 text-xs text-white/60 hover:text-white">• Control</a>
                    <a href="<?php echo base_url(); ?>indexar" class="block py-2 text-xs text-white/60 hover:text-white">• Indexado</a>
                    <a href="<?php echo base_url(); ?>reagrupado" class="block py-2 text-xs text-white/60 hover:text-white">• Reagrupado</a>
                </div>
            </div>
            <?php endif; ?> -->

            <?php if ($_SESSION['id_rol'] == 1): ?>
            <div class="pt-4 pb-2 px-3 text-[12px] uppercase font-bold text-scantec-white tracking-widest border-t border-white/5 mt-4">Sistema</div>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-admin')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all text-red-100">
                    <span class="w-8 text-center"><i class="fas fa-user-shield"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Administración</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-admin"></i>
                </button>
                <div id="menu-admin" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>configuracion/listar" class="block py-2 text-xs text-white/60 hover:text-white">Configuración</a>
                    <a href="<?php echo base_url(); ?>configuracion/mantenimiento" class="block py-2 text-xs text-white/60 hover:text-white">Backup</a>
                    <a href="<?php echo base_url(); ?>usuarios/listar" class="block py-2 text-xs text-white/60 hover:text-white">Gestión Usuarios</a>
                    <a href="<?php echo base_url(); ?>usuarios/grupo" class="block py-2 text-xs text-white/60 hover:text-white">Grupos</a>
                    <a href="<?php echo base_url(); ?>usuarios/activos" class="block py-2 text-xs text-white/60 hover:text-white">Conexiones</a>
                    <a href="<?php echo base_url(); ?>alerta/listar" class="block py-2 text-xs text-white/60 hover:text-white">Alertas Programadas</a>
                    <!-- <button onclick="toggleMenu('menu-alertas')" class="w-full text-left py-2 text-[11px] text-white/60 hover:text-white flex justify-between pr-4 items-center">
                        Alertas Programadas <i class="fas fa-caret-down text-[9px]"></i>
                    </button>
                    <div id="menu-alertas" class="hidden pl-3 border-l border-white/10 space-y-1">
                        <a href="<?php echo base_url(); ?>alerta/listar" class="block py-2 text-[10px]">• Tareas</a>
                         <a href="<?php echo base_url(); ?>alerta/historial" class="block py-2 text-[10px]">• Historial</a> 
                    </div>-->
                </div>
            </div>

            <div class="menu-group">
                <button onclick="toggleMenu('menu-auditoria')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-history"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Auditoría</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-auditoria"></i>
                </button>
                <div id="menu-auditoria" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>logsumango/views" class="block py-2 text-xs text-white/60 hover:text-white">• Log Documentos</a>
                    <a href="<?php echo base_url(); ?>logs/views" class="block py-2 text-xs text-white/60 hover:text-white">• Log Sistema</a>
                    <a href="<?php echo base_url(); ?>logs/registro_views" class="block py-2 text-xs text-white/60 hover:text-white">• Visitas Archivos</a>
                    <a href="<?php echo base_url(); ?>logs/registro_session_fail" class="block py-2 text-xs text-white/60 hover:text-white">• Fallos Sesión</a>
                    <a href="<?php echo base_url(); ?>logs/registro_sesiones" class="block py-2 text-xs text-white/60 hover:text-white">• Sesiones</a>
                </div>
            </div>
            <?php endif; ?>

        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 shadow-sm z-40">
            <button onclick="toggleSidebar()" class="p-2 rounded-lg hover:bg-gray-100 text-scantec-blue transition-colors outline-none">
                <i class="fas fa-bars fa-lg"></i>
            </button>

            <div class="flex items-center space-x-4 relative group">
                <div class="text-right hidden sm:block leading-tight">
                    <p class="text-[11px] font-bold text-scantec-blue uppercase tracking-tighter"><?php echo $_SESSION['nombre']; ?></p>
                    <p class="text-[9px] text-scantec-gray font-bold uppercase tracking-widest mt-0.5">Scantec Auth</p>
                </div>
                
                <button class="flex items-center focus:outline-none bg-scantec-blue/5 p-2 rounded-full hover:bg-scantec-blue/10 transition-colors">
                    <i class="fas fa-user-circle fa-2x text-scantec-blue"></i>
                </button>
                
                <div class="absolute right-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 overflow-hidden mt-1">
                    <div class="py-2">
                        <a href="<?php echo base_url(); ?>Usuarios/editar?id=<?php echo $_SESSION['id']; ?>" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-edit w-6 text-scantec-blue"></i> Editar Perfil
                        </a>
                        <a href="<?php echo base_url(); ?>usuarios/cambiar_pass" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-shield-alt w-6 text-scantec-blue"></i> Seguridad
                        </a>
                        <a href="<?php echo base_url(); ?>usuarios/Ayuda" target="_blank" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-question-circle w-6 text-scantec-blue"></i> Centro de Ayuda
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="<?php echo base_url(); ?>usuarios/salir" class="flex items-center px-4 py-3 text-sm text-scantec-red font-black hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-6"></i> Cerrar Sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">