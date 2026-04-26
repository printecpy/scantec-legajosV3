<?php

class Funcionalidades extends Controllers
{
    private function normalizarTextoFuncionalidades($valor)
    {
        if (is_array($valor)) {
            foreach ($valor as $clave => $item) {
                $valor[$clave] = $this->normalizarTextoFuncionalidades($item);
            }
        }

        return $valor;
    }

    private function filtrarAccesosPorModulosActivos(array $accesosDisponibles): array
    {
        $estados = $this->model->selectEstadosSecciones();

        $mapaAccesoModulo = [
            'buscador_archivos' => 'archivos',
            'reporte_expedientes' => 'archivos',
            'subir_archivos' => 'archivos',
            'log_documentos' => 'archivos',
            'visitas_archivos' => 'archivos',
            'unir_pdf' => 'unir_pdf',
            'dashboard_legajos' => 'dashboard',
            'armar_legajo' => 'legajos',
            'buscar_legajos' => 'legajos',
            'verificar_legajos' => 'legajos',
            'administrar_legajos' => 'legajos',
            'permisos_legajos' => 'legajos',
            'log_legajos' => 'legajos',
            'personas' => 'personas',
        ];

        foreach ($accesosDisponibles as $clave => $info) {
            $modulo = $mapaAccesoModulo[$clave] ?? null;
            if ($modulo === null) {
                continue;
            }

            if (intval($estados[$modulo] ?? 1) !== 1) {
                unset($accesosDisponibles[$clave]);
            }
        }

        return $accesosDisponibles;
    }

    private function filtrarAccesosGestionablesPorUsuarioActual(array $accesosDisponibles): array
    {
        if ($this->esAdministradorScantec()) {
            return $accesosDisponibles;
        }

        $idRolSesion = intval($_SESSION['id_rol'] ?? 0);
        $idDepartamentoSesion = intval($_SESSION['id_departamento'] ?? 0);

        foreach ($accesosDisponibles as $clave => $info) {
            if (!$this->model->puedeAccederItemPorContexto($clave, $idRolSesion, $idDepartamentoSesion)) {
                unset($accesosDisponibles[$clave]);
            }
        }

        return $accesosDisponibles;
    }

    private function esAdministradorScantec(): bool
    {
        return intval($_SESSION['id_rol'] ?? 0) === 1
            || strtolower(trim((string)($_SESSION['usuario'] ?? ''))) === 'root';
    }

    private function esAdministradorSistemaOGlobal(): bool
    {
        return in_array(intval($_SESSION['id_rol'] ?? 0), [1, 5], true);
    }

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
        $this->model->asegurarTablaFuncionalidades();
        $this->model->asegurarTablaAgrupacionItems();
        $this->model->asegurarTablaAccesosRolDepartamento();
        $this->model->asegurarTablaParametros();

