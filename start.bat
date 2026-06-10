@echo off
chcp 65001 >nul
title 拾光書架 - 啟動

echo ============================================
echo   拾光書架 Bookshelf of Memories
echo   一鍵啟動
echo ============================================
echo.

:: 切到批次檔所在目錄
cd /d "%~dp0"

:: ----- 1. 檢查 XAMPP -----
if not exist "C:\xampp\xampp_start.exe" (
    echo [錯誤] 找不到 XAMPP，請確認已安裝於 C:\xampp
    pause
    exit /b 1
)

:: ----- 2. 確保目錄聯接存在 -----
if not exist "C:\xampp\htdocs\20260610final" (
    echo [設定] 建立目錄聯接 C:\xampp\htdocs\20260610final → %cd%
    mklink /J "C:\xampp\htdocs\20260610final" "%cd%" >nul
    if errorlevel 1 (
        echo [錯誤] 無法建立目錄聯接，請用系統管理員身分執行
        pause
        exit /b 1
    )
)

:: ----- 3. 啟動 MySQL -----
echo [啟動] MySQL...
net start mysql >nul 2>&1
if errorlevel 1 (
    :: XAMPP 預設不安裝為 Windows service，改用 .bat
    "C:\xampp\mysql_start.bat" >nul 2>&1
)

:: ----- 4. 啟動 Apache -----
echo [啟動] Apache...
net start apache2.4 >nul 2>&1
if errorlevel 1 (
    "C:\xampp\apache_start.bat" >nul 2>&1
)

:: ----- 5. 等服務就緒 -----
echo [等待] 服務啟動中...
timeout /t 3 /nobreak >nul

:: ----- 6. 確認連線 -----
netstat -an | findstr ":80 " | findstr "LISTENING" >nul
if errorlevel 1 (
    echo [警告] Apache 可能未啟動，請檢查 C:\xampp\xampp-control.exe
)

:: ----- 7. 開啟瀏覽器 -----
echo.
echo ============================================
echo   啟動完成！
echo.
echo   網站首頁： http://localhost/20260610final/
echo   後台管理： http://localhost/20260610final/admin/
echo.
echo   測試帳號：
echo     管理員  admin  / admin123
echo     一般會員 reader / reader123
echo ============================================
echo.
start "" "http://localhost/20260610final/"

pause
