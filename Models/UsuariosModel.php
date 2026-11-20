<?php
class UsuariosModel extends Mysql
{
    protected $id, $clave, $nombre, $usuario, $correo, $id_rol, $estado_usuario;
    public function __construct()
    {
        parent::__construct();
        $this->ensureDepartamentosTable();
        $this->ensureDepartamentoColumn();
        $this->ensureIdDepartamentoColumn();
        $this->ensureForzarCambioClaveColumn();
        $this->migrarDepartamentosUsuarios();
    }

    /**
     * Asegura que existan los roles base (1 = Administrador Scantec, 2 = Administrador).
     * Es una migración: se ejecuta una sola vez por base de datos (marca en funcionalidades_parametros).
     * NO asigna roles a usuarios: el superadmin se crea solo por consola (tools/crear_superadmin.php).
     */
    public function asegurarJerarquiaRolesScantec(): void
    {
        try {
            $marca = $this->select_all(
                "SELECT valor FROM funcionalidades_parametros WHERE clave = 'migracion_roles_base_v1' LIMIT 1"
            );
            if (!empty($marca)) {
                return;   // ya se aplicó en esta base
            }

            if (empty($this->select_all("SHOW COLUMNS FROM roles LIKE 'estado'"))) {
                $this->update("ALTER TABLE roles ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'activo'", []);
            }

            $this->insert(
                "INSERT IGNORE INTO roles (id_rol, descripcion, estado) VALUES (1, 'Administrador Scantec', 'activo'), (2, 'Administrador', 'activo')",
                []
            );

            $this->insert(
                "INSERT INTO funcionalidades_parametros (clave, valor) VALUES ('migracion_roles_base_v1', NOW())",
                []
            );
        } catch (\Throwable $e) {
            error_log('No se pudo aplicar la migración de roles base: ' . $e->getMessage());
        }
    }

