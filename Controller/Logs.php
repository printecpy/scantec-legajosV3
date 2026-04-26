<?php
class Logs extends Controllers
{
    private function fechaIsoValida(string $fecha): bool
    {
        $fecha = trim($fecha);
        if ($fecha === '') {
            return false;
        }

        $date = DateTime::createFromFormat('Y-m-d', $fecha);
        return $date instanceof DateTime && $date->format('Y-m-d') === $fecha;
    }

    private function resolverFiltrosReportePaginasLegajos(): array
    {
        $desde = trim((string)($_GET['desde'] ?? ''));
        $hasta = trim((string)($_GET['hasta'] ?? ''));

        if (!$this->fechaIsoValida($desde)) {
            $desde = date('Y-m-01');
        }

        if (!$this->fechaIsoValida($hasta)) {
            $hasta = date('Y-m-t');
        }

        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
        ];
    }

    private function asegurarAccesoAuditoria(string $itemKey): void
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        if ($idRol === 1) {
            return;
        }

        require_once 'Models/FuncionalidadesModel.php';
        $funcionalidadesModel = new FuncionalidadesModel();
        $idDepartamento = intval($_SESSION['id_departamento'] ?? 0);

        if ($funcionalidadesModel->puedeAccederItemPorContexto($itemKey, $idRol, $idDepartamento)) {
            return;
        }

        $_SESSION['alert_message'] = 'No tienes permiso para acceder a esta página';
        if (!class_exists('UsuariosModel')) {
            require_once 'Models/UsuariosModel.php';
        }
        $usuariosModel = new UsuariosModel();
        $usuariosModel->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
        header('Location: ' . base_url() . FuncionalidadesModel::obtenerRutaRedireccionSegura($idRol, $idDepartamento), true, 302);
        exit();
    }

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    private function checkAdminAccess()
    {
        if ($_SESSION['id_rol'] != 1) {
            $_SESSION['alert_message'] = 'No tienes permiso para acceder a esta página';
            $motivo = 'Acceso no autorizado';
            $usuario = $_SESSION['nombre'];
            // Obtener la instancia de UsuariosModel
            $usuariosModel = new UsuariosModel();
            // Llama al método bloquarPC_IP() del modelo de Usuarios
            $usuariosModel->bloquarPC_IP($usuario, $motivo);
            header('Location: ' . base_url() . 'expedientes/indice_busqueda', true, 302); // Redirigir a la página de índice de búsqueda
            exit();
        }
    }

    // Método auxiliar para verificar el acceso basado en roles específicos
    protected function checkRoleAccess($allowedRoles)
    {
        if (!in_array($_SESSION['id_rol'], $allowedRoles)) {
            $_SESSION['alert_message'] = 'No tienes permiso para acceder a esta página';
            $motivo = 'Acceso no autorizado';
            $usuario = $_SESSION['nombre'];
            // Obtener la instancia de UsuariosModel
            $usuariosModel = new UsuariosModel();
            // Llama al método bloquarPC_IP() del modelo de Usuarios
            $usuariosModel->bloquarPC_IP($usuario, $motivo);
            header('Location: ' . base_url() . 'expedientes/indice_busqueda', true, 302); // Redirigir a la página de índice de búsqueda
            exit();
        }
    }

    public function anotherMethodForSpecificRoles()
    {
        $allowedRoles = [1, 2, 3]; // IDs de roles permitidos
        $this->checkRoleAccess($allowedRoles);
        // Lógica del método
    }

    public function views()
    {
        $this->asegurarAccesoAuditoria('log_sistema');
        $data = $this->model->selectLogs();
        $this->views->getView($this, "listar", $data);
    }

    public function registro_views()
    {
        $this->asegurarAccesoAuditoria('visitas_archivos');
        $registro_views = $this->model->selectViews();
        $data = ['registro_views' => $registro_views];
        $this->views->getView($this, "registro_views", $data);
    }

    public function reporte_paginas_legajos()
    {
        $this->asegurarAccesoAuditoria('reporte_paginas_legajos');
        $filtros = $this->resolverFiltrosReportePaginasLegajos();

        $data = [
            'filtros' => $filtros,
            'totales' => $this->model->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']),
            'resumen_periodos' => $this->model->selectResumenPaginasLegajosPorPeriodo($filtros['desde'], $filtros['hasta']),
            'detalle_legajos' => $this->model->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']),
        ];

        $this->views->getView($this, "reporte_paginas_legajos", $data);
    }

    public function registro_sesiones()
    {
        $this->asegurarAccesoAuditoria('sesiones');
        $registro_sesiones = $this->model->selectSesions();
        $data = ['registro_sesiones' => $registro_sesiones];
        $this->views->getView($this, "registro_sesiones", $data);
    }

    public function registro_session_fail()
    {
        $this->asegurarAccesoAuditoria('fallos_sesion');
        $registro_session_fail = $this->model->selectSesionFails();
        $data = ['registro_session_fail' => $registro_session_fail];
        $this->views->getView($this, "registro_session_fail", $data);
    }
    public function pdf()
    {
        if (ob_get_length()) ob_end_clean();
        $logsumango = $this->model->selectLogs();
        
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Bitácora de Auditoría SQL', 'L', 'LEGAL');
        
        // Cabeceras
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->SetTextColor(0, 0, 0);
        
        $pdf->Cell(35, 6, utf8_decode('Fecha'), 1, 0, 'C', true);
        $pdf->Cell(150, 6, utf8_decode('Execute SQL'), 1, 0, 'C', true);
        $pdf->Cell(150, 6, utf8_decode('Reverse SQL'), 1, 1, 'C', true);

        // Configurar motor multilínea
        $pdf->SetWidths(array(35, 150, 150));
        $pdf->SetAligns(array('C', 'L', 'L'));
        $pdf->SetFont('Arial', '', 7);
        
        // Contenido (Ya no es necesario usar substr para truncar)
        foreach ($logsumango as $row) {
            $pdf->Row(array(
                $row['fecha'],
                utf8_decode($row['executedSQL']),
                utf8_decode($row['reverseSQL'])
            ));
        }
        
        $pdf->Output("Logs_SQL.pdf", "I");
    }

    public function registro_sesionesPdf()
    {
        if (ob_get_length()) ob_end_clean();
        $sesiones = $this->model->selectSesions();
        
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Sesiones Activas e Históricas', 'L', 'A4');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(192, 192, 192);
        
        $pdf->Cell(40, 6, utf8_decode('Fecha Inicio'), 1, 0, 'C', true);
        $pdf->Cell(35, 6, utf8_decode('Dirección IP'), 1, 0, 'C', true);
        $pdf->Cell(50, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', true);
        $pdf->Cell(60, 6, utf8_decode('Nombre Completo'), 1, 0, 'C', true);
        $pdf->Cell(50, 6, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, utf8_decode('Fecha Cierre'), 1, 1, 'C', true);

        $pdf->SetWidths(array(40, 35, 50, 60, 50, 40));
        $pdf->SetAligns(array('C', 'C', 'L', 'L', 'L', 'C'));
        $pdf->SetFont('Arial', '', 8);
        
        foreach ($sesiones as $row) {
            $pdf->Row(array(
                utf8_decode($row['fecha']),
                utf8_decode($row['ip']),
                utf8_decode($row['servidor']),
                utf8_decode($row['nombre']),
                utf8_decode($row['usuario']),
                utf8_decode($row['fecha_cierre'])
            ));
        }
        
        $pdf->Output("Sesiones_Scantec.pdf", "I");
    }

    public function registro_session_failPdf()
    {
        if (ob_get_length()) ob_end_clean();
        $sesionFails = $this->model->selectSesionFails();
        
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Auditoría: Sesiones Fallidas y Bloqueos', 'L', 'A4');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(192, 192, 192);
        
        $pdf->Cell(45, 6, utf8_decode('Usuario / Intento'), 1, 0, 'C', true);
        $pdf->Cell(45, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', true);
        $pdf->Cell(35, 6, utf8_decode('Dirección IP'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, utf8_decode('Fecha y Hora'), 1, 0, 'C', true);
        $pdf->Cell(112, 6, utf8_decode('Motivo de Falla / Bloqueo'), 1, 1, 'C', true);

        $pdf->SetWidths(array(45, 45, 35, 40, 112));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'L'));
        $pdf->SetFont('Arial', '', 8);
        
        foreach ($sesionFails as $row) {
            $pdf->Row(array(
                utf8_decode($row['usuario']),
                utf8_decode($row['nombre_pc']),
                utf8_decode($row['direccion_ip']),
                utf8_decode($row['timestamp']),
                utf8_decode($row['motivo'])
            ));
        }
        
        $pdf->Output("Alertas_Seguridad.pdf", "I");
    }

    public function registro_viewsPdf()
    {
        if (ob_get_length()) ob_end_clean();
        $views = $this->model->selectViews();
        
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Registro de Visualización de Documentos', 'L', 'A4');
        
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(192, 192, 192);
        
        $pdf->Cell(40, 6, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', true);
        $pdf->Cell(35, 6, utf8_decode('Dirección IP'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, utf8_decode('Fecha de Acceso'), 1, 0, 'C', true);
        $pdf->Cell(122, 6, utf8_decode('Documento Visualizado'), 1, 1, 'C', true);

        $pdf->SetWidths(array(40, 40, 35, 40, 122));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'L'));
        $pdf->SetFont('Arial', '', 8);
        
        foreach ($views as $row) {
            $pdf->Row(array(
                utf8_decode($row['usuario']),
                utf8_decode($row['nombre_pc']),
                utf8_decode($row['direccion_ip']),
                utf8_decode($row['fecha']),
                utf8_decode($row['nombre_expediente'])
            ));
        }
        
        $pdf->Output("Views_Documentos.pdf", "I");
    }

    public function reporte_paginas_legajosPdf()
    {
        $this->asegurarAccesoAuditoria('reporte_paginas_legajos');
        $filtros = $this->resolverFiltrosReportePaginasLegajos();
        $totales = $this->model->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']);
        $detalle = $this->model->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']);

        if (ob_get_length()) ob_end_clean();
        require_once 'Helpers/ReportTemplatePDF.php';

        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de paginas procesadas de legajos', 'L', 'A4');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, utf8_decode('Periodo: ' . $filtros['desde'] . ' a ' . $filtros['hasta']), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 7, utf8_decode('Legajos contabilizados: ' . intval($totales['total_legajos'] ?? 0)), 0, 0, 'L');
        $pdf->Cell(90, 7, utf8_decode('Paginas procesadas: ' . intval($totales['total_paginas'] ?? 0)), 0, 0, 'L');
        $pdf->Cell(90, 7, utf8_decode('Promedio por legajo: ' . number_format((float)($totales['promedio_paginas'] ?? 0), 2, ',', '.')), 0, 1, 'L');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell(18, 6, 'ID', 1, 0, 'C', true);
        $pdf->Cell(28, 6, utf8_decode('Fecha'), 1, 0, 'C', true);
        $pdf->Cell(42, 6, utf8_decode('Tipo'), 1, 0, 'C', true);
        $pdf->Cell(38, 6, utf8_decode('CI'), 1, 0, 'C', true);
        $pdf->Cell(70, 6, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell(32, 6, utf8_decode('Solicitud'), 1, 0, 'C', true);
        $pdf->Cell(20, 6, utf8_decode('Paginas'), 1, 0, 'C', true);
        $pdf->Cell(26, 6, utf8_decode('Estado'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 7);
        foreach ($detalle as $row) {
            $pdf->Cell(18, 6, strval($row['id_legajo']), 1, 0, 'C');
            $pdf->Cell(28, 6, utf8_decode(substr((string)$row['fecha_creacion'], 0, 10)), 1, 0, 'C');
            $pdf->Cell(42, 6, utf8_decode((string)($row['nombre_tipo_legajo'] ?? '')), 1, 0, 'L');
            $pdf->Cell(38, 6, utf8_decode((string)($row['ci_socio'] ?? '')), 1, 0, 'L');
            $pdf->Cell(70, 6, utf8_decode((string)($row['nombre_completo'] ?? '')), 1, 0, 'L');
            $pdf->Cell(32, 6, utf8_decode((string)($row['nro_solicitud'] ?? '')), 1, 0, 'L');
            $pdf->Cell(20, 6, strval(intval($row['cantidad_paginas_procesadas'] ?? 0)), 1, 0, 'C');
            $pdf->Cell(26, 6, utf8_decode((string)($row['estado'] ?? '')), 1, 1, 'C');
        }

        $pdf->Output("Paginas_Legajos.pdf", "I");
    }

    public function excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');
        
        $logs = $this->model->selectLogs();
        $excel = new ReportTemplateExcel('Bitácora de Auditoría SQL', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A4', 'Fecha');
        $sheet->setCellValue('B4', 'Execute SQL');
        $sheet->setCellValue('C4', 'Reverse SQL');
        $sheet->getStyle('A4:C4')->applyFromArray($headerStyle);

        $row = 5;
        foreach ($logs as $value) {
            $sheet->setCellValue('A' . $row, $value["fecha"]);
            $sheet->setCellValue('B' . $row, $value['executedSQL']);
            $sheet->setCellValue('C' . $row, $value["reverseSQL"]);
            $row++;
        }

        // Ajustar columnas SQL con ancho fijo para que baje el texto
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 60, // Execute SQL
            'C' => 60  // Reverse SQL
        ]);

        $excel->output('Logs_SQL_' . date('Y_m_d_His'));
    }

    public function registro_sesionesExcel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php'; 
        date_default_timezone_set('America/Asuncion');
        
        $sesiones = $this->model->selectSesions();
        $excel = new ReportTemplateExcel('Reporte de Sesiones', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A4', 'Fecha Inicio');
        $sheet->setCellValue('B4', 'Dirección IP');
        $sheet->setCellValue('C4', 'Nombre HOST');
        $sheet->setCellValue('D4', 'Nombre Completo');
        $sheet->setCellValue('E4', 'Usuario');
        $sheet->setCellValue('F4', 'Fecha Cierre');
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

        $row = 5;
        foreach ($sesiones as $value) {
            $sheet->setCellValue('A' . $row, $value["fecha"]);
            $sheet->setCellValue('B' . $row, $value['ip']);
            $sheet->setCellValue('C' . $row, $value["servidor"]);
            $sheet->setCellValue('D' . $row, $value["nombre"]);
            $sheet->setCellValue('E' . $row, $value["usuario"]);
            $sheet->setCellValue('F' . $row, $value["fecha_cierre"]);
            $row++;
        }

        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 'auto',
            'C' => 30, // Nombre Host
            'D' => 40, // Nombre Completo
            'E' => 30, // Usuario
            'F' => 'auto'
        ]);

        $excel->output('Sesiones_' . date('Y_m_d_His'));
    }

    public function registro_session_failExcel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php'; 
        date_default_timezone_set('America/Asuncion');
        
        $sesionfails = $this->model->selectSesionFails();
        $excel = new ReportTemplateExcel('Sesiones Fallidas y Bloqueos', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A4', 'Usuario / Intento');
        $sheet->setCellValue('B4', 'Nombre HOST');
        $sheet->setCellValue('C4', 'Dirección IP');
        $sheet->setCellValue('D4', 'Fecha y Hora');
        $sheet->setCellValue('E4', 'Motivo Falla / Bloqueo');
        $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

        $row = 5;
        foreach ($sesionfails as $value) {
            $sheet->setCellValue('A' . $row, $value["usuario"]);
            $sheet->setCellValue('B' . $row, $value['nombre_pc']);
            $sheet->setCellValue('C' . $row, $value["direccion_ip"]);
            $sheet->setCellValue('D' . $row, $value["timestamp"]);
            $sheet->setCellValue('E' . $row, $value["motivo"]);
            $row++;
        }

        $excel->setColumnWidths([
            'A' => 30, // Usuario
            'B' => 30, // Host
            'C' => 'auto',
            'D' => 'auto',
            'E' => 60  // Motivo (Suele ser largo)
        ]);

        $excel->output('Sesiones_Fallidas_' . date('Y_m_d_His'));
    }

    public function registro_viewExcel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php'; 
        date_default_timezone_set('America/Asuncion');
        
        $views = $this->model->selectViews();
        $excel = new ReportTemplateExcel('Registro de Visualizaciones', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A4', 'Usuario');
        $sheet->setCellValue('B4', 'Nombre HOST');
        $sheet->setCellValue('C4', 'Dirección IP');
        $sheet->setCellValue('D4', 'Fecha de Acceso');
        $sheet->setCellValue('E4', 'Documento Visualizado');
        $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

        $row = 5;
        foreach ($views as $value) {
            $sheet->setCellValue('A' . $row, $value["usuario"]);
            $sheet->setCellValue('B' . $row, $value['nombre_pc']);
            $sheet->setCellValue('C' . $row, $value["direccion_ip"]);
            $sheet->setCellValue('D' . $row, $value["fecha"]);
            $sheet->setCellValue('E' . $row, $value["nombre_expediente"]);
            $row++;
        }

        $excel->setColumnWidths([
            'A' => 30, // Usuario
            'B' => 30, // Host
            'C' => 'auto',
            'D' => 'auto',
            'E' => 60  // Documento Visualizado
        ]);

        $excel->output('Visualizaciones_' . date('Y_m_d_His'));
    }

    public function reporte_paginas_legajosExcel()
    {
        $this->asegurarAccesoAuditoria('reporte_paginas_legajos');
        $filtros = $this->resolverFiltrosReportePaginasLegajos();
        $totales = $this->model->selectTotalesPaginasLegajos($filtros['desde'], $filtros['hasta']);
        $detalle = $this->model->selectDetallePaginasLegajos($filtros['desde'], $filtros['hasta']);

        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $excel = new ReportTemplateExcel('Paginas procesadas de legajos', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];

        $sheet->setCellValue('A2', 'Periodo');
        $sheet->setCellValue('B2', $filtros['desde'] . ' a ' . $filtros['hasta']);
        $sheet->setCellValue('A3', 'Legajos contabilizados');
        $sheet->setCellValue('B3', intval($totales['total_legajos'] ?? 0));
        $sheet->setCellValue('C3', 'Paginas procesadas');
        $sheet->setCellValue('D3', intval($totales['total_paginas'] ?? 0));

        $sheet->setCellValue('A5', 'ID');
        $sheet->setCellValue('B5', 'Fecha');
        $sheet->setCellValue('C5', 'Tipo de legajo');
        $sheet->setCellValue('D5', 'CI');
        $sheet->setCellValue('E5', 'Nombre completo');
        $sheet->setCellValue('F5', 'Nro solicitud');
        $sheet->setCellValue('G5', 'Paginas procesadas');
        $sheet->setCellValue('H5', 'Estado');
        $sheet->setCellValue('I5', 'Usuario');
        $sheet->getStyle('A5:I5')->applyFromArray($headerStyle);

        $row = 6;
        foreach ($detalle as $value) {
            $sheet->setCellValue('A' . $row, intval($value['id_legajo'] ?? 0));
            $sheet->setCellValue('B' . $row, (string)($value['fecha_creacion'] ?? ''));
            $sheet->setCellValue('C' . $row, (string)($value['nombre_tipo_legajo'] ?? ''));
            $sheet->setCellValue('D' . $row, (string)($value['ci_socio'] ?? ''));
            $sheet->setCellValue('E' . $row, (string)($value['nombre_completo'] ?? ''));
            $sheet->setCellValue('F' . $row, (string)($value['nro_solicitud'] ?? ''));
            $sheet->setCellValue('G' . $row, intval($value['cantidad_paginas_procesadas'] ?? 0));
            $sheet->setCellValue('H' . $row, (string)($value['estado'] ?? ''));
            $sheet->setCellValue('I' . $row, (string)($value['usuario_responsable'] ?? ''));
            $row++;
        }

        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 20,
            'C' => 28,
            'D' => 18,
            'E' => 36,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 24,
        ]);

        $excel->output('Paginas_Legajos_' . date('Y_m_d_His'));
    }
}
