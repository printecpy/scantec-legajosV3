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

}
?>