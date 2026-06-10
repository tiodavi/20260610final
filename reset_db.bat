@echo off
chcp 65001 >nul
title 重建資料庫

echo.
echo ============================================
echo   ⚠  重建資料庫 (會清空所有資料)
echo ============================================
echo.
echo  這會刪除 bookshelf_db 資料庫裡所有資料，
echo  然後用 sql/init.sql 重建。
echo.
set /p CONFIRM=確定要繼續嗎？ (yes/no):

if /i not "%CONFIRM%"=="yes" goto :end

cd /d "%~dp0"
"C:\xampp\mysql\bin\mysql.exe" -u root --default-character-set=utf8mb4 < "sql\init.sql"
if errorlevel 1 (
    echo.
    echo [錯誤] 重建失敗，請確認 MySQL 已啟動
) else (
    echo.
    echo ✓ 重建完成
    echo   管理員帳號： admin / admin123
    echo   一般會員帳號： reader / reader123
)

:end
echo.
pause
