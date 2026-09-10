@echo off
chcp 65001 >nul
title Antigravity Office 2D - Papelaria Real e Antigravity 2.0
color 0A

echo =====================================================================
echo   INICIALIZANDO O ANTIGRAVITY OFFICE 2D PIXEL ART (MRSTOCK ERP)
echo =====================================================================
echo.

cd /d "%~dp0"

:: Garante deteccao do Node.js mesmo se nao estiver no PATH padrao
where node >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\Program Files\nodejs\node.exe" (
        set "PATH=%PATH%;C:\Program Files\nodejs"
    ) else (
        echo [ERRO] Node.js nao foi encontrado no sistema.
        echo Por favor, instale o Node.js LTS para rodar o Antigravity Office.
        pause
        exit /b 1
    )
)

:: Instala dependencias se necessario (sem parenteses dentro de blocos if)
if not exist "node_modules\" (
    echo [INFO] Instalando dependencias locais: express e cors...
    call npm install
    echo.
)

:: Encerra processo anterior na porta 4444 se houver
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":4444 "') do (
    taskkill /f /pid %%a >nul 2>nul
)

:: Inicia o servidor em janela dedicada para observabilidade e logs
echo [INFO] Iniciando servidor Express na porta 4444...
start "Antigravity Office Server [Porta 4444]" cmd /k "title Antigravity Office Server [4444] && cd /d "%~dp0" && node server.js"

:: Aguarda o servidor subir
ping 127.0.0.1 -n 3 >nul

:: Abre o navegador padrao no Command Center
echo [INFO] Abrindo o Command Center no navegador padrao...
start "" "http://localhost:4444"

echo.
echo =====================================================================
echo  [OK] ANTIGRAVITY OFFICE ATIVO EM: http://localhost:4444
echo.
echo  A janela do servidor foi aberta separadamente para exibir os logs.
echo  Para encerrar o servidor, basta fechar a janela do servidor.
echo  Pressione qualquer tecla para fechar esta janela de boot.
echo =====================================================================
pause >nul
