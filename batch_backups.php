<?php
$dbhost       = "localhost";
$dbuser       = "root";
$dbpass       = "";
$dbport       = 3306;

$binaries     = "C:\Program Files\MySQL\MySQL Server 8.0\bin";
$creacion     = "C:\Users\jesus\Desktop";
$destination  = "C:\Users\jesus\Desktop\backups";
$date         = date("Y_m_d_H_i_s");
function verDb($host, $usuario, $pasword){
  $conn = new mysqli($host, $usuario, $pasword);
  if (!$conn->connect_error) {
      $tablas = $conn->query('SHOW DATABASES');
      return $tablas;    
  }else{
      die("Connection failed: " . $conn->connect_error);
  }
}

echo "CREACION DE ARCHIBO BATCH";
echo "<br/>";

$contenido = '@ECHO off

@REM Database information

SET dbhost='.$dbhost.'
SET dbuser='.$dbuser.'
SET dbpass='.$dbpass.'
SET dbport='.$dbport.'

@REM Paths
SET startdir=%cd%
SET binaries='.$binaries.'
SET destination='.$destination.'

@REM Dates
SET YY=%DATE:~-4%
SET MM=%DATE:~-7,2%
SET DD=%DATE:~-10,2%
SET DAYMONTHYEAR=%YY%_%MM%_%DD%
SET HOUR=%TIME:~0,2%

IF "%HOUR:~0,1%" == " " SET HOUR=0%HOUR:~1,1%
SET MINUTE=%time:~3,2%
SET SECOND=%time:~6,2%

SET date=%DAYMONTHYEAR%_%HOUR%_%MINUTE%_%SECOND%
ECHO "Fecha del respaldo"
ECHO %date%

@REM Final directory
IF NOT EXIST %destination%\%date%\temp mkdir "%destination%\%date%\temp"

@REM MySQL Binary files

CD %binaries%

';

$bd = verDb($dbhost, $dbuser, $dbpass);
if(!$bd){
  echo 'OCURRIO UN ERROR AL INTENTAR CONECTARSE';
  echo "<br/>";

}else {
  while ($fila = $bd->fetch_row()) {
    if($fila[0] != 'information_schema' AND $fila[0] != 'mysql' AND $fila[0] != 'performance_schema' ){
echo $fila[0].'<br>';
$contenido .= '	
mysqldump --set-gtid-purged=OFF --no-tablespaces --column-statistics=0 --host=%dbhost% --port=%dbport% --user=%dbuser% --password=%dbpass% '.$fila[0].' > %destination%\%date%\temp\\'.$fila[0].'.sql
ECHO "RESPALDADA CON EXITO BACKUP DE LA BASE DE DATOS %destination%\%date%\temp\\'.$fila[0].'.sql"
';
    }
  }          
    
$contenido .= '
@ECHO "Se ha completado el respaldo! :-O"


@ECHO "CREANDO ARCHIVO COMPRIMIDO"


"C:\Program Files\WinRAR\WinRAR.exe" a -ibck %destination%\%date%.rar "%destination%\%date%"

REM DEL %destination%\%date% /Q

@ECHO "SE CREO EL ARCHIVO COMPRIMIDO"


';                    
}

$contenido .= '
@pause
';

if($bd){
  $name     = 'backup_silpos.bat';  
  $enlace   = $creacion.'/'.$name;
  $miArchivo = fopen($enlace, "w+") or die("No se puede abrir/crear el archivo!");
  
  //Creamos una variable personalizada 
  fwrite($miArchivo, $contenido);
  fclose($miArchivo);
  
}
