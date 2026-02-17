<?php
require_once 'Controller/Configuracion.php';
require_once 'Libraries/fpdi/src/autoload.php';
require_once 'Libraries/pdf/fpdf.php';

use setasign\Fpdi\Fpdi;

class Expedientes extends Controllers
{
    public function __construct()
    {
        session_start();
        if (empty($_SESSION['ACTIVO'])) {
            header("location: " . base_url());
        }
        parent::__construct();
    }
    public function expedientes()
    {
        $expediente = $this->model->selectExpediente();
        $data = ['expediente' => $expediente];
        $this->views->getView($this, "listar", $data);
    }

    public function indice_busqueda()
    {
        $indice_04 = $this->model->selectDocumento();
        $tipos_documentos = $this->model->selectTipoDoc();
        // Asegúrate de que la clave sea la misma en la vista y en el controlador
        $data = ['indice_04' => $indice_04, 'tipos_documentos' => $tipos_documentos];
        $this->views->getView($this, "indice_busqueda", $data);
    }

    public function upload_files()
    {
        $tipos_documentos = $this->model->selectTipoDoc();
        // Asegúrate de que la clave sea la misma en la vista y en el controlador
        $data = ['tipos_documentos' => $tipos_documentos];
        $this->views->getView($this, "upload_files", $data);
    }


    /*         public function busqueda2()
        {
            $indice_04_pattern = $_GET['indice_04_pattern'];
            $busqueda = $this->model->buscarExpediente2($indice_04_pattern);
            $data = ['busqueda' => $busqueda];
            if ($data == 0) {
                $this->busqueda();
            } else {
                $this->views->getView($this, "busqueda", $data);
            }
        }
 */

    /* public function busqueda()
        {
            // Sanitización de los valores recibidos en los inputs
            $id_tipoDoc = filter_input(INPUT_GET, 'id_tipoDoc', FILTER_SANITIZE_NUMBER_INT);
            $indice_01_pattern = filter_input(INPUT_GET, 'indice_01_pattern', FILTER_SANITIZE_STRING);
            $indice_02_pattern = filter_input(INPUT_GET, 'indice_02_pattern', FILTER_SANITIZE_STRING);
            $indice_03_pattern = filter_input(INPUT_GET, 'indice_03_pattern', FILTER_SANITIZE_STRING);
            $indice_04_pattern = filter_input(INPUT_GET, 'indice_04_pattern', FILTER_SANITIZE_STRING);

            $id_tipoDoc = $id_tipoDoc ? (int)$id_tipoDoc : 0;
            $busqueda = $this->model->buscarExpediente2($id_tipoDoc, $indice_01_pattern, $indice_02_pattern, $indice_03_pattern, $indice_04_pattern);
            $data = ['busqueda' => $busqueda];
            if ($data == 0) {
                $this->busqueda();
            } else {
                $this->views->getView($this, "busqueda", $data);
            }
        } */

    public function busqueda()
    {
        $id_tipoDoc = filter_input(INPUT_GET, 'id_tipoDoc', FILTER_SANITIZE_NUMBER_INT);
        $termino = filter_input(INPUT_GET, 'termino', FILTER_SANITIZE_STRING);
        $id_tipoDoc = $id_tipoDoc ? (int) $id_tipoDoc : 0;

        $busqueda = $this->model->buscarExpedientePorTermino($id_tipoDoc, $termino);
        $data = ['busqueda' => $busqueda, 'termino' => $termino];
        $this->views->getView($this, "busqueda", $data);
    }

    public function mostrar_registros()
    {
        $indice_01 = htmlspecialchars($_GET['indice_01']);
        $nombre_tipoDoc = htmlspecialchars($_GET['nombre_tipoDoc']);
        $termino = htmlspecialchars($_GET['termino']);
        $mostrar_registros = $this->model->selectRegistros2($indice_01, $nombre_tipoDoc, $termino);
        $data = ['mostrar_registros' => $mostrar_registros];
        if ($data == 0) {
            $this->mostrar_registros();
        } else {
            $this->views->getView($this, "mostrar_registros", $data);
        }
    }

    public function reporte()
    {
        $data = $this->model->selectExpediente();
        $this->views->getView($this, "reporte", $data);
    }

    public function firmador()
    {
        $data = $this->model->selectExpediente();
        $this->views->getView($this, "firmador", $data);
    }

    /* public function registrar()
        {
            $titulo = $_POST['titulo'];
            $cantidad = $_POST['cantidad'];
            $autor = $_POST['autor'];
            $editorial = $_POST['editorial'];
            $anio_edicion = $_POST['anio_edicion'];
            $editorial = $_POST['editorial'];
            $materia = $_POST['materia'];
            $num_pagina = $_POST['num_pagina'];
            $descripcion = $_POST['descripcion'];
            $img = $_FILES['imagen'];
            $imgName = $img['name'];
            $nombreTemp = $img['tmp_name'];
            $fecha = md5(date("Y-m-d h:i:s")) ."_". $imgName;
            $destino = "Assets/images/libros/" . $fecha;
            if ($imgName == null || $imgName == "") {
                $insert = $this->model->insertarLibro($titulo, $cantidad, $autor, $editorial, $anio_edicion, $materia, $num_pagina, $descripcion, "default-avatar.png");
            }else{
                $insert = $this->model->insertarLibro($titulo, $cantidad, $autor ,$editorial, $anio_edicion, $materia, $num_pagina, $descripcion, $fecha);
                if ($insert) {
                    move_uploaded_file($nombreTemp, $destino);
                }
            }
            header("location: " . base_url() . "expedientes");
            die();
        } */
    public function editar()
    {
        //Validar el ID que viene por URL
        if (empty($_GET['id_expediente'])) {
            header("Location: " . base_url() . "expedientes");
            exit();
        }
        $id_expediente = $_GET['id_expediente'];
        //Consultar al Modelo
        $respuesta_modelo = $this->model->editExpediente($id_expediente);
        // Verificamos si la respuesta tiene el índice 0 (es decir, si es un array de arrays)
        if (!empty($respuesta_modelo) && isset($respuesta_modelo[0])) {
            $expediente = $respuesta_modelo[0];
        } else {
            // Si no tiene índice 0, asumimos que ya es el array plano o está vacío
            $expediente = $respuesta_modelo;
        }
        // validación final: Si después de limpiar sigue vacío, es que no existe el ID
        if (empty($expediente)) {
            header("Location: " . base_url() . "expedientes");
            exit();
        }

        // 5. Enviar a la Vista
        // Ahora $expediente es el array limpio: ['id_expediente' => 10273, 'indice_01' => 'AGOSTO'...]
        $data = [
            'page_title' => 'Modificar Expediente',
            'expediente' => $expediente 
        ];

        $this->views->getView($this, "editar", $data);
    }
    public function renombrar()
    {
        $id_expediente = $_GET['id_expediente'];
        $expediente = $this->model->renomExpediente($id_expediente);
        $data = ['expediente' => $expediente];
        if ($data == 0) {
            $this->expedientes();
        } else {
            $this->views->getView($this, "renombrar", $data);
        }
    }

