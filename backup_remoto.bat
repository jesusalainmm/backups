@ECHO off

@REM Script de Windows Batch para hacer una copia de seguridad de todas las bases de datos mysql en un servidor

@REM Database information
SET dbhost="127.0.0.1"
SET dbuser="root"
SET dbpass=""
SET dbport=3306

@REM Paths
SET startdir=%cd%
@REM Ruta donde esta el executable de mysql.exe y mysqldump.exe
SET binaries=C:\Program Files\MySQL\MySQL Server 8.0\bin
@REM Ruta destino de las copias de seguridad de todas las bases de datos mysql en un servidor
SET destination=C:\Users\jesus\Desktop\backups

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
@ECHO "Fecha del respaldo"
@ECHO %date%

@REM Creacion de la carpeta si no existe
IF NOT EXIST %destination%\%date% mkdir %destination%\%date%

@REM MySQL Binary files

cd %binaries%
@REM Consultar todas las bases de datos
mysql.exe  --host=%dbhost% --port=%dbport% --user=%dbuser% --password=%dbpass% -s -N -e "SHOW DATABASES" | for /F "usebackq" %%D in (`findstr /V "information_schema performance_schema mysql test"`) do (
    :: @ECHO "BASE DE DATOS %%D "
    :: Creando copia de la base de datos
    mysqldump --no-tablespaces --host=%dbhost% --port=%dbport% --default-character-set=utf8 --user=%dbuser% --protocol=tcp --compress=TRUE --single-transaction=TRUE --routines --events --column-statistics=0 --password=%dbpass% %%D > %destination%\%date%\%%D.sql
)
    

@ECHO "Se ha completado el respaldo!"

cd %startdir%

@pause
