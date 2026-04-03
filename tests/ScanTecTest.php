<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ScanTecTest extends TestCase
{
    private InMemorySessionStore $sessionStore;
    private FakeFilesystem $filesystem;
    private JwtHs256 $jwt;
    private AuthService $authService;
    private LegajoService $legajoService;
    private DocumentService $documentService;
    private PermissionService $permissionService;
    private ExpedienteService $expedienteService;
    private ApiGateway $apiGateway;

    /** @var array<int, array<string, mixed>> */
    private array $users;

    /** @var array<int, array<string, mixed>> */
    private array $legajos;

    /** @var array<int, array<string, mixed>> */
    private array $documentLogs;

    /** @var array<int, array<string, mixed>> */
    private array $failedLoginLog;

    /** @var array<int, array<string, mixed>> */
    private array $apiRequestLog;

    /** @var array<int, array<string, mixed>> */
    private array $matrizRequisitos;

    protected function setUp(): void
    {
        $passwordHash = password_hash('Secret123!', PASSWORD_BCRYPT, ['cost' => 12]);

        $this->users = [
            1 => ['id_usuario' => 1, 'usuario' => 'root', 'clave' => $passwordHash, 'estado_usuario' => 'ACTIVO', 'fecha_expiracion' => null, 'id_rol' => 1, 'grupos' => ['global'], 'failed_attempts' => 0],
            2 => ['id_usuario' => 2, 'usuario' => 'operador', 'clave' => $passwordHash, 'estado_usuario' => 'ACTIVO', 'fecha_expiracion' => null, 'id_rol' => 3, 'grupos' => ['rrhh'], 'failed_attempts' => 0],
            3 => ['id_usuario' => 3, 'usuario' => 'inactivo', 'clave' => $passwordHash, 'estado_usuario' => 'INACTIVO', 'fecha_expiracion' => null, 'id_rol' => 4, 'grupos' => ['externo'], 'failed_attempts' => 0],
            4 => ['id_usuario' => 4, 'usuario' => 'vencido', 'clave' => $passwordHash, 'estado_usuario' => 'ACTIVO', 'fecha_expiracion' => '2020-01-01 00:00:00', 'id_rol' => 4, 'grupos' => ['externo'], 'failed_attempts' => 0],
        ];

        $this->legajos = [
            1001 => ['id_legajo' => 1001, 'id_tipo_legajo' => 10, 'ci' => '1234567', 'nombre' => 'Maria Gomez', 'estado' => 'borrador', 'fecha_creacion' => '2026-04-01 10:00:00', 'tipo' => 'Empleado'],
            1002 => ['id_legajo' => 1002, 'id_tipo_legajo' => 10, 'ci' => '7777777', 'nombre' => 'Juan Perez', 'estado' => 'activo', 'fecha_creacion' => '2026-04-02 09:30:00', 'tipo' => 'Empleado'],
            1003 => ['id_legajo' => 1003, 'id_tipo_legajo' => 11, 'ci' => '8888888', 'nombre' => 'Empresa Demo', 'estado' => 'finalizado', 'fecha_creacion' => '2026-03-15 14:45:00', 'tipo' => 'Cliente'],
        ];

        $this->matrizRequisitos = [
            ['id_requisito' => 1, 'id_tipo_legajo' => 10, 'obligatorio' => true, 'id_documento_maestro' => 21],
            ['id_requisito' => 2, 'id_tipo_legajo' => 10, 'obligatorio' => true, 'id_documento_maestro' => 22],
            ['id_requisito' => 3, 'id_tipo_legajo' => 10, 'obligatorio' => false, 'id_documento_maestro' => 23],
            ['id_requisito' => 4, 'id_tipo_legajo' => 11, 'obligatorio' => true, 'id_documento_maestro' => 24],
        ];

        $this->documentLogs = [];
        $this->failedLoginLog = [];
        $this->apiRequestLog = [];
        $this->sessionStore = new InMemorySessionStore();
        $this->filesystem = new FakeFilesystem(5 * 1024 * 1024, ['pdf', 'jpg', 'jpeg', 'png', 'jfif']);
        $this->jwt = new JwtHs256('scantec-test-secret');
        $this->authService = new AuthService($this->users, $this->sessionStore, $this->failedLoginLog, $this->jwt);
        $this->legajoService = new LegajoService($this->legajos, $this->matrizRequisitos);
        $this->documentService = new DocumentService($this->filesystem, $this->documentLogs);
        $this->permissionService = new PermissionService(
            [3 => ['create' => true, 'edit' => true, 'verify' => false, 'close' => false, 'delete' => false], 4 => ['create' => false, 'edit' => false, 'verify' => false, 'close' => false, 'delete' => false]],
            ['rrhh' => [10], 'externo' => [11]]
        );
        $this->expedienteService = new ExpedienteService();
        $this->apiGateway = new ApiGateway($this->jwt, $this->apiRequestLog, 3);
    }

    /**
     * @group auth
     */
    public function test_valid_login_sets_session_correctly(): void
    {
        // Un login correcto debe dejar un contexto de sesión utilizable por todo el sistema web.
        $result = $this->authService->login('operador', 'Secret123!', 'sess-001', new DateTimeImmutable('2026-04-03 09:00:00'));

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['session']['id_usuario']);
        $this->assertSame('operador', $result['session']['usuario']);
        $this->assertSame(3, $result['session']['id_rol']);
        $this->assertSame('sess-001', $this->sessionStore->getActiveSessionId(2));
    }

    /**
     * @group auth
     */
    public function test_invalid_password_increments_failed_attempts_and_logs_to_intentos_login_fallidos(): void
    {
        // Se usa un mock de PDO para verificar el doble registro de seguridad sin requerir MySQL real.
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->exactly(2))->method('execute')->withAnyParameters()->willReturn(true);

        /** @var PDO&MockObject $pdo */
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->exactly(2))->method('prepare')->willReturn($stmt);

        $auth = new AuthService($this->users, $this->sessionStore, $this->failedLoginLog, $this->jwt, $pdo);
        $result = $auth->login('operador', 'ClaveIncorrecta', 'sess-002', new DateTimeImmutable('2026-04-03 09:00:00'));

        $this->assertFalse($result['success']);
        $this->assertSame('invalid_credentials', $result['error']);
        $this->assertSame(1, $auth->getUserByUsername('operador')['failed_attempts']);
        $this->assertCount(1, $this->failedLoginLog);
        $this->assertSame('operador', $this->failedLoginLog[0]['usuario']);
    }

    /**
     * @group auth
     */
    public function test_expired_or_inactive_user_is_rejected(): void
    {
        // Cubrimos dos causas típicas de rechazo para evitar accesos indebidos.
        $inactive = $this->authService->login('inactivo', 'Secret123!', 'sess-003', new DateTimeImmutable('2026-04-03 09:00:00'));
        $expired = $this->authService->login('vencido', 'Secret123!', 'sess-004', new DateTimeImmutable('2026-04-03 09:00:00'));

        $this->assertFalse($inactive['success']);
        $this->assertSame('inactive_user', $inactive['error']);
        $this->assertFalse($expired['success']);
        $this->assertSame('expired_user', $expired['error']);
    }

    /**
     * @group auth
     */
    public function test_csrf_token_generation_and_validation_including_expiry(): void
    {
        // El token debe respetar formato de 64 hex y expirar exactamente a los 3 minutos.
        $token = $this->authService->createCsrfToken(new DateTimeImmutable('2026-04-03 10:00:00'));

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token['token']);
        $this->assertTrue($this->authService->validateCsrfToken($token['token'], $token['expires_at'], new DateTimeImmutable('2026-04-03 10:02:59')));
        $this->assertFalse($this->authService->validateCsrfToken($token['token'], $token['expires_at'], new DateTimeImmutable('2026-04-03 10:03:01')));
    }

    /**
     * @group auth
     */
    public function test_duplicate_session_detection(): void
    {
        // Si existe otra sesión activa para el mismo usuario, el login debe bloquearse.
        $this->sessionStore->setActiveSession(2, 'sess-old');
        $result = $this->authService->login('operador', 'Secret123!', 'sess-new', new DateTimeImmutable('2026-04-03 11:00:00'));

        $this->assertFalse($result['success']);
        $this->assertSame('duplicate_session', $result['error']);
        $this->assertSame('sess-old', $this->sessionStore->getActiveSessionId(2));
    }

    /**
     * @group auth
     */
    public function test_jwt_generation_and_validation_for_api(): void
    {
        // Verificamos el ciclo completo de JWT HS256 para la API REST.
        $token = $this->authService->generateJwt(['sub' => 2, 'roles' => [3]], new DateTimeImmutable('2026-04-03 12:00:00'), 600);
        $payload = $this->authService->validateJwt($token, new DateTimeImmutable('2026-04-03 12:05:00'));

        $this->assertSame(2, $payload['sub']);
        $this->assertSame([3], $payload['roles']);
        $this->assertSame('HS256', $payload['alg']);
    }

    /**
     * @group auth
     */
    public function test_remote_session_invalidation_by_admin(): void
    {
        // El monitoreo remoto debe permitir al admin cortar accesos comprometidos.
        $this->sessionStore->setActiveSession(2, 'sess-active');
        $this->authService->invalidateSessionByAdmin(2);

        $this->assertNull($this->sessionStore->getActiveSessionId(2));
    }

    /**
     * @group legajo
     */
    public function test_create_legajo_with_valid_data(): void
    {
        // Se valida el alta mínima obligatoria de un legajo.
        $legajo = $this->legajoService->createLegajo([
            'id_tipo_legajo' => 10,
            'ci' => '9999999',
            'nombre' => 'Legajo Nuevo',
            'estado' => 'borrador',
            'fecha_creacion' => '2026-04-03 14:00:00',
        ]);

        $this->assertSame(1004, $legajo['id_legajo']);
        $this->assertSame('9999999', $legajo['ci']);
        $this->assertSame('borrador', $legajo['estado']);
    }

    /**
     * @group legajo
     */
    #[DataProvider('missing_legajo_field_provider')]
    public function test_reject_legajo_creation_with_missing_mandatory_fields(array $payload, string $expectedField): void
    {
        // Probamos que cada campo obligatorio se controle de forma explícita.
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedField);

        $this->legajoService->createLegajo($payload);
    }

    /**
     * @group legajo
     */
    public function test_state_transitions_borrador_activo_finalizado_verificado_cerrado(): void
    {
        // Este es el flujo feliz principal del ciclo de vida del legajo.
        $state = 'borrador';
        foreach (['activo', 'finalizado', 'verificado', 'cerrado'] as $targetState) {
            $state = $this->legajoService->transitionState($state, $targetState);
        }

        $this->assertSame('cerrado', $state);
    }

    /**
     * @group legajo
     */
    #[DataProvider('invalid_legajo_transition_provider')]
    public function test_reject_invalid_state_transitions(string $currentState, string $nextState): void
    {
        // Evita saltos de proceso que pueden omitir controles de generación o verificación.
        $this->expectException(DomainException::class);

        $this->legajoService->transitionState($currentState, $nextState);
    }

    /**
     * @group legajo
     */
    public function test_completeness_percentage_calculation_based_on_cfg_matriz_requisitos(): void
    {
        // Solo los requisitos obligatorios deben contar para el porcentaje de completitud.
        $percentage = $this->legajoService->calculateCompletenessPercentage(10, [['id_documento_maestro' => 21]]);
        $this->assertSame(50.0, $percentage);
    }

    /**
     * @group legajo
     */
    public function test_legajo_search_and_filtering_by_type_state_ci_date(): void
    {
        // La búsqueda combinada alimenta bandejas operativas y reportes.
        $results = $this->legajoService->search([
            'id_tipo_legajo' => 10,
            'estado' => 'activo',
            'ci' => '7777',
            'date_from' => '2026-04-01',
            'date_to' => '2026-04-30',
        ]);

        $this->assertCount(1, $results);
        $this->assertSame(1002, $results[0]['id_legajo']);
    }

    /**
     * @group document
     */
    public function test_upload_valid_file_and_link_to_legajo_documento(): void
    {
        // Un archivo válido debe quedar vinculado al legajo y auditado.
        $document = $this->documentService->uploadDocument(1001, ['name' => 'cedula.pdf', 'size' => 1024, 'extension' => 'pdf'], 'REEMPLAZAR');

        $this->assertSame(1001, $document['id_legajo']);
        $this->assertSame('cedula.pdf', $document['nombre_archivo']);
        $this->assertCount(1, $this->documentLogs);
    }

    /**
     * @group document
     */
    public function test_reject_unsupported_file_types(): void
    {
        // Controla la entrada de binarios no soportados o riesgosos.
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('unsupported');

        $this->documentService->uploadDocument(1001, ['name' => 'script.exe', 'size' => 2048, 'extension' => 'exe'], 'REEMPLAZAR');
    }

    /**
     * @group document
     */
    public function test_reject_files_exceeding_size_limit(): void
    {
        // El límite de peso protege storage, tiempos de carga y estabilidad.
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('size');

        $this->documentService->uploadDocument(1001, ['name' => 'gigante.pdf', 'size' => 6 * 1024 * 1024, 'extension' => 'pdf'], 'REEMPLAZAR');
    }

    /**
     * @group document
     */
    #[DataProvider('politica_actualizacion_provider')]
    public function test_validate_politica_actualizacion(string $policy, bool $existingDocument, string $expectedMode): void
    {
        // Cubre las cinco políticas de actualización documental definidas por el sistema.
        $result = $this->documentService->applyUpdatePolicy(
            $policy,
            $existingDocument ? [['nombre_archivo' => 'previo.pdf']] : [],
            ['name' => 'nuevo.pdf', 'size' => 1024, 'extension' => 'pdf']
        );

        $this->assertSame($expectedMode, $result['mode']);
    }

    /**
     * @group document
     */
    public function test_document_expiry_date_calculation_emision_plus_dias_vigencia_base(): void
    {
        // La fecha de vencimiento es clave para alertas y semáforos documentales.
        $expiry = $this->documentService->calculateExpiryDate('2026-04-03', 365);
        $this->assertSame('2027-04-03', $expiry->format('Y-m-d'));
    }

    /**
     * @group document
     */
    public function test_mark_document_as_por_vencer_or_vencido_correctly(): void
    {
        // Verificamos los dos estados de riesgo temporal más importantes.
        $porVencer = $this->documentService->classifyExpiryState(new DateTimeImmutable('2026-04-20'), new DateTimeImmutable('2026-04-03'), 30);
        $vencido = $this->documentService->classifyExpiryState(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-04-03'), 30);

        $this->assertSame('POR_VENCER', $porVencer);
        $this->assertSame('VENCIDO', $vencido);
    }

    /**
     * @group permissions
     */
    public function test_super_admin_id_rol_1_bypasses_all_permission_checks(): void
    {
        // El rol 1 en SCANTEC debe resolver siempre en bypass positivo.
        $allowed = $this->permissionService->can(['id_rol' => 1, 'grupos' => ['global']], 'delete', 999);
        $this->assertTrue($allowed);
    }

    /**
     * @group permissions
     */
    public function test_non_admin_user_is_blocked_by_missing_permission(): void
    {
        // Un usuario sin permiso específico no debe acceder a acciones críticas.
        $allowed = $this->permissionService->can(['id_rol' => 4, 'grupos' => ['externo']], 'edit', 11);
        $this->assertFalse($allowed);
    }

    /**
     * @group permissions
     */
    public function test_group_based_document_type_filtering_permisos_documentos(): void
    {
        // La visibilidad de tipos debe respetar el grupo operativo del usuario.
        $visibleTypes = $this->permissionService->filterAllowedDocumentTypes(['grupos' => ['rrhh']], [10, 11, 12]);
        $this->assertSame([10], array_values($visibleTypes));
    }

    /**
     * @group permissions
     */
    public function test_permisos_legajos_actions_create_edit_verify_close_delete(): void
    {
        // Se valida la matriz de acciones del módulo legajos para un rol normal.
        $user = ['id_rol' => 3, 'grupos' => ['rrhh']];

        $this->assertTrue($this->permissionService->can($user, 'create', 10));
        $this->assertTrue($this->permissionService->can($user, 'edit', 10));
        $this->assertFalse($this->permissionService->can($user, 'verify', 10));
        $this->assertFalse($this->permissionService->can($user, 'close', 10));
        $this->assertFalse($this->permissionService->can($user, 'delete', 10));
    }

    /**
     * @group expediente
     */
    public function test_create_expediente_with_all_indices(): void
    {
        // Cubre creación estándar con todos los índices principales cargados.
        $expediente = $this->expedienteService->createExpediente([
            'indice_01' => 'A001',
            'indice_02' => 'B001',
            'indice_03' => 'C001',
            'indice_04' => 'D001',
            'indice_05' => 'E001',
            'id_requisito' => 1,
        ]);

        $this->assertSame('A001', $expediente['indice_01']);
        $this->assertSame(1, $expediente['id_requisito']);
    }

    /**
     * @group expediente
     */
    public function test_link_expediente_to_cfg_matriz_requisitos_via_id_requisito(): void
    {
        // El vínculo a matriz de requisitos es necesario para trazabilidad documental.
        $expediente = $this->expedienteService->createExpediente([
            'indice_01' => 'A002',
            'indice_02' => 'B002',
            'indice_03' => 'C002',
            'indice_04' => 'D002',
            'indice_05' => 'E002',
            'id_requisito' => 4,
        ]);

        $this->assertSame(4, $expediente['id_requisito']);
    }

    /**
     * @group expediente
     */
    #[DataProvider('estado_validacion_provider')]
    public function test_update_estado_validacion(string $targetState): void
    {
        // Se comprueba que la transición de validación admita todos los estados definidos.
        $expediente = $this->expedienteService->createExpediente([
            'indice_01' => 'A003',
            'indice_02' => 'B003',
            'indice_03' => 'C003',
            'indice_04' => 'D003',
            'indice_05' => 'E003',
            'id_requisito' => 2,
        ]);

        $updated = $this->expedienteService->updateEstadoValidacion($expediente['id_expediente'], $targetState);
        $this->assertSame($targetState, $updated['estado_validacion']);
    }

    /**
     * @group expediente
     */
    public function test_query_via_v_expedientes_view(): void
    {
        // Simula la consulta sobre la vista consolidada usada por filtros y reportes.
        $this->expedienteService->createExpediente([
            'indice_01' => 'AX01',
            'indice_02' => 'BX01',
            'indice_03' => 'CX01',
            'indice_04' => 'DX01',
            'indice_05' => 'EX01',
            'id_requisito' => 2,
        ]);

        $viewRows = $this->expedienteService->queryView(['indice_01' => 'AX01']);
        $this->assertCount(1, $viewRows);
        $this->assertSame('AX01', $viewRows[0]['indice_01']);
    }

    /**
     * @group api
     */
    public function test_valid_jwt_returns_200_with_expected_payload(): void
    {
        // La API debe aceptar un JWT válido y devolver el payload ya autenticado.
        $token = $this->jwt->encode(['sub' => 2, 'roles' => [3]], new DateTimeImmutable('2026-04-03 15:00:00'), 900);
        $response = $this->apiGateway->handleRequest('GET', $token, '10.10.10.10', '/api/legajos/1001', new DateTimeImmutable('2026-04-03 15:05:00'));

        $this->assertSame(200, $response['status']);
        $this->assertSame(2, $response['payload']['sub']);
    }

    /**
     * @group api
     */
    #[DataProvider('missing_or_malformed_jwt_provider')]
    public function test_missing_or_malformed_jwt_returns_401(?string $token): void
    {
        // Ausencia o formato inválido deben terminar siempre en 401.
        $response = $this->apiGateway->handleRequest('GET', $token, '10.10.10.20', '/api/legajos/1001', new DateTimeImmutable('2026-04-03 15:05:00'));
        $this->assertSame(401, $response['status']);
    }

    /**
     * @group api
     */
    public function test_post_put_delete_requests_are_logged_to_api_log(): void
    {
        // Las operaciones mutadoras deben dejar trazabilidad para auditoría.
        $token = $this->jwt->encode(['sub' => 1, 'roles' => [1]], new DateTimeImmutable('2026-04-03 16:00:00'), 900);

        $this->apiGateway->handleRequest('POST', $token, '10.10.10.30', '/api/legajos', new DateTimeImmutable('2026-04-03 16:01:00'));
        $this->apiGateway->handleRequest('PUT', $token, '10.10.10.30', '/api/legajos/1001', new DateTimeImmutable('2026-04-03 16:02:00'));
        $this->apiGateway->handleRequest('DELETE', $token, '10.10.10.30', '/api/legajos/1001', new DateTimeImmutable('2026-04-03 16:03:00'));

        $this->assertCount(3, $this->apiRequestLog);
        $this->assertSame(['POST', 'PUT', 'DELETE'], array_column($this->apiRequestLog, 'method'));
    }

    /**
     * @group api
     */
    public function test_rate_limiting_ip_block_after_repeated_failures(): void
    {
        // Repetidos fallos desde una misma IP deben activar bloqueo/rate limit.
        $ip = '10.10.10.40';
        $now = new DateTimeImmutable('2026-04-03 17:00:00');

        $this->apiGateway->handleRequest('GET', 'malformado', $ip, '/api/legajos', $now);
        $this->apiGateway->handleRequest('GET', 'malformado', $ip, '/api/legajos', $now->modify('+1 minute'));
        $this->apiGateway->handleRequest('GET', 'malformado', $ip, '/api/legajos', $now->modify('+2 minutes'));
        $blocked = $this->apiGateway->handleRequest('GET', 'malformado', $ip, '/api/legajos', $now->modify('+3 minutes'));

        $this->assertSame(429, $blocked['status']);
        $this->assertSame('ip_blocked', $blocked['error']);
    }

    public static function missing_legajo_field_provider(): iterable
    {
        yield 'missing_tipo' => [['ci' => '123', 'nombre' => 'Sin tipo', 'estado' => 'borrador', 'fecha_creacion' => '2026-04-03 14:00:00'], 'id_tipo_legajo'];
        yield 'missing_ci' => [['id_tipo_legajo' => 10, 'nombre' => 'Sin CI', 'estado' => 'borrador', 'fecha_creacion' => '2026-04-03 14:00:00'], 'ci'];
        yield 'missing_nombre' => [['id_tipo_legajo' => 10, 'ci' => '123', 'estado' => 'borrador', 'fecha_creacion' => '2026-04-03 14:00:00'], 'nombre'];
    }

    public static function invalid_legajo_transition_provider(): iterable
    {
        yield ['borrador', 'verificado'];
        yield ['activo', 'cerrado'];
        yield ['cerrado', 'activo'];
        yield ['verificado', 'activo'];
    }

    public static function politica_actualizacion_provider(): iterable
    {
        yield 'replace_without_previous' => ['REEMPLAZAR', false, 'replace'];
        yield 'replace_with_previous' => ['REEMPLAZAR', true, 'replace'];
        yield 'prepend' => ['UNIR_AL_INICIO', true, 'prepend'];
        yield 'append' => ['UNIR_AL_FINAL', true, 'append'];
        yield 'deny' => ['NO_PERMITIR', true, 'deny'];
        yield 'consult' => ['CONSULTAR', true, 'consult'];
    }

    public static function estado_validacion_provider(): iterable
    {
        yield ['VIGENTE'];
        yield ['POR_VENCER'];
        yield ['VENCIDO'];
        yield ['RECHAZADO'];
        yield ['HISTORICO'];
    }

    public static function missing_or_malformed_jwt_provider(): iterable
    {
        yield 'missing' => [null];
        yield 'empty' => [''];
        yield 'garbage' => ['abc.def'];
        yield 'bad_signature_shape' => ['a.b.c'];
    }
}

