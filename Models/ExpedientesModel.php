<?php
class ExpedientesModel extends Mysql
{
    protected $id_expediente;
    public function __construct()
    {
        parent::__construct();
    }

    /*public function selectExpediente()
    {
        $id_grupo = $_SESSION['id_grupo'];
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
                MIN(v.fecha_vencimiento) AS fecha_vencimiento,
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
            AND pd.id_grupo = $id_grupo
            GROUP BY v.indice_01, v.id_tipoDoc
            ORDER BY MIN(v.id_expediente) ASC;";
        $res = $this->select_all($sql);
        return $res;
    } */
    public function selectExpediente()
    {
        $id_grupo = $_SESSION['id_grupo'];
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
            AND pd.id_grupo = $id_grupo
            GROUP BY v.indice_01, v.id_tipoDoc
            ORDER BY MIN(v.id_expediente) ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectExpedientes()
    {
        $sql = "SELECT id_expediente, indice_01, indice_02, indice_03, indice_04,indice_05, indice_06, paginas, ruta_original, 
        ubicacion, estado, fecha_indexado, version, fecha_vencimiento, firma_digital FROM v_expedientes;";
        $res = $this->select_all($sql);
        return $res;
    }
    public function editExpediente(int $id_expediente)
    {
        $sql = "SELECT * FROM expediente WHERE id_expediente = $id_expediente";
        $res = $this->select($sql);
        return $res;
    }

    public function selectDocumento()
    {
        $sql = "SELECT indice_05 FROM expediente ORDER BY id_expediente;";
        $res = $this->select($sql);
        return $res;
    }

    public function selectRegistros(string $indice_01, string $indice_04)
    {
        $sql = "SELECT sub.cant_documentos, v.id_proceso, v.id_expediente, v.indice_01, v.indice_02, v.indice_03,
        v.indice_04,  v.indice_05,  v.indice_06, v.fecha_indexado,
        v.ruta_original, v.estado, v.paginas, v.ubicacion, v.fecha_vencimiento, v.version, v.firma_digital
        FROM v_expedientes v
        JOIN (
            SELECT indice_01, COUNT(*) AS cant_documentos
            FROM v_expedientes
            WHERE estado = 'Activo'
            GROUP BY indice_01
        ) AS sub ON v.indice_01 = sub.indice_01
        WHERE v.estado = 'Activo' AND v.indice_01 = ?
        ORDER BY v.id_expediente ASC;";
        $res = $this->select_all($sql, [$indice_01]);
        return $res;
    }

    // public function selectRegistros2(String $indice_01, string $nombre_tipoDoc)
    // {
    // $id_grupo = $_SESSION['id_grupo'];
    //  $sql = "SELECT sub.cant_documentos, 
    //              v.id_proceso, 
    //              v.id_expediente,
    // 				  v.id_tipoDoc,
    //              v.nombre_tipoDoc,  
    //              v.indice_01, 
    //              v.indice_02, 
    //              v.indice_03,
    //              v.indice_04,  
    //              v.indice_05,  
    //              v.indice_06, 
    //              v.fecha_indexado,
    //              v.ruta_original, 
    //              v.estado, 
    //              v.paginas, 
    //              v.ubicacion, 
    //              v.fecha_vencimiento, 
    //              v.version, 
    //              v.firma_digital
    //       FROM v_expedientes v
    //       JOIN (
    //           SELECT indice_01, COUNT(*) AS cant_documentos
    //           FROM v_expedientes
    //           WHERE estado = 'Activo'
    //           GROUP BY indice_01
    //       ) AS sub ON v.indice_01 = sub.indice_01
    //       JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
    //       WHERE v.estado = 'Activo' 
    //         AND pd.id_grupo = $id_grupo 
    //         AND v.nombre_tipoDoc = '$nombre_tipoDoc'
    //         AND v.indice_01 = '$indice_01'
    //       ORDER BY v.id_expediente ASC;";
    //       $res = $this->select_all($sql);
    //       return $res;
    //   }

    public function selectRegistros2(string $indice_01, string $nombre_tipoDoc, string $termino)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
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
          AND v.nombre_tipoDoc = ?
          AND v.indice_01 = ?";
        $params = [$id_grupo, $nombre_tipoDoc, $indice_01];
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

    // En Models/ExpedientesModel.php

    public function selectRegistrosApi(string $id_grupo_a_filtrar, string $indice_01, string $nombre_tipoDoc, string $termino)
    {
        // NO USA: $id_grupo = $_SESSION['id_grupo'];
        // 1. Configurar el WHERE para el filtro de grupo
        $where_group = "";
        if ($id_grupo_a_filtrar !== 'ALL') {
            // Filtro normal por grupo
            $id_grupo = intval($id_grupo_a_filtrar); // Limpieza de variable
            $where_group = "AND pd.id_grupo = '$id_grupo'";
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
          AND v.nombre_tipoDoc = '$nombre_tipoDoc'
          AND v.indice_01 = '$indice_01'";
        if (!empty($termino)) {
            $sql .= " AND (
                v.indice_01 LIKE '$termino%' OR
                v.indice_02 LIKE '$termino%' OR
                v.indice_03 LIKE '$termino%' OR
                v.indice_04 LIKE '$termino%'
            )";
        }
        $sql .= " ORDER BY v.id_expediente ASC";
        // ... (resto de la ejecución)
        $res = $this->select_all($sql);
        return $res;
    }

    public function obtener_datos_adicionales(string $ruta)
    {
        $sql = "SELECT ubicacion, fecha_indexado, version, fecha_vencimiento 
                FROM v_expedientes 
                WHERE ruta_original = '$ruta'";
        $res = $this->select($sql);
        return $res;
    }
    public function buscarExpediente(string $indice_04)
    {
        $sql = "SELECT 
                v.indice_01,
                COUNT(*) AS cant_documentos,
                MIN(v.id_expediente) AS id_expediente,
                MIN(v.indice_01) AS indice_01,
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
                MIN(v.fecha_vencimiento) AS fecha_vencimiento,
                MIN(v.firma_digital) AS firma_digital
            FROM v_expedientes v
            JOIN (
                SELECT indice_01, COUNT(*) AS documentos_activos
                FROM v_expedientes
                WHERE estado = 'Activo'
                GROUP BY indice_01
                ) AS sub ON v.indice_01 = sub.indice_01
            WHERE v.estado = 'Activo' AND v.indice_04 LIKE ?
            GROUP BY v.indice_01
            ORDER BY MIN(v.id_expediente) DESC;";
        $res = $this->select_all($sql, ['%' . $indice_04 . '%']);
        return $res;
    }

    /* public function selectTipoDoc()
    {
        $id_grupo = $_SESSION['id_grupo'];
        $sql = "SELECT a.id, a.id_grupo, b.descripcion, a.id_tipoDoc, c.nombre_tipoDoc, b.estado AS estado_grupo
                FROM permisos_documentos a, usu_grupo b, tipo_documento c WHERE a.id_grupo=b.id_grupo AND a.id_tipoDoc=c.id_tipoDoc 
                AND a.estado='ACTIVO' AND a.id_grupo=$id_grupo ORDER BY id ASC;";
        $res = $this->select_all($sql);
        return $res;
    } */

    public function selectTipoDoc()
    {
        if (!isset($_SESSION['id_grupo'])) {
            return [];
        }
        $id_grupo = intval(value: $_SESSION['id_grupo']);
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
                FROM permisos_documentos a INNER JOIN usu_grupo b 
                ON a.id_grupo = b.id_grupo INNER JOIN tipo_documento c 
                ON a.id_tipoDoc = c.id_tipoDoc
                WHERE a.estado = 'ACTIVO' AND a.id_grupo = $id_grupo
                ORDER BY a.id ASC;";
        $res = $this->select_all($sql);
        return $res;
    }


    /*     public function buscarExpediente2(string $indice_04_pattern)
    {
        // Obtener el grupo de usuario desde la sesión
        $id_grupo = $_SESSION['id_grupo'];

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
                    MIN(v.ruta_original) AS ruta_original,
                    MIN(v.paginas) AS paginas,
                    MIN(v.ubicacion) AS ubicacion,
                    MIN(v.version) AS version,
                    MIN(v.estado) AS estado,
                    MIN(v.fecha_actualizacion) AS fecha_actualizacion,
                    MIN(v.firma_digital) AS firma_digital
                FROM v_expedientes v
                JOIN (
                    SELECT indice_01, COUNT(*) AS documentos_activos
                    FROM v_expedientes
                    WHERE estado = 'Activo'
                    GROUP BY indice_01
                ) AS sub ON v.indice_01 = sub.indice_01
                JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
                WHERE v.estado = 'Activo' 
                AND pd.id_grupo = $id_grupo
                AND v.indice_04 LIKE '%$indice_04_pattern%'
                GROUP BY v.indice_01
                ORDER BY MIN(v.id_expediente) DESC;";

        $res = $this->select_all($sql);
        return $res;
    } */

    public function buscarExpediente2(int $id_tipoDoc, string $indice_01_pattern, string $indice_02_pattern, string $indice_03_pattern, string $indice_04_pattern)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
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
                MIN(v.ruta_original) AS ruta_original,
                MIN(v.paginas) AS paginas,
                MIN(v.ubicacion) AS ubicacion,
                MIN(v.version) AS version,
                MIN(v.estado) AS estado,
                
                MIN(v.firma_digital) AS firma_digital
            FROM v_expedientes v
            JOIN (
                SELECT indice_01, COUNT(*) AS documentos_activos
                FROM v_expedientes
                WHERE estado = 'Activo'
                GROUP BY indice_01
            ) AS sub ON v.indice_01 = sub.indice_01
            JOIN permisos_documentos pd ON pd.id_tipoDoc = v.id_tipoDoc
            WHERE v.estado = 'Activo' 
            AND pd.id_grupo = ?
            AND (? = 0 OR pd.id_tipoDoc = ?)
            AND v.indice_01 LIKE ?
            AND v.indice_02 LIKE ?
            AND v.indice_03 LIKE ?
            AND v.indice_04 LIKE ?
            GROUP BY v.indice_01
            ORDER BY MIN(v.id_expediente) DESC;";
        $res = $this->select_all($sql, [
            $id_grupo,
            $id_tipoDoc,
            $id_tipoDoc,
            '%' . $indice_01_pattern . '%',
            '%' . $indice_02_pattern . '%',
            '%' . $indice_03_pattern . '%',
            '%' . $indice_04_pattern . '%'
        ]);
        return $res;
    }

