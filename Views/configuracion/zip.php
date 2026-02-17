<?php
date_default_timezone_set('America/Asuncion');
$day=date("d");
$mont=date("m");
$year=date("Y");
$hora=date("H-i-s");
$fecha=$day.'_'.$mont.'_'.$year;
$nombre="Expedientes_".$fecha."_(".$hora."_hrs).zip";
        //$archivo_origen1 = "C:/xampp/htdocs/printec/Documentos/PrintecContratos/";
//Creamos el archivo
$zip = new \ZipArchive();

//abrimos el archivo y lo preparamos para agregarle archivos
if($zip->open("../../../Backup expedientes/".$nombre, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)){
      echo 'error abriendo zip';
}

//indicamos cual es la carpeta que se quiere comprimir
$origen = realpath('../../../Expedientes/');

//Ahora usando funciones de recursividad vamos a explorar todo el directorio y a enlistar todos los archivos contenidos en la carpeta
$files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($origen),
            \RecursiveIteratorIterator::LEAVES_ONLY
);

//Ahora recorremos el arreglo con los nombres los archivos y carpetas y se adjuntan en el zip
foreach ($files as $name => $file)
{
   if (!$file->isDir())
   {
       $filePath = $file->getRealPath();
       $relativePath = substr($filePath, strlen($origen) + 1);

       $zip->addFile($filePath, $relativePath);
   }
}

//Se cierra el Zip
$zip->close();
echo "<script>alert('Copia de seguridad realizada con éxito');window.history.back();</script>";
//echo 'Los archivos han sido comprimidos!';
