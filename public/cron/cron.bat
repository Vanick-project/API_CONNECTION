@ECHO OFF
title ADM DASHBOARD

 

rem position into the working directory
cd "C:\wamp64\www\API_CONNECTION\public\cron"
echo %cd%

 

 

rem code to format and write into logfile
for /f "delims=" %%a in ('wmic OS Get localdatetime  ^| find "."') do set dt=%%a
set datestamp=%dt:~0,8%
set timestamp=%dt:~8,6%
set YYYY=%dt:~0,4%
set MM=%dt:~4,2%
set DD=%dt:~6,2%
set HH=%dt:~8,2%
set Min=%dt:~10,2%
set Sec=%dt:~12,2%
set stamp=%YYYY%-%MM%-%DD%_%HH%:%Min%:%Sec%
set obj=Dashboard_data
echo stamp: "%stamp%"
echo datestamp: "%datestamp%"
echo timestamp: "%timestamp%"

 

rem selecting the log folder
cd "C:\wamp64\www\API_CONNECTION\public\cron"

 

rem to show the working file
echo %cd%

 

set DateTimeFile=%stamp%.log
set logMessage=Producing ADM DATA FIRST FILE...

 

rem cron codes extract et sftp
@REM curl http://localhost/api/api_db_ccoa/adm_ccoa.php
curl -k https://127.0.0.1:8000 

 

echo %logMessage%_%stamp%>>%obj%_%YYYY%_%MM%_%DD%.log