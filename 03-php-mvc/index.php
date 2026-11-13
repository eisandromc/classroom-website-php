<?php
/** Front controller: escolhe qual controlador executar; classes não decidem rotas. */
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/controller/ProdutoControlador.php';
require_once __DIR__ . '/controller/CategoriaControlador.php';
require_once __DIR__ . '/controller/AdministracaoControlador.php';

$paginaSolicitada = (string)($_GET['pagina'] ?? 'inicio');
$codigo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
try {
    switch ($paginaSolicitada) {
        case 'inicio': (new ProdutoControlador())->inicio(); break;
        case 'admin': (new AdministracaoControlador())->exibir(); break;
        case 'admin-acao': (new AdministracaoControlador())->processar(); break;
        case 'produto': (new ProdutoControlador())->detalhe($codigo); break;
        case 'categoria': (new CategoriaControlador())->listarProdutos($codigo); break;
        default: http_response_code(404); echo 'Página não encontrada. <a href="index.php">Voltar</a>';
    }
} catch (Throwable $excecao) {
    error_log($excecao->getMessage());
    http_response_code(503);
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>WebShoes</title><body style="font-family:system-ui;padding:3rem"><h1>Não foi possível carregar a loja.</h1><p>Confira o MySQL e includes/ConexaoBanco.php.</p><a href="index.php">Tentar novamente</a></body></html>';
}
