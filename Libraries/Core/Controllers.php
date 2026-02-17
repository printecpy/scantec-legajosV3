<?php
class Controllers
{
    //$this->views = new Views();
    //$this->loadModel();
    // Propiedades para vistas y modelos
    public $views;
    public $model;
    public function __construct()
    {
        //INICIAR SESIÓN (Si no está iniciada)
        // vital que esto esté al principio del constructor padre
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Cargas normales del framework
        $this->views = new Views();
        $this->loadModel();
        // --- VALIDACIÓN DE SEGURIDAD CENTRALIZADA ---
        // Se ejecuta después de cargar todo, para validar si el usuario sigue vivo
        $this->validarSesionActivaEnBD();
    }
    
    public function loadModel()
    {
        $model = get_class($this) . "Model";
        $routClass = "Models/" . $model . ".php";
        if (file_exists($routClass)) {
            require_once($routClass);
            $this->model = new $model();
        }
    }
    // Método auxiliar para manejo de CSRF (No requiere cambios)
    protected function checkCsrfSafety()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }
    }
    /**
     * Verifica permisos para acceder a una vista (acción GET).
     * @param array $allowedRoles Roles permitidos.
     * @param string $actionContext Cadena 'controlador/accion' para el log.
     */
    protected function checkAccessSafetyView($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {

            // 1. Alerta
            setAlert('warning', "No tienes permiso para acceder a esta sección");

            // 2. Log de Seguridad (Utilizando el contexto)
            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a la vista: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            // 3. Redirección
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }
    // Ejemplo: checkAccessSafetyInsert
    protected function checkAccessSafetyInsert($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {
            setAlert('warning', "No tienes permiso para insertar registros a esta sección");

            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a inserción de: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }
    // Ejemplo: checkAccessSafetyUpdate
    protected function checkAccessSafetyUpdate($allowedRoles, string $actionContext = 'sección desconocida')
    {
        if (!Validador::puedeVer($_SESSION, $allowedRoles)) {
            setAlert('warning', "No tienes permiso para modificar registros a esta sección");

            if (isset($this->model)) {
                $logMessage = 'Acceso no autorizado a modificación de: ' . $actionContext;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }

            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }

    /**
     * Verifica permisos dinámicamente usando la tabla Permisos_Rol.
     * @param string $accion Nombre de la acción que se intenta ejecutar (ej. 'listar', 'actualizar').
     * @param string $mensajeAlerta El mensaje de error específico (ej. "No tienes permiso para insertar...").
     */
    protected function checkDynamicAccess(string $accion, string $mensajeAlerta)
    {
        // Obtiene el nombre real del controlador (ej. 'Usuarios' o 'Expedientes')
        $controlador = str_replace('Controller', '', get_class($this));
        // Si usas namespaces, podría ser necesario un trim o explode para obtener solo el nombre de la clase.
        // Crea la clave de permiso a buscar en la sesión
        $clavePermiso = $controlador . '/' . $accion;
        // Verificar si el permiso está en la sesión y si está marcado como 'permitido' (1)
        if (!isset($_SESSION['PERMISOS'][$clavePermiso]) || $_SESSION['PERMISOS'][$clavePermiso] !== 1) {
            // 1. Alerta y Log
            setAlert('warning', $mensajeAlerta);
            if (isset($this->model)) {
                $logMessage = 'Acceso dinámico denegado a: ' . $clavePermiso;
                $this->model->bloquarPC_IP($_SESSION['nombre'], $logMessage);
            }
            // 2. Redirección
            header('Location: ' . base_url() . 'expedientes/indice_busqueda');
            exit();
        }
    }
    // Método privado para validar si el Admin cerró la sesión
    private function validarSesionActivaEnBD()
    {
        // Solo verificamos si el usuario dice estar logueado
        if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
            
            $mi_session_id = session_id();
            
            // Verificamos directamente en la BD
            // NOTA: Asumo que Controller hereda de una clase que permite usar $this->select
            // Si no, tendrás que instanciar el modelo de usuarios aquí.
            $sql = "SELECT id_visita FROM visitas WHERE session_id = '{$mi_session_id}' AND estado = 'ACTIVO'";
            $request = $this->select($sql); // O usa tu método de conexión preferido

            if (empty($request)) {
                // ¡NO EXISTE O ESTÁ INACTIVO! El Admin me cerró la sesión.
                
                // Limpiamos sesión local
                session_unset();
                session_destroy();
                
                // Lo mandamos al login con aviso
                header("Location: " . base_url() . "?msg=kicked");
                exit();
            }
        }
    }

}
