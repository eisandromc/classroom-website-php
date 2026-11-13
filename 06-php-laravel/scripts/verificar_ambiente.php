<?php
/**
 * Verificações e preparo do ambiente do WebShoes Laravel.
 * Chamado por iniciar.sh (Linux/macOS) e iniciar.bat (Windows).
 *
 * Uso: php scripts/verificar_ambiente.php <requisitos|composer|preparar|banco>
 *
 * Escrito com sintaxe compatível com PHP 7 de propósito: assim ele consegue
 * rodar numa versão antiga e avisar que é preciso atualizar, em vez de quebrar.
 */

define('RAIZ', dirname(__DIR__));
define('PHP_MINIMO', '8.3.0');

$comando = isset($argv[1]) ? $argv[1] : '';

switch ($comando) {
    case 'requisitos':
        exit(verificarRequisitos());
    case 'composer':
        exit(baixarComposer());
    case 'preparar':
        exit(prepararProjeto());
    case 'banco':
        exit(verificarBanco());
    default:
        fwrite(STDERR, "Uso: php scripts/verificar_ambiente.php <requisitos|composer|preparar|banco>\n");
        exit(1);
}

/* ------------------------------------------------------------------ */
/* Saída                                                              */
/* ------------------------------------------------------------------ */

function ok($msg)    { echo "  [OK]    $msg\n"; }
function aviso($msg) { echo "  [AVISO] $msg\n"; }
function falha($msg) { echo "  [ERRO]  $msg\n"; }
function dica($msg)  { echo "          $msg\n"; }

function perguntar($pergunta)
{
    if (function_exists('stream_isatty') && !stream_isatty(STDIN)) {
        return false;
    }
    echo "  $pergunta [s/N] ";
    $resposta = strtolower(trim((string) fgets(STDIN)));
    return in_array($resposta, array('s', 'sim', 'y', 'yes'), true);
}

function emWindows()
{
    return stripos(PHP_OS, 'WIN') === 0;
}

/* ------------------------------------------------------------------ */
/* 1. Requisitos: versão do PHP e extensões                           */
/* ------------------------------------------------------------------ */

function verificarRequisitos()
{
    $erros = 0;

    if (version_compare(PHP_VERSION, PHP_MINIMO, '>=')) {
        ok('PHP ' . PHP_VERSION . ' (' . PHP_BINARY . ')');
    } else {
        falha('PHP ' . PHP_VERSION . ' encontrado; o Laravel 13 exige PHP ' . PHP_MINIMO . ' ou superior.');
        dica('Atualize o PHP (ou o XAMPP) e rode o script de novo.');
        return 1;
    }

    // Extensões exigidas pelo Laravel + pdo_mysql para falar com o MySQL.
    $obrigatorias = array(
        'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'mbstring', 'openssl',
        'pdo', 'pdo_mysql', 'session', 'tokenizer', 'xml',
    );
    $faltando = array();
    foreach ($obrigatorias as $extensao) {
        if (!extension_loaded($extensao)) {
            $faltando[] = $extensao;
        }
    }
    if ($faltando) {
        falha('Extensões do PHP ausentes: ' . implode(', ', $faltando));
        $ini = php_ini_loaded_file();
        if (emWindows()) {
            dica('Abra o php.ini' . ($ini ? " ($ini)" : '') . ' e remova o ";" do início das linhas');
            dica('extension=' . implode(', extension=', $faltando) . ' e rode o script de novo.');
        } else {
            dica('Instale os pacotes correspondentes. Exemplos:');
            dica('  Ubuntu/Debian: sudo apt install php8.3-' . implode(' php8.3-', array_unique(array_map('pacoteLinux', $faltando))));
            dica('  macOS (Homebrew): brew install php   (já inclui as extensões)');
            if ($ini) {
                dica("php.ini em uso: $ini");
            }
        }
        $erros++;
    } else {
        ok('Extensões obrigatórias: ' . implode(', ', $obrigatorias));
    }

    // O Composer precisa de zip (ou unzip/7z) para extrair os pacotes.
    if (extension_loaded('zip')) {
        ok('Extensão zip (usada pelo Composer)');
    } elseif (!emWindows() && (programaNoPath('unzip') || programaNoPath('7z'))) {
        ok('unzip disponível (usado pelo Composer)');
    } else {
        aviso('Sem extensão zip nem unzip: o Composer pode falhar ao baixar os pacotes.');
        dica(emWindows()
            ? 'Habilite "extension=zip" no php.ini.'
            : 'Instale o unzip (ex.: sudo apt install unzip) ou a extensão php-zip.');
    }

    return $erros ? 1 : 0;
}