final class InMemorySessionStore
{
    /** @var array<int, string> */
    private array $activeSessions = [];

    public function setActiveSession(int $userId, string $sessionId): void
    {
        $this->activeSessions[$userId] = $sessionId;
    }

    public function getActiveSessionId(int $userId): ?string
    {
        return $this->activeSessions[$userId] ?? null;
    }

    public function invalidate(int $userId): void
    {
        unset($this->activeSessions[$userId]);
    }
}

final class FakeFilesystem
{
    public function __construct(private readonly int $maxSizeBytes, private readonly array $allowedExtensions)
    {
    }

    public function validateFile(array $file): void
    {
        $extension = strtolower((string) ($file['extension'] ?? ''));
        $size = (int) ($file['size'] ?? 0);

        if (!in_array($extension, $this->allowedExtensions, true)) {
            throw new UnexpectedValueException('unsupported file type');
        }

        if ($size > $this->maxSizeBytes) {
            throw new LengthException('file exceeds allowed size');
        }
    }
}

final class JwtHs256
{
    public function __construct(private readonly string $secret)
    {
    }

    public function encode(array $payload, DateTimeImmutable $issuedAt, int $ttlSeconds): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $body = $payload + ['iat' => $issuedAt->getTimestamp(), 'exp' => $issuedAt->modify("+{$ttlSeconds} seconds")->getTimestamp()];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($body, JSON_THROW_ON_ERROR)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $this->secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function decode(string $jwt, DateTimeImmutable $now): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new UnexpectedValueException('malformed token');
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $this->secret, true));
        if (!hash_equals($expected, $signature)) {
            throw new UnexpectedValueException('invalid signature');
        }

        $decodedHeader = json_decode($this->base64UrlDecode($header), true, 512, JSON_THROW_ON_ERROR);
        $decodedPayload = json_decode($this->base64UrlDecode($payload), true, 512, JSON_THROW_ON_ERROR);
        if (($decodedPayload['exp'] ?? 0) < $now->getTimestamp()) {
            throw new UnexpectedValueException('expired token');
        }

        return $decodedPayload + ['alg' => $decodedHeader['alg'] ?? ''];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/'), true) ?: '';
    }
}

