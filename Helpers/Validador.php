<?php
class Validador
{
    public static function csrfValido()
    {
        return isset($_SESSION['csrf_token'], $_POST['token'], $_SESSION['csrf_expiration'])
            && $_SESSION['csrf_token'] === $_POST['token']
            && $_SESSION['csrf_expiration'] >= time();
    }

    // Rol de superusuario/root
    public static function esSuperUsuario($usuario)
    {
        return isset($usuario['id_rol']) && $usuario['id_rol'] === 1;
        // Podés definir 0 como ID de rol de root
    }

    // Rol de administrador del cliente
    public static function esAdminCliente($usuario)
    {
        return isset($usuario['id_rol']) && $usuario['id_rol'] === 2;
        // 2 = Admin del cliente
    }

    // Usuario normal
    public static function esUsuarioNormal($usuario)
    {
        return isset($usuario['id_rol']) && $usuario['id_rol'] >= 3;
        // 3 o más = usuarios normales
    }

    // Función para determinar acceso a vistas
    public static function puedeVer($usuarioSesion, $rolesPermitidos)
    {
        return isset($usuarioSesion['id_rol']) && in_array($usuarioSesion['id_rol'], $rolesPermitidos);
    }
}
