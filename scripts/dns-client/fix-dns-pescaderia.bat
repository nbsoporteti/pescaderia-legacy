@echo off
chcp 65001 >nul
title Reparar acceso - Pescaderia

:: Elevar a administrador si hace falta
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo Solicitando permisos de administrador...
    powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0fix-dns-pescaderia.ps1"
exit /b %errorlevel%
