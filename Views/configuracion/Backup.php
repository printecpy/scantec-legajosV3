<?php 
require("../../Config/Config.php");

date_default_timezone_set('America/Asuncion');

class SGBD {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . HOST . ";dbname=" . BD;
            try {
                self::$pdo = new PDO($dsn, DB_USER, PASS, [
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die('Error de conexión: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    public static function sql($query) {
        $pdo = self::getConnection();
        try {
            $stmt = $pdo->query($query);
            return $stmt;
        } catch (PDOException $e) {
            die('Falló la consulta: ' . $e->getMessage());
        }
    }

    public static function limpiarCadena($valor) {
        $valor = addslashes($valor);
        $valor = str_ireplace(["<script>", "</script>", "SELECT * FROM", "DELETE FROM", "UPDATE", "INSERT INTO", "DROP TABLE", "TRUNCATE TABLE", "--", "^", "[", "]", "\\", "="], "", $valor);
        return $valor;
    }
}

$day = date("d");
$mont = date("m");
$year = date("Y");
$hora = date("H-i-s");
$fecha = $year . '_' . $mont . '_' . $day;
$DataBASE = BD."_".$fecha . "_(" . $hora . "_hrs).sql";
$tables = [];
$views = [];
$error = 0;

try {
    $result = SGBD::sql('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $result = SGBD::sql('SHOW FULL TABLES WHERE Table_type = "VIEW"');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $views[] = $row[0];
    }

    $sql = 'SET FOREIGN_KEY_CHECKS=0;' . "\n\n";
    $sql .= 'CREATE DATABASE IF NOT EXISTS ' . BD . ";\n\n";
    $sql .= 'USE ' . BD . ";\n\n";

    foreach ($tables as $table) {
        $result = SGBD::sql('SELECT * FROM ' . $table);
        if ($result) {
            $numFields = $result->columnCount();
            $row2 = SGBD::sql('SHOW CREATE TABLE ' . $table)->fetch(PDO::FETCH_NUM);
            $sql .= "\n\n" . $row2[1] . ";\n\n";

            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $sql .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $numFields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    $sql .= isset($row[$j]) ? '"' . $row[$j] . '"' : '""';
                    if ($j < ($numFields - 1)) {
                        $sql .= ',';
                    }
                }
                $sql .= ");\n";
            }
            $sql .= "\n\n\n";
        } else {
            $error = 1;
        }
    }

    foreach ($views as $view) {
        $row2 = SGBD::sql('SHOW CREATE VIEW ' . $view)->fetch(PDO::FETCH_NUM);
        $sql .= "\n\n" . $row2[1] . ";\n\n";
    }

    $triggersResult = SGBD::sql('SHOW TRIGGERS');
    if ($triggersResult) {
        $sql .= "\n\n-- TRIGGERS\n\n";
        while ($trigger = $triggersResult->fetch(PDO::FETCH_ASSOC)) {
            $sql .= 'DELIMITER ;;' . "\n";
            $sql .= 'CREATE TRIGGER `' . $trigger['Trigger'] . '` ' . $trigger['Timing'] . ' ' . $trigger['Event'] . ' ON `' . $trigger['Table'] . '` FOR EACH ROW ' . $trigger['Statement'] . ";;\n";
            $sql .= 'DELIMITER ;' . "\n\n";
        }
    }

    if ($error == 1) {
        // Error al crear la copia de seguridad
        echo "<script>alert('Error al crear la copia de seguridad');window.history.back();</script>";
    } else {
        // Copia de seguridad exitosa
        chmod(BACKUP_PATH, 0777);
        $sql .= 'SET FOREIGN_KEY_CHECKS=1;';
        $handle = fopen(BACKUP_PATH . $DataBASE, 'w+');
        if (fwrite($handle, $sql)) {
            fclose($handle);
            // Mostrar mensaje de éxito usando un toast
            echo "<script>alert('Copia de seguridad de la BD realizada con éxito');window.history.back();</script>";
        } else {
            // Mostrar mensaje de error en caso de fallo al escribir el archivo de copia de seguridad
            echo "<script>alert('Ocurrió un error inesperado al escribir el
            archivo de copia de seguridad');window.history.back();</script>";
        }
    }
}catch (Exception $e) {
    echo "<script>alert('Ocurrio un error=');window.history.back();</script>". $e->getMessage();
}

