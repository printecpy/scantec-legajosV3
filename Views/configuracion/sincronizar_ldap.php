<?php 
require("../../Config/Config.php");
$base_url = "http://$host/scantec2/";
date_default_timezone_set('America/Asuncion');

// Datos de conexión LDAP
$ldapHost = $_POST['ldapHost'];
$ldapPort = $_POST['ldapPort']; // Puerto predeterminado para LDAP
$ldapBaseDn = $_POST['ldapBaseDn']; // Base DN del directorio LDAP
$ldapUser = $_POST['ldapUser'];
$ldapPass = $_POST['ldapPass'];

// Conectarse al servidor LDAP
$ldapConn = ldap_connect($ldapHost, $ldapPort);

// Verificar si la conexión se estableció correctamente
if (!$ldapConn) {
    die("<script>alert('No se pudo conectar al servidor LDAP.');</script>");
}

// Opciones de conexión
ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

// Autenticarse en el servidor LDAP (si es necesario)
$ldapBind = ldap_bind($ldapConn, $ldapUser, $ldapPass);

if (!$ldapBind) {
    die("<script>alert('Error al autenticarse en el servidor LDAP');</script>");
}

// Insertar datos de conexión LDAP en la base de datos
try {
    $dbConn = new PDO("mysql:host=" . HOST . ";dbname=" . BD , DB_USER, PASS);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $fecha_registro = date('Y-m-d H:i:s');
    $hash = hash("SHA512", $ldapPass);
    // Insertar los datos del servidor LDAP en la tabla ldap_config
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

// Realizar operaciones LDAP aquí (por ejemplo, búsqueda de usuarios)
// Ajustar el filtro de búsqueda para excluir usuarios locales
$searchFilter = '(&(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))';
$ldapAttributes = ['samaccountname', 'givenname', 'sn', 'mail']; // Atributos a recuperar

$searchResult = ldap_search($ldapConn, $ldapBaseDn, $searchFilter, $ldapAttributes);
$entries = ldap_get_entries($ldapConn, $searchResult);

// Procesar los usuarios encontrados
foreach ($entries as $entry) {
    if (isset($entry['samaccountname'][0])) {
        $usuario = $entry['samaccountname'][0];
        $givenname = isset($entry['givenname'][0]) ? $entry['givenname'][0] : '';
        $sn = isset($entry['sn'][0]) ? $entry['sn'][0] : '';
        $email = isset($entry['mail'][0]) ? $entry['mail'][0] : '';

        // Puedes combinar el nombre y apellido para obtener el nombre completo si es necesario
        $nombre = $givenname . ' ' . $sn;
        
        // Obtener el timestamp actual
        $clave_actualizacion = date('Y-m-d H:i:s');

        // Conectar a la base de datos MySQL usando PDO
        try {
            $dbConn = new PDO("mysql:host=" . HOST . ";dbname=" . BD , DB_USER, PASS);
            $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar si el usuario ya existe en la base de datos
            $stmt = $dbConn->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();
            $count = $stmt->fetchColumn();

            // Insertar el usuario solo si no existe en la base de datos
            if ($count == 0) {
                $sql = "INSERT INTO usuarios (nombre, usuario, clave, id_rol, estado_usuario, email, fuente_registro, clave_actualizacion) 
                        VALUES (:nombre, :usuario, :clave, 3, 'ACTIVO', :email, 'LDAP', :clave_actualizacion)";
                $stmt = $dbConn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':usuario', $usuario);
                $stmt->bindParam(':clave', $usuario); // Utiliza el nombre de usuario como contraseña
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':clave_actualizacion', $clave_actualizacion);
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
                alert('Error al registrar los usuarios  en la base de datos:'". $e->getMessage();");
                window.location.href = '" .  $base_url . "configuracion/servidor_AD.php;</script>";
        }
    }
}

// Cerrar la conexión LDAP
ldap_unbind($ldapConn);
