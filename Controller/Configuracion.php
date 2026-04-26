<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$base_path = realpath(__DIR__ . '/../');

// Incluir PHPMailer
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/Exception.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/PHPMailer.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/SMTP.php';

class Configuracion extends Controllers
{
    private $configuracionModel, $db;
    private $usuariosModel;

    private function esAdministradorScantec(): bool
    {
        return intval($_SESSION['id_rol'] ?? 0) === 1;
    }

    private function getDepartamentoActualParaTiposLegajo(): int
    {
        return $this->esAdministradorScantec() ? 0 : intval($_SESSION['id_departamento'] ?? 0);
    }

    private function obtenerTiposLegajoVisiblesParaMatriz(): array
    {
        $tiposDisponibles = $this->model->getTiposDocumentoLegajo(0, false);
        if ($this->esAdministradorScantec()) {
            return $tiposDisponibles;
        }

        $idRol = intval($_SESSION['id_rol'] ?? 0);
        if ($idRol <= 0 || empty($tiposDisponibles)) {
            return [];
        }

        try {
            if (!class_exists('SeguridadLegajosModel')) {
                require_once 'Models/SeguridadLegajosModel.php';
            }

            $seguridadModel = new SeguridadLegajosModel();
            $tiposNormalizados = array_map(static function ($tipo) {
                $idTipo = intval($tipo['id_tipo_legajo'] ?? ($tipo['id_tipoDoc'] ?? 0));
                $tipo['id_tipo_legajo'] = $idTipo;
                return $tipo;
            }, $tiposDisponibles);

            $idsPermitidos = $seguridadModel->obtenerTiposLegajoPermitidosPorRol($idRol, $tiposNormalizados);
            if (empty($idsPermitidos)) {
                return [];
            }

            return array_values(array_filter($tiposDisponibles, static function ($tipo) use ($idsPermitidos) {
                $idTipo = intval($tipo['id_tipo_legajo'] ?? ($tipo['id_tipoDoc'] ?? 0));
                return in_array($idTipo, $idsPermitidos, true);
            }));
        } catch (Throwable $e) {
            return [];
        }
    }

    private function puedeAccederItemConfiguracion(string $itemKey): bool
    {
        if ($this->esAdministradorScantec()) {
            return true;
        }

        try {
            if (!class_exists('FuncionalidadesModel')) {
                require_once 'Models/FuncionalidadesModel.php';
            }

            $funcionalidadesModel = new FuncionalidadesModel();
            return $funcionalidadesModel->puedeAccederItemPorContexto(
                $itemKey,
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    private function obtenerDirectorioBranding(): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__)), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'branding';
    }

    private function obtenerLogoBrandingUrl(string $baseNombre): string
    {
        $directorio = $this->obtenerDirectorioBranding();
        if (!is_dir($directorio)) {
            return '';
        }

        $coincidencias = glob($directorio . DIRECTORY_SEPARATOR . $baseNombre . '.*') ?: [];
        if (empty($coincidencias)) {
            return '';
        }

        $archivo = basename($coincidencias[0]);
        return base_url() . 'Assets/img/branding/' . rawurlencode($archivo) . '?v=' . @filemtime($coincidencias[0]);
    }

