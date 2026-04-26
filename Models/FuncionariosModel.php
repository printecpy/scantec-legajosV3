<?php
class FuncionariosModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectFuncionario()
    {
        $sql = "SELECT * FROM funcionario";
        $res = $this->select_all($sql);
        return $res;
    }
    public function insertarFuncionario(String $documento, String $nombre, String $direccion, String $telefono)
    {
     //   $this->codigo = $codigo;
        $this->documento = $documento;
        $this->nombre = $nombre;
       // $this->carrera = $carrera;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $query = "INSERT INTO funcionario(documento,nombre,direccion,telefono,estado) VALUES (?,?,?,?,?)";
        $data = array($this->documento, $this->nombre, $this->direccion, $this->telefono, 1);
        $this->insert($query, $data);
        return true;
    }
    public function editFuncionario(int $id)
    {
        $sql = "SELECT * FROM funcionario WHERE id = ?";
        $res = $this->select($sql, [$id]);
        return $res;
    }
    public function actualizarFuncionario(String $documento, String $nombre, String $direccion, String $telefono, int $id)    {
        //$this->codigo = $codigo;
        $this->documento = $documento;
        $this->nombre = $nombre;
       // $this->carrera = $carrera;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
        $this->id = $id;
        $query = "UPDATE funcionario SET documento = ?, nombre = ?, direccion = ?, telefono = ?  WHERE id = ?";
        $data = array($this->documento, $this->nombre, $this->direccion, $this->telefono, $this->id);
        $this->update($query, $data);
        return true;
    }


    public function estadoFuncionario(int $estado, int $id)
    {
        $this->estado = $estado;
        $this->id = $id;
        $query = "UPDATE funcionario SET estado = ? WHERE id = ?";
        $data = array($this->estado, $this->id);
        $this->update($query, $data);
        return true;
    }
}
?>
