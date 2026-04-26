<?php
class AdminModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectExpedientes()
    {
        $sql = "SELECT * FROM expediente WHERE estado = 'Activo'";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectEspecialidad()
    {
        $sql = "SELECT * FROM especialidad ";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectExpedienteCantidad(int $id_expediente)
    {
        $sql = "SELECT * FROM expediente WHERE id_expediente = ?";
        $res = $this->select($sql, [$id_expediente]);
        return $res;
    }
    public function selectFuncionarios()
    {
        $sql = "SELECT * FROM funcionario WHERE estado = 1";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectPrestamoCantidad()
    {
        $sql = "SELECT * FROM prestamo WHERE estado = 1";
        $res = $this->select($sql);
        return $res;
    }
    public function selectPrestamo()
    {
        $sql = "SELECT p.id, e.id as id_funci, e.nombre, l.id_expediente, l.documento, p.id_funcionario, p.id_expediente,
         s.id_especialidad, s.especialidad, p.fecha_prestamo, p.fecha_devolucion, p.observacion, p.estado
          FROM funcionario e INNER JOIN expediente l INNER JOIN especialidad s INNER JOIN prestamo p 
          ON p.id_funcionario = e.id WHERE p.id_expediente = l.id_expediente order by p.id desc";
        $res = $this->select_all($sql);
        return $res;
    }
    public function insertarPrestamo(int $funcionario, int $expediente,  int $especialidad, String $fecha_prestamo, String $fecha_devolucion ,String $observacion)
    {
        $this->expediente = $expediente;
        $this->funcionario = $funcionario;
        $this->especialidad = $especialidad;
        $this->fecha_prestamo = $fecha_prestamo;
        $this->fecha_devolucion = $fecha_devolucion;
        $this->observacion = $observacion;
        $query = "INSERT INTO prestamo(id_funcionario, id_expediente, id_especialidad, fecha_prestamo, fecha_devolucion, observacion, estado) 
        VALUES (?,?,?,?,?,?, 1)";
        $data = array($this->funcionario, $this->expediente, $this->especialidad, $this->fecha_prestamo, ' ', $this->observacion);
        $this->insert($query, $data);
        return true;
    }
    public function estadoPrestamo(int $estado, int $id)
    {
        //$this->fecha_devolucion = $fecha_devolucion;
        $this->estado = $estado;
        $this->id = $id;
        $query = "UPDATE prestamo SET fecha_devolucion= (SELECT CURRENT_TIME()), estado = ? WHERE id = ?";
        $data = array($this->estado, $this->id);
        $this->update($query, $data);
        return true;
    }
    /* public function actualizarCantidad(String $cantidad, int $id)
    {
        $this->cantidad = $cantidad;
        $this->id = $id;
        $query = "UPDATE libro SET cantidad = ? WHERE id = ?";
        $data = array($this->cantidad, $this->id);
        $this->update($query, $data);
        return true;
    } */
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function selectPrestamoDebe()
    {
        $sql = "SELECT e.id, e.nombre, l.id, l.titulo, p.id, p.id_estudiante, p.id_libro, p.fecha_prestamo, 
        p.fecha_devolucion, p.cantidad, p.observacion, p.estado FROM estudiante e INNER JOIN libro l
         INNER JOIN prestamo p ON p.id_estudiante = e.id WHERE p.id_libro = l.id AND p.estado = 1";
        $res = $this->select_all($sql);
        return $res;
    }
}