    public function buscarExpedientePorTermino(int $id_tipoDoc, string $termino)
    {
        $id_grupo = intval($_SESSION['id_grupo'] ?? 0);
        $sql = "SELECT 
                COUNT(*) AS cant_documentos,
                MIN(v.indice_01) AS indice_01,
                MIN(v.id_expediente) AS id_expediente,
                MIN(v.id_tipoDoc) AS id_tipoDoc,
                MIN(v.nombre_tipoDoc) AS nombre_tipoDoc,
                MIN(v.indice_02) AS indice_02,
                MIN(v.indice_03) AS indice_03,
                MIN(v.indice_04) AS indice_04,
                MIN(v.indice_05) AS indice_05,
                MIN(v.indice_06) AS indice_06,
                MIN(v.fecha_indexado) AS fecha_indexado,
                MIN(v.paginas) AS paginas,
                MIN(v.estado) AS estado
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
            AND (? = 0 OR pd.id_tipoDoc = ?)
            AND (
                v.indice_01 LIKE ? OR
                v.indice_02 LIKE ? OR
                v.indice_03 LIKE ? OR
                v.indice_04 LIKE ?
            )
            GROUP BY v.indice_01, v.id_tipoDoc
            ORDER BY MIN(v.id_expediente) DESC;";
        $terminoLike = '%' . $termino . '%';
        return $this->select_all($sql, [
            $id_grupo,
            $id_tipoDoc,
            $id_tipoDoc,
            $terminoLike,
            $terminoLike,
            $terminoLike,
            $terminoLike
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
        $sql = "SELECT * FROM expediente WHERE id_expediente = $id_expediente";
        $res = $this->select($sql);
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
        $this->desde = $desde;
        $this->hasta = $hasta;
        $sql = "SELECT * FROM expediente WHERE indice_01 BETWEEN '$desde' AND '$hasta' order by indice_01 asc";
        $res = $this->select_all($sql);
        return $res;
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
                    AND pd.id_grupo = ? AND v.fecha_indexado BETWEEN ? AND ?
                GROUP BY v.indice_01
                ORDER BY MIN(v.id_expediente) ASC;";
        $res = $this->select_all($sql, [$id_grupo, $desde, $hasta]);
        return $res;
    }

    public function reporteExpedientesDuplic()
    {
        $sql = "SELECT indice_01, indice_04, COUNT(*) AS cantidad
        FROM v_expedientes WHERE estado='Activo'
        GROUP BY indice_01, indice_04
        HAVING COUNT(*) > 1 ;";
        $res = $this->select_all($sql);
        return $res;
    }

    /*     public function selectMetadatosById($id_expediente)
    {
        $sql = "SELECT 
                    id_proceso,
                    id_expediente,
                    id_tipoDoc,
                    nombre_tipoDoc,
                    indice_01,
                    indice_02,
                    indice_03,
                    indice_04,
                    fecha_indexado,
                    ruta_original,
                    estado,
                    paginas,
                    ubicacion,
                    fecha_vencimiento,
                    version,
                    firma_digital
                FROM v_expedientes
                WHERE id_expediente = $id_expediente AND estado = 'Activo'";
        return $this->select($sql); // Método que ejecuta consultas preparadas
    }
 */
}
