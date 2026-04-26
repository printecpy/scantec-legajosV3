<?php

class PersonasModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
        $this->asegurarTablaPersonas();
    }

    private function asegurarTablaPersonas(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS personas (
            id_persona INT NOT NULL AUTO_INCREMENT,
            apellido VARCHAR(100) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            ci VARCHAR(30) NOT NULL,
            celular VARCHAR(40) NULL DEFAULT NULL,
            correo VARCHAR(150) NULL DEFAULT NULL,
            direccion VARCHAR(255) NULL DEFAULT NULL,
            tipo_persona ENUM('cliente','empleado') NOT NULL DEFAULT 'cliente',
            cargo VARCHAR(120) NULL DEFAULT NULL,
            fecha_cumpleanos DATE NULL DEFAULT NULL,
            estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_persona),
            UNIQUE KEY uk_personas_ci (ci),
            KEY idx_personas_estado (estado),
            KEY idx_personas_tipo (tipo_persona)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci";
        $this->update($sql, []);
    }

    public function selectPersonas(string $termino = '', string $estado = ''): array
    {
        $sql = "SELECT id_persona, apellido, nombre, ci, celular, correo, direccion,
                    tipo_persona, cargo, fecha_cumpleanos, estado
                FROM personas
                WHERE 1=1";
        $params = [];

        $termino = trim($termino);
        if ($termino !== '') {
            $like = '%' . $termino . '%';
            $sql .= " AND (apellido LIKE ? OR nombre LIKE ? OR ci LIKE ? OR correo LIKE ? OR celular LIKE ?)";
            $params = array_merge($params, [$like, $like, $like, $like, $like]);
        }

        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }

        $sql .= " ORDER BY apellido ASC, nombre ASC, id_persona DESC";
        return $this->select_all($sql, $params);
    }

    public function selectPersonasActivas(): array
    {
        return $this->select_all(
            "SELECT id_persona, apellido, nombre, ci, celular, correo, direccion,
                    tipo_persona, cargo, fecha_cumpleanos, estado
             FROM personas
             WHERE estado = 'activo'
             ORDER BY apellido ASC, nombre ASC"
        );
    }

    public function selectPersonaPorId(int $idPersona): array
    {
        $row = $this->select(
            "SELECT id_persona, apellido, nombre, ci, celular, correo, direccion,
                    tipo_persona, cargo, fecha_cumpleanos, estado
             FROM personas
             WHERE id_persona = ?",
            [$idPersona]
        );
        return is_array($row) && isset($row[0]) ? $row[0] : (is_array($row) ? $row : []);
    }

    public function existeCi(string $ci, int $idPersonaExcluir = 0): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM personas WHERE ci = ?";
        $params = [$ci];
        if ($idPersonaExcluir > 0) {
            $sql .= " AND id_persona <> ?";
            $params[] = $idPersonaExcluir;
        }
        $row = $this->select($sql, $params);
        return intval($row['total'] ?? 0) > 0;
    }

    public function guardarPersona(
        int $idPersona,
        string $apellido,
        string $nombre,
        string $ci,
        string $celular,
        string $correo,
        string $direccion,
        string $tipoPersona,
        string $cargo,
        ?string $fechaCumpleanos,
        string $estado
    ) {
        $tipoPersona = in_array($tipoPersona, ['cliente', 'empleado'], true) ? $tipoPersona : 'cliente';
        $estado = in_array($estado, ['activo', 'inactivo'], true) ? $estado : 'activo';
        $cargo = $tipoPersona === 'empleado' ? $cargo : '';
        $fechaCumpleanos = trim((string)$fechaCumpleanos) !== '' ? $fechaCumpleanos : null;

        if ($idPersona > 0) {
            $sql = "UPDATE personas
                    SET apellido = ?, nombre = ?, ci = ?, celular = ?, correo = ?,
                        direccion = ?, tipo_persona = ?, cargo = ?, fecha_cumpleanos = ?, estado = ?
                    WHERE id_persona = ?";
            return $this->update($sql, [
                $apellido,
                $nombre,
                $ci,
                $celular !== '' ? $celular : null,
                $correo !== '' ? $correo : null,
                $direccion !== '' ? $direccion : null,
                $tipoPersona,
                $cargo !== '' ? $cargo : null,
                $fechaCumpleanos,
                $estado,
                $idPersona,
            ]);
        }

        $sql = "INSERT INTO personas
                (apellido, nombre, ci, celular, correo, direccion, tipo_persona, cargo, fecha_cumpleanos, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->insert($sql, [
            $apellido,
            $nombre,
            $ci,
            $celular !== '' ? $celular : null,
            $correo !== '' ? $correo : null,
            $direccion !== '' ? $direccion : null,
            $tipoPersona,
            $cargo !== '' ? $cargo : null,
            $fechaCumpleanos,
            $estado,
        ]);
    }

    public function cambiarEstadoPersona(int $idPersona, string $estado): bool
    {
        if ($idPersona <= 0 || !in_array($estado, ['activo', 'inactivo'], true)) {
            return false;
        }

        return (bool)$this->update(
            "UPDATE personas SET estado = ? WHERE id_persona = ?",
            [$estado, $idPersona]
        );
    }
}
