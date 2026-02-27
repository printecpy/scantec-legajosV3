<?php
class UsuariosModel extends Mysql
{
    protected $id, $clave, $nombre, $usuario, $correo, $id_rol, $estado_usuario;
    public function __construct()
    {
        parent::__construct();
    }
    public function selectUsuarios()
    {
        $sql = "SELECT * FROM usuarios WHERE estado_usuario = 'ACTIVO' order by 1";
        $res = $this->select_all($sql);
        return $res;
    }

    public function getPermisosByRol(int $id_rol): array
    {
        // 1. Consulta SQL para seleccionar los permisos activos de un rol
        $sql = "SELECT controlador, accion, permitido FROM rol_permisos WHERE 
            id_rol = ? AND estado = 'activo'";
        // NOTA: Usar consultas preparadas (el '?' se reemplaza por $id_rol)
        $arrPermisos = $this->select($sql, [$id_rol]);
        $permisosMapeados = [];
        // 2. Mapear los resultados al formato clave-valor deseado: 
        //    'Controlador/Accion' => Permitido (1 o 0)
        if (!empty($arrPermisos)) {
            foreach ($arrPermisos as $p) {
                $clave = $p['controlador'] . '/' . $p['accion'];
                // El valor 'permitido' (1 o 0) se usa directamente
                $permisosMapeados[$clave] = (int) $p['permitido'];
            }
        }
        return $permisosMapeados;
        // Ejemplo de salida: ['Expedientes/listar' => 1, 'Usuarios/insertar' => 0]
    }
    public function selectUsuariosActivos()
    {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        $servidor = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $sql = "SELECT a.id_visita, a.fecha, a.ip, a.servidor, a.id, b.nombre, a.fecha_cierre, 
        a.estado FROM visitas a, usuarios b WHERE a.id=b.id AND a.servidor NOT LIKE '%$servidor%' 
        AND a.ip NOT LIKE '%$ip%'AND estado='ACTIVO' ORDER BY a.fecha ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectRoles()
    {
        $sql = "SELECT * FROM roles";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectGrupos()
    {
        $sql = "SELECT * FROM usu_grupo;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectTipoDoc()
    {
        $sql = "SELECT * FROM tipo_documento;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function verificarUsuarioExistente(string $usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = ? AND estado_usuario='ACTIVO';";
        $data = [$usuario];
        $res = $this->select($sql, $data);
        return $res;
    }

    /**
     * Verifica las credenciales de un usuario activo.
     * Busca el hash de la contraseña y lo compara con la clave proporcionada.
     * * @param string $usuario Nombre de usuario.
     * @param string $clave Contraseña proporcionada por el usuario (sin hashear).
     * @return array|false Retorna el array asociativo del usuario si las credenciales son válidas, o false si falla.
     */
    public function verificarCredenciales(string $usuario, string $clave)
    {
        $sql = "SELECT id, id_rol, nombre, usuario, clave, id_grupo FROM usuarios WHERE usuario = ? AND estado_usuario='ACTIVO';";
        $data = [$usuario];
        $res = $this->select($sql, $data);
        //Verificar si se encontró al usuario
        if (empty($res)) {
            return false; // Usuario no existe o está inactivo
        }
        //Extraer los datos de la primera (y única) fila
        $usuarioDb = $res[0];
        //Verificar la contraseña usando password_verify (comparación segura con el hash)
        // $usuarioDb['password'] debe contener el hash bcrypt
        if (password_verify($clave, $usuarioDb['clave'])) {
            // Éxito: Retorna todos los datos necesarios (id, id_rol, nombre, etc.)
            return $usuarioDb;
        }
        // Falla: Contraseña incorrecta
        return false;
    }

    public function selectPerDoc()
    {
        $sql = "SELECT a.id, a.id_grupo, b.descripcion, a.id_tipoDoc, c.nombre_tipoDoc, b.estado AS estado_grupo, a.estado AS estado_permiso
                FROM permisos_documentos a, usu_grupo b, tipo_documento c WHERE a.id_grupo=b.id_grupo AND a.id_tipoDoc=c.id_tipoDoc 
                 ORDER BY estado_permiso ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function insertarUsuarios(string $nombre, string $usuario, string $clave, string $id_rol, string $grupo, string $fuente_registro, string $email)
    {
        try {
            $this->nombre = $nombre;
            $this->usuario = $usuario;
            $this->clave = $clave;
            $this->id_rol = $id_rol;
            $this->grupo = $grupo;
            $this->fuente_registro = $fuente_registro;
            $this->email = $email;

            $query = "INSERT INTO usuarios(nombre, usuario, clave, id_rol, estado_usuario, id_grupo, fuente_registro, email) VALUES (?,?,?,?,'ACTIVO',?,?,?);";
            $data = array($this->nombre, $this->usuario, $this->clave, $this->id_rol, $this->grupo, $this->fuente_registro, $this->email);

            $resul = $this->insert($query, $data);
            return $resul;

        } catch (\PDOException $e) {
            return false;
        }
    }
    public function editarUsuarios(int $id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = $id";
        $res = $this->select($sql);
        if (empty($res)) {
            return [];
        }
        return $res;
    }

    public function reportUsuario()
    {
        $sql = "SELECT * FROM usuarios";
        $res = $this->select_all($sql);
        if (empty($res)) {
            $res = 0;
        }
        return $res;
    }

    public function reporteUsuarios(string $desde, string $hasta)
    {
        $sql = "SELECT * FROM usuarios WHERE nombre BETWEEN ? AND ? ;";
        $data = [$desde, $hasta];
        $res = $this->select($sql, $data);
        return $res;
    }
    public function actualizarUsuarios(string $nombre, string $usuario, int $id_rol, int $id)
    {
        $return = "";
        $this->nombre = $nombre;
        $this->usuario = $usuario;
        $this->id_rol = $id_rol;
        $this->id = $id;
        $query = "UPDATE usuarios SET nombre=?, usuario=?, id_rol=? WHERE id=?";
        $data = array($this->nombre, $this->usuario, $this->id_rol, $this->id);
        $resul = $this->update($query, $data);
        $return = $resul;
        return $return;
    }
    public function eliminarUsuarios(int $id): bool
    {
        $query = "UPDATE usuarios SET estado_usuario = 'INACTIVO' WHERE id = ?";
        $data = [$id];
        return $this->update($query, $data);
    }
    public function selectUsuario(string $usuario)
    {
        $sql = "SELECT a.id, a.nombre, a.usuario, a.clave, a.id_rol, b.descripcion as roles, a.estado_usuario, c.id_grupo,
        c.descripcion as grupo, a.email FROM usuarios a, roles b, usu_grupo c WHERE a.id_rol=b.id_rol AND a.id_grupo=c.id_grupo
        and a.usuario = ? AND a.estado_usuario = 'ACTIVO';";
        $data = [$usuario];
        $res = $this->select($sql, $data);
        if (!empty($res) && is_array($res) && isset($res[0])) {
            return $res[0];
        }
        return false;
    }

    public function contarUsuariosActivos()
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE estado_usuario = 'ACTIVO';";
        $res = $this->select($sql);
        return $res;
    }

    public function insertarGrupo(string $descripcion)
    {
        $query = "INSERT INTO usu_grupo (descripcion) VALUES (?)";
        $data = [$descripcion];
        $this->insert($query, $data);
        return true;
    }

    public function asignarPermiso(int $id_grupo, int $id_tipoDoc)
    {
        $this->id_grupo = $id_grupo;
        $this->id_tipoDoc = $id_tipoDoc;
        $query = "INSERT INTO permisos_documentos (id_grupo, id_tipoDoc, estado) VALUES (?, ?, 'ACTIVO');";
        $data = array($this->id_grupo, $this->id_tipoDoc);
        $this->insert($query, $data);
        return true;
    }

    public function verificarPermisoExistente(int $id_grupo, int $id_tipoDoc)
    {
        $this->id_grupo = $id_grupo;
        $this->id_tipoDoc = $id_tipoDoc;
        $sql = "SELECT COUNT(*) as total FROM permisos_documentos WHERE id_grupo = $id_grupo AND id_tipoDoc = $id_tipoDoc;";
        $res = $this->select($sql);
        return $res;
    }

    public function insertarTipoDoc(string $nombre_tipoDoc, string $indice_1, string $indice_2, string $indice_3, string $indice_4, string $indice_5, string $indice_6)
    {
        $this->nombre_tipoDoc = $nombre_tipoDoc;
        $this->indice_1 = $indice_1;
        $this->indice_2 = $indice_2;
        $this->indice_3 = $indice_3;
        $this->indice_4 = $indice_4;
        $this->indice_5 = $indice_5;
        $this->indice_6 = $indice_6;
        $query = "INSERT INTO tipo_documento (nombre_tipoDoc, indice_1, indice_2, indice_3, indice_4, indice_5, indice_6) VALUES (?,?,?,?,?,?,?);";
        $data = array($this->nombre_tipoDoc, $this->indice_1, $this->indice_2, $this->indice_3, $this->indice_4, $this->indice_5, $this->indice_6);
        $this->insert($query, $data);
        return true;
    }

    public function eliminarPermiso(int $id_permiso)
    {
        $this->id_permiso = $id_permiso;
        $query = "UPDATE permisos_documentos SET estado = 'INACTIVO' WHERE id=?;";
        $data = array($this->id_permiso);
        $this->insert($query, $data);
        return true;
    }

    // Reactivar Permiso (Nuevo)
    public function reactivarPermiso(int $id_permiso)
    {
        $sql = "UPDATE permisos_documentos SET estado = 'ACTIVO' WHERE id = ?";
        $arrData = array($id_permiso);
        $request = $this->update($sql, $arrData);
        return $request;
    }

    public function reingresarUsuarios(int $id)
    {
        $return = "";
        $this->id = $id;
        $query = "UPDATE usuarios SET estado_usuario = 'ACTIVO' WHERE id=?";
        $data = array($this->id);
        $resul = $this->update($query, $data);
        $return = $resul;
        return $return;
    }

    // Obtener solo la contraseña actual (Más ligero y seguro)
    public function getPassword(int $id)
    {
        $sql = "SELECT clave FROM usuarios WHERE id = $id";
        $request = $this->select($sql);
        // Retornamos directamente el array o vacío
        return $request;
    }

    // Actualizar contraseña y fecha de modificación
    public function cambiarContra(string $clave, int $id)
    {
        $sql = "UPDATE usuarios SET clave = ?, clave_actualizacion = NOW() WHERE id = ?";
        $arrData = array($clave, $id);
        $request = $this->update($sql, $arrData);
        return $request;
    }

    public function selectDatos()
    {
        $sql = "SELECT * FROM configuracion LIMIT 1";
        $res = $this->select($sql);
        if (isset($res[0])) {
            return $res[0];
        }
        if (empty($res)) {
            return [
                'nombre' => 'SIN DATOS DE EMPRESA', // Mensaje informativo
                'telefono' => '',
                'direccion' => '',
                'correo' => '',
                'mensaje' => ''
            ];
        }
        return $res;
    }
    public function bloquearUsuarios(string $usuario)
    {
        $return = "";
        $this->usuario = $usuario;
        $query = "UPDATE usuarios SET estado_usuario = 'BLOQUEADO', timestamp = NOW() WHERE usuario=?";
        $data = array($this->usuario);
        $resul = $this->update($query, $data);
        $return = $resul;
        return $return;
    }
    public function bloquarPC_IP(string $usuario, string $motivo)
    {
        date_default_timezone_set('America/Asuncion');
        $this->usuario = $usuario;
        $this->motivo = $motivo;
        $ip = $_SERVER["REMOTE_ADDR"] ?? "";
        $query = "INSERT INTO intentos_login_fallidos (usuario, direccion_ip, nombre_pc, motivo) 
              VALUES (?, ?, ?, ?)";
        // Corregir el nombre de la variable para el nombre de PC
        $nombre_pc = gethostname(); // Suponiendo que quieres obtener el nombre del PC local
        $data = array($this->usuario, $ip, $nombre_pc, $this->motivo); // Corregir la asignación de valores
        $resul = $this->update($query, $data);
        return $resul;
    }
    //REGISTRAR VISITA (Login)
    public function registrarVisita(int $id_usuario)
    {
        date_default_timezone_set('America/Asuncion');
        $fecha = date("Y-m-d H:i:s");
        $ip = $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0";
        $servidor = gethostbyaddr($_SERVER['REMOTE_ADDR']) ?? "Desconocido";
        // CRÍTICO: Capturamos el ID único de esta sesión específica
        $sessionId = session_id();
        // Insertamos el session_id para poder identificar esta conexión luego
        // Asumo que la columna FK de tu usuario en la tabla visitas se llama 'id' según tu código anterior
        $query = "INSERT INTO visitas (fecha, ip, servidor, id, estado, session_id) VALUES (?, ?, ?, ?, 'ACTIVO', ?)";
        $data = array($fecha, $ip, $servidor, $id_usuario, $sessionId);
        $resul = $this->insert($query, $data);
        return $resul;
    }
    // ACTUALIZAR VISITA (Logout / Timeout)
    public function actualizarVisita(int $id_usuario)
    {
        date_default_timezone_set('America/Asuncion');
        // CRÍTICO: Recuperamos el ID de la sesión ACTUAL que está intentando salir
        // Esto es lo que permite que Juan salga sin cerrarle la sesión a Pedro
        $sessionId = session_id();
        // Buscamos SOLO la fila que tenga ESTE session_id.
        $query = "UPDATE visitas SET 
                    fecha_cierre = NOW(), 
                    estado = 'INACTIVO' 
                  WHERE session_id = ? AND id = ? AND estado = 'ACTIVO'";
        $data = array($sessionId, $id_usuario);
        $resul = $this->update($query, $data);
        return $resul;
    }
    // SUMAR CONEXIÓN
    public function conteoInicioSesion(int $id)
    {
        $query = "UPDATE usuarios SET 
                    ultimo_acceso = NOW(), 
                    cantidad_inicio = cantidad_inicio + 1 
                  WHERE id = ?";
        $data = array($id);
        $resul = $this->update($query, $data);
        return $resul;
    }
    // RESTAR CONEXIÓN
    public function restarInicioSesion(int $id)
    {
        // GREATEST evita números negativos si hay algún desajuste.
        $query = "UPDATE usuarios SET 
                    ultimo_acceso = NOW(), 
                    cantidad_inicio = GREATEST(0, cantidad_inicio - 1) 
                  WHERE id = ?";
        $data = array($id);
        $resul = $this->update($query, $data);
        return $resul;
    }

    public function verificarEstadoSesion(string $session_id)
    {
        // Optimizamos usando LIMIT 1 para respuesta rápida
        $sql = "SELECT id FROM visitas 
                WHERE session_id = '$session_id' AND estado = 'ACTIVO' 
                LIMIT 1";
        $request = $this->select($sql);
        return !empty($request); // TRUE si está activo, FALSE si te patearon
    }

    public function actualizarVisitas(int $id_visita)
    {
        $this->id_visita = $id_visita;
        // Consulta para "matar" sesión en BD
        $query = "UPDATE visitas SET fecha_cierre=NOW(), estado='INACTIVO' WHERE id_visita = ?";
        $data = array($this->id_visita);

        $request = $this->update($query, $data);
        return $request;
    }

    // Obtener config
    public function getLdapConfigById($id)
    {
        $sql = "SELECT * FROM ldap_datos WHERE id = $id AND estado = 'activo';";
        return $this->select($sql);
    }

    // Función inteligente Insertar/Actualizar
    public function sincronizarUsuarioLDAP($usuario, $nombre, $email, $password, $id_rol)
    {
        // 1. Buscar si existe por username
        $sql = "SELECT id FROM usuarios WHERE usuario = '$usuario'";
        $existe = $this->select($sql);

        if (!empty($existe)) {
            // ACTUALIZAR: Si el usuario ya existe, actualizamos su nombre y correo (por si cambiaron en el AD)
            // NO actualizamos la contraseña ni el rol para no pisar configuraciones locales
            $id_user = $existe['id'];
            $sql_update = "UPDATE usuarios SET nombre=?, email=?, clave_actualizacion=NOW() WHERE id=?";
            $arrData = array($nombre, $email, $id_user);
            $this->update($sql_update, $arrData);
            return 'update';
        } else {
            // INSERTAR: Nuevo usuario
            // Nota el campo 'fuente_registro' con valor 'LDAP'
            $sql_insert = "INSERT INTO usuarios (usuario, nombre, email, clave, id_rol, estado_usuario, fuente_registro, fecha_registro, clave_actualizacion) 
                           VALUES (?, ?, ?, ?, 'ACTIVO', 1, 'LDAP', NOW(), NOW())";
            $arrData = array($usuario, $nombre, $email, $password, $id_rol);
            $this->insert($sql_insert, $arrData);
            return 'insert';
        }
    }

}
