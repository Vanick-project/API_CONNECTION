@ECHO OFF
title Execution continue de l'URL

:LOOP
rem Exécutez votre URL ici en utilisant curl ou wget
curl -k https://127.0.0.1:8000

rem Attendez 60 secondes (vous pouvez ajuster le délai en secondes selon vos besoins)
timeout /t 600 /nobreak >nul

rem Retournez à l'étiquette :LOOP pour répéter le processus
goto :LOOP
