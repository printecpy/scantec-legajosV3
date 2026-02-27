<?php
require_once 'Libraries/pdf/fpdf.php';

class ReportTemplatePDF extends FPDF
{
    protected $datosEmpresa;
    protected $tituloReporte;

    // Variables para el motor de tablas multilínea
    protected $widths;
    protected $aligns;

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
        // Calculamos el ancho real de la hoja para hacer el diseño 100% responsivo
        $anchoPagina = $this->GetPageWidth();

        // 1. Logos
        // Logo Izquierdo
        if (defined('MEDIA_PATH') && file_exists(MEDIA_PATH . 'icoScantec2.png')) {
            $this->Image(MEDIA_PATH . 'icoScantec2.png', 10, 7, 33);
        } elseif (file_exists('Assets/img/icoScantec2.png')) {
            $this->Image('Assets/img/icoScantec2.png', 10, 7, 33);
        }

        // Logo Derecho (Calculamos posición exacta según el ancho del papel)
        $xRight = $anchoPagina - 30; // 20 de ancho del logo + 10 de margen derecho
        if (defined('MEDIA_PATH') && file_exists(MEDIA_PATH . 'logo_empresa.jpg')) {
            $this->Image(MEDIA_PATH . 'logo_empresa.jpg', $xRight, 5, 20, 20);
        } elseif (file_exists('Assets/img/logo_empresa.jpg')) {
            $this->Image('Assets/img/logo_empresa.jpg', $xRight, 5, 20, 20);
        }

        /* // 2. Datos de la Empresa (Texto)
        $nombre = !empty($this->datosEmpresa['nombre']) ? $this->datosEmpresa['nombre'] : '---';
        $tel    = !empty($this->datosEmpresa['telefono']) ? $this->datosEmpresa['telefono'] : '';
        $dir    = !empty($this->datosEmpresa['direccion']) ? $this->datosEmpresa['direccion'] : '';
        $email  = !empty($this->datosEmpresa['correo']) ? $this->datosEmpresa['correo'] : '';

        // --- IMPRESIÓN ---
        $this->SetFont('Arial', 'B', 14);
        $this->setX(50);
        $this->Cell(25, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
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
        $this->Cell(50, 5, utf8_decode($email), 0, 1, 'L'); */

        $this->Ln(15);

        // 3. Título del Reporte (Barra Gris Dinámica)
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(192, 192, 192);
        $this->SetTextColor(0, 0, 0);

        // Ancho total exacto para la barra gris (Ancho del papel - márgenes laterales)
        $anchoTotal = $anchoPagina - $this->lMargin - $this->rMargin;

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

    // ========================================================================
    // MOTOR DE TABLAS MULTILÍNEA (AJUSTE AUTOMÁTICO DE TEXTO)
    // ========================================================================
    
    public function SetWidths($w) {
        $this->widths = $w;
    }

    public function SetAligns($a) {
        $this->aligns = $a;
    }

    public function Row($data) {
        $nb = 0;
        // Calcula la altura máxima necesaria para toda la fila
        for($i=0; $i<count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 6 * $nb; // 6 es el alto base de cada renglón
        
        // Verifica si la fila entra en la página o si necesita saltar a otra hoja
        $this->CheckPageBreak($h);
        
        // Dibuja las celdas
        for($i=0; $i<count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Dibuja el borde
            $this->Rect($x, $y, $w, $h);
            
            // Imprime el texto con saltos de línea automáticos
            $this->MultiCell($w, 6, $data[$i], 0, $a);
            
            // Posiciona el cursor a la derecha para la siguiente celda
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    protected function CheckPageBreak($h) {
        // Si el Y actual + altura de fila supera el límite inferior, crea nueva página
        if($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->DefOrientation);
        }
    }

    protected function NbLines($w, $txt) {
        // Calcula cuántos renglones ocupará el texto
        $cw = &$this->CurrentFont['cw'];
        if($w==0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if($nb > 0 and $s[$nb-1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i < $nb) {
            $c = $s[$i];
            if($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if($c == ' ') $sep = $i;
            $l += $cw[$c];
            if($l > $wmax) {
                if($sep == -1) { if($i == $j) $i++; } else { $i = $sep + 1; }
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else { $i++; }
        }
        return $nl;
    }
}