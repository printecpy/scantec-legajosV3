<?php
// Configuración estática para la prueba
$ldap_host = "192.168.100.9";
$ldap_port = 389;

// PRUEBA CON EL ADMINISTRADOR (Cámbialo por aldo.silva y tu clave si prefieres)
$usuario_ad = "PRINTEC\\fbenegas"; 
$clave = "printec2023*"; // <-- ¡OJO! Pon la clave real aquí

echo "<div style='font-family: Arial; padding: 20px;'>";
echo "<h2>Prueba de conexión LDAP Directa</h2>";
echo "Intentando conectar al host: <b>$ldap_host</b><br>";

$ldap_conn = @ldap_connect($ldap_host, $ldap_port);

if (!$ldap_conn) {
    die("<h3 style='color:red;'>Error fatal: PHP no puede alcanzar la IP del servidor.</h3></div>");
}

ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

echo "Intentando autenticar al usuario: <b>$usuario_ad</b>...<hr>";

// Intentamos el Bind
if (@ldap_bind($ldap_conn, $usuario_ad, $clave)) {
    echo "<h2 style='color:green;'>✅ ¡ÉXITO ROTUNDO!</h2>";
    echo "<p>Windows Server aceptó las credenciales perfectamente.</p>";
} else {
    echo "<h2 style='color:red;'>❌ EL SERVIDOR RECHAZÓ EL ACCESO</h2>";
    echo "<p><b>Mensaje de PHP:</b> " . ldap_error($ldap_conn) . "</p>";
    
    // Capturamos el código secreto de Windows
    ldap_get_option($ldap_conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
    echo "<p><b>Código de Error de Windows (Data):</b> <code style='background:#f4f4f4; padding:4px;'>$extended_error</code></p>";
    
    echo "<h3>¿Qué significa el Data Code?</h3>";
    echo "<ul>
            <li><b>Data 52e:</b> Contraseña incorrecta o nombre de usuario mal escrito.</li>
            <li><b>Data 532:</b> La contraseña expiró.</li>
            <li><b>Data 533:</b> La cuenta está deshabilitada en Windows.</li>
            <li><b>Data 775:</b> La cuenta está bloqueada en Windows por muchos intentos.</li>
          </ul>";
}
echo "</div>";
?>