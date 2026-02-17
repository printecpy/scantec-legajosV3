<?php
require "Libraries/php-jwt-main/vendor/autoload.php";
use \Firebase\JWT\JWT;
function login()
{
    if (!empty($_POST['usuario']) || !empty($_POST['clave'])) {
        $usuario = $_POST['usuario'];
        $clave = $_POST['clave'];
        $hash = hash("SHA512", $clave);
        $data = $this->model->selectUsuario($usuario, $hash);
        if (!empty($data)) {
            $_SESSION['id'] = $data['id'];
            $_SESSION['nombre'] = $data['nombre'];
            $_SESSION['usuario'] = $data['usuario'];
            $_SESSION['id_rol'] = $data['id_rol'];
            $_SESSION['ACTIVO'] = true;

            // Generar un token para el usuario
            $_SESSION['token'] = $this->generateToken($_SESSION['id']);

            if ($_SESSION['id_rol'] == 3 || $_SESSION['id_rol'] == 4 ){
                $data = $this->model->registrarVisita($_SESSION['id']);
                $data = $this->model->conteoInicioSesion($_SESSION['id']);
                header('location: '.base_url(). 'expedientes/indice_busqueda');
            } else {
                $data = $this->model->registrarVisita($_SESSION['id']);
                $data = $this->model->conteoInicioSesion($_SESSION['id']);
                header('location: '.base_url(). 'dashboard/listar');
            }                    
        } else {
            $error = 0;
            header("location: ".base_url()."?msg=$error");
        }
    }
}

function generateToken($user_id) {
    $secret_key = '@S1c2A3n4T5e6C*23';
    $issuer_claim = "THE_ISSUER";
    $audience_claim = "THE_AUDIENCE";
    $issuedat_claim = time();
    $notbefore_claim = $issuedat_claim + 10;
    $expire_claim = $issuedat_claim + 60;
    $token = array(
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => array(
            "id" => $user_id,
        ));

    $jwt = JWT::encode($token, $secret_key);
    return $jwt;
}

function verifyToken($token) {
    $secret_key = 'your_secret_key';
    try {
        $decoded = JWT::decode($token, $secret_key, array('HS256'));
        // Si el token es válido, procesa la solicitud
        // ...
    } catch (Exception $e) {
        // Si el token no es válido, rechaza la solicitud
        // ...
    }
}
 if (isset($_GET['msg'])  && $_GET['msg'] === 'backup') { ?>
    <div class="toast ml-auto" id="backup" data-delay="5000" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <img src="<?php echo base_url(); ?>Assets/img/exito.png" class="rounded mr-2" width="20">
            <strong class="mr-auto">Exito</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            Usuario o contraseña incorrecta
        </div>
    </div>
    <?php } else {
                 if (isset($_GET['msg']) && $_GET['msg'] === 'backup_error') { ?>
    <div class="toast ml-auto" id="backup_error" data-delay="5000" role="alert" aria-live="assertive"
        aria-atomic="true">
        <div class="toast-header">
            <img src="<?php echo base_url(); ?>Assets/img/error.png" class="rounded mr-2" width="20">
            <strong class="mr-auto">Alerta</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body">
            Ocurrió un error inesperado al escribir el
            archivo de copia de seguridad
        </div>
    </div>
    <?php } 
       
    }?>