final class AuthService
{
    public function __construct(
        private array &$users,
        private readonly InMemorySessionStore $sessionStore,
        private array &$failedLoginLog,
        private readonly JwtHs256 $jwt,
        private readonly ?PDO $pdo = null
    ) {
    }

    public function login(string $username, string $password, string $sessionId, DateTimeImmutable $now): array
    {
        $userId = $this->findUserIdByUsername($username);
        if ($userId === null) {
            return ['success' => false, 'error' => 'invalid_credentials'];
        }

        $user = $this->users[$userId];
        if (($user['estado_usuario'] ?? 'INACTIVO') !== 'ACTIVO') {
            return ['success' => false, 'error' => 'inactive_user'];
        }

        if (!empty($user['fecha_expiracion']) && new DateTimeImmutable((string) $user['fecha_expiracion']) < $now) {
            return ['success' => false, 'error' => 'expired_user'];
        }

        $activeSession = $this->sessionStore->getActiveSessionId($userId);
        if ($activeSession !== null && $activeSession !== $sessionId) {
            return ['success' => false, 'error' => 'duplicate_session'];
        }

        if (!password_verify($password, (string) $user['clave'])) {
            $this->users[$userId]['failed_attempts'] = (int) $this->users[$userId]['failed_attempts'] + 1;
            $this->failedLoginLog[] = ['usuario' => $username, 'fecha' => $now->format('Y-m-d H:i:s')];

            if ($this->pdo instanceof PDO) {
                $stmtIncrement = $this->pdo->prepare('UPDATE usuarios SET failed_attempts = failed_attempts + 1 WHERE usuario = ?');
                $stmtIncrement->execute([$username]);
                $stmtLog = $this->pdo->prepare('INSERT INTO intentos_login_fallidos (usuario, fecha) VALUES (?, ?)');
                $stmtLog->execute([$username, $now->format('Y-m-d H:i:s')]);
            }

            return ['success' => false, 'error' => 'invalid_credentials'];
        }

        $session = ['id_usuario' => $user['id_usuario'], 'usuario' => $user['usuario'], 'id_rol' => $user['id_rol']];
        $this->sessionStore->setActiveSession($userId, $sessionId);

        return ['success' => true, 'session' => $session];
    }

