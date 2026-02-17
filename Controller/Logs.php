<?php
class Logs extends Controllers
{
    public function __construct()
    {
        session_start();
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
        $this->checkAdminAccess();
        $data = $this->model->selectLogs();
        $this->views->getView($this, "listar", $data);
    }

    public function registro_views()
    {
        $this->checkAdminAccess();
        $registro_views = $this->model->selectViews();
        $data = ['registro_views' => $registro_views];
        $this->views->getView($this, "registro_views", $data);
    }

    public function registro_sesiones()
    {
        $this->checkAdminAccess();
        $registro_sesiones = $this->model->selectSesions();
        $data = ['registro_sesiones' => $registro_sesiones];
        $this->views->getView($this, "registro_sesiones", $data);
    }

    public function registro_session_fail()
    {
        $this->checkAdminAccess();
        $registro_session_fail = $this->model->selectSesionFails();
        $data = ['registro_session_fail' => $registro_session_fail];
        $this->views->getView($this, "registro_session_fail", $data);
    }
    public function pdf()
    {
        $datos = $this->model->selectDatos();
        $logsumango = $this->model->selectLogs();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'LEGAL');
        $pdf->AddPage();
        $pdf->SetTitle('LOGS');
        $pdf->SetAuthor('SCANTEC ' . $_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() . 'Assets/img/icoScantec2.png', 10, 7, 33);
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 320, 5, 20, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setX(20);
        $pdf->Ln();
        $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(340, 8, "Logs", 1, 1, 'C', 1);
        $pdf->Ln();
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(10);
        $pdf->Cell(35, 6, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(152, 6, utf8_decode('Execute SQL'), 1, 0, 'C', 1);
        $pdf->Cell(152, 6, utf8_decode('Reverse SQL'), 1, 1, 'C', 1);
        //   $pdf->Cell(35, 6, 'Host', 1, 1, 'C',1);
        // $pdf->Cell(30, 6, utf8_decode('Páginas'), 1, 1, 'C',1);
        // $pdf->Cell(15, 5, 'Cant. exped', 1, 1, 'L');

        $pdf->SetFont('Arial', '', 6);
        //     $contador = 1;
        foreach ($logsumango as $row) {
            $pdf->setX(10);
            $pdf->Cell(35, 8, ($row['fecha']), 1, 0, 'C');
            $pdf->Cell(152, 8, utf8_decode($row['executedSQL']), 1, 0, 'J');
            $pdf->Cell(152, 8, utf8_decode($row['reverseSQL']), 1, 1, 'J');
            //  $pdf->Cell(35, 5, utf8_decode($row['nombre_host']), 1, 1, 'C');
            //$pdf->Cell(30, 5, utf8_decode($row['total_pag']), 1, 1, 'C');

            // $pdf->Cell(15, 5, $row['cant_expediente'], 1, 1, 'L');

            //  $contador++;

        }
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output("Logs.pdf", "I");
    }

