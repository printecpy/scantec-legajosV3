<?php

class Funcionalidades extends Controllers
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header('Location: ' . base_url());
            exit();
        }
        if (intval($_SESSION['id_rol'] ?? 0) !== 1) {
            require_once 'Models/FuncionalidadesModel.php';
            setAlert('warning', 'Solo el Administrador del sistema puede gestionar funcionalidades.');
            header('Location: ' . base_url() . FuncionalidadesModel::obtenerRutaRedireccionSegura(intval($_SESSION['id_rol'] ?? 0)));
            exit();
        }

        parent::__construct();
        $this->model->asegurarTablaFuncionalidades();
        $this->model->asegurarTablaAgrupacionItems();
    }

    public function listar()
    {
        $data = [
            'secciones' => FuncionalidadesModel::getSeccionesDisponibles(),
            'estados' => $this->model->selectEstadosSecciones(),
            'modulos_items' => FuncionalidadesModel::getModulosItemsDisponibles(),
            'items_agrupacion' => FuncionalidadesModel::getItemsAgrupacionDisponibles(),
            'items_modulo_actual' => $this->model->selectModulosItems(),
            'items_agrupados' => $this->model->selectItemsAgrupadosPorModulo(),
        ];

        $this->views->getView($this, 'listar', $data);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'funcionalidades/listar');
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF invalido o expirado.');
            header('Location: ' . base_url() . 'funcionalidades/listar');
            exit();
        }

        $estados = $_POST['funcionalidades'] ?? [];
        $modulosItems = $_POST['modulos_items'] ?? [];
        $idUsuario = intval($_SESSION['id'] ?? 0);

        $guardadoEstados = $this->model->guardarEstadosSecciones($estados, $idUsuario);
        $guardadoAgrupacion = $this->model->guardarModulosItems($modulosItems, $idUsuario);

        if ($guardadoEstados && $guardadoAgrupacion) {
            setAlert('success', 'Las funcionalidades del sistema se actualizaron correctamente.');
        } else {
            setAlert('error', 'No se pudieron guardar las funcionalidades del sistema.');
        }

        header('Location: ' . base_url() . 'funcionalidades/listar');
        exit();
    }
}
