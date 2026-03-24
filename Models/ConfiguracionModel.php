<?php
class ConfiguracionModel extends Mysql
{
    private $db;
    public function __construct()
    {
        parent::__construct();
        $this->db = new Mysql();
    }

    public function selectConfiguracion()
    {
        $sql = "SELECT 
                c.id, c.nombre, c.telefono, c.direccion, c.correo, c.total_pag,
                (SELECT COUNT(*) FROM usuarios WHERE estado_usuario != 'Activo') as total_usuarios
            FROM configuracion c 
            LIMIT 1;";
        return $this->select_all($sql);
    }

    public function selectLDAP_datos()
    {
        $sql = "SELECT id, ldapHost, ldapPort, ldapUser, ldapPass, ldapBaseDn, fecha_registro, fecha_sincronizacion, estado 
            FROM ldap_datos where estado='activo';";
        return $this->select_all($sql);
    }

    public function selectSMTP_datos()
    {
        $sql = "SELECT host, username, password, smtpsecure, remitente, nombre_remitente, PORT, estado 
            FROM smtp_datos where estado='activo' limit 1;";
        return $this->select_all($sql);
    }

    public function actualizarConfiguracion(string $nombre, string $telefono, string $direccion, string $correo, int $total_pag, int $id)
    {
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->correo = $correo;
        $this->total_pag = $total_pag;
        $this->id = $id;
        $query = "UPDATE configuracion SET nombre=?, telefono=?, direccion=?, correo=?, total_pag=? WHERE id=?";
        $data = array($this->nombre, $this->telefono, $this->direccion, $this->correo, $this->total_pag, $this->id);
        return $this->update($query, $data);
    }

    // Insertar nueva configuración y desactivar las anteriores
    public function insertarServSMTP(string $host, string $username, string $password, string $smtpsecure, string $port, string $remitente, string $nombre_remitente) 
    {
        // 1. Primero ponemos TODO en 'inactivo' para evitar duplicidad
        $sql_update = "UPDATE smtp_datos SET estado = 'inactivo'";
        $this->update($sql_update, array());

        // 2. Insertamos el nuevo como 'activo'
        $query = "INSERT INTO smtp_datos (host, username, password, smtpsecure, port, remitente, nombre_remitente, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')";
        
        $arrData = array($host, $username, $password, $smtpsecure, $port, $remitente, $nombre_remitente);
        $request = $this->insert($query, $arrData);
        return $request;
    }

    // Obtener la configuración activa
    public function getActiveSMTP()
    {
        $sql = "SELECT * FROM smtp_datos WHERE estado = 'activo' ORDER BY id DESC LIMIT 1";
        return $this->select($sql);
    }

    // Método para apagar el servicio SMTP
    public function desactivarSMTP()
    {
        $sql = "UPDATE smtp_datos SET estado = 'inactivo'";
        return $this->update($sql, array());
    }

    public function insertarServLDAP(string $ldapHost, string $ldapPort, string $ldapBaseDn, string $ldapUser, string $ldapPass, string $fecha_registro) 
    {
        $this->ldapHost = $ldapHost;
        $this->ldapPort = $ldapPort;
        $this->ldapBaseDn = $ldapBaseDn;
        $this->ldapUser = $ldapUser;
    
        $this->ldapPass = stringEncryption($ldapPass); 
        
        $this->fecha_registro = $fecha_registro;
        
        $query = "INSERT INTO ldap_datos (ldapHost, ldapPort, ldapUser, ldapPass, ldapBaseDn, fecha_registro, estado) 
            VALUES (?, ?, ?, ?, ?, ?, 'activo');";
        $data = array($this->ldapHost, $this->ldapPort, $this->ldapUser, $this->ldapPass, $this->ldapBaseDn, $this->fecha_registro);
        $this->insert($query, $data);
        return true;
    }