        if (
            !$this->esAdministradorScantec() &&
            !$this->model->puedeAccederItemPorContexto(
                'funcionalidades_accesos',
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            )
        ) {
            setAlert('warning', 'No tienes permiso para acceder a funcionalidades.');
            header('Location: ' . base_url() . FuncionalidadesModel::obtenerRutaRedireccionSegura(
                intval($_SESSION['id_rol'] ?? 0),
                intval($_SESSION['id_departamento'] ?? 0)
            ));
            exit();
        }
    }

    public function listar()
    {
        if (!$this->esAdministradorScantec()) {
            setAlert('warning', 'Solo el Administrador Scantec puede gestionar módulos.');
            header('Location: ' . base_url() . 'funcionalidades/accesos');
            exit();
        }

        $secciones = $this->normalizarTextoFuncionalidades(FuncionalidadesModel::getSeccionesDisponibles());
        $grupos = [];
        foreach ($secciones as $claveSeccion => $infoSeccion) {
            $grupo = $infoSeccion['grupo'] ?? 'General';
            if (!isset($grupos[$grupo])) {
                $grupos[$grupo] = [];
            }
            $grupos[$grupo][$claveSeccion] = $infoSeccion;
        }

        $data = [
            'secciones' => $secciones,
            'grupos' => $grupos,
            'estados' => $this->model->selectEstadosSecciones(),
            'modulos_items' => $this->normalizarTextoFuncionalidades(FuncionalidadesModel::getModulosItemsDisponibles()),
            'items_agrupacion' => $this->normalizarTextoFuncionalidades(FuncionalidadesModel::getItemsAgrupacionDisponibles()),
            'items_modulo_actual' => $this->model->selectModulosItems(),
            'items_agrupados' => $this->model->selectItemsAgrupadosPorModulo(),
        ];

        $this->views->getView($this, 'listar', $data);
    }

    public function accesos()
    {
        require_once 'Models/SeguridadLegajosModel.php';

        $seguridadModel = new SeguridadLegajosModel();
        $roles = $seguridadModel->selectRolesVisiblesPara(intval($_SESSION['id_rol'] ?? 0));

        $idRol = intval($_GET['id_rol'] ?? ($roles[0]['id_rol'] ?? 0));
        $rolActual = [];
        foreach ($roles as $rol) {
            if (intval($rol['id_rol'] ?? 0) === $idRol) {
                $rolActual = $rol;
                break;
            }
        }
        if (empty($rolActual) && !empty($roles)) {
            $rolActual = $roles[0];
            $idRol = intval($rolActual['id_rol'] ?? 0);
        }

        $idDepartamento = intval($rolActual['id_departamento'] ?? 0);
        $accesosDisponibles = $this->filtrarAccesosGestionablesPorUsuarioActual(
            $this->filtrarAccesosPorModulosActivos(FuncionalidadesModel::getAccesosDisponibles())
        );

        $data = [
            'roles' => $roles,
            'rol_actual' => $rolActual,
            'id_rol_actual' => $idRol,
            'id_departamento_actual' => $idDepartamento,
            'accesos_disponibles' => $accesosDisponibles,
            'accesos_actuales' => $this->model->selectAccesosPorRolDepartamento($idRol, $idDepartamento),
        ];

        $this->views->getView($this, 'accesos', $data);
    }

    public function guardar()
    {
        if (!$this->esAdministradorScantec()) {
            setAlert('warning', 'Solo el Administrador Scantec puede gestionar módulos.');
            header('Location: ' . base_url() . 'funcionalidades/accesos');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'funcionalidades/listar');
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido o expirado.');
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

    public function guardar_accesos()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url() . 'funcionalidades/accesos');
            exit();
        }

        if (!Validador::csrfValido()) {
            setAlert('error', 'Token CSRF inválido o expirado.');
            header('Location: ' . base_url() . 'funcionalidades/accesos');
            exit();
        }

        require_once 'Models/SeguridadLegajosModel.php';
        $seguridadModel = new SeguridadLegajosModel();

        $idRol = intval($_POST['id_rol'] ?? 0);
        $items = $_POST['accesos'] ?? [];
        $idUsuario = intval($_SESSION['id'] ?? 0);
        $rolActual = $seguridadModel->selectRolPorId($idRol);
        $idDepartamento = intval($rolActual['id_departamento'] ?? 0);
        $accesosPermitidos = array_keys($this->filtrarAccesosGestionablesPorUsuarioActual(
            $this->filtrarAccesosPorModulosActivos(FuncionalidadesModel::getAccesosDisponibles())
        ));

        if ($idRol <= 0 || $idDepartamento <= 0) {
            setAlert('warning', 'El rol seleccionado debe tener un departamento asociado para configurar accesos.');
            header('Location: ' . base_url() . 'funcionalidades/accesos');
            exit();
        }

        $items = array_intersect_key($items, array_flip($accesosPermitidos));

        $guardado = $this->model->guardarAccesosRolDepartamento($idRol, $idDepartamento, $items, $idUsuario);

        if ($guardado) {
            setAlert('success', 'Los accesos por rol y departamento se actualizaron correctamente.');
        } else {
            setAlert('error', 'No se pudieron guardar los accesos por rol y departamento.');
        }

        header('Location: ' . base_url() . 'funcionalidades/accesos?id_rol=' . $idRol);
        exit();
    }
}