    public function createCsrfToken(DateTimeImmutable $now): array
    {
        return ['token' => bin2hex(random_bytes(32)), 'expires_at' => $now->modify('+3 minutes')];
    }

    public function validateCsrfToken(string $token, DateTimeImmutable $expiresAt, DateTimeImmutable $now): bool
    {
        return preg_match('/^[a-f0-9]{64}$/', $token) === 1 && $expiresAt >= $now;
    }

    public function generateJwt(array $payload, DateTimeImmutable $issuedAt, int $ttlSeconds): string
    {
        return $this->jwt->encode($payload, $issuedAt, $ttlSeconds);
    }

    public function validateJwt(string $token, DateTimeImmutable $now): array
    {
        return $this->jwt->decode($token, $now);
    }

    public function invalidateSessionByAdmin(int $userId): void
    {
        $this->sessionStore->invalidate($userId);
    }

    public function getUserByUsername(string $username): array
    {
        $userId = $this->findUserIdByUsername($username);
        return $this->users[$userId];
    }

    private function findUserIdByUsername(string $username): ?int
    {
        foreach ($this->users as $id => $user) {
            if (($user['usuario'] ?? '') === $username) {
                return $id;
            }
        }

        return null;
    }
}

final class LegajoService
{
    public function __construct(private array &$legajos, private readonly array $matrizRequisitos)
    {
    }

