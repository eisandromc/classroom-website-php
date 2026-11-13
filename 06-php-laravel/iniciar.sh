#!/usr/bin/env bash
# ------------------------------------------------------------------
# WebShoes Laravel - verifica o ambiente, prepara e inicia o projeto.
#
# Uso:  ./iniciar.sh               verifica, prepara e inicia o servidor
#       ./iniciar.sh --preparar    só verifica e prepara (não inicia)
#       PORTA=8080 ./iniciar.sh    usa outra porta (padrão: 8010)
# ------------------------------------------------------------------
set -euo pipefail

cd "$(dirname "$0")"

HOST="127.0.0.1"
PORTA="${PORTA:-8010}"
SO_PREPARAR=0
[ "${1:-}" = "--preparar" ] && SO_PREPARAR=1

etapa() { printf '\n== %s\n' "$1"; }
erro()  { printf '\nFalha: %s\n' "$1" >&2; exit 1; }

echo "WebShoes · Laravel — preparação do ambiente"

# 1. PHP --------------------------------------------------------------
etapa "1/6 PHP"
if command -v php >/dev/null 2>&1; then
    PHP="php"
elif [ -x /Applications/XAMPP/xamppfiles/bin/php ]; then
    PHP="/Applications/XAMPP/xamppfiles/bin/php"
elif [ -x /opt/lampp/bin/php ]; then
    PHP="/opt/lampp/bin/php"
else
    echo "  [ERRO]  PHP não encontrado."
    echo "          macOS:         brew install php"
    echo "          Ubuntu/Debian: sudo apt install php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip unzip"
    erro "instale o PHP 8.3+ e rode o script de novo."
fi
"$PHP" scripts/verificar_ambiente.php requisitos || erro "corrija os requisitos acima."

# 2. Composer ---------------------------------------------------------
etapa "2/6 Composer"
if command -v composer >/dev/null 2>&1; then
    COMPOSER=(composer)
    echo "  [OK]    $(composer --version 2>/dev/null | head -n 1)"
else
    if [ ! -f composer.phar ]; then
        echo "  Composer não encontrado no sistema; instalando localmente (composer.phar)."
        "$PHP" scripts/verificar_ambiente.php composer || erro "não foi possível instalar o Composer."
    fi
    COMPOSER=("$PHP" composer.phar)
    echo "  [OK]    $("$PHP" composer.phar --version 2>/dev/null | head -n 1)"
fi

# 3. Dependências (vendor/) -------------------------------------------
etapa "3/6 Dependências"
if [ ! -f vendor/autoload.php ]; then
    echo "  vendor/ não encontrado; executando composer install (pode demorar alguns minutos)..."
    "${COMPOSER[@]}" install --no-interaction || erro "composer install falhou."
elif [ -f composer.lock ] && [ composer.lock -nt vendor/composer/installed.json ]; then
    echo "  composer.lock mudou desde a última instalação; atualizando vendor/..."
    "${COMPOSER[@]}" install --no-interaction || erro "composer install falhou."
else
    echo "  [OK]    Dependências já instaladas"
fi

# 4. .env, pastas e APP_KEY -------------------------------------------
etapa "4/6 Configuração"
"$PHP" scripts/verificar_ambiente.php preparar || erro "não foi possível preparar o projeto."

# 5. Banco de dados ---------------------------------------------------
etapa "5/6 Banco de dados"
"$PHP" scripts/verificar_ambiente.php banco || erro "o banco de dados não está pronto."

# 6. Servidor ---------------------------------------------------------
etapa "6/6 Servidor"
if [ "$SO_PREPARAR" -eq 1 ]; then
    echo "  Ambiente pronto. Para iniciar: ./iniciar.sh"
    exit 0
fi
echo "  Loja:  http://$HOST:$PORTA/"
echo "  Admin: http://$HOST:$PORTA/admin/login  (admin / admin, apenas local)"
echo "  Pressione Ctrl+C para parar."
echo
exec "$PHP" artisan serve --host="$HOST" --port="$PORTA"
