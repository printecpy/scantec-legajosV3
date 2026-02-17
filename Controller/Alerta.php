<?php
require_once 'Controller/Configuracion.php'; 
class Alerta extends Controllers
{
    private $alertaModel;
    private $db;
    private $mailController; // Nuevo objeto para el controlador de correo/configuración

     public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
        $this->alertaModel = new AlertaModel();
        $this->db = new Mysql();
    }

    public function listar()
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1, 2]))  {
            setAlert('warning', "No tienes permiso para acceder a esta sección!");
            session_write_close();
            $motivo = 'Acceso no autorizado a sección de usuarios';
            $usuario = $_SESSION['nombre'];
            $data = $this->model->bloquarPC_IP($usuario, $motivo);
            header("Location: ".base_url()."expedientes/indice_busqueda");  // Redirigir a la página de índice de búsqueda
            exit();
        }
        $alerts =  $this->model->getTareasPendientes();
        $data = ['alerts' => $alerts];
        $this->views->getView($this, "listar", $data);
    }
    public function historial()
    {
        if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], [1, 2]))  {
            setAlert('warning', "No tienes permiso para acceder a esta sección!");
            session_write_close();
            $motivo = 'Acceso no autorizado a sección de usuarios';
            $usuario = $_SESSION['nombre'];
            $data = $this->model->bloquarPC_IP($usuario, $motivo);
            header("Location: ".base_url()."expedientes/indice_busqueda");  // Redirigir a la página de índice de búsqueda
            exit();
        }
        $alerts =  $this->model->getTareasPendientes();
        $data = ['alerts' => $alerts];
        $this->views->getView($this, "historial", $data);
    }
/**
     * Método principal que será llamado por el cron job (CLI).
     */
    
    public function ejecutarPendientes() {
        if (php_sapi_name() !== "cli") {
            echo "Acceso denegado. Este script solo puede ejecutarse desde la línea de comandos.\n";
            return;
        }

        echo "Iniciando procesamiento de alertas dinámicas...\n";
        
        $tareas = $this->alertaModel->getTareasPendientes();
        
        if (empty($tareas)) {
            echo "No hay tareas programadas para ejecutar ahora.\n";
            return;
        }

        echo "Se encontraron " . count($tareas) . " tareas pendientes.\n";

        foreach ($tareas as $tarea) {
            $id_tarea = $tarea['id'];
            $tipo_informe = $tarea['tipo_informe'];
            echo "--------------------------------------------------\n";
            echo "Procesando Tarea: '{$tarea['nombre_tarea']}' (ID: $id_tarea)\n";
            
            // 1. Generar el informe
            $reporte = $this->generarContenidoReporte($tipo_informe);

            if ($reporte === false) {
                echo "ERROR: No se pudo generar el reporte. Saltando tarea.\n";
                $this->alertaModel->logHistorial($id_tarea, 'N/A', 'Error', "Fallo al generar el reporte $tipo_informe");
                continue;
            }

            // 2. Obtener destinatarios
            $destinatarios = $this->alertaModel->getDestinatariosPorTarea($id_tarea);
            if (empty($destinatarios)) {
                echo "ADVERTENCIA: La tarea $id_tarea no tiene destinatarios activos. Actualizando fecha.\n";
                $this->alertaModel->actualizarTareaProgramada($id_tarea, $tarea['frecuencia']);
                continue; 
            }

            // 3. Enviar el informe a cada destinatario
            foreach ($destinatarios as $destinatario) {
                $correo = $destinatario['correo_destino'];
                echo "Enviando a: $correo ... ";
                
                $asunto = "Gestor Documental: {$tarea['nombre_tarea']}";
                $mensaje_html = $reporte['cuerpo_html'];
                $adjunto_path = $reporte['adjunto_path'];
                
                $nombreDestinatario = $correo; // Usar el correo como nombre por defecto
                
                $enviado = false;
                try {
                    // LLAMANDO AL MÉTODO CENTRALIZADO EN CONFIGURACION
                    $enviado = $this->mailController->sendEmailWithAttachment(
                        $adjunto_path, 
                        $correo, 
                        $nombreDestinatario, 
                        $asunto,
                        $mensaje_html
                    );
                } catch (\Throwable $e) {
                    // Captura cualquier excepción no manejada por el otro controlador
                    $detalleError = $e->getMessage();
                }

                // 4. Loguear el resultado INDIVIDUAL
                if ($enviado) {
                    echo "OK\n";
                    $this->alertaModel->logHistorial($id_tarea, $correo, 'Exitoso', "Correo enviado exitosamente.");
                } else {
                    $detalleError = $detalleError ?? "Error desconocido en el envío por sendEmailWithAttachment.";
                    echo "FALLO. Error: " . $detalleError . "\n";
                    $detalle = "Error al llamar al servicio de correo: " . $detalleError;
                    $this->alertaModel->logHistorial($id_tarea, $correo, 'Error', $detalle);
                }
            }

            // 5. Actualizar la 'tarea_programada' para su próxima ejecución
            $this->alertaModel->actualizarTareaProgramada($id_tarea, $tarea['frecuencia']);
            echo "Tarea (ID: $id_tarea) actualizada para próxima ejecución.\n";
        }
        
        echo "--------------------------------------------------\n";
        echo "Procesamiento finalizado.\n";
    }

