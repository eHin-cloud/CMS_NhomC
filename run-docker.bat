@echo off
title Quan Ly WordPress Docker - WordpressC.local
cd /d "%~dp0"
chcp 65001 >nul

:MENU
cls
echo ===================================================================
echo               QUẢN LÝ DOCKER - WORDPRESSC.LOCAL
echo ===================================================================
echo.
echo   [1] Khởi động Docker (Start) ^& Mở trang web http://WordpressC.local
echo   [2] Dừng tất cả container (Stop)
echo   [3] Khởi động lại (Restart)
echo   [4] Xem trạng thái các container (Status)
echo   [5] Xem logs theo dõi lỗi (Logs)
echo   [6] Mở trang quản trị Database PhpMyAdmin
echo   [0] Thoát
echo.
echo ===================================================================
set "choice="
set /p choice="Nhập lựa chọn của bạn [Mặc định nhấn Enter chọn 1]: "
if "%choice%"=="" set choice=1

if "%choice%"=="1" goto START_DOCKER
if "%choice%"=="2" goto STOP_DOCKER
if "%choice%"=="3" goto RESTART_DOCKER
if "%choice%"=="4" goto STATUS_DOCKER
if "%choice%"=="5" goto LOGS_DOCKER
if "%choice%"=="6" goto OPEN_PMA
if "%choice%"=="0" exit /b
echo Lựa chọn không hợp lệ! Vui lòng chọn lại.
timeout /t 2 >nul
goto MENU

:START_DOCKER
echo.
echo [+] Đang kiểm tra Docker Desktop...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [LỖI] Docker Desktop chưa bật hoặc chưa khởi động xong!
    echo Vui lòng mở ứng dụng Docker Desktop rồi thử lại.
    echo.
    pause
    goto MENU
)

echo [+] Đang khởi động các dịch vụ (db, wordpress, phpmyadmin)...
docker compose up -d
if %errorlevel% neq 0 (
    echo.
    echo [LỖI] Khởi động thất bại. Hãy đảm bảo cổng 80 không bị XAMPP chiếm dụng!
    pause
    goto MENU
)

echo.
echo ===================================================================
echo [THÀNH CÔNG] Môi trường WordPress Docker đã chạy sẵn sàng!
echo - Website:       http://WordpressC.local
echo - Quản trị:      http://WordpressC.local/wp-admin
echo - PhpMyAdmin:    http://localhost:8081
echo ===================================================================
echo.
echo Đang mở trình duyệt đến http://WordpressC.local ...
start http://WordpressC.local
echo.
pause
goto MENU

:STOP_DOCKER
echo.
echo [+] Đang dừng các container...
docker compose stop
echo [ĐÃ DỪNG] Toàn bộ container đã được tạm dừng an toàn.
echo.
pause
goto MENU

:RESTART_DOCKER
echo.
echo [+] Đang khởi động lại các container...
docker compose restart
echo [ĐÃ XONG] Các container đã được khởi động lại!
echo.
pause
goto MENU

:STATUS_DOCKER
echo.
echo [+] Trạng thái hiện tại của các container:
echo.
docker compose ps
echo.
pause
goto MENU

:LOGS_DOCKER
echo.
echo [+] Đang tải logs (Nhấn Ctrl + C để dừng xem logs)...
docker compose logs -f --tail 50
goto MENU

:OPEN_PMA
echo.
echo [+] Đang mở PhpMyAdmin...
start http://localhost:8081
goto MENU
