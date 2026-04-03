<?php
class EscaneosModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectEscaneo()
    {
        $sql = "SELECT a.id_esc, a.fecha, a.pag_esc, a.cant_exp, a.id_est, d.nombre_pc as nombre_pc, a.id, b.nombre, a.id_operador, 
        concat_ws(' ',c.nombre, c.apellido) as operador, a.estado FROM op_escaneo a, usuarios b, op_operador c, op_est_trabajo d 
        WHERE a.id=b.id AND a.id_operador=c.id_operador AND a.id_est=d.id_est ORDER BY id_esc ASC;";
        $res = $this->select_all($sql);
        return $res;
    }
    
    public function selectOperador()
    {
        $sql = "SELECT id_operador, nombre, apellido, direccion, proyecto, estado FROM op_operador where estado='ACTIVO' order by id_operador asc";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectUsuarios()
    {
        $sql = "SELECT * FROM usuarios";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectEstTrabajo()
    {
        $sql = "SELECT * FROM op_est_trabajo";
        $res = $this->select_all($sql);
        return $res;
    }


    public function insertarEscaneo(String $fecha, int $pag_esc, int $cant_exp, int $id_est, int $id, int $id_operador)
    {
        $this->fecha = $fecha;
        $this->pag_esc = $pag_esc;
        $this->cant_exp = $cant_exp;
        $this->id_est = $id_est;
        $this->id = $id;
        $this->id_operador = $id_operador;
        $query = "INSERT INTO op_escaneo(fecha, pag_esc, cant_exp, id_est, id, id_operador, estado)
         VALUES (?,?,?,?,?,?,'ACTIVO')";
        $data = array($this->fecha, $this->pag_esc, $this->cant_exp, $this->id_est, $this->id, $this->id_operador);
        $this->insert($query, $data);
        return true;
    }
    public function editEscaneo(int $id_esc)
    {
        $sql = "SELECT * FROM op_escaneo WHERE id_esc = $id_esc";
        $res = $this->select($sql);
        return $res;
    }

    public function detEscaneo(int $id_esc)
    {
        $sql = "SELECT * FROM det_escaneo WHERE id_esc = $id_esc";
        $res = $this->select_all($sql);
        return $res;
    }

    public function actualizarEscaneo(string $fecha, int $pag_esc, int $cant_exp, int $id_est, int $id_operador, int $id_esc)
    {
        $this->fecha = $fecha;
        $this->pag_esc = $pag_esc;
        $this->cant_exp = $cant_exp;
        $this->id_est = $id_est;
        $this->id_operador = $id_operador;
        $this->id_esc = $id_esc;
        $query = "UPDATE op_escaneo SET fecha=?, pag_esc=?, cant_exp=?, id_est=?, id_operador=? WHERE id_esc = ?";
        $data = array($this->fecha, $this->pag_esc, $this->cant_exp, $this->id_est, $this->id_operador, $this->id_esc);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoEscaneo(string $estado,int $id_esc)
    {
        $this->estado = $estado;
        $this->id_esc = $id_esc;
        $query = "UPDATE op_escaneo SET estado = ? WHERE id_esc = ?";
        $data = array($this->estado, $this->id_esc);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function reporteEscFecha(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_escaneos WHERE fecha BETWEEN '$desde' AND '$hasta' order by id_esc asc;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteEsctotal(string $mes_desde, string $anio_desde, string $mes_hasta, string $anio_hasta)
    {
        // Convertir las cadenas a valores numÃ©ricos
        $mes_desde = (int)$mes_desde;
        $anio_desde = (int)$anio_desde;
        $mes_hasta = (int)$mes_hasta;
        $anio_hasta = (int)$anio_hasta;
        // Escapar las variables para evitar inyecciones SQL
        $this->mes_desde = $mes_desde;
        $this->anio_desde = $anio_desde;
        $this->mes_hasta = $mes_hasta;
        $this->anio_hasta = $anio_hasta;
        // Construir la consulta SQL
        $sql = "SELECT pag_escaneadas, 
                cant_expedientes, 
                mes_anio
         FROM (
             SELECT SUM(pag_esc) AS pag_escaneadas, 
                    SUM(cant_exp) AS cant_expedientes,
                    CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS mes_anio,
                                MIN(fecha) AS fecha_orden
             FROM v_escaneos
             WHERE (YEAR(fecha) = $anio_desde AND MONTH(fecha) >= $mes_desde)
                 OR (YEAR(fecha) = $anio_hasta AND MONTH(fecha) <= $mes_hasta)
                 OR (YEAR(fecha) > $anio_desde AND YEAR(fecha) < $anio_hasta)
             GROUP BY YEAR(fecha), MONTH(fecha), mes_anio
         ) AS derived_table
         ORDER BY fecha_orden ASC;";

        // Ejecutar la consulta y devolver el resultado
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteEscOperador(int $id_operador)
    {
        $this->id_operador = $id_operador;
        $sql = "SELECT pag_escaneadas, 
        cant_expedientes, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        operador
        FROM (
            SELECT SUM(pag_esc) AS pag_escaneadas, 
                    SUM(cant_exp) AS cant_expedientes,
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    operador
            FROM v_escaneos 
            WHERE id_operador = $id_operador
            GROUP BY YEAR(fecha), MONTH(fecha), operador
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteEscpc(int $id_est)
    {
        $this->id_est = $id_est;
        $sql = "SELECT pag_escaneadas, 
        cant_expedientes, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        id_est, nombre_pc
        FROM (
            SELECT SUM(pag_esc) AS pag_escaneadas, 
                    SUM(cant_exp) AS cant_expedientes,
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    id_est, nombre_pc 
            FROM v_escaneos 
            WHERE id_est = $id_est
            GROUP BY YEAR(fecha), MONTH(fecha), id_est, nombre_pc 
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function insertarDetEscaneo(String $nombre_archivo, int $num_pag, String $fecha_creacion, String $fecha_modificacion, String $ruta_archivo,
                    int $id_esc)
    {
        $this->id_esc = $id_esc;
        $this->nombre_archivo = $nombre_archivo;
        $this->num_pag = $num_pag;
        $this->fecha_creacion = $fecha_creacion;
        $this->fecha_modificacion = $fecha_modificacion;
        $this->ruta_archivo = $ruta_archivo;
        $query = "INSERT INTO det_escaneo (id_esc, nombre_archivo, num_pag, fecha_creacion, fecha_modificacion, ruta_archivo) 
        VALUES (?, ?, ?, ?, ?, ?)";
        $data = array($this->id_esc, $this->nombre_archivo, $this->num_pag, $this->fecha_creacion, $this->fecha_modificacion, $this->ruta_archivo);
        $this->insert($query, $data);
        return true;
    }

    public function selectDetEscaneoPag(int $id_esc)
    {
        $sql = "SELECT sum(num_pag) as pag_esc FROM det_escaneo WHERE id_esc = $id_esc;";
        $res = $this->select($sql);
        return $res;
    }

    public function selectDetEscaneoExp(int $id_esc)
    {
        $sql = "SELECT Count(*) as cant_exp FROM det_escaneo WHERE id_esc = $id_esc;";
        $res = $this->select($sql);
        return $res;
    }

    // FunciÃ³n para validar el formato de fecha
    function validarFecha($fecha)
    {
        $fecha_validada = DateTime::createFromFormat('m/d/Y H:i:s', $fecha);
        if (!$fecha_validada) {
            throw new Exception("El formato de fecha no es vÃ¡lido");
        }
        return $fecha_validada->format('Y-m-d H:i:s');
    }
}