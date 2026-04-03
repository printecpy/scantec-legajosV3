<?php
/**
 * @property DashboardModel $model
 */
    class Dashboard extends Controllers{
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
        public function selectPrestamo()
        {
            $sql = "SELECT * FROM prestamo WHERE estado = 1";
            $res = $this->model->select_all($sql);
            return $res;
        }
   
        public function listar()
        {
            $this->dashboard_legajos();
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
            $esAdministradorTotalDashboard = ($idRol === 1);
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

        public function dashboard_legajos()
        {
            $scope = $this->obtenerScopeLegajosDashboard();
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
            $cant_legajos = $this->model->selectLegajosCantidad($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_proceso = $this->model->selectLegajosProceso($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_completados = $this->model->selectLegajosCompletados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_rechazados = $this->model->selectLegajosRechazados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_verificados = $this->model->selectLegajosVerificados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_cerrados = $this->model->selectLegajosCerrados($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $cant_legajos_activos = $this->model->selectLegajosActivos($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $pagina_procesada = $this->model->selectPaginasProcesada();
            $cant_faltante = $this->model->selectCantFaltante();
            $porc_avanza = $this->model->selectPorcAvanza();
            $logs_uman = $this->model->selectLogs_uman();
            $usuarios_activos = $this->model->selectUsuariosActivos();
            $cant_porIndice = $this->model->selectCant_porIndice();
            $exp_consultados = $this->model->selectExpConsultadosDia();
            $legajos_por_tipo = $this->model->selectLegajosPorTipo($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $legajos_por_usuario = $this->model->selectLegajosPorUsuario($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $productividad_solicitudes = $this->model->selectProductividadSolicitudesPorUsuario($periodo_config['cantidad'], $periodo_config['unidad'], $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $docs_vigentes = $this->model->selectDocumentosLegajoVigentes($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $docs_por_vencer = $this->model->selectDocumentosLegajoPorVencer(30, $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $docs_vencidos = $this->model->selectDocumentosLegajoCriticos($scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $archiv_tipoDoc = $this->model->selectarchivosTipoDoc();
            $archiv_tipoDoc2 = $this->model->selectCantArchivosDoc();
            $legajos_armados = $this->model->selectLegajosArmadosPorFechaUsuario($periodo_config['cantidad'], $periodo_config['unidad'], $scope['tipos_permitidos'], $scope['id_usuario'], $scope['solo_propios']);
            $data = ['cant_legajos'=> $cant_legajos, 'cant_legajos_proceso' => $cant_legajos_proceso, 'cant_legajos_completados' => $cant_legajos_completados, 'cant_legajos_rechazados' => $cant_legajos_rechazados, 'cant_legajos_verificados' => $cant_legajos_verificados, 'cant_legajos_cerrados' => $cant_legajos_cerrados, 'cant_legajos_activos' => $cant_legajos_activos, 'pagina_procesada' => $pagina_procesada,
            'cant_faltante' => $cant_faltante, 'porc_avanza' => $porc_avanza,
            'logs_uman' => $logs_uman, 'usuarios_activos' => $usuarios_activos,  'cant_porIndice' => $cant_porIndice,
            'exp_consultados' => $exp_consultados, 'legajos_por_tipo' => $legajos_por_tipo, 'legajos_por_usuario' => $legajos_por_usuario, 'productividad_solicitudes' => $productividad_solicitudes, 'periodo_productividad' => $periodo_productividad, 'docs_vigentes' => $docs_vigentes, 'docs_por_vencer' => $docs_por_vencer, 'docs_vencidos' => $docs_vencidos, 'archiv_tipoDoc' => $archiv_tipoDoc, 'archiv_tipoDoc2' => $archiv_tipoDoc2, 'legajos_armados' => $legajos_armados];
            $data['dashboard_cards'] = $scope['cards'];
            $data['dashboard_scope_solo_propios'] = $scope['solo_propios'];
            $data['puede_verificar_legajos'] = !empty($scope['puede_verificar_legajos']);
            $data['tipos_legajo_disponibles_dashboard'] = $tiposDisponiblesFiltro;
            $data['tipos_legajo_seleccionados_dashboard'] = $tiposSeleccionados;
            $this->views->getView($this, "dashboard_legajos", $data);
        }

        public function listar_legajos()
        {
            $this->dashboard_legajos();
        }

        public function getChartData() {
            return $this->model->getChartData();
        }

       public function registrar()
        {
            $expediente = $_POST['expediente'];
            $funcionario = $_POST['funcionario'];
            $especialidad = $_POST['especialidad'];
          //  $cantidad = $_POST['cantidad'];
            $fecha_prestamo = $_POST['fecha_prestamo'];
            $fecha_devolucion = $_POST['fecha_devolucion'];
            $observacion = $_POST['observacion'];
           // $cantidadActual = $this->model->selectLibrosCantidad($libro);
           $insert = $this->model->insertarPrestamo($funcionario, $expediente, $especialidad, $fecha_prestamo, $fecha_devolucion, $observacion);
               // $total = ($cantidadActual['cantidad'] - $cantidad);
             //   $this->model->actualizarCantidad($total, $libro);
                if ($insert) {
                    header("location: " . base_url() . "admin/listar");
                    die();
                } 
          /*  if ($cantidadActual['cantidad'] < $cantidad) {
                header("location: " . base_url() . "admin/listar?no_s");
            }else{
                
            } */
            
        } 
         public function devolver()
        {
            $id = $_POST['id'];
            /* $cantidadprestado = $this->model->selectPrestamoCantidad($id);
            $cantidadActual = $this->model->selectLibrosCantidad($cantidadprestado['id']);
            $total = ($cantidadActual['cantidad'] + $cantidadprestado['cantidad']); */
            $prest = $this->model->estadoPrestamo(0 , $id);
          // $actualizado = $this->model->actualizarCantidad($total, $cantidadprestado['id_libro']);
            if ($prest) {
                header("location: " . base_url() . "admin/listar");
                die();
            }
        } 
        public function pdf()
        {
        $datos = $this->model->selectDatos();
        $prestamo = $this->model->selectPrestamoDebe();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('P', 'mm', 'letter');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetTitle("Prestamos");
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(195, 5, mb_convert_encoding($datos['nombre'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

        $pdf->image(base_url() . "/Assets/img/logo.jpg", 180, 10, 30, 30, 'JPG');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, mb_convert_encoding("Teléfono: ", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, mb_convert_encoding("Dirección: ", 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, mb_convert_encoding($datos['direccion'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, mb_convert_encoding($datos['correo'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(196, 5, "Detalle de Prestamos", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(14, 5, mb_convert_encoding('N°', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(50, 5, mb_convert_encoding('Estudiantes', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
        $pdf->Cell(87, 5, 'Libros', 1, 0, 'L');
        $pdf->Cell(30, 5, 'Fecha Prestamo', 1, 0, 'L');
        $pdf->Cell(15, 5, 'Cant.', 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $contador = 1;
        foreach ($prestamo as $row) {
            $pdf->Cell(14, 5, $contador, 1, 0, 'L');
            $pdf->Cell(50, 5, $row['nombre'], 1, 0, 'L');
            $pdf->Cell(87, 5, mb_convert_encoding($row['titulo'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
            $pdf->Cell(30, 5, $row['fecha_prestamo'], 1, 0, 'L');
            $pdf->Cell(15, 5, $row['cantidad'], 1, 1, 'L');
            $contador++;
        }
        $pdf->Output("prestamos.pdf", "I");
        }
    
    }