    public function createLegajo(array $data): array
    {
        foreach (['id_tipo_legajo', 'ci', 'nombre', 'estado', 'fecha_creacion'] as $requiredField) {
            if (!isset($data[$requiredField]) || $data[$requiredField] === '') {
                throw new InvalidArgumentException("Missing mandatory field: {$requiredField}");
            }
        }

        $nextId = max(array_keys($this->legajos)) + 1;
        $legajo = ['id_legajo' => $nextId] + $data;
        $this->legajos[$nextId] = $legajo;

        return $legajo;
    }

    public function transitionState(string $currentState, string $nextState): string
    {
        $validMap = [
            'borrador' => ['activo'],
            'activo' => ['finalizado'],
            'finalizado' => ['verificado'],
            'verificado' => ['cerrado'],
            'cerrado' => [],
        ];

        if (!in_array($nextState, $validMap[$currentState] ?? [], true)) {
            throw new DomainException("Invalid transition from {$currentState} to {$nextState}");
        }

        return $nextState;
    }

    public function calculateCompletenessPercentage(int $idTipoLegajo, array $documentosPresentes): float
    {
        $required = array_values(array_filter(
            $this->matrizRequisitos,
            static fn(array $row): bool => (int) $row['id_tipo_legajo'] === $idTipoLegajo && !empty($row['obligatorio'])
        ));

        if ($required === []) {
            return 100.0;
        }

        $presentIds = array_map(static fn(array $doc): int => (int) $doc['id_documento_maestro'], $documentosPresentes);
        $complete = 0;
        foreach ($required as $requirement) {
            if (in_array((int) $requirement['id_documento_maestro'], $presentIds, true)) {
                $complete++;
            }
        }

        return round(($complete / count($required)) * 100, 2);
    }