    private function guardarLogoBranding(string $inputName, string $baseNombre): bool
    {
        if (empty($_FILES[$inputName]) || intval($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        $archivo = $_FILES[$inputName];
        if (intval($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        $mimesPermitidos = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        if (!scantecValidarUpload($archivo, $permitidas, $mimesPermitidos, 2 * 1024 * 1024)) {
            return false;
        }

        $directorio = $this->obtenerDirectorioBranding();
        if (!is_dir($directorio) && !mkdir($directorio, 0777, true) && !is_dir($directorio)) {
            return false;
        }

        foreach (glob($directorio . DIRECTORY_SEPARATOR . $baseNombre . '.*') ?: [] as $existente) {
            @unlink($existente);
        }

        $destino = $directorio . DIRECTORY_SEPARATOR . $baseNombre . '.' . $extension;
        return move_uploaded_file($archivo['tmp_name'], $destino);
    }

    private function asegurarAccesoBaseUsuariosExterna(): void
    {
        if ($this->esAdministradorScantec() || $this->puedeAccederItemConfiguracion('conexiones')) {
            return;
        }

        setAlert('error', "No tienes permisos para gestionar la base de datos externa.");
        header("Location: " . base_url() . "dashboard/dashboard_legajos");
        exit();
    }

    private function obtenerRutaEnv(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    }

    private function limpiarEnvValue(string $valor): string
    {
        return str_replace(["\r", "\n"], '', $valor);
    }

    private function sanitizarIdentificadorEnv(string $valor, bool $permitirVacio = false): string
    {
        $valor = preg_replace('/[^A-Za-z0-9_]/', '', trim($valor));
        if ($permitirVacio) {
            return $valor;
        }
        return $valor !== '' ? $valor : '';
    }

    private function obtenerConfiguracionBaseUsuariosDesdeFuente(array $fuente): array
    {
        // Si la fuente tiene claves con prefijo db_usuarios_ es el config global/env.
        // Antes se detectaba con 'host'/'name'/'user', pero SCANTEC_APP_CONFIG también
        // tiene una clave 'host' (el host de la aplicación), lo que hacía que se leyeran
        // las claves planas incorrectas (enabled, name vacíos) en vez de db_usuarios_*.
        $esFormatoEnv = array_key_exists('db_usuarios_enabled', $fuente)
            || array_key_exists('db_usuarios_host', $fuente)
            || array_key_exists('db_usuarios_name', $fuente);

        if (!$esFormatoEnv) {
            // Formato plano: viene del config temporal de sesión (POST data guardada)
            return [
                'enabled' => trim((string)($fuente['enabled'] ?? '0')) === '1' ? '1' : '0',
                'host'    => trim((string)($fuente['host'] ?? 'localhost')),
                'port'    => trim((string)($fuente['port'] ?? '3306')),
                'name'    => trim((string)($fuente['name'] ?? '')),
                'user'    => trim((string)($fuente['user'] ?? '')),
                'password'              => (string)($fuente['password'] ?? ''),
                'table'                 => $this->sanitizarIdentificadorEnv((string)($fuente['table'] ?? 'usuarios_datos')),
                'field_id'              => $this->sanitizarIdentificadorEnv((string)($fuente['field_id'] ?? 'id')),
                'field_nombre'          => $this->sanitizarIdentificadorEnv((string)($fuente['field_nombre'] ?? 'nombre'), true),
                'field_apellido'        => $this->sanitizarIdentificadorEnv((string)($fuente['field_apellido'] ?? 'apellido'), true),
                'field_nombre_completo' => $this->sanitizarIdentificadorEnv((string)($fuente['field_nombre_completo'] ?? ''), true),
                'field_ci'              => $this->sanitizarIdentificadorEnv((string)($fuente['field_ci'] ?? 'nro_cedula')),
                'field_solicitud'       => $this->sanitizarIdentificadorEnv((string)($fuente['field_solicitud'] ?? ''), true),
            ];
        }

        return [
            'enabled' => trim((string)($fuente['db_usuarios_enabled'] ?? '0')) === '1' ? '1' : '0',
            'host' => trim((string)($fuente['db_usuarios_host'] ?? 'localhost')),
            'port' => trim((string)($fuente['db_usuarios_port'] ?? '3306')),
            'name' => trim((string)($fuente['db_usuarios_name'] ?? '')),
            'user' => trim((string)($fuente['db_usuarios_user'] ?? '')),
            'password' => (string)($fuente['db_usuarios_password'] ?? ''),
            'table' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_table'] ?? 'usuarios_datos')),
            'field_id' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_id'] ?? 'id')),
            'field_nombre' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_nombre'] ?? 'nombre'), true),
            'field_apellido' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_apellido'] ?? 'apellido'), true),
            'field_nombre_completo' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_nombre_completo'] ?? ''), true),
            'field_ci' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_ci'] ?? 'nro_cedula')),
            'field_solicitud' => $this->sanitizarIdentificadorEnv((string)($fuente['db_usuarios_field_solicitud'] ?? ''), true),
        ];
    }

    private function obtenerConfiguracionBaseUsuariosActual(): array
    {
        // Si hay un config temporal en sesión (del último Probar o un Guardar fallido),
        // se mantiene hasta que el usuario guarde exitosamente o navegue fuera.
        if (!empty($_SESSION['db_usuarios_temp']) && is_array($_SESSION['db_usuarios_temp'])) {
            return $this->obtenerConfiguracionBaseUsuariosDesdeFuente($_SESSION['db_usuarios_temp']);
        }

        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];
        return $this->obtenerConfiguracionBaseUsuariosDesdeFuente($config);
    }

    private function obtenerConfiguracionBaseUsuariosPost(): array
    {
        $configActual = $this->obtenerConfiguracionBaseUsuariosDesdeFuente($GLOBALS['SCANTEC_APP_CONFIG'] ?? []);
        $passwordPost = (string)($_POST['password'] ?? '');

        return [
            'enabled' => intval($_POST['enabled'] ?? 0) === 1 ? '1' : '0',
            'host' => trim((string)($_POST['host'] ?? '')),
            'port' => preg_replace('/[^0-9]/', '', trim((string)($_POST['port'] ?? '3306'))),
            'name' => trim((string)($_POST['name'] ?? '')),
            'user' => trim((string)($_POST['user'] ?? '')),
            'password' => $passwordPost !== '' ? $passwordPost : (string)($configActual['password'] ?? ''),
            'table' => $this->sanitizarIdentificadorEnv((string)($_POST['table'] ?? 'usuarios_datos')),
            'field_id' => $this->sanitizarIdentificadorEnv((string)($_POST['field_id'] ?? 'id')),
            'field_nombre' => $this->sanitizarIdentificadorEnv((string)($_POST['field_nombre'] ?? 'nombre'), true),
            'field_apellido' => $this->sanitizarIdentificadorEnv((string)($_POST['field_apellido'] ?? 'apellido'), true),
            'field_nombre_completo' => $this->sanitizarIdentificadorEnv((string)($_POST['field_nombre_completo'] ?? ''), true),
            'field_ci' => $this->sanitizarIdentificadorEnv((string)($_POST['field_ci'] ?? 'nro_cedula')),
            'field_solicitud' => $this->sanitizarIdentificadorEnv((string)($_POST['field_solicitud'] ?? ''), true),
        ];
    }

    private function validarConfiguracionBaseUsuarios(array $config): array
    {
        if (($config['enabled'] ?? '0') !== '1') {
            return ['ok' => true, 'message' => 'Base externa deshabilitada.'];
        }

        $camposRequeridos = [
            'host' => 'Host',
            'port' => 'Puerto',
            'name' => 'Base de datos',
            'user' => 'Usuario',
            'table' => 'Tabla',
            'field_id' => 'Campo ID',
            'field_ci' => 'Campo CI',
        ];

        foreach ($camposRequeridos as $clave => $etiqueta) {
            if (trim((string)($config[$clave] ?? '')) === '') {
                return ['ok' => false, 'message' => "El campo {$etiqueta} es obligatorio."];
            }
        }

        $port = intval($config['port'] ?? 0);
        if ($port < 1 || $port > 65535) {
            return ['ok' => false, 'message' => 'El puerto debe estar entre 1 y 65535.'];
        }

        $usaNombreCompleto = trim((string)($config['field_nombre_completo'] ?? '')) !== '';
        $tieneNombre = trim((string)($config['field_nombre'] ?? '')) !== '';
        $tieneApellido = trim((string)($config['field_apellido'] ?? '')) !== '';

        if (!$usaNombreCompleto && (!$tieneNombre || !$tieneApellido)) {
            return ['ok' => false, 'message' => 'Debe indicar Nombre completo o bien Nombre y Apellido por separado.'];
        }

        return ['ok' => true, 'message' => 'Configuracion valida.'];
    }

    private function probarConexionBaseUsuarios(array $config): array
    {
        $validacion = $this->validarConfiguracionBaseUsuarios($config);
        if (!$validacion['ok']) {
            return $validacion;
        }

        if (($config['enabled'] ?? '0') !== '1') {
            return ['ok' => true, 'message' => 'Base externa deshabilitada. La configuracion puede guardarse sin probar conexion.'];
        }

        $tabla = $config['table'];
        $camposEsperados = [
            $config['field_id'] => 'ID',
            $config['field_ci'] => 'CI',
        ];
        if (trim((string)$config['field_nombre_completo']) !== '') {
            $camposEsperados[$config['field_nombre_completo']] = 'Nombre completo';
        } else {
            $camposEsperados[$config['field_nombre']] = 'Nombre';
            $camposEsperados[$config['field_apellido']] = 'Apellido';
        }
        if (trim((string)$config['field_solicitud']) !== '') {
            $camposEsperados[$config['field_solicitud']] = 'Solicitud';
        }

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $config['user'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $stmtColumnas = $pdo->query("SHOW COLUMNS FROM `{$tabla}`");
            $columnasDisponibles = [];
            foreach ($stmtColumnas->fetchAll() as $columna) {
                $nombreColumna = trim((string)($columna['Field'] ?? ''));
                if ($nombreColumna !== '') {
                    $columnasDisponibles[$nombreColumna] = true;
                }
            }

            $faltantes = [];
            foreach ($camposEsperados as $campo => $etiqueta) {
                if ($campo !== '' && empty($columnasDisponibles[$campo])) {
                    $faltantes[] = "{$etiqueta} ({$campo})";
                }
            }

            if (!empty($faltantes)) {
                return ['ok' => false, 'message' => 'La tabla existe, pero faltan columnas: ' . implode(', ', $faltantes) . '.'];
            }

            $stmtTotal = $pdo->query("SELECT COUNT(*) AS total FROM `{$tabla}`");
            $total = intval($stmtTotal->fetch()['total'] ?? 0);

            $mensaje = "Conexion exitosa a {$config['host']}:{$config['port']} / {$config['name']} con el usuario {$config['user']} y tabla `{$tabla}`.";
            $mensaje .= $total > 0
                ? " Registros disponibles: {$total}."
                : " La tabla esta vacia por ahora.";

            return ['ok' => true, 'message' => $mensaje];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Error de conexion a ' . $config['host'] . ':' . $config['port'] . ' / ' . $config['name'] . ' con el usuario ' . $config['user'] . ': ' . $e->getMessage()];
        }
    }

    private function guardarConfiguracionBaseUsuariosEnv(array $config): array
    {
        $rutaEnv = $this->obtenerRutaEnv();
        if (!is_file($rutaEnv) || !is_readable($rutaEnv) || !is_writable($rutaEnv)) {
            return ['ok' => false, 'message' => 'No se pudo acceder al archivo .env para guardar los cambios.'];
        }

        $contenidoActual = file_get_contents($rutaEnv);
        if ($contenidoActual === false) {
            return ['ok' => false, 'message' => 'No se pudo leer el archivo .env actual.'];
        }

        $lineas = preg_split("/\\r\\n|\\n|\\r/", $contenidoActual);
        if (!is_array($lineas)) {
            $lineas = [];
        }

        $eol = str_contains($contenidoActual, "\r\n") ? "\r\n" : "\n";
        $valoresEnv = [
            'SCANTEC_DB_USUARIOS_ENABLED' => $config['enabled'],
            'SCANTEC_DB_USUARIOS_HOST' => $this->limpiarEnvValue($config['host']),
            'SCANTEC_DB_USUARIOS_PORT' => $this->limpiarEnvValue($config['port']),
            'SCANTEC_DB_USUARIOS_NAME' => $this->limpiarEnvValue($config['name']),
            'SCANTEC_DB_USUARIOS_USER' => $this->limpiarEnvValue($config['user']),
            'SCANTEC_DB_USUARIOS_PASSWORD' => $this->limpiarEnvValue($config['password']),
            'SCANTEC_DB_USUARIOS_TABLE' => $this->limpiarEnvValue($config['table']),
            'SCANTEC_DB_USUARIOS_FIELD_ID' => $this->limpiarEnvValue($config['field_id']),
            'SCANTEC_DB_USUARIOS_FIELD_NOMBRE' => $this->limpiarEnvValue($config['field_nombre']),
            'SCANTEC_DB_USUARIOS_FIELD_APELLIDO' => $this->limpiarEnvValue($config['field_apellido']),
            'SCANTEC_DB_USUARIOS_FIELD_NOMBRE_COMPLETO' => $this->limpiarEnvValue($config['field_nombre_completo']),
            'SCANTEC_DB_USUARIOS_FIELD_CI' => $this->limpiarEnvValue($config['field_ci']),
            'SCANTEC_DB_USUARIOS_FIELD_SOLICITUD' => $this->limpiarEnvValue($config['field_solicitud']),
        ];

        $clavesActualizadas = [];
        foreach ($lineas as $indice => $linea) {
            $pos = strpos($linea, '=');
            if ($pos === false) {
                continue;
            }

            $clave = trim(substr($linea, 0, $pos));
            if (!array_key_exists($clave, $valoresEnv)) {
                continue;
            }

            $lineas[$indice] = $clave . '=' . $valoresEnv[$clave];
            $clavesActualizadas[$clave] = true;
        }

        foreach ($valoresEnv as $clave => $valor) {
            if (!isset($clavesActualizadas[$clave])) {
                $lineas[] = $clave . '=' . $valor;
            }
        }

        $nuevoContenido = implode($eol, $lineas);
        if ($contenidoActual !== '' && !str_ends_with($nuevoContenido, $eol)) {
            $nuevoContenido .= $eol;
        }

        $guardado = file_put_contents($rutaEnv, $nuevoContenido);
        if ($guardado === false) {
            return ['ok' => false, 'message' => 'No se pudo escribir la nueva configuracion en .env.'];
        }

        foreach ($valoresEnv as $clave => $valor) {
            putenv($clave . '=' . $valor);
            $_ENV[$clave] = $valor;
            $_SERVER[$clave] = $valor;
        }

        if (!isset($GLOBALS['SCANTEC_APP_CONFIG']) || !is_array($GLOBALS['SCANTEC_APP_CONFIG'])) {
            $GLOBALS['SCANTEC_APP_CONFIG'] = [];
        }

        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_enabled'] = $config['enabled'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_host'] = $config['host'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_port'] = $config['port'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_name'] = $config['name'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_user'] = $config['user'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_password'] = $config['password'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_table'] = $config['table'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_id'] = $config['field_id'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_nombre'] = $config['field_nombre'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_apellido'] = $config['field_apellido'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_nombre_completo'] = $config['field_nombre_completo'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_ci'] = $config['field_ci'];
        $GLOBALS['SCANTEC_APP_CONFIG']['db_usuarios_field_solicitud'] = $config['field_solicitud'];

        return ['ok' => true, 'message' => 'Configuracion guardada en .env correctamente.'];
    }

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
        $this->configuracionModel = new ConfiguracionModel();
        $usuariosModelPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'UsuariosModel.php';
        if (!class_exists('UsuariosModel') && file_exists($usuariosModelPath)) {
            require_once $usuariosModelPath;
        }
        $this->usuariosModel = new UsuariosModel();
        $this->db = new Mysql();
    }

    public function listar()
    {
        if (empty($_SESSION['csrf_token']) || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        $data = $this->model->selectConfiguracion();
        if (isset($data[0]) && is_array($data[0])) {
            $data[0]['logo_empresa_url'] = $this->obtenerLogoBrandingUrl('logo_empresa');
            $data[0]['logo_empresa_reducido_url'] = $this->obtenerLogoBrandingUrl('logo_empresa_reducido');
            $data[0]['departamentos'] = $this->usuariosModel->selectTodosDepartamentos();
            $data[0]['total_usuarios'] = intval($this->usuariosModel->contarUsuariosActivos()['total'] ?? 0);
        }
        $this->views->getView($this, "listar", $data);
    }

    public function mantenimiento()
    {
        if (empty($_SESSION['csrf_token']) || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        $data = $this->model->selectConfiguracion();
        if (isset($data[0]) && is_array($data[0])) {
            $data[0]['logo_empresa_url'] = $this->obtenerLogoBrandingUrl('logo_empresa');
            $data[0]['logo_empresa_reducido_url'] = $this->obtenerLogoBrandingUrl('logo_empresa_reducido');
            $data[0]['departamentos'] = $this->usuariosModel->selectTodosDepartamentos();
            $data[0]['total_usuarios'] = intval($this->usuariosModel->contarUsuariosActivos()['total'] ?? 0);
        }
        $this->views->getView($this, "mantenimiento", $data);
    }

    public function guardar_departamento()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/listar");
            exit();
        }

        $nombre = trim((string)($_POST['nombre_departamento'] ?? ''));
        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre del departamento.");
            header("Location: " . base_url() . "configuracion/listar");
            exit();
        }

        $idDepartamento = $this->usuariosModel->resolverDepartamentoIdPorNombre($nombre, true);
        if ($idDepartamento > 0) {
            $this->usuariosModel->cambiarEstadoDepartamento($idDepartamento, 'ACTIVO');
            setAlert('success', "Departamento guardado correctamente.");
        } else {
            setAlert('error', "No se pudo guardar el departamento.");
        }

        header("Location: " . base_url() . "configuracion/listar");
        exit();
    }

