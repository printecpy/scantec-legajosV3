<?php
class ExpedientesModel extends Mysql
{
    protected $id_expediente;
    public function __construct()
    {
        parent::__construct();
    }

    public function selectExpediente()
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $sql = "SELECT v.indice_01, v.id_tipoDoc,
                MIN(v.nombre_tipoDoc) AS nombre_tipoDoc,
                COUNT(*) AS cant_documentos,
                MIN(v.id_expediente) AS id_expediente,
                MIN(v.indice_02) AS indice_02,
                MIN(v.indice_03) AS indice_03,
                MIN(v.indice_04) AS indice_04,
                MIN(v.indice_05) AS indice_05,
                MIN(v.indice_06) AS indice_06,
                MIN(v.fecha_indexado) AS fecha_indexado,
                MIN(v.ruta_original) AS ruta_original,
                MIN(v.paginas) AS paginas,
                MIN(v.ubicacion) AS ubicacion,
                MIN(v.version) AS version,
                MIN(v.estado) AS estado,
                
                MIN(v.firma_digital) AS firma_digital
            FROM v_expedientes v
            JOIN (
                SELECT indice_01
                FROM v_expedientes
                WHERE estado = 'Activo'
                GROUP BY indice_01
            ) AS sub ON v.indice_01 = sub.indice_01
            JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
            WHERE v.estado = 'Activo' 
            AND pd.id_grupo = ?
            AND pd.estado = 'ACTIVO'
            GROUP BY v.indice_01, v.id_tipoDoc
            ORDER BY MIN(v.id_expediente) ASC;";
        $res = $this->select_all($sql, [$id_grupo]);
        return $res;
    }

    public function selectExpedientes()
    {
        $sql = "SELECT id_expediente, indice_01, indice_02, indice_03, indice_04,indice_05, indice_06, paginas, ruta_original, 
        ubicacion, estado, fecha_indexado, version, fecha_actualizacion, fecha_vencimiento, firma_digital FROM v_expedientes;";
        $res = $this->select_all($sql);
        return $res;
    }
    public function editExpediente(int $id_expediente)
    {
        $sql = "SELECT * FROM expediente WHERE id_expediente = ?";
        $res = $this->select($sql, [$id_expediente]);
        return $res;
    }


    public function obtenerExpedienteGestionable(int $id_expediente, int $id_grupo): ?array
    {
        $sql = "SELECT e.*
            FROM expediente e
            WHERE e.id_expediente = ?
              AND EXISTS (SELECT 1 FROM permisos_documentos pd
                          WHERE pd.id_tipoDoc = e.id_tipoDoc
                            AND pd.id_grupo = ?
                            AND pd.estado = 'ACTIVO')
            LIMIT 1";
        $res = $this->select_all($sql, [$id_expediente, $id_grupo]);
        return $res[0] ?? null;
    }

    public function obtenerRutaExpediente(int $id_expediente)
    {
        $sql = "SELECT ruta_original FROM expediente WHERE id_expediente = ?";
        $res = $this->select($sql, [$id_expediente]);
        return !empty($res) ? $res[0] : [];
    }

    public function getTipoDocById($id_tipoDoc)
    {
        $sql = "SELECT nombre_tipoDoc 
        FROM tipo_documento 
        WHERE id_tipoDoc = ?;";

        $arrData = array($id_tipoDoc);
        $res = $this->select_all($sql, $arrData);

        return $res;
    }


    public function selectRegistros2(string $indice_01, int $id_tipoDoc, int $id_grupo)
    {
        $sql = "SELECT sub.cant_documentos, 
                   v.id_proceso, 
                   v.id_expediente,
                   v.id_tipoDoc,
                   v.nombre_tipoDoc,  
                   v.indice_01, 
                   v.indice_02, 
                   v.indice_03,
                   v.indice_04,  
                   v.indice_05,  
                   v.indice_06, 
                   v.fecha_indexado,
                   v.fecha_actualizacion,
                   v.ruta_original, 
                   v.estado, 
                   v.paginas, 
                   v.ubicacion, 
                   v.version, 
                   v.firma_digital
            FROM v_expedientes v
            JOIN (
                SELECT indice_01, COUNT(*) AS cant_documentos
                FROM v_expedientes
                WHERE estado = 'Activo'
                GROUP BY indice_01
            ) AS sub ON v.indice_01 = sub.indice_01
            JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
            WHERE v.estado = 'Activo' 
              AND pd.id_grupo = ? 
              AND pd.estado = 'ACTIVO'
              AND v.id_tipoDoc = ?
              AND v.indice_01 = ?
            ORDER BY v.id_expediente ASC;";

        return $this->select_all($sql, [$id_grupo, $id_tipoDoc, $indice_01]);
    }

    public function obtenerReferenciaExpediente(int $id_expediente, int $id_grupo)
    {
        $sql = "SELECT v.indice_01, v.id_tipoDoc
            FROM v_expedientes v
            JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
            WHERE v.id_expediente = ?
              AND v.estado = 'Activo'
              AND pd.id_grupo = ?
              AND pd.estado = 'ACTIVO'
            LIMIT 1;";
        $res = $this->select_all($sql, [$id_expediente, $id_grupo]);
        return $res[0] ?? null;
    }

    // Carpetas (indice_01 + tipo de documento) de un indice_01 EXACTO, con permiso vigente del grupo.
    // cant_documentos = documentos que el usuario ve al entrar (mismo criterio que selectRegistros2).
    // $id_tipoDoc = 0 -> todos los tipos.
    public function buscarExpedientesPorIndice01(string $indice_01, int $id_tipoDoc, int $id_grupo): array
    {
        $sql = "SELECT grp.cant_documentos,
                       v.id_expediente, v.id_tipoDoc, v.nombre_tipoDoc,
                       v.indice_01, v.indice_02, v.indice_03, v.indice_04, v.indice_05, v.indice_06,
                       v.fecha_indexado, v.fecha_actualizacion, v.paginas, v.estado
                FROM v_expedientes v
                JOIN (
                    SELECT vv.indice_01, vv.id_tipoDoc,
                           MIN(vv.id_expediente) AS min_id,
                           COUNT(*)              AS cant_documentos
                    FROM v_expedientes vv
                    WHERE vv.estado = 'Activo'
                      AND vv.indice_01 = ?
                      AND (? = 0 OR vv.id_tipoDoc = ?)
                      AND EXISTS (SELECT 1 FROM permisos_documentos pd
                                  WHERE pd.id_tipoDoc = vv.id_tipoDoc
                                    AND pd.id_grupo = ?
                                    AND pd.estado = 'ACTIVO')
                    GROUP BY vv.indice_01, vv.id_tipoDoc
                ) AS grp ON grp.min_id = v.id_expediente
                ORDER BY v.nombre_tipoDoc, v.id_expediente
                LIMIT 200";
        $res = $this->select_all($sql, [$indice_01, $id_tipoDoc, $id_tipoDoc, $id_grupo]);
        return is_array($res) ? $res : [];
    }

    // Resuelve indice_01 + tipo a partir de un id, solo si el grupo tiene permiso vigente sobre ese tipo.
    public function obtenerReferenciaExpediente2(int $id_expediente, int $id_grupo): ?array
    {
        $sql = "SELECT v.indice_01, v.id_tipoDoc
                FROM v_expedientes v
                WHERE v.id_expediente = ?
                  AND v.estado = 'Activo'
                  AND EXISTS (SELECT 1 FROM permisos_documentos pd
                              WHERE pd.id_tipoDoc = v.id_tipoDoc
                                AND pd.id_grupo = ?
                                AND pd.estado = 'ACTIVO')
                LIMIT 1";
        $res = $this->select_all($sql, [$id_expediente, $id_grupo]);
        return $res[0] ?? null;
    }

    // Documentos de una carpeta (indice_01 + tipo). Sin ruta_original: no se envian rutas del servidor al HTML.
    public function selectRegistros(string $indice_01, int $id_tipoDoc, int $id_grupo): array
    {
        $sql = "SELECT v.id_proceso, v.id_expediente, v.id_tipoDoc, v.nombre_tipoDoc,
                       v.indice_01, v.indice_02, v.indice_03, v.indice_04, v.indice_05, v.indice_06,
                       v.fecha_indexado, v.fecha_actualizacion, v.estado, v.paginas, v.ubicacion,
                       v.version, v.firma_digital
                FROM v_expedientes v
                WHERE v.estado = 'Activo'
                  AND v.id_tipoDoc = ?
                  AND v.indice_01 = ?
                  AND EXISTS (SELECT 1 FROM permisos_documentos pd
                              WHERE pd.id_tipoDoc = v.id_tipoDoc
                                AND pd.id_grupo = ?
                                AND pd.estado = 'ACTIVO')
                ORDER BY v.id_expediente ASC";
        $res = $this->select_all($sql, [$id_tipoDoc, $indice_01, $id_grupo]);
        return is_array($res) ? $res : [];
    }

    // ===================== FACTURACION DE EXPEDIENTES =====================
    // Fuente: tabla expediente_facturacion (hechos registrados por disparadores en la base de datos).
    // NUNCA se recalcula sobre la tabla expediente: asi los meses pasados no cambian.

    public function existeFacturacionExpedientes(): bool
    {
        $res = $this->select_all("SELECT COUNT(*) AS n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'expediente_facturacion'");
        return !empty($res) && intval($res[0]['n'] ?? 0) > 0;
    }

    /** Documentos nuevos del periodo, por tipo de documento (fecha base: fecha_indexado). */
    public function selectFacturacionNuevos(string $desde, string $hasta): array
    {
        if (!$this->existeFacturacionExpedientes()) {
            return [];
        }
        $sql = "SELECT f.id_tipoDoc, COALESCE(t.nombre_tipoDoc, 'Sin tipo de documento') AS tipo,
                   COUNT(*) AS documentos, COALESCE(SUM(f.paginas), 0) AS paginas,
                   SUM(CASE WHEN e.estado = 'Activo' THEN 1 ELSE 0 END) AS documentos_activos_hoy,
                   SUM(CASE WHEN e.estado = 'Activo' THEN f.paginas ELSE 0 END) AS paginas_activas_hoy,
                   SUM(CASE WHEN f.paginas < 1 THEN 1 ELSE 0 END) AS sin_conteo,
                   SUM(f.reconstruido) AS reconstruidos
            FROM expediente_facturacion f
            LEFT JOIN tipo_documento t ON t.id_tipoDoc = f.id_tipoDoc
            LEFT JOIN expediente e ON e.id_expediente = f.id_expediente
            WHERE f.tipo_evento = 'NUEVO' AND f.es_legajo = 0 AND f.fecha_negocio BETWEEN ? AND ?
            GROUP BY f.id_tipoDoc, t.nombre_tipoDoc
            ORDER BY tipo";
        return $this->select_all($sql, [$desde, $hasta]);
    }

    /** Actualizaciones del periodo, por tipo de documento (fecha base: fecha_actualizacion). */
    public function selectFacturacionActualizados(string $desde, string $hasta): array
    {
        if (!$this->existeFacturacionExpedientes()) {
            return [];
        }
        $sql = "SELECT f.id_tipoDoc, COALESCE(t.nombre_tipoDoc, 'Sin tipo de documento') AS tipo,
                   COUNT(*) AS actualizaciones, COUNT(DISTINCT f.id_expediente) AS archivos, COALESCE(SUM(f.paginas), 0) AS paginas,
                   SUM(CASE WHEN e.estado = 'Activo' THEN 1 ELSE 0 END) AS actualizaciones_activas_hoy,
                   SUM(CASE WHEN f.paginas < 1 THEN 1 ELSE 0 END) AS sin_conteo,
                   SUM(f.reconstruido) AS reconstruidos
            FROM expediente_facturacion f
            LEFT JOIN tipo_documento t ON t.id_tipoDoc = f.id_tipoDoc
            LEFT JOIN expediente e ON e.id_expediente = f.id_expediente
            WHERE f.tipo_evento = 'ACTUALIZACION' AND f.es_legajo = 0 AND f.fecha_negocio BETWEEN ? AND ?
            GROUP BY f.id_tipoDoc, t.nombre_tipoDoc
            ORDER BY tipo";
        return $this->select_all($sql, [$desde, $hasta]);
    }

    /** Cada hecho del periodo, para auditar los totales y para la hoja de detalle del Excel. */
    public function selectFacturacionDetalle(string $desde, string $hasta): array
    {
        if (!$this->existeFacturacionExpedientes()) {
            return [];
        }
        $sql = "SELECT f.id_evento, f.tipo_evento, f.fecha_negocio, COALESCE(t.nombre_tipoDoc, 'Sin tipo de documento') AS tipo,
                   f.id_expediente, e.indice_01, f.paginas, f.paginas_previas,
                   CASE WHEN e.id_expediente IS NULL THEN 'Eliminado' ELSE e.estado END AS estado_hoy,
                   f.usuario_db, f.reconstruido
            FROM expediente_facturacion f
            LEFT JOIN tipo_documento t ON t.id_tipoDoc = f.id_tipoDoc
            LEFT JOIN expediente e ON e.id_expediente = f.id_expediente
            WHERE f.es_legajo = 0 AND f.fecha_negocio BETWEEN ? AND ?
            ORDER BY f.fecha_negocio, f.id_evento";
        return $this->select_all($sql, [$desde, $hasta]);
    }

    /** Desde cuando se registran los hechos en el momento en que ocurren (los anteriores son reconstruidos). */
    public function selectFacturacionInicioRegistro(): ?string
    {
        if (!$this->existeFacturacionExpedientes()) {
            return null;
        }
        $res = $this->select_all("SELECT MIN(fecha_registro) AS desde FROM expediente_facturacion WHERE reconstruido = 0");
        return !empty($res) && !empty($res[0]['desde']) ? (string) $res[0]['desde'] : null;
    }

    /**
     * Verifica si un nombre de tipo de documento existe Y es accesible para el grupo dado.
     * Para admin (id_grupo_a_filtrar = 'ALL'), verifica sin restricción de grupo.
     */
    public function existeTipoDocParaGrupo(string $id_grupo_a_filtrar, string $nombre_tipoDoc): bool
    {
        $where_group = "";
        $params = [$nombre_tipoDoc];

        if ($id_grupo_a_filtrar !== 'ALL') {
            $where_group = "AND pd.id_grupo = ?";
            $params[] = intval($id_grupo_a_filtrar);
        }

        $sql = "SELECT COUNT(*) AS total
            FROM tipo_documento c
            JOIN permisos_documentos pd ON pd.id_tipoDoc = c.id_tipoDoc
            WHERE c.nombre_tipoDoc = ?
              AND pd.estado = 'ACTIVO'
              {$where_group}";

        $res = $this->select($sql, $params);
        if (is_array($res) && isset($res[0]) && is_array($res[0])) {
            $res = $res[0];
        }
        return intval($res['total'] ?? 0) > 0;
    }

    public function tipoDocPerteneceAGrupo(string $id_grupo_a_filtrar, string $id_tipoDoc): bool
    {
        if ($id_grupo_a_filtrar === 'ALL') {
            return true;
        }
        $sql = "SELECT COUNT(*) AS total FROM permisos_documentos
            WHERE id_tipoDoc = ? AND id_grupo = ? AND estado = 'ACTIVO'";
        $res = $this->select($sql, [$id_tipoDoc, intval($id_grupo_a_filtrar)]);
        if (is_array($res) && isset($res[0]) && is_array($res[0])) {
            $res = $res[0];
        }
        return intval($res['total'] ?? 0) > 0;
    }

    public function selectRegistrosApi(string $id_grupo_a_filtrar, string $indice_01, string $nombre_tipoDoc, string $termino)
    {
        $where_group = "";
        $params = [];
        if ($id_grupo_a_filtrar !== 'ALL') {
            // Filtro normal por grupo
            $id_grupo = intval($id_grupo_a_filtrar); // Limpieza de variable
            $where_group = "AND pd.id_grupo = ?";
            $params[] = $id_grupo;
        }
        $sql = "SELECT sub.cant_documentos, v.id_proceso, 
               v.id_expediente,
               v.id_tipoDoc,
               v.nombre_tipoDoc,  
               v.indice_01, 
               v.indice_02, 
               v.indice_03,
               v.indice_04,  
               v.indice_05,  
               v.indice_06, 
               v.fecha_indexado,
               v.fecha_actualizacion,
               v.ruta_original, 
               v.estado, 
               v.paginas, 
               v.ubicacion, 
            v.version, 
            v.firma_digital
        FROM v_expedientes v
        JOIN (
            SELECT indice_01, COUNT(*) AS cant_documentos
            FROM v_expedientes
            WHERE estado = 'Activo'
            GROUP BY indice_01
        ) AS sub ON v.indice_01 = sub.indice_01
        JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
        WHERE v.estado = 'Activo' 
          {$where_group} 
          AND v.nombre_tipoDoc = ?
          AND v.indice_01 = ?";
        $params[] = $nombre_tipoDoc;
        $params[] = $indice_01;
        if (!empty($termino)) {
            $sql .= " AND (
                v.indice_01 LIKE ? OR
                v.indice_02 LIKE ? OR
                v.indice_03 LIKE ? OR
                v.indice_04 LIKE ?
            )";
            $terminoLike = $termino . '%';
            array_push($params, $terminoLike, $terminoLike, $terminoLike, $terminoLike);
        }
        $sql .= " ORDER BY v.id_expediente ASC";
        $res = $this->select_all($sql, $params);
        return $res;
    }


    public function getExpedienteApiPorId(int $idExpediente): array
    {
        $sql = "SELECT 
                v.id_expediente,
                v.id_tipoDoc,
                v.nombre_tipoDoc,
                v.indice_01,
                v.ruta_original,
                v.estado,
                pd.id_grupo
            FROM v_expedientes v
            JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
            WHERE v.id_expediente = ?
              AND v.estado = 'Activo'
              AND pd.estado = 'ACTIVO'
            LIMIT 1";
        $res = $this->select_all($sql, [$idExpediente]);
        return !empty($res[0]) && is_array($res[0]) ? $res[0] : [];
    }

    public function obtener_datos_adicionales(string $ruta)
    {
        $sql = "SELECT ubicacion, fecha_indexado, version, fecha_vencimiento 
                FROM v_expedientes 
                WHERE ruta_original = ?";
        $res = $this->select($sql, [$ruta]);
        return $res;
    }

    public function selectTipoDoc()
    {
        if (!isset($_SESSION['id_grupo'])) {
            return [];
        }
        $id_grupo = intval($_SESSION['id_grupo']);
        $sql = "SELECT 
                a.id, 
                a.id_grupo, 
                b.descripcion AS grupo_descripcion, 
                a.id_tipoDoc, 
                c.nombre_tipoDoc, 
                c.indice_1, 
                c.indice_2, 
                c.indice_3, 
                c.indice_4, 
                c.indice_5, 
                c.indice_6, 
                b.estado AS estado_grupo
            FROM permisos_documentos a 
            INNER JOIN usu_grupo b ON a.id_grupo = b.id_grupo 
            INNER JOIN tipo_documento c ON a.id_tipoDoc = c.id_tipoDoc
            WHERE a.estado = 'ACTIVO' AND a.id_grupo = ?
            ORDER BY a.id ASC;";
        return $this->select_all($sql, [$id_grupo]);
    }


    public function buscarExpedientePorTermino(int $id_tipoDoc, string $termino)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $terminoLike = '%' . $termino . '%';

        $sql = "SELECT 
                total.cant_documentos,
                v.id_expediente,
                v.id_tipoDoc,
                v.nombre_tipoDoc,
                v.indice_01,
                v.indice_02,
                v.indice_03,
                v.indice_04,
                v.indice_05,
                v.indice_06,
                v.fecha_indexado,
                v.fecha_actualizacion,
                v.paginas,
                v.estado
            FROM v_expedientes v
            JOIN (
                -- Decide QUÉ carpetas (indice_01 + tipo) califican para el resultado,
                -- según el término buscado. Ancla a UNA fila real (MIN(id_expediente))
                -- para traer los datos de esa fila sin mezclar columnas de filas distintas.
                SELECT vv.indice_01, vv.id_tipoDoc, MIN(vv.id_expediente) AS min_id
                FROM v_expedientes vv
                JOIN permisos_documentos pd2 ON pd2.id_tipoDoc = vv.id_tipoDoc
                WHERE vv.estado = 'Activo'
                  AND pd2.id_grupo = ?
                  AND pd2.estado = 'ACTIVO'
                  AND (? = 0 OR pd2.id_tipoDoc = ?)
                  AND (
                      vv.indice_01 LIKE ? OR
                      vv.indice_02 LIKE ? OR
                      vv.indice_03 LIKE ? OR
                      vv.indice_04 LIKE ?
                  )
                GROUP BY vv.indice_01, vv.id_tipoDoc
            ) AS grp ON v.indice_01 = grp.indice_01 
                     AND v.id_tipoDoc = grp.id_tipoDoc 
                     AND v.id_expediente = grp.min_id
            JOIN (
                -- Cuenta el TOTAL REAL de documentos de cada carpeta, SIN filtrar por término
                -- (el mismo total que va a ver el usuario al entrar con selectRegistros).
                SELECT vv2.indice_01, vv2.id_tipoDoc, COUNT(*) AS cant_documentos
                FROM v_expedientes vv2
                JOIN permisos_documentos pd3 ON pd3.id_tipoDoc = vv2.id_tipoDoc
                WHERE vv2.estado = 'Activo'
                  AND pd3.id_grupo = ?
                  AND pd3.estado = 'ACTIVO'
                GROUP BY vv2.indice_01, vv2.id_tipoDoc
            ) AS total ON total.indice_01 = v.indice_01 
                        AND total.id_tipoDoc = v.id_tipoDoc
            WHERE v.estado = 'Activo'
            ORDER BY v.id_expediente DESC;";

        return $this->select_all($sql, [
            $id_grupo,
            $id_tipoDoc,
            $id_tipoDoc,
            $terminoLike,
            $terminoLike,
            $terminoLike,
            $terminoLike,
            $id_grupo
        ]);
    }

    public function registrar_visualizacion(
        int $id_user,
        int $id_expediente,
        string $usuario,
        string $nombre_pc,
        string $nombre_expediente,
        string $direccion_ip,
        string $fecha
    ) {
        $this->id_expediente = $id_expediente;
        $this->id_user = $id_user;
        $this->usuario = $usuario;
        $this->nombre_pc = $nombre_pc;
        $this->nombre_expediente = $nombre_expediente;
        $this->direccion_ip = $direccion_ip;
        $this->fecha = $fecha;
        $query = "INSERT INTO document_views (id_user, id_expediente, usuario, nombre_pc, nombre_expediente, direccion_ip, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?);";
        $data = array($id_user, $id_expediente, $usuario, $nombre_pc, $nombre_expediente, $direccion_ip, $fecha);
        $this->insert($query, $data);
        return true;
    }

    public function renomExpediente(int $id_expediente)
    {
        $sql = "SELECT * FROM expediente WHERE id_expediente = ?";
        $res = $this->select($sql, [$id_expediente]);
        return $res;
    }

    public function actualizarExpediente(
        string $id_proceso,
        string $indice_01,
        string $indice_02,
        string $indice_03,
        string $indice_04,
        string $indice_05,
        string $indice_06,
        string $ubicacion,
        string $firma_digital,
        string $version,
        int $paginas,       // <--- AGREGADO: Faltaba recibir esto
        int $id_expediente
    ) {
        // 1. La consulta SQL (11 campos + 1 WHERE = 12 signos de interrogación)
        // OJO: Si id_proceso venía disabled, a veces es mejor NO actualizarlo. 
        // Aquí asumo que quieres actualizarlo si o si.
        $query = "UPDATE expediente SET 
                    id_proceso=?, 
                    indice_01=?, 
                    indice_02=?, 
                    indice_03=?, 
                    indice_04=?, 
                    indice_05=?, 
                    indice_06=?, 
                    ubicacion=?, 
                    firma_digital=?, 
                    version=?, 
                    paginas=?        
                  WHERE id_expediente = ?";

        // 2. El Array de Datos (El orden DEBE ser idéntico al SQL de arriba)
        $data = array(
            $id_proceso,
            $indice_01,
            $indice_02,
            $indice_03,
            $indice_04,
            $indice_05,
            $indice_06,
            $ubicacion,
            $firma_digital,
            $version,
            $paginas,       // <--- Faltaba esto antes del ID
            $id_expediente  // Este va al final porque está en el WHERE
        );

        // 3. Ejecutar
        // Asegúrate que tu método update devuelva true/false
        return $this->update($query, $data);
    }
    public function registrarExpediente(
        string $id_proceso,
        string $id_tipoDoc,
        string $indice_01,
        string $indice_02,
        string $indice_03,
        string $indice_04,
        string $indice_05,
        string $indice_06,
        int $paginas,
        string $ruta_original,
        string $ubicacion,
        string $version,
        string $fecha_indexado
    ) {
        $this->id_proceso = $id_proceso;
        $this->id_tipoDoc = $id_tipoDoc;
        $this->indice_01 = $indice_01;
        $this->indice_02 = $indice_02;
        $this->indice_03 = $indice_03;
        $this->indice_04 = $indice_04;
        $this->indice_05 = $indice_05;
        $this->indice_06 = $indice_06;
        $this->paginas = $paginas;
        $this->ruta_original = $ruta_original;
        $this->ubicacion = $ubicacion;
        $this->version = $version;
        $this->fecha_indexado = $fecha_indexado;
        $query = "INSERT INTO expediente (
                        id_proceso, id_tipoDoc, indice_01, indice_02, indice_03, 
                        indice_04, indice_05, indice_06, ubicacion, paginas, 
                        ruta_original, version, fecha_indexado, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo');";
        $data = array(
            $this->id_proceso,
            $this->id_tipoDoc,
            $this->indice_01,
            $this->indice_02,
            $this->indice_03,
            $this->indice_04,
            $this->indice_05,
            $this->indice_06,
            $this->ubicacion,
            $this->paginas,
            $this->ruta_original,
            $this->version,
            $this->fecha_indexado
        );
        $this->insert($query, $data);
        return true;
    }

    public function renombrarExpediente(string $indice_01, string $indice_02, string $indice_05, string $ruta_original, int $id_expediente)
    {
        $this->indice_01 = $indice_01;
        $this->indice_02 = $indice_02;
        $this->ruta_original = $ruta_original;
        $this->indice_05 = $indice_05;
        $this->id_expediente = $id_expediente;
        $query = "UPDATE expediente SET indice_01=?, indice_02=?, indice_05=?, ruta_original=? WHERE id_expediente = ?";
        $data = array($this->indice_01, $this->indice_02, $this->indice_05, $this->ruta_original, $this->id_expediente);
        $this->update($query, $data);
        return true;
    }

    public function estadoExpediente(string $estado, int $id_expediente)
    {
        $this->estado = $estado;
        $this->id_expediente = $id_expediente;
        $query = "UPDATE expediente SET estado = ? WHERE id_expediente = ?;";
        $data = array($this->estado, $this->id_expediente);
        $this->update($query, $data);
        return true;
    }

    // Método para obtener las credenciales de la API desde la base de datos
    public function getApiCredentials(string $apiName)
    {
        $query = "SELECT base_url, api_key, api_secret FROM api_credenciales WHERE api_name = ?";
        return $this->select($query, array($apiName));
    }

    // Método para registrar la firma en la tabla 'registros_firmas'
    public function registrarFirma($idExpediente, $datosFirma)
    {
        $query = "INSERT INTO registros_firmas (id_expediente, tipo_firma, fecha_firma, detalles_firma) VALUES (?, ?, NOW(), ?)";
        $this->insert($query, array($idExpediente, $datosFirma['tipo_firma'], json_encode($datosFirma)));
    }

    public function selectDatos()
    {
        $sql = "SELECT id, nombre, telefono, direccion, correo, total_pag FROM configuracion limit 1;";
        return $this->select($sql);
    }

    public function reporteExpedientes(string $desde, string $hasta)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $sql = "SELECT v.id_expediente, v.indice_01, v.indice_02, v.indice_03, v.indice_04, v.indice_05, v.paginas
            FROM v_expedientes v
            WHERE v.indice_01 BETWEEN ? AND ?
              AND EXISTS (SELECT 1 FROM permisos_documentos pd
                          WHERE pd.id_tipoDoc = v.id_tipoDoc
                            AND pd.id_grupo = ?
                            AND pd.estado = 'ACTIVO')
            ORDER BY v.indice_01 ASC";
        return $this->select_all($sql, [$desde, $hasta, $id_grupo]);
    }

    public function reporteExpedientesFecha(string $desde, string $hasta)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT 
                v.indice_01,
                COUNT(*) AS cant_documentos,
                MIN(v.id_expediente) AS id_expediente,
                MIN(v.id_tipoDoc) AS id_tipoDoc,
                MIN(v.nombre_tipoDoc) AS nombre_tipoDoc,
                MIN(v.indice_01) AS indice_01,
                MIN(v.indice_02) AS indice_02,
                MIN(v.indice_03) AS indice_03,
                MIN(v.indice_04) AS indice_04,
                MIN(v.indice_05) AS indice_05,
                MIN(v.indice_06) AS indice_06,
                MIN(v.fecha_indexado) AS fecha_indexado,
                MIN(v.fecha_actualizacion) AS fecha_actualizacion,
                MIN(v.ruta_original) AS ruta_original,
                MIN(v.paginas) AS paginas,
                MIN(v.ubicacion) AS ubicacion,
                MIN(v.version) AS version,
                MIN(v.estado) AS estado,
                MIN(v.fecha_vencimiento) AS fecha_vencimiento,
                MIN(v.firma_digital) AS firma_digital
                FROM v_expedientes v
                JOIN (
                    SELECT indice_01, COUNT(*) AS cant_documentos
                    FROM v_expedientes
                    WHERE estado = 'Activo'
                    GROUP BY indice_01
                ) AS sub ON v.indice_01 = sub.indice_01
                JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
                WHERE v.estado = 'Activo' 
                    AND pd.id_grupo = ? 
                    AND pd.estado = 'ACTIVO'
                    AND v.fecha_indexado BETWEEN ? AND ?
                GROUP BY v.indice_01
                ORDER BY MIN(v.id_expediente) ASC;";
        $res = $this->select_all($sql, [$id_grupo, $desde, $hasta]);
        return $res;
    }

    public function reporteExpedientesDuplic()
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $sql = "SELECT v.indice_01, v.indice_04, COUNT(*) AS cantidad
            FROM v_expedientes v
            WHERE v.estado = 'Activo'
              AND EXISTS (SELECT 1 FROM permisos_documentos pd
                          WHERE pd.id_tipoDoc = v.id_tipoDoc
                            AND pd.id_grupo = ?
                            AND pd.estado = 'ACTIVO')
            GROUP BY v.indice_01, v.indice_04
            HAVING COUNT(*) > 1";
        return $this->select_all($sql, [$id_grupo]);
    }
}
