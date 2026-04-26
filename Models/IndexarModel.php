<?php
class IndexarModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectIndexado()
    {
        $sql = "SELECT a.id_index, a.fecha, a.pag_index, a.exp_index, a.id_est, d.nombre_pc AS nombre_pc, a.id, b.nombre, a.id_operador, concat_ws(' ',c.nombre, c.apellido) as operador,
         a.estado FROM op_indexado a, usuarios b, op_operador c, op_est_trabajo d WHERE a.id=b.id AND a.id_operador=c.id_operador 
         AND a.id_est=d.id_est ORDER BY a.id_index ASC;";
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


    public function insertarIndexado(String $fecha, int $pag_index, int $exp_index, int $id_est,
    int $id, int $id_operador)
    {
        $this->fecha = $fecha;
        $this->pag_index = $pag_index;
        $this->exp_index = $exp_index;
        $this->id_est = $id_est;
        $this->id = $id;
        $this->id_operador = $id_operador;
        $query = "INSERT INTO op_indexado(fecha, pag_index, exp_index, id_est, id, id_operador, estado) VALUES (?,?,?,?,?,?,'ACTIVO')";
        $data = array($this->fecha, $this->pag_index, $this->exp_index, $this->id_est, $this->id, $this->id_operador);
        $this->insert($query, $data);
        return true;
    }
    public function editIndexar(int $id_index)
    {
        $sql = "SELECT * FROM op_indexado WHERE id_index = ?";
        $res = $this->select($sql, [$id_index]);
        return $res;
    }

    public function actualizarIndexado(String $fecha, int $pag_index, int $exp_index, int $id_est, int $id_operador, int $id_index)
    {
        $this->fecha = $fecha;
        $this->pag_index = $pag_index;
        $this->exp_index = $exp_index;
        $this->id_est = $id_est;
        $this->id_operador = $id_operador;
        $this->id_index = $id_index;
        $query = "UPDATE op_indexado SET fecha = ?, pag_index = ?, exp_index = ?, id_est = ?, id_operador = ? WHERE id_index = ?";
        $data = array($this->fecha, $this->pag_index, $this->exp_index, $this->id_est, $this->id_operador, $this->id_index);
        $this->update($query, $data);
        return true;
    }
   
    public function estadoIndexado(string $estado,int $id_index)
    {
        $this->estado = $estado;
        $this->id_index = $id_index;
        $query = "UPDATE op_indexado SET estado = ? WHERE id_index = ?";
        $data = array($this->estado, $this->id_index);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    
    public function reporteIndexFecha(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_indexado WHERE fecha BETWEEN ? AND ? order by id_index asc";
        $res = $this->select_all($sql, [$desde, $hasta]);
        return $res;
    }

    public function reporteIndextotal(string $mes_desde, string $anio_desde, string $mes_hasta, string $anio_hasta)
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
        // Construir la consulta SQL
        $sql = "SELECT pag_indexadas, 
            exp_indexadas, 
            mes_anio
            FROM (
                SELECT SUM(pag_index) AS pag_indexadas, SUM(exp_index) AS exp_indexadas,
                        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS mes_anio,
                                MIN(fecha) AS fecha_orden
                FROM v_indexado
                WHERE (YEAR(fecha) = ? AND MONTH(fecha) >= ?)
                    OR (YEAR(fecha) = ? AND MONTH(fecha) <= ?)
                    OR (YEAR(fecha) > ? AND YEAR(fecha) < ?)
                GROUP BY YEAR(fecha), MONTH(fecha), mes_anio
            ) AS derived_table
            ORDER BY fecha_orden ASC;";
        // Ejecutar la consulta y devolver el resultado
        $res = $this->select_all($sql, [$anio_desde, $mes_desde, $anio_hasta, $mes_hasta, $anio_desde, $anio_hasta]);
        return $res;
    }

    public function reporteIndexOperador(int $id_operador)
    {
        $this->id_operador = $id_operador;
        $sql = "SELECT pag_indexadas, exp_indexadas, 
        CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
        operador
        FROM (
            SELECT SUM(pag_index) AS pag_indexadas, SUM(exp_index) AS exp_indexadas, 
                    MIN(fecha) AS fecha,
                    (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                    operador
            FROM v_indexado 
            WHERE id_operador = ?
            GROUP BY YEAR(fecha), MONTH(fecha), operador 
        ) AS derived_table 
        ORDER BY month_number ASC;";
        $res = $this->select_all($sql, [$id_operador]);
        return $res;
    }

    public function reporteIndexpc(int $id_est)
    {
        $this->id_est = $id_est;
        $sql = "SELECT pag_indexadas, 
                exp_indexadas, 
                CONCAT(YEAR(fecha), '-', MONTHNAME(fecha)) AS fecha, 
                id_est, nombre_pc
                FROM (
                    SELECT SUM(pag_index) AS pag_indexadas, 
                            SUM(exp_index) AS exp_indexadas,
                            MIN(fecha) AS fecha,
                            (YEAR(MIN(fecha)) * 100 + MONTH(MIN(fecha))) AS month_number, 
                            id_est, nombre_pc
                    FROM v_indexado 
                    WHERE id_est = ?
                    GROUP BY YEAR(fecha), MONTH(fecha), id_est, nombre_pc
                ) AS derived_table 
                ORDER BY month_number ASC;";
        $res = $this->select_all($sql, [$id_est]);
        return $res;
    }

   

/*   
metodo que sirve para saber cantidad de indexado de un usuario seleccionado y una fecha cualquiera partir de los logs de umango
$fecha_deseada = '2024-05-20'; // o $fecha_actual para la fecha de hoy

// Crear la conexión a la base de datos
$mysqli = new mysqli("localhost", "usuario", "contraseña", "nombre_de_la_bd");

// Verificar la conexión
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Preparar la consulta SQL
$sql = "
    SELECT SUM(paginas_exportadas) AS paginas_exportadas
    FROM logs_umango
    WHERE usuario = 'Umango_Admin_01'
      AND fecha_finalizacion >= ?
      AND fecha_finalizacion < ?";

// Preparar la declaración
$stmt = $mysqli->prepare($sql);

// Calcular el rango de fechas
$fecha_inicio = $fecha_deseada . ' 00:00:00';
$fecha_fin = date('Y-m-d H:i:s', strtotime($fecha_deseada . ' +1 day'));

// Enlazar los parámetros
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);

// Ejecutar la declaración
$stmt->execute();

// Obtener el resultado
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Mostrar el resultado
echo "Páginas exportadas: " . $row['paginas_exportadas'];

// Cerrar la declaración y la conexión
$stmt->close();
$mysqli->close(); */

    
}
