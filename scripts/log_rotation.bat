@echo off
REM Log Rotation Batch Script for Windows
REM Runs PHP log rotation script

REM Set PHP and script paths
SET PHP_PATH=php
SET SCRIPT_PATH=%~dp0log_rotation.php

REM Run log rotation script
"%PHP_PATH%" "%SCRIPT_PATH%"

REM Check script exit status
IF %ERRORLEVEL% NEQ 0 (
    echo Log rotation failed with error code %ERRORLEVEL%
    exit /b %ERRORLEVEL%
) 