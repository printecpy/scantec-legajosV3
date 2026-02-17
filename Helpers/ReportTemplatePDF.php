<?php
require_once 'Libraries/pdf/fpdf.php';

class ReportTemplatePDF extends FPDF
{
    protected $datosEmpresa;
    protected $tituloReporte;

    // Constructor personalizado para recibir datos iniciales
    public function __construct($datosEmpresa, $tituloReporte, $orientation = 'L', $size = 'A4')
    {
        parent::__construct($orientation, 'mm', $size);
        $this->datosEmpresa = $datosEmpresa;
        $this->tituloReporte = $tituloReporte;
        $this->SetMargins(10, 10, 10);
        $this->SetAutoPageBreak(true, 15);
        $this->AliasNbPages(); // Para el número total de páginas {nb}
        $this->AddPage();
    }

    // Encabezado Automático en cada página
    function Header()
    {
        // 1. Logos
        // Logo Izquierdo
        if (defined('MEDIA_PATH') && file_exists(MEDIA_PATH . 'icoScantec2.png')) {
            $this->Image(MEDIA_PATH . 'icoScantec2.png', 10, 7, 33);
        } elseif (file_exists('Assets/img/icoScantec2.png')) {
            $this->Image('Assets/img/icoScantec2.png', 10, 7, 33);
        }

        // Logo Derecho (Calculamos posición según orientación)
        $xRight = ($this->DefOrientation == 'L') ? 270 : 185;
        if (defined('MEDIA_PATH') && file_exists(MEDIA_PATH . 'logo_empresa.jpg')) {
            $this->Image(MEDIA_PATH . 'logo_empresa.jpg', $xRight, 5, 20, 20);
        }
        // 2. Datos de la Empresa (Texto)
        // --- Nombre del Proyecto ---
        $this->SetFont('Arial', 'B', 14);
        $this->setX(50);
        $this->Cell(25, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        $nombre = !empty($this->datosEmpresa['nombre']) ? $this->datosEmpresa['nombre'] : '---';
        $tel    = !empty($this->datosEmpresa['telefono']) ? $this->datosEmpresa['telefono'] : '';
        $dir    = !empty($this->datosEmpresa['direccion']) ? $this->datosEmpresa['direccion'] : '';
        $email  = !empty($this->datosEmpresa['correo']) ? $this->datosEmpresa['correo'] : '';

        // --- IMPRESIÓN ---
        $this->SetFont('Arial', 'B', 14);
        $this->setX(50);
        $this->Cell(25, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
        // Aquí saldrá "PRINTEC SA" (tu dato real) o "SIN DATOS DE EMPRESA" (si está vacío)
        $this->Cell(100, 5, utf8_decode($nombre), 0, 1, 'L');

        // Detalles
        $this->SetFont('Arial', 'B', 10);
        $this->setX(50);
        $this->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 5, utf8_decode($tel), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->setX(50);
        $this->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(100, 5, utf8_decode($dir), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->setX(50);
        $this->Cell(20, 5, "Correo: ", 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 5, utf8_decode($email), 0, 1, 'L');

        $this->Ln(10);

        // 3. Título del Reporte (Barra Gris)
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(192, 192, 192);
        $this->SetTextColor(0, 0, 0);

        // Ancho dinámico según orientación
        $anchoTotal = ($this->DefOrientation == 'L') ? 277 : 190;

        $this->Cell($anchoTotal, 8, utf8_decode($this->tituloReporte), 1, 1, 'C', true);
        $this->Ln(5);
    }

    // Pie de página Automático
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(0, 0, 0);

        // Número de página
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');

        // Copyright o Usuario a la izquierda
        $this->SetX(10);
        $usuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Sistema';
        $this->Cell(0, 10, utf8_decode('Generado por: ' . $usuario . ' - ' . date('d/m/Y H:i')), 0, 0, 'L');
    }
}