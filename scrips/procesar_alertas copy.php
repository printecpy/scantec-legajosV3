<?php
// ========================================================
// CONFIGURACIÓN INICIAL
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
}

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
    registrarLog("Conexión exitosa a la base de datos.");
} catch (PDOException $e) {
    registrarLog("❌ Error al conectar a la base de datos: " . $e->getMessage());
    die();
}

// ========================================================
// OBTENER CONFIGURACIÓN SMTP
// ========================================================
$sqlSMTP = "SELECT host, username, password, smtpsecure, remitente, nombre_remitente, port, estado 
            FROM smtp_datos WHERE estado='activo' LIMIT 1;";
$stmtSMTP = $pdo->query($sqlSMTP);
$smtp = $stmtSMTP->fetch(PDO::FETCH_ASSOC);

if (!$smtp) {
    registrarLog("❌ No se encontró configuración SMTP activa.");
    die();
}

// ========================================================
// OBTENER TAREAS PENDIENTES
// ========================================================
$sqlTareas = "SELECT * FROM tarea_programada 
              WHERE estado = 'activo' AND fecha_proxima_ejecucion <= NOW()";
$stmt = $pdo->query($sqlTareas);
$tareasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($tareasPendientes)) {
    registrarLog("No hay tareas pendientes por ejecutar.");
    die();
}

