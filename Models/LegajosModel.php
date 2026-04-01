<?php
class LegajosModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureMatrizActivoColumn();
        $this->ensureTipoLegajoDepartamentoColumn();
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

    private function ensureMatrizActivoColumn(): void
    {
        try {
            if ($this->existeTabla('cfg_matriz_requisitos') && !$this->existeColumna('cfg_matriz_requisitos', 'activo')) {
                $this->update("ALTER TABLE cfg_matriz_requisitos ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER politica_actualizacion", []);
            }
        } catch (Throwable $e) {
            // No interrumpimos la carga.
        }
    }

    private function ensureTipoLegajoDepartamentoColumn(): void
    {
        try {
            if ($this->existeTabla('cfg_tipo_legajo') && !$this->existeColumna('cfg_tipo_legajo', 'id_departamento')) {
                $this->update("ALTER TABLE cfg_tipo_legajo ADD COLUMN id_departamento INT NOT NULL DEFAULT 0 AFTER descripcion", []);
            }
        } catch (Throwable $e) {
            // No interrumpimos la carga.
        }
    }

    /**
     * Retorna el fragmento SQL del CASE de estado de legajo a texto legible.
     * Centraliza la lÃ³gica para no duplicar en mÃºltiples queries.
     */
    private function sqlCaseEstadoTexto(string $alias = 'l'): string
    {
        return "CASE
                    WHEN {$alias}.estado = 'cerrado' THEN 'Cerrado'
                    WHEN {$alias}.estado = 'aprobado' THEN 'Cerrado'
                    WHEN {$alias}.estado = 'verificacion_rechazada' THEN 'Verificación rechazada'
                    WHEN {$alias}.estado = 'verificado' THEN 'Verificado'
                    WHEN {$alias}.estado = 'finalizado' THEN 'Completado'
                    WHEN {$alias}.estado = 'activo' THEN 'Vencido'
                    ELSE 'Incompleto'
                END";
    }

    private function construirDetalleAccion(?string $detalle, ?string $observacion = null): ?string
    {
        $detalle = trim((string)$detalle);
        $observacion = trim((string)$observacion);
        if ($detalle === '' && $observacion === '') {
            return null;
        }
        if ($detalle !== '' && $observacion !== '') {
            return $detalle . ' | ' . $observacion;
        }
        return $detalle !== '' ? $detalle : $observacion;
    }

    public function soportaEstadoLegajo(string $estado): bool
    {
        $sql = "SELECT COLUMN_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'cfg_legajo'
                  AND COLUMN_NAME = 'estado'";
        $result = $this->select($sql);
        $columnType = strtolower($result[0]['COLUMN_TYPE'] ?? '');
        if ($columnType === '') {
            return false;
        }

        return strpos($columnType, "'" . strtolower($estado) . "'") !== false;
    }

    public function soportaEstadoDocumentoLegajo(string $estado): bool
    {
        $sql = "SELECT COLUMN_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'cfg_legajo_documento'
                  AND COLUMN_NAME = 'estado'";
        $result = $this->select($sql);
        $columnType = strtolower($result[0]['COLUMN_TYPE'] ?? '');
        if ($columnType === '') {
            return false;
        }

        return strpos($columnType, "'" . strtolower($estado) . "'") !== false;
    }

    public function selectTiposLegajo()
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return [];
        }

        $campoDepartamento = $this->existeColumna('cfg_tipo_legajo', 'id_departamento')
            ? 'id_departamento'
            : '0 AS id_departamento';
        $campoRequiereSolicitud = $this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')
            ? 'requiere_nro_solicitud'
            : '0 AS requiere_nro_solicitud';

        $sql = "SELECT id_tipo_legajo, nombre, descripcion, activo, $campoDepartamento, $campoRequiereSolicitud
                FROM cfg_tipo_legajo
                WHERE activo = 1
                ORDER BY nombre ASC";
        return $this->select_all($sql);
    }

    public function selectTipoLegajoPorId(int $idTipoLegajo)
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return [];
        }

        $campoRequiereSolicitud = $this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')
            ? 'requiere_nro_solicitud'
            : '0 AS requiere_nro_solicitud';

        $sql = "SELECT id_tipo_legajo, nombre, descripcion, activo, $campoRequiereSolicitud
                FROM cfg_tipo_legajo
                WHERE id_tipo_legajo = ?";
        $rows = $this->select($sql, [$idTipoLegajo]);
        return !empty($rows) ? $rows[0] : [];
    }

    public function selectMatrizTiposLegajo()
    {
        if (!$this->existeTabla('cfg_matriz_requisitos') || !$this->existeTabla('cfg_catalogo_documentos')) {
            return [];
        }

        $campoPoliticaActualizacion = $this->existeColumna('cfg_matriz_requisitos', 'politica_actualizacion')
            ? 'mr.politica_actualizacion'
            : "CASE WHEN mr.permite_reemplazo = 1 THEN 'REEMPLAZAR' ELSE 'NO_PERMITIR' END AS politica_actualizacion";

        if ($this->existeTabla('cfg_tipo_legajo') && $this->existeColumna('cfg_matriz_requisitos', 'id_tipo_legajo')) {
            $campoActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? 'mr.activo' : '1 AS activo';
            $sql = "SELECT mr.id_requisito, mr.id_tipo_legajo, mr.id_tipoDoc, mr.id_documento_maestro, mr.rol_vinculado,
                    mr.es_obligatorio, mr.orden_visual, mr.permite_reemplazo, $campoPoliticaActualizacion, $campoActivo,
                    cd.nombre AS documento_nombre, cd.codigo_interno, cd.tiene_vencimiento, cd.dias_vigencia_base
                    FROM cfg_matriz_requisitos mr
                    INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                    ORDER BY COALESCE(mr.id_tipo_legajo, mr.id_tipoDoc) ASC, mr.orden_visual ASC, mr.id_requisito ASC";
            return $this->select_all($sql);
        }

        $campoActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? 'mr.activo' : '1 AS activo';
        $sql = "SELECT mr.id_requisito, NULL AS id_tipo_legajo, mr.id_tipoDoc, mr.id_documento_maestro, mr.rol_vinculado,
                mr.es_obligatorio, mr.orden_visual, mr.permite_reemplazo, $campoPoliticaActualizacion, $campoActivo,
                cd.nombre AS documento_nombre, cd.codigo_interno, cd.tiene_vencimiento, cd.dias_vigencia_base
                FROM cfg_matriz_requisitos mr
                INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                ORDER BY mr.id_tipoDoc ASC, mr.orden_visual ASC, mr.id_requisito ASC";
        return $this->select_all($sql);
    }

    public function obtenerMatrizLegajoPorTipo(int $idTipoLegajo)
    {
        if (!$this->existeTabla('cfg_matriz_requisitos') || !$this->existeTabla('cfg_catalogo_documentos')) {
            return [];
        }

        $campoPoliticaActualizacion = $this->existeColumna('cfg_matriz_requisitos', 'politica_actualizacion')
            ? 'mr.politica_actualizacion'
            : "CASE WHEN mr.permite_reemplazo = 1 THEN 'REEMPLAZAR' ELSE 'NO_PERMITIR' END AS politica_actualizacion";

        if ($this->existeColumna('cfg_matriz_requisitos', 'id_tipo_legajo')) {
            $campoActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? 'mr.activo' : '1 AS activo';
            $filtroActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? ' AND mr.activo = 1' : '';
            $sql = "SELECT mr.id_requisito, mr.id_documento_maestro, mr.rol_vinculado,
                    mr.es_obligatorio, mr.permite_reemplazo, mr.orden_visual, $campoPoliticaActualizacion, $campoActivo,
                    cd.nombre AS documento_nombre, cd.codigo_interno, cd.tiene_vencimiento, cd.dias_vigencia_base
                    FROM cfg_matriz_requisitos mr
                    INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                    WHERE mr.id_tipo_legajo = ?$filtroActivo
                    ORDER BY mr.orden_visual ASC, mr.id_requisito ASC";
            return $this->select_all($sql, [$idTipoLegajo]);
        }

        $campoActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? 'mr.activo' : '1 AS activo';
        $filtroActivo = $this->existeColumna('cfg_matriz_requisitos', 'activo') ? ' AND mr.activo = 1' : '';
        $sql = "SELECT mr.id_requisito, mr.id_documento_maestro, mr.rol_vinculado,
                mr.es_obligatorio, mr.permite_reemplazo, mr.orden_visual, $campoPoliticaActualizacion, $campoActivo,
                cd.nombre AS documento_nombre, cd.codigo_interno, cd.tiene_vencimiento, cd.dias_vigencia_base
                FROM cfg_matriz_requisitos mr
                INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                WHERE mr.id_tipoDoc = ?$filtroActivo
                ORDER BY mr.orden_visual ASC, mr.id_requisito ASC";
        return $this->select_all($sql, [$idTipoLegajo]);
    }

    public function insertarLegajo(int $idTipoLegajo, string $ciSocio, string $nombreCompleto, ?string $nroSolicitud, int $idUsuario, string $estado = 'borrador')
    {
        $query = "INSERT INTO cfg_legajo (id_tipo_legajo, ci_socio, nombre_completo, nro_solicitud, estado, id_usuario)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($query, [
            $idTipoLegajo,
            $ciSocio,
            $nombreCompleto,
            $nroSolicitud !== '' ? $nroSolicitud : null,
            $estado,
            $idUsuario
        ]);
    }

    public function actualizarLegajo(int $idLegajo, int $idTipoLegajo, string $ciSocio, string $nombreCompleto, ?string $nroSolicitud, string $estado = 'borrador')
    {
        $query = "UPDATE cfg_legajo
                SET id_tipo_legajo = ?, ci_socio = ?, nombre_completo = ?, nro_solicitud = ?, estado = ?
                WHERE id_legajo = ?";
        return $this->update($query, [
            $idTipoLegajo,
            $ciSocio,
            $nombreCompleto,
            $nroSolicitud !== '' ? $nroSolicitud : null,
            $estado,
            $idLegajo
        ]);
    }

    public function actualizarObservacionLegajo(int $idLegajo, ?string $observacion)
    {
        if ($idLegajo <= 0 || !$this->existeColumna('cfg_legajo', 'observacion')) {
            return false;
        }

        $query = "UPDATE cfg_legajo SET observacion = ? WHERE id_legajo = ?";
        return $this->update($query, [
            trim((string)$observacion) !== '' ? trim((string)$observacion) : null,
            $idLegajo
        ]);
    }

    public function selectLegajoPorId(int $idLegajo)
    {
        $campoObservacion = $this->existeColumna('cfg_legajo', 'observacion')
            ? 'l.observacion'
            : 'NULL AS observacion';
        $campoUsuarioArmado = $this->existeColumna('cfg_legajo', 'id_usuario_armado')
            ? 'l.id_usuario_armado'
            : 'l.id_usuario AS id_usuario_armado';
        $sql = "SELECT l.id_legajo, l.id_tipo_legajo, l.ci_socio, l.nombre_completo, l.nro_solicitud,
                l.estado, l.fecha_creacion, l.fecha_cierre, l.id_usuario, $campoObservacion, $campoUsuarioArmado,
                tl.nombre AS nombre_tipo_legajo,
                uc.nombre AS nombre_usuario_creador,
                ua.nombre AS nombre_usuario_armado
                FROM cfg_legajo l
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                LEFT JOIN usuarios uc ON uc.id = l.id_usuario
                LEFT JOIN usuarios ua ON ua.id = " . ($this->existeColumna('cfg_legajo', 'id_usuario_armado') ? 'l.id_usuario_armado' : 'l.id_usuario') . "
                WHERE l.id_legajo = ?";
        $rows = $this->select($sql, [$idLegajo]);
        return !empty($rows) ? $rows[0] : [];
    }

    public function usuarioEsPropietarioLegajo(int $idLegajo, int $idUsuario): bool
    {
        if ($idLegajo <= 0 || $idUsuario <= 0) {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM cfg_legajo
                WHERE id_legajo = ? AND id_usuario = ?";
        $result = $this->select($sql, [$idLegajo, $idUsuario]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function actualizarUsuarioArmado(int $idLegajo, int $idUsuario)
    {
        if ($idLegajo <= 0 || $idUsuario <= 0 || !$this->existeColumna('cfg_legajo', 'id_usuario_armado')) {
            return false;
        }

        $query = "UPDATE cfg_legajo SET id_usuario_armado = ? WHERE id_legajo = ?";
        return $this->update($query, [$idUsuario, $idLegajo]);
    }

    public function buscarLegajosPorTermino(string $termino, string $estadoFiltro = '', int $idTipoLegajo = 0, string $filtroDocumentos = '', int $idUsuario = 0, bool $soloPropios = false, array $tiposPermitidos = [])
    {
        $termino = trim($termino);
        $estadoFiltro = trim($estadoFiltro);
        $filtroDocumentos = trim($filtroDocumentos);
        $campoObservacion = $this->existeColumna('cfg_legajo', 'observacion')
            ? 'l.observacion'
            : 'NULL AS observacion';
        $caseEstado = $this->sqlCaseEstadoTexto('l');
        $sql = "SELECT l.id_legajo, l.ci_socio, l.nombre_completo, l.nro_solicitud, l.estado, $campoObservacion,
                $caseEstado AS estado_legajo_texto,
                l.fecha_creacion, tl.nombre AS nombre_tipo_legajo
                FROM cfg_legajo l
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                WHERE 1=1";

        $params = [];
        if ($termino !== '' && $termino !== '*.*') {
            $sql .= " AND (l.ci_socio LIKE ? OR l.nro_solicitud LIKE ? OR l.nombre_completo LIKE ?)";
            $like = '%' . $termino . '%';
            $params = [$like, $like, $like];
        }

        if ($estadoFiltro !== '') {
            $sql .= " AND ($caseEstado) = ?";
            $params[] = $estadoFiltro;
        }

        if ($idTipoLegajo > 0) {
            $sql .= " AND l.id_tipo_legajo = ?";
            $params[] = $idTipoLegajo;
        }

        if (!empty($tiposPermitidos)) {
            $tiposPermitidos = array_values(array_unique(array_map('intval', $tiposPermitidos)));
            $placeholdersTipos = implode(',', array_fill(0, count($tiposPermitidos), '?'));
            $sql .= " AND l.id_tipo_legajo IN ($placeholdersTipos)";
            $params = array_merge($params, $tiposPermitidos);
        }

        if ($soloPropios && $idUsuario > 0) {
            $sql .= " AND l.id_usuario = ?";
            $params[] = $idUsuario;
        }

        if ($filtroDocumentos === 'por_vencer') {
            $sql .= " AND l.estado NOT IN ('cerrado', 'aprobado')
                      AND EXISTS (
                          SELECT 1
                          FROM cfg_legajo_documento ld
                          WHERE ld.id_legajo = l.id_legajo
                            AND ld.ruta_archivo IS NOT NULL
                            AND TRIM(ld.ruta_archivo) <> ''
                            AND ld.fecha_vencimiento IS NOT NULL
                            AND DATE(ld.fecha_vencimiento) >= CURDATE()
                            AND DATE(ld.fecha_vencimiento) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                      )";
        } elseif ($filtroDocumentos === 'vencidos') {
            $sql .= " AND l.estado NOT IN ('cerrado', 'aprobado')
                      AND EXISTS (
                          SELECT 1
                          FROM cfg_legajo_documento ld
                          WHERE ld.id_legajo = l.id_legajo
                            AND (
                                ld.estado = 'pendiente'
                                OR (
                                    ld.fecha_vencimiento IS NOT NULL
                                    AND DATE(ld.fecha_vencimiento) < CURDATE()
                                )
                            )
                      )";
        }

        $sql .= " ORDER BY l.fecha_creacion DESC, l.id_legajo DESC LIMIT 50";
        return $this->select($sql, $params);
    }

    public function buscarLegajosParaVerificar(string $termino = '', string $estadoFiltro = '', int $idTipoLegajo = 0, bool $soloPendientes = true, int $idUsuario = 0, bool $soloPropios = false, array $tiposPermitidos = [])
    {
        $termino = trim($termino);
        $estadoFiltro = trim($estadoFiltro);
        $campoObservacion = $this->existeColumna('cfg_legajo', 'observacion')
            ? 'l.observacion'
            : 'NULL AS observacion';
        $joinUsuarioArmado = $this->existeColumna('cfg_legajo', 'id_usuario_armado')
            ? 'LEFT JOIN usuarios ua ON ua.id = l.id_usuario_armado'
            : 'LEFT JOIN usuarios ua ON ua.id = l.id_usuario';
        $caseEstado = $this->sqlCaseEstadoTexto('l');
        $sql = "SELECT l.id_legajo, l.ci_socio, l.nombre_completo, l.nro_solicitud, l.estado, $campoObservacion,
                $caseEstado AS estado_legajo_texto,
                l.fecha_creacion, tl.nombre AS nombre_tipo_legajo,
                ua.nombre AS nombre_usuario_armado
                FROM cfg_legajo l
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                $joinUsuarioArmado
                WHERE 1=1";

        if ($soloPendientes) {
            $sql .= " AND l.estado <> 'cerrado' AND l.estado <> 'aprobado' AND l.estado <> 'verificado'";
        }

        $params = [];
        if ($termino !== '') {
            $sql .= " AND (l.ci_socio LIKE ? OR l.nro_solicitud LIKE ? OR l.nombre_completo LIKE ?)";
            $like = '%' . $termino . '%';
            $params = [$like, $like, $like];
        }

        if ($estadoFiltro !== '') {
            $sql .= " AND ($caseEstado) = ?";
            $params[] = $estadoFiltro;
        }

        if ($idTipoLegajo > 0) {
            $sql .= " AND l.id_tipo_legajo = ?";
            $params[] = $idTipoLegajo;
        }

        if (!empty($tiposPermitidos)) {
            $tiposPermitidos = array_values(array_unique(array_map('intval', $tiposPermitidos)));
            $placeholdersTipos = implode(',', array_fill(0, count($tiposPermitidos), '?'));
            $sql .= " AND l.id_tipo_legajo IN ($placeholdersTipos)";
            $params = array_merge($params, $tiposPermitidos);
        }

        if ($soloPropios && $idUsuario > 0) {
            $sql .= " AND l.id_usuario = ?";
            $params[] = $idUsuario;
        }

        $sql .= " ORDER BY l.fecha_creacion DESC, l.id_legajo DESC LIMIT 50";
        return $this->select($sql, $params);
    }

    // Nota: usar selectLegajoPorId() directamente. Alias eliminado para evitar confusiÃ³n.

    public function existeSolicitudAprobada(string $nroSolicitud, int $idLegajoExcluir = 0): bool
    {
        $nroSolicitud = trim($nroSolicitud);
        if ($nroSolicitud === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM cfg_legajo
                WHERE nro_solicitud = ? AND estado = 'aprobado'";
        $params = [$nroSolicitud];

        if ($idLegajoExcluir > 0) {
            $sql .= " AND id_legajo <> ?";
            $params[] = $idLegajoExcluir;
        }

        $result = $this->select($sql, $params);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function existeSolicitudDuplicada(string $nroSolicitud, int $idLegajoExcluir = 0): bool
    {
        $nroSolicitud = trim($nroSolicitud);
        if ($nroSolicitud === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM cfg_legajo
                WHERE nro_solicitud = ?";
        $params = [$nroSolicitud];

        if ($idLegajoExcluir > 0) {
            $sql .= " AND id_legajo <> ?";
            $params[] = $idLegajoExcluir;
        }

        $result = $this->select($sql, $params);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function selectLegajoPorSolicitud(string $nroSolicitud, int $idLegajoExcluir = 0)
    {
        $nroSolicitud = trim($nroSolicitud);
        if ($nroSolicitud === '') {
            return [];
        }

        $sql = "SELECT *
                FROM cfg_legajo
                WHERE nro_solicitud = ?";
        $params = [$nroSolicitud];

        if ($idLegajoExcluir > 0) {
            $sql .= " AND id_legajo <> ?";
            $params[] = $idLegajoExcluir;
        }

        $sql .= " ORDER BY id_legajo DESC LIMIT 1";
        $result = $this->select($sql, $params);

        if (is_array($result) && isset($result[0]) && is_array($result[0])) {
            return $result[0];
        }

        return is_array($result) ? $result : [];
    }

    public function existeLegajoDuplicadoSinSolicitud(int $idTipoLegajo, string $ciSocio, int $idLegajoExcluir = 0): bool
    {
        $ciSocio = trim($ciSocio);
        if ($idTipoLegajo <= 0 || $ciSocio === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM cfg_legajo
                WHERE id_tipo_legajo = ?
                  AND ci_socio = ?
                  AND (nro_solicitud IS NULL OR TRIM(nro_solicitud) = '')";
        $params = [$idTipoLegajo, $ciSocio];

        if ($idLegajoExcluir > 0) {
            $sql .= " AND id_legajo <> ?";
            $params[] = $idLegajoExcluir;
        }

        $result = $this->select($sql, $params);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function selectLegajoDuplicadoSinSolicitud(int $idTipoLegajo, string $ciSocio, int $idLegajoExcluir = 0)
    {
        $ciSocio = trim($ciSocio);
        if ($idTipoLegajo <= 0 || $ciSocio === '') {
            return [];
        }

        $sql = "SELECT *
                FROM cfg_legajo
                WHERE id_tipo_legajo = ?
                  AND ci_socio = ?
                  AND (nro_solicitud IS NULL OR TRIM(nro_solicitud) = '')";
        $params = [$idTipoLegajo, $ciSocio];

        if ($idLegajoExcluir > 0) {
            $sql .= " AND id_legajo <> ?";
            $params[] = $idLegajoExcluir;
        }

        $sql .= " ORDER BY id_legajo DESC LIMIT 1";
        $result = $this->select($sql, $params);

        if (is_array($result) && isset($result[0]) && is_array($result[0])) {
            return $result[0];
        }

        return is_array($result) ? $result : [];
    }

    public function selectLegajoDocumentosPorLegajo(int $idLegajo)
    {
        $campoObservacion = $this->existeColumna('cfg_legajo_documento', 'observacion')
            ? 'observacion'
            : 'NULL AS observacion';
        $sql = "SELECT id_legajo_doc, id_legajo, id_requisito, id_documento_maestro, rol_vinculado,
                es_obligatorio, estado, ruta_archivo, fecha_carga, fecha_vencimiento, reemplazado_por, $campoObservacion
                FROM cfg_legajo_documento
                WHERE id_legajo = ?
                ORDER BY id_requisito ASC, id_legajo_doc ASC";
        return $this->select($sql, [$idLegajo]);
    }

    // Nota: usar selectLegajoDocumentosPorLegajo() directamente. Alias eliminado para evitar confusiÃ³n.

    public function existeLegajoDocumento(int $idLegajo, int $idRequisito): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM cfg_legajo_documento
                WHERE id_legajo = ? AND id_requisito = ?";
        $result = $this->select($sql, [$idLegajo, $idRequisito]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function insertarLegajoDocumento(
        int $idLegajo,
        int $idRequisito,
        int $idDocumentoMaestro,
        string $rolVinculado,
        int $esObligatorio,
        string $estado = 'pendiente'
    ) {
        $query = "INSERT INTO cfg_legajo_documento
                (id_legajo, id_requisito, id_documento_maestro, rol_vinculado, es_obligatorio, estado)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($query, [
            $idLegajo,
            $idRequisito,
            $idDocumentoMaestro,
            $rolVinculado,
            $esObligatorio,
            $estado
        ]);
    }

    public function actualizarLegajoDocumento(
        int $idLegajo,
        int $idRequisito,
        ?string $rutaArchivo,
        ?string $fechaVencimiento,
        ?string $estado = null,
        ?string $observacion = null
    ) {
        $set = [];
        $params = [];

        if ($rutaArchivo !== null) {
            $set[] = "ruta_archivo = ?";
            $params[] = $rutaArchivo;
            $set[] = "fecha_carga = " . (trim($rutaArchivo) !== '' ? "NOW()" : "NULL");
        }

        $set[] = "fecha_vencimiento = ?";
        $params[] = $fechaVencimiento !== '' ? $fechaVencimiento : null;

        if ($estado !== null) {
            $set[] = "estado = ?";
            $params[] = $estado;
        }

        if ($observacion !== null && $this->existeColumna('cfg_legajo_documento', 'observacion')) {
            $set[] = "observacion = ?";
            $params[] = trim($observacion) !== '' ? trim($observacion) : null;
        }

        if (empty($set)) {
            return false;
        }

        $params[] = $idLegajo;
        $params[] = $idRequisito;

        $query = "UPDATE cfg_legajo_documento
                SET " . implode(', ', $set) . "
                WHERE id_legajo = ? AND id_requisito = ?";
        return $this->update($query, $params);
    }

    public function registrarLogLegajo(
        int $idLegajo,
        string $accion,
        ?string $detalle,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        int $idUsuario,
        ?string $nombreHost,
        ?string $ipHost,
        ?string $observacion = null
    ) {
        if (!$this->existeTabla('cfg_legajo_log') || $idLegajo <= 0) {
            return false;
        }

        $query = "INSERT INTO cfg_legajo_log
                (id_legajo, accion, detalle, estado_anterior, estado_nuevo, observacion, id_usuario, nombre_host, ip_host, fecha_evento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->insert($query, [
            $idLegajo,
            $accion,
            $this->construirDetalleAccion($detalle, $observacion),
            $estadoAnterior !== '' ? $estadoAnterior : null,
            $estadoNuevo !== '' ? $estadoNuevo : null,
            $observacion !== '' ? $observacion : null,
            $idUsuario > 0 ? $idUsuario : null,
            $nombreHost !== '' ? $nombreHost : null,
            $ipHost !== '' ? $ipHost : null,
        ]);
    }

    public function registrarLogLegajoDocumento(
        int $idLegajo,
        int $idRequisito,
        string $accion,
        ?string $detalle,
        ?string $rutaAnterior,
        ?string $rutaNueva,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?string $fechaVencimiento,
        int $idUsuario,
        ?string $nombreHost,
        ?string $ipHost,
        ?string $observacion = null
    ) {
        if (!$this->existeTabla('cfg_legajo_documento_log') || $idLegajo <= 0 || $idRequisito <= 0) {
            return false;
        }

        $documento = $this->select(
            "SELECT id_legajo_doc, id_documento_maestro
             FROM cfg_legajo_documento
             WHERE id_legajo = ? AND id_requisito = ?
             ORDER BY id_legajo_doc DESC
             LIMIT 1",
            [$idLegajo, $idRequisito]
        );
        $idLegajoDoc = intval($documento[0]['id_legajo_doc'] ?? 0);
        $idDocumentoMaestro = intval($documento[0]['id_documento_maestro'] ?? 0);
        if ($idLegajoDoc <= 0) {
            return false;
        }

        $query = "INSERT INTO cfg_legajo_documento_log
                (id_legajo, id_legajo_doc, id_requisito, id_documento_maestro, accion, detalle, ruta_anterior, ruta_nueva, estado_anterior, estado_nuevo, fecha_vencimiento, observacion, id_usuario, nombre_host, ip_host, fecha_evento)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->insert($query, [
            $idLegajo,
            $idLegajoDoc,
            $idRequisito,
            $idDocumentoMaestro > 0 ? $idDocumentoMaestro : null,
            $accion,
            $this->construirDetalleAccion($detalle, $observacion),
            $rutaAnterior !== '' ? $rutaAnterior : null,
            $rutaNueva !== '' ? $rutaNueva : null,
            $estadoAnterior !== '' ? $estadoAnterior : null,
            $estadoNuevo !== '' ? $estadoNuevo : null,
            $fechaVencimiento !== '' ? $fechaVencimiento : null,
            $observacion !== '' ? $observacion : null,
            $idUsuario > 0 ? $idUsuario : null,
            $nombreHost !== '' ? $nombreHost : null,
            $ipHost !== '' ? $ipHost : null,
        ]);
    }

    public function sincronizarEstadosDocumentosLegajo(int $diasMargen = 30)
    {
        $diasMargen = max(0, intval($diasMargen));
        $estadoPorVencer = $this->soportaEstadoDocumentoLegajo('por_vencer') ? 'por_vencer' : 'cargado';

        $query = "UPDATE cfg_legajo_documento ld
                INNER JOIN cfg_legajo l ON l.id_legajo = ld.id_legajo
                SET ld.estado = CASE
                    WHEN ld.ruta_archivo IS NULL OR TRIM(ld.ruta_archivo) = '' THEN 'pendiente'
                    WHEN ld.fecha_vencimiento IS NOT NULL AND DATE(ld.fecha_vencimiento) < CURDATE() THEN 'vencido'
                    WHEN ld.fecha_vencimiento IS NOT NULL AND DATE(ld.fecha_vencimiento) <= DATE_ADD(CURDATE(), INTERVAL ? DAY) THEN ?
                    ELSE 'cargado'
                END
                WHERE l.estado NOT IN ('cerrado', 'aprobado')";

        return $this->update($query, [$diasMargen, $estadoPorVencer]);
    }

    public function obtenerDocumentosCargadosParaUnir(int $idLegajo)
    {
        $sql = "SELECT ld.id_legajo_doc, ld.id_requisito, ld.ruta_archivo, ld.fecha_vencimiento,
                ld.estado, ld.rol_vinculado, cd.nombre AS documento_nombre
                FROM cfg_legajo_documento ld
                INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = ld.id_documento_maestro
                WHERE ld.id_legajo = ? AND ld.ruta_archivo IS NOT NULL AND ld.ruta_archivo <> ''
                ORDER BY ld.id_requisito ASC, ld.id_legajo_doc ASC";
        return $this->select($sql, [$idLegajo]);
    }

    public function marcarLegajoFinalizado(int $idLegajo)
    {
        $query = "UPDATE cfg_legajo
                SET estado = 'finalizado', fecha_cierre = NOW()
                WHERE id_legajo = ?";
        return $this->update($query, [$idLegajo]);
    }

    public function actualizarEstadoLegajo(int $idLegajo, string $estado, bool $cerrar = false)
    {
        if (!$this->soportaEstadoLegajo($estado)) {
            return false;
        }

        $query = "UPDATE cfg_legajo
                SET estado = ?, fecha_cierre = " . ($cerrar ? "NOW()" : "NULL") . "
                WHERE id_legajo = ?";
        return $this->update($query, [$estado, $idLegajo]);
    }

    public function eliminarLegajo(int $idLegajo)
    {
        if ($idLegajo <= 0) {
            return false;
        }

        try {
            if ($this->existeTabla('cfg_legajo_documento_log')) {
                $this->update("DELETE FROM cfg_legajo_documento_log WHERE id_legajo = ?", [$idLegajo]);
            }
            if ($this->existeTabla('cfg_legajo_log')) {
                $this->update("DELETE FROM cfg_legajo_log WHERE id_legajo = ?", [$idLegajo]);
            }
            $this->update("DELETE FROM cfg_legajo_documento WHERE id_legajo = ?", [$idLegajo]);
            return $this->update("DELETE FROM cfg_legajo WHERE id_legajo = ?", [$idLegajo]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function insertarLogUnionLegajo(string $ciSocio, string $nombreCompleto, ?string $nroSolicitud, string $usuario, string $nombreArchivo, string $rutaCreacion)
    {
        $sql = "INSERT INTO unirpdf (columna_01, columna_02, columna_03, usuario, ruta_creacion, nombre_archivo, fecha_creacion)
                VALUES (?,?,?,?,?,?, NOW())";
        return $this->insert($sql, [
            $ciSocio,
            $nombreCompleto,
            $nroSolicitud !== '' ? $nroSolicitud : null,
            $usuario,
            $rutaCreacion,
            $nombreArchivo
        ]);
    }

    public function selectLogLegajos()
    {
        $tieneLogLegajo = $this->existeTabla('cfg_legajo_log');
        $tieneLogDocumento = $this->existeTabla('cfg_legajo_documento_log');
        $tieneLogPermisos = $this->existeTabla('cfg_permisos_log');

        if (!$tieneLogLegajo && !$tieneLogDocumento && !$tieneLogPermisos) {
            return [];
        }

        $consultas = [];
        $params = [];

        if ($tieneLogLegajo) {
            $consultas[] = "SELECT
                    ll.id_log_legajo AS id_log,
                    ll.id_legajo,
                    l.ci_socio,
                    l.nombre_completo,
                    l.nro_solicitud,
                    tl.nombre AS nombre_tipo_legajo,
                    'LEGAJO' AS origen,
                    ll.accion,
                    ll.detalle,
                    NULL AS documento,
                    COALESCE(ll.estado_nuevo, ll.estado_anterior, '') AS estado_evento,
                    COALESCE(u.nombre, 'Sistema') AS usuario_evento,
                    ll.nombre_host,
                    ll.ip_host,
                    ll.fecha_evento
                FROM cfg_legajo_log ll
                INNER JOIN cfg_legajo l ON l.id_legajo = ll.id_legajo
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                LEFT JOIN usuarios u ON u.id = ll.id_usuario";
        }

        if ($tieneLogDocumento) {
            $consultas[] = "SELECT
                    ld.id_log_legajo_doc AS id_log,
                    ld.id_legajo,
                    l.ci_socio,
                    l.nombre_completo,
                    l.nro_solicitud,
                    tl.nombre AS nombre_tipo_legajo,
                    'DOCUMENTO' AS origen,
                    ld.accion,
                    ld.detalle,
                    cd.nombre AS documento,
                    COALESCE(ld.estado_nuevo, ld.estado_anterior, '') AS estado_evento,
                    COALESCE(u.nombre, 'Sistema') AS usuario_evento,
                    ld.nombre_host,
                    ld.ip_host,
                    ld.fecha_evento
                FROM cfg_legajo_documento_log ld
                INNER JOIN cfg_legajo l ON l.id_legajo = ld.id_legajo
                LEFT JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = l.id_tipo_legajo
                LEFT JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = ld.id_documento_maestro
                LEFT JOIN usuarios u ON u.id = ld.id_usuario";
        }

        if ($tieneLogPermisos) {
            $consultas[] = "SELECT
                    pl.id_log_permiso AS id_log,
                    NULL AS id_legajo,
                    NULL AS ci_socio,
                    r.descripcion AS nombre_completo,
                    NULL AS nro_solicitud,
                    'SEGURIDAD' AS nombre_tipo_legajo,
                    'PERMISOS' AS origen,
                    pl.accion,
                    pl.detalle,
                    NULL AS documento,
                    CASE 
                        WHEN pl.estado_nuevo = 1 THEN 'HABILITADO'
                        WHEN pl.estado_nuevo = 0 THEN 'DESHABILITADO'
                        ELSE 'SIN CAMBIO'
                    END AS estado_evento,
                    COALESCE(u.nombre, 'Sistema') AS usuario_evento,
                    pl.nombre_host,
                    pl.ip_host,
                    pl.fecha_evento
                FROM cfg_permisos_log pl
                INNER JOIN roles r ON r.id_rol = pl.id_rol
                LEFT JOIN usuarios u ON u.id = pl.id_usuario";
        }

        $sql = implode(" UNION ALL ", $consultas) . " ORDER BY fecha_evento DESC, id_log DESC";
        return $this->select($sql, $params);
    }
}



