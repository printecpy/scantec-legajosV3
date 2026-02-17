@echo off
REM Obtener la ruta del directorio donde se encuentra este .bat
SET BAT_PATH=%~dp0

REM -----------------------------------------------------------------
REM >> APLICAR LA RUTA ABSOLUTA DEL EJECUTABLE PHP (MENOS PROPENSA A FALLOS) <<
REM -----------------------------------------------------------------
SET PHP_EXE="C:\xampp\php\php.exe" 

REM -----------------------------------------------------------------
REM >> CORREGIR LA RUTA DEL SCRIPT: USAR LA RUTA COMPLETA DE LA RAÍZ <<
REM -----------------------------------------------------------------
REM La ruta de tu proyecto es C:\xampp\htdocs\scantec2
SET SCRIPT_PATH="C:\xampp\htdocs\scantec2\index.php"

REM El comando a ejecutar: "controlador metodo"
SET CI_COMMAND="Alerta ejecutarPendientes()"

REM -----------------------------------------------------------------
REM >> IMPRIMIR Y PAUSAR ANTES DE LA EJECUCIÓN DEL COMANDO PHP <<
REM -----------------------------------------------------------------
echo --------------------------------------------------
echo VERIFICACION DE RUTAS:
echo PHP EXE: %PHP_EXE%
echo SCRIPT PATH: %SCRIPT_PATH%
echo COMANDO CI: %CI_COMMAND%
echo --------------------------------------------------
pause

REM El comando a ejecutar: "controlador" "metodo"
SET CI_COMMAND="Alerta" "ejecutarPendientes" 
REM O simplemente:
SET CI_COMMAND=Alerta ejecutarPendientes

REM Ejecutar el comando (dejar como estaba)
echo Ejecutando tareas programadas...
%PHP_EXE% %SCRIPT_PATH% %CI_COMMAND%
pause