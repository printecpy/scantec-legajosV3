<?php
class LogsModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectLogs()
    {
        $sql = "SELECT id_log, fecha,
                REGEXP_REPLACE(executedSQL, 'clave = ''[^'']+''', 'clave = ''*********************''') AS executedSQL,
                REGEXP_REPLACE(reverseSQL, 'clave = ''[^'']+''', 'clave = ''*********************''') AS reverseSQL
            FROM logs ORDER BY id_log DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectViews()
    {
        $sql = "SELECT * 
        FROM document_views 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectSesionFails()
    {
        $sql = "SELECT * 
        FROM intentos_login_fallidos 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectSesions()
    {
        $sql = "SELECT * 
        FROM v_visitas 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id_visita DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
}
