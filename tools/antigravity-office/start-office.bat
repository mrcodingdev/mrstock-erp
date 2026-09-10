@echo off
chcp 65001 >nul
title Antigravity Office 2D — Papelaria Real & Antigravity 2.0
color 0A

echo =====================================================================
echo  🏢 INICIALIZANDO O ANTIGRAVITY OFFICE 2D PIXEL ART (MRSTOCK ERP)
echo =====================================================================
echo.

cd /d "C:\xampp\htdocs\MrStock\tools\antigravity-office"

:: Verifica Node.js
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERRO] Node.js nao foi encontrado no PATH do sistema.
    echo Por favor, instale o Node.js LTS para rodar o Antigravity Office.
    pause
    exit /b 1
)

:: Instala dependencias se a pasta node_modules nao existir
if not exist "node_modules\" (
    echo [INFO] Instalando dependencias locais (express, cors)...
    call npm install
    echo.
)

:: Inicia o servidor em segundo plano e abre o navegador
echo [INFO] Iniciando micro-servidor Express na porta 4444...
start "" node server.js

timeout /t 2 /nobreak >nul

echo [INFO] Abrindo o Command Center no seu navegador padrao...
start http://localhost:4444

echo.
echo =====================================================================
echo  ✅ ANTIGRAVITY OFFICE ATIVO EM: http://localhost:4444
echo  Pressione qualquer tecla para fechar esta janela de inicializacao.
echo =====================================================================
pause >nul
