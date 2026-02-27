<?php
class AlertaModel extends Mysql {
    
    public function __construct()
    {
        parent::__construct();
    }

    public function getTareasPendientes() {
        $query = "SELECT id, nombre_tarea, tipo_informe, frecuencia, 
                         fecha_proxima_ejecucion, fecha_ultima_ejecucion, estado
                  FROM tarea_programada 
                  WHERE estado = 'activo' 
                  AND fecha_proxima_ejecucion <= NOW()";                  
        return $this->select_all($query);
    }

    public function getDestinatariosPorTarea($id_tarea) {
        $id_limpio = intval($id_tarea);
        $sql = "SELECT id, correo_destino FROM alerta_destinatarios WHERE id_tarea_programada = $id_limpio AND estado = 'activa'";
        return $this->select_all($sql);
    }

    public function logHistorial(int $id_tarea, string $correo_destino, string $estado, string $detalle) {
        $query = "INSERT INTO alerta_historial (documento_id, correo_destino, fecha_envio, estado, detalle) VALUES (?, ?, NOW(), ?, ?)";
        $data = array($id_tarea, $correo_destino, $estado, $detalle);
        return $this->insert($query, $data); 
    }

    public function actualizarTareaProgramada(int $id_tarea, string $frecuencia) {
        $intervalo = 'P1D'; 
        switch (strtoupper($frecuencia)) {
            case 'HORARIA': $intervalo = 'PT1H'; break;
            case 'DIARIA':  $intervalo = 'P1D'; break;
            case 'SEMANAL': $intervalo = 'P1W'; break;
            case 'MENSUAL': $intervalo = 'P1M'; break;
        }

        $fechaProxima = new DateTime();
        $fechaProxima->add(new DateInterval($intervalo));
        $fechaProximaStr = $fechaProxima->format('Y-m-d H:i:s');

        $query = "UPDATE tarea_programada SET fecha_ultima_ejecucion = NOW(), fecha_proxima_ejecucion = ? WHERE id = ?";
        $data = array($fechaProximaStr, $id_tarea);
        return $this->update($query, $data);
    }

    public function getTareaById($id) {
        $id_limpio = intval($id);
        $sql = "SELECT * FROM tarea_programada WHERE id = $id_limpio";
        return $this->select($sql); 
    }

    public function updateTarea(int $id, string $nombre, string $tipo, string $frecuencia) {
        $sql = "UPDATE tarea_programada SET nombre_tarea = ?, tipo_informe = ?, frecuencia = ? WHERE id = ?";
        $data = array($nombre, $tipo, $frecuencia, $id);
        return $this->update($sql, $data);
    }

    public function addDestinatario(int $id_tarea, string $correo) {
        $sql = "INSERT INTO alerta_destinatarios (id_tarea_programada, correo_destino, estado) VALUES (?, ?, 'activa')";
        $data = array($id_tarea, $correo);
        return $this->insert($sql, $data);
    }

    public function deleteDestinatario(int $id_destinatario) {
        $sql = "DELETE FROM alerta_destinatarios WHERE id = ?";
        $data = array($id_destinatario);
        return $this->update($sql, $data); 
    }

    public function estadoTarea(int $id, string $estado) {
        $sql = "UPDATE tarea_programada SET estado = ? WHERE id = ?";
        $data = array($estado, $id);
        return $this->update($sql, $data);
    }

    public function getReporteData(array $tarea) {
        $tipo_informe = $tarea['tipo_informe'] ?? '';
        $dias = 0;

        switch ($tipo_informe) {
            case 'VENC_5_DIAS':  $dias = 5; break;
            case 'VENC_15_DIAS': $dias = 15; break;
            case 'VENC_1_MES':   $dias = 30; break;
            case 'VENC_3_MESES': $dias = 90; break;
            default: return []; 
        }

        if ($dias > 0) {
            $fecha_limite = date('Y-m-d H:i:s', strtotime("+$dias days"));            
            $query = "SELECT id_expediente, indice_01, fecha_vencimiento FROM expediente 
                      WHERE estado = 'Activo' 
                      AND fecha_vencimiento BETWEEN CURDATE() AND '$fecha_limite' 
                      ORDER BY fecha_vencimiento ASC";
            return $this->select_all($query);
        }
        return []; 
    }
}
?>