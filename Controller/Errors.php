<?php
class Errors extends Controllers
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notFound()
    {
        // No validamos sesión aquí obligatoriamente, 
        // porque un usuario no logueado también podría llegar a una url 404.
        $data['page_title'] = "Página no encontrada";
        $this->views->getView($this, "error", $data);
    }
}
