<?php
class Usuarios extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    public function listar()
    {
        $this->checkDynamicAccess('listar', 'No tienes permiso para ver los usuarios.');
        $usuario = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();
        $data = ['usuario' => $usuario, 'roles' => $roles, 'grupos' => $grupos];
        $this->views->getView($this, "listar", $data);
    }

    public function activos()
    {
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta sección");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        $activos = $this->model->selectUsuariosActivos();
        $data = ['activos' => $activos];
        $this->views->getView($this, "activos", $data);
    }

    public function grupo()
    {
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta sección");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        // Usuario autorizado: obtener datos y mostrar vista
        $grupos = $this->model->selectGrupos();
        $tipos_documentos = $this->model->selectTipoDoc();
        $permisos = $this->model->selectPerDoc();
        $data = [
            'grupos' => $grupos,
            'tipos_documentos' => $tipos_documentos,
            'permisos' => $permisos
        ];
        $this->views->getView($this, "grupo", $data);
    }

    public function asignar_permisos()
    {
        // Verificación del token CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta sección");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        // Obtener datos del formulario
        $id_grupo = intval($_POST['id_grupo']);
        $id_tipoDoc = intval($_POST['id_tipoDoc']);
        // Verificar si el permiso ya existe
        $permisoExistente = $this->model->verificarPermisoExistente($id_grupo, $id_tipoDoc);
        if ($permisoExistente['total'] > 0) {
            // Mostrar mensaje de error en caso de duplicado
            setAlert('error', "Permiso existente para este grupo y tipo de documento!");
            session_write_close();
            header('location: ' . base_url() . "usuarios/grupo");
            exit();
        } else {
            // Asignar el nuevo permiso
            $this->model->asignarPermiso($id_grupo, $id_tipoDoc);
            setAlert('success', "El permiso ha sido registrado!");
            session_write_close();
            header('location: ' . base_url() . "usuarios/grupo");
            exit();
        }
    }

    public function eliminar_permiso()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            // Registrar alerta y bloqueo
            setAlert('warning', "No tienes permiso para acceder a esta sección");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            // Redirigir solo una vez
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        $id_permiso = $_POST['id_permiso'];
        $this->model->eliminarPermiso($id_permiso);
        // Redirigir de nuevo a la gestión de grupos
        header("Location: " . base_url() . "usuarios/grupo");
    }

    // Método para reactivar un permiso desactivado
    public function reactivar_permiso()
    {
        // 1. Validar CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

        // 2. Validar Permisos de Usuario (Admin)
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            setAlert('warning', "No tienes permiso para acceder a esta sección");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Acceso no autorizado');
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }

        // 3. Procesar la reactivación
        if (isset($_POST['id_permiso'])) {
            $id_permiso = intval($_POST['id_permiso']);

            // Llamamos al modelo para actualizar estado a 1 (ACTIVO)
            $request = $this->model->reactivarPermiso($id_permiso);

            if ($request) {
                setAlert('success', "Permiso reactivado correctamente.");
            } else {
                setAlert('error', "Error al reactivar el permiso.");
            }
        }

        // 4. Redirigir
        header("Location: " . base_url() . "usuarios/grupo");
        die();
    }

    public function reporte()
    {
        $usuario = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();
        $data = ['usuario' => $usuario, 'roles' => $roles, 'grupos' => $grupos];
        ;
        $this->views->getView($this, "reporte", $data);
    }

    public function insertar()
    {
        $this->checkCsrfSafety();
        // Verificar permiso usando el método específico de INSERTAR
        $this->checkAccessSafetyInsert([1, 2]);
        // Verificar límite de usuarios antes de insertar
        $usuariosActuales = $this->model->contarUsuariosActivos()['total'];
        if ($usuariosActuales >= LIMITE_USUARIOS) {
            // Podés redirigir con mensaje de error o mostrar alerta
            setAlert('warning', '🚫 No se puede agregar más usuarios. Se alcanzó el límite de la licencia.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit;
        }
        $nombre = htmlspecialchars($_POST['nombre']);
        $usuario = htmlspecialchars($_POST['usuario']);
        $clave = $_POST['clave'];
        $rol = $_POST['id_rol'];
        $grupo = $_POST['id_grupo'];
        $email = htmlspecialchars($_POST['email']);
        $fuente_registro = 'scantec';
        // Verificar usuarios existente antes de insertar
        $usuariosExiste = $this->model->verificarUsuarioExistente($usuario)['total'];
        if ($usuariosExiste > 0) {
            // Podés redirigir con mensaje de error o mostrar alerta
            setAlert('warning', 'Este usuario ya existe.');
            header('Location: ' . base_url() . 'usuarios/listar');
            exit;
        }
        // Encriptar la contraseña con bcrypt y cost de 12
        $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->model->insertarUsuarios($nombre, $usuario, $hash, $rol, $grupo, $fuente_registro, $email);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function registrar_grupo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $descripcion = htmlspecialchars($_POST['descripcion']);
        $insert = $this->model->insertarGrupo($descripcion);
        if ($insert) {
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function registrar_tipoDoc()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        // El token CSRF es válido y no ha caducado, proceder con la insercion de datos
        // Realizar cualquier sanitización adicional de los datos si es necesario
        $nombre_tipoDoc = htmlspecialchars($_POST['nombre_tipoDoc']);
        $indice_1 = htmlspecialchars($_POST['indice_1']);
        $indice_2 = htmlspecialchars($_POST['indice_2']);
        $indice_3 = htmlspecialchars($_POST['indice_3']);
        $indice_4 = htmlspecialchars($_POST['indice_4']);
        $indice_5 = htmlspecialchars($_POST['indice_5']);
        $indice_6 = htmlspecialchars($_POST['indice_6']);
        $insert = $this->model->insertarTipoDoc($nombre_tipoDoc, $indice_1, $indice_2, $indice_3, $indice_4, $indice_5, $indice_6);
        if ($insert) {
            header("location: " . base_url() . "usuarios/grupo");
            die();
        }
    }

    public function editar()
    {
        // 1. Seguridad
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            setAlert('warning', "Acceso denegado.");
            $this->model->bloquarPC_IP($_SESSION['nombre'], 'Intento edición sin permiso');
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
        if (empty($_GET['id'])) {
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        }
        $id = intval($_GET['id']);
        // 2. Obtener Datos del Modelo
        $usuarioRaw = $this->model->editarUsuarios($id);
        $usuario = [];
        if (!empty($usuarioRaw)) {
            if (isset($usuarioRaw[0])) {
                $usuario = $usuarioRaw[0]; // Sacamos la fila 0
            } else {
                $usuario = $usuarioRaw;
            }
        }
        if (empty($usuario)) {
            header("Location: " . base_url() . "usuarios/listar");
            exit();
        }
        $rol = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();
        $data = [
            'usuario' => $usuario,
            'rol' => $rol,
            'grupos' => $grupos
        ];

        $this->views->getView($this, "editar", $data);
    }

    public function actualizar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        // El token CSRF es válido y no ha caducado, proceder con la actualización de usuario
        // Realizar cualquier sanitización adicional de los datos si es necesario
        $id = htmlspecialchars($_POST['id']);
        $nombre = htmlspecialchars($_POST['nombre']);
        $usuario = htmlspecialchars($_POST['usuario']);
        $rol = htmlspecialchars($_POST['id_rol']);
        $grupo = htmlspecialchars($_POST['id_grupo']);
        $email = htmlspecialchars($_POST['email']);
        // Actualizar el usuario en la base de datos
        $actualizar = $this->model->actualizarUsuarios($nombre, $usuario, $rol, $grupo, $email, $id);
        // Verificar si la actualización fue exitosa
        if ($actualizar == 1) {
            $alert = 'modificado';
        } else {
            $alert = 'error';
        }
        if (!Validador::puedeVer($_SESSION, [1, 2])) {
            header('Location: ' . base_url() . 'usuarios/listar');
            exit();
        } else {
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }

    public function eliminar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $id = htmlspecialchars($_POST['id']);
        $this->model->eliminarUsuarios($id);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function bloquear()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $id = htmlspecialchars($_POST['id']);
        $this->model->bloquearUsuarios($id);
        header("location: " . base_url() . "usuarios/listar");
        die();
    }

    public function reingresar()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
        $id = htmlspecialchars($_POST['id']);
        $this->model->reingresarUsuarios($id);
        $this->model->selectUsuarios();
        header('location: ' . base_url() . 'usuarios/Listar');
        die();
    }

    // public function login()
    // {
    //     // Aseguramos que la sesión esté activa para manejar los intentos y las alertas
    //     if (session_status() === PHP_SESSION_NONE) {
    //         session_start();
    //     }

    //     if (!isset($_SESSION['login_attempts'])) {
    //         $_SESSION['login_attempts'] = 0;
    //     }

    //     if (!empty($_POST['usuario']) && !empty($_POST['clave'])) {
    //         $usuario = htmlspecialchars($_POST['usuario'], ENT_QUOTES, 'UTF-8');
    //         $claveIngresada = $_POST['clave'];

    //         // Consulta al modelo
    //         $data = $this->model->selectUsuario($usuario);

    //         /**
    //          * VALIDACIÓN UNIFICADA
    //          * Se comprueba en un solo bloque si el usuario existe, está activo y la clave es correcta.
    //          * Si cualquiera de estas falla, el flujo va al 'else' genérico.
    //          */
    //         if (!empty($data) && $data['estado_usuario'] == 'ACTIVO' && password_verify($claveIngresada, $data['clave'])) {

    //             // --- CASO 1: LOGIN EXITOSO ---
    //             $_SESSION['id'] = $data['id'];
    //             $_SESSION['nombre'] = $data['nombre'];
    //             $_SESSION['usuario'] = $data['usuario'];
    //             $_SESSION['id_rol'] = $data['id_rol'];
    //             $_SESSION['ACTIVO'] = true;

    //             // Tokens de seguridad
    //             $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    //             $_SESSION['csrf_expiration'] = time() + (30 * 60);

    //             $_SESSION['id_grupo'] = $data['id_grupo'];
    //             $_SESSION['grupo'] = $data['grupo'];
    //             $_SESSION['PERMISOS'] = $this->model->getPermisosByRol($data['id_rol']);

    //             // Reiniciamos intentos al entrar con éxito
    //             $_SESSION['login_attempts'] = 0;

    //             // Auditoría
    //             $this->model->registrarVisita($_SESSION['id']);
    //             $this->model->conteoInicioSesion($_SESSION['id']);

    //             // Redirección según rol
    //             if ($data['id_rol'] == 3 || $data['id_rol'] == 4) {
    //                 header('location: ' . base_url() . 'expedientes/indice_busqueda');
    //             } else {
    //                 header('location: ' . base_url() . 'dashboard/listar');
    //             }
    //             exit();

    //         } else {
    //             // --- CASO 2: LOGIN FALLIDO (Genérico por seguridad) ---
    //             $_SESSION['login_attempts']++;

    //             if ($_SESSION['login_attempts'] >= 3) {
    //                 // Bloqueo de seguridad
    //                 $motivo = 'Excedió el número de intentos de inicio de sesión (Credenciales inválidas)';

    //                 // Solo intentamos bloquear en la DB si el usuario realmente existe
    //                 if (!empty($data)) {
    //                     $this->model->bloquearUsuarios($usuario);
    //                 }

    //                 // Bloqueo por IP (Siempre se ejecuta para frenar ataques de fuerza bruta)
    //                 $this->model->bloquarPC_IP($usuario, $motivo);

    //                 setAlert('error', "ACCESO RESTRINGIDO: Demasiados intentos fallidos. Su acceso ha sido bloqueado por seguridad.");
    //                 header('location: ' . base_url());
    //                 exit();

    //             } else {
    //                 // Mensaje genérico para no revelar si el usuario existe o no
    //                 $restantes = 3 - $_SESSION['login_attempts'];
    //                 setAlert('error', "Usuario o contraseña incorrecta. Le quedan $restantes intentos.");

    //                 header('location: ' . base_url());
    //                 exit();
    //             }
    //         }
    //     } else {
    //         // CASO 3: CAMPOS VACÍOS
    //         setAlert('warning', "Debe completar todos los campos del formulario.");
    //         header('location: ' . base_url());
    //         exit();
    //     }
    // }
    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;

        // --- 1. LIMPIEZA INTELIGENTE DE USUARIO ---
        $usuario_raw = trim($_POST['usuario'] ?? '');
        
        // Si el usuario escribe por costumbre "DOMINIO\usuario", le quitamos el dominio
        if (strpos($usuario_raw, '\\') !== false) {
            $usuario_raw = explode('\\', $usuario_raw)[1]; 
        }        
        if (strpos($usuario_raw, '@') !== false) {
            $usuario_raw = explode('@', $usuario_raw)[0]; 
        }

        $usuario = htmlspecialchars($usuario_raw, ENT_QUOTES, 'UTF-8');
        $claveIngresada = $_POST['clave'] ?? '';
        $fuente_registro = $_POST['fuente_registro'] ?? 'scantec';

        if (empty($usuario) || empty($claveIngresada)) {
            setAlert('warning', "Debe completar todos los campos."); 
            header('location: ' . base_url()); 
            exit();
        }

        $data = $this->model->selectUsuario($usuario);
        if (empty($data)) {
            setAlert('error', "El usuario no existe en la base de datos.");
            header('location: ' . base_url()); 
            exit();
        }

        if ($data['estado_usuario'] !== 'ACTIVO') {
            setAlert('error', "Tu usuario está inactivo o bloqueado.");
            header('location: ' . base_url()); 
            exit();
        }

        $auth_success = false;

        // =========================================================
        // 1. MODO DIRECTORIO ACTIVO (Si seleccionó LDAP)
        // =========================================================
        if ($fuente_registro === 'LDAP') {
            require_once 'Models/ConfiguracionModel.php';
            $configModel = new ConfiguracionModel();
            $configLdapArray = $configModel->selectLDAP_datos();
            $configLdap = !empty($configLdapArray) ? $configLdapArray[0] : null;

            if ($configLdap && !empty($configLdap['ldapHost'])) {
                $ldap_host = $configLdap['ldapHost'];
                $ldap_port = !empty($configLdap['ldapPort']) ? $configLdap['ldapPort'] : 389;
                $ldap_host = str_replace(['ldap://', 'ldaps://'], '', $ldap_host);

                $ldap_conn = @ldap_connect($ldap_host, $ldap_port);
                
                if ($ldap_conn) {
                    ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

                    // --- GENERADOR DINÁMICO DE DOMINIO NETBIOS ---
                    // Extrae la empresa desde "OU=printec,DC=printec,DC=local" -> "PRINTEC"
                    $dominio_netbios = 'DOMINIO'; // Valor por defecto
                    if (preg_match('/DC=([^,]+)/i', $configLdap['ldapBaseDn'], $matches)) {
                        $dominio_netbios = strtoupper($matches[1]);
                    }

                    // Arma el formato correcto automáticamente (Ej: PRINTEC\aldo.silva)
                    $usuario_ad = $dominio_netbios . "\\" . $usuario; 

                    if (@ldap_bind($ldap_conn, $usuario_ad, $claveIngresada)) {
                        $auth_success = true; // ¡El AD aceptó la clave!
                    }
                }
            }
        } 
        // =========================================================
        // 2. MODO USUARIO LOCAL
        // =========================================================
        else {
            if (password_verify($claveIngresada, $data['clave'])) {
                $auth_success = true;
            }
        }

        // =========================================================
        // 3. RESOLUCIÓN DE ACCESO
        // =========================================================
        if ($auth_success) {
            $_SESSION['id'] = $data['id'];
            $_SESSION['nombre'] = $data['nombre'];
            $_SESSION['usuario'] = $data['usuario'];
            $_SESSION['id_rol'] = $data['id_rol'];
            $_SESSION['ACTIVO'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + (30 * 60);
            
            $_SESSION['id_grupo'] = $data['id_grupo'] ?? 0;
            $_SESSION['grupo'] = $data['grupo'] ?? '';
            $_SESSION['PERMISOS'] = $this->model->getPermisosByRol($data['id_rol']);
            
            $_SESSION['login_attempts'] = 0; // Reiniciar intentos
            //auditoria de sesiones
            $this->model->registrarVisita($_SESSION['id']);
            $this->model->conteoInicioSesion($_SESSION['id']);

            if ($data['id_rol'] == 3 || $data['id_rol'] == 4) {
                header('location: ' . base_url() . 'expedientes/indice_busqueda');
            } else {
                header('location: ' . base_url() . 'dashboard/listar');
            }
            exit();
        } else {
            $_SESSION['login_attempts']++;
            
            if ($_SESSION['login_attempts'] >= 3) {
                $this->model->bloquearUsuarios($usuario);
                $this->model->bloquearPC_IP($usuario, 'Excedió intentos');
            }
            
            $restantes = 3 - $_SESSION['login_attempts'];
            setAlert('error', "Usuario o contraseña incorrecta. Le quedan $restantes intentos.");
            header('location: ' . base_url());
            exit();
        }
    }

    // =========================================================
    // Muestra el formulario (Tu archivo cambiar_pass.php)
    // =========================================================
    public function cambiar_pass()
    {
        $data['page_title'] = "Cambiar Contraseña";
        // Cargamos tu vista específica: Views/Usuarios/cambiar_pass.php
        $this->views->getView($this, "cambiar_pass", $data);
    }
    // =========================================================
    //Recibe los datos y actualiza
    // =========================================================
    public function actualizar_password()
    {
        // Validación CSRF
        if (!Validador::csrfValido()) {
            setAlert('error', "Token inválido.");
            header("Location: " . base_url() . "usuarios/cambiar_pass");
            exit();
        }
        if ($_POST) {
            $idUser = $_SESSION['idUser'] ?? $_SESSION['id'];
            // Nombres de los inputs que definimos en la vista
            $actual = $_POST['clave_actual'];
            $nueva = $_POST['clave_nueva'];
            $confirmar = $_POST['clave_confirmar'];
            // A. Validaciones 
            if (empty($actual) || empty($nueva) || empty($confirmar)) {
                setAlert('error', "Todos los campos son obligatorios.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
                exit();
            }
            if ($nueva !== $confirmar) {
                setAlert('error', "Las contraseñas nuevas no coinciden.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
                exit();
            }
            // B. Verificar contraseña actual en BD
            $dataDB = $this->model->getPassword($idUser);
            // Ajuste por si el modelo devuelve array [0] o plano
            $passDB = (isset($dataDB[0]['clave'])) ? $dataDB[0]['clave'] : ($dataDB['clave'] ?? '');
            if (password_verify($actual, $passDB)) {
                // Generar Hash y Actualizar
                $nuevaHash = password_hash($nueva, PASSWORD_BCRYPT, ['cost' => 12]);
                $request = $this->model->cambiarContra($nuevaHash, $idUser);
                if ($request) {
                    setAlert('success', "Contraseña actualizada correctamente.");
                    if ($_SESSION['id_rol'] == 1 || $_SESSION['id_rol'] == 2) {
                        header("Location: " . base_url() . "usuarios/listar");
                    }
                    // Si es Usuario normal, NO tiene permiso de ver 'listar', así que va a su perfil o dashboard
                    else {
                        header("Location: " . base_url() . "usuarios/perfil");
                        // O si prefieres que vaya al inicio: "dashboard"
                    }
                    exit();
                } else {
                    setAlert('error', "Error al guardar en base de datos.");
                    header("Location: " . base_url() . "usuarios/cambiar_pass");
                }
            } else {
                setAlert('error', "La contraseña actual es incorrecta.");
                header("Location: " . base_url() . "usuarios/cambiar_pass");
            }
        }
        exit();
    }

    public function salir()
    {
        // 1. Recuperar la sesión existente (CRÍTICO: Esto recupera el session_id)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['id'])) {
            $idUser = $_SESSION['id'];
            // Al llamar a estas funciones, el modelo usará session_id() internamente
            // para saber exactamente qué fila cerrar en la BD.
            $this->model->actualizarVisita($idUser);
            // Restamos 1 al contador global del usuario
            $this->model->restarInicioSesion($idUser);
        }
        // 2. Destruir la sesión del servidor
        session_unset();
        session_destroy();
        // 3. Redirigir
        header('location: ' . base_url());
        exit();
    }

    public function fin_session()
    {
        // Validamos que sea un Admin quien ejecuta esto (Opcional pero recomendado)
        // if($_SESSION['rol'] != 1) { header("Location: ".base_url()); exit(); }
        if (isset($_GET['id_visita']) && isset($_GET['id'])) {
            // Limpieza de datos
            $id_visita = intval($_GET['id_visita']); // Forzamos a entero por seguridad
            $id_usuario = intval($_GET['id']);
            // 1. Restar inicio de sesión (si tu lógica lo requiere)
            $this->model->restarInicioSesion($id_usuario);
            // 2. ACTUALIZAR VISITA A 'INACTIVO' (Esto es lo que dispara el Kick)
            $this->model->actualizarVisitas($id_visita);
            // 3. Redireccionar
            header("Location: " . base_url() . "Usuarios/activos?msg=killed");
            die();
        } else {
            // Si faltan datos
            header("Location: " . base_url() . "Usuarios/activos?msg=error");
            die();
        }
    }

    public function Ayuda()
    {
        $ruta_pdf = 'Assets/files/SCANTEC_MANUAL.pdf';
        $archivo = fopen($ruta_pdf, 'r');
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Ayuda.pdf"');
        header('Content-Length: ' . filesize($ruta_pdf));
        readfile($ruta_pdf);
        fclose($archivo);
        exit;
    }

    /*  public function importar(){
        if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
          header("Location: " . base_url() . "?error=csrf");
          die();
          }
         require_once 'Config/Config.php';

        // Conexión a la base de datos
        try {
            $pdo = new PDO(
            "mysql:host=".HOST.";dbname=".BD.";charset=utf8",
            DB_USER, PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Exception $ex) { exit($ex->getMessage()); }

        if(isset($_FILES["file"]))
        {
            $file_type=$_FILES["file"]["type"];
            $file_name=$_FILES["file"]["name"];
            $file_size=$_FILES["file"]["size"];
            $file_tmp=$_FILES["file"]["tmp_name"];
            $file_ext=pathinfo($file_name,PATHINFO_EXTENSION);

            if($file_ext=='csv')
            {
                $fh = fopen($file_tmp, "r");
                if ($fh === false) {
                    exit("No se pudo abrir el archivo CSV cargado");
                }

                // (C) IMPORT ROW BY ROW
                while (($row = fgetcsv($fh)) !== false) {
                    try {
                        //print_r($row);
                        $nombre = htmlspecialchars($row[0]);
                        $usuario = htmlspecialchars($row[1]);
                        $clave = $row[2];
                        // Encriptar la contraseña con SHA-512
                        $passwordHash = hash('SHA512', $clave);
                        $id_rol = 3;
                        $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                    }catch (Exception $ex) { echo $ex->getmessage(); }
                }
                fclose($fh);                
                header("location: " . base_url() . "usuarios/listar");
                die();
            }
            else if($file_ext=='xls' || $file_ext=='xlsx')
            {
                require_once 'Libraries/vendor/autoload.php';

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

                if($file_ext == 'xls')
                {
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                }

                $spreadsheet = $reader->load($file_tmp);

                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                foreach ($sheetData as $key=>$value) {
                    //if($key == 1) continue; //si quieres omitir la primer fila
                    try {
                        //print_r($value);
                        $nombre = htmlspecialchars($value['A']);
                        $usuario = htmlspecialchars($value['B']);
                        $clave = $value['C'];
                        // Encriptar la contraseña con SHA-512
                        $passwordHash = hash('SHA512', $clave);
                        $id_rol = 3;
                        $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                    }catch (Exception $ex){  // Encriptar la contraseña con SHA-512
                    $passwordHash = hash('SHA512', $clave);
                    $id_rol = 3;
                    $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol);
                }catch (Exception $ex) { 
                    echo $ex->getmessage(); }
                }
                header("location: " . base_url() . "usuarios/listar");
                die();
                }
            }
        } */

    /**
     * Función para validar que el archivo tenga la estructura correcta.
     */
    private function validarEstructura($header)
    {
        $estructuraCorrecta = ["nombre", "usuario", "clave", "rol", "grupo", "email"];
        return count(array_intersect($estructuraCorrecta, $header)) === count($estructuraCorrecta);
    }

    /**
     * Función para validar que la contraseña cumpla los requisitos.
     */
    private function validarClave($clave)
    {
        $regex = "/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^\&*\-_.])(?=.{7,})/";
        return preg_match($regex, $clave);
    }


    public function importar()
    {
        // 1. Validación de Seguridad (CSRF)
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "usuarios/listar");
            die();
        }

        // 2. Verificar que se subió un archivo sin errores
        if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {

            $file_tmp = $_FILES["file"]["tmp_name"];
            $file_name = $_FILES["file"]["name"];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $usuarios = [];
            $errores = []; // Acumulador de errores para no dejar el proceso a medias
            $fila_actual = 0;

            // 3. PROCESAR CSV
            if ($file_ext === 'csv') {
                $fh = fopen($file_tmp, "r");
                if ($fh === false) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo leer el archivo CSV.'];
                    header("Location: " . base_url() . "usuarios/listar");
                    die();
                }

                while (($row = fgetcsv($fh)) !== false) {
                    $fila_actual++;
                    // Saltar la primera fila si contiene cabeceras (títulos)
                    if ($fila_actual === 1)
                        continue;

                    // Evitar filas vacías
                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row[0] ?? ''));
                    $usuario = htmlspecialchars(trim($row[1] ?? ''));
                    $clave = trim($row[2] ?? '');
                    $id_rol = (int) ($row[3] ?? 0);
                    $id_grupo = (int) ($row[4] ?? 0);
                    $email = filter_var(trim($row[5] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contraseña del usuario '{$usuario}' es débil.";
                        continue;
                    }

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email];
                }
                fclose($fh);

                // 4. PROCESAR EXCEL (XLS, XLSX)
            } else if ($file_ext === 'xls' || $file_ext === 'xlsx') {
                require_once 'Libraries/vendor/autoload.php';

                $reader = ($file_ext === 'xlsx') ? new \PhpOffice\PhpSpreadsheet\Reader\Xlsx() : new \PhpOffice\PhpSpreadsheet\Reader\Xls();
                $reader->setReadDataOnly(true); // Optimiza la lectura de memoria
                $spreadsheet = $reader->load($file_tmp);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $index => $row) {
                    $fila_actual = $index;
                    // Saltar la cabecera (Fila 1 de Excel)
                    if ($fila_actual === 1)
                        continue;

                    // Evitar filas vacías
                    if (empty(array_filter($row)))
                        continue;

                    $nombre = htmlspecialchars(trim($row['A'] ?? ''));
                    $usuario = htmlspecialchars(trim($row['B'] ?? ''));
                    $clave = trim($row['C'] ?? '');
                    $id_rol = (int) ($row['D'] ?? 0);
                    $id_grupo = (int) ($row['E'] ?? 0);
                    $email = filter_var(trim($row['F'] ?? ''), FILTER_SANITIZE_EMAIL);
                    $fuente_registro = 'scantec';

                    if (empty($usuario) || empty($email)) {
                        $errores[] = "Fila {$fila_actual}: El usuario y el correo son obligatorios.";
                        continue;
                    }

                    if (empty($clave) || !$this->validarClave($clave)) {
                        $errores[] = "Fila {$fila_actual}: La contraseña del usuario '{$usuario}' es débil.";
                        continue;
                    }

                    $usuarios[] = [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Formato no soportado. Use CSV, XLS o XLSX.'];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            // 5. VALIDACIÓN FINAL: Si hay un solo error, frenar todo para proteger la BD
            if (count($errores) > 0) {
                // Limitamos a mostrar solo los primeros 5 errores para no desbordar la alerta
                $errores_mostrar = array_slice($errores, 0, 5);
                $mensaje_error = "<b>La importación fue cancelada por errores de formato:</b><br><br>" . implode("<br>", $errores_mostrar);
                if (count($errores) > 5)
                    $mensaje_error .= "<br><i>...y " . (count($errores) - 5) . " errores más.</i>";

                $_SESSION['alert'] = ['type' => 'error', 'message' => $mensaje_error];
                header("Location: " . base_url() . "usuarios/listar");
                die();
            }

            // 6. INSERCIÓN SEGURA (Solo llega aquí si el 100% de los usuarios pasaron las validaciones)
            $importados = 0;
            foreach ($usuarios as $user) {
                [$nombre, $usuario, $clave, $id_rol, $id_grupo, $fuente_registro, $email] = $user;
                $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);

                $insert = $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, (string) $id_rol, (string) $id_grupo, $fuente_registro, $email);
                if ($insert) {
                    $importados++;
                }
            }

            if ($importados > 0) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => "¡Éxito! Se importaron correctamente $importados usuarios."];
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'El archivo se leyó, pero no se importó ningún usuario (Archivo vacío).'];
            }

        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Ocurrió un error al subir el archivo.'];
        }

        header("Location: " . base_url() . "usuarios/listar");
        die();
    }

    public function sincronizarAD()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('max_execution_time', 300);

        // 1. Verificar Token CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 2. Obtener configuración
        $id_config = intval($_POST['id']);
        $ldapConfig = $this->model->getLdapConfigById($id_config);

        // Parche de robustez: Si el modelo devuelve un array de arrays, tomamos el primero
        if (isset($ldapConfig[0]['ldapHost'])) {
            $ldapConfig = $ldapConfig[0];
        }

        if (empty($ldapConfig) || !isset($ldapConfig['ldapHost'])) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se pudieron leer los datos de la configuración (ID: ' . $id_config . ').'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 3. DESENCRIPTAR CONTRASEÑA
        // Usamos la función si existe, sino texto plano (para evitar errores fatales)
        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($ldapConfig['ldapPass']);
        } else {
            $password_real = $ldapConfig['ldapPass'];
        }

        // 4. Conexión LDAP
        $ldapConn = ldap_connect($ldapConfig['ldapHost'], $ldapConfig['ldapPort']);
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (!$ldapConn || !@ldap_bind($ldapConn, $ldapConfig['ldapUser'], $password_real)) {
            $err = ldap_error($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => "Fallo de conexión LDAP: $err"];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        // 5. Búsqueda
        $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(mail=*))";
        $attributes = ['samaccountname', 'mail', 'displayname', 'givenname', 'sn'];

        $search = ldap_search($ldapConn, $ldapConfig['ldapBaseDn'], $filter, $attributes);

        if (!$search) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error en la búsqueda. Verifique el BaseDN configurado.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        $entries = ldap_get_entries($ldapConn, $search);

        $contador_nuevos = 0;
        $contador_actualizados = 0;

        // Validamos que haya resultados antes de iterar
        if ($entries['count'] > 0) {
            for ($i = 0; $i < $entries['count']; $i++) {

                $username = isset($entries[$i]['samaccountname'][0]) ? trim($entries[$i]['samaccountname'][0]) : '';
                $email = isset($entries[$i]['mail'][0]) ? trim($entries[$i]['mail'][0]) : '';

                // Construir nombre
                $nombre = '';
                if (isset($entries[$i]['displayname'][0])) {
                    $nombre = $entries[$i]['displayname'][0];
                } else {
                    $given = isset($entries[$i]['givenname'][0]) ? $entries[$i]['givenname'][0] : '';
                    $sn = isset($entries[$i]['sn'][0]) ? $entries[$i]['sn'][0] : '';
                    $nombre = trim($given . ' ' . $sn);
                }

                if (!empty($username) && !empty($email)) {

                    // Contraseña dummy segura
                    try {
                        $bytes = random_bytes(10);
                    } catch (Exception $e) {
                        $bytes = openssl_random_pseudo_bytes(10);
                    }
                    $password_dummy = password_hash(bin2hex($bytes), PASSWORD_DEFAULT);

                    $rol_defecto = 3;

                    $resultado = $this->model->sincronizarUsuarioLDAP($username, $nombre, $email, $password_dummy, $rol_defecto);

                    if ($resultado == 'insert')
                        $contador_nuevos++;
                    if ($resultado == 'update')
                        $contador_actualizados++;
                }
            }
        } else {
            // MENSAJE MEJORADO: Caso sin resultados
            $_SESSION['alert'] = ['type' => 'info', 'message' => 'Conexión exitosa, pero no se encontraron usuarios activos con correo en la ruta especificada.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            die();
        }

        ldap_unbind($ldapConn);

        // MENSAJE FINAL MEJORADO (Sin HTML)
        if ($contador_nuevos == 0 && $contador_actualizados == 0) {
            $msg = "Sincronización completada. No se encontraron usuarios nuevos ni cambios en los existentes.";
            $tipo = "info";
        } else {
            // Ejemplo: "Proceso finalizado. Registrados: 5 | Actualizados: 2"
            $msg = "Proceso finalizado correctamente. Registrados: $contador_nuevos | Actualizados: $contador_actualizados.";
            $tipo = "success";
        }

        $_SESSION['alert'] = ['type' => $tipo, 'message' => $msg];

        header("Location: " . base_url() . "configuracion/servidor_AD");
        die();
    }

    // public function importar(){
    //     if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
    //         header("Location: " . base_url() . "?error=csrf");
    //         die();
    //     }
    //     require_once 'Config/Config.php';

    //     try {
    //         $pdo = new PDO(
    //             "mysql:host=".HOST.";dbname=".BD.";charset=utf8",
    //             DB_USER, PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    //         );
    //     } catch (Exception $ex) { exit($ex->getMessage()); }

    //     if (isset($_FILES["file"])) {
    //         $file_type = $_FILES["file"]["type"];
    //         $file_name = $_FILES["file"]["name"];
    //         $file_size = $_FILES["file"]["size"];
    //         $file_tmp = $_FILES["file"]["tmp_name"];
    //         $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

    //         if ($file_ext == 'csv') {
    //             $fh = fopen($file_tmp, "r");
    //             if ($fh === false) {
    //                 exit("No se pudo abrir el archivo CSV cargado");
    //             }

    //             while (($row = fgetcsv($fh)) !== false) {
    //                 try {
    //                     $nombre = htmlspecialchars($row[0]);
    //                     $usuario = htmlspecialchars($row[1]);
    //                     $clave = $row[2];
    //                     $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
    //                     $id_rol = htmlspecialchars($row[3]);
    //                     $id_grupo = htmlspecialchars($row[4]);
    //                     $fuenteRegistro = "scantec-import";
    //                     $id_grupo = htmlspecialchars($row[5]);
    //                     $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol, $id_grupo, $fuenteRegistro, $email);
    //                 } catch (Exception $ex) { 
    //                     echo $ex->getMessage(); 
    //                 }
    //             }
    //             fclose($fh);                
    //             header("location: " . base_url() . "usuarios/listar");
    //             die();
    //         }
    //         else if ($file_ext == 'xls' || $file_ext == 'xlsx') {
    //             require_once 'Libraries/vendor/autoload.php';

    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //             if ($file_ext == 'xls') {
    //                 $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //             }

    //             $spreadsheet = $reader->load($file_tmp);
    //             $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //             foreach ($sheetData as $key => $value) {
    //                 try {
    //                     $nombre = htmlspecialchars($value['A']);
    //                     $usuario = htmlspecialchars($value['B']);
    //                     $clave = $value['C'];
    //                     $passwordHash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
    //                     $id_rol = htmlspecialchars($value['D']);
    //                     $id_grupo = htmlspecialchars($value['E']);
    //                     $fuenteRegistro = "scantec-import";
    //                     $email = htmlspecialchars($value['F']);
    //                     $this->model->insertarUsuarios($nombre, $usuario, $passwordHash, $id_rol, $id_grupo, $fuenteRegistro, $email);
    //                 } catch (Exception $ex) { 
    //                     echo $ex->getMessage(); 
    //                 }
    //             }
    //             header("location: " . base_url() . "usuarios/listar");
    //             die();
    //         }
    //     }
    // }


    public function pdf()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $usuarios = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();

        // 2. Instanciar PDF (Usamos plantilla con SCANTEC fijo)
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Usuarios', 'L', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Centrar tabla (A4 Landscape = 297mm. Suma anchos = 265mm. Margen = ~16mm)
        $w = array(15, 70, 45, 50, 50, 35);
        $pdf->SetLeftMargin(16);
        $pdf->setX(16);

        $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Grupo', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[5], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($usuarios as $row) {
            $nombreGrupo = '';
            foreach ($grupos as $grup) {
                if ($grup['id_grupo'] == $row['id_grupo']) {
                    $nombreGrupo = $grup['descripcion'];
                    break;
                }
            }
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $row['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $pdf->Row(array(
                $row['id'],
                utf8_decode($row['nombre']),
                utf8_decode($row['usuario']),
                utf8_decode($nombreGrupo),
                utf8_decode($nombreRol),
                $row['estado_usuario']
            ));
        }

        $pdf->Output("Usuarios_" . date('Y_m_d') . ".pdf", "I");
    }

    public function pdf_filtro()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $usuarios = $this->model->reporteUsuarios($desde, $hasta);
        $roles = $this->model->selectRoles();

        // 2. Instanciar PDF
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Usuarios (Filtrado)', 'L', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Definir anchos y centrar tabla dinámicamente
        $w = array(15, 70, 45, 50, 35); // Suma: 215mm
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->SetLeftMargin($margin);
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Nombre'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('Usuario'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Rol', 1, 0, 'C', true);
        $pdf->Cell($w[4], 7, 'Estado', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'C', 'C'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($usuarios as $row) {
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $row['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $pdf->Row(array(
                $row['id'],
                utf8_decode($row['nombre']),
                utf8_decode($row['usuario']),
                utf8_decode($nombreRol),
                $row['estado_usuario']
            ));
        }

        $pdf->Output("Usuarios_Filtrado_" . date('Y_m_d') . ".pdf", "I");
    }

    public function grupo_pdf()
    {
        if (ob_get_length())
            ob_end_clean();

        // 1. Obtener datos
        $permisos = $this->model->selectPerDoc();

        // 2. Instanciar PDF
        require_once 'Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF(['nombre' => 'SCANTEC'], 'Reporte de Permisos por Grupo', 'P', 'A4');

        // 3. Configurar Cabecera
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        // Centrar tabla
        $w = array(30, 70, 30, 50); // Suma: 180mm
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->SetLeftMargin($margin);
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N° Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[1], 7, utf8_decode('Descripción Grupo'), 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, utf8_decode('N° Tipo Doc'), 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, 'Tipo Documento', 1, 1, 'C', true);

        // 4. Configurar motor multilínea
        $pdf->SetWidths($w);
        $pdf->SetAligns(array('C', 'L', 'C', 'L'));
        $pdf->SetFont('Arial', '', 10);

        // 5. Llenar filas
        foreach ($permisos as $row) {
            $pdf->Row(array(
                $row['id_grupo'],
                utf8_decode($row['descripcion']),
                $row['id_tipoDoc'],
                utf8_decode($row['nombre_tipoDoc'])
            ));
        }

        $pdf->Output("Permisos_Grupos_" . date('Y_m_d') . ".pdf", "I");
    }

    public function excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $usuario = $this->model->selectUsuarios();
        $roles = $this->model->selectRoles();
        $grupos = $this->model->selectGrupos();

        $excel = new ReportTemplateExcel('REGISTRO DE USUARIOS', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Encabezados en fila 4
        $headerRow = 4;
        $headers = ['NOMBRE', 'USUARIO', 'GRUPO', 'ROL', 'EMAIL', 'STATUS'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:F$headerRow")->applyFromArray($headerStyle);

        // Datos desde fila 5
        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($usuario as $value) {
            $nombreGrupo = '';
            foreach ($grupos as $grup) {
                if ($grup['id_grupo'] == $value['id_grupo']) {
                    $nombreGrupo = $grup['descripcion'];
                    break;
                }
            }
            $nombreRol = '';
            foreach ($roles as $rol) {
                if ($rol['id_rol'] == $value['id_rol']) {
                    $nombreRol = $rol['descripcion'];
                    break;
                }
            }

            $sheet->setCellValue('A' . $dataRow, $value["nombre"]);
            $sheet->setCellValue('B' . $dataRow, $value["usuario"]);
            $sheet->setCellValue('C' . $dataRow, $nombreGrupo);
            $sheet->setCellValue('D' . $dataRow, $nombreRol);
            $email = isset($value['email']) ? $value['email'] : '';
            $sheet->setCellValue('E' . $dataRow, $email);
            $sheet->setCellValue('F' . $dataRow, $value['estado_usuario']);

            $sheet->getStyle('A' . $dataRow . ':F' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 40,     // Nombre
            'B' => 'auto', // Usuario
            'C' => 30,     // Grupo
            'D' => 30,     // Rol
            'E' => 45,     // Email (Suele ser largo)
            'F' => 'auto'  // Status
        ]);

        $nombreArchivo = 'Usuarios_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function grupo_excel()
    {
        ob_start();
        require_once 'Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $perdoc = $this->model->selectPerDoc();

        $excel = new ReportTemplateExcel('GRUPOS Y DEPENDENCIAS', 'SCANTEC');
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '878787']],
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        ];

        // Encabezados en fila 4 (estandarizado)
        $headerRow = 4;
        $headers = ['ID GRUPO', 'NOMBRE GRUPO', 'ID TIPO DOC', 'TIPO DOCUMENTO'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:D$headerRow")->applyFromArray($headerStyle);

        // Datos desde fila 5
        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;

        foreach ($perdoc as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["id_grupo"]);
            $sheet->setCellValue('B' . $dataRow, $value["descripcion"]);
            $sheet->setCellValue('C' . $dataRow, $value["id_tipoDoc"]);
            $sheet->setCellValue('D' . $dataRow, $value['nombre_tipoDoc']);

            $sheet->getStyle('A' . $dataRow . ':D' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        // Ajustar columnas
        $excel->setColumnWidths([
            'A' => 'auto',
            'B' => 45, // Descripción de grupo larga
            'C' => 'auto',
            'D' => 45  // Tipo de documento largo
        ]);

        $excel->output('Grupo_Dependencias_' . date('Y_m_d_His'));
    }

    public function usuario_muestra()
    {
        date_default_timezone_set('America/Asuncion');
        $ruta = base_url() . 'Assets/files/usuarios.csv';

        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=' . $ruta . '".csv');
        readfile("./saldos.csv");
    }
}