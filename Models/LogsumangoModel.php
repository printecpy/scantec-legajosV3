<?php
class LogsumangoModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectLogsumango()
    {
        $sql = "SELECT * FROM logs_umango";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteLogFecha(string $desde)
    {
        $this->desde = $desde;
        $sql = "SELECT * FROM logs_umango WHERE fecha_finalizacion LIKE '%$desde%' order by idlog_umango asc;";
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
?>