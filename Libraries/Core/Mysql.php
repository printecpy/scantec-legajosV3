<?php
class Mysql extends Conexion
{
    private $conexion;
    private $strquery;
    private $arrvalues;
    private $id;
    private $connectionError = '';
    function __construct()
    {
        $conexionBase = new Conexion();
        $this->conexion = $conexionBase->conect();
        $this->connectionError = $conexionBase->getErrorMessage();
    }
    private function ensureConnection(): void
    {
        if (!($this->conexion instanceof PDO)) {
            $message = $this->connectionError !== '' ? $this->connectionError : 'No se pudo establecer la conexion con la base de datos.';
            throw new RuntimeException($message);
        }
    }
    public function insert(string $query, array $arrvalues)
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $this->arrvalues = $arrvalues;
        $insert = $this->conexion->prepare($this->strquery);
        $res = $insert->execute($this->arrvalues);
        if ($res) {
            $lastInsert = $this->conexion->lastInsertId();
        } else {
            $lastInsert = 0;
        }
        return $lastInsert;
    }
    /* public function select(string $query){
        $this->strquery = $query;
        $result = $this->conexion->prepare($this->strquery);
        $result->execute();
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data;
    } */
    public function select(string $query, array $arr_params = [])
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $result = $this->conexion->prepare($this->strquery);
        if (!empty($arr_params)) {
            $result->execute($arr_params);
        } else {
            $result->execute();
        }
        if (strpos(strtoupper($query), 'COUNT') !== false) {
            $data = $result->fetch(PDO::FETCH_ASSOC);
        } else {
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }
    public function select_all(string $query, array $arr_params = [])
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $result = $this->conexion->prepare($this->strquery);
        if (!empty($arr_params)) {
            $result->execute($arr_params);
        } else {
            $result->execute();
        }
        $data = $result->fetchall(PDO::FETCH_ASSOC);
        return $data;
    }
    public function update(string $query, array $arrvalues)
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $this->arrvalues = $arrvalues;
        $update = $this->conexion->prepare($this->strquery);
        $res = $update->execute($this->arrvalues);
        return $res;
    }
    public function delete(string $query)
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $result = $this->conexion->prepare($this->strquery);
        $result->execute();
        return $result;
    }
}