    public function registro_sesionesPdf()
    {
        $datos = $this->model->selectDatos();
        $sesiones = $this->model->selectSesions();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetTitle('Logs session');
        $pdf->SetAuthor('SCANTEC ' . $_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() . 'Assets/img/icoScantec2.png', 10, 7, 33);
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 275, 5, 20, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setX(20);
        $pdf->Ln();
        $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(96, 96, 96);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(280, 8, "Sesiones", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell(280, 8, utf8_decode("Reporte de los últimos 7 días"), 1, 1, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(10);
        $pdf->Cell(40, 6, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(50, 6, utf8_decode('Direccon IP'), 1, 0, 'C', 1);
        $pdf->Cell(50, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', 1);
        $pdf->Cell(50, 6, utf8_decode('Nombre usuario'), 1, 0, 'C', 1);
        $pdf->Cell(50, 6, utf8_decode('Usuario'), 1, 0, 'C', 1);
        $pdf->Cell(40, 6, utf8_decode('Fecha Cierre'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 7);

        foreach ($sesiones as $row) {
            $pdf->setX(10);
            $pdf->Cell(40, 5, utf8_decode($row['fecha']), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['ip']), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['servidor']), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['nombre']), 1, 0, 'C');
            $pdf->Cell(50, 5, utf8_decode($row['usuario']), 1, 0, 'C');
            $pdf->Cell(40, 5, utf8_decode($row['fecha_cierre']), 1, 1, 'C');
        }
        $pdf->SetXY(160, 185);
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output("Sesiones.pdf", "I");
    }

    public function registro_session_failPdf()
    {
        $datos = $this->model->selectDatos();
        $sesionFails = $this->model->selectSesionFails();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetTitle('Logs session fail');
        $pdf->SetAuthor('SCANTEC ' . $_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() . 'Assets/img/icoScantec2.png', 10, 7, 33);
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 275, 5, 20, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setX(20);
        $pdf->Ln();
        $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(96, 96, 96);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(280, 8, "Logs", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell(280, 8, utf8_decode("Reporte de los últimos 7 días"), 1, 1, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(10);
        $pdf->Cell(45, 6, utf8_decode('Usuario'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Direccion IP'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(100, 6, utf8_decode('Motivo'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 7);

        foreach ($sesionFails as $row) {
            $pdf->setX(10);
            $pdf->Cell(45, 5, utf8_decode($row['usuario']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['direccion_ip']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['timestamp']), 1, 0, 'C');
            $pdf->Cell(100, 5, utf8_decode($row['motivo']), 1, 1, 'L');
        }
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output("Sessions_Fail.pdf", "I");
    }

    public function registro_viewsPdf()
    {
        $datos = $this->model->selectDatos();
        $views = $this->model->selectViews();
        require_once 'Libraries/pdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->SetTitle('Logs views documents');
        $pdf->SetAuthor('SCANTEC ' . $_SESSION['usuario']);
        $pdf->SetCreator('SCANTEC');
        $pdf->SetMargins(10, 5, 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->setX(60);
        $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
        $pdf->image(base_url() . 'Assets/img/icoScantec2.png', 10, 7, 33);
        $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 275, 5, 20, 20);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->setX(20);
        $pdf->Ln();
        $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(96, 96, 96);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(280, 8, "Logs", 1, 1, 'C', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell(280, 8, utf8_decode("Reporte de los últimos 7 días"), 1, 1, 'L', 1);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(192, 192, 192);
        $pdf->setX(10);
        $pdf->Cell(45, 6, utf8_decode('Usuario'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Nombre HOST'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Direccion IP'), 1, 0, 'C', 1);
        $pdf->Cell(45, 6, utf8_decode('Fecha'), 1, 0, 'C', 1);
        $pdf->Cell(100, 6, utf8_decode('Nombre del archivo'), 1, 1, 'C', 1);;

        $pdf->SetFont('Arial', '', 7);

        foreach ($views as $row) {
            $pdf->setX(10);
            $pdf->Cell(45, 5, utf8_decode($row['usuario']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['nombre_pc']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['direccion_ip']), 1, 0, 'C');
            $pdf->Cell(45, 5, utf8_decode($row['fecha']), 1, 0, 'C');
            $pdf->Cell(100, 5, utf8_decode($row['nombre_expediente']), 1, 1, 'L');
        }
        $pdf->SetXY(160, 185);
        // Arial italic 8
        $pdf->SetFont('Arial', 'B', 8);
        // Número de página
        $pdf->Cell(0, 0, utf8_decode('Página ') . $pdf->PageNo() . '', 0, 0, 'R');
        $pdf->Output("Views_expedientes.pdf", "I");
    }

    public function excel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $logs = $this->model->selectLogs();
        $nombre = 'logs';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs scantec');
        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Execute SQL');
        $sheet->setCellValue('C1', 'Reverse SQL');
        // Obtener el estilo de la celda A1 y aplicar color de fondo
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];
        // Establecer estilo de fuente para la cabecera
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];
        // Establecer estilo de fuente para el resto del contenido
        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($logs as $value) {
            $sheet->setCellValue('A' . $row, $value["fecha"]);
            $sheet->setCellValue('B' . $row, $value['executedSQL']);
            $sheet->setCellValue('C' . $row, $value["reverseSQL"]);
            $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray($contentStyle);
            $row++;
        }
        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }

    public function registro_sesionesExcel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $sesiones = $this->model->selectSesions();
        $nombre = 'sesiones';

        // Crear una nueva hoja de cálculo
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs sessions');

        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');

        // Establecer encabezados
        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Direccion IP');
        $sheet->setCellValue('C1', 'Nombre HOST');
        $sheet->setCellValue('D1', 'Nombre');
        $sheet->setCellValue('E1', 'Usuario');
        $sheet->setCellValue('F1', 'Fecha cierre');

        // Estilos para encabezados
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];

        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:F1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Rellenar datos
        $row = 2;
        foreach ($sesiones as $value) {
            $sheet->setCellValue('A' . $row, $value["fecha"]);
            $sheet->setCellValue('B' . $row, $value['ip']);
            $sheet->setCellValue('C' . $row, $value["servidor"]);
            $sheet->setCellValue('D' . $row, $value["nombre"]);
            $sheet->setCellValue('E' . $row, $value["usuario"]);
            $sheet->setCellValue('F' . $row, $value["fecha_cierre"]);
            $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray($contentStyle);
            $row++;
        }

        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }

    public function registro_viewExcel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $views = $this->model->selectViews();
        $nombre = 'views';
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Crear una nueva hoja de cálculo
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs views');

        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');

        // Establecer encabezados
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Nombre HOST');
        $sheet->setCellValue('C1', 'Direccion IP');
        $sheet->setCellValue('D1', 'Fecha');
        $sheet->setCellValue('E1', 'Archivo');

        // Estilos para encabezados
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];

        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:E1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Rellenar datos
        $row = 2;
        foreach ($views as $value) {
            $sheet->setCellValue('A' . $row, $value["usuario"]);
            $sheet->setCellValue('B' . $row, $value['nombre_pc']);
            $sheet->setCellValue('C' . $row, $value["direccion_ip"]);
            $sheet->setCellValue('D' . $row, $value["fecha"]);
            $sheet->setCellValue('E' . $row, $value["nombre_expediente"]);
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($contentStyle);
            $row++;
        }

        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }

