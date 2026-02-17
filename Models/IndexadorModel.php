<?php
class IndexadorModel extends Mysql{
    protected $columna_01, $columna_02, $columna_03;
    public function __construct()
    {
        parent::__construct();
    }

    public function insertarUnirpdf(string $columna_01, string $columna_02, string $columna_03, string $usuario, string $nombre_archivo, string $ruta_creacion)
    {
        $return = "";
        $this->columna_01 = $columna_01;
        $this->columna_02 = $columna_02;
        $this->columna_03 = $columna_03; 
        $this->usuario = $usuario;
        $this->ruta_creacion = $ruta_creacion;  
        $this->nombre_archivo = $nombre_archivo;         
        $query = "INSERT INTO unirpdf(columna_01, columna_02, columna_03, usuario, ruta_creacion, nombre_archivo, fecha_creacion) VALUES (?, ?, ?, ?, ?, ?, (CURRENT_TIME))";
        $data = array($this->columna_01, $this->columna_02, $this->columna_03, $this->usuario, $this->ruta_creacion, $this->nombre_archivo);
        $resul = $this->insert($query, $data);
        $return = $resul; 
        return $return;
    }

    public function selectConfiguracion()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

}