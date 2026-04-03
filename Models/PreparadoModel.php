<?php
class PreparadoModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectPreparado()
    {
        $sql = "SELECT a.id_prep, a.fecha, a.cant_expediente, a.cant_cajas, a.observaciones, a.id, b.nombre, a.id_operador, 
        concat_ws(' ',c.nombre, c.apellido) as operador, a.estado FROM op_preparado a, usuarios b, op_operador c
        WHERE a.id=b.id AND a.id_operador=c.id_operador order by id_prep asc;";
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

    public function insertarPreparado(String $fecha, int $cant_expediente, string $cant_cajas, string $observaciones, int $id, int $id_operador)
    {
        $this->fecha = $fecha;
        $this->cant_expediente = $cant_expediente;
        $this->cant_cajas = $cant_cajas;
        $this->observaciones = $observaciones;
        $this->id = $id;
        $this->id_operador = $id_operador;
        $query = "INSERT INTO op_preparado (fecha, cant_expediente, cant_cajas, observaciones, id, id_operador, estado) VALUES
         (?, ?, ?, ?, ?, ?, 'ACTIVO')";
        $data = array($this->fecha, $this->cant_expediente, $this->cant_cajas, $this->observaciones, $this->id, $this->id_operador);
        $this->insert($query, $data);
        return true;
    }

    public function editPreparado(int $id_prep)
    {
        $sql = "SELECT * FROM op_preparado WHERE id_prep = $id_prep";
        $res = $this->select($sql);
        return $res;
    }

    public function actualizarPreparado(String $fecha, int $cant_expediente, string $cant_cajas, string $observaciones, int $id_operador, int $id_prep)
    {
        $this->fecha = $fecha;
        $this->cant_expediente = $cant_expediente;
        $this->cant_cajas = $cant_cajas;
        $this->observaciones = $observaciones;
        $this->id_operador = $id_operador;
        $this->id_prep = $id_prep;
        $query = "UPDATE op_preparado SET fecha=?, cant_expediente=?, cant_cajas=?, observaciones=?, id_operador=? WHERE id_prep = ?";
        $data = array($this->fecha, $this->cant_expediente, $this->cant_cajas, $this->observaciones, $this->id_operador, $this->id_prep);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoPreparado(string $estado, int $id_prep)
    {
        $this->estado = $estado;
        $this->id_prep = $id_prep;
        $query = "UPDATE op_preparado SET estado = ? WHERE id_prep = ?";
        $data = array($this->estado, $this->id_prep);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function reportePreparadoFecha(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_preparado WHERE fecha BETWEEN '$desde' AND '$hasta' order by id_prep asc;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reportePreparadototal(string $mes_desde, string $anio_desde, string $mes_hasta, string $anio_hasta)
    {
        // Convertir las cadenas a valores numéricos
        $mes_desde = (int)$mes_desde;
        $anio_desde = (int)$anio_desde;
        $mes_hasta = (int)$mes_hasta;
        $anio_hasta = (int)$anio_hasta;
        // Escapar las variables para evitar inyecciones SQL
        $this->mes_desde = $mes_desde;
        $this->anio_desde = $anio_desde;
        $this->mes_hasta = $mes_hasta;
        $this->anio_hasta = $anio_hasta;
        $sql = "SELECT cant_expedientes, 
                    cajas_totales, 
                    mes_anio
                    FROM (
                        SELECT SUM(cant_expediente) AS cant_expedientes, SUM(cant_cajas) AS cajas_totales,
                                CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS mes_anio,
                                MIN(fecha) AS fecha_orden
                        FROM v_preparado
                        WHERE (YEAR(fecha) = $anio_desde AND MONTH(fecha) >= $mes_desde)
                    OR (YEAR(fecha) = $anio_hasta AND MONTH(fecha) <= $mes_hasta)
                    OR (YEAR(fecha) > $anio_desde AND YEAR(fecha) < $anio_hasta)
                        GROUP BY YEAR(fecha), MONTH(fecha), mes_anio
                    ) AS derived_table
                    ORDER BY fecha_orden ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reportePreparadoOperador(int $id_operador)
    {
        $this->id_operador = $id_operador;
        $sql = "SELECT cant_expedientes, cajas_totales, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        operador
        FROM (
            SELECT SUM(cant_expediente) AS cant_expedientes, SUM(cant_cajas) AS cajas_totales, 
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    operador
            FROM v_preparado 
            WHERE id_operador = $id_operador
            GROUP BY YEAR(fecha), MONTH(fecha), operador 
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    
}
