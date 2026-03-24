<?php
// ========================================================
// CONFIGURACIÓN INICIAL Y LOGS
// ========================================================
date_default_timezone_set('America/Asuncion');
$logDir = __DIR__ . '/../logs/';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$logFile = $logDir . 'alertas_' . date('Ymd') . '.log';

function registrarLog($mensaje)
{
    global $logFile;
    $hora = date('[Y-m-d H:i:s] ');
    file_put_contents($logFile, $hora . $mensaje . PHP_EOL, FILE_APPEND);
    // echo $hora . $mensaje . "\n"; // Descomenta para ver en consola al probar
}

registrarLog("🚀 Iniciando ejecución de tareas programadas...");

// ========================================================
// CONEXIÓN A BASE DE DATOS
// ========================================================
$db_host = "localhost";
$db_name = "scantec_2";
$db_user = "root";
$db_pass = "scantec";

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    registrarLog("❌ Error al conectar a la base de datos: " . $e->getMessage());
    die();
}

// ========================================================
// IMPORTAR HELPERS Y LIBRERÍAS
// ========================================================
// 1. Helpers (Para desencriptar contraseñas si aplica)
$helperPath = __DIR__ . '/../Helpers/Helpers.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

// 2. FPDF y Tu Plantilla Centralizada
require_once __DIR__ . '/../Libraries/pdf/fpdf.php';
require_once __DIR__ . '/../Libraries/pdf/ReportTemplatePDF.php';

// 3. PHPMailer
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/Exception.php';
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ========================================================
// OBTENER CONFIGURACIÓN SMTP
// ========================================================
$sqlSMTP = "SELECT host, username, password, smtpsecure, remitente, nombre_remitente, port, estado 
            FROM smtp_datos WHERE estado='activo' LIMIT 1;";
$stmtSMTP = $pdo->query($sqlSMTP);
$smtp = $stmtSMTP->fetch(PDO::FETCH_ASSOC);

if (!$smtp) {
    registrarLog("❌ No se encontró configuración SMTP activa. Abortando.");
    die();
}

// Preparar contraseña SMTP
$smtp_password = $smtp['password'];
if (function_exists('stringDecryption')) {
    $smtp_password = stringDecryption($smtp['password']);
}

// ========================================================
// OBTENER TAREAS PENDIENTES
// ========================================================
$sqlTareas = "SELECT * FROM tarea_programada 
              WHERE estado = 'activo' AND fecha_proxima_ejecucion <= NOW()";
$stmt = $pdo->query($sqlTareas);
$tareasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tareasPendientes)) {
    registrarLog("✅ No hay tareas pendientes por ejecutar en este momento.");
    die();
}

