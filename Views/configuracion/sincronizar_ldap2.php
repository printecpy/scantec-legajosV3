<?php 
require("../../Config/Config.php");
$base_url = "http://$host/scantec2/";
date_default_timezone_set('America/Asuncion');

// Datos de conexión LDAP
$ldapHost = $_POST['ldapHost'];
$ldapPort = $_POST['ldapPort'];
$ldapBaseDn = $_POST['ldapBaseDn'];
$ldapUser = $_POST['ldapUser'];
$ldapPass = $_POST['ldapPass'];

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

try {
    $dbConn = new PDO("mysql:host=" . HOST . ";dbname=" . BD , DB_USER, PASS);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $fecha_registro = date('Y-m-d H:i:s');
    $hash = password_hash($ldapPass, PASSWORD_BCRYPT, ['cost' => 10]);

    $sql = "INSERT INTO ldap_datos (ldapHost, ldapPort, ldapUser, ldapPass, ldapBaseDn, fecha_registro) 
            VALUES (:ldapHost, :ldapPort, :ldapUser, :ldapPass, :ldapBaseDn, :fecha_registro)";
    $stmt = $dbConn->prepare($sql);
    $stmt->bindParam(':ldapHost', $ldapHost);
    $stmt->bindParam(':ldapPort', $ldapPort);
    $stmt->bindParam(':ldapUser', $ldapUser);
    $stmt->bindParam(':ldapPass', $hash);
    $stmt->bindParam(':ldapBaseDn', $ldapBaseDn);
    $stmt->bindParam(':fecha_registro', $fecha_registro);
    $stmt->execute();

    echo "<script>alert('Datos del servidor LDAP registrados correctamente.');</script>";
} catch (PDOException $e) {
    echo "Error al registrar los datos del servidor LDAP: " . $e->getMessage();
}

$searchFilter = '(&(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';
$ldapAttributes = ['samaccountname', 'givenname', 'sn', 'mail'];

$searchResult = ldap_search($ldapConn, $ldapBaseDn, $searchFilter, $ldapAttributes);
$entries = ldap_get_entries($ldapConn, $searchResult);

foreach ($entries as $entry) {
    if (isset($entry['samaccountname'][0])) {
        $usuario = $entry['samaccountname'][0];
        $givenname = isset($entry['givenname'][0]) ? $entry['givenname'][0] : '';
        $sn = isset($entry['sn'][0]) ? $entry['sn'][0] : '';
        $email = isset($entry['mail'][0]) ? $entry['mail'][0] : '';
        $nombre = $givenname . ' ' . $sn;

        $clave_actualizacion = date('Y-m-d H:i:s');
        
        try {
            $dbConn = new PDO("mysql:host=" . HOST . ";dbname=" . BD , DB_USER, PASS);
            $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $dbConn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $hashPassword = password_hash($usuario, PASSWORD_BCRYPT, ['cost' => 10]);
                $grupo_id = $_POST['id_grupo']; // ID del grupo recibido por POST

                $sql = "INSERT INTO usuarios (nombre, usuario, clave, id_rol, estado_usuario, email, fuente_registro, clave_actualizacion, id_grupo) 
                        VALUES (:nombre, :usuario, :clave, 3, 'ACTIVO', :email, 'LDAP', :clave_actualizacion, :grupo_id)";
                $stmt = $dbConn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':clave', $hashPassword);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':clave_actualizacion', $clave_actualizacion);
                $stmt->bindParam(':id_grupo', $id_grupo);
                $stmt->execute();

                echo "<script>
                    alert('Usuarios registrados correctamente en la base de datos');
                    window.location.href = '" .  $base_url."usuarios/listar';</script>";
            } else {
                echo "<script>
                    alert('El usuario $usuario ya está registrado en la base de datos.');
                    window.location.href = '" .  $base_url . "usuarios/listar';</script>";
            }
        } catch (PDOException $e) {
            echo "<script>
                alert('Error al registrar los usuarios en la base de datos: ' . $e->getMessage());
                window.location.href = '" .  $base_url . "configuracion/servidor_AD.php';</script>";
        }
    }
}

ldap_unbind($ldapConn);
