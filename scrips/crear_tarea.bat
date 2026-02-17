setlocal
REM --- Configuración de la Tarea ---
SET TASK_NAME="SCANTEC - Envío de Alertas Programadas"
SET TASK_DESCRIPTION="Ejecuta el script de PHP para procesar alertas y enviar informes."
REM Obtiene la ruta completa al script que debe ejecutar la tarea.
REM %~dp0 es la ruta del directorio donde se está ejecutando este .bat
SET TASK_RUNNER_BAT="%~dp0ejecutar_tareas.bat"

echo ==========================================================
echo    Asistente de Creacion de Tarea Programada (PHP MVC)
echo ==========================================================
echo.
echo Este script creara la siguiente tarea:
echo.
echo   Nombre: %TASK_NAME%
echo   Accion: %TASK_RUNNER_BAT%
echo   Horario: Cada 1 hora, todos los dias.
echo   Usuario: SISTEMA (NT AUTHORITY\SYSTEM)
echo   Privilegios: Mas altos.
echo.

REM -----------------------------------------------------------------
REM  Comprobar si se está ejecutando como Administrador
REM -----------------------------------------------------------------
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Este script debe ejecutarse como Administrador.
    echo.
    echo Por favor, haz clic derecho sobre "crear_tarea.bat" y selecciona:
    echo "Ejecutar como administrador".
    echo.
    pause
    goto :eof
) else (
    echo [OK] Privilegios de Administrador detectados.
)

echo.
echo Intentando crear la tarea...
echo.

REM -----------------------------------------------------------------
REM  Comando para crear la Tarea Programada
REM -----------------------------------------------------------------
schtasks /create ^
    /F ^
    /TN %TASK_NAME% ^
    /TR %TASK_RUNNER_BAT% ^
    /SC HOURLY ^
    /MO 1 ^
    /ST 00:00 ^
    /RL HIGHEST ^
    /RU "NT AUTHORITY\SYSTEM" ^
    /D %TASK_DESCRIPTION%

REM -----------------------------------------------------------------
REM  Verificar el resultado
REM -----------------------------------------------------------------
if %errorlevel% equ 0 (
    echo.
    echo [EXITO] La tarea %TASK_NAME% se ha creado correctamente.
    echo.
    echo.
    echo [IMPORTANTE] Por favor, realiza una verificacion manual:
    echo 1. Abre el "Programador de Tareas" de Windows.
    echo 2. Busca la tarea "MiApp - Envío de Alertas Programadas".
    echo 3. Ve a la pestana "Condiciones".
    echo 4. Asegurate de que la opcion "Iniciar la tarea solo si el equipo esta conectado a la corriente alterna" ESTE DESMARCADA.
    echo    (Este script no puede modificar esa opcion, es la unica verificacion manual).
) else (
    echo.
    echo [ERROR] No se pudo crear la tarea. Codigo de error: %errorlevel%
    echo Asegurate de que estas ejecutando este script como Administrador.
)

echo.
echo Presiona cualquier tecla para salir...
pause >nul

