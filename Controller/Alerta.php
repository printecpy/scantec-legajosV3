<?php
require_once 'Controller/Configuracion.php'; 

class Alerta extends Controllers
{
    private $mailController; 

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
            exit();
        }
        parent::__construct();
        $this->mailController = new Configuracion(); 
    }

    
    public function listar()
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1, 2]))  {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'No tienes permiso para acceder a esta sección.'];
            header("Location: ".base_url()."expedientes/indice_busqueda"); 
            exit();
        }
        
        $alerts = $this->model->getTareasActivas();
        $data = ['alerts' => $alerts];
        $this->views->getView($this, "listar", $data);
    }

    public function historial()
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1, 2]))  {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'No tienes permiso para acceder a esta sección.'];
            header("Location: ".base_url()."expedientes/indice_busqueda"); 
            exit();
        }
        
        $historial = $this->model->select_all("SELECT * FROM alerta_historial ORDER BY fecha_envio DESC"); 
        $data = ['historial' => $historial];
        $this->views->getView($this, "historial", $data);
    }

    public function editar()
    {
        if (empty($_GET['id'])) {
            header("Location: " . base_url() . "alerta/listar");
            exit();
        }
        $id_tarea = intval($_GET['id']);

        $respuesta_tarea = $this->model->getTareaById($id_tarea);
        
        // Aplanar el array si viene dentro de [0]
        if (!empty($respuesta_tarea) && isset($respuesta_tarea[0])) {
            $tarea = $respuesta_tarea[0];
        } else {
            $tarea = $respuesta_tarea;
        }

        if (empty($tarea)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'La alerta solicitada no existe.'];
            header("Location: " . base_url() . "alerta/listar");
            exit();
        }

        $destinatarios = $this->model->getDestinatariosPorTarea($id_tarea);

        $data = [
            'page_title' => 'Modificar Alerta',
            'tarea' => $tarea,
            'destinatarios' => $destinatarios
        ];

        $this->views->getView($this, "editar", $data);
    }

    public function modificar()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "alerta/listar");
            die();
        }

        $id_tarea = intval($_POST['id_tarea']);
        $nombre_tarea = htmlspecialchars(trim($_POST['nombre_tarea']));
        $tipo_informe = htmlspecialchars(trim($_POST['tipo_informe']));
        $frecuencia = htmlspecialchars(trim($_POST['frecuencia']));

        $request = $this->model->updateTarea($id_tarea, $nombre_tarea, $tipo_informe, $frecuencia);

        if ($request) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Alerta actualizada correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudieron guardar los cambios.'];
        }
        
        header("Location: " . base_url() . "alerta/editar?id=" . $id_tarea);
        die();
    }

    public function agregarDestinatario()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) { die(); }

        $id_tarea = intval($_POST['id_tarea']);
        $correo = filter_var(trim($_POST['correo_destino']), FILTER_SANITIZE_EMAIL);

        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->model->addDestinatario($id_tarea, $correo);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Destinatario agregado con éxito.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'El correo ingresado no es válido.'];
        }

        header("Location: " . base_url() . "alerta/editar?id=" . $id_tarea);
        die();
    }

    public function eliminarDestinatario()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) { die(); }

        $id_destinatario = intval($_POST['id_destinatario']);
        $id_tarea = intval($_POST['id_tarea']); 

        $this->model->deleteDestinatario($id_destinatario);
        
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Destinatario eliminado.'];
        header("Location: " . base_url() . "alerta/editar?id=" . $id_tarea);
        die();
    }

    public function eliminar()
    {
        if (!isset($_POST['id']) || !isset($_POST['token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad.'];
            header("Location: " . base_url() . "alerta/listar");
            die();
        }
        $id_tarea = intval($_POST['id']);
        $this->model->estadoTarea($id_tarea, 'inactivo');
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Alerta desactivada correctamente.'];
        header("Location: " . base_url() . "alerta/listar");
        die();
    }

    public function reingresar()
    {
        if (!isset($_POST['id']) || !isset($_POST['token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad.'];
            header("Location: " . base_url() . "alerta/listar");
            die();
        }
        $id_tarea = intval($_POST['id']);
        $this->model->estadoTarea($id_tarea, 'activo');
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Alerta reactivada correctamente.'];
        header("Location: " . base_url() . "alerta/listar");
        die();
    }

    public function insertar()
    {
        // 1. Validación de Seguridad CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "alerta?error=csrf");
            exit();
        }

        if ($_POST) {
            // 2. Limpieza de datos recibidos del formulario
            $nombre_tarea = htmlspecialchars($_POST['nombre_tarea']);
            $tipo_informe = htmlspecialchars($_POST['tipo_informe']);
            $frecuencia   = htmlspecialchars($_POST['frecuencia']);

            // 3. Traducción Lógica: Convertir el 'tipo_informe' en 'dias_alerta' numéricos
            $dias_alerta = null;
            switch ($tipo_informe) {
                case 'VENC_5_DIAS':  $dias_alerta = 5; break;
                case 'VENC_15_DIAS': $dias_alerta = 15; break;
                case 'VENC_1_MES':   $dias_alerta = 30; break;
                case 'VENC_3_MESES': $dias_alerta = 90; break;
            }

            // 4. Traducción Lógica: Calcular la próxima ejecución basada en la frecuencia
            // Por defecto, lo programamos para que arranque a las 08:00 AM del próximo ciclo
            $fecha_proxima = null;
            $hoy_a_las_8 = date('Y-m-d 08:00:00'); 
            
            switch ($frecuencia) {
                case 'DIARIA':  
                    $fecha_proxima = date('Y-m-d H:i:s', strtotime($hoy_a_las_8 . ' + 1 day')); 
                    break;
                case 'SEMANAL': 
                    $fecha_proxima = date('Y-m-d H:i:s', strtotime($hoy_a_las_8 . ' + 1 week')); 
                    break;
                case 'MENSUAL': 
                    $fecha_proxima = date('Y-m-d H:i:s', strtotime($hoy_a_las_8 . ' + 1 month')); 
                    break;
            }

            // 5. Enviar al Modelo
            $insert = $this->model->insertarTarea($nombre_tarea, $tipo_informe, $frecuencia, $dias_alerta, $fecha_proxima);

            // 6. Manejo de respuesta
            if ($insert > 0) {
                // Guardamos una alerta de éxito en sesión (asumiendo tu función setAlert)
                setAlert('success', "Tarea programada creada con éxito.");
                header("location: " . base_url() . "alerta/listar");
                die();
            } else {
                setAlert('error', "Error al crear la tarea programada.");
                header("location: " . base_url() . "alerta/listar");
                die();
            }
        }
    }

    public function ejecutarPendientes() {
        if (php_sapi_name() !== "cli") {
            echo "Acceso denegado. Solo línea de comandos.\n";
            return;
        }

        echo "Iniciando procesamiento...\n";
        $tareas = $this->model->getTareasPendientes();
        
        if (empty($tareas)) {
            echo "No hay tareas.\n";
            return;
        }

        foreach ($tareas as $tarea) {
            $id_tarea = $tarea['id'];
            echo "Procesando Tarea: '{$tarea['nombre_tarea']}'\n";
            
            $reporte = $this->generarContenidoReporte($tarea);

            if ($reporte === false) {
                echo "ERROR: Saltando tarea.\n";
                $this->model->logHistorial($id_tarea, 'N/A', 'Error', "Fallo al generar el reporte");
                continue;
            }

            $destinatarios = $this->model->getDestinatariosPorTarea($id_tarea);
            if (empty($destinatarios)) {
                $this->model->actualizarTareaProgramada($id_tarea, $tarea['frecuencia']);
                continue; 
            }

            foreach ($destinatarios as $destinatario) {
                $correo = $destinatario['correo_destino'];
                $asunto = "SCANTEC: {$tarea['nombre_tarea']}";
                
                $enviado = false;
                try {
                    $enviado = $this->mailController->sendEmailWithAttachment($reporte['adjunto_path'], $correo, $correo, $asunto, $reporte['cuerpo_html']);
                } catch (\Throwable $e) {
                    $detalleError = $e->getMessage();
                }

                if ($enviado) {
                    $this->model->logHistorial($id_tarea, $correo, 'Exitoso', "Correo enviado exitosamente.");
                } else {
                    $detalleError = $detalleError ?? "Error SMTP.";
                    $this->model->logHistorial($id_tarea, $correo, 'Error', $detalleError);
                }
            }
            $this->model->actualizarTareaProgramada($id_tarea, $tarea['frecuencia']);
        }
    }

    private function generarContenidoReporte(array $tarea) {
        $tipo_informe = $tarea['tipo_informe'];
        $temp_dir = sys_get_temp_dir() . '/scantec_reportes/';
        if (!is_dir($temp_dir)) { mkdir($temp_dir, 0777, true); }
        
        $cuerpo_html = "<p>Estimado usuario,</p><p>Este es un correo automático generado por el sistema de alertas de SCANTEC.</p>";
        $adjunto_path = null;

        switch ($tipo_informe) {
            case 'VENC_5_DIAS':
            case 'VENC_15_DIAS':
            case 'VENC_1_MES':
            case 'VENC_3_MESES':
                $datos_reporte = $this->model->getReporteData($tarea);

                if (empty($datos_reporte)) {
                    $cuerpo_html .= "<p>Para la alerta '{$tarea['nombre_tarea']}', no se encontraron documentos por vencer en el período configurado.</p>";
                    break; 
                }

                $filename = 'reporte_vencimientos_' . date('Ymd_His') . '.pdf';
                $adjunto_path = $temp_dir . $filename;

                // 1. Llamamos a tu archivo de Plantilla Centralizada
                require_once 'Helpers/ReportTemplatePDF.php';

                // 2. Inicializamos la plantilla con datos estáticos (Sin consultar BD)
                $titulo = 'Reporte de Documentos por Vencer';
                $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], $titulo, 'P', 'A4');

                // 3. Dibujamos las cabeceras de la tabla (Fondo gris claro)
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetFillColor(240, 240, 240); 
                $pdf->SetTextColor(0, 0, 0);
                
                $pdf->Cell(30, 8, 'ID Exp.', 1, 0, 'C', true);
                $pdf->Cell(110, 8, 'Indice Principal', 1, 0, 'C', true);
                $pdf->Cell(50, 8, 'Fecha Vencimiento', 1, 1, 'C', true);
                
                // 4. Configurar el motor multilínea para los datos del bucle
                $pdf->SetWidths(array(30, 110, 50));
                $pdf->SetAligns(array('C', 'L', 'C'));
                $pdf->SetFont('Arial', '', 10);
                
                // 5. Imprimir el contenido de la tabla ajustando el texto largo
                foreach ($datos_reporte as $item) {
                    $pdf->Row(array(
                        $item['id_expediente'],
                        utf8_decode($item['indice_01']),
                        date("d/m/Y", strtotime($item['fecha_vencimiento']))
                    ));
                }

                // 6. Generar y guardar físicamente el PDF ('F' para archivo temporal)
                $pdf->Output('F', $adjunto_path);

                $cuerpo_html .= "<p>Adjunto encontrará el reporte de documentos correspondientes a la alerta '{$tarea['nombre_tarea']}'.</p>";
                break;
                
            default:
                return false; 
        }

        $cuerpo_html .= "<br><p>Atentamente,<br>El Equipo de SCANTEC</p>";
        return ['cuerpo_html' => $cuerpo_html, 'adjunto_path' => $adjunto_path];
    }
}