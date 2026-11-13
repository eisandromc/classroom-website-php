@echo off
chcp 65001 >nul
setlocal EnableExtensions
rem ------------------------------------------------------------------
rem WebShoes Laravel - verifica o ambiente, prepara e inicia o projeto.
rem
rem Uso:  iniciar.bat               verifica, prepara e inicia o servidor
rem       iniciar.bat --preparar    so verifica e prepara, nao inicia
rem       defina a variavel PORTA antes para usar outra porta, padrao 8010
rem ------------------------------------------------------------------

cd /d "%~dp0"

set "HOST=127.0.0.1"
if not defined PORTA set "PORTA=8010"
set "SO_PREPARAR=0"
if /i "%~1"=="--preparar" set "SO_PREPARAR=1"

echo WebShoes · Laravel — preparação do ambiente

rem 1. PHP --------------------------------------------------------------
echo.
echo == 1/6 PHP
set "PHP="
where php >nul 2>nul && set "PHP=php"
if not defined PHP if exist "C:\xampp\php\php.exe" set "PHP=C:\xampp\php\php.exe"
if not defined PHP goto :sem_php
"%PHP%" scripts\verificar_ambiente.php requisitos
if errorlevel 1 goto :falha

rem 2. Composer ---------------------------------------------------------
echo.
echo == 2/6 Composer
where composer >nul 2>nul
if errorlevel 1 goto :composer_local
set "COMPOSER=call composer"
goto :composer_ok

:composer_local
if exist composer.phar goto :composer_phar
echo   Composer não encontrado no sistema; instalando localmente: composer.phar
"%PHP%" scripts\verificar_ambiente.php composer
if errorlevel 1 goto :falha
:composer_phar
set "COMPOSER="%PHP%" composer.phar"

:composer_ok
%COMPOSER% --version
if errorlevel 1 goto :falha

rem 3. Dependencias - vendor ------------------------------------------
echo.
echo == 3/6 Dependências
if not exist vendor\autoload.php goto :instalar
if not exist composer.lock goto :dependencias_ok
if not exist vendor\composer\installed.json goto :instalar
"%PHP%" -r "exit(filemtime('composer.lock') > filemtime('vendor/composer/installed.json') ? 0 : 1);"
if errorlevel 1 goto :dependencias_ok

:instalar
echo   Executando composer install, pode demorar alguns minutos...
%COMPOSER% install --no-interaction
if errorlevel 1 goto :falha
goto :configuracao

:dependencias_ok
echo   [OK]    Dependências já instaladas

rem 4. .env, pastas e APP_KEY -------------------------------------------
:configuracao
echo.
echo == 4/6 Configuração
"%PHP%" scripts\verificar_ambiente.php preparar
if errorlevel 1 goto :falha

rem 5. Banco de dados ---------------------------------------------------
echo.
echo == 5/6 Banco de dados
"%PHP%" scripts\verificar_ambiente.php banco
if errorlevel 1 goto :falha

rem 6. Servidor ---------------------------------------------------------
echo.
echo == 6/6 Servidor
if "%SO_PREPARAR%"=="1" goto :pronto
echo   Loja:  http://%HOST%:%PORTA%/
echo   Admin: http://%HOST%:%PORTA%/admin/login  - admin / admin, apenas local
echo   Pressione Ctrl+C para parar.
echo.
"%PHP%" artisan serve --host=%HOST% --port=%PORTA%
goto :fim

:pronto
echo   Ambiente pronto. Para iniciar: iniciar.bat
goto :fim

:sem_php
echo   [ERRO]  PHP não encontrado no PATH nem em C:\xampp\php\php.exe.
echo           Instale o XAMPP com PHP 8.3+ ou o PHP para Windows: https://windows.php.net/download/
echo           e adicione a pasta do php.exe ao PATH.

:falha
echo.
echo Falha: corrija o problema indicado acima e rode iniciar.bat de novo.
pause
endlocal
exit /b 1

:fim
endlocal
exit /b 0
