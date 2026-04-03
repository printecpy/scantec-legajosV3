<?php
$mensajeFlash = $mensajeFlash ?? null;
$envioExitoso = is_array($mensajeFlash) && (($mensajeFlash['type'] ?? '') === 'success') && (($mensajeFlash['action'] ?? '') === 'sent');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario externo de legajo</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2937; margin: 0; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 18px; box-shadow: 0 8px 24px rgba(15, 23, 42, .08); margin-bottom: 20px; overflow: hidden; }
        .head { background: #182541; color: #fff; padding: 18px 24px; }
        .body { padding: 24px; }
        .grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 16px; }
        .col-4 { grid-column: span 4; }
        .col-6 { grid-column: span 6; }
        .col-8 { grid-column: span 8; }
        .col-12 { grid-column: span 12; }
        label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
        input[type="text"], input[type="date"], textarea { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 12px; font-size: 14px; }
        textarea { min-height: 84px; resize: vertical; }
        .btn { display: inline-block; border: 0; border-radius: 12px; padding: 12px 18px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: #182541; color: #fff; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-success { background: #166534; color: #fff; }
        .msg { padding: 14px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 14px; }
        .msg-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .msg-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 14px; border-bottom: 1px solid #e5e7eb; vertical-align: top; font-size: 14px; }
        .table th { background: #f9fafb; text-align: left; font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .chip { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
        @media (max-width: 900px) {
            .col-4, .col-6, .col-8, .col-12 { grid-column: span 12; }
            .wrap { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h1 style="margin:0;font-size:24px;">Carga externa de legajo</h1>
                <p style="margin:8px 0 0;font-size:14px;opacity:.85;">Complete sus datos y adjunte la documentación solicitada usando este enlace único.</p>
            </div>
            <div class="body">
                <?php if ($mensajeFlash && is_array($mensajeFlash)): ?>
                <div class="msg <?php echo ($mensajeFlash['type'] ?? '') === 'success' ? 'msg-success' : 'msg-error'; ?>">
                    <?php echo htmlspecialchars($mensajeFlash['message'] ?? ''); ?>
                </div>
                <?php endif; ?>

                <?php if ($envioExitoso): ?>
                <?php elseif (empty($formulario)): ?>
                <div class="msg msg-error">El enlace no es válido o ya no está disponible.</div>
                <?php elseif (empty($formularioDisponible)): ?>
                <div class="msg msg-error">Este enlace ya fue usado, venció o fue desactivado.</div>
                <?php elseif (!$autorizado): ?>
                <?php if ($errorAcceso !== ''): ?>
                <div class="msg msg-error"><?php echo htmlspecialchars($errorAcceso); ?></div>
                <?php endif; ?>
                <form method="POST" action="<?php echo htmlspecialchars($baseUrl . 'legajos/formulario_externo'); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($formulario['token'] ?? ''); ?>">
                    <div class="grid">
                        <div class="col-6">
                            <label>Cédula</label>
                            <input type="text" name="cedula_acceso" required placeholder="Ingrese su número de cédula">
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Ingresar al formulario</button>
                    </div>
                </form>
                <?php else: ?>
                <div class="grid">
                    <div class="col-4">
                        <label>Tipo de legajo</label>
                        <div class="chip"><?php echo htmlspecialchars($formulario['nombre_tipo_legajo'] ?? ''); ?></div>
                    </div>
                    <div class="col-4">
                        <label>Modo</label>
                        <div class="chip"><?php echo htmlspecialchars(ucfirst($formulario['modo_carga'] ?? 'nuevo')); ?></div>
                    </div>
                    <div class="col-4">
                        <label>Vence</label>
                        <div class="chip"><?php echo !empty($formulario['vence_en']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($formulario['vence_en']))) : '-'; ?></div>
                    </div>
                </div>
                <?php
                $nombreTipoLegajo = mb_strtolower(trim((string)($formulario['nombre_tipo_legajo'] ?? '')), 'UTF-8');
                $requiereSolicitud = !empty($formulario['requiere_nro_solicitud']) && strpos($nombreTipoLegajo, 'empleado') === false;
                $actionBorrador = $baseUrl . 'legajos/guardar_borrador_formulario_externo';
                $actionEnviar = $baseUrl . 'legajos/enviar_formulario_externo';
                ?>
                <form method="POST" action="<?php echo htmlspecialchars($actionBorrador); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="token_formulario" value="<?php echo htmlspecialchars($formulario['token'] ?? ''); ?>">

                    <div class="card" style="box-shadow:none;border:1px solid #e5e7eb;">
                        <div class="head"><h2 style="margin:0;font-size:18px;">Datos personales</h2></div>
                        <div class="body">
                            <div class="grid">
                                <div class="col-4">
                                    <label>Cédula</label>
                                    <input type="text" name="ci_socio" value="<?php echo htmlspecialchars($formulario['ci_validacion'] ?? ''); ?>" readonly>
                                </div>
                                <div class="col-8">
                                    <label>Nombre completo</label>
                                    <input type="text" name="nombre_socio" required value="<?php echo htmlspecialchars($formulario['nombre_referencia'] ?? ($formulario['nombre_legajo_base'] ?? '')); ?>">
                                </div>
                                <div class="col-6" <?php echo $requiereSolicitud ? '' : 'style="display:none;"'; ?>>
                                    <label>Número de solicitud</label>
                                    <input type="text" name="nro_solicitud" value="<?php echo htmlspecialchars($formulario['nro_solicitud_referencia'] ?? ($formulario['solicitud_legajo_base'] ?? '')); ?>" <?php echo $requiereSolicitud ? 'required' : 'disabled'; ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="box-shadow:none;border:1px solid #e5e7eb;">
                        <div class="head"><h2 style="margin:0;font-size:18px;">Documentos requeridos</h2></div>
                        <div class="body" style="padding:0;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Documento</th>
                                        <th>Archivo</th>
                                        <th>Fecha expedición</th>
                                        <th>Observación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matriz as $regla): ?>
                                    <?php $idRequisito = intval($regla['id_requisito'] ?? 0); ?>
                                    <?php $documento = $documentos[$idRequisito] ?? []; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($regla['documento_nombre'] ?? 'Documento'); ?></strong>
                                            <?php if (!empty($regla['es_obligatorio'])): ?>
                                            <div style="font-size:12px;color:#b91c1c;margin-top:4px;">Obligatorio</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($documento['ruta_archivo'])): ?>
                                            <div style="font-size:12px;color:#166534;margin-bottom:8px;">Archivo cargado</div>
                                            <label style="display:flex;align-items:center;gap:8px;text-transform:none;font-size:13px;font-weight:500;color:#374151;">
                                                <input type="checkbox" name="eliminar_archivo_<?php echo $idRequisito; ?>" value="1"> Eliminar archivo actual
                                            </label>
                                            <?php endif; ?>
                                            <input type="file" name="doc_<?php echo $idRequisito; ?>[]" multiple accept=".pdf,.jpg,.jpeg,.png,.jfif">
                                        </td>
                                        <td>
                                            <input type="date" name="fecha_expedicion_<?php echo $idRequisito; ?>" value="<?php echo htmlspecialchars($documento['fecha_expedicion'] ?? ''); ?>" <?php echo !empty($regla['tiene_vencimiento']) ? 'required' : ''; ?>>
                                        </td>
                                        <td>
                                            <textarea name="observacion_<?php echo $idRequisito; ?>"><?php echo htmlspecialchars($documento['observacion'] ?? ''); ?></textarea>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="actions">
                        <button class="btn btn-secondary" type="submit">Guardar borrador</button>
                        <button class="btn btn-success" type="submit" formaction="<?php echo htmlspecialchars($actionEnviar); ?>">Enviar formulario</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
