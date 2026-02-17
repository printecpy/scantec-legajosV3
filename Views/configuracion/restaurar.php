<?php 
require '../../Config/Config.php';

$dbName     = 'prueba';
$filePath   = $_POST['file'];

    /* if ($filePath === false) { 
        echo ("No se pudo abrir el archivo SQL cargado");
      } */
      // Conexión a la base de datos
      try {
          $pdo = new PDO(
            "mysql:host=".HOST.";dbname=prueba;".CHARSET,
            DB_USER, PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
          );
        } catch (Exception $ex) { exit($ex->getMessage()); }

    // Temporary variable, used to store current query
    $templine = '';
    
    // Read in entire file
    $lines = file($filePath);
    
    $error = '';
    
    // Loop through each line
    foreach ($lines as $line){
        // Skip it if it's a comment
        if(substr($line, 0, 2) == '--' || $line == ''){
            continue;
        }
        
        // Add this line to the current segment
        $templine .= $line;
        
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';'){
            // Perform the query
            if(!$pdo->query($templine)){
                $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $pdo->$error . '<br /><br />';
            }
            
            // Reset temp variable to empty
            $templine = '';
        }
    }
    return !empty($error)?$error:true;