    public function search(array $filters): array
    {
        return array_values(array_filter($this->legajos, static function (array $legajo) use ($filters): bool {
            if (isset($filters['id_tipo_legajo']) && (int) $legajo['id_tipo_legajo'] !== (int) $filters['id_tipo_legajo']) {
                return false;
            }
            if (isset($filters['estado']) && $legajo['estado'] !== $filters['estado']) {
                return false;
            }
            if (!empty($filters['ci']) && !str_contains((string) $legajo['ci'], (string) $filters['ci'])) {
                return false;
            }
            if (!empty($filters['date_from']) && substr((string) $legajo['fecha_creacion'], 0, 10) < $filters['date_from']) {
                return false;
            }
            if (!empty($filters['date_to']) && substr((string) $legajo['fecha_creacion'], 0, 10) > $filters['date_to']) {
                return false;
            }

            return true;
        }));
    }
}

final class DocumentService
{
    public function __construct(private readonly FakeFilesystem $filesystem, private array &$documentLogs)
    {
    }

    public function uploadDocument(int $legajoId, array $file, string $policy): array
    {
        $this->filesystem->validateFile($file);
        $result = $this->applyUpdatePolicy($policy, [], $file);
        if ($result['mode'] === 'deny') {
            throw new DomainException('update policy does not allow upload');
        }

        $document = ['id_legajo' => $legajoId, 'nombre_archivo' => $file['name'], 'politica' => $policy];
        $this->documentLogs[] = $document;

        return $document;
    }