function pacoteLinux($extensao)
{
    $mapa = array(
        'pdo' => 'mysql', 'pdo_mysql' => 'mysql', 'dom' => 'xml', 'xml' => 'xml',
        'ctype' => 'common', 'fileinfo' => 'common', 'filter' => 'common',
        'tokenizer' => 'common', 'session' => 'common', 'openssl' => 'common',
    );
    return isset($mapa[$extensao]) ? $mapa[$extensao] : $extensao;
}

function programaNoPath($programa)
{
    foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $pasta) {
        if ($pasta !== '' && is_executable($pasta . DIRECTORY_SEPARATOR . $programa)) {
            return true;
        }
    }
    return false;
}

/* ------------------------------------------------------------------ */
/* 2. Composer: baixa composer.phar para a pasta do projeto           */
/* ------------------------------------------------------------------ */

function baixarComposer()
{
    if (!ini_get('allow_url_fopen')) {
        falha('allow_url_fopen está desativado no php.ini; não é possível baixar o Composer.');
        dica('Instale manualmente: https://getcomposer.org/download/');
        return 1;
    }

    echo "  Baixando o instalador oficial do Composer...\n";
    $assinatura = @file_get_contents('https://composer.github.io/installer.sig');
    $instalador = RAIZ . DIRECTORY_SEPARATOR . 'composer-setup.php';
    if ($assinatura === false || !@copy('https://getcomposer.org/installer', $instalador)) {
        falha('Não foi possível baixar o Composer (sem internet?).');
        dica('Instale manualmente: https://getcomposer.org/download/');
        return 1;
    }

    // Confere a assinatura publicada pelo Composer antes de executar o instalador.
    if (!hash_equals(trim($assinatura), hash_file('sha384', $instalador))) {
        @unlink($instalador);
        falha('Assinatura do instalador do Composer não confere; download descartado.');
        return 1;
    }

    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($instalador)
         . ' --quiet --install-dir=' . escapeshellarg(RAIZ) . ' --filename=composer.phar';
    passthru($cmd, $codigo);
    @unlink($instalador);

    if ($codigo !== 0 || !is_file(RAIZ . '/composer.phar')) {
        falha('O instalador do Composer terminou com erro.');
        return 1;
    }
    ok('composer.phar instalado na pasta do projeto.');
    return 0;
}

/* ------------------------------------------------------------------ */
/* 3. Preparar: .env, pastas graváveis e APP_KEY                      */
/* ------------------------------------------------------------------ */

