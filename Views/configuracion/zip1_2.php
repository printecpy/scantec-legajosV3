<?php
// Ruta al ejecutable de Cobian Backup (ajústala según tu instalación)
$rutaCobian = 'C:\/Program Files (x86)\/Cobian Backup 11\/cbInterface.exe';

// Comando para abrir la interfaz de Cobian Backup
$comando = "\"$rutaCobian\"";

// Ejecuta el comando
shell_exec($comando);

echo "<script>alert('Se abrirá Cobian Backup!');window.history.back();</script>";
