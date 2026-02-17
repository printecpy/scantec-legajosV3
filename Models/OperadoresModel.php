<?php
class OperadoresModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectOperador()
    {
        $sql = "SELECT id_operador, nombre, apellido, direccion, proyecto, estado FROM op_operador order by id_operador asc";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectEstacio_trabajo()
    {
        $sql = "SELECT * FROM op_est_trabajo order by id_est asc;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectEscaner()
    {
        $sql = "SELECT * FROM op_escaner order by id_escaner asc;";
        $res = $this->select_all($sql);
        return $res;
    }
    public function insertarOperador(String $nombre, string $apellido, string $direccion, string $proyecto)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->direccion = $direccion;
        $this->proyecto = $proyecto;
        $query = "INSERT INTO op_operador (nombre, apellido, direccion, proyecto, estado) VALUES (?, ?, ?, ?, 'ACTIVO')";
        $data = array($this->nombre, $this->apellido, $this->direccion, $this->proyecto);
        $this->insert($query, $data);
        return true;
    }

    public function insertarPC(String $nombre_pc)
    {
        $this->nombre_pc = $nombre_pc;
        $query = "INSERT INTO op_est_trabajo (nombre_pc, estado) VALUES (?, 'ACTIVO')";
        $data = array($this->nombre_pc);
        $this->insert($query, $data);
        return true;
    }
    public function editOperador(int $id_operador)
    {
        $sql = "SELECT * FROM op_operador WHERE id_operador = $id_operador";
        $res = $this->select($sql);
        return $res;
    }
    public function actualizarOperador(string $nombre, string $apellido, string $direccion, string $proyecto, int $id_operador)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->direccion = $direccion;
        $this->proyecto = $proyecto;
        $this->id_operador = $id_operador;
        $query = "UPDATE op_operador SET nombre=?, apellido=?, direccion=?, proyecto=? WHERE id_operador = ?";
        $data = array($this->nombre, $this->apellido, $this->direccion, $this->proyecto, $this->id_operador);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoOperador(string $estado, int $id_operador)
    {
        $this->estado = $estado;
        $this->id_operador = $id_operador;        
        $query = "UPDATE op_operador SET estado =? WHERE id_operador =?;";
        $data = array($this->estado, $this->id_operador);
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
