<?php
class LogsModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureLegajoPaginasProcesadasColumn();
    }

    private function existeTabla(string $tabla): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $result = $this->select($sql, [$tabla]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    private function existeColumna(string $tabla, string $columna): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $result = $this->select($sql, [$tabla, $columna]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    private function ensureLegajoPaginasProcesadasColumn(): void
    {
        try {
            if ($this->existeTabla('cfg_legajo') && !$this->existeColumna('cfg_legajo', 'cantidad_paginas_procesadas')) {
                $this->update("ALTER TABLE cfg_legajo ADD COLUMN cantidad_paginas_procesadas INT NOT NULL DEFAULT 0 AFTER fecha_cierre", []);
            }
        } catch (Throwable $e) {
            // No interrumpimos la carga de la auditoria.
        }
    }

    private function normalizarLista($result): array
    {
        if (!is_array($result) || empty($result)) {
            return [];
        }

        return isset($result[0]) && is_array($result[0]) ? $result : [$result];
    }

    private function construirWhereReportePaginasLegajos(?string $desde, ?string $hasta, array &$params): string
    {
        $where = " WHERE COALESCE(l.cantidad_paginas_procesadas, 0) > 0";

        if (!empty($desde)) {
            $where .= " AND DATE(l.fecha_creacion) >= ?";
            $params[] = $desde;
        }

        if (!empty($hasta)) {
            $where .= " AND DATE(l.fecha_creacion) <= ?";
            $params[] = $hasta;
        }

        return $where;
    }
    public function selectLogs()
    {
        $sql = "SELECT id_log, fecha,
                REGEXP_REPLACE(executedSQL, 'clave = ''[^'']+''', 'clave = ''*********************''') AS executedSQL,
                REGEXP_REPLACE(reverseSQL, 'clave = ''[^'']+''', 'clave = ''*********************''') AS reverseSQL
            FROM logs ORDER BY id_log DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectViews()
    {
        $sql = "SELECT * 
        FROM document_views 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectSesionFails()
    {
        $sql = "SELECT * 
        FROM intentos_login_fallidos 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectSesions()
    {
        $sql = "SELECT * 
        FROM v_visitas 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ORDER BY id_visita DESC LIMIT 50;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

    public function selectTotalesPaginasLegajos(?string $desde = null, ?string $hasta = null): array
    {
        $params = [];
        $where = $this->construirWhereReportePaginasLegajos($desde, $hasta, $params);

        $sql = "SELECT
                    COUNT(*) AS total_legajos,
                    COALESCE(SUM(l.cantidad_paginas_procesadas), 0) AS total_paginas,
                    COALESCE(ROUND(AVG(l.cantidad_paginas_procesadas), 2), 0) AS promedio_paginas
                FROM cfg_legajo l" . $where;

        $row = $this->select($sql, $params);
        if (is_array($row) && isset($row[0]) && is_array($row[0])) {
            $row = $row[0];
        }

        return is_array($row) ? $row : [];
    }

    public function selectResumenPaginasLegajosPorPeriodo(?string $desde = null, ?string $hasta = null): array
    {
        $params = [];
        $where = $this->construirWhereReportePaginasLegajos($desde, $hasta, $params);

        $sql = "SELECT
                    DATE_FORMAT(l.fecha_creacion, '%Y-%m') AS periodo_codigo,
                    DATE_FORMAT(l.fecha_creacion, '%m/%Y') AS periodo,
                    COUNT(*) AS total_legajos,
                    COALESCE(SUM(l.cantidad_paginas_procesadas), 0) AS total_paginas
                FROM cfg_legajo l
                $where
                GROUP BY YEAR(l.fecha_creacion), MONTH(l.fecha_creacion)
                ORDER BY YEAR(l.fecha_creacion) DESC, MONTH(l.fecha_creacion) DESC";

        return $this->normalizarLista($this->select($sql, $params));
    }

    public function selectDetallePaginasLegajos(?string $desde = null, ?string $hasta = null): array
    {
        $params = [];
        $where = $this->construirWhereReportePaginasLegajos($desde, $hasta, $params);
        $joinUsuarioArmado = $this->existeColumna('cfg_legajo', 'id_usuario_armado')
            ? 'LEFT JOIN usuarios ua ON ua.id = l.id_usuario_armado'
            : '';
        $campoUsuario = $this->existeColumna('cfg_legajo', 'id_usuario_armado')
            ? "COALESCE(ua.nombre, uc.nombre, 'Sistema')"
            : "COALESCE(uc.nombre, 'Sistema')";

        $sql = "SELECT
                    l.id_legajo,
                    l.fecha_creacion,
                    l.cantidad_paginas_procesadas,
                    l.estado,
                    l.ci_socio,
                    l.nombre_completo,
                    l.nro_solicitud,
                    tl.nombre AS nombre_tipo_legajo,
                    $campoUsuario AS usuario_responsable
                FROM cfg_legajo l
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                LEFT JOIN usuarios uc ON uc.id = l.id_usuario
                $joinUsuarioArmado
                $where
                ORDER BY l.fecha_creacion DESC, l.id_legajo DESC";

        return $this->normalizarLista($this->select($sql, $params));
    }
}