   public function modificar()
    {
        // 1. Verificar Token CSRF (Seguridad)
        // Si no existe o no coincide, detenemos la ejecución para evitar ataques.
        if (!isset($_POST['token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "expedientes?error=csrf");
            die();
        }

        // 2. Recibir datos del formulario (POST)
        // Usamos el operador de fusión null (??) para evitar errores "Undefined index"
        $id_expediente = $_POST['id_expediente'];
        
        // id_proceso puede venir vacío si el input estaba 'disabled' en el HTML.
        // Si es vital, asegúrate de quitar el 'disabled' o usar 'readonly'.
        $id_proceso    = $_POST['id_proceso'] ?? ''; 

        // Limpiamos los datos básicos con htmlspecialchars para evitar XSS
        $indice_01     = htmlspecialchars($_POST['indice_01'] ?? '');
        $indice_02     = htmlspecialchars($_POST['indice_02'] ?? '');
        $indice_03     = htmlspecialchars($_POST['indice_03'] ?? '');
        $indice_04     = htmlspecialchars($_POST['indice_04'] ?? '');
        $indice_05     = htmlspecialchars($_POST['indice_05'] ?? '');
        $indice_06     = htmlspecialchars($_POST['indice_06'] ?? '');
        $ubicacion     = htmlspecialchars($_POST['ubicacion'] ?? '');
        $firma_digital = htmlspecialchars($_POST['firma_digital'] ?? 'no'); // Valor por defecto 'no'
        $version       = htmlspecialchars($_POST['version'] ?? '1.0');     // Valor por defecto '1.0'
        
        // CORRECCIÓN CLAVE DEL ERROR 500:
        // Aseguramos que 'paginas' sea un número entero. Si no viene, ponemos 1.
        $paginas       = !empty($_POST['paginas']) ? intval($_POST['paginas']) : 1;

        // 3. Llamar al Modelo
        // El orden de los argumentos debe coincidir EXACTAMENTE con la definición en tu Modelo.
        $actualizar = $this->model->actualizarExpediente(
            $id_proceso,
            $indice_01,
            $indice_02,
            $indice_03,
            $indice_04,
            $indice_05,
            $indice_06,
            $ubicacion,
            $firma_digital,
            $version,
            $paginas,       // <--- Este era el dato que faltaba antes
            $id_expediente
        );

        // 4. Redireccionar con Mensaje
        if ($actualizar) {
            // Guardamos mensaje de ÉXITO en la sesión
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Expediente modificado correctamente.'
            ];
            
            // Redirigimos a la misma página de edición para ver los cambios reflejados
            header("Location: " . base_url() . "expedientes/editar?id_expediente=" . $id_expediente);
            die();
        } else {
            // Guardamos mensaje de ERROR en la sesión
            $_SESSION['alert'] = [
                'type' => 'error',
                'message' => 'Error al guardar los cambios en la base de datos.'
            ];

            // Redirigimos para que el usuario intente de nuevo
            header("Location: " . base_url() . "expedientes/editar?id_expediente=" . $id_expediente);
            die();
        }
    }
    public function subir()
    {
        // 1. 🚀 CONFIGURACIÓN DE RENDIMIENTO
        // Evita que el script se corte por tiempo (Timeout)
        set_time_limit(0);
        // Aumenta memoria temporalmente para este proceso pesado
        ini_set('memory_limit', '1024M'); 

        // --- 🔐 SEGURIDAD: Validación de CSRF ---
        if (!isset($_POST['token']) || !isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['token']) {
            header("Location: " . base_url() . "?error=csrf");
            die();
        }

        // === 🛠️ RUTAS ABSOLUTAS ===
        $magick_escaped = escapeshellarg(MAGICK_EXECUTABLE_PATH);
        $tesseract_escaped = escapeshellarg(TESSERACT_EXECUTABLE_PATH);

        // --- ⚙️ PREPARACIÓN DE DATOS ---
        date_default_timezone_set('America/Asuncion');
        $id_proceso = date("Ymd-His");
        
        // Saneamiento de entradas
        $id_tipoDoc = filter_input(INPUT_POST, 'id_tipoDoc', FILTER_SANITIZE_STRING);
        $indice_01 = filter_input(INPUT_POST, 'indice_01', FILTER_SANITIZE_STRING);
        $indice_02 = filter_input(INPUT_POST, 'indice_02', FILTER_SANITIZE_STRING);
        $indice_03 = filter_input(INPUT_POST, 'indice_03', FILTER_SANITIZE_STRING);
        $indice_04 = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['indice_04']); // Solo letras, nums y guiones
        $indice_05 = filter_input(INPUT_POST, 'indice_05', FILTER_SANITIZE_STRING);
        $indice_06 = filter_input(INPUT_POST, 'indice_06', FILTER_SANITIZE_STRING);

        // --- 📁 RUTAS DE ARCHIVOS ---
        $nombre_final = $indice_04 . '.pdf'; // Usamos Indice 4 como nombre
        $ruta_relativa = 'Expedientes/' . $nombre_final;
        $ubicacion_fisica = RUTA_BASE . $ruta_relativa;

        // --- 📤 VALIDAR SUBIDA ---
        if (!isset($_FILES['file_pdf']) || $_FILES['file_pdf']['error'] !== UPLOAD_ERR_OK) {
            setAlert('error', "Error al subir el archivo.");
            header("location: " . base_url() . "expedientes/upload_files");
            exit();
        }

        // Crear carpeta si no existe
        $dir_expedientes = dirname($ubicacion_fisica);
        if (!is_dir($dir_expedientes)) {
            mkdir($dir_expedientes, 0777, true);
        }

        // Mover archivo
        if (!move_uploaded_file($_FILES['file_pdf']['tmp_name'], $ubicacion_fisica)) {
            setAlert('error', "Error al guardar el archivo en el servidor.");
            header("location: " . base_url() . "expedientes/upload_files");
            exit();
        }

        // =============================================
        // 🔄 PROCESAMIENTO: ENDEREZADO (DESKEW)
        // =============================================
        // Nota: Solo procesamos si realmente queremos corregir (Esto es LENTO)
        
        $temp_dir = RUTA_BASE . 'Temp/' . $id_proceso . '/';
        if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);

        $temp_output = RUTA_BASE . 'Temp/oriented_' . $nombre_final;
        $pdf_path_escaped = escapeshellarg($ubicacion_fisica);
        $img_output_path = escapeshellarg($temp_dir . 'page_%03d.png');

        // 1️⃣ Convertir PDF a imágenes (Bajamos a 200 DPI para velocidad, 300 es muy pesado)
        $cmd_convert = "$magick_escaped -density 200 -units PixelsPerInch $pdf_path_escaped $img_output_path 2>&1";
        exec($cmd_convert, $output_lines, $return_var);

        if ($return_var === 0) {
            $images = glob($temp_dir . "page_*.png");
            
            if (!empty($images)) {
                foreach ($images as $img) {
                    $img_escaped = escapeshellarg($img);
                    
                    // 2️⃣ Enderezar (Deskew)
                    shell_exec("$magick_escaped mogrify -deskew 40% $img_escaped 2>&1");

                    // 3️⃣ Detectar rotación con Tesseract (OSD)
                    $cmd_tesseract = "$tesseract_escaped $img_escaped stdout --psm 0 -l osd 2>&1";
                    $output_tess = [];
                    exec($cmd_tesseract, $output_tess);
                    $tess_result = implode("\n", $output_tess);

                    if (preg_match('/Orientation in degrees: (\d+)/', $tess_result, $match)) {
                        $angle = intval($match[1]);
                        if ($angle > 0 && $angle < 360) {
                            $rotate = 360 - $angle;
                            shell_exec("$magick_escaped mogrify -rotate $rotate $img_escaped 2>&1");
                        }
                    }
                }

                // 4️⃣ Reconstruir PDF
                $temp_output_escaped = escapeshellarg($temp_output);
                $img_input_path = escapeshellarg($temp_dir . "page_*.png");
                
                // Reconstruimos el PDF
                $cmd_rebuild = "$magick_escaped -density 200 $img_input_path $temp_output_escaped 2>&1";
                shell_exec($cmd_rebuild);

                // Si se creó bien, reemplazamos el original
                if (file_exists($temp_output)) {
                    copy($temp_output, $ubicacion_fisica);
                    unlink($temp_output);
                }
            }
        }