// ========================================================
// IMPORTAR LIBRERÍAS
// ========================================================
require_once __DIR__ . '/../Libraries/pdf/fpdf.php';
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/Exception.php';
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/PHPMailer.php';
require_once __DIR__ . '/../Libraries/PHPMailer6.9.2/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ========================================================
// PROCESAR CADA TAREA
// ========================================================
foreach ($tareasPendientes as $tarea) {
    $id_tarea = $tarea['id'];
    $dias = (int)$tarea['dias_alerta'];
    registrarLog("🔹 Procesando tarea ID {$id_tarea}: {$tarea['nombre_tarea']}.");

    // --------------------------------------------------------
    // DESTINATARIOS ACTIVOS
    // --------------------------------------------------------
    $sqlDest = "SELECT correo_destino FROM alerta_destinatarios 
                WHERE id_tarea_programada = :id_tarea AND estado = 'activa';";
    $stmtDest = $pdo->prepare($sqlDest);
    $stmtDest->execute(['id_tarea' => $id_tarea]);
    $destinatarios = array_column($stmtDest->fetchAll(PDO::FETCH_ASSOC), 'correo_destino');

    if (empty($destinatarios)) {
        registrarLog("⚠ No hay destinatarios activos para la tarea {$id_tarea}.");
        continue;
    }

    // --------------------------------------------------------
    // ALERTAS DE EXPEDIENTES PRÓXIMOS A VENCER
    // --------------------------------------------------------
    $sqlAlertas = "SELECT * FROM v_expedientes
                   WHERE estado = 'Activo'
                     AND fecha_vencimiento BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) 
                                               AND DATE_ADD(NOW(), INTERVAL :dias DAY)
                   ORDER BY fecha_vencimiento ASC;";
    $stmtAlertas = $pdo->prepare($sqlAlertas);
    $stmtAlertas->execute(['dias' => $dias]);
    $alertas = $stmtAlertas->fetchAll(PDO::FETCH_ASSOC);

    if (empty($alertas)) {
        registrarLog("ℹ No hay alertas para la tarea {$id_tarea}.");
        continue;
    }

    // --------------------------------------------------------
    // DATOS DE CONFIGURACIÓN (empresa)
    // --------------------------------------------------------
    $sqldatos = "SELECT * FROM configuracion LIMIT 1;";
    $stmtDatos = $pdo->query($sqldatos);
    $datos = $stmtDatos->fetch(PDO::FETCH_ASSOC) ?: [];

    // ========================================================
    // GENERAR PDF TEMPORAL
    // ========================================================
    $tempDir = __DIR__ . '/../temp/';
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
    $fileName = 'Reporte_' . date('Ymd_His') . '.pdf';
    $filePath = $tempDir . $fileName;

    define('IMAGE_PATH', __DIR__ . '/../assets/img/');
    $pdf = new FPDF('L', 'mm', 'LEGAL');
    $pdf->AddPage();

    // Cabecera
    $pdf->SetTitle('Archivos próximos a vencer');
    $pdf->SetAuthor('SCANTEC');
    $pdf->SetCreator('SCANTEC');
    $pdf->SetMargins(10, 5, 5);
    $pdf->SetFont('Arial', 'B', 14);

    if (file_exists(IMAGE_PATH . 'icoScantec2.png')) {
        $pdf->Image(IMAGE_PATH . 'icoScantec2.png', 10, 7, 33);
    }
    if (file_exists(IMAGE_PATH . 'logo_empresa.jpg')) {
        $pdf->Image(IMAGE_PATH . 'logo_empresa.jpg', 320, 5, 20, 20);
    }

    $pdf->setX(60);
    $pdf->Cell(5, 5, utf8_decode("Cliente: "), 0, 0, 'L');
    $pdf->Cell(52, 5, utf8_decode($datos['nombre'] ?? 'No definido'), 0, 1, 'R');

    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 5, $datos['telefono'] ?? '', 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 5, utf8_decode($datos['direccion'] ?? ''), 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 5, utf8_decode($datos['correo'] ?? ''), 0, 1, 'L');

    $pdf->Ln(8);
    $pdf->setX(5);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(192, 192, 192);
    $pdf->Cell(345, 8, "Archivos próximos a vencer", 1, 1, 'C', 1);
    $pdf->Ln(2);

    // Encabezado de tabla
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->setX(5);
    $pdf->Cell(45, 6, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
    $pdf->Cell(65, 6, utf8_decode('Indice 1'), 1, 0, 'C', 1);
    $pdf->Cell(40, 6, 'Indice 2', 1, 0, 'C', 1);
    $pdf->Cell(40, 6, 'Indice 3', 1, 0, 'C', 1);
    $pdf->Cell(50, 6, utf8_decode('Indice 4'), 1, 0, 'C', 1);
    $pdf->Cell(35, 6, 'Indice 5', 1, 0, 'C', 1);
    $pdf->Cell(35, 6, 'Fecha carga', 1, 0, 'C', 1);
    $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 0, 'C', 1);
    $pdf->Cell(15, 6, utf8_decode('Versión'), 1, 1, 'C', 1);

    // Contenido
    $pdf->SetFont('Arial', '', 7);
    foreach ($alertas as $row) {
        $pdf->setX(5);
        $pdf->Cell(45, 6, utf8_decode($row['nombre_tipoDoc']), 1);
        $pdf->Cell(65, 6, utf8_decode($row['indice_01']), 1);
        $pdf->Cell(40, 6, utf8_decode($row['indice_02']), 1);
        $pdf->Cell(40, 6, utf8_decode($row['indice_03']), 1);
        $pdf->Cell(50, 6, utf8_decode($row['indice_04']), 1);
        $pdf->Cell(35, 6, utf8_decode($row['indice_05']), 1);
        $pdf->Cell(35, 6, utf8_decode($row['fecha_indexado']), 1);
        $pdf->Cell(15, 6, utf8_decode($row['paginas']), 1);
        $pdf->Cell(15, 6, utf8_decode($row['version']), 1, 1);
    }

    $pdf->SetXY(160, 273);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, utf8_decode('Página ') . $pdf->PageNo(), 0, 0, 'R');
    $pdf->Output($filePath, 'F');

    registrarLog("📄 PDF generado: {$tempDir}");

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
            $mail->Password = $smtp['password'];
            $mail->SMTPSecure = ($smtp['smtpsecure'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp['port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($smtp['remitente'], $smtp['nombre_remitente']);
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = 'Informe de archivos por vencer';
            $mail->AddEmbeddedImage(__DIR__ . '/../assets/img/logo_scantec.png', 'logo_scantec', 'logo_scantec.png');
            $mail->addAttachment($filePath);

            $mail->Body = '
            <html>
            <body style="font-family: Roboto, sans-serif; color: #333;">
                <p>Estimado usuario,</p>
                <p>Se adjunta el informe <strong>' . htmlspecialchars($tarea['nombre_tarea']) . '</strong> de los archivos por vencer en los próximos <strong>' . htmlspecialchars($dias) . '</strong> días.</p>
                <p>Por favor, revise los documentos próximos a vencer.</p>
                <br>
                <hr style="border: none; border-top: 1px solid #ccc;">
                <div style="text-align: center; margin-top: 15px;">
                    <p style="font-size: 12px; color: #777; margin-bottom: 5px;">
                        Generado automáticamente por <strong>SCANTEC</strong>
                    </p>
                    <img src="cid:logo_scantec" alt="Logo SCANTEC"
                         style="width: 180px; height: auto; margin-top: 5px; display: block; margin: 0 auto;">
                </div>
            </body>
            </html>';

            $mail->AltBody = "Se adjunta el informe de archivos por vencer en los próximos {$dias} días.\nGenerado automáticamente por SCANTEC.";
            $mail->send();

            registrarLog("✅ Correo enviado correctamente a: {$correo}");

            $sqlHist = "INSERT INTO alerta_historial (correo_destino, fecha_envio, estado, detalle)
                        VALUES (:correo, NOW(), 'enviado', 'Correo enviado correctamente')";
            $pdo->prepare($sqlHist)->execute(['correo' => $correo]);
        } catch (Exception $e) {
            registrarLog("❌ Error al enviar correo a {$correo}: {$mail->ErrorInfo}");
            $sqlHist = "INSERT INTO alerta_historial (correo_destino, fecha_envio, estado, detalle)
                        VALUES (:correo, NOW(), 'error', :detalle)";
            $pdo->prepare($sqlHist)->execute([
                'correo' => $correo,
                'detalle' => $mail->ErrorInfo
            ]);
        }
    }

    // Eliminar PDF temporal
    if (file_exists($filePath)) unlink($filePath);

    // ========================================================
    // ACTUALIZAR FECHAS DE EJECUCIÓN
    // ========================================================
    $fecha_ultima = date('Y-m-d H:i:s');
    $frecuencia = [
        'diaria' => '+1 day',
        'semanal' => '+1 week',
        'mensual' => '+1 month'
    ];
    $fecha_proxima = date('Y-m-d H:i:s', strtotime($frecuencia[$tarea['frecuencia']] ?? '+1 day'));

    $sqlUpdate = "UPDATE tarea_programada 
                  SET fecha_ultima_ejecucion=:ultima, fecha_proxima_ejecucion=:proxima WHERE id=:id";
    $pdo->prepare($sqlUpdate)->execute([
        'ultima' => $fecha_ultima,
        'proxima' => $fecha_proxima,
        'id' => $id_tarea
    ]);
}

registrarLog("✅ Proceso finalizado correctamente.");
