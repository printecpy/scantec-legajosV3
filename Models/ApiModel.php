<?php
class ApiModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function selectExpedienteCantidad()
    {
        $sql = "SELECT COUNT(*) AS cant_expediente FROM expediente";
        $res = $this->select($sql);
        return $res;
    }


}