    public function applyUpdatePolicy(string $policy, array $existingDocuments, array $incomingFile): array
    {
        $hasExisting = $existingDocuments !== [];

        return match (strtoupper($policy)) {
            'REEMPLAZAR' => ['mode' => 'replace', 'file' => $incomingFile],
            'UNIR_AL_INICIO' => ['mode' => 'prepend', 'file' => $incomingFile, 'existing' => $existingDocuments],
            'UNIR_AL_FINAL' => ['mode' => 'append', 'file' => $incomingFile, 'existing' => $existingDocuments],
            'NO_PERMITIR' => ['mode' => $hasExisting ? 'deny' : 'replace', 'file' => $incomingFile],
            'CONSULTAR' => ['mode' => 'consult', 'file' => $incomingFile, 'existing' => $existingDocuments],
            default => throw new InvalidArgumentException('unknown update policy'),
        };
    }

    public function calculateExpiryDate(string $emision, int $diasVigenciaBase): DateTimeImmutable
    {
        return (new DateTimeImmutable($emision))->modify("+{$diasVigenciaBase} days");
    }

    public function classifyExpiryState(DateTimeImmutable $expiryDate, DateTimeImmutable $now, int $alertDays): string
    {
        if ($expiryDate < $now) {
            return 'VENCIDO';
        }

        $diffDays = (int) $now->diff($expiryDate)->format('%a');
        return $diffDays <= $alertDays ? 'POR_VENCER' : 'VIGENTE';
    }
}

