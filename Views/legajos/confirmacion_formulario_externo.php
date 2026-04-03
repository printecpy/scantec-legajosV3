<?php
$mensajeFlash = $mensajeFlash ?? null;
$mensaje = is_array($mensajeFlash) ? trim((string)($mensajeFlash['message'] ?? '')) : '';
$esExito = is_array($mensajeFlash) && (($mensajeFlash['type'] ?? '') === 'success');
if ($mensaje === '') {
    $mensaje = $esExito
        ? 'Formulario enviado correctamente.'
        : 'No se pudo completar la operación.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de formulario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2937; margin: 0; }
        .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; }
        .card { width: 100%; max-width: 620px; background: #fff; border-radius: 20px; box-shadow: 0 12px 30px rgba(15, 23, 42, .10); overflow: hidden; }
        .head { background: #182541; color: #fff; padding: 22px 26px; }
        .body { padding: 28px 26px; }
        .state { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 999px; font-size: 28px; margin-bottom: 18px; }
        .state-ok { background: #dcfce7; color: #166534; }
        .state-error { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="head">
                <h1 style="margin:0;font-size:24px;">Confirmación</h1>
            </div>
            <div class="body">
                <div class="state <?php echo $esExito ? 'state-ok' : 'state-error'; ?>">
                    <?php echo $esExito ? 'OK' : '!'; ?>
                </div>
                <h2 style="margin:0 0 10px;font-size:24px;color:#111827;">
                    <?php echo $esExito ? 'Formulario recibido' : 'No se pudo completar'; ?>
                </h2>
                <p style="margin:0 0 24px;font-size:15px;color:#4b5563;">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
                <p style="margin:0;font-size:14px;color:#6b7280;">
                    Puede cerrar esta pestaña.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
