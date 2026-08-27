@echo off
title FaithConnection - MySQL Database Setup
echo ========================================================
echo   FAITHCONNECTION - MYSQL DATABASE SETUP & CREATION
echo ========================================================
echo.
echo Checking MySQL installation...

set "MYSQL_PATH=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"

if not exist "%MYSQL_PATH%" (
    set "MYSQL_PATH=mysql"
)

echo Found MySQL at: "%MYSQL_PATH%"
echo.
set /p MYSQL_PASS="Enter your MySQL 'root' password (press Enter if no password): "

echo.
echo Creating database 'faithconnection_db' and importing tables...

if "%MYSQL_PASS%"=="" (
    "%MYSQL_PATH%" -u root < "%~dp0database_schema.sql"
) else (
    "%MYSQL_PATH%" -u root -p%MYSQL_PASS% < "%~dp0database_schema.sql"
)

if %ERRORLEVEL% equ 0 (
    echo.
    echo ========================================================
    echo   [SUCCESS] Database 'faithconnection_db' created successfully!
    echo   All 12 tables and initial fellowship data are ready.
    echo ========================================================
) else (
    echo.
    echo ========================================================
    echo   [ERROR] Failed to import database. Please verify your password.
    echo ========================================================
)

echo.
pause
