<?php
if (isset($_SESSION['alert'])) {
    $type = $_SESSION['alert']['type'] ?? 'info';
    $msg = $_SESSION['alert']['message'] ?? '';
    $typeJson = json_encode((string) $type, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $msgJson = json_encode((string) $msg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: $typeJson,
                title: 'Notificacion Scantec',
                text: $msgJson,
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
    <title>Scantec DMS - Gestion Documental</title>

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
            <?php
            $__funcionalidadesSistema = [];
            try {
                if (!class_exists('FuncionalidadesModel')) { require_once 'Models/FuncionalidadesModel.php'; }
                $__funcionalidadesModel = new FuncionalidadesModel();
                $__funcionalidadesSistema = $__funcionalidadesModel->selectEstadosSecciones();
            } catch (Throwable $e) {
                $__funcionalidadesSistema = [];
            }

            $__seccionHabilitada = function (string $clave) use ($__funcionalidadesSistema) {
                return intval($__funcionalidadesSistema[$clave] ?? 1) === 1;
            };

            $__esAdminDash = intval($_SESSION['id_rol'] ?? 0) <= 2;
            $__puedeVerDash = $__esAdminDash;
            if (!$__esAdminDash) {
                try {
                    if (!class_exists('SeguridadLegajosModel')) { require_once 'Models/SeguridadLegajosModel.php'; }
                    $__segModelDash = new SeguridadLegajosModel();
                    $__puedeVerDash = $__segModelDash->tienePermisoLegajo(intval($_SESSION['id_rol'] ?? 0), 'dashboard_legajos');
                } catch (Throwable $e) {
                    $__puedeVerDash = false;
                }
            }

            $__esAdmin = intval($_SESSION['id_rol'] ?? 0) <= 2;
            $__permisosLegajo = [];
            if (!$__esAdmin) {
                try {
                    if (!class_exists('SeguridadLegajosModel')) { require_once 'Models/SeguridadLegajosModel.php'; }
                    $__segModel = new SeguridadLegajosModel();
                    $__permisosLegajo = $__segModel->selectPermisosLegajosPorRol(intval($_SESSION['id_rol'] ?? 0));
                } catch (Throwable $e) {
                    $__permisosLegajo = [];
                }
            }

            $__puedeVerLegajo = function (string $accion) use ($__esAdmin, $__permisosLegajo) {
                if ($__esAdmin) {
                    return true;
                }
                return !empty($__permisosLegajo[$accion]);
            };
            ?>

            <?php if ($__seccionHabilitada('dashboard') && $__puedeVerDash): ?>
            <a href="<?php echo base_url(); ?>dashboard/dashboard_legajos" class="flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-colors">
                <span class="w-8 text-center"><i class="fas fa-chart-line text-scantec-white"></i></span>
                <span class="ml-3 font-medium text-sm">Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if ($__seccionHabilitada('archivos')): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-archivos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-folder-open text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Archivos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-archivos"></i>
                </button>
                <div id="menu-archivos" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>expedientes/indice_busqueda" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Buscador</a>
                    <a href="<?php echo base_url(); ?>expedientes/reporte" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Reporte de expedientes</a>
                    <?php if (intval($_SESSION['id_rol'] ?? 0) <= 2): ?>
                    <a href="<?php echo base_url(); ?>expedientes/upload_files" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Subir archivos</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($__seccionHabilitada('legajos') && ($__puedeVerLegajo('armar_legajo') || $__puedeVerLegajo('buscar_legajos') || $__puedeVerLegajo('verificar_legajos') || $__puedeVerLegajo('administrar_legajos'))): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-legajos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-layer-group text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Legajos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-legajos"></i>
                </button>
                <div id="menu-legajos" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__puedeVerLegajo('armar_legajo')): ?>
                    <a href="<?php echo base_url(); ?>legajos/armar_legajo" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Armar legajo</a>
                    <?php endif; ?>
                    <?php if ($__puedeVerLegajo('buscar_legajos')): ?>
                    <a href="<?php echo base_url(); ?>legajos/buscar_legajos" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Buscar legajos</a>
                    <?php endif; ?>
                    <?php if ($__puedeVerLegajo('verificar_legajos')): ?>
                    <a href="<?php echo base_url(); ?>legajos/verificar_legajos" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Verificar legajos</a>
                    <?php endif; ?>
                    <?php if ($__puedeVerLegajo('administrar_legajos')): ?>
                    <a href="<?php echo base_url(); ?>legajos/administrar_legajos" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Administrar legajos</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($__seccionHabilitada('unir_pdf') && in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2, 3], true)): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-pdf')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-file-pdf"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Unir PDF's</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-pdf"></i>
                </button>
                <div id="menu-pdf" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>unirpdf/unir_documentos" class="block py-2 text-xs text-white/60 hover:text-white">Unir Documentos</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (intval($_SESSION['id_rol'] ?? 0) === 1): ?>
            <div class="pt-4 pb-2 px-3 text-[12px] uppercase font-bold text-scantec-white tracking-widest border-t border-white/5 mt-4">Sistema</div>

            <?php $__mostrarAdministracion = $__seccionHabilitada('configuracion') || $__seccionHabilitada('alertas') || $__seccionHabilitada('archivos'); ?>
            <?php if ($__mostrarAdministracion): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-admin')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all text-red-100">
                    <span class="w-8 text-center"><i class="fas fa-cog"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Administracion</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-admin"></i>
                </button>
                <div id="menu-admin" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__seccionHabilitada('configuracion')): ?>
                    <a href="<?php echo base_url(); ?>configuracion/listar" class="block py-2 text-xs text-white/60 hover:text-white">Empresa</a>
                    <a href="<?php echo base_url(); ?>configuracion/mantenimiento" class="block py-2 text-xs text-white/60 hover:text-white">Backup</a>
                    <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Matriz de legajos</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('archivos')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/grupo" class="block py-2 text-xs text-white/60 hover:text-white">Grupos</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('alertas')): ?>
                    <a href="<?php echo base_url(); ?>alerta/listar" class="block py-2 text-xs text-white/60 hover:text-white">Alertas Programadas</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php $__mostrarUsuariosAccesos = $__seccionHabilitada('usuarios') || $__seccionHabilitada('seguridad') || $__seccionHabilitada('legajos'); ?>
            <?php if ($__mostrarUsuariosAccesos): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-usuarios-accesos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-user-shield text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Usuarios y Accesos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-usuarios-accesos"></i>
                </button>
                <div id="menu-usuarios-accesos" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__seccionHabilitada('seguridad')): ?>
                    <a href="<?php echo base_url(); ?>seguridad/roles" class="block py-2 text-xs text-white/60 hover:text-white">Roles</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('usuarios')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/listar" class="block py-2 text-xs text-white/60 hover:text-white">Gestion Usuarios</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('legajos')): ?>
                    <a href="<?php echo base_url(); ?>seguridad/permisos_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Permisos Legajos</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="menu-group">
                <button onclick="toggleMenu('menu-funcionalidades')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-puzzle-piece text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Funcionalidades</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-funcionalidades"></i>
                </button>
                <div id="menu-funcionalidades" class="hidden pl-11 space-y-1 mt-1">
                    <a href="<?php echo base_url(); ?>funcionalidades/listar" class="block py-2 text-xs text-white/60 hover:text-white">Gestion de modulos</a>
                </div>
            </div>

            <?php $__mostrarAuditoria = $__seccionHabilitada('auditoria') || $__seccionHabilitada('archivos'); ?>
            <?php if ($__mostrarAuditoria): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-auditoria')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-history"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Auditoria</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-auditoria"></i>
                </button>
                <div id="menu-auditoria" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__seccionHabilitada('archivos')): ?>
                    <a href="<?php echo base_url(); ?>logsumango/views" class="block py-2 text-xs text-white/60 hover:text-white">Log Documentos</a>
                    <a href="<?php echo base_url(); ?>logs/registro_views" class="block py-2 text-xs text-white/60 hover:text-white">Visitas Archivos</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('auditoria')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/activos" class="block py-2 text-xs text-white/60 hover:text-white">Monitor de conexiones</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('auditoria')): ?>
                    <a href="<?php echo base_url(); ?>legajos/log_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Log Legajos</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('auditoria')): ?>
                    <a href="<?php echo base_url(); ?>logs/views" class="block py-2 text-xs text-white/60 hover:text-white">Log Sistema</a>
                    <a href="<?php echo base_url(); ?>logs/registro_session_fail" class="block py-2 text-xs text-white/60 hover:text-white">Fallos Sesion</a>
                    <a href="<?php echo base_url(); ?>logs/registro_sesiones" class="block py-2 text-xs text-white/60 hover:text-white">Sesiones</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
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
                            <i class="fas fa-sign-out-alt w-6"></i> Cerrar Sesion
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
