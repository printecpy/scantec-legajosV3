<?php
/**
 * Modelo para leer personas desde una base externa.
 *
 * Toda la configuración (tabla, columnas, motor) sale del .env / SCANTEC_APP_CONFIG.
 * No hay nombres de columna hardcodeados en el código: si cambia el .env de una
 * instalación, este modelo se adapta sin tocar una sola línea de PHP.
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'ConexionUsuarios.php';

class UsuariosDatosModel extends MysqlUsuarios
{
    private array $mapaCampos;
    private string $motor;

    public function __construct()
    {
        parent::__construct();

        $config = $GLOBALS['SCANTEC_APP_CONFIG'] ?? [];

        // Defensa temprana: si por algún motivo se instancia este modelo sin que
        // la base externa esté habilitada, fallamos con un mensaje claro en vez
        // de armar un mapeo con valores de fallback que probablemente no existan
        // en la base real del cliente.
        if (empty($config['db_usuarios_enabled'])) {
            throw new \RuntimeException('UsuariosDatosModel: la base de datos externa de usuarios no está habilitada (SCANTEC_DB_USUARIOS_ENABLED).');
        }

        // Preparado para múltiples motores a futuro. Hoy solo MySQL está
        // implementado; cualquier otro valor falla explícitamente en vez de
        // generar SQL incorrecto en silencio.
        $this->motor = strtolower(trim((string) ($config['db_usuarios_motor'] ?? 'mysql')));
        $motoresSoportados = ['mysql'];
        if (!in_array($this->motor, $motoresSoportados, true)) {
            throw new \RuntimeException("UsuariosDatosModel: el motor '{$this->motor}' aún no está soportado para la base externa de usuarios.");
        }

        $this->mapaCampos = [
            'tabla' => $this->sanitizarIdentificador((string) ($config['db_usuarios_table'] ?? 'usuarios_datos'), 'usuarios_datos'),
            'id' => $this->sanitizarIdentificador((string) ($config['db_usuarios_field_id'] ?? 'id'), 'id'),
            'nombre' => $this->sanitizarIdentificadorOpcional((string) ($config['db_usuarios_field_nombre'] ?? '')),
            'apellido' => $this->sanitizarIdentificadorOpcional((string) ($config['db_usuarios_field_apellido'] ?? '')),
            'nombre_completo' => $this->sanitizarIdentificadorOpcional((string) ($config['db_usuarios_field_nombre_completo'] ?? '')),
            'ci' => $this->sanitizarIdentificador((string) ($config['db_usuarios_field_ci'] ?? 'nro_cedula'), 'nro_cedula'),
            'solicitud' => $this->sanitizarIdentificadorOpcional((string) ($config['db_usuarios_field_solicitud'] ?? '')),
            'nro_socio' => $this->sanitizarIdentificadorOpcional((string) ($config['db_usuarios_field_nro_socio'] ?? '')),
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

    /**
     * Escapa un identificador (nombre de columna/tabla) según el motor activo.
     * MySQL usa backticks; Postgres/SQL Server usarían otro delimitador el día
     * que se implementen — este es el único punto que habría que tocar.
     */
    private function escaparIdentificador(string $nombre): string
    {
        switch ($this->motor) {
            case 'mysql':
            default:
                return "`{$nombre}`";
        }
    }

    /**
     * Arma la cláusula de paginación según el motor activo.
     * MySQL/Postgres: LIMIT/OFFSET. SQL Server necesitaría OFFSET/FETCH NEXT.
     */
    private function sqlLimitOffset(int $limite, int $offset): string
    {
        switch ($this->motor) {
            case 'mysql':
            default:
                return "LIMIT {$limite} OFFSET {$offset}";
        }
    }

    /**
     * Arma el SELECT base a partir del mapeo configurado en el .env.
     * Nunca usa nombres de columna fijos: si nombre_completo no está
     * configurado, arma el nombre a partir de apellido (o nombre como
     * último recurso), respetando lo que cada instalación tenga definido.
     */
    private function sqlBaseSelect(): string
    {
        $idCol = $this->escaparIdentificador($this->campo('id'));
        $ciCol = $this->escaparIdentificador($this->campo('ci'));
        $tablaEsc = $this->escaparIdentificador($this->tabla());

        $campoNombreCompleto = $this->campo('nombre_completo');
        $campoApellido = $this->campo('apellido');
        $campoNombre = $this->campo('nombre');
        $campoNroSocio = $this->campo('nro_socio');

        if ($campoNombreCompleto !== '') {
            $selectNombreCompleto = $this->escaparIdentificador($campoNombreCompleto) . ' AS nombre_completo';
        } elseif ($campoApellido !== '') {
            $selectNombreCompleto = $this->escaparIdentificador($campoApellido) . ' AS nombre_completo';
        } elseif ($campoNombre !== '') {
            $selectNombreCompleto = $this->escaparIdentificador($campoNombre) . ' AS nombre_completo';
        } else {
            $selectNombreCompleto = "'' AS nombre_completo";
        }

        $selectNroSocio = $campoNroSocio !== ''
            ? $this->escaparIdentificador($campoNroSocio) . ' AS nro_socio'
            : "'' AS nro_socio";

        return "SELECT
            {$idCol} AS id,
            {$selectNombreCompleto},
            {$ciCol} AS nro_cedula,
            {$ciCol} AS ci,
            {$selectNroSocio}
        FROM {$tablaEsc}";
    }

    /**
     * Arma la condición WHERE para búsqueda por término libre, usando
     * únicamente las columnas configuradas en el .env. El número de
     * placeholders devuelto SIEMPRE coincide con la cantidad de condiciones
     * armadas, para no repetir el bug de placeholders desalineados.
     *
     * @return array{0: string, 1: int} [condicionSQL, cantidadDePlaceholders]
     */
    private function whereBusquedaTermino(): array
{
    $condiciones = [];

    $campoCi = $this->campo('ci');
    $campoNroSocio = $this->campo('nro_socio');
    $campoNombreCompleto = $this->campo('nombre_completo');
    $campoApellido = $this->campo('apellido');
    $campoNombre = $this->campo('nombre');

    // Se agregan TODAS las columnas configuradas, sin importar el orden de prioridad
    // que se usa para el nombre_completo mostrado. Evita "columnas tapadas" cuando
    // el mapeo de un cliente es cruzado (ej: 'nombre' apunta a una columna de CI).
    foreach ([$campoCi, $campoNroSocio, $campoNombreCompleto, $campoApellido, $campoNombre] as $columna) {
        if ($columna !== '') {
            $condiciones[] = $this->escaparIdentificador($columna) . ' LIKE ?';
        }
    }

    // Evitar duplicar la misma columna física dos veces en el WHERE
    // (ej: si 'ci' y 'nro_socio' apuntan a la misma columna real)
    $condiciones = array_values(array_unique($condiciones));

    return [implode(' OR ', $condiciones), count($condiciones)];
}

    private function normalizarFila(array $fila): array
    {
        return [
            'id' => intval($fila['id'] ?? 0),
            'nombre_completo' => trim((string) ($fila['nombre_completo'] ?? '')),
            'nro_cedula' => trim((string) ($fila['nro_cedula'] ?? '')),
            'nro_solicitud' => trim((string) ($fila['nro_solicitud'] ?? '')),
            'nro_socio' => trim((string) ($fila['nro_socio'] ?? '')),
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
        $ciCol = $this->escaparIdentificador($this->campo('ci'));
        $sql = $this->sqlBaseSelect() . " WHERE {$ciCol} = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$nroCedula]));
    }

    public function buscarPorSolicitud(string $nroSolicitud): array
    {
        if ($this->campo('solicitud') === '') {
            return [];
        }

        $solicitudCol = $this->escaparIdentificador($this->campo('solicitud'));
        $sql = $this->sqlBaseSelect() . " WHERE {$solicitudCol} = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$nroSolicitud]));
    }

    public function buscarPorNombre(string $nombre): array
    {
        [$condicionWhere, $cantidadPlaceholders] = $this->whereBusquedaTermino();
        if ($condicionWhere === '') {
            return [];
        }

        $sql = $this->sqlBaseSelect() . "
                WHERE ({$condicionWhere})
                ORDER BY nombre_completo
                LIMIT 50";

        $terminoLike = "%{$nombre}%";
        $params = array_fill(0, $cantidadPlaceholders, $terminoLike);

        return array_map([$this, 'normalizarFila'], $this->select_all($sql, $params));
    }

    public function buscarPaginado(string $termino, int $offset, int $limite): array
    {
        $termino = trim($termino);
        $offset = max(0, $offset);
        $limite = max(1, $limite);

        if ($termino === '') {
            $sql = $this->sqlBaseSelect() . " ORDER BY nombre_completo " . $this->sqlLimitOffset($limite, $offset);
            return array_map([$this, 'normalizarFila'], $this->select_all($sql));
        }

        [$condicionWhere, $cantidadPlaceholders] = $this->whereBusquedaTermino();
        if ($condicionWhere === '') {
            return [];
        }

        $sql = $this->sqlBaseSelect() . "
                WHERE ({$condicionWhere})
                ORDER BY nombre_completo
                " . $this->sqlLimitOffset($limite, $offset);

        $terminoLike = "%{$termino}%";
        $params = array_fill(0, $cantidadPlaceholders, $terminoLike);

        return array_map([$this, 'normalizarFila'], $this->select_all($sql, $params));
    }

    public function obtenerTodos(): array
    {
        $sql = $this->sqlBaseSelect() . " ORDER BY nombre_completo";
        return array_map([$this, 'normalizarFila'], $this->select_all($sql));
    }

    public function obtenerPorId(int $id): array
    {
        $idCol = $this->escaparIdentificador($this->campo('id'));
        $sql = $this->sqlBaseSelect() . " WHERE {$idCol} = ?";
        return $this->normalizarPrimeraFila($this->select($sql, [$id]));
    }
}