        // ========================================================
        // 5️⃣ CONTAR PÁGINAS (MÉTODO ÓPTIMO SIN PHP_IMAGICK) 🚀
        // ========================================================
        // Usamos 'identify' de consola. Es mucho más ligero.
        // -format %n : Devuelve el número de páginas.
        
        $num_paginas = 1; // Valor por defecto
        
        // Ejecutamos el comando
        $cmd_count = "$magick_escaped identify -format %n $pdf_path_escaped";
        $output_count = shell_exec($cmd_count);

        // ImageMagick a veces devuelve un número por cada imagen procesada si hay ghostscript de fondo.
        // O devuelve directamente el número. Lo limpiamos:
        if ($output_count) {
            // A veces devuelve "111" (tres páginas) o líneas separadas.
            // La forma segura para PDF multipágina:
            $output_lines_count = [];
            exec($cmd_count, $output_lines_count);
            
            // Si devuelve muchas líneas, el conteo es el número de líneas
            if(count($output_lines_count) > 0) {
                // Opción A: Contar líneas (típico comportamiento de identify en PDFs)
                $num_paginas = count($output_lines_count);
                
                // Opción B: Si devuelve un solo número
                if ($num_paginas == 1 && is_numeric(trim($output_lines_count[0]))) {
                     $num_paginas = intval(trim($output_lines_count[0]));
                }
            }
        }

        // Limpieza de temporales
        if (is_dir($temp_dir)) {
            array_map('unlink', glob($temp_dir . "*"));
            rmdir($temp_dir);
        }

        // =============================
        // 💾 REGISTRO EN BD
        // =============================
        $ubicacion = "scantec";
        $version = "1.0";
        $fecha_indexado = date("Y-m-d");

        $registrar = $this->model->registrarExpediente(
            $id_proceso, $id_tipoDoc, $indice_01, $indice_02, $indice_03,
            $indice_04, $indice_05, $indice_06, 
            $num_paginas, // Variable obtenida optimizadamente
            $ruta_relativa, 
            $ubicacion, $version, $fecha_indexado
        );

        if ($registrar) {
            setAlert('success', "Procesado correctamente. Páginas detectadas: $num_paginas");
        } else {
            setAlert('error', "Error al registrar en BD.");
        }