function prepararProjeto()
{
    $env = RAIZ . '/.env';

    if (!is_file($env)) {
        if (!copy(RAIZ . '/.env.exemplo', $env)) {
            falha('Não foi possível criar o .env a partir de .env.exemplo.');
            return 1;
        }
        ok('.env criado a partir de .env.exemplo');
        dica('Confira DB_USERNAME e DB_PASSWORD no .env se o seu MySQL tiver senha.');
    } else {
        ok('.env já existe');
    }

    // Pastas que o Laravel precisa gravar (vazias, por isso não vêm no repositório).
    $pastas = array(
        'storage/app/public',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'bootstrap/cache',
        '../imagens/uploads', // uploads do painel admin, compartilhados com as outras versões
    );
    $erros = 0;
    foreach ($pastas as $relativa) {
        $pasta = RAIZ . '/' . $relativa;
        if (!is_dir($pasta) && !@mkdir($pasta, 0775, true)) {
            falha("Não foi possível criar a pasta $relativa");
            $erros++;
        } elseif (!is_writable($pasta)) {
            falha("Sem permissão de escrita em $relativa");
            dica(emWindows() ? 'Verifique as permissões da pasta.' : "Rode: chmod -R u+w $relativa");
            $erros++;
        }
    }
    if ($erros) {
        return 1;
    }
    ok('Pastas storage/, bootstrap/cache/ e ../imagens/uploads/ prontas');

    // Config em cache ignora o .env; limpa para as mudanças valerem.
    if (is_file(RAIZ . '/bootstrap/cache/config.php')) {
        artisan('config:clear');
    }

    $valores = lerEnv($env);
    if (!array_key_exists('APP_KEY', $valores)) {
        $conteudo = file_get_contents($env);
        $quebra = ($conteudo === '' || substr($conteudo, -1) === "\n") ? '' : PHP_EOL;
        file_put_contents($env, $conteudo . $quebra . 'APP_KEY=' . PHP_EOL);
        $valores['APP_KEY'] = '';
    }
    if ($valores['APP_KEY'] === '') {
        echo "  Gerando APP_KEY...\n";
        if (artisan('key:generate --ansi') !== 0) {
            falha('php artisan key:generate falhou.');
            return 1;
        }
    } else {
        ok('APP_KEY definida');
    }

    return 0;
}

function artisan($argumentos)
{
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(RAIZ . '/artisan') . ' ' . $argumentos;
    passthru($cmd, $codigo);
    return $codigo;
}

function lerEnv($arquivo)
{
    $valores = array();
    foreach (file($arquivo, FILE_IGNORE_NEW_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }
        list($chave, $valor) = explode('=', $linha, 2);
        $valor = trim($valor);
        if (strlen($valor) >= 2 && ($valor[0] === '"' || $valor[0] === "'") && substr($valor, -1) === $valor[0]) {
            $valor = substr($valor, 1, -1);
        }
        $valores[trim($chave)] = $valor;
    }
    return $valores;
}

/* ------------------------------------------------------------------ */
/* 4. Banco: conexão, banco webshoes e tabelas                        */
/* ------------------------------------------------------------------ */

