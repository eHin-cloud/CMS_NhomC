@echo off
:: Kiem tra quyen Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Dang yeu cau quyen Administrator de chinh sua file hosts...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo Dang cap nhat file C:\Windows\System32\drivers\etc\hosts ...
findstr /i "WordpressC.local" C:\Windows\System32\drivers\etc\hosts >nul
if %errorLevel% neq 0 (
    echo.>> C:\Windows\System32\drivers\etc\hosts
    echo 127.0.0.1   WordpressC.local>> C:\Windows\System32\drivers\etc\hosts
    echo 127.0.0.1   www.WordpressC.local>> C:\Windows\System32\drivers\etc\hosts
    echo Da them thanh cong WordpressC.local vao file hosts!
) else (
    echo WordpressC.local da ton tai trong file hosts.
)

ipconfig /flushdns
echo.
echo ==============================================
echo DA CAU HINH XONG VIRTUAL HOST CHO WINDOWS!
echo ==============================================
pause