        session_write_close();
        header("location: " . base_url() . "expedientes/upload_files");
        exit();
    }

    public function buscar()
    {
        $indice_05 = $_POST['indice_05'];
        $actualizar = $this->model->buscarExpediente($indice_05);
        if ($actualizar) {
            header("location: " . base_url() . "expedientes/buscador");
            die();
        }
    }

    public function modificar_nombre()
    {
        $id_expediente = $_POST['id_expediente'];
        $indice_01 = $_POST['indice_01'];
        $indice_02 = $_POST['indice_02'];
        $indice_05 = $_POST['indice_05'];
        //$nombre = $_POST['nombre_archivo'];
        $file = 'Expedientes/' . $_POST['nombre_archivo'] . '.pdf';
        $pathInfo = pathinfo($file);
        //$nombre_archivo = 'C:/xampp/htdocs/Expedientes/'.$_POST['nombre_archivo'].'.pdf';
        $archivo = 'Expedientes/' . $indice_01 . '_' . $indice_02 . '_' . $indice_05 . '.pdf';
        $nombre_nuevo = $indice_01 . '_' . $indice_02 . '_' . $indice_05;
        rename($file, $nombre_nuevo . $pathInfo['.pdf']);
        $actualizar = $this->model->renombrarExpediente($indice_01, $indice_02, $indice_05, $archivo, $id_expediente);
        if ($actualizar) {
            header("location: " . base_url() . "expedientes");
            die();
        }
    }
    /* public function modificar()
        {
            $id_expediente = $_POST['id_expediente'];
            $cantidad = $_POST['cantidad'];
            $autor = $_POST['autor'];
            $editorial = $_POST['editorial'];
            $anio_edicion = $_POST['anio_edicion'];
            $editorial = $_POST['editorial'];
            $materia = $_POST['materia'];
            $num_pagina = $_POST['num_pagina'];
            $descripcion = $_POST['descripcion'];
            $img = $_FILES['imagen'];
            $imgName = $img['name'];
            $nombreTemp = $img['tmp_name'];
            $fecha = md5(date("Y-m-d h:i:s")) . "_" . $imgName;
            $destino = "Assets/images/libros/".$fecha;
            $imgAntigua = $_POST['foto'];
            if ($imgName == null || $imgName == "") {
                $actualizar = $this->model->actualizarLibro($titulo, $cantidad, $autor ,$editorial, $anio_edicion, $materia, $num_pagina, $descripcion, $imgAntigua, $id_expediente);
            } else {
                $actualizar = $this->model->actualizarLibro($titulo, $cantidad, $autor ,$editorial, $anio_edicion, $materia, $num_pagina, $descripcion, $fecha, $id_expediente);
                if ($actualizar) {
                    move_uploaded_file($nombreTemp, $destino);
                    if ($imgAntigua != "default-avatar.png") {
                        unlink("Assets/images/libros/" . $imgAntigua);
                    }
                }
            }
            header("location: " . base_url() . "expedientes");
            die();
        } */
    public function eliminar()
    {
        if ($_SESSION['csrf_token'] !== $_POST['token'] || $_SESSION['csrf_expiration'] < time()) {
            // Redirigir y mostrar un mensaje de error en caso de token CSRF inválido o caducado
            header("Location: " . base_url() . "?error=csrf");
            die();
        }
        $id_expediente = $_POST['id_expediente'];
        $this->model->estadoExpediente('Inactivo', $id_expediente);
        header("location: " . base_url() . "expedientes");
        die();
    }
    public function reingresar()
    {
        $id_expediente = $_POST['id_expediente'];
        $this->model->estadoExpediente('Activo', $id_expediente);
        header("location: " . base_url() . "expedientes");
        die();
    }

    public function api_verExpediente()
    {
        header('Content-Type: application/json');

        if (isset($_GET['ruta']) && !empty($_GET['ruta']) && isset($_GET['id_expediente']) && !empty($_GET['id_expediente'])) {

            $ruta_original = RUTA_BASE . urldecode($_GET['ruta']);
            $id_expediente = intval($_GET['id_expediente']);

            if (file_exists($ruta_original)) {
                // ✅ Registrar visualización
                date_default_timezone_set('America/Asuncion');
                $fecha = date("Y-m-d H:i:s");
                $direccion_ip = $_SERVER["REMOTE_ADDR"] ?? "";
                $nombre_pc = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                $nombre_expediente = urldecode($_GET['ruta']);
                // ⚠️ Reemplazar con datos seguros si usás tokens
                $id_user = 1;
                $usuario = 'api_user';
                $this->model->registrar_visualizacion($id_user, $id_expediente, $usuario, $nombre_pc, $nombre_expediente, $direccion_ip, $fecha);
                // ✅ Mostrar PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($ruta_original) . '"');
                readfile($ruta_original);
                exit;
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'El archivo no existe o fue removido']);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros inválidos']);
            exit;
        }
    }

    public function ver_expediente2()
    {
        $ruta = $_GET['ruta'];
        $archivo = RUTA_BASE . $ruta;

        if (file_exists($archivo)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($archivo) . '"');
            readfile($archivo);
            exit;
        } else {
            echo "Archivo no encontrado";
        }
    }


    /*   public function ver_expediente() 
        {
            // Verificar si se ha seleccionado un archivo y un ID de expediente
                if (isset($_GET['ruta']) && !empty($_GET['ruta']) && isset($_GET['id_expediente']) && !empty($_GET['id_expediente'])) {
                    // Obtener la ruta del archivo PDF almacenada en la base de datos
                    $ruta_original = RUTA_BASE . urldecode($_GET['ruta']);
                    $id_expediente = isset($_GET['id_expediente']) ? intval($_GET['id_expediente']) : 0;

                    $ruta_original = RUTA_BASE . urldecode($_GET['ruta']);
                    echo "Ruta construida: $ruta_original";

                    if (file_exists($ruta_original)) { // Verificar si el archivo existe
                    // Registra la visualización del expediente
                    date_default_timezone_set('America/Asuncion');
                    $fecha = date("Y-m-d H:i:s");
                    $direccion_ip = $_SERVER["REMOTE_ADDR"] ?? "";
                    $nombre_pc = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                    $nombre_expediente = urldecode($_GET['ruta']);
                    $id_user = intval($_SESSION['id']);
                    $usuario= htmlspecialchars($_SESSION['usuario']);
                    $this->model->registrar_visualizacion($id_user, $id_expediente, $usuario, $nombre_pc, $nombre_expediente, $direccion_ip, $fecha);
                    // Mostrar el PDF directamente en el navegador
                    header('Content-Type: application/pdf');
                    readfile($ruta_original);
                    //exit;
                } else {
                    echo "<script>alert(El archivo no existe o ha sido removido de la ruta establecida!);window.history.back();</script>";
                }
            } else {
                echo "<script>alert('No se ha proporcionado la ruta del archivo o el ID del expediente!');window.history.back();</script>";
            }
        } */

    public function ver_expediente()
    {
        // Validar parámetros
        if (!isset($_GET['ruta'], $_GET['id_expediente']) || empty($_GET['ruta']) || empty($_GET['id_expediente'])) {
            die("Error: No se ha proporcionado la ruta del archivo o el ID del expediente.");
        }

        $archivo_relativo = urldecode($_GET['ruta']);
        $id_expediente = intval($_GET['id_expediente']);

        // Construir ruta completa
        $ruta_original = RUTA_BASE . $archivo_relativo;

        // Validar que la ruta esté dentro de la carpeta base
        $ruta_base_real = realpath(RUTA_BASE);
        $ruta_archivo_real = realpath($ruta_original);

        // Validar ruta segura
        if ($ruta_archivo_real === false || strpos($ruta_archivo_real, $ruta_base_real) !== 0) {
            setAlert('warning', 'Ruta inválida o fuera de la carpeta segura.');
            $return_url = $_GET['return_url'] ?? base_url() . 'expedientes/mostrar_registros';
            header('Location: ' . $return_url);
            exit;
        }
        // Verificar existencia del archivo
        if (!file_exists($ruta_archivo_real)) {
            setAlert('warning', 'El archivo no existe o ha sido removido.');
            $return_url = $_GET['return_url'] ?? base_url() . 'expedientes/mostrar_registros';
            header('Location: ' . $return_url);
            exit;
        }
        // Registrar visualización
        date_default_timezone_set('America/Asuncion');
        $fecha = date("Y-m-d H:i:s");
        $direccion_ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $nombre_pc = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $id_user = intval($_SESSION['id']);
        $usuario = htmlspecialchars($_SESSION['usuario']);
        $nombre_expediente = basename($ruta_archivo_real);

        $this->model->registrar_visualizacion(
            $id_user,
            $id_expediente,
            $usuario,
            $nombre_pc,
            $nombre_expediente,
            $direccion_ip,
            $fecha
        );
        // Mostrar PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombre_expediente . '"');
        header('Content-Length: ' . filesize($ruta_archivo_real));
        readfile($ruta_archivo_real);
        exit;
    }


    /*  public function expediente() 
        {   
            // Si se han seleccionado ambos archivos
            if (isset($_GET['ruta']) && !empty($_GET['ruta']) && isset($_GET['id_expediente']) && !empty($_GET['id_expediente'])) {
                // Obtener la ruta del archivo PDF almacenada en la base de datos
                $ruta_original = RUTA_BASE . urldecode($_GET['ruta']);
                //$archivo = urldecode($_GET['archivo']);
                $archivo = urldecode($_GET['ruta']);
                $id_expediente = isset($_GET['id_expediente']) ? intval($_GET['id_expediente']) : 0;

                if (file_exists($ruta_original)) { // Verificar si el archivo existe
                    // Crear nuevo objeto FPDI
                    $pdf = new FPDI();
                    // Establecer la fuente y el tamaño del texto para la marca de agua
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetTextColor(128, 128, 128); // Gris para la marca de agua
                    date_default_timezone_set('America/Asuncion');
                    // Obtener la fecha y hora actual
                    $fecha_hora = date('Y-m-d H:i:s');
                    $csrf_token = bin2hex(random_bytes(32)); // Generar token CSRF único
                    $usuario = $_SESSION['nombre'];

                    // Agregar la marca de agua en cada página del PDF
                    $pageCount = $pdf->setSourceFile($ruta_original);
                    for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                        $tplIdx = $pdf->importPage($pageNumber);
                        // Obtener el tamaño original de la página
                        $size = $pdf->getTemplateSize($pageNumber);
                        // Agregar una nueva página con el mismo formato que la original
                        $pdf->AddPage($size['orientation'], $size);
                        $pdf->useTemplate($tplIdx, null, null, null, null, true); // Escalar automáticamente al tamaño de la página original
                        $pdf->SetXY(10, 5); // Posición de la marca de agua
                        $pdf->Write(0, $csrf_token . "_" . $fecha_hora . "_Usuario:".$usuario); // Texto de la marca de agua
                    }

                    // Mostrar el PDF con la marca de agua en el navegador
                    ob_clean(); // Limpiar el buffer de salida
                    $pdf->Output('I', $archivo); 

                    date_default_timezone_set('America/Asuncion');
                    $fecha = date("Y-m-d H:i:s");
                    $direccion_ip = $_SERVER["REMOTE_ADDR"] ?? "";
                    $nombre_pc = gethostbyaddr($_SERVER['REMOTE_ADDR']);
                    $nombre_expediente = $archivo; 
                    $id_user = intval($_SESSION['id']);
                    $usuario= htmlspecialchars($_SESSION['usuario']);
                    $this->model->registrar_visualizacion($id_user, $id_expediente, $usuario, $nombre_pc, $nombre_expediente, $direccion_ip, $fecha);
                } else {
                    echo "<script>alert('El archivo no existe o ha sido removido de la ruta establecida!');window.history.back();</script>";
                }
            } else {
                echo "<script>alert('No se ha proporcionado la ruta del archivo o el ID del expediente!');window.history.back();</script>";
            }
        }  */
    public function expediente()
    {
        // Verificar que se recibieron los parámetros necesarios
        if (!isset($_GET['ruta']) || empty($_GET['ruta']) || !isset($_GET['id_expediente']) || empty($_GET['id_expediente'])) {
            echo "<script>alert('No se ha proporcionado la ruta del archivo o el ID del expediente!');window.history.back();</script>";
            exit;
        }

        $archivo = urldecode($_GET['ruta']);
        $id_expediente = intval($_GET['id_expediente']);

        // Definir carpeta base segura
        $ruta_base_segura = realpath(RUTA_BASE);
        $ruta_archivo_real = realpath(RUTA_BASE . $archivo);

        // Validar ruta segura
        if ($ruta_archivo_real === false || strpos($ruta_archivo_real, $ruta_base_segura) !== 0) {
            setAlert('warning', 'Ruta inválida o fuera de la carpeta segura.');
            $return_url = $_GET['return_url'] ?? base_url() . 'expedientes/mostrar_registros';
            header('Location: ' . $return_url);
            exit;
        }

        // Verificar existencia del archivo
        if (!file_exists($ruta_archivo_real)) {
            setAlert('warning', 'El archivo no existe o ha sido removido.');
            $return_url = $_GET['return_url'] ?? base_url() . 'expedientes/mostrar_registros';
            header('Location: ' . $return_url);
            exit;
        }

        // Crear PDF con marca de agua
        $pdf = new FPDI();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(128, 128, 128); // Gris
        date_default_timezone_set('America/Asuncion');
        $fecha_hora = date('Y-m-d H:i:s');
        $csrf_token = bin2hex(random_bytes(32));
        $usuario = $_SESSION['nombre'];

        $pageCount = $pdf->setSourceFile($ruta_archivo_real);
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $tplIdx = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($pageNumber);
            $pdf->AddPage($size['orientation'], $size);
            $pdf->useTemplate($tplIdx, null, null, null, null, true);
            $pdf->SetXY(10, 5);
            $pdf->Write(0, $csrf_token . "_" . $fecha_hora . "_Usuario:" . $usuario);
        }

        ob_clean();
        $pdf->Output('I', basename($archivo));

        // Registrar visualización
        $direccion_ip = $_SERVER["REMOTE_ADDR"] ?? "";
        $nombre_pc = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $nombre_expediente = basename($archivo);
        $id_user = intval($_SESSION['id']);
        $usuario = htmlspecialchars($_SESSION['usuario']);
        $this->model->registrar_visualizacion($id_user, $id_expediente, $usuario, $nombre_pc, $nombre_expediente, $direccion_ip, $fecha_hora);
    }


    /* public function expediente()
{
    if (isset($_GET['ruta']) && !empty($_GET['ruta']) && isset($_GET['id_expediente']) && !empty($_GET['id_expediente'])) {

        $ruta_original = RUTA_BASE . urldecode($_GET['ruta']);
        $archivo = urldecode($_GET['ruta']);
        $id_expediente = intval($_GET['id_expediente']);

        if (!file_exists($ruta_original)) {
            echo "<script>alert('El archivo no existe o ha sido removido de la ruta establecida!');window.history.back();</script>";
            exit;
        }

        $extension = strtolower(pathinfo($ruta_original, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'pdf':
                // === Tu código original para FPDI ===
                $pdf = new FPDI();
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetTextColor(128, 128, 128);
                date_default_timezone_set('America/Asuncion');
                $fecha_hora = date('Y-m-d H:i:s');
                $csrf_token = bin2hex(random_bytes(32));
                $usuario = $_SESSION['nombre'];

                $pageCount = $pdf->setSourceFile($ruta_original);
                for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                    $tplIdx = $pdf->importPage($pageNumber);
                    $size = $pdf->getTemplateSize($pageNumber);
                    $pdf->AddPage($size['orientation'], $size);
                    $pdf->useTemplate($tplIdx, null, null, null, null, true);
                    $pdf->SetXY(10, 5);
                    $pdf->Write(0, $csrf_token . "_" . $fecha_hora . "_Usuario:" . $usuario);
                }

                ob_clean();
                $pdf->Output('I', $archivo);
                break;

            case 'doc':
            case 'docx':
            case 'ppt':
            case 'pptx':
                // Redirige al visor de Google Docs
                $url_publica = base_url() . urldecode($_GET['ruta']);
                header("Location: https://docs.google.com/gview?url=" . urlencode($url_publica) . "&embedded=true");
                exit;

            default:
                echo "<script>alert('Formato no soportado para vista previa.');window.history.back();</script>";
                exit;
        }
    } else {
        echo "<script>alert('No se ha proporcionado la ruta del archivo o el ID del expediente!');window.history.back();</script>";
    }
} */

    // Método para obtener el token dinámicamente al firmar el archivo
    public function getApiToken()
    {
        $credentials = $this->model->getApiCredentials('API de Firma Digital');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $credentials['base_url'] . '/api/v1/Auth/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'username' => $credentials['api_key'],
                'password' => $credentials['api_secret']
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $responseData = json_decode($response, true);

        if (isset($responseData['token'])) {
            return $responseData['token']; // Token solo para esta operación
        } else {
            throw new Exception("No se pudo obtener el token.");
        }
    }

    // Método para firmar el archivo usando un webhook
    public function signFileWithWebhook($idExpediente, $fileToSign, $usernameCert, $passwordCert, $pinCert)
    {
        $token = $this->getApiToken();
        $webhookUrl = "http://181.94.210.128/webhooks/webhook.php"; // URL del webhook accesible
        $logUrl = "http://181.94.210.128/webhooks/webhook_log.php"; // URL para logs

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://datamex.com.py:11801/ltv/api/v1/Timestamp/signfilewebhook',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array("Authorization: Bearer $token"),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'level' => 'T',
                'username' => $usernameCert,
                'password' => $passwordCert,
                'pin' => $pinCert,
                'urlout' => $webhookUrl . "/result_" . basename($fileToSign),
                'urlback' => $logUrl . "/log_" . basename($fileToSign),
                'file_in' => new CURLFILE($fileToSign),
                'graficarfirma' => '1'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response, true);

        // Registrar firma en 'registros_firmas'
        if ($result['success']) {
            $this->model->registrarFirma($idExpediente, array(
                'tipo_firma' => 'avanzada', // O el tipo seleccionado
                'detalles_firma' => $result
            ));
        }

        return $result;
    }


    /*         public function obtener_metadatos()
        {
            // Asegúrate de sanitizar el parámetro de entrada
            $ruta_archivo = htmlspecialchars($_GET['ruta']);
            $ruta_completa = RUTA_BASE . $ruta_archivo;

            if (!file_exists($ruta_completa)) {
                echo json_encode(['success' => false, 'error' => 'El archivo no existe.']);
                return;
            }

            // Ruta del ExifTool
            $exiftool = "C:/Tools/exiftool.exe"; // Ajustar según tu instalación
            $comando = "$exiftool -j \"$ruta_completa\"";

            // Ejecutar comando
            $output = shell_exec($comando);

            if ($output) {
                $metadatos = json_decode($output, true);
                echo json_encode(['success' => true, 'data' => $metadatos[0]]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudieron obtener los metadatos.']);
            }
        } */

    public function metadatos()
    {
        // 1. Recibir ruta por POST (Tu JS usa FormData, que es POST)
        $ruta_relativa = $_POST['ruta'] ?? '';

        if (empty($ruta_relativa)) {
            echo "<div class='text-red-500 p-4'>Error: No se recibió la ruta del archivo.</div>";
            exit;
        }

        // 2. Construir y validar ruta física
        // RUTA_BASE debe terminar en '/' o asegurarse al concatenar
        $archivo_pdf = RUTA_BASE . $ruta_relativa;

        if (!file_exists($archivo_pdf)) {
            echo "<div class='text-red-500 p-4'>Error: El archivo no existe en el servidor.<br><small class='text-gray-400'>$archivo_pdf</small></div>";
            exit;
        }

        // 3. Datos Básicos (PHP Nativo - Siempre funcionan)
        $nombre = basename($archivo_pdf);
        $peso = round(filesize($archivo_pdf) / 1024, 2) . " KB";
        $fecha = date("d/m/Y H:i:s", filemtime($archivo_pdf));

        // 4. Intentar obtener Metadatos Avanzados con ExifTool
        $meta_avanzada = [];
        
        // Verificar si la constante y el ejecutable existen
        if (defined('RUTA_EXIFTOOL') && file_exists(RUTA_EXIFTOOL)) {
            // USAR escapeshellarg PARA RUTAS CON ESPACIOS
            $cmd_tool = escapeshellarg(RUTA_EXIFTOOL);
            $cmd_file = escapeshellarg($archivo_pdf);
            
            // Ejecutar comando (-j para JSON, -g para agrupar)
            // En Windows a veces se requiere llamar directo al exe sin comillas externas en todo el comando
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $comando = RUTA_EXIFTOOL . " -j " . $cmd_file;
            } else {
                $comando = "$cmd_tool -j $cmd_file";
            }

            $output = shell_exec($comando);

            if ($output) {
                $data = json_decode($output, true);
                if (!empty($data) && isset($data[0])) {
                    $meta_avanzada = $data[0];
                }
            }
        }

        // 5. Generar HTML para el Modal
        // Esto es lo que tu JavaScript espera recibir
        ?>
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                <h4 class="font-bold text-scantec-blue mb-2 text-sm uppercase">Información del Archivo</h4>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li class="flex justify-between"><span class="font-semibold">Nombre:</span> <span><?php echo $nombre; ?></span></li>
                    <li class="flex justify-between"><span class="font-semibold">Tamaño:</span> <span><?php echo $peso; ?></span></li>
                    <li class="flex justify-between"><span class="font-semibold">Modificado:</span> <span><?php echo $fecha; ?></span></li>
                </ul>
            </div>

            <?php if (!empty($meta_avanzada)): ?>
                <div class="border-t border-gray-100 pt-4">
                    <h4 class="font-bold text-gray-600 mb-3 text-sm uppercase">Metadatos Internos (PDF)</h4>
                    <div class="grid grid-cols-1 gap-2 text-sm">
                        <?php 
                        // Campos de interés a mostrar (para no mostrar basura técnica)
                        $campos_interes = [
                            'Title' => 'Título',
                            'Author' => 'Autor',
                            'Subject' => 'Asunto',
                            'Keywords' => 'Palabras Clave',
                            'Creator' => 'Creador',
                            'Producer' => 'Productor PDF',
                            'CreateDate' => 'Fecha Creación',
                            'PageCount' => 'Páginas',
                            'PDFVersion' => 'Versión PDF'
                        ];

                        foreach ($campos_interes as $key => $label): 
                            if (isset($meta_avanzada[$key])): 
                        ?>
                            <div class="flex flex-col border-b border-gray-50 pb-1">
                                <span class="text-xs text-gray-400 font-bold uppercase"><?php echo $label; ?></span>
                                <span class="text-gray-800"><?php echo $meta_avanzada[$key]; ?></span>
                            </div>
                        <?php 
                            endif; 
                        endforeach; 
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-2">
                    <p class="text-xs text-gray-400 italic">
                        No se pudieron leer metadatos internos (ExifTool no configurado o sin datos).
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function obtener_metadatos_pdf()
    {
        header('Content-Type: application/json');
        // Validar el parámetro 'ruta'
        if (!isset($_GET['ruta']) || empty($_GET['ruta'])) {
            echo json_encode(['success' => false, 'error' => 'La ruta del archivo PDF es requerida.']);
            exit;
        }

        // Sanitizar y verificar que el archivo existe
        $archivo_pdf = realpath(RUTA_BASE . htmlspecialchars($_GET['ruta']));
        if (!$archivo_pdf || !file_exists($archivo_pdf)) {
            echo json_encode(['success' => false, 'error' => 'El archivo no existe o la ruta es inválida.']);
            exit;
        }

        // Ejecutar ExifTool
        $exiftool = RUTA_EXIFTOOL;
        if (!file_exists($exiftool)) {
            echo json_encode(['success' => false, 'error' => 'ExifTool no se encuentra en la ruta especificada.']);
            exit;
        }

        $comando = escapeshellcmd("$exiftool -j \"$archivo_pdf\"");
        $output = shell_exec($comando);

        // Respuesta del comando
        if ($output) {
            $metadatos = json_decode($output, true);
            if (!empty($metadatos)) {
                echo json_encode(['success' => true, 'data' => $metadatos[0]]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se encontraron metadatos en el archivo.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Error ejecutando ExifTool.']);
        }
    }


    public function pdf_email()
    {
        if (ob_get_length())
            ob_end_clean();

        $mailController = new Configuracion();
        // Obtener los datos necesarios
        $expediente = $this->model->selectExpediente();
        $datosEmpresa = $this->model->selectDatos();
        // Datos del correo (pueden venir de la vista o de la base de datos)
        $destinatario = 'aldo.silva@printec.com.py';  // O $datos['email']
        $nombreDestinatario = 'Aldo Silva';  // O $datos['nombre']
        $asunto = 'Informe de Expedientes';
        $mensaje = 'Adjunto el informe de expedientes generado.';

        // Generar PDF con plantilla
        require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF($datosEmpresa, 'Reporte de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        $w = [45, 65, 40, 40, 50, 35, 35, 15, 15]; // Suma 340
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell($w[1], 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell($w[2], 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell($w[5], 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell($w[6], 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell($w[7], 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell($w[8], 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($expediente as $row) {
            $pdf->setX($margin);
            $pdf->Cell($w[0], 6, utf8_decode($row['nombre_tipoDoc']), 1, 0, 'L');
            $pdf->Cell($w[1], 6, utf8_decode($row['indice_01']), 1, 0, 'L');
            $pdf->Cell($w[2], 6, utf8_decode($row['indice_02']), 1, 0, 'C');
            $pdf->Cell($w[3], 6, utf8_decode($row['indice_03']), 1, 0, 'C');
            $pdf->Cell($w[4], 6, utf8_decode($row['indice_04']), 1, 0, 'C');
            $pdf->Cell($w[5], 6, utf8_decode($row['indice_05']), 1, 0, 'C');
            $pdf->Cell($w[6], 6, utf8_decode($row['fecha_indexado']), 1, 0, 'C');
            $pdf->Cell($w[7], 6, utf8_decode($row['paginas']), 1, 0, 'C');
            $pdf->Cell($w[8], 6, utf8_decode($row['version']), 1, 1, 'C');
        }

        // Guardar el PDF temporalmente
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . "/Expedientes_" . date('Y_m_d_His') . ".pdf";
        $pdf->Output($filePath, 'F');  // 'F' guarda el archivo en el servidor

        // Validar si el archivo se generó correctamente
        if (file_exists($filePath)) {
            // Enviar correo con los parámetros necesarios
            if ($mailController->sendEmailWithAttachment($filePath, [$destinatario => $nombreDestinatario], $asunto, $mensaje)) {
                echo 'Correo enviado correctamente.';
            } else {
                echo 'Error al enviar correo: ';
            }
            // Eliminar el archivo después del envío
            unlink($filePath);
        } else {
            echo 'Error al generar el PDF.';
        }
    }

    public function pdf_emails()
    {
        if (ob_get_length())
            ob_end_clean();

        $desde = $_POST['desde'];
        $dias = $_POST['dias'];
        $mailController = new Configuracion();
        // Obtener los datos necesarios
        $expediente = $this->model->reporteExpedientesFecha($desde, $dias);
        $datosEmpresa = $this->model->selectDatos();
        // Obtener los destinatarios y nombres del formulario
        $emails = isset($_POST['emails']) ? $_POST['emails'] : '';
        $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';

        // Separar los valores por coma
        $listaEmails = array_map('trim', explode(',', $emails));
        $listaNombres = array_map('trim', explode(',', $nombres));
        // Crear array asociativo para los destinatarios
        $destinatarios = [];
        // Recorrer los correos y asignar nombres
        foreach ($listaEmails as $index => $email) {
            // Si hay un nombre correspondiente, úsalo. Si no, usa el correo como nombre.
            $nombre = isset($listaNombres[$index]) && !empty($listaNombres[$index])
                ? $listaNombres[$index]
                : $email;

            $destinatarios[$email] = $nombre;
        }

        // Validar que haya al menos un correo válido
        if (empty($destinatarios)) {
            echo 'Error: No se ingresaron correos válidos.';
            return;
        }
        $asunto = 'Informe de Archivos';
        $mensaje = 'Se adjunta el informe de archivos generado';

        // Generar PDF con plantilla
        require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF($datosEmpresa, 'Reporte de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        $w = [45, 65, 40, 40, 50, 35, 35, 15, 15];
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell($w[1], 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell($w[2], 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell($w[5], 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell($w[6], 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell($w[7], 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell($w[8], 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($expediente as $row) {
            $pdf->setX($margin);
            $pdf->Cell($w[0], 6, utf8_decode($row['nombre_tipoDoc']), 1, 0, 'L');
            $pdf->Cell($w[1], 6, utf8_decode($row['indice_01']), 1, 0, 'L');
            $pdf->Cell($w[2], 6, utf8_decode($row['indice_02']), 1, 0, 'C');
            $pdf->Cell($w[3], 6, utf8_decode($row['indice_03']), 1, 0, 'C');
            $pdf->Cell($w[4], 6, utf8_decode($row['indice_04']), 1, 0, 'C');
            $pdf->Cell($w[5], 6, utf8_decode($row['indice_05']), 1, 0, 'C');
            $pdf->Cell($w[6], 6, utf8_decode($row['fecha_indexado']), 1, 0, 'C');
            $pdf->Cell($w[7], 6, utf8_decode($row['paginas']), 1, 0, 'C');
            $pdf->Cell($w[8], 6, utf8_decode($row['version']), 1, 1, 'C');
        }

        // Guardar el PDF temporalmente
        $tempDir = sys_get_temp_dir();
        $filePath = $tempDir . "/Expedientes_" . date('Y_m_d_His') . ".pdf";
        $pdf->Output($filePath, 'F');  // 'F' guarda el archivo en el servidor

        // Validar si el archivo se generó correctamente
        // Enviar el correo
        if (file_exists($filePath)) {
            $mailController->sendEmailWithAttachment($filePath, $destinatarios, $asunto, $mensaje);
            unlink($filePath);
        } else {
            echo 'Error al generar el PDF.';
        }
    }

    /* public function pdf()
        {
            $expediente = $this->model->selectExpediente();
            $datos = $this->model->selectDatos();
            require_once 'Libraries/pdf/fpdf.php';
            $pdf = new FPDF('L', 'mm', 'LEGAL');
            $pdf->AddPage();
            // Agregar metadatos
            $pdf->SetTitle('EXPEDIENTES');
            $pdf->SetAuthor('SCANTEC - '.$_SESSION['usuario']);
            $pdf->SetCreator('SCANTEC');
            $pdf->SetMargins(10, 5, 5);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->setX(60);
            $pdf->Cell(5, 5, utf8_decode("Proyecto: "), 0, 0, 'L');
            $pdf->Cell(52, 5, utf8_decode($datos['nombre']), 0, 1, 'R');
            $pdf->image(base_url() .'Assets/img/icoScantec2.png',10,7,33);
            $pdf->image(base_url() . "Assets/img/logo_empresa.jpg", 320, 5, 20, 20);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->setX(20);
            $pdf->Ln();
            $pdf->Cell(20, 5, utf8_decode("Teléfono: "), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 5, $datos['telefono'], 0, 1, 'L');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 5, utf8_decode("Dirección: "), 0, 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 5, utf8_decode($datos['direccion']), 0, 1, 'L');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(20, 5, "Correo: ", 0, 0, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(20, 5, utf8_decode($datos['correo']), 0, 1, 'L');
            $pdf->Ln();
            $pdf->setX(5);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->SetTextColor(0, 0, 0);            
            $pdf->Cell(345, 8, "EXPEDIENTES", 1, 1, 'C', 1);
            $pdf->Ln();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(192, 192, 192);
            $pdf->setX(5);
            $pdf->Cell(45, 6, utf8_decode('Tipo documento'), 1, 0, 'C',1);
            $pdf->Cell(65, 6, utf8_decode('Indice 1'), 1, 0, 'C',1);
            $pdf->Cell(40, 6, 'Indice 2', 1, 0, 'C',1);
            $pdf->Cell(40, 6, 'Indice 3', 1, 0, 'C',1);
            $pdf->Cell(50, 6, utf8_decode('Indice 4'), 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Indice 5', 1, 0, 'C',1);
            $pdf->Cell(35, 6, 'Fecha carga', 1, 0, 'C',1);
            $pdf->Cell(15, 6, utf8_decode('Páginas'), 1, 0, 'C',1);
            $pdf->Cell(15, 6, utf8_decode('Versión'), 1, 1, 'C',1);

            $pdf->SetFont('Arial', '', 7);
            foreach ($expediente as $row) {
                $pdf->setX(5);
                $pdf->Cell(45, 6, utf8_decode($row['nombre_tipoDoc']), 1, 0, 'C');
                $pdf->Cell(65, 6, utf8_decode($row['indice_01']), 1, 0, 'L');
                $pdf->Cell(40, 6, utf8_decode($row['indice_02']), 1, 0,'C');
                $pdf->Cell(40, 6, utf8_decode($row['indice_03']), 1, 0, 'C');
                $pdf->Cell(50, 6, utf8_decode($row['indice_04']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['indice_05']), 1, 0, 'C');
                $pdf->Cell(35, 6, utf8_decode($row['fecha_indexado']), 1, 0, 'C');
                $pdf->Cell(15, 6, utf8_decode($row['paginas']), 1, 0, 'C');
                $pdf->Cell(15, 6, utf8_decode($row['version']), 1, 1, 'C');
            }
            $pdf->SetXY(160,273);
            // Arial italic 8
            $pdf->SetFont('Arial','B',8);
            // Número de página
            $pdf->Cell(0,0,utf8_decode('Página ').$pdf->PageNo().'',0,0,'R');
            $pdf->Output("Expedientes_" . date('Y_m_d_H_i_s').".pdf", "I");
        } */

    public function pdf()
    {
        if (ob_get_length())
            ob_end_clean();

        $expediente = $this->model->selectExpediente();
        $datosEmpresa = $this->model->selectDatos();

        require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF($datosEmpresa, 'Reporte General de Expedientes', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        $w = [45, 65, 40, 40, 50, 35, 35, 15, 15]; // Suma 340
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell($w[1], 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell($w[2], 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell($w[5], 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell($w[6], 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell($w[7], 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell($w[8], 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($expediente as $row) {
            $pdf->setX($margin);
            $pdf->Cell($w[0], 6, utf8_decode($row['nombre_tipoDoc']), 1, 0, 'L');
            $pdf->Cell($w[1], 6, utf8_decode($row['indice_01']), 1, 0, 'L');
            $pdf->Cell($w[2], 6, utf8_decode($row['indice_02']), 1, 0, 'C');
            $pdf->Cell($w[3], 6, utf8_decode($row['indice_03']), 1, 0, 'C');
            $pdf->Cell($w[4], 6, utf8_decode($row['indice_04']), 1, 0, 'C');
            $pdf->Cell($w[5], 6, utf8_decode($row['indice_05']), 1, 0, 'C');
            $pdf->Cell($w[6], 6, utf8_decode($row['fecha_indexado']), 1, 0, 'C');
            $pdf->Cell($w[7], 6, utf8_decode($row['paginas']), 1, 0, 'C');
            $pdf->Cell($w[8], 6, utf8_decode($row['version']), 1, 1, 'C');
        }

        $pdf->Output("Expedientes_" . date('Y_m_d_H_i_s') . ".pdf", "I");
    }

    public function pdf_filtroFecha()
    {
        if (ob_get_length())
            ob_end_clean();

        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $expediente = $this->model->reporteExpedientesFecha($desde, $hasta);
        $datosEmpresa = $this->model->selectDatos();

        require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF($datosEmpresa, 'Expedientes por Fecha de Carga', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        $w = [45, 65, 40, 40, 50, 35, 35, 15, 15];
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('Tipo documento'), 1, 0, 'C', 1);
        $pdf->Cell($w[1], 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell($w[2], 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell($w[5], 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell($w[6], 7, 'Fecha carga', 1, 0, 'C', 1);
        $pdf->Cell($w[7], 7, utf8_decode('Páginas'), 1, 0, 'C', 1);
        $pdf->Cell($w[8], 7, utf8_decode('Versión'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($expediente as $row) {
            $pdf->setX($margin);
            $pdf->Cell($w[0], 6, utf8_decode($row['nombre_tipoDoc']), 1, 0, 'L');
            $pdf->Cell($w[1], 6, utf8_decode($row['indice_01']), 1, 0, 'L');
            $pdf->Cell($w[2], 6, utf8_decode($row['indice_02']), 1, 0, 'C');
            $pdf->Cell($w[3], 6, utf8_decode($row['indice_03']), 1, 0, 'C');
            $pdf->Cell($w[4], 6, utf8_decode($row['indice_04']), 1, 0, 'C');
            $pdf->Cell($w[5], 6, utf8_decode($row['indice_05']), 1, 0, 'C');
            $pdf->Cell($w[6], 6, utf8_decode($row['fecha_indexado']), 1, 0, 'C');
            $pdf->Cell($w[7], 6, utf8_decode($row['paginas']), 1, 0, 'C');
            $pdf->Cell($w[8], 6, utf8_decode($row['version']), 1, 1, 'C');
        }

        $pdf->Output("Expedientes_" . date('Y_m_d_H_i_s') . ".pdf", "I");
    }

    public function pdf_filtro()
    {
        if (ob_get_length())
            ob_end_clean();

        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $expediente = $this->model->reporteExpedientes($desde, $hasta);
        $datosEmpresa = $this->model->selectDatos();

        require_once __DIR__ . '/../Helpers/ReportTemplatePDF.php';
        $pdf = new ReportTemplatePDF($datosEmpresa, 'Expedientes por Rango de Índice', 'L', 'LEGAL');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetTextColor(0, 0, 0);

        $w = [15, 35, 35, 35, 35, 168, 15]; // Suma 338
        $margin = ($pdf->GetPageWidth() - array_sum($w)) / 2;
        $pdf->setX($margin);

        $pdf->Cell($w[0], 7, utf8_decode('N°'), 1, 0, 'C', 1);
        $pdf->Cell($w[1], 7, utf8_decode('Indice 1'), 1, 0, 'C', 1);
        $pdf->Cell($w[2], 7, 'Indice 2', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 7, 'Indice 3', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 7, utf8_decode('Indice 4'), 1, 0, 'C', 1);
        $pdf->Cell($w[5], 7, 'Indice 5', 1, 0, 'C', 1);
        $pdf->Cell($w[6], 7, utf8_decode('Páginas'), 1, 1, 'C', 1);

        $pdf->SetFont('Arial', '', 8);
        foreach ($expediente as $row) {
            $pdf->setX($margin);
            $pdf->Cell($w[0], 6, $row['id_expediente'], 1, 0, 'C');
            $pdf->Cell($w[1], 6, utf8_decode($row['indice_01']), 1, 0, 'C');
            $pdf->Cell($w[2], 6, utf8_decode($row['indice_02']), 1, 0, 'C');
            $pdf->Cell($w[3], 6, utf8_decode($row['indice_03']), 1, 0, 'C');
            $pdf->Cell($w[4], 6, utf8_decode($row['indice_04']), 1, 0, 'C');
            $pdf->Cell($w[5], 6, utf8_decode($row['indice_05']), 1, 0, 'L');
            $pdf->Cell($w[6], 6, utf8_decode($row['paginas']), 1, 1, 'C');
        }

        $pdf->Output("Expedientes.pdf", "I");
    }

    public function excel()
    {
        if (ob_get_length())
            ob_end_clean();
        require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $datosEmpresa = $this->model->selectDatos();
        $expediente = $this->model->selectExpediente();

        $nombreEmpresa = $datosEmpresa['nombre'];
        $excel = new ReportTemplateExcel('REPORTE GENERAL DE EXPEDIENTES', $nombreEmpresa);
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '878787'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $headerRow = 4;
        $headers = ['TIPO DOCUMENTO', 'INDICE 01', 'INDICE 02', 'INDICE 03', 'INDICE 04', 'INDICE 05', 'PAGINAS', 'UBICACION', 'FECHA CARGA', 'VERSION'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:J$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;
        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["nombre_tipoDoc"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('C' . $dataRow, $value["indice_02"]);
            $sheet->setCellValue('D' . $dataRow, $value['indice_03']);
            $sheet->setCellValue('E' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('F' . $dataRow, $value["indice_05"]);
            $sheet->setCellValue('G' . $dataRow, $value["paginas"]);
            $sheet->setCellValue('H' . $dataRow, $value["ubicacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["fecha_indexado"]);
            $sheet->setCellValue('J' . $dataRow, $value["version"]);
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $nombreArchivo = 'Expedientes_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function excel_filtroFecha()
    {
        if (ob_get_length())
            ob_end_clean();
        require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        $desde = $_POST['desde'];
        $hasta = $_POST['hasta'];
        $datosEmpresa = $this->model->selectDatos();
        $expediente = $this->model->reporteExpedientesFecha($desde, $hasta);

        $nombreEmpresa = $datosEmpresa['nombre'];
        $excel = new ReportTemplateExcel('EXPEDIENTES POR FECHA', $nombreEmpresa);
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '878787'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $headerRow = 4;
        $headers = ['TIPO DOCUMENTO', 'INDICE 01', 'INDICE 02', 'INDICE 03', 'INDICE 04', 'INDICE 05', 'PAGINAS', 'UBICACION', 'FECHA CARGA', 'VERSION'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:J$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;
        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["nombre_tipoDoc"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('C' . $dataRow, $value["indice_02"]);
            $sheet->setCellValue('D' . $dataRow, $value['indice_03']);
            $sheet->setCellValue('E' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('F' . $dataRow, $value["indice_05"]);
            $sheet->setCellValue('G' . $dataRow, $value["paginas"]);
            $sheet->setCellValue('H' . $dataRow, $value["ubicacion"]);
            $sheet->setCellValue('I' . $dataRow, $value["fecha_indexado"]);
            $sheet->setCellValue('J' . $dataRow, $value["version"]);
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $nombreArchivo = 'Expedientes_Por_Fecha_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }

    public function excel_filtroDuplic()
    {
        if (ob_get_length())
            ob_end_clean();
        require_once __DIR__ . '/../Helpers/ReportTemplateExcel.php';
        date_default_timezone_set('America/Asuncion');

        // Obtener los expedientes duplicados
        $datosEmpresa = $this->model->selectDatos();
        $expediente = $this->model->reporteExpedientesDuplic();

        $nombreEmpresa = $datosEmpresa['nombre'];
        $excel = new ReportTemplateExcel('EXPEDIENTES DUPLICADOS', $nombreEmpresa);
        $sheet = $excel->getSheet();

        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '878787'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $headerRow = 4;
        $headers = ['INDICE 01', 'INDICE 04', 'CANTIDAD'];
        $col = 'A';
        foreach ($headers as $txt) {
            $sheet->setCellValue($col . $headerRow, $txt);
            $col++;
        }
        $sheet->getStyle("A$headerRow:C$headerRow")->applyFromArray($headerStyle);

        $contentStyle = ['font' => ['size' => 9], 'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $dataRow = $headerRow + 1;
        foreach ($expediente as $value) {
            $sheet->setCellValue('A' . $dataRow, $value["indice_01"]);
            $sheet->setCellValue('B' . $dataRow, $value["indice_04"]);
            $sheet->setCellValue('C' . $dataRow, $value["cantidad"]);
            $sheet->getStyle('A' . $dataRow . ':C' . $dataRow)->applyFromArray($contentStyle);
            $dataRow++;
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $nombreArchivo = 'Expedientes_Duplicados_' . date('Y_m_d_His');
        $excel->output($nombreArchivo);
    }
}