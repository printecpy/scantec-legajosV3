<?php
class Funcionarios extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    public function funcionarios()
    {
        $data = $this->model->selectFuncionario();
        $this->views->getView($this, "listar", $data);
    }
    public function registrar()
    {
    $token = $_POST['token'];
 
    if($_SESSION['token'] == $token){
        //$codigo = $_POST['codigo'];
        $documento = $_POST['documento'];
        $nombre = $_POST['nombre'];
        //$carrera = $_POST['carrera'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $insert = $this->model->insertarFuncionario($documento, $nombre, $direccion, $telefono);
        if ($insert) {
            header("location: " . base_url() . "funcionarios");
            die();    
        }
    }else{
        echo "Has intentado acceder sin cumplir con el token";
        header("location: " . base_url() . "funcionarios");
    }
    }
    public function editar()
    {
        $id = $_GET['id'];
        $data = $this->model->editFuncionario($id);
        if ($data == 0) {
            $this->funcionarios();
        } else {
            $this->views->getView($this, "editar", $data);
        }
    }
    public function modificar()
    {
        $id = $_POST['id'];
        $documento = $_POST['documento'];
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $actualizar = $this->model->actualizarFuncionario($documento, $nombre, $direccion, $telefono, $id);
        if ($actualizar) {   
            header("location: " . base_url() . "funcionarios"); 
            die();
        }
    }

    public function eliminar()
    {
        $id = $_POST['id'];
        $this->model->estadoFuncionario(0, $id);
        header("location: " . base_url() . "funcionarios");
        die();
    }
    public function reingresar()
    {
        $id = $_POST['id'];
        $this->model->estadoFuncionario(1, $id);
        header("location: " . base_url() . "funcionarios");
        die();
    }
}
?>