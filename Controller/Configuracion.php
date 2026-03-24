<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$base_path = realpath(__DIR__ . '/../');

// Incluir PHPMailer
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/Exception.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/PHPMailer.php';
require_once $base_path . '/Libraries/PHPMailer6.9.2/src/SMTP.php';

class Configuracion extends Controllers
{
    private $configuracionModel, $db;

    private function obtenerDirectorioBranding(): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, dirname(__DIR__)), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'branding';
    }

    private function obtenerLogoBrandingUrl(string $baseNombre): string
    {
        $directorio = $this->obtenerDirectorioBranding();
        if (!is_dir($directorio)) {
            return '';
        }

        $coincidencias = glob($directorio . DIRECTORY_SEPARATOR . $baseNombre . '.*') ?: [];
        if (empty($coincidencias)) {
            return '';
        }

        $archivo = basename($coincidencias[0]);
        return base_url() . 'Assets/img/branding/' . rawurlencode($archivo) . '?v=' . @filemtime($coincidencias[0]);
    }

    private function guardarLogoBranding(string $inputName, string $baseNombre): bool
    {
        if (empty($_FILES[$inputName]) || intval($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        $archivo = $_FILES[$inputName];
        if (intval($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        $permitidas = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg'];
        if (!in_array($extension, $permitidas, true)) {
            return false;
        }

        $directorio = $this->obtenerDirectorioBranding();
        if (!is_dir($directorio) && !mkdir($directorio, 0777, true) && !is_dir($directorio)) {
            return false;
        }

        foreach (glob($directorio . DIRECTORY_SEPARATOR . $baseNombre . '.*') ?: [] as $existente) {
            @unlink($existente);
        }

        $destino = $directorio . DIRECTORY_SEPARATOR . $baseNombre . '.' . $extension;
        return move_uploaded_file($archivo['tmp_name'], $destino);
    }

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
        $this->configuracionModel = new ConfiguracionModel();
        $this->db = new Mysql();
    }

    public function listar()
    {
        $data = $this->model->selectConfiguracion();
        if (isset($data[0]) && is_array($data[0])) {
            $data[0]['logo_empresa_url'] = $this->obtenerLogoBrandingUrl('logo_empresa');
            $data[0]['logo_empresa_reducido_url'] = $this->obtenerLogoBrandingUrl('logo_empresa_reducido');
        }
        $this->views->getView($this, "listar", $data);
    }

    public function mantenimiento()
    {
        $data = $this->model->selectConfiguracion();
        if (isset($data[0]) && is_array($data[0])) {
            $data[0]['logo_empresa_url'] = $this->obtenerLogoBrandingUrl('logo_empresa');
            $data[0]['logo_empresa_reducido_url'] = $this->obtenerLogoBrandingUrl('logo_empresa_reducido');
        }
        $this->views->getView($this, "mantenimiento", $data);
    }

    public function configuracion_legajos()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        $catalogo_documentos = $this->model->getCatalogoDocumentosLegajo();
        $tipos_documento = $this->model->getTiposDocumentoLegajo();
        $tab_actual = isset($_GET['tab']) ? $_GET['tab'] : 'catalogo';
        $id_documento_editar = isset($_GET['editar_documento']) ? intval($_GET['editar_documento']) : 0;
        $id_tipo_legajo_editar = isset($_GET['editar_tipo_legajo']) ? intval($_GET['editar_tipo_legajo']) : 0;
        $documento_editar = null;
        $tipo_legajo_editar = null;
        if ($id_documento_editar > 0) {
            $documento_editar = $this->model->getCatalogoDocumentoLegajoById($id_documento_editar);
        }
        if ($id_tipo_legajo_editar > 0) {
            $tipo_legajo_editar = $this->model->getTipoLegajoById($id_tipo_legajo_editar);
        }

        $id_tipoDoc = isset($_GET['id_tipoDoc']) ? intval($_GET['id_tipoDoc']) : 0;
        if ($id_tipoDoc <= 0 && !empty($tipos_documento)) {
            $id_tipoDoc = intval($tipos_documento[0]['id_tipoDoc']);
        }

        $matriz_requisitos = $id_tipoDoc > 0
            ? $this->model->getMatrizRequisitosLegajo($id_tipoDoc)
            : [];

        $tipo_documento_actual = null;
        foreach ($tipos_documento as $tipo_documento) {
            if (intval($tipo_documento['id_tipoDoc']) === $id_tipoDoc) {
                $tipo_documento_actual = $tipo_documento;
                break;
            }
        }

        $relaciones = $this->model->getRelacionesActivas();
        $politicas_actualizacion = $this->model->getPoliticasActualizacionActivas();
        
        $todas_relaciones = $this->model->getRelaciones();
        $todas_politicas = $this->model->getPoliticasActualizacion();

        $data = [
            'catalogo_documentos' => $catalogo_documentos,
            'tipos_documento' => $tipos_documento,
            'matriz_requisitos' => $matriz_requisitos,
            'id_tipoDoc_actual' => $id_tipoDoc,
            'tipo_documento_actual' => $tipo_documento_actual,
            'tab_actual' => $tab_actual,
            'documento_editar' => $documento_editar,
            'tipo_legajo_editar' => $tipo_legajo_editar,
            'relaciones' => $relaciones,
            'politicas_actualizacion' => $politicas_actualizacion,
            'todas_relaciones' => $todas_relaciones,
            'todas_politicas' => $todas_politicas
        ];
        $this->views->getView($this, "configuracion_legajos", $data);
    }

    public function guardar_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_interno = trim($_POST['codigo_interno'] ?? '');
        $tiene_vencimiento = intval($_POST['tiene_vencimiento'] ?? 0) === 1 ? 1 : 0;
        $dias_vigencia_base = $tiene_vencimiento ? intval($_POST['dias_vigencia_base'] ?? 0) : null;
        $dias_alerta_previa = $tiene_vencimiento ? intval($_POST['dias_alerta_previa'] ?? 30) : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre del documento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        if ($tiene_vencimiento && $dias_vigencia_base !== null && $dias_vigencia_base <= 0) {
            setAlert('warning', "Los días de vigencia deben ser mayores a cero.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $insert = $this->model->insertarCatalogoDocumentoLegajo(
            $nombre,
            $codigo_interno,
            $tiene_vencimiento,
            $dias_vigencia_base,
            $tiene_vencimiento ? ($dias_alerta_previa > 0 ? $dias_alerta_previa : 30) : null,
            $activo
        );

        if ($insert) {
            setAlert('success', "Documento maestro registrado correctamente.");
        } else {
            setAlert('error', "No se pudo registrar el documento maestro.");
        }

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function actualizar_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $codigo_interno = trim($_POST['codigo_interno'] ?? '');
        $tiene_vencimiento = intval($_POST['tiene_vencimiento'] ?? 0) === 1 ? 1 : 0;
        $dias_vigencia_base = $tiene_vencimiento ? intval($_POST['dias_vigencia_base'] ?? 0) : null;
        $dias_alerta_previa = $tiene_vencimiento ? intval($_POST['dias_alerta_previa'] ?? 30) : null;
        $activo = isset($_POST['activo']) ? 1 : 0;

        if ($id_documento_maestro <= 0 || $nombre === '') {
            setAlert('warning', "Datos inválidos para actualizar el documento.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        if ($tiene_vencimiento && $dias_vigencia_base !== null && $dias_vigencia_base <= 0) {
            setAlert('warning', "Los días de vigencia deben ser mayores a cero.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo&editar_documento=" . $id_documento_maestro);
            exit();
        }

        $ok = $this->model->actualizarCatalogoDocumentoLegajo(
            $id_documento_maestro,
            $nombre,
            $codigo_interno,
            $tiene_vencimiento,
            $dias_vigencia_base,
            $tiene_vencimiento ? ($dias_alerta_previa > 0 ? $dias_alerta_previa : 30) : null,
            $activo
        );

        setAlert($ok ? 'success' : 'error', $ok ? "Documento actualizado correctamente." : "No se pudo actualizar el documento.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function cambiar_estado_catalogo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($id_documento_maestro <= 0) {
            setAlert('warning', "Documento inválido.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
            exit();
        }

        $ok = $this->model->actualizarEstadoCatalogoDocumentoLegajo($id_documento_maestro, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=catalogo");
        exit();
    }

    public function guardar_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);
        $id_documento_maestro = intval($_POST['id_documento_maestro'] ?? 0);
        $rol_vinculado = trim($_POST['rol_vinculado'] ?? 'TITULAR');
        $es_obligatorio = isset($_POST['es_obligatorio']) ? 1 : 0;
        $orden_visual = intval($_POST['orden_visual'] ?? 1);
        $politicaActualizacion = strtoupper(trim($_POST['politica_actualizacion'] ?? 'REEMPLAZAR'));
        $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
        if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
            $politicaActualizacion = 'REEMPLAZAR';
        }
        $permite_reemplazo = $politicaActualizacion === 'NO_PERMITIR' ? 0 : 1;

        if ($id_tipoDoc <= 0 || $id_documento_maestro <= 0) {
            setAlert('warning', "Debe seleccionar el tipo de legajo y el documento maestro.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        if ($this->model->existeMatrizRequisitoLegajo($id_tipoDoc, $id_documento_maestro, $rol_vinculado)) {
            setAlert('warning', "Ya existe una regla para ese documento y rol en el tipo seleccionado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $insert = $this->model->insertarMatrizRequisitoLegajo(
            $id_tipoDoc,
            $id_documento_maestro,
            $rol_vinculado,
            $es_obligatorio,
            $orden_visual > 0 ? $orden_visual : 1,
            $permite_reemplazo,
            $politicaActualizacion
        );

        if ($insert) {
            setAlert('success', "Regla agregada correctamente.");
        } else {
            setAlert('error', "No se pudo agregar la regla.");
        }

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    public function guardar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF invÃ¡lido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $nombre = trim($_POST['nombre_tipo_legajo'] ?? '');
        $descripcion = trim($_POST['descripcion_tipo_legajo'] ?? '');
        $requiereNroSolicitud = isset($_POST['requiere_nro_solicitud']) ? 1 : 0;

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre del tipo de legajo.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        if ($this->model->existeTipoLegajoPorNombre($nombre)) {
            setAlert('warning', "Ese tipo de legajo ya existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $insert = $this->model->insertarTipoLegajo($nombre, $descripcion !== '' ? $descripcion : null, 1, $requiereNroSolicitud);
        if ($insert) {
            setAlert('success', "Tipo de legajo registrado correctamente.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        setAlert('error', "No se pudo registrar el tipo de legajo.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function actualizar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF invÃ¡lido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $idTipoLegajo = intval($_POST['id_tipo_legajo'] ?? 0);
        $nombre = trim($_POST['nombre_tipo_legajo'] ?? '');
        $descripcion = trim($_POST['descripcion_tipo_legajo'] ?? '');
        $activo = isset($_POST['activo_tipo_legajo']) ? 1 : 0;
        $requiereNroSolicitud = isset($_POST['requiere_nro_solicitud']) ? 1 : 0;

        if ($idTipoLegajo <= 0 || $nombre === '') {
            setAlert('warning', "Datos invÃ¡lidos para actualizar el tipo de legajo.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $actual = $this->model->getTipoLegajoById($idTipoLegajo);
        if (!$actual) {
            setAlert('error', "El tipo de legajo no existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        if (strcasecmp(trim($actual['nombre'] ?? ''), $nombre) !== 0 && $this->model->existeTipoLegajoPorNombre($nombre)) {
            setAlert('warning', "Ese tipo de legajo ya existe.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos&editar_tipo_legajo=" . $idTipoLegajo);
            exit();
        }

        $ok = $this->model->actualizarTipoLegajo($idTipoLegajo, $nombre, $descripcion !== '' ? $descripcion : null, $activo, $requiereNroSolicitud);
        setAlert($ok ? 'success' : 'error', $ok ? "Tipo de legajo actualizado correctamente." : "No se pudo actualizar el tipo de legajo.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function eliminar_tipo_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF invÃ¡lido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $idTipoLegajo = intval($_POST['id_tipo_legajo'] ?? 0);
        if ($idTipoLegajo <= 0) {
            setAlert('warning', "Tipo de legajo invÃ¡lido.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
            exit();
        }

        $ok = $this->model->eliminarTipoLegajo($idTipoLegajo);
        setAlert($ok ? 'success' : 'error', $ok ? "Tipo de legajo eliminado correctamente." : "No se pudo eliminar el tipo de legajo. Verifique si tiene reglas asociadas.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=tipos");
        exit();
    }

    public function eliminar_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $id_requisito = intval($_POST['id_requisito'] ?? 0);
        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);

        if ($id_requisito <= 0) {
            setAlert('warning', "Regla inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $ok = $this->model->eliminarMatrizRequisitoLegajo($id_requisito);
        setAlert($ok ? 'success' : 'error', $ok ? "Regla eliminada correctamente." : "No se pudo eliminar la regla.");

        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    public function guardar_cambios_matriz_legajo()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz");
            exit();
        }

        $id_tipoDoc = intval($_POST['id_tipoDoc'] ?? 0);
        $reglas = $_POST['reglas'] ?? [];

        if ($id_tipoDoc <= 0 || empty($reglas) || !is_array($reglas)) {
            setAlert('warning', "No hay cambios válidos para guardar.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
            exit();
        }

        $actualizados = 0;
        foreach ($reglas as $idRequisito => $regla) {
            $idRequisito = intval($idRequisito);
            if ($idRequisito <= 0) {
                continue;
            }

            $idDocumentoMaestro = intval($regla['id_documento_maestro'] ?? 0);
            $rolVinculado = trim($regla['rol_vinculado'] ?? 'TITULAR');
            $esObligatorio = intval($regla['es_obligatorio'] ?? 0) === 1 ? 1 : 0;
            $ordenVisual = intval($regla['orden_visual'] ?? 1);
            $politicaActualizacion = strtoupper(trim($regla['politica_actualizacion'] ?? ''));
            $politicasPermitidas = ['REEMPLAZAR', 'UNIR_AL_INICIO', 'UNIR_AL_FINAL', 'NO_PERMITIR', 'CONSULTAR'];
            if (!in_array($politicaActualizacion, $politicasPermitidas, true)) {
                $permiteReemplazoFallback = intval($regla['permite_reemplazo'] ?? 0) === 1;
                $politicaActualizacion = $permiteReemplazoFallback ? 'REEMPLAZAR' : 'NO_PERMITIR';
            }
            $permiteReemplazo = $politicaActualizacion === 'NO_PERMITIR' ? 0 : 1;

            if ($idDocumentoMaestro <= 0) {
                continue;
            }

            if ($this->model->existeOtroMatrizRequisitoLegajo($idRequisito, $id_tipoDoc, $idDocumentoMaestro, $rolVinculado)) {
                setAlert('warning', "Existe una regla duplicada para el documento y rol seleccionados.");
                header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
                exit();
            }

            $ok = $this->model->actualizarMatrizRequisitoLegajo(
                $idRequisito,
                $idDocumentoMaestro,
                $rolVinculado,
                $esObligatorio,
                $ordenVisual > 0 ? $ordenVisual : 1,
                $permiteReemplazo,
                $politicaActualizacion
            );

            if ($ok) {
                $actualizados++;
            }
        }

        setAlert($actualizados > 0 ? 'success' : 'info', $actualizados > 0 ? "Cambios de matriz guardados." : "No se detectaron cambios para guardar.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=matriz&id_tipoDoc=" . $id_tipoDoc);
        exit();
    }

    // ============================================================
    // SECCIÓN: Legajos Datos Generales (Administración de Relaciones)
    // ============================================================

    // ELIMINADO: public function datos_generales_legajos()

    public function guardar_relacion()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $nombre = strtoupper(trim($_POST['nombre_relacion'] ?? ''));
        $orden = intval($_POST['orden_relacion'] ?? 0);

        if ($nombre === '') {
            setAlert('warning', "Debe ingresar el nombre de la relación.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        if ($this->model->existeRelacionPorNombre($nombre)) {
            setAlert('warning', "Ya existe una relación con ese nombre.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $insert = $this->model->insertarRelacion($nombre, $orden);
        setAlert($insert ? 'success' : 'error', $insert ? "Relación registrada correctamente." : "No se pudo registrar la relación.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function cambiar_estado_relacion()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idRelacion = intval($_POST['id_relacion'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($idRelacion <= 0) {
            setAlert('warning', "Relación inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $ok = $this->model->cambiarEstadoRelacion($idRelacion, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function eliminar_relacion()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idRelacion = intval($_POST['id_relacion'] ?? 0);
        if ($idRelacion <= 0) {
            setAlert('warning', "Relación inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $resultado = $this->model->eliminarRelacion($idRelacion);

        if ($resultado === 'EN_USO_MATRIZ') {
            setAlert('error', "No se puede eliminar: esta relación está asignada en reglas de la Matriz de Requisitos. Puede desactivarla en su lugar.");
        } elseif ($resultado === 'EN_USO_LEGAJOS') {
            setAlert('error', "No se puede eliminar: existen legajos armados que usan esta relación. Puede desactivarla en su lugar.");
        } elseif ($resultado) {
            setAlert('success', "Relación eliminada correctamente.");
        } else {
            setAlert('error', "No se pudo eliminar la relación.");
        }
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function cambiar_estado_politica()
    {
        if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $idPolitica = intval($_POST['id_politica'] ?? 0);
        $activo = intval($_POST['activo'] ?? 0);

        if ($idPolitica <= 0) {
            setAlert('warning', "Política inválida.");
            header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
            exit();
        }

        $ok = $this->model->cambiarEstadoPolitica($idPolitica, $activo);
        setAlert($ok ? 'success' : 'error', $ok ? "Estado actualizado." : "No se pudo actualizar el estado.");
        header("Location: " . base_url() . "configuracion/configuracion_legajos?tab=datos");
        exit();
    }

    public function servidor_AD()
    {
        $LDAP_datos = $this->model->selectLDAP_datos();
        $data = ['LDAP_datos' => $LDAP_datos];
        $this->views->getView($this, "servidor_AD", $data);
    }

    // ==========================================
    // CARGAR VISTA SMTP (Corregido CSRF y Array anidado)
    // ==========================================
    public function servidor_smtp()
    {
        // 1. Generar Token CSRF si no existe (Soluciona los Warnings)
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_expiration'] = time() + 3600;
        }

        // 2. Traer solo la configuración activa
        $smtp_datos = $this->model->getActiveSMTP();

        // 3. Desanidar el array para que la vista detecte el 'ACTIVO'
        if (isset($smtp_datos[0]['host'])) {
            $smtp_datos = $smtp_datos[0];
        }

        $data = ['smtp_datos' => $smtp_datos];
        $this->views->getView($this, "servidor_smtp", $data);
    }

    public function actualizar()
    {
        if ($_POST) {
            if (empty($_POST['nombre']) || empty($_POST['correo'])) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'El nombre y correo son obligatorios.'];
                header("location: " . base_url() . "configuracion/listar");
                die();
            }

            $id = intval($_POST['id']);
            $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
            $telefono = htmlspecialchars(trim($_POST['telefono']), ENT_QUOTES, 'UTF-8');
            $direccion = htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8');
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $total_pag = intval($_POST['total_pag']);

            $logoPrincipalOk = $this->guardarLogoBranding('logo_empresa', 'logo_empresa');
            $logoReducidoOk = $this->guardarLogoBranding('logo_empresa_reducido', 'logo_empresa_reducido');
            if (!$logoPrincipalOk || !$logoReducidoOk) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo cargar uno de los logos. Verifique el formato del archivo.'];
                header("location: " . base_url() . "configuracion/listar");
                die();
            }

            $actualizar = $this->model->actualizarConfiguracion($nombre, $telefono, $direccion, $correo, $total_pag, $id);

            if ($actualizar) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Datos de la empresa actualizados.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudieron guardar los cambios.'];
            }

            header("location: " . base_url() . "configuracion/listar");
            die();
        }
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function backup()
    {
        $result = $this->configuracionModel->backupDatabase();
        if ($result['status']) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $result['msg']];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function restore()
    {
        if (isset($_FILES['sqlFile']) && $_FILES['sqlFile']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['sqlFile']['tmp_name'];
            $fileName = $_FILES['sqlFile']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($fileExtension === 'sql') {
                $result = $this->configuracionModel->RestoreDatabase($fileTmpPath);
                if ($result['status']) {
                    $_SESSION['alert'] = ['type' => 'success', 'message' => $result['msg']];
                } else {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error en BD: ' . $result['msg']];
                }
            } else {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Error: El archivo debe tener extensión .sql'];
            }
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: No se seleccionó ningún archivo o hubo un error en la subida.'];
        }
        header("location: " . base_url() . "configuracion/mantenimiento");
        die();
    }

    // ==========================================
    // MODIFICADO: Uso de $_SESSION['alert']
    // ==========================================
    public function respaldo_archivos()
    {
        try {
            // 1. Verificar Token CSRF (Seguridad)
            if (!Validador::csrfValido()) {
            setAlert('error', "Token CSRF inválido o expirado.");
            session_write_close();
            header("Location: " . base_url() . "?error=csrf");
            exit();
        }

            // 2. Recibir y limpiar la ruta de forma segura
            $ruta_destino = isset($_POST['ruta_destino']) ? trim($_POST['ruta_destino']) : '';

            if (empty($ruta_destino)) {
                $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Debe especificar una ruta de destino válida.'];
                header('Location: ' . base_url() . 'configuracion/mantenimiento');
                exit();
            }

            // 3. Ejecutar el respaldo
            $resultado = $this->configuracionModel->ejecutarRespaldo($ruta_destino);

            if ($resultado['status']) {
                $_SESSION['alert'] = ['type' => 'success', 'message' => $resultado['msg']];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error: ' . $resultado['msg']];
            }

        } catch (Throwable $e) {
            // ESTO EVITA EL ERROR 500. Atrapa el error fatal y lo muestra en la alerta.
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error Crítico (500): ' . $e->getMessage() . ' en la línea ' . $e->getLine()];
        }

        header('Location: ' . base_url() . 'configuracion/mantenimiento');
        exit;
    }

    // ==========================================
    // ENVÍO PARA OTRAS PARTES DEL SISTEMA
    // ==========================================
    public function sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje)
    {
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host']))
            $smtpConfig = $smtpConfig[0];

        if (empty($smtpConfig))
            return false;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];

            // Desencriptar contraseña de la BD
            if (function_exists('stringDecryption')) {
                $mail->Password = stringDecryption($smtpConfig['password']);
            } else {
                $mail->Password = $smtpConfig['password'];
            }

            if ($smtpConfig['smtpsecure'] === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->Port = $smtpConfig['port'];

            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';
            // Forzamos username como remitente para evitar bloqueos
            $mail->setFrom($smtpConfig['username'], $fromName);

            if (is_array($destinatarios)) {
                foreach ($destinatarios as $email => $nombre) {
                    $mail->addAddress($email, $nombre);
                }
            } else {
                $mail->addAddress($destinatarios);
            }
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';

            if ($filePath && file_exists($filePath)) {
                $mail->addAttachment($filePath);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // ==========================================
    // 1. GUARDAR CONFIGURACIÓN SMTP
    // ==========================================
    public function guardarServCorreo()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // Recuperar config actual para no borrar la clave
        $configActual = $this->model->getActiveSMTP();
        if (isset($configActual[0]['host']))
            $configActual = $configActual[0];

        // LOGICA DE CONTRASEÑA: Si viene vacía, usamos la que ya estaba guardada.
        if (empty($password_raw) && !empty($configActual['password'])) {
            $password = $configActual['password'];
        } else {
            $password = function_exists('stringEncryption') ? stringEncryption($password_raw) : $password_raw;
        }

        $remitente = !empty($_POST['remitente']) ? trim($_POST['remitente']) : $username;
        $nombre_remitente = !empty($_POST['nombre_remitente']) ? trim($_POST['nombre_remitente']) : 'SCANTEC Notificaciones';

        $request = $this->model->insertarServSMTP($host, $username, $password, $smtpsecure, $port, $remitente, $nombre_remitente);

        if ($request) {
            unset($_SESSION['smtp_temp']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Configuración guardada y activada correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar los datos en la base de datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 2. PROBAR CONEXIÓN SMTP (Inteligente con la clave)
    // ==========================================
    public function probar_smtp()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $host = trim($_POST['host']);
        $port = intval($_POST['port']);
        $username = trim($_POST['username']);
        $smtpsecure = $_POST['smtpsecure'];
        $password_raw = $_POST['password'];

        // --- LÓGICA INTELIGENTE DE CONTRASEÑA PARA EL TEST ---
        if (empty($password_raw)) {
            // Si dejó el campo vacío, buscamos la contraseña de la BD y la DESENCRIPTAMOS
            $configActual = $this->model->getActiveSMTP();
            if (isset($configActual[0]['host'])) {
                $configActual = $configActual[0];
            }

            if (!empty($configActual['password'])) {
                $password = function_exists('stringDecryption') ? stringDecryption($configActual['password']) : $configActual['password'];
            } else {
                $password = '';
            }
        } else {
            // Si el usuario escribió algo, asumimos que es su contraseña real y la usamos tal cual
            $password = $password_raw;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password; // Usamos la contraseña real o la desencriptada

            if ($smtpsecure === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($smtpsecure === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $port;
            $mail->Timeout = 10;

            // Tolerancia de certificados para evitar problemas de conexión locales
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Conexión Exitosa! El servidor aceptó las credenciales.'];
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Conexión fallida. Verifique los datos o su firewall.'];
            }

        } catch (Exception $e) {
            $errorMsg = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de conexión: ' . $errorMsg];
        }

        $_SESSION['smtp_temp'] = $_POST;
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }
    public function desactivar_servicio_smtp()
    {
        $this->model->desactivarSMTP();
        $_SESSION['alert'] = ['type' => 'info', 'message' => 'El servicio de correo ha sido desactivado.'];
        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // 4. TEST DE ENVÍO
    // ==========================================
    public function enviarCorreo()
    {
        // 1. Validar CSRF
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad CSRF.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        // 2. Obtener datos
        $smtpConfig = $this->model->getActiveSMTP();
        if (isset($smtpConfig[0]['host'])) {
            $smtpConfig = $smtpConfig[0];
        }

        if (empty($smtpConfig) || !isset($smtpConfig['host'])) {
            $_SESSION['alert'] = ['type' => 'warning', 'message' => 'No hay configuración SMTP activa.'];
            header("Location: " . base_url() . "configuracion/servidor_smtp");
            die();
        }

        $destinatario = trim($_POST['destinatario']);
        $asunto = trim($_POST['asunto']);
        $mensaje = trim($_POST['mensaje']);

        if (function_exists('stringDecryption')) {
            $password_real = stringDecryption($smtpConfig['password']);
        } else {
            $password_real = $smtpConfig['password'];
        }

        try {
            $mail = new PHPMailer(true);

            // Silenciamos el debug para que no imprima texto en la pantalla
            $mail->SMTPDebug = 0;
            $mail->setLanguage('es', '../vendor/phpmailer/phpmailer/language/');

            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $password_real;

            if (strtolower($smtpConfig['smtpsecure']) == 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else if (strtolower($smtpConfig['smtpsecure']) == 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            $mail->Port = $smtpConfig['port'];
            $mail->Timeout = 15;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Blindaje de remitente: Usamos el username para evitar bloqueos del servidor
            $fromEmail = $smtpConfig['username'];
            $fromName = !empty($smtpConfig['nombre_remitente']) ? $smtpConfig['nombre_remitente'] : 'SCANTEC Notificaciones';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($destinatario);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $asunto;
            $mail->Body = $mensaje;

            $mail->send();

            $_SESSION['alert'] = ['type' => 'success', 'message' => '¡Correo enviado correctamente a ' . $destinatario . '!'];

        } catch (Exception $e) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al enviar: ' . $mail->ErrorInfo];
        }

        header("Location: " . base_url() . "configuracion/servidor_smtp");
        die();
    }

    // ==========================================
    // GUARDAR LDAP
    // ==========================================
    public function probar_conexionAD()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'CSRF inválido.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapHost = filter_input(INPUT_POST, 'ldapHost', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPort = filter_input(INPUT_POST, 'ldapPort', FILTER_VALIDATE_INT);
        $ldapUser = filter_input(INPUT_POST, 'ldapUser', FILTER_SANITIZE_SPECIAL_CHARS);
        $ldapPass = $_POST['ldapPass'];
        $ldapBaseDn = $_POST['ldapBaseDn'];

        $_SESSION['ldap_data'] = [
            'ldapHost' => $ldapHost,
            'ldapPort' => $ldapPort,
            'ldapUser' => $ldapUser,
            'ldapPass' => $ldapPass,
            'ldapBaseDn' => $ldapBaseDn
        ];
        if (!$ldapHost || !$ldapPort || !$ldapUser || !$ldapPass || !$ldapBaseDn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        $ldapConn = ldap_connect($ldapHost, $ldapPort);
        if (!$ldapConn) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'No se pudo conectar al servidor LDAP.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($ldapConn, $ldapUser, $ldapPass)) {
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Conexión exitosa.'];
        } else {
            $errorMsg = ldap_error($ldapConn);
            ldap_unbind($ldapConn);
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Fallo al conectar: ' . $errorMsg];
        }
        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }

    public function saveLDAP_server()
    {
        if (!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error de seguridad (Token inválido).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        $ldapHost = filter_var(trim($_POST['ldapHost']), FILTER_SANITIZE_URL);
        $ldapPort = filter_var($_POST['ldapPort'], FILTER_VALIDATE_INT);
        $ldapBaseDn = trim($_POST['ldapBaseDn']);
        $ldapUser = trim($_POST['ldapUser']);
        $ldapPass = $_POST['ldapPass'];

        if (empty($ldapHost) || empty($ldapPort) || empty($ldapBaseDn) || empty($ldapUser) || empty($ldapPass)) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Todos los campos son obligatorios.'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        if (!$ldapPort || $ldapPort <= 0 || $ldapPort > 65535) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Puerto inválido (1-65535).'];
            header("Location: " . base_url() . "configuracion/servidor_AD");
            exit();
        }

        date_default_timezone_set('America/Asuncion');
        $fecha_registro = date('Y-m-d H:i:s');

        $guardarLDAPserver = $this->model->insertarServLDAP($ldapHost, $ldapPort, $ldapBaseDn, $ldapUser, $ldapPass, $fecha_registro);

        if ($guardarLDAPserver) {
            unset($_SESSION['ldap_data']);
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Servidor LDAP registrado y encriptado correctamente.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error al guardar en la Base de Datos.'];
        }

        header("Location: " . base_url() . "configuracion/servidor_AD");
        exit();
    }
}
