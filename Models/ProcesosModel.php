<?php
class ProcesosModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectProceso()
    {
      //  $sql = "SELECT a.id, e.id, e.editorial, m.id, m.materia, l.id, l.titulo, l.cantidad, l.id_editorial, l.id_materia,
      // l.descripcion, l.imagen, l.estado FROM autor a INNER JOIN editorial e
      // INNER JOIN materia m INNER JOIN libro l ON a.id = l.id_autor AND e.id = l.id_editorial WHERE m.id = l.id_materia";
        /* $sql = "SELECT a.id_proceso, a.id_registro, b.fecha_recibido, b.fecha_entregado, b.cant_expediente, a.fecha_proceso, a.desde, a.hasta,
        a.nro_caja, a.id, c.nombre, a.id_tipo_proceso, d.tipo_proceso, a.observacion FROM proceso a INNER JOIN
        lote b INNER JOIN usuarios c INNER JOIN tipo_proceso d ON c.id=a.id AND d.id_tipo_proceso=a.id_tipo_proceso 
        WHERE a.id_registro=b.id_registro order by a.id_proceso ASC"; */
        $sql = "SELECT * FROM v_procesos;";
        $res = $this->select_all($sql);
        return $res;
    }
     public function selectLote()
    {
        $sql = "SELECT * FROM lote";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectUsuario()
    {
        $sql = "SELECT * FROM usuarios";
        $res = $this->select_all($sql);
        return $res;
    }
    
    public function selectTipo()
    {
        $sql = "SELECT * FROM tipo_proceso";
        $res = $this->select_all($sql);
        return $res;
    }

    
    public function insertarProceso(int $lote, int $usuario, int $tipo_proceso, string $nro_caja, string $desde, string $hasta,  string $fecha_proceso, string $observacion)
    {
        $this->lote = $lote;
        $this->usuario = $usuario;
        $this->tipo_proceso = $tipo_proceso;
        $this->nro_caja = $nro_caja;       
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->fecha_proceso = $fecha_proceso;
        $this->observacion = $observacion;
        /* $this->anio_edicion = $anio_edicion;
        $this->materia = $materia;
        $this->num_pagina = $num_pagina;
        $this->descripcion = $descripcion;
        $this->imgName = $imgName; */
        $query = "INSERT INTO proceso(id_registro, id, id_tipo_proceso, nro_caja, desde, hasta, fecha_proceso, observacion)
         VALUES (?,?,?,?,?,?,?,?)";
        $data = array($this->lote,$this->usuario, $this->tipo_proceso, $this->nro_caja, $this->desde, $this->hasta, $this->fecha_proceso, $this->observacion);
        $this->insert($query, $data);
        return true;
    }
    public function editProceso(int $id_proceso)
    {
        $sql = "SELECT * FROM proceso WHERE id_proceso = $id_proceso";
        $res = $this->select($sql);
        return $res;
    }

    public function histoProceso(int $id_proceso)
    {
        $sql = "SELECT * FROM historial_proceso WHERE id_proceso = $id_proceso";
        $res = $this->select($sql);
        return $res;
        }

    public function detalleProceso(int $id_proceso)
    {
        $sql = "SELECT * FROM detalle_proceso WHERE id_proceso = $id_proceso";
        $res = $this->select($sql);
        return $res;
    }
    
    //SELECT `id_expediente`, `id_proceso`, `paciente`, `documento`, `ruta_original`, `estado`, `paginas` FROM `expediente`
   
    public function actualizarTotal(int $id_proceso)
    {
        $this->id_proceso = $id_proceso;
        $id_proc = $id_proceso;
        $sql = "SELECT SUM(total_pag) FROM `detalle_proceso` WHERE id_proceso= '%$id_proc%'";
        $res = $this->select_all($sql);
        $query = "UPDATE proceso SET estado='FINALIZADO', totalpag ='%$res%') WHERE id_proceso = ?";
        $data = array($this->id_proceso);
        $this->update($query, $data);
        return true;
    }
    public function actualizarProceso(string $desde, string $hasta, int $id_tipo_proceso, string $fecha_proceso, string $nro_caja, 
    string $observacion, int $id, int $id_proceso)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->id_tipo_proceso = $id_tipo_proceso;
        $this->fecha_proceso = $fecha_proceso;
        $this->nro_caja = $nro_caja;
        $this->observacion = $observacion;
        $this->id = $id;
        $this->id_proceso = $id_proceso;
        $query = "UPDATE proceso SET desde=?, hasta=?, id_tipo_proceso=?, fecha_proceso=?, nro_caja=?, observacion=?, id=? WHERE id_proceso = ?";
        $data = array($this->desde, $this->hasta, $this->id_tipo_proceso, $this->fecha_proceso, $this->nro_caja, $this->observacion, $this->id, $this->id_proceso);
        $this->update($query, $data);
        return true;
    }
   /*  public function estadoLote(int $id_registro)
    {
       // $this->estado = $estado;
        $this->id_registro = $id_registro;
        $query = "UPDATE lote SET estado = 'INACTIVO' WHERE id_registro = ?";
        $data = array($this->id_registro);
        $this->update($query, $data);
        return true;
    } */
    public function estadoProceso(string $estado,int $id_proceso)
    {
        $this->estado = $estado;
        $this->id_registro = $id_registro;
        $query = "UPDATE lote SET estado = ? WHERE id_registro = ?";
        $data = array($this->estado, $this->id_registro);
        $this->update($query, $data);
        return true;
    }
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

    public function reporteProcesoCajas(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_procesos WHERE nro_caja BETWEEN '$desde' AND '$hasta' order by nro_caja asc";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteProcesoFechas(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM v_procesos WHERE fecha_proceso BETWEEN '$desde' AND '$hasta' order by fecha_proceso asc";
        $res = $this->select_all($sql);
        return $res;
    }

}