function verificarBanco()
{
    $env = lerEnv(RAIZ . '/.env');
    $host   = isset($env['DB_HOST']) ? $env['DB_HOST'] : '127.0.0.1';
    $porta  = isset($env['DB_PORT']) ? $env['DB_PORT'] : '3306';
    $banco  = isset($env['DB_DATABASE']) ? $env['DB_DATABASE'] : 'webshoes';
    $usuario = isset($env['DB_USERNAME']) ? $env['DB_USERNAME'] : 'root';
    $senha  = isset($env['DB_PASSWORD']) ? $env['DB_PASSWORD'] : '';
    $socket = isset($env['DB_SOCKET']) ? $env['DB_SOCKET'] : '';

    $dsn = $socket !== ''
        ? "mysql:unix_socket=$socket;charset=utf8mb4"
        : "mysql:host=$host;port=$porta;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $usuario, $senha, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ));
    } catch (PDOException $e) {
        falha("Não foi possível conectar ao MySQL em $host:$porta como \"$usuario\".");
        dica('Motivo: ' . $e->getMessage());
        dica('Verifique se o MySQL/MariaDB está rodando (no XAMPP: botão Start do MySQL)');
        dica('e se DB_HOST, DB_PORT, DB_USERNAME e DB_PASSWORD no .env estão corretos.');
        return 1;
    }
    ok("Conectado ao MySQL em $host:$porta");

    $tabelasEsperadas = array('categorias', 'produtos', 'usuarios_administrativos');
    $consulta = $pdo->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?');
    $consulta->execute(array($banco));
    $existentes = array_intersect($tabelasEsperadas, $consulta->fetchAll(PDO::FETCH_COLUMN));

    $pastaSql = dirname(RAIZ) . '/00_banco';
    $estrutura = $pastaSql . '/estrutura.sql';
    $dados = $pastaSql . '/dados.sql';

    // Os scripts de 00_banco criam o banco "webshoes" com nome fixo.
    $podeImportar = ($banco === 'webshoes') && is_file($estrutura) && is_file($dados);

    if (count($existentes) === 0) {
        aviso("O banco \"$banco\" não existe ou ainda não tem as tabelas do WebShoes.");
        if (!$podeImportar) {
            dica('Importe ../00_banco/estrutura.sql e ../00_banco/dados.sql no seu MySQL e rode de novo.');
            return 1;
        }
        if (!perguntar('Criar agora importando ../00_banco/estrutura.sql e dados.sql?')) {
            dica('Importe ../00_banco/estrutura.sql e ../00_banco/dados.sql e rode de novo.');
            return 1;
        }
        if (!importarSql($pdo, $estrutura) || !importarSql($pdo, $dados)) {
            return 1;
        }
    } elseif (count($existentes) < count($tabelasEsperadas)) {
        falha("O banco \"$banco\" está incompleto. Faltam: " . implode(', ', array_diff($tabelasEsperadas, $existentes)));
        dica('Para não apagar dados, nada foi alterado. Confira o banco manualmente;');
        dica('em ambiente novo, reimporte ../00_banco/estrutura.sql e dados.sql.');
        return 1;
    } else {
        $pdo->exec('USE `' . str_replace('`', '``', $banco) . '`');
        $vazio = (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn() === 0
              && (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn() === 0;
        if ($vazio) {
            aviso("As tabelas existem, mas estão vazias.");
            if ($podeImportar && perguntar('Inserir os dados de demonstração de ../00_banco/dados.sql?')) {
                if (!importarSql($pdo, $dados)) {
                    return 1;
                }
            }
        }
    }

    $pdo->exec('USE `' . str_replace('`', '``', $banco) . '`');
    $categorias = (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
    $produtos = (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    $admins = (int) $pdo->query('SELECT COUNT(*) FROM usuarios_administrativos WHERE ativo = 1')->fetchColumn();
    ok("Banco \"$banco\": $categorias categorias, $produtos produtos, $admins usuário(s) admin ativo(s)");
    if ($admins === 0) {
        aviso('Nenhum usuário admin ativo: o login em /admin/login não vai funcionar.');
    }
    return 0;
}

function importarSql(PDO $pdo, $arquivo)
{
    echo '  Importando ' . basename($arquivo) . "...\n";
    try {
        foreach (separarComandosSql(file_get_contents($arquivo)) as $comando) {
            $pdo->exec($comando);
        }
    } catch (PDOException $e) {
        falha('Erro ao importar ' . basename($arquivo) . ': ' . $e->getMessage());
        return false;
    }
    ok(basename($arquivo) . ' importado');
    return true;
}

/** Divide um script SQL em comandos, respeitando textos entre aspas e comentários. */
function separarComandosSql($sql)
{
    $comandos = array();
    $atual = '';
    $tamanho = strlen($sql);
    for ($i = 0; $i < $tamanho; $i++) {
        $c = $sql[$i];
        $proximo = $i + 1 < $tamanho ? $sql[$i + 1] : '';

        if ($c === '-' && $proximo === '-' || $c === '#') {          // comentário de linha
            $fim = strpos($sql, "\n", $i);
            $i = $fim === false ? $tamanho : $fim;
            $atual .= "\n";
            continue;
        }
        if ($c === '/' && $proximo === '*') {                        // comentário de bloco
            $fim = strpos($sql, '*/', $i + 2);
            $i = $fim === false ? $tamanho : $fim + 1;
            continue;
        }
        if ($c === "'" || $c === '"' || $c === '`') {                // texto entre aspas
            $atual .= $c;
            for ($i++; $i < $tamanho; $i++) {
                $atual .= $sql[$i];
                if ($sql[$i] === '\\' && $c !== '`') {
                    $i++;
                    if ($i < $tamanho) {
                        $atual .= $sql[$i];
                    }
                } elseif ($sql[$i] === $c) {
                    break;
                }
            }
            continue;
        }
        if ($c === ';') {
            if (trim($atual) !== '') {
                $comandos[] = trim($atual);
            }
            $atual = '';
            continue;
        }
        $atual .= $c;
    }
    if (trim($atual) !== '') {
        $comandos[] = trim($atual);
    }
    return $comandos;
}
