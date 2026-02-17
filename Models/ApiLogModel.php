<?php
class ApiLogModel extends Mysql // O la clase base de tus modelos
{
    public function __construct()
    {
        parent::__construct();
    }

    public function registrarLogApi(
        $id_usuario,
        $endpoint,
        $metodo_http,
        $codigo_respuesta,
        $mensaje_log,
        $ip_origen
    ) {
        // 1. Definir la sentencia SQL con placeholders '?'
        // Asegúrate de que los nombres de columna coincidan con tu tabla `api_logs`
        $query = "INSERT INTO api_logs (fecha_hora, id_usuario, endpoint, metodo_http, codigo_respuesta, mensaje_log, ip_origen) 
              VALUES (?, ?, ?, ?, ?, ?, ?);";

        // 2. Ensamblar los datos como un array indexado (listado de valores)
        $data = array(
            date('Y-m-d H:i:s'), // 1. fecha_hora
            $id_usuario,         // 2. id_usuario
            $endpoint,           // 3. endpoint
            $metodo_http,        // 4. metodo_http
            $codigo_respuesta,   // 5. codigo_respuesta
            substr($mensaje_log, 0, 255), // 6. mensaje_log (Truncado)
            $ip_origen           // 7. ip_origen
        );

        // 3. LLAMADA CORRECTA: Pasar el string SQL primero, y el array de datos segundo.
        $this->insert($query, $data);

        return true;
    }
}
