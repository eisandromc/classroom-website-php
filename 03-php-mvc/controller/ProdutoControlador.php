<?php
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../model/Categoria.php';

class ProdutoControlador
{
    public function inicio(): void
    {
        $erro = null;
        $modeloCategoria = new Categoria();
        $categorias = $modeloCategoria->listar();
        $busca = trim((string)($_GET['busca'] ?? ''));
        $ordem = (string)($_GET['ordem'] ?? 'destaques');
        $produtos = (new Produto())->listar(null, $busca, $ordem);
        $tituloDocumento = 'WebShoes MVC — Seu passo, suas regras';
        $pagina = 'inicio';
        require __DIR__ . '/../view/layout.php';
    }

    public function detalhe(int $codigo): void
    {
        $erro = null;
        $categorias = (new Categoria())->listar();
        $produto = $codigo > 0 ? (new Produto())->buscarPorCodigo($codigo) : null;
        if (!$produto) http_response_code(404);
        $tituloDocumento = ($produto['nome'] ?? 'Produto') . ' | WebShoes MVC';
        $pagina = 'produto';
        require __DIR__ . '/../view/layout.php';
    }
}
