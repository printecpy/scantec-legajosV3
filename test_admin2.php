<?php
require 'Config/Config.php';
require_once 'Config/ConfigDb.php';
require_once 'Libraries/Core/Conexion.php';
require_once 'Libraries/Core/Mysql.php';
class T extends Mysql {
    public function run() {
        print_r($this->select_all("SELECT id, usuario, id_rol, estado_usuario FROM usuarios WHERE usuario='admin'"));
    }
}
$t = new T();
$t->run();
