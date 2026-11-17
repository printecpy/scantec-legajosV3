<?php
/**
 * @property DashboardModel $model
 */
class Dashboard extends Controllers
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
            exit();
        }
        parent::__construct();
    }

    public function listar()
    {
        $this->dashboard();
    }

    // Retrocompatibilidad: cualquier enlace viejo a /dashboard/dashboard_legajos
    // o /dashboard/listar_legajos sigue funcionando, redirigiendo al unificado.
    public function dashboard_legajos()
    {
        $this->dashboard();
    }

    public function listar_legajos()
    {
        $this->dashboard();
    }

    private function obtenerScopeLegajosDashboard(): array
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        $idUsuario = intval($_SESSION['id'] ?? 0);
        $idDepartamento = intval($_SESSION['id_departamento'] ?? 0);

        if (!class_exists('SeguridadLegajosModel')) {
            require_once 'Models/SeguridadLegajosModel.php';
        }
        if (!class_exists('LegajosModel')) {
            require_once 'Models/LegajosModel.php';
        }

        $segModel = new SeguridadLegajosModel();
        $legajosModel = new LegajosModel();
        $rolActual = $segModel->selectRolPorId($idRol);
        $idDepartamentoRol = intval($rolActual['id_departamento'] ?? 0);
        if ($idDepartamentoRol > 0) {
            $idDepartamento = $idDepartamentoRol;
        }
        $tiposDisponibles = $legajosModel->selectTiposLegajo();
        $esAdministradorDashboard = ($idRol === 1);
        $esAdministradorSinRestriccionPropios = in_array($idRol, [1, 2], true);
        $puedeVerOtrosUsuarios = $esAdministradorSinRestriccionPropios || $segModel->puedeVerLegajosOtrosUsuarios($idRol);
        $tiposPermitidos = $esAdministradorDashboard
            ? []
            : ($segModel->obtenerTiposLegajoPermitidosPorRol($idRol, $tiposDisponibles) ?: [-1]);
        $cardsPermitidas = $esAdministradorDashboard
            ? array_fill_keys(array_keys(SeguridadLegajosModel::getDashboardCardsDisponibles()), 1)
            : $segModel->selectDashboardCardsPorRol($idRol);
        $puedeVerificarLegajos = $esAdministradorDashboard || $segModel->tienePermisoLegajo($idRol, 'verificar_legajos');

        return [
            'id_rol' => $idRol,
            'id_usuario' => $idUsuario,
            'id_departamento' => $idDepartamento,
            'solo_propios' => !$puedeVerOtrosUsuarios,
            'tipos_permitidos' => $tiposPermitidos,
            'cards' => $cardsPermitidas,
            'puede_verificar_legajos' => $puedeVerificarLegajos,
        ];
    }

    public function dashboard()
    {
        $scope = $this->obtenerScopeLegajosDashboard();
        $cards = $scope['cards'];

        // --- Filtro por tipo de legajo (igual que antes) ---
        $tiposPermitidosDashboard = $scope['tipos_permitidos'];
        $tiposDisponiblesFiltro = [];
        if (!class_exists('LegajosModel')) {
            require_once 'Models/LegajosModel.php';
        }
        $legajosModel = new LegajosModel();
        $todosLosTipos = $legajosModel->selectTiposLegajo();
        foreach ($todosLosTipos as $tipo) {
            $idTipo = intval($tipo['id_tipo_legajo'] ?? 0);
            if (empty($tiposPermitidosDashboard) || in_array($idTipo, $tiposPermitidosDashboard, true)) {
                $tiposDisponiblesFiltro[] = $tipo;
            }
        }
        $tiposSeleccionados = array_values(array_unique(array_filter(array_map('intval', (array)($_GET['tipos_legajo'] ?? [])))));
        if (!empty($tiposSeleccionados)) {
            $idsTiposDisponibles = array_map(static function ($tipo) {
                return intval($tipo['id_tipo_legajo'] ?? 0);
            }, $tiposDisponiblesFiltro);
            $tiposSeleccionados = array_values(array_intersect($tiposSeleccionados, $idsTiposDisponibles));
            $scope['tipos_permitidos'] = !empty($tiposSeleccionados) ? $tiposSeleccionados : [-1];
        }

        // --- Período de productividad de legajos (igual que antes) ---
        $periodo_productividad = trim((string)($_GET['periodo_productividad'] ?? '1w'));
        $periodos_permitidos = [
            '1d' => ['cantidad' => 1, 'unidad' => 'DAY'],
            '1w' => ['cantidad' => 1, 'unidad' => 'WEEK'],
            '4w' => ['cantidad' => 4, 'unidad' => 'WEEK'],
            '8w' => ['cantidad' => 8, 'unidad' => 'WEEK'],
            '12w' => ['cantidad' => 12, 'unidad' => 'WEEK'],
            '24w' => ['cantidad' => 24, 'unidad' => 'WEEK'],
        ];
        if (!isset($periodos_permitidos[$periodo_productividad])) {
            $periodo_productividad = '1w';
        }
        $periodo_config = $periodos_permitidos[$periodo_productividad];

        // Helper para no repetir la comprobación de card en cada consulta.
        $cardActiva = static function (string $clave) use ($cards): bool {
            return !empty($cards[$clave]);
        };

        // ==========================================================
        //  DATOS DE LEGAJOS — solo se consultan si su card está activa
        // ==========================================================
        $cant_legajos = $this->model->selectLegajosCantidad($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);

        $cant_legajos_proceso = $cardActiva('dashboard_card_legajos_proceso')
            ? $this->model->selectLegajosProceso($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_legajos_proceso' => 0];
        $cant_legajos_completados = $cardActiva('dashboard_card_legajos_completados')
            ? $this->model->selectLegajosCompletados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_legajos_completados' => 0];
        $cant_legajos_rechazados = $cardActiva('dashboard_card_legajos_rechazados')
            ? $this->model->selectLegajosRechazados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_legajos_rechazados' => 0];
        $cant_legajos_verificados = $cardActiva('dashboard_card_legajos_verificados')
            ? $this->model->selectLegajosVerificados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_legajos_verificados' => 0];
        $cant_legajos_cerrados = $cardActiva('dashboard_card_legajos_cerrados')
            ? $this->model->selectLegajosCerrados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_legajos_cerrados' => 0];

        $docs_vigentes = $cardActiva('dashboard_card_docs_vigentes')
            ? $this->model->selectDocumentosLegajoVigentes($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_documentos_vigentes' => 0];
        $docs_por_vencer = $cardActiva('dashboard_card_docs_por_vencer')
            ? $this->model->selectDocumentosLegajoPorVencer(30, $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_documentos_por_vencer' => 0];
        $docs_vencidos = $cardActiva('dashboard_card_docs_vencidos')
            ? $this->model->selectDocumentosLegajoCriticos($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : ['cant_documentos_criticos' => 0];

        $legajos_por_tipo = $cardActiva('dashboard_card_legajos_por_tipo')
            ? $this->model->selectLegajosPorTipo($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : [];
        $legajos_por_usuario = $cardActiva('dashboard_card_legajos_por_usuario')
            ? $this->model->selectLegajosPorUsuario($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : [];

        $mostrarGraficoProductividad = $cardActiva('dashboard_card_grafico_productividad');
        $productividad_solicitudes = $mostrarGraficoProductividad
            ? $this->model->selectProductividadSolicitudesPorUsuario($periodo_config['cantidad'], $periodo_config['unidad'], $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : [];
        $legajos_armados = $mostrarGraficoProductividad
            ? $this->model->selectLegajosArmadosPorFechaUsuario($periodo_config['cantidad'], $periodo_config['unidad'], $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios'])
            : [];

        // ==========================================================
        //  DATOS DE EXPEDIENTES — solo se consultan si su card está activa
        // ==========================================================
        $cant_expedient = $cardActiva('dashboard_card_exp_totales')
            ? $this->model->selectExpedienteCantidad()
            : ['cant_expediente' => 0];
        $exp_consultados = $cardActiva('dashboard_card_exp_consultados')
            ? $this->model->selectExpConsultadosDia()
            : ['exp_consultados' => 0, 'cantidad_documentos' => 0];
        $pagina_procesada = $cardActiva('dashboard_card_exp_paginas')
            ? $this->model->selectPaginasProcesada()
            : ['paginas_procesadas' => 0];
        $usuarios_activos = $cardActiva('dashboard_card_exp_usuarios_online')
            ? $this->model->selectUsuariosActivos()
            : ['cant_usuarios' => 0];

        // Estas dos tablas comparten la misma consulta base (archivos por tipo de doc).
        $necesitaArchivosTipoDoc = $cardActiva('dashboard_card_exp_archivos_por_tipo') || $cardActiva('dashboard_card_exp_paginas_por_tipo');
        $archiv_tipoDoc = $necesitaArchivosTipoDoc ? $this->model->selectarchivosTipoDoc() : [];

        $archiv_tipoDoc2 = $cardActiva('dashboard_card_exp_productividad_15dias')
            ? $this->model->selectCantArchivosDoc()
            : [];
        $logs_uman = $cardActiva('dashboard_card_exp_logs_umango')
            ? $this->model->selectLogs_uman()
            : [];

        // ==========================================================
        //  Ensamblado de $data
        // ==========================================================
        $data = [
            // Legajos
            'cant_legajos' => $cant_legajos,
            'cant_legajos_proceso' => $cant_legajos_proceso,
            'cant_legajos_completados' => $cant_legajos_completados,
            'cant_legajos_rechazados' => $cant_legajos_rechazados,
            'cant_legajos_verificados' => $cant_legajos_verificados,
            'cant_legajos_cerrados' => $cant_legajos_cerrados,
            'docs_vigentes' => $docs_vigentes,
            'docs_por_vencer' => $docs_por_vencer,
            'docs_vencidos' => $docs_vencidos,
            'legajos_por_tipo' => $legajos_por_tipo,
            'legajos_por_usuario' => $legajos_por_usuario,
            'productividad_solicitudes' => $productividad_solicitudes,
            'periodo_productividad' => $periodo_productividad,
            'legajos_armados' => $legajos_armados,
            // Expedientes
            'cant_expedient' => $cant_expedient,
            'exp_consultados' => $exp_consultados,
            'pagina_procesada' => $pagina_procesada,
            'usuarios_activos' => $usuarios_activos,
            'archiv_tipoDoc' => $archiv_tipoDoc,
            'archiv_tipoDoc2' => $archiv_tipoDoc2,
            'logs_uman' => $logs_uman,
        ];

        // Metadatos de scope / permisos (para que la vista sepa qué mostrar)
        $data['dashboard_cards'] = $cards;
        $data['dashboard_scope_solo_propios'] = $scope['solo_propios'];
        $data['puede_verificar_legajos'] = !empty($scope['puede_verificar_legajos']);
        $data['tipos_legajo_disponibles_dashboard'] = $tiposDisponiblesFiltro;
        $data['tipos_legajo_seleccionados_dashboard'] = $tiposSeleccionados;

        $this->views->getView($this, "dashboard", $data);
    }

    public function getChartData()
    {
        return $this->model->getChartData();
    }
}