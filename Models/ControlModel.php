<?php
class ControlModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectControl()
    {
        $sql = "SELECT a.id_cont, a.fecha, a.pag_control, a.exp_control, a.solicitado, a.exp_reescaneo, a.id_est, d.nombre_pc, a.id, b.nombre, 
        a.id_operador, concat_ws(' ',c.nombre, c.apellido) as operador, a.estado FROM op_control a, usuarios b, op_operador c, op_est_trabajo d 
        WHERE a.id=b.id AND a.id_operador=c.id_operador and a.id_est=d.id_est ORDER BY a.id_cont ASC;";
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

    public function insertarControl(String $fecha, int $pag_control, int $exp_control, int $solicitado, int $exp_reescaneo, int $id_est,
    int $id, int $id_operador)
    {
        $this->fecha = $fecha;
        $this->pag_control = $pag_control;
        $this->exp_control = $exp_control;
        $this->solicitado = $solicitado;
        $this->exp_reescaneo = $exp_reescaneo;
        $this->id_est = $id_est;
        $this->id = $id;
        $this->id_operador = $id_operador;
        $query = "INSERT INTO op_control(fecha, pag_control, exp_control, solicitado, exp_reescaneo, id_est, 
        id, id_operador, estado) VALUES (?,?,?,?,?,?,?,?,'ACTIVO')";
        $data = array($this->fecha, $this->pag_control, $this->exp_control, $this->solicitado, $this->exp_reescaneo, $this->id_est,
        $this->id, $this->id_operador);
        $this->insert($query, $data);
        return true;
    }
    public function editControl(int $id_cont)
    {
        $sql = "SELECT * FROM op_control WHERE id_cont = $id_cont";
        $res = $this->select($sql);
        return $res;
    }

    public function detControl(int $id_cont)
    {
        $sql = "SELECT * FROM det_control WHERE id_cont = $id_cont";
        $res = $this->select_all($sql);
        return $res;
    }

    public function actualizarControl(String $fecha, int $pag_control, int $exp_control, int $solicitado, int $exp_reescaneo, int $id_est,
    int $id_operador,  int $id_cont)
    {
        $this->fecha = $fecha;
        $this->pag_control = $pag_control;
        $this->exp_control = $exp_control;
        $this->solicitado = $solicitado;
        $this->exp_reescaneo = $exp_reescaneo;
        $this->id_est = $id_est;
        $this->id_operador = $id_operador;
        $this->id_cont = $id_cont;
        $query = "UPDATE op_control SET fecha = ?, pag_control = ?, exp_control = ?, solicitado = ?, exp_reescaneo = ?, id_est = ?, 
        id_operador = ? WHERE id_cont = ?";
        $data = array($this->fecha, $this->pag_control, $this->exp_control, $this->solicitado, $this->exp_reescaneo, $this->id_est,
        $this->id_operador, $this->id_cont);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoControl(string $estado,int $id_cont)
    {
        $this->estado = $estado;
        $this->id_cont = $id_cont;
        $query = "UPDATE op_control SET estado = ? WHERE id_cont = ?";
        $data = array($this->estado, $this->id_cont);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

    public function reporteControlFecha(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_control WHERE fecha BETWEEN '$desde' AND '$hasta' order by id_cont asc;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteControltotal(string $mes_desde, string $anio_desde, string $mes_hasta, string $anio_hasta)
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
        $sql = "SELECT pag_controladas, 
            exp_controladas, 
            mes_anio,
            solicitados,
            reescaneos
            FROM (
                SELECT SUM(pag_control) AS pag_controladas, 
                        SUM(exp_control) AS exp_controladas,
                        SUM(solicitado) as solicitados, 
                                SUM(exp_reescaneo) as reescaneos,
                        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS mes_anio,
                                MIN(fecha) AS fecha_orden
                FROM v_control
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

    public function reporteControlOperador(int $id_operador)
    {
        $this->id_operador = $id_operador;
        $sql = "SELECT pag_controladas, 
        exp_controladas, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        operador, solicitados, reescaneos
        FROM (
            SELECT SUM(pag_control) AS pag_controladas, 
                    SUM(exp_control) AS exp_controladas,
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    operador,
                    SUM(solicitado) as solicitados, 
        					SUM(exp_reescaneo) as reescaneos
            FROM v_control 
            WHERE id_operador = $id_operador
            GROUP BY YEAR(fecha), MONTH(fecha), operador 
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteControlpc(int $id_est)
    {
        $this->id_est = $id_est;
        $sql = "SELECT pag_controladas, 
        exp_controladas, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        id_est, nombre_pc, solicitados, reescaneos
        FROM (
            SELECT SUM(pag_control) AS pag_controladas, 
                    SUM(exp_control) AS exp_controladas,
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    id_est, nombre_pc,
                    SUM(solicitado) as solicitados, 
        			SUM(exp_reescaneo) as reescaneos
            FROM v_control 
            WHERE id_est = $id_est
            GROUP BY YEAR(fecha), MONTH(fecha), id_est, nombre_pc
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function insertarDetControl(String $nombre_archivo, int $num_pag, String $fecha_creacion, String $fecha_modificacion, String $ruta_archivo,
                    int $id_cont)
    {
        $this->id_cont = $id_cont;
        $this->nombre_archivo = $nombre_archivo;
        $this->num_pag = $num_pag;
        $this->fecha_creacion = $fecha_creacion;
        $this->fecha_modificacion = $fecha_modificacion;
        $this->ruta_archivo = $ruta_archivo;
        $query = "INSERT INTO det_control (id_cont, nombre_archivo, num_pag, fecha_creacion, fecha_modificacion, ruta_archivo) 
        VALUES (?, ?, ?, ?, ?, ?)";
        $data = array($this->id_cont, $this->nombre_archivo, $this->num_pag, $this->fecha_creacion, $this->fecha_modificacion, $this->ruta_archivo);
        $this->insert($query, $data);
        return true;
    }

    public function selectDetControlPag(int $id_cont)
    {
        $sql = "SELECT sum(num_pag) as pag_control FROM det_control WHERE id_cont = $id_cont;";
        $res = $this->select($sql);
        return $res;
    }

    public function selectDetControlExp(int $id_cont)
    {
        $sql = "SELECT Count(*) as exp_control FROM det_control WHERE id_cont = $id_cont;";
        $res = $this->select($sql);
        return $res;
    }

    
}