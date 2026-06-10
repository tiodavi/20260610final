@echo off
chcp 65001 >nul
title 拾光書架 - 停止

echo ============================================
echo   停止 XAMPP 服務
echo ============================================
echo.

net stop apache2.4 >nul 2>&1
"C:\xampp\apache_stop.bat" >nul 2>&1
net stop mysql >nul 2>&1
"C:\xampp\mysql_stop.bat" >nul 2>&1

echo.
echo 已停止。
pause