    public function obtenerRolUsuarioPorId(int $idUsuario): int
    {
        if ($idUsuario <= 0) {
            return 0;
        }

        try {
            $row = $this->select("SELECT id_rol FROM usuarios WHERE id = ? LIMIT 1", [$idUsuario]);
            return intval($row['id_rol'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Migración única: grupo "SIN DOCUMENTOS" y departamento "SIN DEPARTAMENTO", ambos de sistema (protegidos).
     * El rol 1 queda en ellos: administra, pero no accede al contenido salvo asignación explícita.
     */
    public function asegurarEstructuraSinAcceso(): void
    {
        try {
            $marca = $this->select_all("SELECT valor FROM funcionalidades_parametros WHERE clave = 'migracion_sin_acceso_v1' LIMIT 1");
            if (!empty($marca)) {
                return;
            }

            foreach (['usu_grupo', 'departamentos'] as $tabla) {
                if (empty($this->select_all("SHOW COLUMNS FROM {$tabla} LIKE 'es_sistema'"))) {
                    $this->update("ALTER TABLE {$tabla} ADD COLUMN es_sistema TINYINT(1) NOT NULL DEFAULT 0", []);
                }
            }

            if ($this->idGrupoSinDocumentos() === 0) {
                $this->insert("INSERT INTO usu_grupo (descripcion, estado, es_sistema) VALUES ('SIN DOCUMENTOS', 'ACTIVO', 1)", []);
            }
            if ($this->idDepartamentoSinAsignar() === 0) {
                // 'nombre' es único: si un departamento común ya usa el nombre, el de sistema lleva otro
                $ocupado = $this->select_all("SELECT id_departamento FROM departamentos WHERE nombre = 'SIN DEPARTAMENTO' LIMIT 1");
                $nombre = empty($ocupado) ? 'SIN DEPARTAMENTO' : 'SIN DEPARTAMENTO (SISTEMA)';
                $this->insert("INSERT INTO departamentos (nombre, estado, es_sistema) VALUES (?, 'ACTIVO', 1)", [$nombre]);
            }
            $idGrupo = $this->idGrupoSinDocumentos();
            $idDepto = $this->idDepartamentoSinAsignar();
            if ($idGrupo === 0 || $idDepto === 0) {
                error_log("Migración sin acceso: no se pudo crear o encontrar el grupo ({$idGrupo}) o el departamento ({$idDepto}) de sistema");
                return;   // no se marca la migración: se reintenta en el próximo login
            }

            // El grupo de sistema nunca tiene tipos de documento
            $this->update("DELETE FROM permisos_documentos WHERE id_grupo = ?", [$idGrupo]);

            // Rol 1: administrativo, sin acceso a contenido
            $this->update("UPDATE usuarios SET id_grupo = ?, id_departamento = ? WHERE id_rol = 1", [$idGrupo, $idDepto]);
            $this->update("UPDATE roles SET id_departamento = ? WHERE id_rol = 1", [$idDepto]);

            $this->insert("INSERT INTO funcionalidades_parametros (clave, valor) VALUES ('migracion_sin_acceso_v1', NOW())", []);
        } catch (\Throwable $e) {
            error_log('No se pudo aplicar la migración de grupo y departamento sin acceso: ' . $e->getMessage());
        }
    }

    public function idGrupoSinDocumentos(): int
    {
        try {
            $r = $this->select_all("SELECT id_grupo FROM usu_grupo WHERE es_sistema = 1 ORDER BY id_grupo LIMIT 1");
            return intval($r[0]['id_grupo'] ?? 0);
        } catch (\Throwable $e) {
            return 0;   // la columna todavía no existe (antes de la migración)
        }
    }

    public function idDepartamentoSinAsignar(): int
    {
        try {
            $r = $this->select_all("SELECT id_departamento FROM departamentos WHERE es_sistema = 1 ORDER BY id_departamento LIMIT 1");
            return intval($r[0]['id_departamento'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function esGrupoSistema(int $idGrupo): bool
    {
        try {
            $r = $this->select_all("SELECT es_sistema FROM usu_grupo WHERE id_grupo = ? LIMIT 1", [$idGrupo]);
            return intval($r[0]['es_sistema'] ?? 0) === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function esDepartamentoSistema(int $idDepartamento): bool
    {
        try {
            $r = $this->select_all("SELECT es_sistema FROM departamentos WHERE id_departamento = ? LIMIT 1", [$idDepartamento]);
            return intval($r[0]['es_sistema'] ?? 0) === 1;
        } catch (\Throwable $e) {
            return false;
        }
    }


    private function ensureDepartamentosTable(): void
    {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS departamentos (
                        id_departamento INT NOT NULL AUTO_INCREMENT,
                        nombre VARCHAR(100) NOT NULL,
                        estado VARCHAR(20) NOT NULL DEFAULT 'ACTIVO',
                        PRIMARY KEY (id_departamento),
                        UNIQUE KEY uk_departamentos_nombre (nombre)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
            $this->insert($sql, []);
        } catch (\Throwable $e) {
            // No interrumpimos la carga si aun no esta lista la base.
        }
    }

    private function ensureDepartamentoColumn(): void
    {
        try {
            $column = $this->select_all("SHOW COLUMNS FROM usuarios LIKE 'departamento'");
            if (empty($column)) {
                $this->delete("ALTER TABLE usuarios ADD COLUMN departamento VARCHAR(100) NULL DEFAULT NULL AFTER nombre");
            }
        } catch (\Throwable $e) {
            // Si la tabla aun no existe o la conexion no esta disponible, no interrumpimos la carga.
        }
    }

    private function ensureIdDepartamentoColumn(): void
    {
        try {
            $column = $this->select_all("SHOW COLUMNS FROM usuarios LIKE 'id_departamento'");
            if (empty($column)) {
                $this->delete("ALTER TABLE usuarios ADD COLUMN id_departamento INT NULL DEFAULT NULL AFTER departamento");
            }
        } catch (\Throwable $e) {
            // Si la tabla aun no existe o la conexion no esta disponible, no interrumpimos la carga.
        }
    }

    private function ensureForzarCambioClaveColumn(): void
    {
        try {
            $column = $this->select_all("SHOW COLUMNS FROM usuarios LIKE 'forzar_cambio_clave'");
            if (empty($column)) {
                $this->delete("ALTER TABLE usuarios ADD COLUMN forzar_cambio_clave TINYINT(1) NOT NULL DEFAULT 0 AFTER clave_actualizacion");
            }
        } catch (\Throwable $e) {
            // Si la tabla aun no existe o la conexion no esta disponible, no interrumpimos la carga.
        }
    }

    private function migrarDepartamentosUsuarios(): void
    {
        try {
            $usuarios = $this->select_all("SELECT id, departamento, id_departamento FROM usuarios");
            foreach ($usuarios as $usuario) {
                $idUsuario = intval($usuario['id'] ?? 0);
                $nombreDepartamento = trim((string) ($usuario['departamento'] ?? ''));
                $idDepartamento = intval($usuario['id_departamento'] ?? 0);

                if ($idUsuario <= 0) {
                    continue;
                }

                if ($idDepartamento <= 0 && $nombreDepartamento !== '') {
                    $idDepartamento = $this->resolverDepartamentoIdPorNombre($nombreDepartamento, true);
                    if ($idDepartamento > 0) {
                        $this->update(
                            "UPDATE usuarios SET id_departamento = ? WHERE id = ?",
                            [$idDepartamento, $idUsuario]
                        );
                    }
                } elseif ($idDepartamento > 0 && $nombreDepartamento === '') {
                    $nombreResuelto = $this->obtenerNombreDepartamentoPorId($idDepartamento);
                    if ($nombreResuelto !== '') {
                        $this->update(
                            "UPDATE usuarios SET departamento = ? WHERE id = ?",
                            [$nombreResuelto, $idUsuario]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // No interrumpimos la carga si falla la migracion silenciosa.
        }

        try {
            $total = $this->select("SELECT COUNT(*) AS total FROM departamentos");
            if (intval($total['total'] ?? 0) === 0) {
                $this->insert("INSERT INTO departamentos (nombre, estado) VALUES (?, 'ACTIVO')", ['General']);
            }
        } catch (\Throwable $e) {
            // No interrumpimos la carga.
        }
    }
    public function selectUsuarios()
    {
        $sql = "SELECT u.*, COALESCE(d.nombre, u.departamento) AS departamento
                FROM usuarios u
                LEFT JOIN departamentos d ON d.id_departamento = u.id_departamento
                ORDER BY CASE WHEN u.estado_usuario = 'ACTIVO' THEN 0 ELSE 1 END, u.id ASC";
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
        $servidor = gethostbyaddr($_SERVER['REMOTE_ADDR'] ?? '') ?: '';
        $sql = "SELECT a.id_visita, a.fecha, a.ip, a.servidor, a.id, b.nombre, a.fecha_cierre, 
        a.estado FROM visitas a, usuarios b WHERE a.id=b.id AND a.servidor NOT LIKE ? 
        AND a.ip NOT LIKE ? AND estado='ACTIVO' ORDER BY a.fecha ASC;";
        $res = $this->select_all($sql, ['%' . $servidor . '%', '%' . $ip . '%']);
        return $res;
    }

    public function selectRoles()
    {
        $sql = "SELECT * FROM roles";
        $res = $this->select_all($sql);
        return $res;
    }

    public function selectRolesVisiblesPara(int $idRolActual): array
    {
        $roles = $this->selectRoles();
        if ($idRolActual <= 0) {
            return $roles;
        }

        return array_values(array_filter($roles, static function ($rol) use ($idRolActual) {
            return intval($rol['id_rol'] ?? 0) >= $idRolActual;
        }));
    }

    public function puedeGestionarRolObjetivo(int $idRolActual, int $idRolObjetivo): bool
    {
        if ($idRolActual <= 0 || $idRolObjetivo <= 0) {
            return false;
        }

        return $idRolObjetivo >= $idRolActual;
    }

    public function selectRolesRegistrables(): array
    {
        $sql = "SELECT * FROM roles WHERE id_rol <> 1 ORDER BY descripcion ASC";
        return $this->select_all($sql);
    }

    public function selectDepartamentos(): array
    {
        try {
            $sql = "SELECT * FROM departamentos WHERE estado = 'ACTIVO' ORDER BY nombre ASC";
            return $this->select_all($sql);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function selectTodosDepartamentos(): array
    {
        try {
            $sql = "SELECT d.*, (SELECT COUNT(*) FROM usuarios u WHERE u.id_departamento = d.id_departamento AND u.estado_usuario = 'ACTIVO' 
                    AND u.id_rol != 1) AS total_usuarios
                    FROM departamentos d
                    ORDER BY d.nombre ASC;";
            return $this->select_all($sql);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function obtenerNombreDepartamentoPorId(int $idDepartamento): string
    {
        if ($idDepartamento <= 0) {
            return '';
        }

        try {
            $row = $this->select("SELECT nombre FROM departamentos WHERE id_departamento = ?", [$idDepartamento]);
            if (is_array($row) && isset($row[0]) && is_array($row[0])) {
                $row = $row[0];
            }
            return trim((string) ($row['nombre'] ?? ''));
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function resolverDepartamentoIdPorNombre(string $nombre, bool $crearSiNoExiste = false): int
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return 0;
        }

        try {
            $row = $this->select("SELECT id_departamento FROM departamentos WHERE TRIM(nombre) = TRIM(?) LIMIT 1", [$nombre]);
            if (is_array($row) && isset($row[0]) && is_array($row[0])) {
                $row = $row[0];
            }
            $id = intval($row['id_departamento'] ?? 0);
            if ($id > 0) {
                return $id;
            }

            if ($crearSiNoExiste) {
                $this->insert("INSERT INTO departamentos (nombre, estado) VALUES (?, 'ACTIVO')", [$nombre]);
                $row = $this->select("SELECT id_departamento FROM departamentos WHERE TRIM(nombre) = TRIM(?) LIMIT 1", [$nombre]);
                if (is_array($row) && isset($row[0]) && is_array($row[0])) {
                    $row = $row[0];
                }
                return intval($row['id_departamento'] ?? 0);
            }
        } catch (\Throwable $e) {
            return 0;
        }

        return 0;
    }

    public function contarUsuariosPorDepartamento(int $idDepartamento): int
    {
        try {
            $row = $this->select("SELECT COUNT(*) AS total FROM usuarios WHERE id_departamento = ?", [$idDepartamento]);
            return intval($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function actualizarDepartamento(int $idDepartamento, string $nombre, string $estado = 'ACTIVO'): bool
    {
        $nombre = trim($nombre);
        $estado = strtoupper(trim($estado));
        if ($idDepartamento <= 0 || $nombre === '' || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            return false;
        }

        try {
            $existente = $this->select("SELECT id_departamento FROM departamentos WHERE TRIM(nombre) = TRIM(?) AND id_departamento <> ? LIMIT 1", [$nombre, $idDepartamento]);
            if (!empty($existente)) {
                return false;
            }

            $ok = (bool) $this->update("UPDATE departamentos SET nombre = ?, estado = ? WHERE id_departamento = ?", [$nombre, $estado, $idDepartamento]);
            if ($ok) {
                $this->update("UPDATE usuarios SET departamento = ? WHERE id_departamento = ?", [$nombre, $idDepartamento]);
            }
            return $ok;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function contarRolesPorDepartamento(int $idDepartamento): int
    {
        if ($idDepartamento <= 0) {
            return 0;
        }

        try {
            $tablaRoles = $this->select_all("SHOW TABLES LIKE 'roles'");
            if (empty($tablaRoles)) {
                return 0;
            }

            $columna = $this->select_all("SHOW COLUMNS FROM roles LIKE 'id_departamento'");
            if (empty($columna)) {
                return 0;
            }

            $row = $this->select("SELECT COUNT(*) AS total FROM roles WHERE id_departamento = ?", [$idDepartamento]);
            return intval($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function contarAccesosDepartamento(int $idDepartamento): int
    {
        if ($idDepartamento <= 0) {
            return 0;
        }

        try {
            $tablaAccesos = $this->select_all("SHOW TABLES LIKE 'funcionalidades_acceso_rol_departamento'");
            if (empty($tablaAccesos)) {
                return 0;
            }

            $row = $this->select("SELECT COUNT(*) AS total FROM funcionalidades_acceso_rol_departamento WHERE id_departamento = ?", [$idDepartamento]);
            return intval($row['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public function eliminarDepartamento(int $idDepartamento, string $accion = 'desactivar'): string
    {
        if ($idDepartamento <= 0) {
            return 'invalido';
        }

        $accion = strtolower(trim($accion));
        if (!in_array($accion, ['eliminar', 'desactivar'], true)) {
            $accion = 'desactivar';
        }

        try {
            $totalUsuarios = $this->contarUsuariosPorDepartamento($idDepartamento);
            $totalRoles = $this->contarRolesPorDepartamento($idDepartamento);
            $totalAccesos = $this->contarAccesosDepartamento($idDepartamento);
            $enUso = ($totalUsuarios + $totalRoles + $totalAccesos) > 0;

            $ok = $this->cambiarEstadoDepartamento($idDepartamento, 'INACTIVO');
            if (!$ok) {
                return 'error';
            }

            if ($accion === 'eliminar' && $enUso) {
                return 'desactivado_en_uso';
            }

            return 'desactivado';
        } catch (\Throwable $e) {
            return 'error';
        }
    }
    public function cambiarEstadoDepartamento(int $idDepartamento, string $estado): bool
    {
        $estado = strtoupper(trim($estado));
        if ($idDepartamento <= 0 || !in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            return false;
        }

        try {
            return (bool) $this->update("UPDATE departamentos SET estado = ? WHERE id_departamento = ?", [$estado, $idDepartamento]);
        } catch (\Throwable $e) {
            return false;
        }
    }
    public function selectGrupos()
    {
        $sql = "SELECT * FROM usu_grupo;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function obtenerGrupoRegistroPorDefecto(): int
    {
        return $this->idGrupoSinDocumentos();
    }

    public function selectTipoDoc()
    {
        $sql = "SELECT * FROM tipo_documento;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function verificarUsuarioExistente(string $usuario)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE usuario = ?;";
        $data = [$usuario];
        $res = $this->select($sql, $data);
        return $res;
    }

    public function verificarEmailExistente(string $email)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?;";
        return $this->select($sql, [$email]);
    }

    public function existeGrupoActivo(int $idGrupo): bool
    {
        if ($idGrupo <= 0) {
            return false;
        }
        $res = $this->select_all(
            "SELECT COUNT(*) AS total FROM usu_grupo WHERE id_grupo = ? AND (estado IS NULL OR UPPER(estado) = 'ACTIVO')",
            [$idGrupo]
        );
        return intval($res[0]['total'] ?? 0) > 0;
    }

    /**
     * Verifica las credenciales de un usuario activo.
     * Busca el hash de la contraseña y lo compara con la clave proporcionada.
     * * @param string $usuario Nombre de usuario.
     * @param string $clave contraseña proporcionada por el usuario (sin hashear).
     * @return array|false Retorna el array asociativo del usuario si las credenciales son válidas, o false si falla.
     */
    public function verificarCredenciales(string $usuario, string $clave)
    {
        $sql = "SELECT u.id, u.id_rol, u.nombre, COALESCE(d.nombre, u.departamento) AS departamento, u.usuario, u.clave, u.id_grupo, u.fuente_registro
            FROM usuarios u
            LEFT JOIN departamentos d ON d.id_departamento = u.id_departamento
            WHERE u.usuario = ? AND u.estado_usuario='ACTIVO';";
        $data = [$usuario];
        $res = $this->select($sql, $data);

        if (empty($res)) {
            return false;
        }

        $usuarioDb = $res[0];

        if (password_verify($clave, $usuarioDb['clave'])) {
            return $usuarioDb;
        }

        return false;
    }

    public function obtenerUsuarioApiPorId(int $idUsuario): array
    {
        $sql = "SELECT id, id_rol, id_grupo, fuente_registro
            FROM usuarios
            WHERE id = ? AND estado_usuario = 'ACTIVO' AND fuente_registro = 'API'
            LIMIT 1";
        $res = $this->select($sql, [$idUsuario]);
        if (is_array($res) && isset($res[0]) && is_array($res[0])) {
            $res = $res[0];
        }
        return !empty($res) && isset($res['id']) ? $res : [];
    }

    public function contarTiposDocumentoActivos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM tipo_documento WHERE activo = 1;";
        $res = $this->select($sql);
        if (isset($res['total'])) {
            return intval($res['total']);
        }
        if (isset($res[0]['total'])) {
            return intval($res[0]['total']);
        }
        return 0;
    }

    public function selectPerDoc()
    {
        $sql = "SELECT a.id, a.id_grupo, b.descripcion, a.id_tipoDoc, c.nombre_tipoDoc, b.estado AS estado_grupo, a.estado AS estado_permiso
                FROM permisos_documentos a, usu_grupo b, tipo_documento c WHERE a.id_grupo=b.id_grupo AND a.id_tipoDoc=c.id_tipoDoc 
                 ORDER BY estado_permiso ASC;";
        $res = $this->select_all($sql);
        return $res;
    }

    public function insertarUsuarios(string $nombre, int $idDepartamento, string $usuario, string $clave, string $id_rol, string $grupo, string $fuente_registro, string $email, string $estadoUsuario = 'ACTIVO', int $forzarCambioClave = 0)
    {
        try {
            $this->nombre = $nombre;
            $this->id_departamento = $idDepartamento > 0 ? $idDepartamento : null;
            $this->departamento = $this->obtenerNombreDepartamentoPorId($idDepartamento);
            $this->usuario = $usuario;
            $this->clave = $clave;
            $this->id_rol = $id_rol;
            $this->grupo = $grupo;
            $this->fuente_registro = $fuente_registro;
            $this->email = $email;
            $this->estado_usuario = strtoupper(trim($estadoUsuario)) === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO';
            $this->forzar_cambio_clave = $forzarCambioClave === 1 ? 1 : 0;

            $query = "INSERT INTO usuarios(nombre, departamento, id_departamento, usuario, clave, id_rol, estado_usuario, id_grupo, fuente_registro, email, forzar_cambio_clave) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
            $data = array($this->nombre, $this->departamento, $this->id_departamento, $this->usuario, $this->clave, $this->id_rol, $this->estado_usuario, $this->grupo, $this->fuente_registro, $this->email, $this->forzar_cambio_clave);

            $resul = $this->insert($query, $data);
            return $resul;

        } catch (\PDOException $e) {
            error_log('[insertarUsuarios] FALLÓ: ' . $e->getMessage());
            return false;
        }
    }

    public function insertarUsuarioPendiente(string $nombre, int $idDepartamento, string $usuario, string $clave, int $id_rol, int $id_grupo, string $email)
    {
        try {
            $departamento = $this->obtenerNombreDepartamentoPorId($idDepartamento);
            $query = "INSERT INTO usuarios(nombre, departamento, id_departamento, usuario, clave, id_rol, estado_usuario, id_grupo, fuente_registro, email)
                      VALUES (?,?,?,?,?,?,'INACTIVO',?,'scantec',?)";
            $data = [$nombre, $departamento, $idDepartamento > 0 ? $idDepartamento : null, $usuario, $clave, $id_rol, $id_grupo, $email];
            return $this->insert($query, $data);
        } catch (\PDOException $e) {
            return false;
        }
    }
    public function editarUsuarios(int $id)
    {
        $sql = "SELECT u.*, COALESCE(d.nombre, u.departamento) AS departamento
                FROM usuarios u
                LEFT JOIN departamentos d ON d.id_departamento = u.id_departamento
                WHERE u.id = ?";
        $res = $this->select($sql, [$id]);
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
    public function actualizarUsuarios(string $nombre, int $idDepartamento, string $usuario, int $id_rol, int $id_grupo, string $email, string $estado_usuario, int $id, string $clave = '')
    {
        $return = "";
        $this->nombre = $nombre;
        $this->id_departamento = $idDepartamento > 0 ? $idDepartamento : null;
        $this->departamento = $this->obtenerNombreDepartamentoPorId($idDepartamento);
        $this->usuario = $usuario;
        $this->id_rol = $id_rol;
        $this->id_grupo = $id_grupo;
        $this->email = $email;
        $this->estado_usuario = $estado_usuario;
        $this->id = $id;
        if ($clave !== '') {
            $query = "UPDATE usuarios SET nombre=?, departamento=?, id_departamento=?, usuario=?, clave=?, id_rol=?, id_grupo=?, email=?, estado_usuario=?, clave_actualizacion=NOW(), forzar_cambio_clave=0 WHERE id=?";
            $data = array($this->nombre, $this->departamento, $this->id_departamento, $this->usuario, $clave, $this->id_rol, $this->id_grupo, $this->email, $this->estado_usuario, $this->id);
        } else {
            $query = "UPDATE usuarios SET nombre=?, departamento=?, id_departamento=?, usuario=?, id_rol=?, id_grupo=?, email=?, estado_usuario=? WHERE id=?";
            $data = array($this->nombre, $this->departamento, $this->id_departamento, $this->usuario, $this->id_rol, $this->id_grupo, $this->email, $this->estado_usuario, $this->id);
        }
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
        $sql = "SELECT a.id, a.nombre, COALESCE(d.nombre, a.departamento) AS departamento, a.id_departamento, a.usuario, a.clave, a.id_rol, b.descripcion as roles, 
                       a.estado_usuario, c.id_grupo, c.descripcion as grupo, a.email, a.forzar_cambio_clave,
                       a.fuente_registro, a.clave_actualizacion
                FROM usuarios a
                LEFT JOIN roles b ON a.id_rol = b.id_rol
                LEFT JOIN usu_grupo c ON a.id_grupo = c.id_grupo
                LEFT JOIN departamentos d ON d.id_departamento = a.id_departamento
                WHERE TRIM(a.usuario) = TRIM(?)";

        $data = [$usuario];
        $res = $this->select($sql, $data);

        return (!empty($res) && isset($res[0])) ? $res[0] : [];
    }

    public function contarUsuariosActivos()
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE estado_usuario = 'ACTIVO' AND id_rol != 1;";
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
        $sql = "SELECT COUNT(*) as total FROM permisos_documentos WHERE id_grupo = ? AND id_tipoDoc = ?;";
        $res = $this->select($sql, [$id_grupo, $id_tipoDoc]);
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

    // --- MÉTODOS PARA GRUPO ---

    public function obtenerGrupo(int $id_grupo)
    {
        $sql = "SELECT * FROM usu_grupo WHERE id_grupo = ?";
        $res = $this->select($sql, [$id_grupo]);
        // Retornamos solo la primera fila si existe
        return !empty($res) ? $res[0] : [];
    }

    public function actualizarGrupo(int $id_grupo, string $descripcion)
    {
        $query = "UPDATE usu_grupo SET descripcion = ? WHERE id_grupo = ?";
        $data = [$descripcion, $id_grupo];
        // Asumo que tu framework interno usa un método update(), similar a insert()
        $this->update($query, $data);
        return true;
    }


    // --- MÉTODOS PARA TIPO DE DOCUMENTO ---

    public function obtenerTipoDoc(int $id_tipoDoc)
    {
        $sql = "SELECT * FROM tipo_documento WHERE id_tipoDoc = ?";
        $res = $this->select($sql, [$id_tipoDoc]);
        return !empty($res) ? $res[0] : [];
    }

    public function actualizarTipoDoc(int $id_tipoDoc, string $nombre, string $i1, string $i2, string $i3, string $i4, string $i5, string $i6)
    {
        $query = "UPDATE tipo_documento 
                  SET nombre_tipoDoc = ?, indice_1 = ?, indice_2 = ?, indice_3 = ?, indice_4 = ?, indice_5 = ?, indice_6 = ? 
                  WHERE id_tipoDoc = ?";
        $data = [$nombre, $i1, $i2, $i3, $i4, $i5, $i6, $id_tipoDoc];
        $this->update($query, $data);
        return true;
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

    public function reingresarUsuariosMasivo(array $ids): bool
    {
        $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }));

        if (empty($ids)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "UPDATE usuarios SET estado_usuario = 'ACTIVO' WHERE id IN ($placeholders)";
        return $this->update($query, $ids);
    }

    // Obtener solo la contrase�a actual (Más ligero y seguro)
    public function getPassword(int $id)
    {
        $sql = "SELECT clave FROM usuarios WHERE id = ?";
        $request = $this->select($sql, [$id]);
        // Retornamos directamente el array o vacío
        return $request;
    }

    // Actualizar contrase�a y fecha de modificación
    public function cambiarContra(string $clave, int $id)
    {
        $sql = "UPDATE usuarios SET clave = ?, clave_actualizacion = NOW(), forzar_cambio_clave = 0 WHERE id = ?";
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
                'nombre' => 'SIN DATOS DE EMPRESA',
                'telefono' => '',
                'direccion' => '',
                'correo' => '',
                'mensaje' => ''
            ];
        }
        return $res;
    }
    public function bloquearUsuarios($usuarioOId)
    {
        $valor = trim((string) $usuarioOId);
        if ($valor === '') {
            return false;
        }

        if (ctype_digit($valor)) {
            $query = "UPDATE usuarios SET estado_usuario = 'BLOQUEADO' WHERE id = ?";
            return $this->update($query, [intval($valor)]);
        }

        $query = "UPDATE usuarios SET estado_usuario = 'BLOQUEADO' WHERE usuario = ?";
        return $this->update($query, [$valor]);
    }
    public function bloquearPC_IP(string $usuario, string $motivo)
    {
        date_default_timezone_set('America/Asuncion');

        // Defensa en profundidad: largo máximo y sin caracteres de control.
        // El escape para HTML se hace en la vista con escapar(); esto solo evita basura y textos gigantes.
        $usuario = mb_substr(preg_replace('/[^\p{L}\p{N} ._@\-]/u', '?', $usuario), 0, 100);
        $motivo = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', ' ', $motivo), 0, 255);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $nombre_pc = gethostname();   // (hallazgo H-06: se revisa en la Fase 2, con la nueva auditoría)

        $query = "INSERT INTO intentos_login_fallidos (usuario, direccion_ip, nombre_pc, motivo) VALUES (?, ?, ?, ?)";
        return $this->update($query, [$usuario, $ip, $nombre_pc, $motivo]);
    }
    public function bloquarPC_IP(string $usuario, string $motivo)
    {
        return $this->bloquearPC_IP($usuario, $motivo);
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
        // Asumo que la columna FK de tu usuario en la tabla visitas se llama 'id' según tu C�digo anterior
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
        // CORRECCIÓN: usar prepared statement en lugar de concatenación directa
        $sql = "SELECT id FROM visitas 
                WHERE session_id = ? AND estado = 'ACTIVO' 
                LIMIT 1";
        $request = $this->select($sql, [$session_id]);
        return !empty($request); // TRUE si está activo, FALSE si te patearon
    }

    /**
     * Obtiene la visita activa de un session_id dado.
     * Usada por Controllers::validarSesionActivaEnBD() para verificar si el Admin cerró la sesión.
     */
    public function obtenerVisitaActivaPorSession(string $session_id)
    {
        $sql = "SELECT id_visita FROM visitas WHERE session_id = ? AND estado = 'ACTIVO' LIMIT 1";
        return $this->select($sql, [$session_id]);
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
        $sql = "SELECT * FROM ldap_datos WHERE id = ? AND estado = 'activo';";
        return $this->select($sql, [$id]);
    }

    public function ensureFechaRegistroUsuarios()
    {
        try {
            $columna = $this->select(
                "SELECT COUNT(*) AS total 
             FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'usuarios' 
               AND COLUMN_NAME = 'fecha_registro'"
            );

            $existe = !empty($columna) && intval($columna[0]['total'] ?? ($columna['total'] ?? 0)) > 0;

            if (!$existe) {
                $this->update(
                    "ALTER TABLE usuarios ADD COLUMN fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER fuente_registro",
                    []
                );
            }
        } catch (Throwable $e) {
            error_log('[ensureFechaRegistroUsuarios] FALLÓ: ' . $e->getMessage());
        }
    }

    public function sincronizarUsuarioLDAP($usuario, $nombre, $email, $password, $id_rol)
    {
        $idGrupoPorDefecto = defined('LDAP_ID_GRUPO_DEFECTO') ? intval(LDAP_ID_GRUPO_DEFECTO) : 1;

        $sql = "SELECT id FROM usuarios WHERE usuario = ?";
        $existe = $this->select($sql, [$usuario]);

        if (!empty($existe)) {
            $id_user = $existe[0]['id'] ?? null;
            if ($id_user === null) {
                return false;
            }
            $sql_update = "UPDATE usuarios SET nombre=?, email=?, clave_actualizacion=NOW() WHERE id=?";
            $arrData = array($nombre, $email, $id_user);
            $this->update($sql_update, $arrData);
            return 'update';
        } else {
            $sql_insert = "INSERT INTO usuarios (usuario, nombre, email, clave, id_rol, id_grupo, cantidad_inicio, estado_usuario, fuente_registro, fecha_registro, clave_actualizacion) 
                       VALUES (?, ?, ?, ?, ?, ?, 0, 'ACTIVO', 'LDAP', NOW(), NOW())";
            $arrData = array($usuario, $nombre, $email, $password, $id_rol, $idGrupoPorDefecto);
            $this->insert($sql_insert, $arrData);
            return 'insert';
        }
    }

    /**
     * Verifica si un usuario ya tiene una sesión activa en la tabla visitas.
     * Excluye la sesión actual para no contar el intento en curso.
     */
    public function verificarSesionActivaDeUsuario(int $id_usuario): bool
    {
        $this->cerrarVisitasVencidas();
        $session_actual = session_id();
        $sql = "SELECT COUNT(*) AS total FROM visitas 
                WHERE id = ? AND estado = 'ACTIVO' AND session_id <> ?";
        $resultado = $this->select($sql, [$id_usuario, $session_actual]);
        return !empty($resultado) && intval($resultado['total'] ?? 0) > 0;
    }

    /**
     * Cierra todas las sesiones activas de un usuario (marca INACTIVO en visitas).
     * Se llama cuando el usuario decide desplazar la sesión anterior.
     */
    public function cerrarSesionesActivasDeUsuario(int $id_usuario): bool
    {
        $session_actual = session_id();
        $sql = "UPDATE visitas SET estado = 'INACTIVO', fecha_cierre = NOW()
                WHERE id = ? AND estado = 'ACTIVO' AND session_id <> ?";
        return $this->update($sql, [$id_usuario, $session_actual]);
    }

    private static bool $esquemaVisitasListo = false;

    private function contarEsquemaVisitas(string $sql): int
    {
        $fila = $this->select($sql);
        if (is_array($fila) && isset($fila[0])) {
            $fila = $fila[0];
        }
        return intval($fila['n'] ?? 0);
    }

    // Deja la tabla visitas lista: columna de actividad e índices. Cada pieza se crea solo si falta.
    private function asegurarEsquemaVisitas(): void
    {
        if (self::$esquemaVisitasListo) {
            return;
        }
        self::$esquemaVisitasListo = true;                    // una sola vez por pedido, aunque falle
        $tabla = "table_schema = DATABASE() AND table_name = 'visitas'";
        $pasos = [
            "SELECT COUNT(*) AS n FROM information_schema.columns WHERE $tabla AND column_name = 'ultima_actividad'"
            => "ALTER TABLE visitas ADD COLUMN ultima_actividad DATETIME NULL",
            // índices: se crean solo si no hay ninguno que empiece por esa columna, cualquiera sea su nombre
            "SELECT COUNT(*) AS n FROM information_schema.statistics WHERE $tabla AND column_name = 'session_id' AND seq_in_index = 1"
            => "ALTER TABLE visitas ADD INDEX idx_visitas_session (session_id)",
            "SELECT COUNT(*) AS n FROM information_schema.statistics WHERE $tabla AND column_name = 'estado' AND seq_in_index = 1"
            => "ALTER TABLE visitas ADD INDEX idx_visitas_estado_id (estado, id)",
        ];
        foreach ($pasos as $consulta => $ddl) {
            try {
                if ($this->contarEsquemaVisitas($consulta) === 0) {
                    $this->insert($ddl, []);
                }
            } catch (Throwable $e) {
                if (stripos($e->getMessage(), 'Duplicate') === false) {   // dos logins a la vez: el segundo ve "Duplicate", no es un error
                    error_log('Error asegurando el esquema de visitas: ' . $e->getMessage());
                }
            }
        }
    }

    public function registrarActividad(string $sessionId): void
    {
        $this->asegurarEsquemaVisitas();
        $this->update("UPDATE visitas SET ultima_actividad = NOW() WHERE session_id = ? AND estado = 'ACTIVO'", [$sessionId]);
    }

    public function cerrarVisitasVencidas(): void
    {
        $this->asegurarEsquemaVisitas();
        $segundos = defined('SESION_TIEMPO_SEGUNDOS') ? max(60, (int) SESION_TIEMPO_SEGUNDOS) : 7200;
        $vencidas = "estado = 'ACTIVO' AND COALESCE(ultima_actividad, fecha) < (NOW() - INTERVAL " . $segundos . " SECOND)";
        // primero descuenta el contador de cada visita que vence, después las marca inactivas
        $this->update("UPDATE usuarios u JOIN (SELECT id, COUNT(*) AS n FROM visitas WHERE $vencidas GROUP BY id) x ON x.id = u.id
                   SET u.cantidad_inicio = GREATEST(0, u.cantidad_inicio - x.n)", []);
        $this->update("UPDATE visitas SET estado = 'INACTIVO', fecha_cierre = NOW() WHERE $vencidas", []);
    }

}