<?php
/**
 * Modelo para leer personas desde una base externa.
 *
 * Para legajos solo necesitamos:
 * - id
 * - ci
 * - nombre completo (armado desde apellido + nombre si hace falta)
 * - nro_solicitud opcional
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'ConexionUsuarios.php';

class UsuariosDatosModel extends MysqlUsuarios
{
    private array $mapaCampos;

    public function __construct()
    {
        parent::__construct();

        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];
        $this->mapaCampos = [
            'tabla' => $this->sanitizarIdentificador((string)($config['db_usuarios_table'] ?? 'usuarios_datos'), 'usuarios_datos'),
            'id' => $this->sanitizarIdentificador((string)($config['db_usuarios_field_id'] ?? 'id'), 'id'),
            'nombre' => $this->sanitizarIdentificadorOpcional((string)($config['db_usuarios_field_nombre'] ?? 'nombre')),
            'apellido' => $this->sanitizarIdentificadorOpcional((string)($config['db_usuarios_field_apellido'] ?? 'apellido')),
            'nombre_completo' => $this->sanitizarIdentificadorOpcional((string)($config['db_usuarios_field_nombre_completo'] ?? '')),
            'ci' => $this->sanitizarIdentificador((string)($config['db_usuarios_field_ci'] ?? 'nro_cedula'), 'nro_cedula'),
            'solicitud' => $this->sanitizarIdentificadorOpcional((string)($config['db_usuarios_field_solicitud'] ?? 'nro_solicitud')),
        ];
    }

    private function sanitizarIdentificador(string $valor, string $fallback): string
    {
        $valor = preg_replace('/[^A-Za-z0-9_]/', '', trim($valor));
        return $valor !== '' ? $valor : $fallback;
    }

    private function sanitizarIdentificadorOpcional(string $valor): string
    {
        return preg_replace('/[^A-Za-z0-9_]/', '', trim($valor));
    }

    private function campo(string $clave): string
    {
        return $this->mapaCampos[$clave] ?? '';
    }

    private function tabla(): string
    {
        return $this->campo('tabla');
    }

    private function sqlColumnaOpcional(string $clave, string $alias): string
    {
        $campo = $this->campo($clave);
        return $campo !== '' ? "`{$campo}` AS {$alias}" : "NULL AS {$alias}";
    }

    private function sqlBaseSelect(): string
    {
        $campoNombreCompleto = $this->campo('nombre_completo');
        $sqlNombreCompleto = $campoNombreCompleto !== ''
            ? "`{$campoNombreCompleto}` AS nombre_completo"
            : "TRIM(CONCAT_WS(' ', `{$this->campo('apellido')}`, `{$this->campo('nombre')}`)) AS nombre_completo";

        return "SELECT
                    `{$this->campo('id')}` AS id,
                    {$sqlNombreCompleto},
                    `{$this->campo('ci')}` AS nro_cedula,
                    {$this->sqlColumnaOpcional('solicitud', 'nro_solicitud')}
                FROM `{$this->tabla()}`";
    }

    private function normalizarFila(array $fila): array
    {
        return [
            'id' => intval($fila['id'] ?? 0),
            'nombre_completo' => trim((string)($fila['nombre_completo'] ?? '')),
            'nro_cedula' => trim((string)($fila['nro_cedula'] ?? '')),
            'nro_solicitud' => trim((string)($fila['nro_solicitud'] ?? '')),
        ];
    }

    private function normalizarPrimeraFila($resultado): array
    {
        if (!is_array($resultado) || empty($resultado)) {
            return [];
        }

        $fila = isset($resultado[0]) && is_array($resultado[0]) ? $resultado[0] : $resultado;
        return is_array($fila) ? $this->normalizarFila($fila) : [];
    }

    public function buscarPorCedula(string $nroCedula): array
    {
        $sql = $this->sqlBaseSelect() . " WHERE `{$this->campo('ci')}` = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$nroCedula]));
    }

    public function buscarPorSolicitud(string $nroSolicitud): array
    {
        if ($this->campo('solicitud') === '') {
            return [];
        }

        $sql = $this->sqlBaseSelect() . " WHERE `{$this->campo('solicitud')}` = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$nroSolicitud]));
    }

    public function buscarPorNombre(string $nombre): array
    {
        $campoNombreCompleto = $this->campo('nombre_completo');
        $whereNombre = $campoNombreCompleto !== ''
            ? "(`{$campoNombreCompleto}` LIKE ? OR `{$this->campo('ci')}` LIKE ?)"
            : "(`{$this->campo('nombre')}` LIKE ? OR `{$this->campo('apellido')}` LIKE ? OR `{$this->campo('ci')}` LIKE ?)";
        $params = ["%{$nombre}%", "%{$nombre}%"];
        if ($campoNombreCompleto === '') {
            $params[] = "%{$nombre}%";
        }

        $sql = $this->sqlBaseSelect() . "
                WHERE {$whereNombre}
                ORDER BY nombre_completo
                LIMIT 50";
        return array_map([$this, 'normalizarFila'], $this->select_all($sql, $params));
    }

    public function obtenerTodos(): array
    {
        $sql = $this->sqlBaseSelect() . " ORDER BY nombre_completo";
        return array_map([$this, 'normalizarFila'], $this->select_all($sql));
    }

    public function obtenerPorId(int $id): array
    {
        $sql = $this->sqlBaseSelect() . " WHERE `{$this->campo('id')}` = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$id]));
    }
}
