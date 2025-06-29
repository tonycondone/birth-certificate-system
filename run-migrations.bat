@echo off
echo Running Database Migrations...
echo.

REM Get database credentials from .env file
for /f "tokens=1,2 delims==" %%a in (.env) do (
    if "%%a"=="DB_HOST" set DB_HOST=%%b
    if "%%a"=="DB_NAME" set DB_NAME=%%b
    if "%%a"=="DB_USER" set DB_USER=%%b
    if "%%a"=="DB_PASS" set DB_PASS=%%b
)

echo Database Configuration:
echo Host: %DB_HOST%
echo Database: %DB_NAME%
echo User: %DB_USER%
echo.

REM Run each migration file
for %%f in (database\migrations\*.sql) do (
    echo Running migration: %%f
    mysql -h%DB_HOST% -u%DB_USER% -p%DB_PASS% %DB_NAME% < "%%f"
    if !errorlevel! neq 0 (
        echo Error running migration: %%f
        pause
        exit /b 1
    )
    echo Migration completed: %%f
    echo.
)

echo All migrations completed successfully!
echo.
pause 