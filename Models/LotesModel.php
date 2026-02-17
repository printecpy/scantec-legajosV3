<?php
class LotesModel extends Mysql{
    protected $id_registro, $estado;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectLote()
    {
      //  $sql = "SELECT a.id, e.id, e.editorial, m.id, m.materia, l.id, l.titulo, l.cantidad, l.id_editorial, l.id_materia,
      // l.descripcion, l.imagen, l.estado FROM autor a INNER JOIN editorial e
      // INNER JOIN materia m INNER JOIN libro l ON a.id = l.id_autor AND e.id = l.id_editorial WHERE m.id = l.id_materia";
        $sql = "SELECT id_registro, inicio_lote, fin_lote, cant_expediente, fecha_recibido, 
        fecha_entregado, estado, total_paginas FROM lote order by id_registro asc";
        $res = $this->select_all($sql);
        return $res;
    }
    /* public function selectMateria()
    {
        $sql = "SELECT * FROM materia";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectEditorial()
    {
        $sql = "SELECT * FROM editorial";
        $res = $this->select_all($sql);
        return $res;
    }
    public function selectAutor()
    {
        $sql = "SELECT * FROM autor";
        $res = $this->select_all($sql);
        return $res;
    } */
    public function insertarLote(String $inicio_lote, string $fin_lote, int $cant_expediente, string $fecha_recibido)
    {
        $this->inicio_lote = $inicio_lote;
        $this->fin_lote = $fin_lote;
        $this->cant_expediente = $cant_expediente;
        $this->fecha_recibido = $fecha_recibido;
        /* $this->anio_edicion = $anio_edicion;
        $this->materia = $materia;
        $this->num_pagina = $num_pagina;
        $this->descripcion = $descripcion;
        $this->imgName = $imgName; */
        $query = "INSERT INTO lote(inicio_lote, fin_lote, cant_expediente, fecha_recibido, estado)
         VALUES (?,?,?,?,'EN PROCESO')";
        $data = array($this->inicio_lote, $this->fin_lote, $this->cant_expediente, $this->fecha_recibido);
        $this->insert($query, $data);
        return true;
    }
    public function editLote(int $id_registro)
    {
        $sql = "SELECT * FROM lote WHERE id_registro = $id_registro";
        $res = $this->select($sql);
        return $res;
    }

    public function actualizarLote(string $inicio_lote, string $fin_lote, string $fecha_entregado, int $cant_expediente, 
    int $total_paginas,  int $id_registro)
    {
        $this->inicio_lote = $inicio_lote;
        $this->fin_lote = $fin_lote;
        $this->fecha_entregado = $fecha_entregado;
        $this->cant_expediente = $cant_expediente;
        $this->total_paginas = $total_paginas;
        $this->id_registro = $id_registro;
        $query = "UPDATE lote SET inicio_lote=?, fin_lote=?, fecha_entregado=?, cant_expediente=?, total_paginas=?, estado='FINALIZADO' WHERE id_registro = ?";
        $data = array($this->inicio_lote, $this->fin_lote, $this->fecha_entregado, $this->cant_expediente, $this->total_paginas, $this->id_registro);
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
    public function estadoLote(string $estado,int $id_registro)
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

    public function selectExpCant(int $id_registro)
    {
        $sql = "SELECT COUNT(*) AS cant_expediente FROM detalle_proceso WHERE id_registro = $id_registro";
        $res = $this->select($sql);
        return $res;
    }

    public function selectCantPag(int $id_registro)
    {
        $sql = "SELECT SUM(total_pag) AS total_paginas FROM detalle_proceso WHERE id_registro = $id_registro";
        $res = $this->select($sql);
        return $res;
    }

    public function reporteLotesInicio(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM lote WHERE inicio_lote BETWEEN '$desde' AND '$hasta' order by inicio_lote asc";
        $res = $this->select_all($sql);
        return $res;
    }

    public function reporteLotesFechaRecib(string $desde, string $hasta)
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM lote WHERE fecha_recibido BETWEEN '$desde' AND '$hasta' order by fecha_recibido asc";
        $res = $this->select_all($sql);
        return $res;
    }

}
