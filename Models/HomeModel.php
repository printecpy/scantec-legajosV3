<?php
class HomeModel extends Mysql{
    public $usuario, $clave;
    public function __construct()
    {
        parent::__construct();
    }
    /* public function consultarPW(string $nombre, string $usuario) {
        $query = "SELECT * FROM usuarios WHERE nombre = '$nombre' AND usuario = '$usuario'";
        $resul = $this->select($query);
        return $resul;
    }
    
    public function restaurar_pw(string $nueva, string $nombre, string $usuario) {
        $this->nueva = $nueva;
        $this->nombre = $nombre;
        $this->usuario = $usuario;
        $query = "UPDATE usuarios SET clave = ? WHERE nombre = ? AND usuario = ?";
        $data = array($this->nueva, $this->nombre, $this->usuario);
        $resul = $this->update($query, $data);
        return $resul;
    } */

    public function consultarPW($nombre, $usuario) {
        $query = "SELECT * FROM usuarios WHERE nombre = ? AND usuario = ?;";
        $resul = $this->select($query, [$nombre, $usuario]);
        return $resul;
    }
    
    public function restaurar_pw($clave, $nombre, $usuario) {
        $query = "UPDATE usuarios SET clave = ?, clave_actualizacion = NOW() WHERE nombre = ? AND usuario = ?;";
        $data = array($clave, $nombre, $usuario);
        $resul = $this->update($query, $data);
        return $resul;
    }
    
}
?>
