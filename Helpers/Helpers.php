<?php
function base_url()
{
    return BASE_URL;
}
function encabezado($data = "")
{
    $VistaH = "Views/template/header.php";
    require_once($VistaH);
}
function pie($data = "")
{
    $VistaP = "Views/template/footer.php";
    require_once($VistaP);
}
function report($data = "")
{
    $VistaP = "Views/template/report.php";
    require_once($VistaP);
}
function setAlert($type, $message)
{
    $validAlertTypes = ['success', 'error', 'warning', 'info'];
    if (!in_array($type, $validAlertTypes)) {
        $type = 'info';
    }
    $_SESSION['alert'] = [
        'type' => (string) $type,
        'message' => (string) $message
    ];
}

function verificarSesion()
{
    if (!isset($_SESSION['usuario'])) {
        header("Location: " . base_url() . "/login");
        exit();
    }
}

function limpiar($cadena)
{
    // Eliminar etiquetas peligrosas
    $cadena = strip_tags($cadena);

    // Quitar espacios y barras innecesarias
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);

    // Escapar HTML especial
    $cadena = htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');

    return $cadena;
}

function scantecValidarUpload(array $archivo, array $extensionesPermitidas, array $mimesPermitidos, int $maxBytes): bool
{
    if (intval($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    $tmp = (string) ($archivo['tmp_name'] ?? '');
    if ($tmp === '' || (!is_uploaded_file($tmp) && !is_file($tmp))) {
        return false;
    }

    $size = intval($archivo['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        return false;
    }

    $extension = strtolower(pathinfo((string) ($archivo['name'] ?? ''), PATHINFO_EXTENSION));
    if ($extension === '' || !in_array($extension, $extensionesPermitidas, true)) {
        return false;
    }

    if ($mimesPermitidos === []) {
        return true;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        return false;
    }

    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    return is_string($mime) && in_array($mime, $mimesPermitidos, true);
}

function stringEncryption($string)
{
    if (!$string)
        return false;

    // Usamos tu clave maestra existente
    $key = hash('sha256', ENCRYPTION_KEY);
    // Generamos un vector de inicialización basado en tu clave
    $iv = substr(hash('sha256', md5(ENCRYPTION_KEY)), 0, 16);

    $output = openssl_encrypt($string, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($output);
}

function stringDecryption($string)
{
    if (!$string)
        return false;

    $key = hash('sha256', ENCRYPTION_KEY);
    $iv = substr(hash('sha256', md5(ENCRYPTION_KEY)), 0, 16);

    $output = openssl_decrypt(base64_decode($string), 'AES-256-CBC', $key, 0, $iv);
    return $output;
}

function encabezado_limpio($data = "")
{
    require_once("Views/template/header_limpio.php");
}
function pie_limpio($data = "")
{
    require_once("Views/template/footer_limpio.php");
}
function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rutaRetornoSegura(string $uri, string $baseUrl): ?string
{
    static $permitidas = [
    'expedientes/buscarporindice01' => ['id_tipoDoc', 'indice1'],
    'expedientes/mostrar_resultados' => ['id_expediente'],
    ];
    if ($uri === '' || strlen($uri) > 500 || preg_match('/[\x00-\x20\x7f\\\\]/', $uri)) {
        return null;
    }
    $p = parse_url($uri);
    if ($p === false || isset($p['scheme']) || isset($p['host']) || isset($p['user']) || !isset($p['path'])) {
        return null;
    }
    $prefijo = '/' . trim(parse_url($baseUrl, PHP_URL_PATH) ?: '', '/');
    $prefijo = ($prefijo === '/') ? '/' : $prefijo . '/';
    if (strpos($p['path'], $prefijo) !== 0) {
        return null;
    }
    $ruta = trim(substr($p['path'], strlen($prefijo)), '/');
    if (!isset($permitidas[$ruta])) {
        return null;
    }
    parse_str($p['query'] ?? '', $q);
    $limpio = [];
    foreach ($permitidas[$ruta] as $k) {
        if (isset($q[$k]) && is_string($q[$k]) && strlen($q[$k]) <= 100) {
            $limpio[$k] = $q[$k];
        }
    }
    return $prefijo . $ruta . ($limpio ? '?' . http_build_query($limpio) : '');
}

/**
 * Arma el nombre que se envia al servidor LDAP/AD en el bind, a partir del "formato de usuario" configurado.
 * Placeholders (exactamente UNO por formato):
 *   {usuario}     el usuario tal cual (DOMINIO\{usuario}, {usuario}, {usuario}@sufijo)
 *   {usuario_dn}  el usuario escapado para un DN (uid={usuario_dn},ou=...,dc=...)
 * Devuelve null si el formato o el usuario no son validos (quien llama usa el formato por defecto).
 */
function ldapNombreBind(string $formato, string $usuario): ?string
{
    $formato = trim($formato);
    if ($formato === '' || strlen($formato) > 255 || preg_match('/[\x00-\x1f\x7f]/', $formato)) {
        return null;
    }
    $enCrudo = substr_count($formato, '{usuario}');
    $enDn = substr_count($formato, '{usuario_dn}');
    if (($enCrudo + $enDn) !== 1) {
        return null;
    }
    if ($usuario === '' || strlen($usuario) > 128 || preg_match('/[\x00-\x1f\x7f]/', $usuario)) {
        return null;
    }
    $valor = ($enDn === 1) ? ldap_escape($usuario, '', LDAP_ESCAPE_DN) : $usuario;
    return str_replace(['{usuario_dn}', '{usuario}'], $valor, $formato);
}

/**
 * Escribe un valor en una celda de Excel SIEMPRE como texto.
 * Evita la inyección de fórmulas: un valor que empiece con =, +, - o @ no se ejecuta como fórmula.
 * Usar para todo dato que venga de usuarios, de la base o de sistemas externos.
 */
function excelTexto($hoja, string $celda, $valor): void
{
    $hoja->setCellValueExplicit(
        $celda,
        (string) ($valor ?? ''),
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
    );
}

/**
 * ¿El usuario de la sesión es superadministrador Scantec?
 * Única fuente de verdad: id_rol = 1. El nombre de usuario NO otorga privilegios.
 */
function esSuperadmin(): bool
{
    return intval($_SESSION['id_rol'] ?? 0) === 1;
}

/** Nombres que nadie puede usar como usuario (evita confusión y suplantación). Única lista del sistema. */
function esUsuarioReservado(string $usuario): bool
{
    return in_array(strtolower(trim($usuario)), ['root', 'scantec', 'admin', 'administrador'], true);
}

/**
 * Jerarquía de roles: ¿el usuario de la sesión puede hacer $accion sobre el rol $idRolObjetivo?
 * Acciones: 'renombrar', 'estado', 'eliminar', 'permisos'.
 * - Solo los roles 1 y 2 gestionan roles.
 * - El rol 1 no se modifica desde la web (solo por consola).
 * - El rol 2 solo se puede renombrar; nunca desactivar ni eliminar.
 */
function puedeGestionarRol(int $idRolObjetivo, string $accion): bool
{
    if ($idRolObjetivo <= 0 || !in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2], true)) {
        return false;
    }
    if ($idRolObjetivo === 1) {
        return false;
    }
    if ($idRolObjetivo === 2) {
        return in_array($accion, ['renombrar', 'permisos'], true);   // nunca desactivar ni eliminar
    }
    return in_array($accion, ['renombrar', 'estado', 'eliminar', 'permisos'], true);
}

/** Solo los roles 1 y 2 crean roles. */
function puedeCrearRol(): bool
{
    return in_array(intval($_SESSION['id_rol'] ?? 0), [1, 2], true);
}