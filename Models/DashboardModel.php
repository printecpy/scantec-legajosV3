<?php
class DashboardModel extends Mysql{
    public function __construct()
    {
        parent::__construct();
    }
    public function selectLotesProceso()
    {
        $sql = "SELECT COUNT(*) AS lote_procesos FROM lote WHERE estado = 'EN PROCESO';";
        $res = $this->select($sql);
        return $res;
    }

    public function selectCantProceso()
    {
        $sql = "SELECT COUNT(*) AS cant_procesos FROM proceso;";
        $res = $this->select($sql);
        return $res;
    }
    
    public function selectLotesFinalizado()
    {
        $sql = "SELECT COUNT(*) AS lote_finalizados FROM lote WHERE estado = 'FINALIZADO';";
        $res = $this->select($sql);
        return $res;
    }
    public function selectEspecialidad()
    {
        $sql = "SELECT * FROM especialidad;";
        $res = $this->select_all($sql);
        return $res;
    }
    
    public function selectarchivosTipoDoc()
    {
        $sql = "SELECT v.id_tipoDoc, MIN(v.nombre_tipoDoc) AS nombre_tipoDoc, SUM(v.paginas) AS total_paginas, COUNT(*) AS cantidad_archivos 
                FROM v_expedientes v WHERE v.estado = 'Activo' GROUP BY v.id_tipoDoc ORDER BY total_paginas DESC, cantidad_archivos DESC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectCantArchivosDoc()
    {
        $sql = "SELECT COALESCE(fecha_indexado, 'Sin Fecha') AS fecha_indexado, COUNT(*) AS total_archivos,
                         GROUP_CONCAT(CONCAT(nombre_tipoDoc, ': ', doc_count) SEPARATOR '<br>') AS detalle
                  FROM (SELECT fecha_indexado, nombre_tipoDoc, COUNT(*) AS doc_count FROM v_expedientes
                      WHERE fecha_indexado >= CURDATE() - INTERVAL 15 DAY OR fecha_indexado IS NULL
                      GROUP BY fecha_indexado, nombre_tipoDoc) AS subquerys GROUP BY fecha_indexado;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectExpedienteCantidad()
    {
        $sql = "SELECT COUNT(*) AS cant_expediente FROM expediente where estado = 'Activo';";
        $res = $this->select($sql);
        return $res;
    }


    public function selectPaginasProcesada()
    {
        $sql = "SELECT SUM(paginas) as paginas_procesadas FROM expediente where estado = 'Activo';";
        $res = $this->select($sql);
       // echo $res;
        return $res;
    }
    public function selectPorcAvanza()
    {
        $sql = "SELECT ((SUM(a.total_pag)) / ((select b.total_pag) * 100)) as porc_avanzar 
        FROM detalle_proceso a, configuracion b group by b.total_pag";
        $res = $this->select($sql);
       // echo $res;
        return $res;
    }

    public function selectCantFaltante()
    {
        $sql = "SELECT (SELECT total_pag FROM configuracion) - (SUM(a.total_pag))  as cant_faltantes 
        FROM detalle_proceso a ";
        $res = $this->select($sql);
       // echo $res;
        return $res;
    }
    public function selectExpedLote(){
        $sql = "SELECT id_registro,  cant_expediente, total_paginas FROM lote ORDER BY id_registro ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectLogs_uman(){
        $sql = "SELECT idlog_umango, id_proceso_umango, id_lote, fuente_captura,
         archivo_origen, orden_documento, paginas_exportadas, fecha_inicio, fecha_finalizacion, 
         creador, usuario, trabajo, estado, nombre_host, ip_host FROM logs_umango  WHERE STR_TO_DATE(fecha_finalizacion, '%Y-%m-%d') = DATE_SUB(CURDATE(),
          INTERVAL 1 DAY) ORDER BY fecha_inicio ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectUsuariosActivos()
    {
        $sql = "SELECT COUNT(*) AS cant_usuarios, estado FROM visitas WHERE estado='ACTIVO' ";
        $res = $this->select($sql);
        return $res;
    }

    public function selectCant_porIndice()
    {
        $sql = "SELECT COUNT(DISTINCT indice_01) AS total_porIndice
        FROM v_expedientes;";
        $res = $this->select($sql);
        return $res;
    }

    public function selectExpConsultadosDia()
    {
        $sql = "SELECT COUNT(DISTINCT id_expediente) AS exp_consultados, COUNT(*) AS cantidad_documentos
        FROM document_views 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 1 DAY);";
        $res = $this->select($sql);
        return $res;
    }

    public function getChartData() {
        $sql = "SELECT fecha_indexado, COUNT(*) AS cantidad FROM expediente 
        WHERE fecha_indexado >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY fecha_indexado";
        $res = $this->select($sql);
        $labels = [];
        $data = [];
        while ($row = $res->fetch())
        {
            $labels[] = $row['fecha_indexado'];
            $data[] = $row['cantidad'];
        }
        return [$labels, $data];
    }

       public function selectPrestamoCantidad()
    {
        $sql = "SELECT * FROM prestamo WHERE estado = 1";
        $res = $this->select($sql);
        return $res;
    }
 
    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }
    public function selectPrestamoDebe()
    {
        $sql = "SELECT e.id, e.nombre, l.id, l.titulo, p.id, p.id_estudiante, p.id_libro, p.fecha_prestamo, 
        p.fecha_devolucion, p.cantidad, p.observacion, p.estado FROM estudiante e INNER JOIN libro l
         INNER JOIN prestamo p ON p.id_estudiante = e.id WHERE p.id_libro = l.id AND p.estado = 1";
        $res = $this->select_all($sql);
        return $res;
    }
    
}