// ========================================================
// PROCESAR CADA TAREA PENDIENTE
// ========================================================
foreach ($tareasPendientes as $tarea) {
    $id_tarea = $tarea['id'];
    $dias = isset($tarea['dias_alerta']) ? (int)$tarea['dias_alerta'] : 5; 
    
    registrarLog("🔹 Procesando tarea ID {$id_tarea}: {$tarea['nombre_tarea']}");

    // 1. OBTENER DESTINATARIOS ACTIVOS
    $sqlDest = "SELECT correo_destino FROM alerta_destinatarios 
                WHERE id_tarea_programada = :id_tarea AND estado = 'activa';";
    $stmtDest = $pdo->prepare($sqlDest);
    $stmtDest->execute(['id_tarea' => $id_tarea]);
    $destinatarios = array_column($stmtDest->fetchAll(PDO::FETCH_ASSOC), 'correo_destino');

    // 2. OBTENER DOCUMENTOS POR VENCER
    $sqlAlertas = "SELECT * FROM v_expedientes
                   WHERE estado = 'Activo'
                     AND fecha_vencimiento BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) 
                                               AND DATE_ADD(NOW(), INTERVAL :dias DAY)
                   ORDER BY fecha_vencimiento ASC;";
    $stmtAlertas = $pdo->prepare($sqlAlertas);
    $stmtAlertas->execute(['dias' => $dias]);
    $alertas = $stmtAlertas->fetchAll(PDO::FETCH_ASSOC);

    // 3. OBTENER CONFIGURACIÓN DE EMPRESA (Para la cabecera del PDF)
    $sqldatos = "SELECT * FROM configuracion LIMIT 1;";
    $stmtDatos = $pdo->query($sqldatos);
    $datos = $stmtDatos->fetch(PDO::FETCH_ASSOC) ?: [];

    // Validar si vale la pena generar PDF y enviar correos
    if (empty($destinatarios)) {
        registrarLog("⚠ No hay destinatarios activos. Se saltará el envío, pero se actualizará la fecha.");
    } elseif (empty($alertas)) {
        registrarLog("ℹ No hay documentos próximos a vencer para esta alerta. Se saltará el envío.");
    } else {
        
        // ========================================================
        // GENERAR PDF CON LA PLANTILLA CENTRALIZADA
        // ========================================================
        $tempDir = __DIR__ . '/../temp/';
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
        $fileName = 'Reporte_Vencimientos_' . date('Ymd_His') . '.pdf';
        $filePath = $tempDir . $fileName;

        // Adaptar datos al formato que espera la plantilla
        $datosPlantilla = [
            'nombre'    => $datos['nombre'] ?? 'SCANTEC S.A.',
            'telefono'  => $datos['telefono'] ?? '',
            'direccion' => $datos['direccion'] ?? '',
            'correo'    => $datos['correo'] ?? ''
        ];
        
        $tituloReporte = "Archivos próximos a vencer (" . $dias . " días)";

        // Instanciar plantilla (L = Horizontal, LEGAL)
        $pdf = new ReportTemplatePDF($datosPlantilla, $tituloReporte, 'L', 'LEGAL');
        
        // Definir Cabeceras de la Tabla
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->setX(5);
        $pdf->Cell(45, 6, utf8_decode('Tipo documento'), 1, 0, 'C', true);
        $pdf->Cell(65, 6, utf8_decode('Indice 1'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, 'Indice 2', 1, 0, 'C', true);
        $pdf->Cell(40, 6, 'Indice 3', 1, 0, 'C', true);
        $pdf->Cell(50, 6, utf8_decode('Indice 4'), 1, 0, 'C', true);
        $pdf->Cell(35, 6, 'Indice 5', 1, 0, 'C', true);
        $pdf->Cell(35, 6, 'Fecha carga', 1, 0, 'C', true);
        $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 0, 'C', true);
        $pdf->Cell(15, 6, utf8_decode('Versión'), 1, 1, 'C', true);

        // Imprimir Contenido de la Tabla
        $pdf->SetFont('Arial', '', 7);
        foreach ($alertas as $row) {
            $pdf->setX(5);
            $pdf->Cell(45, 6, utf8_decode($row['nombre_tipoDoc'] ?? ''), 1);
            $pdf->Cell(65, 6, utf8_decode($row['indice_01'] ?? ''), 1);
            $pdf->Cell(40, 6, utf8_decode($row['indice_02'] ?? ''), 1);
            $pdf->Cell(40, 6, utf8_decode($row['indice_03'] ?? ''), 1);
            $pdf->Cell(50, 6, utf8_decode($row['indice_04'] ?? ''), 1);
            $pdf->Cell(35, 6, utf8_decode($row['indice_05'] ?? ''), 1);
            $pdf->Cell(35, 6, utf8_decode($row['fecha_indexado'] ?? ''), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($row['paginas'] ?? ''), 1, 0, 'C');
            $pdf->Cell(15, 6, utf8_decode($row['version'] ?? ''), 1, 1, 'C');
        }

        // Guardar archivo físico
        $pdf->Output('F', $filePath);
        registrarLog("📄 PDF centralizado generado: {$filePath}");

        // ========================================================
        // ENVÍO DE CORREOS
        // ========================================================
        foreach ($destinatarios as $correo) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['username'];
                $mail->Password = $smtp_password; 
                $mail->SMTPSecure = (strtolower($smtp['smtpsecure']) === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtp['port'];
                $mail->CharSet = 'UTF-8';
                $mail->Timeout = 15;

                $mail->setFrom($smtp['username'], $smtp['nombre_remitente']);
                $mail->addAddress($correo);

                $mail->isHTML(true);
                $mail->Subject = 'SCANTEC: Informe de archivos por vencer';
                
                $logoPath = __DIR__ . '/../assets/img/logo_scantec.png';
                if(file_exists($logoPath)) {
                    $mail->AddEmbeddedImage($logoPath, 'logo_scantec', 'logo_scantec.png');
                    $imgTag = '<img src="cid:logo_scantec" alt="Logo SCANTEC" style="width: 180px; height: auto; display: block; margin: 0 auto;">';
                } else {
                    $imgTag = '<strong>SCANTEC</strong>';
                }
                
                $mail->addAttachment($filePath);

                $mail->Body = '
                <html>
                <body style="font-family: Roboto, Arial, sans-serif; color: #333; line-height: 1.6;">
                    <p>Estimado usuario,</p>
                    <p>Se adjunta el informe <strong>' . htmlspecialchars($tarea['nombre_tarea']) . '</strong> detallando los archivos por vencer en los próximos <strong>' . htmlspecialchars($dias) . '</strong> días.</p>
                    <p>Por favor, revise los documentos correspondientes.</p>
                    <br>
                    <hr style="border: none; border-top: 1px solid #eaeaea;">
                    <div style="text-align: center; margin-top: 20px;">
                        <p style="font-size: 11px; color: #888; margin-bottom: 10px;">Generado automáticamente por el Sistema de Alertas</p>
                        ' . $imgTag . '
                    </div>
                </body>
                </html>';

                $mail->AltBody = "Se adjunta el informe de archivos por vencer en {$dias} días.\nGenerado automáticamente por SCANTEC.";
                $mail->send();

                registrarLog("Correo enviado a: {$correo}");

                // Guardar en el historial
                $sqlHist = "INSERT INTO alerta_historial (documento_id, correo_destino, fecha_envio, estado, detalle)
                            VALUES (:id_tarea, :correo, NOW(), 'Exitoso', 'Correo y reporte enviados correctamente')";
                $pdo->prepare($sqlHist)->execute(['id_tarea' => $id_tarea, 'correo' => $correo]);
                
            } catch (Exception $e) {
                registrarLog("Error SMTP para {$correo}: {$mail->ErrorInfo}");
                $sqlHist = "INSERT INTO alerta_historial (documento_id, correo_destino, fecha_envio, estado, detalle)
                            VALUES (:id_tarea, :correo, NOW(), 'Error', :detalle)";
                $pdo->prepare($sqlHist)->execute([
                    'id_tarea' => $id_tarea,
                    'correo' => $correo,
                    'detalle' => "Error de red/SMTP: " . $mail->ErrorInfo
                ]);
            }
        } // Fin foreach destinatarios

        // Eliminar PDF temporal para no saturar el servidor
        if (file_exists($filePath)) unlink($filePath);
    }

    // ========================================================
    // ACTUALIZAR FECHAS DE EJECUCIÓN (Se ejecuta siempre)
    // ========================================================
    $fecha_ultima = date('Y-m-d H:i:s');
    $frecuenciaList = [
        'diaria'  => '+1 day',
        'semanal' => '+1 week',
        'mensual' => '+1 month'
    ];
    
    $frecIndex = strtolower($tarea['frecuencia']);
    $fecha_proxima = date('Y-m-d H:i:s', strtotime($frecuenciaList[$frecIndex] ?? '+1 day'));

    $sqlUpdate = "UPDATE tarea_programada 
                  SET fecha_ultima_ejecucion=:ultima, fecha_proxima_ejecucion=:proxima WHERE id=:id";
    $pdo->prepare($sqlUpdate)->execute([
        'ultima' => $fecha_ultima,
        'proxima' => $fecha_proxima,
        'id' => $id_tarea
    ]);
    
    registrarLog("Tarea ID {$id_tarea} recalendarizada. Próxima ejecución: {$fecha_proxima}");
}

registrarLog("Proceso de Script finalizado en su totalidad.");
?>