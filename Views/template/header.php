<?php
if (isset($_SESSION['alert'])) {
    $type = $_SESSION['alert']['type'] ?? 'info';
    $msg = $_SESSION['alert']['message'] ?? '';
    $typeJson = json_encode((string) $type, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $msgJson = json_encode((string) $msg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    echo "<script>
        function __scantecRestaurarScroll() {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.documentElement.style.paddingRight = '';
            document.body.style.paddingRight = '';
            document.body.classList.remove('swal2-shown', 'swal2-height-auto');
            document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto');
        }
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: $typeJson,
                title: 'Notificación Scantec',
                text: $msgJson,
                confirmButtonColor: '#182541',
                didClose: __scantecRestaurarScroll,
                willClose: __scantecRestaurarScroll
            });
        });
    </script>";
    unset($_SESSION['alert']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo defined('APP_LANG') ? htmlspecialchars(APP_LANG, ENT_QUOTES, 'UTF-8') : 'es'; ?>">
<head>
    <meta charset="<?php echo defined('APP_CHARSET') ? htmlspecialchars(APP_CHARSET, ENT_QUOTES, 'UTF-8') : 'UTF-8'; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scantec DMS - Gestión Documental</title>

    <?php include "Views/template/config_tailwind.php"; ?>

    <link rel="icon" href="<?php echo base_url(); ?>Assets/img/icoScantec-copia2.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/select2.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>Assets/css/estilo.css">

    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #f3f4f6;
        }
        .swal2-container:not(.swal2-backdrop-show):not(.swal2-noanimation) {
            display: none !important;
        }
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .rotate-icon { transition: transform 0.2s ease-in-out; }
        .rotate-180 { transform: rotate(180deg); }
        .sidebar-collapsed {
            width: 0 !important;
            min-width: 0 !important;
            overflow: hidden !important;
            transform: translateX(-0.5rem);
            box-shadow: none !important;
        }
        .content-expanded {
            width: 100%;
        }
        .sidebar-restore-visible {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }
        .menu-item-active {
            border-left: 4px solid var(--scantec-red);
            background: rgba(255,255,255,0.08);
            color: #ffffff !important;
        }
        .menu-parent-active {
            background: rgba(255,255,255,0.12) !important;
            color: #ffffff !important;
            box-shadow: inset 4px 0 0 var(--scantec-red);
        }
        .menu-subitem-active {
            color: #ffffff !important;
            font-weight: 700;
            padding-left: 0.75rem;
            border-left: 3px solid rgba(255,255,255,0.85);
            background: rgba(255,255,255,0.06);
            border-radius: 0 0.5rem 0.5rem 0;
        }
        .btn-action {
            width: 2.5rem;
            height: 2.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.2s ease-in-out;
        }
        .btn-action:hover { transform: translateY(-1px); }
        .btn-action-primary { background: var(--scantec-blue); }
        .btn-action-primary:hover { background: #1d4ed8; }
        .btn-action-danger { background: #b91c1c; }
        .btn-action-danger:hover { background: #7f1d1d; }
        .btn-action-neutral { background: #374151; }
        .btn-action-neutral:hover { background: #111827; }
        .btn-action-success { background: #047857; }
        .btn-action-success:hover { background: #065f46; }
        .btn-action-warning { background: #b45309; }
        .btn-action-warning:hover { background: #92400e; }
        .btn-action-disabled {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
            transform: none !important;
        }
    </style>
    <script>
        function __scantecRestaurarScrollGlobal() {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            document.documentElement.style.paddingRight = '';
            document.body.style.paddingRight = '';
            document.body.classList.remove('swal2-shown', 'swal2-height-auto');
            document.documentElement.classList.remove('swal2-shown', 'swal2-height-auto');
        }
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.swal2-container').forEach(function (container) {
                const popupVisible = container.querySelector('.swal2-popup.swal2-show');
                if (!container.classList.contains('swal2-backdrop-show') && !popupVisible) {
                    container.remove();
                }
            });
            __scantecRestaurarScrollGlobal();
        });
    </script>
</head>

<body class="m-0 p-0 bg-gray-100 font-sans antialiased text-scantec-black">
<div class="flex min-h-screen">
    <aside id="sidebar" class="sidebar-transition w-64 bg-scantec-blue text-white flex-shrink-0 flex flex-col shadow-2xl z-50">
        <div class="h-16 flex items-center justify-center border-b border-white/10 bg-black/10">
            <img src="<?php echo base_url(); ?>Assets/img/icoScantec2.png" class="h-10 bg-white p-1 rounded" alt="Scantec Logo">
        </div>

        <nav class="flex-1 overflow-y-auto no-scrollbar py-4 px-3 space-y-1">
            <?php
            $__idRol = intval($_SESSION['id_rol'] ?? 0);
            $__idDepartamento = intval($_SESSION['id_departamento'] ?? 0);
            $__esAdministradorScantec = $__idRol === 1 || strtolower(trim((string)($_SESSION['usuario'] ?? ''))) === 'root';

            $__funcionalidadesSistema = [];
            $__accesosRolDepartamento = [];
            try {
                if (!class_exists('FuncionalidadesModel')) { require_once 'Models/FuncionalidadesModel.php'; }
                $__funcionalidadesModel = new FuncionalidadesModel();
                $__funcionalidadesSistema = $__funcionalidadesModel->selectEstadosSecciones();
                $__accesosRolDepartamento = $__funcionalidadesModel->selectAccesosPorRolDepartamento($__idRol, $__idDepartamento);
            } catch (Throwable $e) {
                $__funcionalidadesSistema = [];
                $__accesosRolDepartamento = [];
            }

            // Solo root (rol 1) ignora las restricciones de Gestión de módulos.
            // Todos los demás roles respetan la configuración de funcionalidades_sistema.
            $__seccionHabilitada = function (string $clave) use ($__funcionalidadesSistema, $__esAdministradorScantec) {
                if ($__esAdministradorScantec) {
                    return true;
                }
                return intval($__funcionalidadesSistema[$clave] ?? 1) === 1;
            };

            // Solo root (rol 1) ignora las restricciones por rol/departamento.
            // Otros roles usan la tabla funcionalidades_acceso_rol_departamento.
            $__accesoItem = function (string $clave) use ($__esAdministradorScantec, $__accesosRolDepartamento) {
                if ($__esAdministradorScantec) {
                    return true;
                }
                return intval($__accesosRolDepartamento[$clave] ?? 1) === 1;
            };

            $__puedeVerDash = $__accesoItem('dashboard_legajos');

            // Permisos de legajos: rol 1 tiene todo, otros consultan permisos_legajos
            $__permisosLegajo = [];
            if (!$__esAdministradorScantec) {
                try {
                    if (!class_exists('SeguridadLegajosModel')) { require_once 'Models/SeguridadLegajosModel.php'; }
                    $__segModel = new SeguridadLegajosModel();
                    $__permisosLegajo = $__segModel->selectPermisosLegajosPorRol($__idRol);
                } catch (Throwable $e) {
                    $__permisosLegajo = [];
                }
            }

            $__puedeVerLegajo = function (string $accion) use ($__esAdministradorScantec, $__permisosLegajo, $__accesoItem) {
                if (!$__accesoItem($accion)) {
                    return false;
                }
                if ($__esAdministradorScantec) {
                    return true;
                }
                return !empty($__permisosLegajo[$accion]);
            };

            $__mostrarMenuArchivos = $__seccionHabilitada('archivos') && (
                $__accesoItem('buscador_archivos') ||
                $__accesoItem('reporte_expedientes') ||
                $__accesoItem('subir_archivos')
            );

            $__mostrarMenuLegajos = $__seccionHabilitada('legajos') && (
                $__puedeVerLegajo('armar_legajo') ||
                $__puedeVerLegajo('buscar_legajos') ||
                $__puedeVerLegajo('verificar_legajos') ||
                $__puedeVerLegajo('administrar_legajos')
            );
            ?>

            <?php if ($__seccionHabilitada('dashboard') && $__puedeVerDash): ?>
            <a href="<?php echo base_url(); ?>dashboard/dashboard_legajos" class="flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-colors">
                <span class="w-8 text-center"><i class="fas fa-chart-line text-scantec-white"></i></span>
                <span class="ml-3 font-medium text-sm">Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if ($__mostrarMenuArchivos): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-archivos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-folder-open text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Archivos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-archivos"></i>
                </button>
                <div id="menu-archivos" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__accesoItem('buscador_archivos')): ?>
                    <a href="<?php echo base_url(); ?>expedientes/indice_busqueda" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Buscador</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('reporte_expedientes')): ?>
                    <a href="<?php echo base_url(); ?>expedientes/reporte" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Reporte de expedientes</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('subir_archivos')): ?>
                    <a href="<?php echo base_url(); ?>expedientes/upload_files" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Subir archivos</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($__mostrarMenuLegajos): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-legajos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-layer-group text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Legajos</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-legajos"></i>
                </button>
                <div id="menu-legajos" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__puedeVerLegajo('armar_legajo')): ?>
                    <a href="<?php echo base_url(); ?>legajos/armar_legajo" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Armar legajo</a>
                    <a href="<?php echo base_url(); ?>legajos/solicitar_documentos" class="block py-2 text-xs text-white/60 hover:text-white transition-colors">Solicitar documentos</a>
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

            <?php if ($__seccionHabilitada('unir_pdf') && $__accesoItem('unir_pdf')): ?>
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

            <?php
            $__mostrarAdministracion = $__accesoItem('empresa') ||
                $__accesoItem('roles') ||
                $__accesoItem('matriz_legajos') ||
                $__accesoItem('gestion_usuarios') ||
                $__accesoItem('grupos') ||
                $__accesoItem('alertas_programadas');

            $__mostrarUsuariosAccesos = $__accesoItem('backup') ||
                ($__seccionHabilitada('legajos') && $__puedeVerLegajo('permisos_legajos'));

            $__mostrarAuditoria = ($__seccionHabilitada('archivos') && $__accesoItem('log_documentos')) ||
                ($__seccionHabilitada('archivos') && $__accesoItem('visitas_archivos')) ||
                $__accesoItem('conexiones') ||
                $__accesoItem('log_legajos') ||
                $__accesoItem('log_sistema') ||
                $__accesoItem('fallos_sesion') ||
                $__accesoItem('sesiones');
            
            $__mostrarMenuSistema = $__esAdministradorScantec || $__mostrarAdministracion || $__mostrarUsuariosAccesos || $__mostrarAuditoria;
            ?>
            <?php if ($__mostrarMenuSistema): ?>
            <div class="pt-4 pb-2 px-3 text-[12px] uppercase font-bold text-scantec-white tracking-widest border-t border-white/5 mt-4">Sistema</div>

            <?php if ($__mostrarAdministracion): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-admin')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-cog"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Administración</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-admin"></i>
                </button>
                <div id="menu-admin" class="hidden pl-11 space-y-1 mt-1">

                    <?php if ($__accesoItem('empresa')): ?>
                    <a href="<?php echo base_url(); ?>configuracion/listar" class="block py-2 text-xs text-white/60 hover:text-white">Empresa</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('roles')): ?>
                    <a href="<?php echo base_url(); ?>seguridad/roles" class="block py-2 text-xs text-white/60 hover:text-white">Roles</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('matriz_legajos')): ?>
                    <a href="<?php echo base_url(); ?>configuracion/configuracion_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Matriz de legajos</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('gestion_usuarios')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/listar" class="block py-2 text-xs text-white/60 hover:text-white">Gestión de usuarios</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('grupos')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/grupo" class="block py-2 text-xs text-white/60 hover:text-white">Grupos</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('alertas_programadas')): ?>
                    <a href="<?php echo base_url(); ?>alerta/listar" class="block py-2 text-xs text-white/60 hover:text-white">Alertas Programadas</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($__mostrarUsuariosAccesos): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-usuarios-accesos')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-user-shield text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Seguridad</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-usuarios-accesos"></i>
                </button>
                <div id="menu-usuarios-accesos" class="hidden pl-11 space-y-1 mt-1">

                    <?php if ($__accesoItem('backup')): ?>
                    <a href="<?php echo base_url(); ?>configuracion/mantenimiento" class="block py-2 text-xs text-white/60 hover:text-white">Backup</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('legajos') && $__puedeVerLegajo('permisos_legajos')): ?>
                    <a href="<?php echo base_url(); ?>seguridad/permisos_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Permisos Legajos</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php $__mostrarFuncionalidades = $__esAdministradorScantec || $__accesoItem('funcionalidades_accesos'); ?>
            <?php if ($__mostrarFuncionalidades): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-funcionalidades')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-puzzle-piece text-scantec-white"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Funcionalidades</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-funcionalidades"></i>
                </button>
                <div id="menu-funcionalidades" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__esAdministradorScantec): ?>
                    <a href="<?php echo base_url(); ?>funcionalidades/listar" class="block py-2 text-xs text-white/60 hover:text-white">Gestión de módulos</a>
                    <?php endif; ?>
                    <?php if ($__esAdministradorScantec || $__accesoItem('funcionalidades_accesos')): ?>
                    <a href="<?php echo base_url(); ?>funcionalidades/accesos" class="block py-2 text-xs text-white/60 hover:text-white">Accesos por rol y departamento</a>
                    <?php endif; ?>
                    <?php if ($__esAdministradorScantec || $__accesoItem('reinicio_sistema')): ?>
                    <a href="<?php echo base_url(); ?>configuracion/reinicio_sistema" class="block py-2 text-xs text-scantec-red hover:text-red-300 font-bold uppercase">Reinicio</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($__mostrarAuditoria): ?>
            <div class="menu-group">
                <button onclick="toggleMenu('menu-auditoria')" class="w-full flex items-center px-3 py-3 rounded-lg hover:bg-white/10 transition-all">
                    <span class="w-8 text-center"><i class="fas fa-history"></i></span>
                    <span class="ml-3 font-medium flex-1 text-left text-[11px] uppercase tracking-wider">Auditoría</span>
                    <i class="fas fa-chevron-down text-[10px] rotate-icon" id="icon-menu-auditoria"></i>
                </button>
                <div id="menu-auditoria" class="hidden pl-11 space-y-1 mt-1">
                    <?php if ($__seccionHabilitada('archivos') && $__accesoItem('log_documentos')): ?>
                    <a href="<?php echo base_url(); ?>logsumango/views" class="block py-2 text-xs text-white/60 hover:text-white">Log Documentos</a>
                    <?php endif; ?>
                    <?php if ($__seccionHabilitada('archivos') && $__accesoItem('visitas_archivos')): ?>
                    <a href="<?php echo base_url(); ?>logs/registro_views" class="block py-2 text-xs text-white/60 hover:text-white">Visitas Archivos</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('conexiones')): ?>
                    <a href="<?php echo base_url(); ?>usuarios/activos" class="block py-2 text-xs text-white/60 hover:text-white">Monitor de conexiones</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('log_legajos')): ?>
                    <a href="<?php echo base_url(); ?>legajos/log_legajos" class="block py-2 text-xs text-white/60 hover:text-white">Log Legajos</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('log_sistema')): ?>
                    <a href="<?php echo base_url(); ?>logs/views" class="block py-2 text-xs text-white/60 hover:text-white">Log Sistema</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('fallos_sesion')): ?>
                    <a href="<?php echo base_url(); ?>logs/registro_session_fail" class="block py-2 text-xs text-white/60 hover:text-white">Fallos de sesión</a>
                    <?php endif; ?>
                    <?php if ($__accesoItem('sesiones')): ?>
                    <a href="<?php echo base_url(); ?>logs/registro_sesiones" class="block py-2 text-xs text-white/60 hover:text-white">Sesiones</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </nav>
    </aside>

    <div id="app-shell-content" class="flex-1 flex flex-col min-w-0">
        <header class="h-16 bg-white border-b flex items-center justify-between px-6 shadow-sm z-40">
            <button id="sidebar-toggle-button" onclick="toggleSidebar()" class="inline-flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 text-scantec-blue transition-colors outline-none">
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
                            <i class="fas fa-sign-out-alt w-6"></i> Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <button id="sidebar-restore-button"
            type="button"
            onclick="toggleSidebar(true)"
            class="fixed left-4 top-24 z-50 inline-flex items-center justify-center rounded-full bg-scantec-blue p-3 text-white shadow-xl transition-all opacity-0 pointer-events-none -translate-x-3 hover:bg-blue-800"
            title="Mostrar menú">
            <i class="fas fa-angles-right"></i>
        </button>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">







