<?php
/**
 * Conexión a la base de datos de usuarios
 * 
 * Esta clase se utiliza para conectar a la base de datos de usuarios
 * (datos personales de clientes) separada de la base de datos de legajos.
 */

class ConexionUsuarios
{
    private $conect;
    private $errorMessage = '';

    public function __construct()
    {
        $conexion = "mysql:host=" . DB_USUARIOS_HOST . ";port=" . DB_USUARIOS_PORT . ";dbname=" . BD_USUARIOS . ";charset=utf8mb4";

        try {
            $this->conect = new PDO($conexion, DB_USUARIOS_USER, DB_USUARIOS_PASS);
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conect->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
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

/**
 * Clase Mysql para la base de datos de usuarios
 */
class MysqlUsuarios
{
    private $conexion;
    private $strquery;
    private $arrvalues;
    private $id;
    private $connectionError = '';

    function __construct()
    {
        $conexionBase = new ConexionUsuarios();
        $this->conexion = $conexionBase->conect();
        $this->connectionError = $conexionBase->getErrorMessage();
    }

    private function ensureConnection(): void
    {
        if (!($this->conexion instanceof PDO)) {
            $message = $this->connectionError !== '' ? $this->connectionError : 'No se pudo establecer la conexión con la base de datos de usuarios.';
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

    public function delete(string $query, array $arrvalues = [])
    {
        $this->ensureConnection();
        $this->strquery = $query;
        $this->arrvalues = $arrvalues;
        $delete = $this->conexion->prepare($this->strquery);
        $res = $delete->execute($this->arrvalues);
        return $res;
    }
}