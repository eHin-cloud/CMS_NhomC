@echo off
title Dung Apache Local de giai phong cong 80
:: Kiem tra quyen Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Dang yeu cau quyen Administrator de dung dich vu Apache...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo ===================================================
echo   Dang dung dich vu Apache2.4 tren Windows...
echo ===================================================
net stop Apache2.4

echo.
echo ===================================================
echo   Khoi dong lai container Docker WordPress...
echo ===================================================
cd /d "%~dp0"
docker compose restart wordpress

echo.
echo [XONG] Da giai phong cong 80 cho Docker!
echo Ban co the truy cap lai: http://WordpressC.local
echo.
pause
