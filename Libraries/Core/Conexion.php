<?php
class Conexion
{
    private $conect;
    private $errorMessage = '';

    public function __construct()
    {
        $conexion = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . BD . ";charset=utf8mb4";

        try {
            $this->conect = new PDO($conexion, DB_USER, PASS);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conect->exec("SET @@lc_time_names = 'es_ES'");
        } catch (PDOException $e) {
            $this->conect = null;
            $this->errorMessage = $e->getMessage();
        }
    }

    public function conect()
    {
        return $this->conect;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}

?>
