<?php
class ApiLogModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
        $this->asegurarTabla();
    }

    private function asegurarTabla(): void
    {
        try {
            $this->insert("CREATE TABLE IF NOT EXISTS api_logs (
                id INT NOT NULL AUTO_INCREMENT,
                fecha_hora DATETIME NOT NULL,
                id_usuario INT DEFAULT NULL,
                endpoint VARCHAR(255) DEFAULT NULL,
                metodo_http VARCHAR(10) DEFAULT NULL,
                codigo_respuesta INT DEFAULT NULL,
                mensaje_log VARCHAR(255) DEFAULT NULL,
                ip_origen VARCHAR(45) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_usuario (id_usuario),
                KEY idx_fecha (fecha_hora)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci", []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla api_logs: ' . $e->getMessage());
        }
    }

    public function registrarLogApi(
        $id_usuario,
        $endpoint,
        $metodo_http,
        $codigo_respuesta,
        $mensaje_log,
        $ip_origen
    ) {
        $query = "INSERT INTO api_logs (fecha_hora, id_usuario, endpoint, metodo_http, codigo_respuesta, mensaje_log, ip_origen) 
                  VALUES (?, ?, ?, ?, ?, ?, ?);";
        $data = array(
            date('Y-m-d H:i:s'),
            $id_usuario,
            $endpoint,
            $metodo_http,
            $codigo_respuesta,
            substr($mensaje_log, 0, 255),
            $ip_origen
        );
        $this->insert($query, $data);
        return true;
    }
}