<?php

declare(strict_types=1);

require_once __DIR__ . '/../Config/DB_Config.php';

function dumpEscapeIdentifier(string $value): string
{
    return '`' . str_replace('`', '``', $value) . '`';
}

function dumpSqlValue(mysqli $mysqli, $value): string
{
    if ($value === null) {
        return 'NULL';
    }

    if (is_bool($value)) {
        return $value ? '1' : '0';
    }

    if (is_int($value) || is_float($value)) {
        return (string)$value;
    }

    if (is_numeric($value) && preg_match('/^-?\d+(\.\d+)?$/', (string)$value)) {
        return (string)$value;
    }

    return "'" . $mysqli->real_escape_string((string)$value) . "'";
}

function fetchSingleCreate(mysqli $mysqli, string $sql, string $key): ?string
{
    $result = $mysqli->query($sql);
    if (!$result instanceof mysqli_result) {
        return null;
    }

    $row = $result->fetch_assoc();
    $result->free();

    return isset($row[$key]) ? (string)$row[$key] : null;
}

$database = $argv[1] ?? BD_DEFAULT;
$database = preg_replace('/[^A-Za-z0-9_]/', '', (string)$database);
if ($database === '') {
    fwrite(STDERR, "Base de datos invalida.\n");
    exit(1);
}

$connection = obtenerConexionBaseSeleccionada($database, BD_DEFAULT);
$host = (string)($connection['host'] ?? DB_HOST_DEFAULT);
$port = intval($connection['port'] ?? DB_PORT_DEFAULT);
$user = (string)($connection['user'] ?? DB_APP_USER_DEFAULT);
$password = (string)($connection['password'] ?? DB_APP_PASS_DEFAULT);

$timestamp = date('Y-m-d_H-i-s');
$defaultOutput = __DIR__ . '/../BD/' . $database . '_dump_' . $timestamp . '.sql';
$outputPath = $argv[2] ?? $defaultOutput;

$mysqli = @new mysqli($host, $user, $password, $database, $port);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "Error de conexion: " . $mysqli->connect_error . "\n");
    exit(1);
}

$mysqli->set_charset('utf8mb4');

$sql = [];
$sql[] = '-- Respaldo generado automaticamente';
$sql[] = '-- Fecha: ' . date('Y-m-d H:i:s');
$sql[] = '-- Base de datos: ' . $database;
$sql[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
$sql[] = 'SET time_zone = "+00:00";';
$sql[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$sql[] = 'SET NAMES utf8mb4;';
$sql[] = '';
$sql[] = 'CREATE DATABASE IF NOT EXISTS ' . dumpEscapeIdentifier($database) . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;';
$sql[] = 'USE ' . dumpEscapeIdentifier($database) . ';';
$sql[] = '';

$tables = [];
$views = [];
$resultTables = $mysqli->query('SHOW FULL TABLES');
if ($resultTables instanceof mysqli_result) {
    while ($row = $resultTables->fetch_row()) {
        $name = (string)($row[0] ?? '');
        $type = strtoupper((string)($row[1] ?? ''));
        if ($name === '') {
            continue;
        }
        if ($type === 'VIEW') {
            $views[] = $name;
        } else {
            $tables[] = $name;
        }
    }
    $resultTables->free();
}

foreach ($tables as $table) {
    $createTable = fetchSingleCreate(
        $mysqli,
        'SHOW CREATE TABLE ' . dumpEscapeIdentifier($table),
        'Create Table'
    );

    if ($createTable === null) {
        continue;
    }

    $sql[] = '-- --------------------------------------------------------';
    $sql[] = '-- Estructura de tabla para ' . $table;
    $sql[] = 'DROP TABLE IF EXISTS ' . dumpEscapeIdentifier($table) . ';';
    $sql[] = $createTable . ';';
    $sql[] = '';

    $rowsResult = $mysqli->query('SELECT * FROM ' . dumpEscapeIdentifier($table));
    if (!$rowsResult instanceof mysqli_result) {
        continue;
    }

    if ($rowsResult->num_rows > 0) {
        $fields = [];
        foreach ($rowsResult->fetch_fields() as $field) {
            $fields[] = dumpEscapeIdentifier($field->name);
        }
        $fieldList = implode(', ', $fields);

        while ($row = $rowsResult->fetch_assoc()) {
            $values = [];
            foreach ($row as $value) {
                $values[] = dumpSqlValue($mysqli, $value);
            }
            $sql[] = 'INSERT INTO ' . dumpEscapeIdentifier($table) . ' (' . $fieldList . ') VALUES (' . implode(', ', $values) . ');';
        }
        $sql[] = '';
    }

    $rowsResult->free();
}

foreach ($views as $view) {
    $createView = fetchSingleCreate(
        $mysqli,
        'SHOW CREATE VIEW ' . dumpEscapeIdentifier($view),
        'Create View'
    );

    if ($createView === null) {
        continue;
    }

    $sql[] = '-- --------------------------------------------------------';
    $sql[] = '-- Estructura de vista para ' . $view;
    $sql[] = 'DROP VIEW IF EXISTS ' . dumpEscapeIdentifier($view) . ';';
    $sql[] = $createView . ';';
    $sql[] = '';
}

$triggers = $mysqli->query('SHOW TRIGGERS');
if ($triggers instanceof mysqli_result) {
    while ($trigger = $triggers->fetch_assoc()) {
        $triggerName = (string)($trigger['Trigger'] ?? '');
        if ($triggerName === '') {
            continue;
        }
        $createTrigger = fetchSingleCreate(
            $mysqli,
            'SHOW CREATE TRIGGER ' . dumpEscapeIdentifier($triggerName),
            'SQL Original Statement'
        );
        $timing = (string)($trigger['Timing'] ?? '');
        $event = (string)($trigger['Event'] ?? '');
        $table = (string)($trigger['Table'] ?? '');
        if ($createTrigger === null || $timing === '' || $event === '' || $table === '') {
            continue;
        }
        $sql[] = '-- --------------------------------------------------------';
        $sql[] = '-- Trigger ' . $triggerName;
        $sql[] = 'DROP TRIGGER IF EXISTS ' . dumpEscapeIdentifier($triggerName) . ';';
        $sql[] = 'CREATE TRIGGER ' . dumpEscapeIdentifier($triggerName)
            . ' ' . $timing
            . ' ' . $event
            . ' ON ' . dumpEscapeIdentifier($table)
            . ' FOR EACH ROW '
            . $createTrigger . ';';
        $sql[] = '';
    }
    $triggers->free();
}

$sql[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$sql[] = '';

$content = implode(PHP_EOL, $sql);
if (@file_put_contents($outputPath, $content) === false) {
    fwrite(STDERR, "No se pudo escribir el archivo: " . $outputPath . "\n");
    $mysqli->close();
    exit(1);
}

$mysqli->close();

echo $outputPath . PHP_EOL;
