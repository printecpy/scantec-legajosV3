<?php
// Convertimos la primera letra a mayúscula (ej: usuarios -> Usuarios)
$controller = ucwords($controller);

// 1. Definir la ruta del archivo del controlador
// Verificamos si es una petición API o Web
if (defined('IS_API') && IS_API === true) {
    // Ruta para API: Controller/Api/NombreController.php
    $controllerFile = "Controller/Api/" . $controller . ".php";
} else {
    // Ruta Web Normal: Controller/NombreController.php
    $controllerFile = "Controller/" . $controller . ".php";
}

// 2. Verificar existencia y cargar
if (file_exists($controllerFile)) {
    require_once($controllerFile);

    // Instanciamos la clase del controlador
    $controllerClass = new $controller();

    // Verificamos si el método existe
    if (method_exists($controllerClass, $methop)) {
        // Ejecutamos el método con los parámetros
        $controllerClass->{$methop}($params);
    } else {
        // ---------------------------------------------------------
        // CASO 1: EL CONTROLADOR EXISTE, PERO EL MÉTODO NO
        // ---------------------------------------------------------
        if (defined('IS_API') && IS_API === true) {
            // Error para API (JSON)
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => "Método no encontrado: $methop"]);
        } else {
            // Error para Web -> Cargar Vista 404
            require_once("Controller/Errors.php"); // Cargamos el archivo
            $error = new Errors();                 // Instanciamos la clase
            $error->notFound();                    // Ejecutamos la vista
        }
    }
} else {
    // ---------------------------------------------------------
    // CASO 2: EL ARCHIVO DEL CONTROLADOR NO EXISTE
    // ---------------------------------------------------------
    if (defined('IS_API') && IS_API === true) {
        // Error para API (JSON)
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => "Controlador no encontrado: $controller"]);
    } else {
        // Error para Web -> Cargar Vista 404
        require_once("Controller/Errors.php"); // Cargamos el archivo
        $error = new Errors();                 // Instanciamos la clase
        $error->notFound();                    // Ejecutamos la vista
    }
}