    public function selectLDAP_sincronizar()
    {
        $sql = "SELECT * FROM ldap_datos where estado='activo';";
        return $this->select($sql);
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

    private function getCampoRelacionTipoLegajo(): string
    {
        return $this->existeColumna('cfg_matriz_requisitos', 'id_tipo_legajo') ? 'id_tipo_legajo' : 'id_tipoDoc';
    }

    private function getCampoPoliticaActualizacionSql(): string
    {
        return $this->existeColumna('cfg_matriz_requisitos', 'politica_actualizacion')
            ? 'mr.politica_actualizacion'
            : "CASE WHEN mr.permite_reemplazo = 1 THEN 'REEMPLAZAR' ELSE 'NO_PERMITIR' END AS politica_actualizacion";
    }

    public function getCatalogoDocumentosLegajo()
    {
        $sql = "SELECT id_documento_maestro, nombre, codigo_interno, tiene_vencimiento,
                dias_vigencia_base, dias_alerta_previa, activo
                FROM cfg_catalogo_documentos
                ORDER BY activo DESC, nombre ASC";
        return $this->select_all($sql);
    }

    public function getTiposDocumentoLegajo()
    {
        if ($this->existeTabla('cfg_tipo_legajo')) {
            $campoRequiereSolicitud = $this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')
                ? 'requiere_nro_solicitud'
                : '0 AS requiere_nro_solicitud';
            $sql = "SELECT id_tipo_legajo AS id_tipoDoc, nombre AS nombre_tipoDoc, descripcion, activo, $campoRequiereSolicitud
                    FROM cfg_tipo_legajo
                    ORDER BY nombre ASC";
            return $this->select_all($sql);
        }

        $sql = "SELECT id_tipoDoc, nombre_tipoDoc
                FROM tipo_documento
                ORDER BY nombre_tipoDoc ASC";
        return $this->select_all($sql);
    }

    public function existeTipoLegajoPorNombre(string $nombre): bool
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return false;
        }

        $sql = "SELECT COUNT(*) AS total
                FROM cfg_tipo_legajo
                WHERE UPPER(nombre) = UPPER(?)";
        $result = $this->select($sql, [$nombre]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function insertarTipoLegajo(string $nombre, ?string $descripcion = null, int $activo = 1, int $requiereNroSolicitud = 0)
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return false;
        }

        if ($this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')) {
            $query = "INSERT INTO cfg_tipo_legajo (nombre, descripcion, activo, requiere_nro_solicitud) VALUES (?, ?, ?, ?)";
            return $this->insert($query, [$nombre, $descripcion, $activo, $requiereNroSolicitud]);
        }

