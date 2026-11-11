@echo off
echo ========================================================
echo   Starting Online Registration Web Application
echo   URL: http://localhost:8000
echo ========================================================
echo.

IF EXIST "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8000
) ELSE (
    php -S localhost:8000
)
pause
