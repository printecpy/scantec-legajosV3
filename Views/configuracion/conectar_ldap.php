<?php 
if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
    // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
  header("Location: " . base_url() . "?error=csrf");
  die();
}
require("../../Config/Config.php");
date_default_timezone_set('America/Asuncion');

// Sanitizar y validar los datos de entrada
$ldapHost = filter_input(INPUT_POST, 'ldapHost', FILTER_SANITIZE_STRING);
$ldapPort = filter_input(INPUT_POST, 'ldapPort', FILTER_VALIDATE_INT);
$ldapBaseDn = filter_input(INPUT_POST, 'ldapBaseDn', FILTER_SANITIZE_STRING);
$ldapUser = filter_input(INPUT_POST, 'ldapUser', FILTER_SANITIZE_STRING);
$ldapPass = $_POST['ldapPass']; // La contraseña se hashea, por lo que no necesita sanitización

// Conectarse al servidor LDAP
$ldapConn = ldap_connect($ldapHost, $ldapPort);
if (!$ldapConn) {
    die("<script>alert('No se pudo conectar al servidor LDAP.');</script>");
}

ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

$ldapBind = ldap_bind($ldapConn, $ldapUser, $ldapPass);
if (!$ldapBind) {
    die("<script>alert('Error al autenticarse en el servidor LDAP');</script>");
}

// Insertar datos de conexión LDAP en la base de datos
try {
    $dbConn = new PDO("mysql:host=" . HOST . ";dbname=" . BD , DB_USER, PASS);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Actualizar el estado de registros existentes a 'inactivo'
    $updateSql = "UPDATE ldap_datos SET estado = 'inactivo' WHERE ldapHost = :ldapHost AND ldapPort = :ldapPort AND ldapUser = :ldapUser";
    $updateStmt = $dbConn->prepare($updateSql);
    $updateStmt->bindParam(':ldapHost', $ldapHost);
    $updateStmt->bindParam(':ldapPort', $ldapPort);
    $updateStmt->bindParam(':ldapUser', $ldapUser);
    $updateStmt->execute();

    // Insertar el nuevo registro como 'activo'
    $fecha_registro = date('Y-m-d H:i:s');
    $hash = hash("SHA512", $ldapPass);
    $sql = "INSERT INTO ldap_datos (ldapHost, ldapPort, ldapUser, ldapPass, ldapBaseDn, fecha_registro, estado) 
            VALUES (:ldapHost, :ldapPort, :ldapUser, :ldapPass, :ldapBaseDn, :fecha_registro, 'activo')";
    $stmt = $dbConn->prepare($sql);
    $stmt->bindParam(':ldapHost', $ldapHost);
    $stmt->bindParam(':ldapPort', $ldapPort);
    $stmt->bindParam(':ldapUser', $ldapUser);
    $stmt->bindParam(':ldapPass', $hash);
    $stmt->bindParam(':ldapBaseDn', $ldapBaseDn);
    $stmt->bindParam(':fecha_registro', $fecha_registro);
    $stmt->execute();

    echo "<script>alert('Datos del servidor LDAP registrados correctamente y el registro anterior ha sido actualizado a inactivo.');</script>";
} catch (PDOException $e) {
    echo "Error al registrar los datos del servidor LDAP: " . $e->getMessage();
}