final class PermissionService
{
    public function __construct(private readonly array $rolePermissions, private readonly array $groupTypePermissions)
    {
    }

    public function can(array $user, string $action, int $tipoLegajo): bool
    {
        if ((int) ($user['id_rol'] ?? 0) === 1) {
            return true;
        }

        $roleAllowed = $this->rolePermissions[(int) $user['id_rol']][$action] ?? false;
        if (!$roleAllowed) {
            return false;
        }

        return $this->filterAllowedDocumentTypes($user, [$tipoLegajo]) !== [];
    }

    public function filterAllowedDocumentTypes(array $user, array $candidateTypes): array
    {
        $allowed = [];
        foreach ((array) ($user['grupos'] ?? []) as $group) {
            $allowed = array_merge($allowed, $this->groupTypePermissions[$group] ?? []);
        }

        return array_values(array_intersect($candidateTypes, array_unique($allowed)));
    }
}

final class ExpedienteService
{
    /** @var array<int, array<string, mixed>> */
    private array $expedientes = [];

    public function createExpediente(array $data): array
    {
        foreach (['indice_01', 'indice_02', 'indice_03', 'indice_04', 'indice_05', 'id_requisito'] as $required) {
            if (!isset($data[$required]) || $data[$required] === '') {
                throw new InvalidArgumentException("Missing expediente field: {$required}");
            }
        }

        $id = count($this->expedientes) + 1;
        $expediente = ['id_expediente' => $id, 'estado_validacion' => 'VIGENTE'] + $data;
        $this->expedientes[$id] = $expediente;

        return $expediente;
    }

    public function updateEstadoValidacion(int $idExpediente, string $estado): array
    {
        if (!in_array($estado, ['VIGENTE', 'POR_VENCER', 'VENCIDO', 'RECHAZADO', 'HISTORICO'], true)) {
            throw new InvalidArgumentException('invalid validation state');
        }

        $this->expedientes[$idExpediente]['estado_validacion'] = $estado;
        return $this->expedientes[$idExpediente];
    }

    public function queryView(array $filters): array
    {
        return array_values(array_filter($this->expedientes, static function (array $row) use ($filters): bool {
            foreach ($filters as $field => $value) {
                if (($row[$field] ?? null) !== $value) {
                    return false;
                }
            }

            return true;
        }));
    }
}

final class ApiGateway
{
    /** @var array<string, int> */
    private array $failedAttemptsByIp = [];

    public function __construct(private readonly JwtHs256 $jwt, private array &$apiRequestLog, private readonly int $maxFailuresPerIp)
    {
    }

    public function handleRequest(string $method, ?string $token, string $ip, string $path, DateTimeImmutable $now): array
    {
        if (($this->failedAttemptsByIp[$ip] ?? 0) >= $this->maxFailuresPerIp) {
            return ['status' => 429, 'error' => 'ip_blocked'];
        }

        if ($token === null || $token === '') {
            $this->failedAttemptsByIp[$ip] = ($this->failedAttemptsByIp[$ip] ?? 0) + 1;
            return ['status' => 401, 'error' => 'missing_token'];
        }

        try {
            $payload = $this->jwt->decode($token, $now);
        } catch (Throwable) {
            $this->failedAttemptsByIp[$ip] = ($this->failedAttemptsByIp[$ip] ?? 0) + 1;
            if (($this->failedAttemptsByIp[$ip] ?? 0) > $this->maxFailuresPerIp) {
                return ['status' => 429, 'error' => 'ip_blocked'];
            }

            return ['status' => 401, 'error' => 'invalid_token'];
        }

        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE'], true)) {
            $this->apiRequestLog[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'ip' => $ip,
                'sub' => $payload['sub'] ?? null,
                'timestamp' => $now->format('Y-m-d H:i:s'),
            ];
        }

        return ['status' => 200, 'payload' => $payload];
    }
}
