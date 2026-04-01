<?php
class DashboardModel extends Mysql
{
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

    private function existeColumna(string $tabla, string $columna): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $result = $this->select($sql, [$tabla, $columna]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    private function construirFiltroLegajos(string $alias = 'l', array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false): array
    {
        $sql = '';
        $params = [];

        $tiposPermitidos = array_values(array_unique(array_filter(array_map('intval', $tiposPermitidos))));
        if (!empty($tiposPermitidos)) {
            $placeholders = implode(',', array_fill(0, count($tiposPermitidos), '?'));
            $sql .= " AND {$alias}.id_tipo_legajo IN ($placeholders)";
            $params = array_merge($params, $tiposPermitidos);
        }

        if ($soloPropios && $idUsuario > 0) {
            if ($this->existeColumna('cfg_legajo', 'id_usuario_armado')) {
                $sql .= " AND ({$alias}.id_usuario = ? OR {$alias}.id_usuario_armado = ?)";
                $params[] = $idUsuario;
                $params[] = $idUsuario;
            } else {
                $sql .= " AND {$alias}.id_usuario = ?";
                $params[] = $idUsuario;
            }
        }

        return ['sql' => $sql, 'params' => $params];
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
        // Obtener legajos agrupados por fecha y usuario (últimos 15 días)
        $sql = "SELECT DATE_FORMAT(cl.fecha_creacion, '%Y-%m-%d') AS fecha_indexado,
                       u.nombre AS nombre_usuario,
                       COUNT(*) AS total_archivos
                FROM cfg_legajo cl
                LEFT JOIN usuarios u ON u.id = cl.id_usuario
                WHERE cl.fecha_creacion >= CURDATE() - INTERVAL 15 DAY
                GROUP BY DATE_FORMAT(cl.fecha_creacion, '%Y-%m-%d'), u.nombre
                ORDER BY fecha_indexado ASC, u.nombre ASC";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectExpedienteCantidad()
    {
        $sql = "SELECT COUNT(*) AS cant_expediente FROM expediente where estado = 'Activo';";
        $res = $this->select($sql);
        return $res;
    }

    public function selectLegajosCantidad(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos FROM cfg_legajo WHERE 1=1" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosProceso(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_proceso
                FROM cfg_legajo
                WHERE estado IN ('borrador', 'activo')" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosCompletados(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_completados
                FROM cfg_legajo
                WHERE estado = 'finalizado'" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosRechazados(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_rechazados
                FROM cfg_legajo
                WHERE estado = 'verificacion_rechazada'" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosVerificados(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_verificados
                FROM cfg_legajo
                WHERE estado = 'verificado'" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosCerrados(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_cerrados
                FROM cfg_legajo
                WHERE estado IN ('cerrado', 'aprobado')" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosActivos(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('cfg_legajo', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_legajos_activos
                FROM cfg_legajo
                WHERE estado = 'activo'" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosPorTipo(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT
                    COALESCE(tl.nombre, 'Sin tipo') AS nombre_tipo_legajo,
                    COUNT(l.id_legajo) AS cantidad_legajos
                FROM cfg_legajo l
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                WHERE l.estado = 'finalizado'" . $scope['sql'] . "
                GROUP BY l.id_tipo_legajo, tl.nombre
                ORDER BY cantidad_legajos DESC, nombre_tipo_legajo ASC;";
        $res = $this->select_all($sql, $scope['params']);
        return $res;
    }

    public function selectLegajosPorUsuario(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT
                    COALESCE(u.nombre, 'Sin usuario') AS nombre_usuario,
                    COUNT(l.id_legajo) AS cantidad_legajos
                FROM cfg_legajo l
                LEFT JOIN usuarios u ON u.id = l.id_usuario
                WHERE l.estado = 'verificado'" . $scope['sql'] . "
                GROUP BY l.id_usuario, u.nombre
                ORDER BY cantidad_legajos DESC, nombre_usuario ASC;";
        $res = $this->select_all($sql, $scope['params']);
        return $res;
    }

    public function selectProductividadSolicitudesPorUsuario(int $periodoCantidad = 8, string $periodoUnidad = 'WEEK', array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $periodoCantidad = max(1, $periodoCantidad);
        $periodoUnidad = strtoupper($periodoUnidad);
        if (!in_array($periodoUnidad, ['DAY', 'WEEK'], true)) {
            $periodoUnidad = 'WEEK';
        }
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT
                    COALESCE(u.nombre, 'Sin usuario') AS nombre_usuario,
                    SUM(CASE WHEN l.estado IN ('borrador', 'activo') THEN 1 ELSE 0 END) AS cantidad_proceso,
                    SUM(CASE WHEN l.estado = 'finalizado' THEN 1 ELSE 0 END) AS cantidad_completado,
                    SUM(CASE WHEN l.estado = 'verificado' THEN 1 ELSE 0 END) AS cantidad_verificado
                FROM cfg_legajo l
                INNER JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                LEFT JOIN usuarios u ON u.id = l.id_usuario
                WHERE COALESCE(tl.requiere_nro_solicitud, 0) = 1
                  AND DATE(l.fecha_creacion) >= DATE_SUB(CURDATE(), INTERVAL ? " . $periodoUnidad . ")" . $scope['sql'] . "
                GROUP BY l.id_usuario, u.nombre
                HAVING (cantidad_proceso + cantidad_completado + cantidad_verificado) > 0
                ORDER BY (cantidad_proceso + cantidad_completado + cantidad_verificado) DESC, nombre_usuario ASC;";
        $res = $this->select_all($sql, array_merge([$periodoCantidad], $scope['params']));
        return is_array($res) ? $res : [];
    }

    public function selectLegajosArmadosPorFechaUsuario(int $periodoCantidad = 1, string $periodoUnidad = 'WEEK', array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $periodoCantidad = max(1, $periodoCantidad);
        $periodoUnidad = strtoupper($periodoUnidad);
        if (!in_array($periodoUnidad, ['DAY', 'WEEK'], true)) {
            $periodoUnidad = 'WEEK';
        }

        // Si la columna id_usuario_armado existe, filtramos por usuarios armadores.
        $fieldUsuarioArmado = $this->existeColumna('cfg_legajo', 'id_usuario_armado')
            ? 'l.id_usuario_armado' : 'l.id_usuario';
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);

        $sql = "SELECT
                    DATE_FORMAT(l.fecha_creacion, '%Y-%m-%d') AS fecha_indexado,
                    COALESCE(ua.nombre, 'Sin usuario') AS nombre_usuario,
                    COUNT(*) AS cantidad_legajos
                FROM cfg_legajo l
                LEFT JOIN usuarios ua ON ua.id = $fieldUsuarioArmado
                WHERE DATE(l.fecha_creacion) >= DATE_SUB(CURDATE(), INTERVAL ? " . $periodoUnidad . ")
                  AND $fieldUsuarioArmado IS NOT NULL
                  AND $fieldUsuarioArmado <> 0
                  AND l.estado IN ('finalizado', 'verificado', 'cerrado', 'aprobado')" . $scope['sql'] . "
                GROUP BY DATE_FORMAT(l.fecha_creacion, '%Y-%m-%d'), ua.nombre
                ORDER BY fecha_indexado ASC, nombre_usuario ASC";

        $res = $this->select_all($sql, array_merge([$periodoCantidad], $scope['params']));
        return is_array($res) ? $res : [];
    }

    public function insertarPrestamo(int $funcionario, int $expediente, int $especialidad, string $fecha_prestamo, string $fecha_devolucion, string $observacion)
    {
        $query = "INSERT INTO prestamo(id_funcionario, id_expediente, id_especialidad, fecha_prestamo, fecha_devolucion, observacion, estado) VALUES (?, ?, ?, ?, ?, ?, 1)";
        $data = array($funcionario, $expediente, $especialidad, $fecha_prestamo, $fecha_devolucion, $observacion);
        $this->insert($query, $data);
        return true;
    }

    public function estadoPrestamo(int $estado, int $id)
    {
        $query = "UPDATE prestamo SET fecha_devolucion = CURRENT_TIME(), estado = ? WHERE id = ?";
        $data = array($estado, $id);
        $this->update($query, $data);
        return true;
    }

    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

    public function selectPrestamoDebe()
    {
        $sql = "SELECT e.id, e.nombre, l.id, l.titulo, p.id, p.id_estudiante, p.id_libro, p.fecha_prestamo, p.fecha_devolucion, p.cantidad, p.observacion, p.estado FROM estudiante e INNER JOIN libro l INNER JOIN prestamo p ON p.id_estudiante = e.id WHERE p.id_libro = l.id AND p.estado = 1";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectDocumentosLegajoVigentes(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_documentos_vigentes
                FROM cfg_legajo_documento ld
                INNER JOIN cfg_legajo l ON l.id_legajo = ld.id_legajo
                WHERE ld.estado = 'cargado'" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }

    public function selectDocumentosLegajoPorVencer(int $diasMargen = 30, array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_documentos_por_vencer
                FROM cfg_legajo_documento ld
                INNER JOIN cfg_legajo l ON l.id_legajo = ld.id_legajo
                WHERE ld.estado = 'cargado'
                  AND ld.fecha_vencimiento IS NOT NULL
                  AND DATE(ld.fecha_vencimiento) >= CURDATE()
                  AND DATE(ld.fecha_vencimiento) <= DATE_ADD(CURDATE(), INTERVAL ? DAY)" . $scope['sql'];
        $res = $this->select($sql, array_merge([$diasMargen], $scope['params']));
        return $res;
    }

    public function selectDocumentosLegajoCriticos(array $tiposPermitidos = [], int $idUsuario = 0, bool $soloPropios = false)
    {
        $scope = $this->construirFiltroLegajos('l', $tiposPermitidos, $idUsuario, $soloPropios);
        $sql = "SELECT COUNT(*) AS cant_documentos_criticos
                FROM cfg_legajo_documento ld
                INNER JOIN cfg_legajo l ON l.id_legajo = ld.id_legajo
                WHERE ld.estado IN ('pendiente', 'vencido')" . $scope['sql'];
        $res = $this->select($sql, $scope['params']);
        return $res;
    }


    public function selectPaginasProcesada()
    {
        $sql = "SELECT IFNULL(SUM(paginas), 0) as paginas_procesadas FROM expediente WHERE estado = 'Activo';";
        $res = $this->select($sql);
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
    public function selectExpedLote()
    {
        $sql = "SELECT id_registro,  cant_expediente, total_paginas FROM lote ORDER BY id_registro ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectLogs_uman()
    {
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

    public function getChartData()
    {
        $sql = "SELECT fecha_indexado, COUNT(*) AS cantidad FROM expediente 
        WHERE fecha_indexado >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) GROUP BY fecha_indexado";
        $res = $this->select_all($sql);
        $labels = [];
        $data = [];
        foreach ($res as $row) {
            $labels[] = $row['fecha_indexado'];
            $data[] = $row['cantidad'];
        }
        return [$labels, $data];
    }

}