/*     public function obtenerExpedientesPorTipo($tipo_informe)
{
    // 1️⃣ Conexión al modelo (ajusta si tu modelo se llama distinto)
    $db = $this->db; // o $this->conexion según tu estructura

    // 2️⃣ Obtener los días de vencimiento según el tipo de informe
    $query = $db->prepare("SELECT dias_vencimiento FROM tipo_informe WHERE nombre_tipo = ?");
    $query->execute([$tipo_informe]);
    $tipo = $query->fetch(PDO::FETCH_ASSOC);

    // Verifica si se encontró el tipo
    if (!$tipo) {
        return []; // Tipo de informe no encontrado
    }

    $dias = (int) $tipo['dias_vencimiento'];

    // 3️⃣ Consulta de expedientes que vencen dentro del rango
    $sql = "
        SELECT 
            id_expediente, 
            indice_01,
            fecha_vencimiento 
        FROM 
            expediente 
        WHERE 
            estado = 'Activo'
            AND fecha_vencimiento BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) 
                                      AND DATE_ADD(NOW(), INTERVAL :dias DAY)
        ORDER BY fecha_vencimiento ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $resultados;
} */

    /**
     * Genera el contenido HTML del correo y la ruta del adjunto para el informe.
     * @return array|false Retorna ['cuerpo_html' => '...', 'adjunto_path' => '...'] o false en caso de error.
     */
    private function generarContenidoReporte($tipo_informe) {
        // Definir la ruta temporal donde se guardará el adjunto (si aplica)
        $temp_dir = sys_get_temp_dir() . '/scantec_reportes/';
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777, true);
        }
        
        $cuerpo_html = "<p>Estimado usuario,</p>";
        $adjunto_path = null;

        switch ($tipo_informe) {
            case 'FACTURAS_VENCIDAS':
                // 1. Obtener los datos reales de facturas vencidas (llamada a un Modelo de Facturas)
                $datos = ['datos_de_facturas' => '...']; 
                
                // 2. Generar el PDF (usando librerías como mpdf, dompdf, o FPDF)
                // $pdf_content = $this->pdfLibrary->generate('vista_facturas_vencidas', $datos);

                // 3. Guardar el PDF en una ubicación temporal
                $filename = 'reporte_facturas_' . date('Ymd') . '.pdf';
                $adjunto_path = $temp_dir . $filename;
                // file_put_contents($adjunto_path, $pdf_content); // Descomentar al implementar el generador de PDF real
                
                // SIMULACIÓN
                file_put_contents($adjunto_path, "Contenido de prueba para $tipo_informe.");
                
                $cuerpo_html .= "<p>Adjunto encontrará el reporte de Facturas Vencidas al día de hoy.</p>";
                break;
                
            case 'TAREAS_PENDIENTES':
                $cuerpo_html .= "<p>El sistema informa que tiene Tareas Pendientes sin procesar. Revise su panel de control.</p>";
                break;
                
            default:
                return false; // Tipo de informe no reconocido
        }

        return [
            'cuerpo_html' => $cuerpo_html . "<p>Atentamente, El Equipo SCANTEC.</p>",
            'adjunto_path' => $adjunto_path,
        ];
    }

    /**
     * Genera el contenido del informe. (sin cambios)
     */
    private function generarContenidoReporte2($tipo_informe) {
        $html = "<h1>Informe: $tipo_informe</h1>";
        $ruta_adjunto = null;

        try {
            switch ($tipo_informe) {
                case 'INFORME_DIARIO_VENTAS':
                    $html .= "<p>Contenido del informe de ventas diarias...</p>";
                    break;
                case 'INFORME_DOCUMENTOS_PENDIENTES':
                    $html .= "<p>Listado de documentos pendientes de firma...</p>";
                    break;
                default:
                    $html .= "<p>Tipo de informe no reconocido.</p>";
                    break;
            }

            return [
                'cuerpo_html' => $html,
                'adjunto_path' => $ruta_adjunto
            ];

        } catch (Exception $e) {
            echo "Error al generar reporte $tipo_informe: " . $e->getMessage() . "\n";
            return false;
        }
    }

}