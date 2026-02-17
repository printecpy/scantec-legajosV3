<?php
class OrdenamientoModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectOrdenamiento()
    {
        $sql = "SELECT * 
        FROM archivos_fisicos
        ORDER BY CAST(SUBSTRING(codigo_caja, LOCATE(' ', codigo_caja) + 1) AS UNSIGNED) ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function insertarOrdenamiento(String $codigo_caja, string $descripcion, string $ubicacion, string $fecha_almacenamiento, 
        string $observaciones, string $tipo)
    {
        $this->codigo_caja = $codigo_caja;
        $this->descripcion = $descripcion;
        $this->ubicacion = $ubicacion;
        $this->fecha_almacenamiento = $fecha_almacenamiento;
        $this->observaciones = $observaciones;
        $this->tipo = $tipo;
        $query = "INSERT INTO archivos_fisicos(codigo_caja, descripcion, ubicacion, fecha_almacenamiento, observaciones, tipo)
         VALUES (?,?,?,?,?,?)";
        $data = array($this->codigo_caja, $this->descripcion, $this->ubicacion, $this->fecha_almacenamiento, $this->observaciones, $this->tipo);
        $this->insert($query, $data);
        return true;
    }
    public function editOrdenamiento(int $id)
    {
        $sql = "SELECT * FROM archivos_fisicos WHERE id = $id";
        $res = $this->select($sql);
        return $res;
    }

    public function actualizarOrdenamiento(String $codigo_caja, string $descripcion, string $ubicacion, string $fecha_almacenamiento, 
        string $observaciones, string $tipo,  int $id)
    {
        $this->codigo_caja = $codigo_caja;
        $this->descripcion = $descripcion;
        $this->ubicacion = $ubicacion;
        $this->fecha_almacenamiento = $fecha_almacenamiento;
        $this->observaciones = $observaciones;
        $this->tipo = $tipo;
        $this->id = $id;
        $query = "UPDATE archivos_fisicos SET codigo_caja=?, descripcion=?, ubicacion=?, fecha_almacenamiento=?, observaciones=?, tipo=? WHERE id = ?";
        $data = array($this->codigo_caja, $this->descripcion, $this->ubicacion, $this->fecha_almacenamiento, $this->observaciones, $this->tipo, $this->id);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoLote(string $estado,int $id_registro)
    {
        $this->estado = $estado;
        $this->id_registro = $id_registro;
        $query = "UPDATE lote SET estado = ? WHERE id_registro = ?";
        $data = array($this->estado, $this->id_registro);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function selectExpCant(int $id_lote)
    {
        $sql = "SELECT COUNT(*) AS cant_expediente FROM detalle_proceso WHERE id_registro = $id_lote";
        $res = $this->select($sql);
        return $res;
    }

    public function selectCantPag(int $id_lote)
    {
        $sql = "SELECT SUM(total_pag) AS total_paginas FROM detalle_proceso WHERE id_registro = $id_lote";
        $res = $this->select($sql);
        return $res;
    }
    
}