        $query = "INSERT INTO cfg_tipo_legajo (nombre, descripcion, activo) VALUES (?, ?, ?)";
        return $this->insert($query, [$nombre, $descripcion, $activo]);
    }

    public function getTipoLegajoById(int $idTipoLegajo)
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return null;
        }

        $campoRequiereSolicitud = $this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')
            ? 'requiere_nro_solicitud'
            : '0 AS requiere_nro_solicitud';
        $sql = "SELECT id_tipo_legajo, nombre, descripcion, activo, $campoRequiereSolicitud
                FROM cfg_tipo_legajo
                WHERE id_tipo_legajo = ?";
        $result = $this->select($sql, [$idTipoLegajo]);
        if (!empty($result) && isset($result[0])) {
            return $result[0];
        }
        return $result;
    }

    public function actualizarTipoLegajo(int $idTipoLegajo, string $nombre, ?string $descripcion = null, int $activo = 1, int $requiereNroSolicitud = 0)
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return false;
        }

        if ($this->existeColumna('cfg_tipo_legajo', 'requiere_nro_solicitud')) {
            $query = "UPDATE cfg_tipo_legajo
                    SET nombre = ?, descripcion = ?, activo = ?, requiere_nro_solicitud = ?
                    WHERE id_tipo_legajo = ?";
            return $this->update($query, [$nombre, $descripcion, $activo, $requiereNroSolicitud, $idTipoLegajo]);
        }

        $query = "UPDATE cfg_tipo_legajo
                SET nombre = ?, descripcion = ?, activo = ?
                WHERE id_tipo_legajo = ?";
        return $this->update($query, [$nombre, $descripcion, $activo, $idTipoLegajo]);
    }

    public function eliminarTipoLegajo(int $idTipoLegajo)
    {
        if (!$this->existeTabla('cfg_tipo_legajo')) {
            return false;
        }

        $campoRelacion = $this->getCampoRelacionTipoLegajo();
        $sql = "SELECT COUNT(*) AS total
                FROM cfg_matriz_requisitos
                WHERE $campoRelacion = ?";
        $result = $this->select($sql, [$idTipoLegajo]);
        if (!empty($result) && intval($result['total'] ?? 0) > 0) {
            return false;
        }

        $query = "DELETE FROM cfg_tipo_legajo WHERE id_tipo_legajo = ?";
        try {
            return $this->update($query, [$idTipoLegajo]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getMatrizRequisitosLegajo(int $idTipoDoc)
    {
        $campoRelacion = $this->getCampoRelacionTipoLegajo();
        $campoPoliticaActualizacion = $this->getCampoPoliticaActualizacionSql();

        if ($this->existeTabla('cfg_tipo_legajo')) {
            $sql = "SELECT mr.id_requisito, mr.$campoRelacion AS id_tipoDoc, mr.id_documento_maestro, mr.rol_vinculado,
                    mr.es_obligatorio, mr.orden_visual, mr.permite_reemplazo, $campoPoliticaActualizacion,
                    cd.nombre AS documento_nombre, cd.codigo_interno,
                    tl.nombre AS nombre_tipoDoc
                    FROM cfg_matriz_requisitos mr
                    INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                    INNER JOIN cfg_tipo_legajo tl ON tl.id_tipo_legajo = mr.$campoRelacion
                    WHERE mr.$campoRelacion = $idTipoDoc
                    ORDER BY mr.orden_visual ASC, mr.id_requisito ASC";
            return $this->select_all($sql);
        }

        $sql = "SELECT mr.id_requisito, mr.id_tipoDoc, mr.id_documento_maestro, mr.rol_vinculado,
                mr.es_obligatorio, mr.orden_visual, mr.permite_reemplazo, $campoPoliticaActualizacion,
                cd.nombre AS documento_nombre, cd.codigo_interno,
                td.nombre_tipoDoc
                FROM cfg_matriz_requisitos mr
                INNER JOIN cfg_catalogo_documentos cd ON cd.id_documento_maestro = mr.id_documento_maestro
                INNER JOIN tipo_documento td ON td.id_tipoDoc = mr.id_tipoDoc
                WHERE mr.id_tipoDoc = $idTipoDoc
                ORDER BY mr.orden_visual ASC, mr.id_requisito ASC";
        return $this->select_all($sql);
    }

    public function insertarCatalogoDocumentoLegajo(
        string $nombre,
        string $codigoInterno,
        int $tieneVencimiento,
        ?int $diasVigenciaBase,
        ?int $diasAlertaPrevia,
        int $activo
    ) {
        $query = "INSERT INTO cfg_catalogo_documentos
                (nombre, codigo_interno, tiene_vencimiento, dias_vigencia_base, dias_alerta_previa, activo)
                VALUES (?, ?, ?, ?, ?, ?)";
        $data = [
            $nombre,
            $codigoInterno !== '' ? $codigoInterno : null,
            $tieneVencimiento,
            $diasVigenciaBase,
            $diasAlertaPrevia,
            $activo
        ];
        return $this->insert($query, $data);
    }

    public function actualizarEstadoCatalogoDocumentoLegajo(int $idDocumentoMaestro, int $activo)
    {
        $query = "UPDATE cfg_catalogo_documentos SET activo = ? WHERE id_documento_maestro = ?";
        return $this->update($query, [$activo, $idDocumentoMaestro]);
    }

    public function getCatalogoDocumentoLegajoById(int $idDocumentoMaestro)
    {
        $sql = "SELECT id_documento_maestro, nombre, codigo_interno, tiene_vencimiento,
                dias_vigencia_base, dias_alerta_previa, activo
                FROM cfg_catalogo_documentos
                WHERE id_documento_maestro = ?";
        $result = $this->select($sql, [$idDocumentoMaestro]);
        if (!empty($result) && isset($result[0])) {
            return $result[0];
        }
        return $result;
    }

    public function actualizarCatalogoDocumentoLegajo(
        int $idDocumentoMaestro,
        string $nombre,
        string $codigoInterno,
        int $tieneVencimiento,
        ?int $diasVigenciaBase,
        ?int $diasAlertaPrevia,
        int $activo
    ) {
        $query = "UPDATE cfg_catalogo_documentos
                SET nombre = ?, codigo_interno = ?, tiene_vencimiento = ?,
                    dias_vigencia_base = ?, dias_alerta_previa = ?, activo = ?
                WHERE id_documento_maestro = ?";
        return $this->update($query, [
            $nombre,
            $codigoInterno !== '' ? $codigoInterno : null,
            $tieneVencimiento,
            $diasVigenciaBase,
            $diasAlertaPrevia,
            $activo,
            $idDocumentoMaestro
        ]);
    }

    public function existeMatrizRequisitoLegajo(int $idTipoDoc, int $idDocumentoMaestro, string $rolVinculado)
    {
        $campoRelacion = $this->getCampoRelacionTipoLegajo();
        $sql = "SELECT COUNT(*) AS total
                FROM cfg_matriz_requisitos
                WHERE $campoRelacion = ? AND id_documento_maestro = ? AND rol_vinculado = ?";
        $result = $this->select($sql, [$idTipoDoc, $idDocumentoMaestro, $rolVinculado]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function existeOtroMatrizRequisitoLegajo(int $idRequisito, int $idTipoDoc, int $idDocumentoMaestro, string $rolVinculado)
    {
        $campoRelacion = $this->getCampoRelacionTipoLegajo();
        $sql = "SELECT COUNT(*) AS total
                FROM cfg_matriz_requisitos
                WHERE id_requisito != ? AND $campoRelacion = ? AND id_documento_maestro = ? AND rol_vinculado = ?";
        $result = $this->select($sql, [$idRequisito, $idTipoDoc, $idDocumentoMaestro, $rolVinculado]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function insertarMatrizRequisitoLegajo(
        int $idTipoDoc,
        int $idDocumentoMaestro,
        string $rolVinculado,
        int $esObligatorio,
        int $ordenVisual,
        int $permiteReemplazo,
        string $politicaActualizacion = 'REEMPLAZAR'
    ) {
        $campoRelacion = $this->getCampoRelacionTipoLegajo();
        $usaPoliticaActualizacion = $this->existeColumna('cfg_matriz_requisitos', 'politica_actualizacion');
        $politicaActualizacion = strtoupper(trim($politicaActualizacion));
        $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
        if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
            $politicaActualizacion = $permiteReemplazo === 1 ? 'REEMPLAZAR' : 'NO_PERMITIR';
        }

        if ($campoRelacion === 'id_tipo_legajo' && $this->existeColumna('cfg_matriz_requisitos', 'id_tipoDoc')) {
            if ($usaPoliticaActualizacion) {
                $query = "INSERT INTO cfg_matriz_requisitos
                        (id_tipoDoc, id_tipo_legajo, id_documento_maestro, rol_vinculado, es_obligatorio, orden_visual, permite_reemplazo, politica_actualizacion)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                return $this->insert($query, [
                    $idTipoDoc,
                    $idTipoDoc,
                    $idDocumentoMaestro,
                    $rolVinculado,
                    $esObligatorio,
                    $ordenVisual,
                    $permiteReemplazo,
                    $politicaActualizacion
                ]);
            }

            $query = "INSERT INTO cfg_matriz_requisitos
                    (id_tipoDoc, id_tipo_legajo, id_documento_maestro, rol_vinculado, es_obligatorio, orden_visual, permite_reemplazo)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            return $this->insert($query, [
                $idTipoDoc,
                $idTipoDoc,
                $idDocumentoMaestro,
                $rolVinculado,
                $esObligatorio,
                $ordenVisual,
                $permiteReemplazo
            ]);
        }

        if ($usaPoliticaActualizacion) {
            $query = "INSERT INTO cfg_matriz_requisitos
                    ($campoRelacion, id_documento_maestro, rol_vinculado, es_obligatorio, orden_visual, permite_reemplazo, politica_actualizacion)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            return $this->insert($query, [
                $idTipoDoc,
                $idDocumentoMaestro,
                $rolVinculado,
                $esObligatorio,
                $ordenVisual,
                $permiteReemplazo,
                $politicaActualizacion
            ]);
        }

        $query = "INSERT INTO cfg_matriz_requisitos
                ($campoRelacion, id_documento_maestro, rol_vinculado, es_obligatorio, orden_visual, permite_reemplazo)
                VALUES (?, ?, ?, ?, ?, ?)";
        return $this->insert($query, [
            $idTipoDoc,
            $idDocumentoMaestro,
            $rolVinculado,
            $esObligatorio,
            $ordenVisual,
            $permiteReemplazo
        ]);
    }

    public function eliminarMatrizRequisitoLegajo(int $idRequisito)
    {
        $query = "DELETE FROM cfg_matriz_requisitos WHERE id_requisito = ?";
        return $this->update($query, [$idRequisito]);
    }

    public function actualizarMatrizRequisitoLegajo(
        int $idRequisito,
        int $idDocumentoMaestro,
        string $rolVinculado,
        int $esObligatorio,
        int $ordenVisual,
        int $permiteReemplazo,
        string $politicaActualizacion = 'REEMPLAZAR'
    ) {
        $politicaActualizacion = strtoupper(trim($politicaActualizacion));
        $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
        if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
            $politicaActualizacion = $permiteReemplazo === 1 ? 'REEMPLAZAR' : 'NO_PERMITIR';
        }

        if ($this->existeColumna('cfg_matriz_requisitos', 'politica_actualizacion')) {
            $query = "UPDATE cfg_matriz_requisitos
                    SET id_documento_maestro = ?, rol_vinculado = ?, es_obligatorio = ?,
                        orden_visual = ?, permite_reemplazo = ?, politica_actualizacion = ?
                    WHERE id_requisito = ?";
            return $this->update($query, [
                $idDocumentoMaestro,
                $rolVinculado,
                $esObligatorio,
                $ordenVisual,
                $permiteReemplazo,
                $politicaActualizacion,
                $idRequisito
            ]);
        }

        $query = "UPDATE cfg_matriz_requisitos
                SET id_documento_maestro = ?, rol_vinculado = ?, es_obligatorio = ?,
                    orden_visual = ?, permite_reemplazo = ?
                WHERE id_requisito = ?";
        return $this->update($query, [
            $idDocumentoMaestro,
            $rolVinculado,
            $esObligatorio,
            $ordenVisual,
            $permiteReemplazo,
            $idRequisito
        ]);
    }

    public function backupDatabase()
    {
        require_once 'Config/Config.php';
        $host = HOST;
        $user = DB_USER;
        $pass = PASS;
        $dbname = BD;
        
        // CORRECCIÓN: Usar constante o ruta por defecto, pero asegurarse que exista
        $backup_dir = defined('BACKUP_PATH') ? BACKUP_PATH : dirname(__DIR__) . "\\backups\\";
        
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        $date = date("Y-m-d_H-i-s");
        $filename = $dbname . "_" . $date . ".sql";
        $backup_file = $backup_dir . $filename;

        // CORRECCIÓN: Ruta de mysqldump con comillas por si hay espacios
        // NOTA: Verifica que esta ruta exista en tu servidor. 
        // Idealmente debería estar en una constante en Config.php
        $mysqldumpPath = '"C:\\Program Files\\MySQL\\MySQL Server 8.1\\bin\\mysqldump.exe"';

        // Comando con manejo de errores y comillas en rutas
        $command = "$mysqldumpPath --opt --host=$host --user=$user --password=$pass $dbname > \"$backup_file\"";

        $output = null;
        $result_code = null;
        exec($command, $output, $result_code);

        if ($result_code === 0) {
            return ['status' => true, 'msg' => "Respaldo creado correctamente.", 'file' => $filename];
        } else {
            return ['status' => false, 'msg' => "Error al crear respaldo (Código: $result_code)."];
        }
    }

    public function RestoreDatabase($backup_file_path)
    {
        require_once 'Config/Config.php';
        $host = HOST;
        $user = DB_USER;
        $pass = PASS;
        $dbname = BD;

        // CORRECCIÓN: Ruta de mysql.exe con comillas
        $mysqlPath = '"C:\\Program Files\\MySQL\\MySQL Server 8.1\\bin\\mysql.exe"';

        // Validar que el archivo existe antes de intentar
        if (!file_exists($backup_file_path)) {
             return ['status' => false, 'msg' => "El archivo temporal no se encuentra."];
        }

        // Comando seguro: comillas alrededor del archivo de entrada
        $command = "$mysqlPath --host=$host --user=$user --password=$pass $dbname < \"$backup_file_path\"";

        $output = null;
        $result_code = null;
        exec($command, $output, $result_code);

        if ($result_code === 0) {
            return ['status' => true, 'msg' => "Base de datos restaurada exitosamente."];
        } else {
            return ['status' => false, 'msg' => "Error crítico al restaurar. Código: $result_code."];
        }
    }

    public function ejecutarRespaldo($ruta_destino)
    {
        try {
            // 1. Validar destino
            if (!is_dir($ruta_destino)) {
                if (!@mkdir($ruta_destino, 0777, true)) {
                    return ['status' => false, 'msg' => 'La ruta destino no existe y no pudo ser creada.'];
                }
            }

            if (!defined('RUTA_BASE')) {
                return ['status' => false, 'msg' => 'La constante RUTA_BASE no está definida.'];
            }

            // 2. Dividir la ruta para que Windows Explorer lea bien el ZIP
            // Si RUTA_BASE es "C:/xampp/scantec_storage/"
            $ruta_base_limpia = rtrim(RUTA_BASE, '/\\');
            $directorio_padre = dirname($ruta_base_limpia); // Queda: "C:/xampp"
            $nombre_carpeta   = basename($ruta_base_limpia); // Queda: "scantec_storage"

            if (!is_dir($ruta_base_limpia)) {
                return ['status' => false, 'msg' => 'La carpeta origen no existe: ' . $ruta_base_limpia];
            }

            // 3. Crear el comando exacto
            $fecha = date('Ymd_His');
            $archivo_zip = rtrim($ruta_destino, '/\\') . DIRECTORY_SEPARATOR . "backup_documentos_{$fecha}.zip";

            // tar comprimirá la carpeta completa desde afuera
            $comando = 'tar -a -c -f ' . escapeshellarg($archivo_zip) . ' -C ' . escapeshellarg($directorio_padre) . ' ' . escapeshellarg($nombre_carpeta);

            // 4. Ejecutar en segundo plano
            exec('start "" /B ' . $comando);
            
            return ['status' => true, 'msg' => 'El respaldo físico se inició. Archivo: backup_documentos_' . $fecha . '.zip'];

        } catch (Throwable $e) {
            return ['status' => false, 'msg' => 'Error en el modelo: ' . $e->getMessage()];
        }
    }

    // ============================================================
    // CRUD para cfg_relaciones (Tipos de relación en legajos)
    // ============================================================

    public function getRelaciones()
    {
        if (!$this->existeTabla('cfg_relaciones')) {
            return [];
        }
        $sql = "SELECT id_relacion, nombre, activo, orden
                FROM cfg_relaciones
                ORDER BY orden ASC, nombre ASC";
        return $this->select_all($sql);
    }

    public function getRelacionesActivas()
    {
        if (!$this->existeTabla('cfg_relaciones')) {
            // Fallback: devolver las opciones clásicas hardcodeadas
            return [
                ['nombre' => 'TITULAR'],
                ['nombre' => 'CONYUGE'],
                ['nombre' => 'CODEUDOR']
            ];
        }
        $sql = "SELECT id_relacion, nombre
                FROM cfg_relaciones
                WHERE activo = 1
                ORDER BY orden ASC, nombre ASC";
        return $this->select_all($sql);
    }

    public function existeRelacionPorNombre(string $nombre): bool
    {
        if (!$this->existeTabla('cfg_relaciones')) {
            return false;
        }
        $sql = "SELECT COUNT(*) AS total FROM cfg_relaciones WHERE UPPER(nombre) = UPPER(?)";
        $result = $this->select($sql, [$nombre]);
        return !empty($result) && intval($result['total'] ?? 0) > 0;
    }

    public function insertarRelacion(string $nombre, int $orden = 0)
    {
        if (!$this->existeTabla('cfg_relaciones')) {
            return false;
        }
        if ($orden <= 0) {
            $sql = "SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM cfg_relaciones";
            $result = $this->select($sql);
            $orden = intval($result['siguiente'] ?? 1);
        }
        $query = "INSERT INTO cfg_relaciones (nombre, activo, orden) VALUES (?, 1, ?)";
        return $this->insert($query, [strtoupper(trim($nombre)), $orden]);
    }

    public function cambiarEstadoRelacion(int $idRelacion, int $activo)
    {
        $query = "UPDATE cfg_relaciones SET activo = ? WHERE id_relacion = ?";
        return $this->update($query, [$activo, $idRelacion]);
    }

    public function eliminarRelacion(int $idRelacion)
    {
        // 1. Obtener el nombre de la relación
        $sql = "SELECT r.nombre FROM cfg_relaciones r WHERE r.id_relacion = ?";
        $relacion = $this->select($sql, [$idRelacion]);
        if (empty($relacion)) {
            return false;
        }
        $nombre = $relacion[0]['nombre'] ?? ($relacion['nombre'] ?? '');
        if ($nombre === '') {
            return false;
        }

        // 2. Verificar si está en uso en la matriz de requisitos (reglas)
        $sqlMatriz = "SELECT COUNT(*) AS total FROM cfg_matriz_requisitos WHERE rol_vinculado = ?";
        $enMatriz = $this->select($sqlMatriz, [$nombre]);
        if (!empty($enMatriz) && intval($enMatriz['total'] ?? 0) > 0) {
            return 'EN_USO_MATRIZ';
        }

        // 3. Verificar si está en uso en legajos ya armados (documentos cargados)
        if ($this->existeTabla('legajos_documentos')) {
            $sqlLegajos = "SELECT COUNT(*) AS total FROM legajos_documentos WHERE rol_vinculado = ?";
            $enLegajos = $this->select($sqlLegajos, [$nombre]);
            if (!empty($enLegajos) && intval($enLegajos['total'] ?? 0) > 0) {
                return 'EN_USO_LEGAJOS';
            }
        }

        // 4. Si no está en uso en ningún lado, eliminar
        $query = "DELETE FROM cfg_relaciones WHERE id_relacion = ?";
        try {
            return $this->update($query, [$idRelacion]);
        } catch (Throwable $e) {
            return false;
        }
    }

    // ============================================================
    // CRUD para cfg_politicas_actualizacion
    // ============================================================

    private function asegurarTablaPoliticas(): void
    {
        if ($this->existeTabla('cfg_politicas_actualizacion')) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS cfg_politicas_actualizacion (
            id_politica INT AUTO_INCREMENT PRIMARY KEY,
            clave VARCHAR(30) NOT NULL,
            etiqueta VARCHAR(60) NOT NULL,
            descripcion VARCHAR(150) DEFAULT NULL,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            orden INT NOT NULL DEFAULT 0,
            UNIQUE KEY uk_clave (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->update($sql, []);

        $insert = "INSERT IGNORE INTO cfg_politicas_actualizacion (clave, etiqueta, descripcion, activo, orden) VALUES
            ('REEMPLAZAR', 'Solo reemplazar', 'El archivo nuevo reemplaza al anterior', 1, 1),
            ('UNIR_AL_INICIO', 'Solo agregar al inicio', 'El archivo nuevo se agrega al inicio del existente', 1, 2),
            ('UNIR_AL_FINAL', 'Solo agregar al final', 'El archivo nuevo se agrega al final del existente', 1, 3),
            ('NO_PERMITIR', 'No permitir actualizar', 'Una vez cargado, no se permite modificar', 1, 4),
            ('CONSULTAR', 'Consultar en cada archivo', 'Pregunta al usuario qué hacer en cada carga', 1, 5)";
        $this->update($insert, []);
    }

    public function getPoliticasActualizacion()
    {
        $this->asegurarTablaPoliticas();
        $sql = "SELECT id_politica, clave, etiqueta, descripcion, activo, orden
                FROM cfg_politicas_actualizacion
                ORDER BY orden ASC, clave ASC";
        return $this->select_all($sql);
    }

    public function getPoliticasActualizacionActivas()
    {
        $this->asegurarTablaPoliticas();
        $sql = "SELECT id_politica, clave, etiqueta
                FROM cfg_politicas_actualizacion
                WHERE activo = 1
                ORDER BY orden ASC";
        return $this->select_all($sql);
    }

    public function cambiarEstadoPolitica(int $idPolitica, int $activo)
    {
        $query = "UPDATE cfg_politicas_actualizacion SET activo = ? WHERE id_politica = ?";
        return $this->update($query, [$activo, $idPolitica]);
    }

}
?>
