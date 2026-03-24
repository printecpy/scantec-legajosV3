<?php
class UnirpdfModel extends Mysql{
    protected $columna_01, $columna_02, $columna_03;
    public function __construct()
    {
        parent::__construct();
    }

    public function selectConfiguracion()
    {
        $sql = "SELECT * FROM configuracion";
        $res = $this->select($sql);
        return $res;
    }

   public function selectTipoDoc()
    {
        // Validación de seguridad
        if (empty($_SESSION['id_grupo'])) {
            return [];
        }
        $id_grupo = intval($_SESSION['id_grupo']); 
        $sql = "SELECT 
                    a.id, 
                    a.id_grupo, 
                    b.descripcion AS grupo_descripcion, 
                    a.id_tipoDoc, 
                    c.nombre_tipoDoc, 
                    c.indice_1, 
                    c.indice_2, 
                    c.indice_3, 
                    c.indice_4, 
                    c.indice_5, 
                    c.indice_6, 
                    b.estado AS estado_grupo
                FROM permisos_documentos a INNER JOIN usu_grupo b 
                ON a.id_grupo = b.id_grupo INNER JOIN tipo_documento c 
                ON a.id_tipoDoc = c.id_tipoDoc
                WHERE a.estado = 'ACTIVO' AND a.id_grupo = $id_grupo
                ORDER BY a.id ASC;";
        $res = $this->select_all($sql);
        return $res;
    }
    
    public function insertarUnirpdf($col1, $col2, $col3, $usuario, $archivo, $ruta)
    {
        $sql = "INSERT INTO unirpdf (columna_01, columna_02, columna_03, usuario, ruta_creacion, nombre_archivo, fecha_creacion) VALUES (?,?,?,?,?,?, NOW())";
        $datos = array($col1, $col2, $col3, $usuario, $archivo, $ruta);
        return $this->insert($sql, $datos);
    }

    // Función para la tabla nueva
    public function registrarExpediente(
        string $id_proceso,
        string $id_tipoDoc,
        string $indice_01,
        string $indice_02,
        string $indice_03,
        string $indice_04,
        string $indice_05,
        string $indice_06,
        int $paginas,
        string $ruta_original,
        string $ubicacion,
        string $version,
        string $fecha_indexado
    ) {
        $this->id_proceso = $id_proceso;
        $this->id_tipoDoc = $id_tipoDoc;
        $this->indice_01 = $indice_01;
        $this->indice_02 = $indice_02;
        $this->indice_03 = $indice_03;
        $this->indice_04 = $indice_04;
        $this->indice_05 = $indice_05;
        $this->indice_06 = $indice_06;
        $this->paginas = $paginas;
        $this->ruta_original = $ruta_original;
        $this->ubicacion = $ubicacion;
        $this->version = $version;
        $this->fecha_indexado = $fecha_indexado;
        $query = "INSERT INTO expediente (
                        id_proceso, id_tipoDoc, indice_01, indice_02, indice_03, 
                        indice_04, indice_05, indice_06, ubicacion, paginas, 
                        ruta_original, version, fecha_indexado, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Activo');";
        $data = array(
            $this->id_proceso,
            $this->id_tipoDoc,
            $this->indice_01,
            $this->indice_02,
            $this->indice_03,
            $this->indice_04,
            $this->indice_05,
            $this->indice_06,
            $this->ubicacion,
            $this->paginas,
            $this->ruta_original,
            $this->version,
            $this->fecha_indexado
        );
        $this->insert($query, $data);
        return true;
    }

}