    public function actualizar_departamento()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/listar");
            exit();
        }

        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $nombre = trim((string)($_POST['nombre_departamento'] ?? ''));
        $estado = strtoupper(trim((string)($_POST['estado_departamento'] ?? 'ACTIVO')));

        if ($idDepartamento <= 0 || $nombre === '') {
            setAlert('warning', "Datos inválidos para actualizar el departamento.");
            header("Location: " . base_url() . "configuracion/listar");
            exit();
        }

        $ok = $this->usuariosModel->actualizarDepartamento($idDepartamento, $nombre, $estado);
        setAlert($ok ? 'success' : 'error', $ok ? "Departamento actualizado correctamente." : "No se pudo actualizar el departamento.");

        header("Location: " . base_url() . "configuracion/listar");
        exit();
    }

    public function eliminar_departamento()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/listar");
            exit();
        }

        $idDepartamento = intval($_POST['id_departamento'] ?? 0);
        $accion = trim((string)($_POST['accion_departamento'] ?? 'desactivar'));
        $resultado = $this->usuariosModel->eliminarDepartamento($idDepartamento, $accion);

        switch ($resultado) {
            case 'eliminado':
                setAlert('success', "Departamento eliminado correctamente.");
                break;
            case 'desactivado':
                setAlert('success', "Departamento desactivado correctamente.");
                break;
            case 'desactivado_en_uso':
                setAlert('warning', "El departamento está en uso y fue desactivado para evitar errores.");
                break;
            case 'invalido':
                setAlert('warning', "Departamento inválido.");
                break;
            default:
                setAlert('error', "No se pudo procesar el departamento.");
                break;
        }

        header("Location: " . base_url() . "configuracion/listar");
        exit();
    }

    public function configuracion_legajos()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        $catalogo_documentos = $this->model->getCatalogoDocumentosLegajo();
        $idDepartamentoActual = $this->getDepartamentoActualParaTiposLegajo();
        $filtrarTiposPorDepartamento = !$this->esAdministradorScantec() && $idDepartamentoActual > 0;
        $tipos_documento_departamento = $this->model->getTiposDocumentoLegajo($idDepartamentoActual, $filtrarTiposPorDepartamento);
        $tipos_documento = $this->obtenerTiposLegajoVisiblesParaMatriz();
        $tipos_documento_matriz = $tipos_documento;
        $tab_actual = isset($_GET['tab']) ? $_GET['tab'] : 'catalogo';
        $id_documento_editar = isset($_GET['editar_documento']) ? intval($_GET['editar_documento']) : 0;
        $id_tipo_legajo_editar = isset($_GET['editar_tipo_legajo']) ? intval($_GET['editar_tipo_legajo']) : 0;
        $id_requisito_editar = isset($_GET['editar_requisito']) ? intval($_GET['editar_requisito']) : 0;
        $documento_editar = null;
        $tipo_legajo_editar = null;
        $requisito_editar = null;
        if ($id_documento_editar > 0) {
            $documento_editar = $this->model->getCatalogoDocumentoLegajoById($id_documento_editar);
        }
        if ($id_tipo_legajo_editar > 0) {
            $tipo_legajo_editar = $this->model->getTipoLegajoById($id_tipo_legajo_editar);
        }

        $id_tipoDoc = isset($_GET['id_tipoDoc']) ? intval($_GET['id_tipoDoc']) : 0;
        if ($id_tipoDoc <= 0 && !empty($tipos_documento_matriz)) {
            $id_tipoDoc = intval($tipos_documento_matriz[0]['id_tipoDoc']);
        }

        $matriz_requisitos = $id_tipoDoc > 0
            ? $this->model->getMatrizRequisitosLegajo($id_tipoDoc)
            : [];

        $tipo_documento_actual = null;
        foreach ($tipos_documento_matriz as $tipo_documento) {
            if (intval($tipo_documento['id_tipoDoc']) === $id_tipoDoc) {
                $tipo_documento_actual = $tipo_documento;
                break;
            }
        }

        if ($id_requisito_editar > 0) {
            $requisito_editar = $this->model->getMatrizRequisitoLegajoById($id_requisito_editar);
            if (!empty($requisito_editar)) {
                $id_tipoDoc = intval($requisito_editar['id_tipoDoc'] ?? $id_tipoDoc);
                $matriz_requisitos = $id_tipoDoc > 0
                    ? $this->model->getMatrizRequisitosLegajo($id_tipoDoc)
                    : [];

                foreach ($tipos_documento_matriz as $tipo_documento) {
                    if (intval($tipo_documento['id_tipoDoc']) === $id_tipoDoc) {
                        $tipo_documento_actual = $tipo_documento;
                        break;
                    }
                }
            }
        }

        $relaciones = $this->model->getRelacionesActivas();
        $politicas_actualizacion = $this->model->getPoliticasActualizacionActivas();
        
        $todas_relaciones = $this->model->getRelaciones();
        $todas_politicas = $this->model->getPoliticasActualizacion();
        $puedeVerTiposRelacionArchivos = $this->puedeAccederItemConfiguracion('tipos_relacion_archivos');
        $puedeVerMetodosActualizacionArchivos = $this->puedeAccederItemConfiguracion('metodos_actualizacion_archivos');
        $puedeVerDatosGenerales = $puedeVerTiposRelacionArchivos || $puedeVerMetodosActualizacionArchivos;

        if ($tab_actual === 'datos' && !$puedeVerDatosGenerales) {
            $tab_actual = 'matriz';
        }

        $data = [
            'catalogo_documentos' => $catalogo_documentos,
            'tipos_documento' => $tipos_documento,
            'tipos_documento_departamento' => $tipos_documento_departamento,
            'tipos_documento_matriz' => $tipos_documento_matriz,
            'matriz_requisitos' => $matriz_requisitos,
            'id_tipoDoc_actual' => $id_tipoDoc,
            'tipo_documento_actual' => $tipo_documento_actual,
            'id_departamento_actual' => $idDepartamentoActual,
            'filtrar_tipos_por_departamento' => $filtrarTiposPorDepartamento,
            'tab_actual' => $tab_actual,
            'documento_editar' => $documento_editar,
            'tipo_legajo_editar' => $tipo_legajo_editar,
            'requisito_editar' => $requisito_editar,
            'relaciones' => $relaciones,
            'politicas_actualizacion' => $politicas_actualizacion,
            'todas_relaciones' => $todas_relaciones,
            'todas_politicas' => $todas_politicas,
            'puede_ver_datos_generales' => $puedeVerDatosGenerales,
            'puede_ver_tipos_relacion_archivos' => $puedeVerTiposRelacionArchivos,
            'puede_ver_metodos_actualizacion_archivos' => $puedeVerMetodosActualizacionArchivos
        ];
        $this->views->getView($this, "configuracion_legajos", $data);
    }

    private function normalizarTipoCampoCatalogo(?string $tipoCampo): string
    {
        $tipoCampo = strtolower(trim((string)$tipoCampo));
        $permitidos = ['documento', 'texto', 'lista', 'casilla'];
        return in_array($tipoCampo, $permitidos, true) ? $tipoCampo : 'documento';
    }

    private function normalizarOpcionesCampoCatalogo(?string $opciones): string
    {
        $lineas = preg_split('/\r\n|\r|\n/', (string)$opciones);
        $lineas = array_map(static function ($item) {
            return trim((string)$item);
        }, is_array($lineas) ? $lineas : []);
        $lineas = array_values(array_unique(array_filter($lineas, static function ($item) {
            return $item !== '';
        })));

        return implode(PHP_EOL, $lineas);
    }

    public function guardar_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_interno = trim($_POST['codigo_interno'] ?? '');
        $tipo_campo = $this->normalizarTipoCampoCatalogo($_POST['tipo_campo'] ?? 'documento');
        $opciones_campo = $tipo_campo === 'lista'
            ? $this->normalizarOpcionesCampoCatalogo($_POST['opciones_campo'] ?? '')
            : '';
        $tiene_vencimiento = intval($_POST['tiene_vencimiento'] ?? 0) === 1 ? 1 : 0;
        if ($tipo_campo !== 'documento') {
            $tiene_vencimiento = 0;
        }
        $dias_vigencia_base = $tiene_vencimiento ? intval($_POST['dias_vigencia_base'] ?? 0) : null;
        $dias_alerta_previa = $tiene_vencimiento ? intval($_POST['dias_alerta_previa'] ?? 30) : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre del documento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        if ($tipo_campo === 'lista' && $opciones_campo === '') {
            setAlert('warning', "Debe cargar al menos una opcion para la lista desplegable.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        if ($tiene_vencimiento && $dias_vigencia_base !== null && $dias_vigencia_base <= 0) {
            setAlert('warning', "Los años de vigencia deben ser mayores a cero.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $insert = $this->model->insertarCatalogoDocumentoLegajo(
            $nombre,
            $codigo_interno,
            $tipo_campo,
            $opciones_campo !== '' ? $opciones_campo : null,
            $tiene_vencimiento,
            $dias_vigencia_base,
            $tiene_vencimiento ? ($dias_alerta_previa > 0 ? $dias_alerta_previa : 30) : null,
            $activo
        );

        if ($insert) {
            setAlert('success', "Documento maestro registrado correctamente.");
        } else {
            setAlert('error', "No se pudo registrar el documento maestro.");
        }

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function actualizar_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_interno = trim($_POST['codigo_interno'] ?? '');
        $tipo_campo = $this->normalizarTipoCampoCatalogo($_POST['tipo_campo'] ?? 'documento');
        $opciones_campo = $tipo_campo === 'lista' ? $this->normalizarOpcionesCampoCatalogo($_POST['opciones_campo'] ?? '') : '';
        $tiene_vencimiento = intval($_POST['tiene_vencimiento'] ?? 0) === 1 ? 1 : 0;
        if ($tipo_campo !== 'documento') {
            $tiene_vencimiento = 0;
        }
        $dias_vigencia_base = $tiene_vencimiento ? intval($_POST['dias_vigencia_base'] ?? 0) : null;
        $dias_alerta_previa = $tiene_vencimiento ? intval($_POST['dias_alerta_previa'] ?? 30) : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id_documento_maestro <= 0 || $nombre === '') {
            setAlert('warning', "Datos inválidos para actualizar el documento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        if ($tipo_campo === 'lista' && $opciones_campo === '') {
            setAlert('warning', "Debe cargar al menos una opcion para la lista desplegable.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo&editar_documento=" . $id_documento_maestro);
            exit();
        }

        if ($tiene_vencimiento && $dias_vigencia_base !== null && $dias_vigencia_base <= 0) {
            setAlert('warning', "Los años de vigencia deben ser mayores a cero.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo&editar_documento=" . $id_documento_maestro);
            exit();
        }

        $ok = $this->model->actualizarCatalogoDocumentoLegajo(
            $id_documento_maestro,
            $nombre,
            $codigo_interno,
            $tipo_campo,
            $opciones_campo !== '' ? $opciones_campo : null,
            $tiene_vencimiento,
            $dias_vigencia_base,
            $tiene_vencimiento ? ($dias_alerta_previa > 0 ? $dias_alerta_previa : 30) : null,
            $activo
        );

        setAlert($ok ? 'success' : 'error', $ok ? "Documento actualizado correctamente." : "No se pudo actualizar el documento.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function cambiar_estado_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($id_documento_maestro <= 0) {
            setAlert('warning', "Documento inválido.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $ok = $this->model->actualizarEstadoCatalogoDocumentoLegajo($id_documento_maestro, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function guardar_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);
        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $rol_vinculado = trim($_POST['rol_vinculado'] ?? 'TITULAR');
        $es_obligatorio = isset($_POST['es_obligatorio']) ? 1 : 0;
        $orden_visual = intval($_POST['orden_visual'] ?? 1);
        $politicaActualizacion = strtoupper(trim($_POST['politica_actualizacion'] ?? 'REEMPLAZAR'));
        $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
        if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
            $politicaActualizacion = 'REEMPLAZAR';
        }
        $permite_reemplazo = $politicaActualizacion === 'NO_PERMITIR' ? 0 : 1;

        if ($id_tipoDoc <= 0 || $id_documento_maestro <= 0) {
            setAlert('warning', "Debe seleccionar el tipo de legajo y el documento maestro.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        if ($this->model->existeMatrizRequisitoLegajo($id_tipoDoc, $id_documento_maestro, $rol_vinculado)) {
            setAlert('warning', "Ya existe una regla para ese documento y rol en el tipo seleccionado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $insert = $this->model->insertarMatrizRequisitoLegajo(
            $id_tipoDoc,
            $id_documento_maestro,
            $rol_vinculado,
            $es_obligatorio,
            $orden_visual > 0 ? $orden_visual : 1,
            $permite_reemplazo,
            $politicaActualizacion
        );

        if ($insert) {
            setAlert('success', "Regla agregada correctamente.");
        } else {
            setAlert('error', "No se pudo agregar la regla.");
        }

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    public function guardar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $nombre = trim($_POST['nombre_tipo_legajo'] ?? '');
        $descripcion = trim($_POST['descripcion_tipo_legajo'] ?? '');
        $requiereNroSolicitud = isset($_POST['requiere_nro_solicitud']) ? 1 : 0;
        $selloCaratulaTexto = trim((string)($_POST['sello_caratula_texto'] ?? ''));
        $selloCaratulaPosicion = trim((string)($_POST['sello_caratula_posicion'] ?? 'arriba'));
        $selloAnexosTexto = trim((string)($_POST['sello_anexos_texto'] ?? ''));
        $selloAnexosPosicion = trim((string)($_POST['sello_anexos_posicion'] ?? 'derecha'));
        if ($selloCaratulaPosicion === 'cruzado') {
            $selloCaratulaPosicion = 'arriba';
        }
        if (!in_array($selloCaratulaPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $selloCaratulaPosicion = 'arriba';
        }
        if (!in_array($selloAnexosPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $selloAnexosPosicion = 'derecha';
        }
        $idDepartamentoActual = $this->getDepartamentoActualParaTiposLegajo();
        $filtrarTiposPorDepartamento = !$this->esAdministradorScantec() && $idDepartamentoActual > 0;

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre del tipo de legajo.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        if ($this->model->existeTipoLegajoPorNombre($nombre, $idDepartamentoActual, $filtrarTiposPorDepartamento)) {
            setAlert('warning', "Ese tipo de legajo ya existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $this->model->sello_caratula_texto = $selloCaratulaTexto;
        $this->model->sello_caratula_posicion = $selloCaratulaPosicion;
        $this->model->sello_anexos_texto = $selloAnexosTexto;
        $this->model->sello_anexos_posicion = $selloAnexosPosicion;
        $insert = $this->model->insertarTipoLegajo(
            $nombre,
            $descripcion !== '' ? $descripcion : null,
            1,
            $requiereNroSolicitud,
            $filtrarTiposPorDepartamento ? $idDepartamentoActual : null
        );
        if ($insert) {
            if (!$this->esAdministradorScantec()) {
                if (!class_exists('SeguridadLegajosModel')) {
                    require_once 'Models/SeguridadLegajosModel.php';
                }
                $seguridadModel = new SeguridadLegajosModel();
                $seguridadModel->agregarPermisoTipoLegajo(intval($_SESSION['id_rol'] ?? 0), $insert);
            }
            setAlert('success', "Tipo de legajo registrado correctamente.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        setAlert('error', "No se pudo registrar el tipo de legajo.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function actualizar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $idTipoLegajo = intval($_POST['id_tipo_legajo'] ?? 0);
        $idDepartamentoActual = $this->getDepartamentoActualParaTiposLegajo();
        $filtrarTiposPorDepartamento = !$this->esAdministradorScantec() && $idDepartamentoActual > 0;
        $nombre = trim($_POST['nombre_tipo_legajo'] ?? '');
        $descripcion = trim($_POST['descripcion_tipo_legajo'] ?? '');
        $activo = isset($_POST['activo_tipo_legajo']) ? 1 : 0;
        $requiereNroSolicitud = isset($_POST['requiere_nro_solicitud']) ? 1 : 0;
        $selloCaratulaTexto = trim((string)($_POST['sello_caratula_texto'] ?? ''));
        $selloCaratulaPosicion = trim((string)($_POST['sello_caratula_posicion'] ?? 'arriba'));
        $selloAnexosTexto = trim((string)($_POST['sello_anexos_texto'] ?? ''));
        $selloAnexosPosicion = trim((string)($_POST['sello_anexos_posicion'] ?? 'derecha'));
        if ($selloCaratulaPosicion === 'cruzado') {
            $selloCaratulaPosicion = 'arriba';
        }
        if (!in_array($selloCaratulaPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $selloCaratulaPosicion = 'arriba';
        }
        if (!in_array($selloAnexosPosicion, ['arriba', 'abajo', 'derecha', 'izquierda'], true)) {
            $selloAnexosPosicion = 'derecha';
        }
        $idDepartamentoActual = $this->getDepartamentoActualParaTiposLegajo();
        $filtrarTiposPorDepartamento = !$this->esAdministradorScantec() && $idDepartamentoActual > 0;

        if ($idTipoLegajo <= 0 || $nombre === '') {
            setAlert('warning', "Datos inválidos para actualizar el tipo de legajo.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $actual = $this->model->getTipoLegajoById($idTipoLegajo);
        if (!$actual) {
            setAlert('error', "El tipo de legajo no existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        if ($filtrarTiposPorDepartamento && intval($actual['id_departamento'] ?? 0) !== $idDepartamentoActual) {
            setAlert('warning', "No puede modificar tipos de legajo de otro departamento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        if (strcasecmp(trim($actual['nombre'] ?? ''), $nombre) !== 0 && $this->model->existeTipoLegajoPorNombre($nombre, $idDepartamentoActual, $filtrarTiposPorDepartamento)) {
            setAlert('warning', "Ese tipo de legajo ya existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos&editar_tipo_legajo=" . $idTipoLegajo);
            exit();
        }

        $this->model->sello_caratula_texto = $selloCaratulaTexto;
        $this->model->sello_caratula_posicion = $selloCaratulaPosicion;
        $this->model->sello_anexos_texto = $selloAnexosTexto;
        $this->model->sello_anexos_posicion = $selloAnexosPosicion;
        $ok = $this->model->actualizarTipoLegajo(
            $idTipoLegajo,
            $nombre,
            $descripcion !== '' ? $descripcion : null,
            $activo,
            $requiereNroSolicitud,
            $filtrarTiposPorDepartamento ? $idDepartamentoActual : intval($actual['id_departamento'] ?? 0)
        );
        setAlert($ok ? 'success' : 'error', $ok ? "Tipo de legajo actualizado correctamente." : "No se pudo actualizar el tipo de legajo.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function eliminar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $idTipoLegajo = intval($_POST['id_tipo_legajo'] ?? 0);
        if ($idTipoLegajo <= 0) {
            setAlert('warning', "Tipo de legajo inválido.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $idDepartamentoActual = $this->getDepartamentoActualParaTiposLegajo();
        $filtrarTiposPorDepartamento = !$this->esAdministradorScantec() && $idDepartamentoActual > 0;

        $actual = $this->model->getTipoLegajoById($idTipoLegajo);
        if ($filtrarTiposPorDepartamento && intval($actual['id_departamento'] ?? 0) !== $idDepartamentoActual) {
            setAlert('warning', "No puede eliminar tipos de legajo de otro departamento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $ok = $this->model->eliminarTipoLegajo($idTipoLegajo);
        setAlert($ok ? 'success' : 'error', $ok ? "Tipo de legajo eliminado correctamente." : "No se pudo eliminar el tipo de legajo. Verifique si tiene reglas asociadas.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function eliminar_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $id_requisito = intval($_POST['id_requisito'] ?? 0);
        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);

        if ($id_requisito <= 0) {
            setAlert('warning', "Regla inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $ok = $this->model->eliminarMatrizRequisitoLegajo($id_requisito);
        setAlert($ok ? 'success' : 'error', $ok ? "Regla eliminada correctamente." : "No se pudo eliminar la regla.");

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    public function guardar_cambios_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);
        $reglas = $_POST['reglas'] ?? [];

        if ($id_tipoDoc <= 0 || empty($reglas) || !is_array($reglas)) {
            setAlert('warning', "No hay cambios válidos para guardar.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $actualizados = 0;
        foreach ($reglas as $idRequisito => $regla) {
            $idRequisito = intval($idRequisito);
            if ($idRequisito <= 0) {
                continue;
            }

            $idDocumentoMaestro = intval($regla['id_documento_maestro'] ?? 0);
            $rolVinculado = trim($regla['rol_vinculado'] ?? 'TITULAR');
            $esObligatorio = intval($regla['es_obligatorio'] ?? 0) === 1 ? 1 : 0;
            $ordenVisual = intval($regla['orden_visual'] ?? 1);
            $politicaActualizacion = strtoupper(trim($regla['politica_actualizacion'] ?? ''));
            $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
            if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
                $permiteReemplazoFallback = intval($regla['permite_reemplazo'] ?? 0) === 1;
                $politicaActualizacion = $permiteReemplazoFallback ? 'REEMPLAZAR' : 'NO_PERMITIR';
            }
            $permiteReemplazo = $politicaActualizacion === 'NO_PERMITIR' ? 0 : 1;

            if ($idDocumentoMaestro <= 0) {
                continue;
            }

            if ($this->model->existeOtroMatrizRequisitoLegajo($idRequisito, $id_tipoDoc, $idDocumentoMaestro, $rolVinculado)) {
                setAlert('warning', "Existe una regla duplicada para el documento y rol seleccionados.");
                header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
                exit();
            }

            $ok = $this->model->actualizarMatrizRequisitoLegajo(
                $idRequisito,
                $idDocumentoMaestro,
                $rolVinculado,
                $esObligatorio,
                $ordenVisual > 0 ? $ordenVisual : 1,
                $permiteReemplazo,
                $politicaActualizacion
            );

            if ($ok) {
                $actualizados++;
            }
        }

        setAlert($actualizados > 0 ? 'success' : 'info', $actualizados > 0 ? "Cambios de matriz guardados." : "No se detectaron cambios para guardar.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    public function actualizar_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $idRequisito = intval($_POST['id_requisito'] ?? 0);
        $idTipoDoc = intval($_POST['id_tipoDoc'] ?? 0);
        $idDocumentoMaestro = intval($_POST['id_documento_maestro'] ?? 0);
        $rolVinculado = trim($_POST['rol_vinculado'] ?? 'TITULAR');
        $esObligatorio = intval($_POST['es_obligatorio'] ?? 0) === 1 ? 1 : 0;
        $ordenVisual = max(1, intval($_POST['orden_visual'] ?? 1));
        $activo = intval($_POST['activo'] ?? 0) === 1 ? 1 : 0;
        $politicaActualizacion = strtoupper(trim($_POST['politica_actualizacion'] ?? ''));
        $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
        if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
            $politicaActualizacion = 'REEMPLAZAR';
        }
        $permiteReemplazo = $politicaActualizacion === 'NO_PERMITIR' ? 0 : 1;

        if ($idRequisito <= 0 || $idTipoDoc <= 0 || $idDocumentoMaestro <= 0) {
            setAlert('warning', "Regla inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $idTipoDoc);
            exit();
        }

        if ($this->model->existeOtroMatrizRequisitoLegajo($idRequisito, $idTipoDoc, $idDocumentoMaestro, $rolVinculado)) {
            setAlert('warning', "Existe una regla duplicada para el documento y rol seleccionados.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $idTipoDoc . "&editar_requisito=" . $idRequisito);
            exit();
        }

        $ok = $this->model->actualizarMatrizRequisitoLegajo(
            $idRequisito,
            $idDocumentoMaestro,
            $rolVinculado,
            $esObligatorio,
            $ordenVisual,
            $permiteReemplazo,
            $politicaActualizacion,
            $activo
        );

        setAlert($ok ? 'success' : 'error', $ok ? "Regla actualizada correctamente." : "No se pudo actualizar la regla.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $idTipoDoc);
        exit();
    }

    // ============================================================
    // SECCIÓN: Legajos Datos Generales (Administración de Relaciones)
    // ============================================================

    // ELIMINADO: public function datos_generales_legajos()

    public function guardar_relacion()
    {
        if (!$this->puedeAccederItemConfiguracion('tipos_relacion_archivos')) {
            setAlert('error', "No tienes permiso para gestionar los tipos de relacion.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $nombre = strtoupper(trim($_POST['nombre_relacion'] ?? ''));
        $orden = intval($_POST['orden_relacion'] ?? 0);

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre de la relación.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if ($this->model->existeRelacionPorNombre($nombre)) {
            setAlert('warning', "Ya existe una relación con ese nombre.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $insert = $this->model->insertarRelacion($nombre, $orden);
        setAlert($insert ? 'success' : 'error', $insert ? "Relación registrada correctamente." : "No se pudo registrar la relación.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function cambiar_estado_relacion()
    {
        if (!$this->puedeAccederItemConfiguracion('tipos_relacion_archivos')) {
            setAlert('error', "No tienes permiso para gestionar los tipos de relacion.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idRelacion = intval($_POST['id_relacion'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($idRelacion <= 0) {
            setAlert('warning', "Relación inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $ok = $this->model->cambiarEstadoRelacion($idRelacion, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function eliminar_relacion()
    {
        if (!$this->puedeAccederItemConfiguracion('tipos_relacion_archivos')) {
            setAlert('error', "No tienes permiso para gestionar los tipos de relacion.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idRelacion = intval($_POST['id_relacion'] ?? 0);
        if ($idRelacion <= 0) {
            setAlert('warning', "Relación inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $resultado = $this->model->eliminarRelacion($idRelacion);

        if ($resultado === 'EN_USO_MATRIZ') {
            setAlert('error', "No se puede eliminar: esta relación está asignada en reglas de la Matriz de Requisitos. Puede desactivarla en su lugar.");
        } elseif ($resultado === 'EN_USO_LEGAJOS') {
            setAlert('error', "No se puede eliminar: existen legajos armados que usan esta relación. Puede desactivarla en su lugar.");
        } elseif ($resultado) {
            setAlert('success', "Relación eliminada correctamente.");
        } else {
            setAlert('error', "No se pudo eliminar la relación.");
        }
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function cambiar_estado_politica()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if (!$this->puedeAccederItemConfiguracion('metodos_actualizacion_archivos')) {
            setAlert('error', "No tienes permiso para modificar los metodos de actualizacion de archivos.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idPolitica = intval($_POST['id_politica'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($idPolitica <= 0) {
            setAlert('warning', "Política inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $ok = $this->model->cambiarEstadoPolitica($idPolitica, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function servidor_AD()
    {
        $LDAP_datos = $this->model->selectLDAP_datos();
        $data = ['LDAP_datos' => $LDAP_datos];
        $this->views->getView($this, "servidor_AD", $data);
    }

    // ==========================================
    // CARGAR VISTA SMTP (Corregido CSRF y Array anidado)
    // ==========================================
    public function servidor_smtp()
    {
        // 1. Generar Token CSRF si no existe (Soluciona los Warnings)
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        // 2. Traer solo la configuración activa
        $smtp_datos = $this->model->getActiveSMTP();

        // 3. Desanidar el array para que la vista detecte el 'ACTIVO'
        if (isset($smtp_datos[0]['host'])) {
            $smtp_datos = $smtp_datos[0];
        }

        $data = ['smtp_datos' => $smtp_datos];
        $this->views->getView($this, "servidor_smtp", $data);
    }

    public function base_datos_externa()
    {
        $this->asegurarAccesoBaseUsuariosExterna();

        if (empty($_SESSION['csrf_token']) || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        $data = [
            'db_usuarios' => $this->obtenerConfiguracionBaseUsuariosActual(),
            'db_usuarios_resultado' => $_SESSION['db_usuarios_resultado'] ?? null,
        ];

        unset($_SESSION['db_usuarios_resultado']);

        $this->views->getView($this, "base_datos_externa", $data);
    }

    public function actualizar()
    {
        if ($_POST) {
            if (empty($_POST['nombre']) || empty($_POST['correo'])) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'El nombre y correo son obligatorios.'];
                header("location: " . base_url() . "configuracion/listar");
                die();
            }

            $id = intval($_POST['id']);
            $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
            $telefono = htmlspecialchars(trim($_POST['telefono']), ENT_QUOTES, 'UTF-8');
            $direccion = htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8');
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $total_pag = intval($_POST['total_pag']);
            $legajoMarcaAguaTexto = htmlspecialchars(trim((string)($_POST['legajo_marca_agua_texto'] ?? '')), ENT_QUOTES, 'UTF-8');
            $legajoMarcaAguaPosicion = trim((string)($_POST['legajo_marca_agua_posicion'] ?? 'cruzado'));
            $legajoSelloTexto = htmlspecialchars(trim((string)($_POST['legajo_sello_texto'] ?? '')), ENT_QUOTES, 'UTF-8');
            $legajoSelloPosicion = trim((string)($_POST['legajo_sello_posicion'] ?? 'derecha'));
            $legajoMarcaAguaActiva = $legajoMarcaAguaTexto !== '' ? 1 : 0;
            $legajoSelloActivo = $legajoSelloTexto !== '' ? 1 : 0;
            $posicionesMarcaPermitidas = ['cruzado', 'arriba', 'abajo', 'derecha', 'izquierda'];
            if (!in_array($legajoMarcaAguaPosicion, $posicionesMarcaPermitidas, true)) {
                $legajoMarcaAguaPosicion = 'cruzado';
            }
            $posicionesSelloPermitidas = ['arriba', 'abajo', 'derecha', 'izquierda'];
            if (!in_array($legajoSelloPosicion, $posicionesSelloPermitidas, true)) {
                $legajoSelloPosicion = 'derecha';
            }

            $logoPrincipalOk = $this->guardarLogoBranding('logo_empresa', 'logo_empresa');
            $logoReducidoOk = $this->guardarLogoBranding('logo_empresa_reducido', 'logo_empresa_reducido');
            if (!$logoPrincipalOk || !$logoReducidoOk) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo cargar uno de los logos. Verifique el formato del archivo.'];
                header("location: " . base_url() . "configuracion/listar");
                die();
            }

            $actualizar = $this->model->actualizarConfiguracion(
                $nombre,
                $telefono,
                $direccion,
                $correo,
                $total_pag,
                $id,
                $legajoMarcaAguaTexto,
                $legajoMarcaAguaActiva,
                $legajoMarcaAguaPosicion,
                $legajoSelloTexto,
                $legajoSelloActivo,
                $legajoSelloPosicion
            );

            if ($actualizar) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Datos de la empresa actualizados.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudieron guardar los cambios.'];
            }

            header("location: " . base_url() . "configuracion/listar");
            die();
        }
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function backup()
    {
        $result = $this->configuracionModel->backupDatabase();
        if ($result['status']) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $result['msg']];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function restore()
    {
        if (isset($_FILES['sqlFile']) && $_FILES['sqlFile']['error'] === UPLOAD_ERR_OK) {
            if (!scantecValidarUpload($_FILES['sqlFile'], ['sql'], ['text/plain', 'application/sql', 'application/octet-stream'], 25 * 1024 * 1024)) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error: El archivo SQL no es valido o supera 25 MB'];
                header("location: " . base_url() . "configuracion/mantenimiento");
                die();
            }
            $fileTmpPath = $_FILES['sqlFile']['tmp_name'];
            $fileName = $_FILES['sqlFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'sql') {
                $result = $this->configuracionModel->RestoreDatabase($fileTmpPath);
                if ($result['status']) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
                } else {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error en BD: ' . $result['msg']];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error: El archivo debe tener extensión .sql'];
            }
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se seleccionó ningún archivo o hubo un error en la subida.'];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function respaldo_archivos()
    {
        try {
            // 1. Verificar Token CSRF (Seguridad)
            if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

            // 2. Recibir y limpiar la ruta de forma segura
            $ruta_destino = isset($_POST['ruta_destino']) ? trim($_POST['ruta_destino']) : '';

            if (empty($ruta_destino)) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Debe especificar una ruta de destino válida.'];
                header('Location: ' . base_url() . 'configuracion/mantenimiento');
                exit();
            }

            // 3. Ejecutar el respaldo
            $resultado = $this->configuracionModel->ejecutarRespaldo($ruta_destino);

            if ($resultado['status']) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => $resultado['msg']];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: ' . $resultado['msg']];
            }

        } catch (Throwable $e) {
            // ESTO EVITA EL ERROR 500. Atrapa el error fatal y lo muestra en la alerta.
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error Crítico (500): ' . $e->getMessage() . ' en la línea ' . $e->getLine()];
        }

        header('Location: ' . base_url() . 'configuracion/mantenimiento');
        exit;
    }

    // ==========================================
    // ENVÍO PARA OTRAS PARTES DEL SISTEMA
    // ==========================================
    public function sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje)
    {
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host']))
            $smtpConfig = $smtpConfig[0];

        if (empty($smtpConfig))
            return false;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];

            // Desencriptar contraseña de la BD
            if (function_exists('stringDecryption')) {
                $mail->Password = stringDecryption($smtpConfig['password']);
            } else {
                $mail->Password = $smtpConfig['password'];
            }

            if ($smtpConfig['smtpsecure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = $smtpConfig['port'];

            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';
            // Forzamos username como remitente para evitar bloqueos
            $mail->setFrom($smtpConfig['username'], $fromName);

            if (is_array($destinatarios)) {
                foreach ($destinatarios as $email => $nombre) {
                    $mail->addAddress($email, $nombre);
                }
            } else {
                $mail->addAddress($destinatarios);
            }
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            if ($filePath && file_exists($filePath)) {
                $mail->addAttachment($filePath);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // 1. GUARDAR CONFIGURACIÓN SMTP
    // ==========================================
    public function guardarServCorreo()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // Recuperar config actual para no borrar la clave
        $configActual = $this->model->getActiveSMTP();
        if (isset($configActual[0]['host']))
            $configActual = $configActual[0];

        // LOGICA DE CONTRASEÑA: Si viene vacía, usamos la que ya estaba guardada.
        if (empty($password_raw) && !empty($configActual['password'])) {
            $password = $configActual['password'];
        } else {
            $password = function_exists('stringEncryption') ? stringEncryption($password_raw) : $password_raw;
        }

        $remitente = !empty($_POST['remitente']) ? trim($_POST['remitente']) : $username;
        $nombre_remitente = !empty($_POST['nombre_remitente']) ? trim($_POST['nombre_remitente']) : 'SCANTEC Notificaciones';

        $request = $this->model->insertarServSMTP($host, $username, $password, $smtpsecure, $port, $remitente, $nombre_remitente);

        if ($request) {
            unset($_SESSION['smtp_temp']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Configuración guardada y activada correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar los datos en la base de datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 2. PROBAR CONEXIÓN SMTP (Inteligente con la clave)
    // ==========================================
    public function probar_smtp()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // --- LÓGICA INTELIGENTE DE CONTRASEÑA PARA EL TEST ---
        if (empty($password_raw)) {
            // Si dejó el campo vacío, buscamos la contraseña de la BD y la DESENCRIPTAMOS
            $configActual = $this->model->getActiveSMTP();
            if (isset($configActual[0]['host'])) {
                $configActual = $configActual[0];
            }

            if (!empty($configActual['password'])) {
                $password = function_exists('stringDecryption') ? stringDecryption($configActual['password']) : $configActual['password'];
            } else {
                $password = '';
            }
        } else {
            // Si el usuario escribió algo, asumimos que es su contraseña real y la usamos tal cual
            $password = $password_raw;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password; // Usamos la contraseña real o la desencriptada

            if ($smtpsecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpsecure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $port;
            $mail->Timeout = 10;

            // Tolerancia de certificados para evitar problemas de conexión locales
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Conexión Exitosa! El servidor aceptó las credenciales.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Conexión fallida. Verifique los datos o su firewall.'];
            }

        } catch (Exception $e) {
            $errorMsg = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de conexión: ' . $errorMsg];
        }

        $_SESSION['smtp_temp'] = $_POST;
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }
    public function desactivar_servicio_smtp()
    {
        $this->model->desactivarSMTP();
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'El servicio de correo ha sido desactivado.'];
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 4. TEST DE ENVÍO
    // ==========================================
    public function enviarCorreo()
    {
        // 1. Validar CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        // 2. Obtener datos
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host'])) {
            $smtpConfig = $smtpConfig[0];
        }

        if (empty($smtpConfig) || !isset($smtpConfig['host'])) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'No hay configuración SMTP activa.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $destinatario = trim($_POST['destinatario']);
        $asunto = trim($_POST['asunto']);
        $mensaje = trim($_POST['mensaje']);

        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($smtpConfig['password']);
        } else {
            $password_real = $smtpConfig['password'];
        }

        try {
            $mail = new PHPMailer(true);

            // Silenciamos el debug para que no imprima texto en la pantalla
            $mail->SMTPDebug = 0;
            $mail->setLanguage('es', '../vendor/phpmailer/phpmailer/language/');

            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $password_real;

            if (strtolower($smtpConfig['smtpsecure']) == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else if (strtolower($smtpConfig['smtpsecure']) == 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $smtpConfig['port'];
            $mail->Timeout = 15;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Blindaje de remitente: Usamos el username para evitar bloqueos del servidor
            $fromEmail = $smtpConfig['username'];
            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            $mail->send();

            $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Correo enviado correctamente a ' . $destinatario . '!'];

        } catch (Exception $e) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // GUARDAR LDAP
    // ==========================================
    public function probar_conexionAD()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'CSRF inválido.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapHost = filter_input(INPUT_POST, 'ldapHost', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPort = filter_input(INPUT_POST, 'ldapPort', FILTER_VALIDATE_INT);
        $ldapUser = filter_input(INPUT_POST, 'ldapUser', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPass = $_POST['ldapPass'];
        $ldapBaseDn = $_POST['ldapBaseDn'];

        $_SESSION['ldap_data'] = [
            'ldapHost' => $ldapHost,
            'ldapPort' => $ldapPort,
            'ldapUser' => $ldapUser,
            'ldapPass' => $ldapPass,
            'ldapBaseDn' => $ldapBaseDn
        ];
        if (!$ldapHost || !$ldapPort || !$ldapUser || !$ldapPass || !$ldapBaseDn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo conectar al servidor LDAP.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldapConn, $ldapUser, $ldapPass)) {
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Conexión exitosa.'];
        } else {
            $errorMsg = ldap_error($ldapConn);
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Fallo al conectar: ' . $errorMsg];
        }
        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }

    public function saveLDAP_server()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        $ldapHost = filter_var(trim($_POST['ldapHost']), FILTER_SANITIZE_URL);
        $ldapPort = filter_var($_POST['ldapPort'], FILTER_VALIDATE_INT);
        $ldapBaseDn = trim($_POST['ldapBaseDn']);
        $ldapUser = trim($_POST['ldapUser']);
        $ldapPass = $_POST['ldapPass'];

        if (empty($ldapHost) || empty($ldapPort) || empty($ldapBaseDn) || empty($ldapUser) || empty($ldapPass)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        if (!$ldapPort || $ldapPort <= 0 || $ldapPort > 65535) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Puerto inválido (1-65535).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        date_default_timezone_set('America/Asuncion');
        $fecha_registro = date('Y-m-d H:i:s');

        $guardarLDAPserver = $this->model->insertarServLDAP($ldapHost, $ldapPort, $ldapBaseDn, $ldapUser, $ldapPass, $fecha_registro);

        if ($guardarLDAPserver) {
            unset($_SESSION['ldap_data']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Servidor LDAP registrado y encriptado correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar en la Base de Datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }
    public function probar_base_datos_externa()
    {
        $this->asegurarAccesoBaseUsuariosExterna();

        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($_POST['token'] ?? '') || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            setAlert('error', "Error de seguridad (Token invalido).");
            header("Location: " . base_url() . "configuracion/base_datos_externa");
            exit();
        }

        $config = $this->obtenerConfiguracionBaseUsuariosPost();
        // Persistimos el config del formulario en sesión para que el checkbox
        // no se resetee al redirigir (el .env no se modifica al probar).
        $_SESSION['db_usuarios_temp'] = $config;

        $resultado = $this->probarConexionBaseUsuarios($config);
        $_SESSION['db_usuarios_resultado'] = $resultado;
        setAlert($resultado['ok'] ? 'success' : 'error', $resultado['message']);

        header("Location: " . base_url() . "configuracion/base_datos_externa");
        exit();
    }

    public function guardar_base_datos_externa()
    {
        $this->asegurarAccesoBaseUsuariosExterna();

        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($_POST['token'] ?? '') || intval($_SESSION['csrf_expiration'] ?? 0) < time()) {
            setAlert('error', "Error de seguridad (Token invalido).");
            header("Location: " . base_url() . "configuracion/base_datos_externa");
            exit();
        }

        $config = $this->obtenerConfiguracionBaseUsuariosPost();
        $_SESSION['db_usuarios_temp'] = $config;

        $validacion = $this->validarConfiguracionBaseUsuarios($config);
        if (!$validacion['ok']) {
            setAlert('error', $validacion['message']);
            header("Location: " . base_url() . "configuracion/base_datos_externa");
            exit();
        }

        $guardado = $this->guardarConfiguracionBaseUsuariosEnv($config);
        if ($guardado['ok']) {
            // Al guardar exitosamente, limpiamos el temp: el .env ya tiene el estado correcto.
            unset($_SESSION['db_usuarios_temp']);
            $_SESSION['db_usuarios_resultado'] = [
                'ok' => true,
                'message' => 'Configuracion cargada desde .env actualizada correctamente.',
            ];
            setAlert('success', $guardado['message']);
        } else {
            setAlert('error', $guardado['message']);
        }

        header("Location: " . base_url() . "configuracion/base_datos_externa");
        exit();
    }
    public function reinicio_sistema()
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        $usuario = strtolower(trim($_SESSION['usuario'] ?? ''));
        $esAdmin = ($idRol === 1 || $usuario === 'root');
        
        if (!$esAdmin) {
            setAlert('error', 'Acceso denegado. Se requiere nivel de Administrador.');
            header("Location: " . base_url() . "dashboard/dashboard_legajos");
            exit();
        }

        $this->views->getView($this, "reinicio_sistema", []);
    }

    public function procesar_reinicio()
    {
        $idRol = intval($_SESSION['id_rol'] ?? 0);
        $usuario = strtolower(trim($_SESSION['usuario'] ?? ''));
        $esAdmin = ($idRol === 1 || $usuario === 'root');
        
        if (!$esAdmin) {
            echo json_encode(['status' => 'error', 'msg' => 'Acceso denegado.']);
            exit();
        }

        $modulos = isset($_POST['modulos']) && is_array($_POST['modulos']) ? $_POST['modulos'] : [];
        if (empty($modulos)) {
            echo json_encode(['status' => 'error', 'msg' => 'No se seleccionaron módulos.']);
            exit();
        }

        if (!method_exists($this->model, 'vaciarTablasReinicio')) {
            echo json_encode(['status' => 'error', 'msg' => 'El modelo no soporta esta operación todavía.']);
            exit();
        }

        $resultado = $this->model->vaciarTablasReinicio($modulos);

        if (in_array('legajos', $modulos)) {
            $this->limpiarDirectorioLegajos();
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'msg' => 'Limpieza de los módulos ejecutada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Ocurrió un error al intentar vaciar las tablas.']);
        }
        exit();
    }

    private function limpiarDirectorioLegajos()
    {
        if (!defined('RUTA_BASE')) {
            require_once 'Config/Config.php';
        }
        $rutaLegajos = rtrim(RUTA_BASE, '/\\') . DIRECTORY_SEPARATOR . 'Legajos';
        $this->eliminarDirectorioRecursivo($rutaLegajos, false);
    }

    private function eliminarDirectorioRecursivo($dir, $eliminarPropio = true)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff(scandir($dir), array('.', '..'));
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->eliminarDirectorioRecursivo($path, true) : unlink($path);
        }
        if ($eliminarPropio) {
            rmdir($dir);
        }
    }
}

