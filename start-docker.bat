@echo off
title Khoi dong WordPress Docker - WordpressC.local
cd /d "%~dp0"
echo ===================================================
echo   Dang khoi dong Docker cho WordpressC.local...
echo ===================================================
docker compose up -d
if %errorlevel% equ 0 (
    echo.
    echo Khoi dong thanh cong! Dang mo trinh duyet...
    start http://WordpressC.local
) else (
    echo.
    echo Co loi xay ra khi chay docker compose up -d
    pause
)
