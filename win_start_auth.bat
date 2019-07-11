@echo off
REM version:v1.01.11
REM time:2019.04.11
REM author:Changzong.Fan

REM ÉèÖÃ¸üÄ¿Â¼
set "path_addr=%~dp0"

echo Starting auth...
start /b %path_addr%cygdrive/bin/php.exe script Server/start auth

pause