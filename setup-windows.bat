@echo off
setlocal

cd /d "%~dp0"

echo Restauracja 2026 setup
echo.
echo This launcher runs scripts\setup-windows.ps1.
echo If your PostgreSQL user has a password, pass it like this:
echo setup-windows.bat -DbUsername postgres -DbPassword "your_password"
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\setup-windows.ps1" %*

if errorlevel 1 (
    echo.
    echo Setup failed. Read the error above, fix the problem, and run this file again.
    pause
    exit /b 1
)

echo.
echo Setup finished successfully.
pause
