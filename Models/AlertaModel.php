<?php
class AlertaModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Obtiene las tareas que deben ejecutarse AHORA.
     * Compara la fecha_proxima_ejecucion con la hora actual.
     * @return array Lista de tareas pendientes.
     */
    public function getTareasPendientes() {
        // Selecciona tareas activas cuya hora de ejecución ya pasó
        $query = "SELECT id, nombre_tarea, tipo_informe, frecuencia, 
	                fecha_proxima_ejecucion, fecha_ultima_ejecucion, estado
                  FROM tarea_programada 
                  WHERE estado = 'activo' 
                  AND fecha_proxima_ejecucion <= NOW();";                  
        $res = $this->select_all($query);
        return $res;
    }

    public function ExpedientesPorTipoInforme($tipo_informe) {
        // Selecciona tareas activas cuya hora de ejecución ya pasó
        $query = "SELECT dias_vencimiento FROM tipo_informe WHERE nombre_tipo = ?";                  
        $res = $this->select($query);
        $data = array($tipo_informe);
        return $this->select($res, $data); 
        return $res;
    }

    /**
     * Obtiene la lista de correos para una tarea específica.
     * @param int $id_tarea ID de la tarea programada.
     * @return array Lista de destinatarios activos.
     */
    public function getDestinatariosPorTarea2(int $id_tarea)
    {
        $sql = "SELECT id, correo_destino
                FROM alerta_destinatarios
                WHERE id_tarea_programada = ?
                AND estado = 'activa';";
        $res = $this->select($sql);
        return $res;
    }

    public function getReport($dias)
    {
        $sql = "SELECT id_expediente, indice_01, fecha_vencimiento FROM 
                expediente WHERE estado = 'Activo' AND fecha_vencimiento BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) 
                              AND DATE_ADD(NOW(), INTERVAL ? DAY) ORDER BY fecha_vencimiento ASC;";
        $res = $this->select($sql);
        return $res;
    }

    /**
     * Obtiene los correos electrónicos a los que se debe enviar la alerta.
     */
    public function getDestinatariosPorTarea($id_tarea) {
        // Simulación de consulta a la DB (tabla 'destinatarios_tarea')
        // En tu código real: $this->db->get_where('destinatarios_tarea', ['id_tarea' => $id_tarea, 'activo' => 1])->result_array();

        // Simulación:
        return [
            ['correo_destino' => 'aldo.silva@printec.com.py'],
            ['correo_destino' => 'aldo.silva@printec.com.py'],
        ];
    }
    /**
     * Actualiza la fecha de la próxima ejecución en la base de datos.
     */
    public function actualizarTareaProgramada($id_tarea, $frecuencia) {
        // 1. Calcular la próxima fecha basada en la $frecuencia ('HOURLY', 'DAILY', 'MONTHLY', etc.)
        $proxima_fecha = $this->calcularProximaFecha($frecuencia);
        
        $data = ['fecha_proxima_ejecucion' => $proxima_fecha];
        
        // En tu código real: $this->db->where('id', $id_tarea)->update('tareas_programadas', $data);
        // echo " [DB UPDATE] Tarea $id_tarea actualizada a: $proxima_fecha\n"; // Descomentar para depuración
        return true;
    }

    /**
     * Lógica para calcular la próxima fecha de ejecución.
     */
    private function calcularProximaFecha($frecuencia) {
        $now = new DateTime();
        switch (strtoupper($frecuencia)) {
            case 'HOURLY':
                $now->modify('+1 hour');
                break;
            case 'DAILY':
                $now->modify('+1 day');
                break;
            case 'WEEKLY':
                $now->modify('+1 week');
                break;
            case 'MONTHLY':
                $now->modify('+1 month');
                break;
            default:
                $now->modify('+1 day'); // Frecuencia por defecto
        }
        return $now->format('Y-m-d H:i:s');
    }

    public function getReporteData(array $tarea) {
        $tipo_informe = $tarea['tipo_informe'] ?? '';
        $fecha_limite = null;
        $dias = 0;

        // 1. Determinar el rango de fechas basado en el tipo de informe
        switch ($tipo_informe) {
            case 'VENC_5_DIAS':
                $dias = 5;
                break;
            case 'VENC_15_DIAS':
                $dias = 15;
                break;
            case 'VENC_1_MES':
                // 1 mes (aproximadamente 30 días)
                $dias = 30; 
                break;
            case 'VENC_3_MESES':
                // 3 meses (aproximadamente 90 días)
                $dias = 90;
                break;
            // Aquí puedes añadir más casos, como 'INFORME_DIARIO_VENTAS', si lo necesitas.
            default:
                // Tipo de informe no manejado, se devuelve array vacío.
                return []; 
        }

        // Si se define un rango de días, calculamos la fecha límite para la consulta
        if ($dias > 0) {
            // Calcula la fecha a futuro (HOY + X DÍAS)
            $fecha_limite = date('Y-m-d H:i:s', strtotime("+$dias days"));            
            $query = "SELECT id_expediente, indice_01, fecha_vencimiento FROM 
                expediente WHERE estado = 'Activo' AND fecha_vencimiento BETWEEN DATE_ADD(NOW(), INTERVAL 1 DAY) 
                              AND DATE_ADD(NOW(), INTERVAL 8 DAY) ORDER BY fecha_vencimiento ASC;";
            $data = array($fecha_limite);            
            // Asume que $this->select_all() es el método de tu framework para SELECT
            $expedientes = $this->select_all($query, $data);
            return $expedientes;
        }

        return []; // Retorna array vacío si no se pudo determinar el tipo
    }
    
    /**
     * Registra un evento (exitoso o fallido) en el historial.
     * @param int $id_tarea ID de la tarea ejecutada.
     * @param string $correo_destino Email al que se intentó enviar.
     * @param string $estado 'Exitoso' o 'Error'.
     * @param string $detalle Mensaje detallado del resultado.
     * * NOTA: Se asume que $this->execute_query() es el método de tu framework para
     * ejecutar consultas de tipo INSERT/UPDATE/DELETE con parámetros.
     */
   public function logHistorial(int $documento_id, string $correo_destino, string $estado, string $detalle) {
        $query = "INSERT INTO alerta_historial 
                      (documento_id, correo_destino, fecha_envio, estado, detalle)
                        VALUES (?, ?, NOW(), ?, ?)";
        $data = array($documento_id, $correo_destino, $estado, $detalle);
        // Asume que tu clase Mysql tiene un método para ejecutar consultas (ej. save/insert)
        return $this->execute_query($query, $data); 
    }

    /**
     * Actualiza la tarea principal, marcando la última ejecución
     * y calculando la próxima según la frecuencia.
     * * NOTA: Se utiliza PHP DateInterval para el cálculo de fechas, lo cual es más robusto.
     */
    public function actualizarTareaProgramada2(int $id_tarea, string $frecuencia) {
        // Define el intervalo de PHP DateInterval según la frecuencia
        $intervalo = 'P1D'; // Por defecto: 1 día (Daily)
        switch (strtoupper($frecuencia)) {
            case 'HORARIA':
                $intervalo = 'PT1H'; // 1 hora
                break;
            case 'DIARIA':
                $intervalo = 'P1D'; // 1 día
                break;
            case 'SEMANAL':
                $intervalo = 'P1W'; // 1 semana
                break;
            case 'MENSUAL':
                $intervalo = 'P1M'; // 1 mes
                break;
            default:
                // Si la frecuencia es desconocida, usa el valor por defecto de 1 día.
                break;
        }

        // Calcula la próxima fecha de ejecución
        $fechaProxima = new DateTime();
        $fechaProxima->add(new DateInterval($intervalo));
        $fechaProximaStr = $fechaProxima->format('Y-m-d H:i:s');

        // Actualiza la tarea
        $query = "UPDATE tarea_programada
                  SET 
                      fecha_ultima_ejecucion = NOW(),
                      fecha_proxima_ejecucion = ?
                  WHERE id = ?";
                  
        $data = array($fechaProximaStr, $id_tarea);
        
        // Asume que tu clase Mysql tiene un método para ejecutar consultas (ej. save/update)
        return $this->execute_query($query, $data);
    }
    
    // Si tu framework usa un método llamado 'execute_query', mantenlo.
    // Si usa 'insert', 'update', 'save', 'ejecutar', reemplaza $this->execute_query por el nombre correcto.
    public function execute_query(string $query, array $data = []) {
        // Ejemplo de cómo podría ser esta función en tu clase Mysql (o la que heredes)
        // $stmt = $this->db->prepare($query);
        // return $stmt->execute($data);
        return true; // placeholder
    }
}
