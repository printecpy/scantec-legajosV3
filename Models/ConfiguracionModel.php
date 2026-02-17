<?php
class ConfiguracionModel extends Mysql
{
    //protected $id, $nombre, $telefono, $direccion;
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
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectSMTP_datos()
    {
        $sql = "SELECT host, username, password, smtpsecure, remitente, nombre_remitente, PORT, estado 
            FROM smtp_datos where estado='activo' limit 1;";
        $res = $this->select($sql);
        return $res;
    }

    public function actualizarConfiguracion(string $nombre, string $telefono, string $direccion, string $correo, int $total_pag, int $id)
    {
        $return = "";
        $this->nombre = $nombre;
        $this->telefono = $telefono;
        $this->direccion = $direccion;
        $this->correo = $correo;
        $this->total_pag = $total_pag;
        $this->id = $id;
        $query = "UPDATE configuracion SET nombre=?, telefono=?, direccion=?, correo=?, total_pag=? WHERE id=?";
        $data = array($this->nombre, $this->telefono, $this->direccion, $this->correo, $this->total_pag, $this->id);
        $resul = $this->update($query, $data);
        $return = $resul;
        return $return;
    }

    public function insertarServSMTP(
        string $host,
        string $username,
        string $password,
        string $smtpsecure,
        string $port
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->smtpsecure = $smtpsecure;
        $this->port = $port;
        $query = "INSERT INTO smtp_datos (host, username, password, smtpsecure, port, estado) 
            VALUES (?, ?, ?, ?, ?, 'activo');";
        $data = array($host, $username, $password, $smtpsecure, $port);
        $this->insert($query, $data);
        return true;
    }

    public function insertarServLDAP(
        string $ldapHost,
        string $ldapPort,
        string $ldapBaseDn,
        string $ldapUser,
        string $ldapPass,
        string $fecha_registro
    ) {
        $this->ldapHost = $ldapHost;
        $this->ldapPort = $ldapPort;
        $this->ldapBaseDn = $ldapBaseDn;
        $this->ldapUser = $ldapUser;
        $this->ldapPass = $ldapPass;
        $this->fecha_registro = $fecha_registro;
        $query = "INSERT INTO ldap_datos (ldapHost, ldapPort, ldapUser, ldapPass, ldapBaseDn, fecha_registro, estado) 
            VALUES (?, ?, ?, ?, ?, ?, 'activo');";
        $data = array($ldapHost, $ldapPort, $ldapUser, $ldapPass, $ldapBaseDn, $fecha_registro);
        $this->insert($query, $data);
        return true;
    }

    public function selectLDAP_sincronizar()
    {
        $sql = "SELECT * FROM ldap_datos where estado='activo';";
        $res = $this->select($sql);
        return $res;
    }

    public function backupDatabase()
    {
        require_once 'Config/Config.php';
        $host = HOST;
        $user = DB_USER;
        $pass = PASS;
        $dbname = BD;
        $backup_dir = BACKUP_PATH; // Usamos la ruta del config
        // Verificar que la ruta exista o crearla
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }
        // Generar nombre del archivo con fecha/hora
        $backup_file = $backup_dir . $dbname . '_' . date("Y-m-d_H-i-s") . '.sql';
        // Ruta completa al archivo mysqldump.exe
        $mysqldumpPath = '"C:\\Program Files\\MySQL\\MySQL Server 8.1\\bin\\mysqldump.exe"';

        $command = "$mysqldumpPath --opt --host=$host --user=$user --password=$pass $dbname > $backup_file";

        // Ejecutar el comando
        system($command, $output);

        if ($output == 0) {
            return "Respaldo completado exitosamente.";
        } else {
            return "Error al realizar el respaldo.";
        }
    }

    public function RestoreDatabase($backup_file)
    {
        require_once 'Config/Config.php';
        $host = HOST;
        $user = DB_USER;
        $pass = PASS;
        $dbname = BD;

        // Ruta completa al archivo mysql.exe
        $mysqlPath = '"C:\\Program Files\\MySQL\\MySQL Server 8.1\\bin\\mysql.exe"';

        // Comando para restaurar la base de datos
        $command = "$mysqlPath --host=$host --user=$user --password=$pass $dbname < $backup_file";

        // Ejecutar el comando
        system($command, $output);

        // Validar el resultado
        if ($output == 0) {
            return "Restauración completada exitosamente.";
        } else {
            return "Error al realizar la restauración.";
        }
    }

    public function ejecutarRespaldo()
    {
        $scriptPath = "C:\\xampp\\htdocs\\scantec2\\scrips\\backup.bat";
        pclose(popen("start /B " . $scriptPath, "r"));
        return true;
    }
}
