@echo off
:rerun
cls
php.exe translator.php
cls
color 2
echo Successful
pause
goto rerun;