    public function registro_session_failExcel()
    {
        require_once 'Libraries/vendor/autoload.php';
        date_default_timezone_set('America/Asuncion');
        $sesionfails = $this->model->selectSesionFails();
        $nombre = 'sesionfails';

        // Crear una nueva hoja de cálculo
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Establecer el nombre de la hoja
        $sheet->setTitle($nombre);
        // Agregar metadatos
        $spreadsheet->getProperties()
            ->setCreator('SCANTEC')
            ->setLastModifiedBy('Scantec - ' . $_SESSION['usuario'])
            ->setTitle('Logs sesionfails');

        // Establecer estilo de fuente predeterminado
        $spreadsheet->getDefaultStyle()->getFont()->setName('Helvetica Neue');

        // Establecer encabezados
        $sheet->setCellValue('A1', 'Usuario');
        $sheet->setCellValue('B1', 'Nombre HOST');
        $sheet->setCellValue('C1', 'Direccion IP');
        $sheet->setCellValue('D1', 'Fecha');
        $sheet->setCellValue('E1', 'Motivo');

        // Estilos para encabezados
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '878787',
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12, // Tamaño de fuente 12 para la cabecera
            ],
        ];

        $contentStyle = [
            'font' => [
                'size' => 8, // Tamaño de fuente 8 para el resto del contenido
            ],
        ];

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:E1')->applyFromArray($styleArray);
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // Rellenar datos
        $row = 2;
        foreach ($sesionfails as $value) {
            $sheet->setCellValue('A' . $row, $value["usuario"]);
            $sheet->setCellValue('B' . $row, $value['nombre_pc']);
            $sheet->setCellValue('C' . $row, $value["direccion_ip"]);
            $sheet->setCellValue('D' . $row, $value["timestamp"]);
            $sheet->setCellValue('E' . $row, $value["motivo"]);
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($contentStyle);
            $row++;
        }

        // Ajustar el ancho de las columnas automáticamente
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Crear el archivo Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre . '_' . date('Y_m_d_H:_i:_s') . '.xlsx"');
        header('Cache-Control: max-age=0');

        // Guardar el archivo y enviarlo al navegador
        $writer->save('php://output');
        exit(); // Asegurarse de que no se envíe más contenido
    }
}
