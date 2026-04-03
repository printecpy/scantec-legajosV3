<?php
class ReagrupadoModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectReagrupado()
    {
        $sql = "SELECT a.id_reagrup, a.fecha, a.solicitado, a.cant_cajas, a.observaciones, a.id_operador, concat_ws(' ',c.nombre, c.apellido) as operador,
         a.id, b.nombre, a.estado FROM op_reagrupado a, usuarios b, op_operador c WHERE a.id=b.id 
         AND a.id_operador=c.id_operador order by a.fecha ASC;";
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

    public function insertarReagrupado(String $fecha, int $solicitado, string $cant_cajas, string $observaciones, int $id, int $id_operador)
    {
        $this->fecha = $fecha;
        $this->solicitado = $solicitado;
        $this->cant_cajas = $cant_cajas;
        $this->observaciones = $observaciones;
        $this->id = $id;
        $this->id_operador = $id_operador;
        $query = "INSERT INTO op_reagrupado (fecha, solicitado, cant_cajas, observaciones, id, id_operador, estado) VALUES
         (?, ?, ?, ?, ?, ?, 'ACTIVO')";
        $data = array($this->fecha, $this->solicitado, $this->cant_cajas, $this->observaciones, $this->id, $this->id_operador);
        $this->insert($query, $data);
        return true;
    }

    public function editReagrupado(int $id_reagrup)
    {
        $sql = "SELECT * FROM op_reagrupado WHERE id_reagrup = $id_reagrup";
        $res = $this->select($sql);
        return $res;
    }

    public function actualizarReagrupado(String $fecha, int $solicitado, string $cant_cajas, string $observaciones, int $id_operador, int $id_reagrup)
    {
        $this->fecha = $fecha;
        $this->solicitado = $solicitado;
        $this->cant_cajas = $cant_cajas;
        $this->observaciones = $observaciones;
        $this->id_operador = $id_operador;
        $this->id_reagrup = $id_reagrup;
        $query = "UPDATE op_reagrupado SET fecha=?, solicitado=?, cant_cajas=?, observaciones=?, id_operador=? WHERE id_reagrup = ?";
        $data = array($this->fecha, $this->solicitado, $this->cant_cajas, $this->observaciones, $this->id_operador, $this->id_reagrup);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoReagrupado(string $estado, int $id_reagrup)
    {
        $this->estado = $estado;
        $this->id_reagrup = $id_reagrup;
        $query = "UPDATE op_reagrupado SET estado = ? WHERE id_reagrup = ?";
        $data = array($this->estado, $this->id_reagrup);
        $this->update($query, $data);
        return true;
    }

    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function reporteReagrupadoFecha(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_reagrupado WHERE fecha BETWEEN '$desde' AND '$hasta'  order by id_reagrup asc;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteReagrupadototal(string $mes_desde, string $anio_desde, string $mes_hasta, string $anio_hasta)
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
        $sql = "SELECT solicitados, 
                    cajas_totales, 
                    mes_anio
                    FROM (
                        SELECT SUM(solicitado) AS solicitados, SUM(cant_cajas) AS cajas_totales,
                                CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS mes_anio,
                                MIN(fecha) AS fecha_orden
                        FROM v_reagrupado
                        WHERE (YEAR(fecha) = $anio_desde AND MONTH(fecha) >= $mes_desde)
                    OR (YEAR(fecha) = $anio_hasta AND MONTH(fecha) <= $mes_hasta)
                    OR (YEAR(fecha) > $anio_desde AND YEAR(fecha) < $anio_hasta)
                        GROUP BY YEAR(fecha), MONTH(fecha), mes_anio
                    ) AS derived_table
                    ORDER BY fecha_orden ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteReagrupadoOperador(int $id_operador)
    {
        $this->id_operador = $id_operador;
        $sql = "SELECT solicitados, cajas_totales, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        operador
        FROM (
            SELECT SUM(solicitado) AS solicitados, SUM(cant_cajas) AS cajas_totales, 
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    operador
            FROM v_reagrupado 
            WHERE id_operador = $id_operador
            GROUP BY YEAR(fecha), MONTH(fecha), operador 
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    
}
