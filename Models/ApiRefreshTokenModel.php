<?php
class ApiRefreshTokenModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
        $this->asegurarTabla();
    }

    private function asegurarTabla(): void
    {
        try {
            $this->insert("CREATE TABLE IF NOT EXISTS api_refresh_tokens (
                id INT NOT NULL AUTO_INCREMENT,
                id_usuario INT NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expira_en DATETIME NOT NULL,
                revocado TINYINT(1) NOT NULL DEFAULT 0,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ip_origen VARCHAR(45) DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uk_token_hash (token_hash),
                KEY idx_usuario (id_usuario),
                KEY idx_expira (expira_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci", []);
        } catch (Throwable $e) {
            error_log('Error asegurando tabla api_refresh_tokens: ' . $e->getMessage());
        }
    }

    /**
     * Guarda un refresh token nuevo. Recibe el token en claro y guarda solo su hash SHA-256.
     */
    public function crear(int $idUsuario, string $tokenClaro, int $diasVigencia, ?string $ipOrigen = null): bool
    {
        $hash = hash('sha256', $tokenClaro);
        $expiraEn = (new DateTime())->modify('+' . max(1, $diasVigencia) . ' days')->format('Y-m-d H:i:s');

        $sql = "INSERT INTO api_refresh_tokens (id_usuario, token_hash, expira_en, ip_origen)
                VALUES (?, ?, ?, ?)";
        try {
            $this->insert($sql, [$idUsuario, $hash, $expiraEn, $ipOrigen]);
            return true;
        } catch (Throwable $e) {
            error_log('Error creando refresh token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Devuelve la fila del token si es válido (no expirado, no revocado); [] si no lo es.
     */
    public function obtenerValido(string $tokenClaro): array
    {
        $hash = hash('sha256', $tokenClaro);
        $sql = "SELECT id, id_usuario, expira_en, revocado
                FROM api_refresh_tokens
                WHERE token_hash = ? AND revocado = 0 AND expira_en >= NOW()
                LIMIT 1";
        $res = $this->select($sql, [$hash]);

        if (is_array($res) && isset($res[0]) && is_array($res[0])) {
            $res = $res[0];
        }
        return !empty($res) && isset($res['id_usuario']) ? $res : [];
    }

    /**
     * Revoca un refresh token puntual por su valor en claro.
     */
    public function revocar(string $tokenClaro): bool
    {
        $hash = hash('sha256', $tokenClaro);
        try {
            $this->update("UPDATE api_refresh_tokens SET revocado = 1 WHERE token_hash = ?", [$hash]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Revoca TODOS los tokens activos de un usuario (para cortar un acceso comprometido).
     */
    public function revocarTodosDeUsuario(int $idUsuario): bool
    {
        try {
            $this->update("UPDATE api_refresh_tokens SET revocado = 1 WHERE id_usuario = ? AND revocado = 0", [$idUsuario]);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Limpieza opcional de tokens vencidos o revocados (para no dejar crecer la tabla).
     */
    public function limpiarExpirados(): void
    {
        try {
            $this->update("DELETE FROM api_refresh_tokens WHERE expira_en < NOW() OR revocado = 1", []);
        } catch (Throwable $e) {
            // No crítico.
        }
    }
}