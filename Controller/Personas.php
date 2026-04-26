<?php

class Personas extends Controllers
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

        parent::__construct();

        require_once 'Models/FuncionalidadesModel.php';
        $funcionalidadesModel = new FuncionalidadesModel();
        if (!$funcionalidadesModel->estaSeccionHabilitada('personas')) {
            setAlert('warning', 'El modulo Personas no esta activo.');
            header('Location: ' . base_url());
            exit();
        }
        $esAdministradorScantec = intval($_SESSION['id_rol'] ?? 0) === 1
            || strtolower(trim((string)($_SESSION['usuario'] ?? ''))) === 'root';
        if (
            !$esAdministradorScantec &&
            !$funcionalidadesModel->puedeAccederItemPorContexto(
                'personas',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            )
        ) {
            setAlert('warning', 'No tienes permiso para acceder a Personas.');
            header('Location: ' . base_url());
            exit();
        }
    }

    private function limpiarTexto(string $valor, int $max = 255): string
    {
        $valor = trim($valor);
        if (function_exists('mb_substr')) {
            return mb_substr($valor, 0, $max, 'UTF-8');
        }
        return substr($valor, 0, $max);
    }

    public function listar()
    {
        $termino = trim((string)($_GET['termino'] ?? ''));
        $estado = trim((string)($_GET['estado'] ?? ''));

        $data = [
            'personas' => $this->model->selectPersonas($termino, $estado),
            'termino' => $termino,
            'estado' => $estado,
        ];

        $this->views->getView($this, 'listar', $data);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF invalido o expirado.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        $idPersona = intval($_POST['id_persona'] ?? 0);
        $apellido = $this->limpiarTexto((string)($_POST['apellido'] ?? ''), 100);
        $nombre = $this->limpiarTexto((string)($_POST['nombre'] ?? ''), 100);
        $ci = preg_replace('/\D+/', '', (string)($_POST['ci'] ?? ''));
        $celular = $this->limpiarTexto((string)($_POST['celular'] ?? ''), 40);
        $correo = filter_var(trim((string)($_POST['correo'] ?? '')), FILTER_SANITIZE_EMAIL);
        $direccion = $this->limpiarTexto((string)($_POST['direccion'] ?? ''), 255);
        $tipoPersona = strtolower(trim((string)($_POST['tipo_persona'] ?? 'cliente')));
        $cargo = $this->limpiarTexto((string)($_POST['cargo'] ?? ''), 120);
        $fechaCumpleanos = trim((string)($_POST['fecha_cumpleanos'] ?? ''));
        $estado = strtolower(trim((string)($_POST['estado'] ?? 'activo')));

        if ($apellido === '' || $nombre === '' || $ci === '') {
            setAlert('warning', 'Apellido, nombre y CI son obligatorios.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            setAlert('warning', 'El correo de la persona no es valido.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        if ($fechaCumpleanos !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCumpleanos)) {
            setAlert('warning', 'La fecha de cumpleanos no es valida.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        if ($this->model->existeCi($ci, $idPersona)) {
            setAlert('warning', 'Ya existe una persona con ese CI.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        $ok = $this->model->guardarPersona(
            $idPersona,
            $apellido,
            $nombre,
            $ci,
            $celular,
            $correo,
            $direccion,
            $tipoPersona,
            $cargo,
            $fechaCumpleanos,
            $estado
        );

        setAlert($ok ? 'success' : 'error', $ok ? 'Persona guardada correctamente.' : 'No se pudo guardar la persona.');
        header('Location: ' . base_url() . 'personas/listar');
        exit();
    }

    public function cambiar_estado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF invalido o expirado.');
            header('Location: ' . base_url() . 'personas/listar');
            exit();
        }

        $idPersona = intval($_POST['id_persona'] ?? 0);
        $estado = strtolower(trim((string)($_POST['estado'] ?? 'inactivo')));
        $ok = $this->model->cambiarEstadoPersona($idPersona, $estado);

        setAlert($ok ? 'success' : 'error', $ok ? 'Estado de persona actualizado.' : 'No se pudo actualizar el estado.');
        header('Location: ' . base_url() . 'personas/listar');
        exit();
    }
}
