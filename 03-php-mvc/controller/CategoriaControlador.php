<?php
require_once __DIR__ . '/../model/Categoria.php';
require_once __DIR__ . '/../model/Produto.php';

class CategoriaControlador
{
    public function listarProdutos(int $codigo): void
    {
        $erro = null;
        $modeloCategoria = new Categoria();
        $categorias = $modeloCategoria->listar();
        $categoriaAtual = $codigo > 0 ? $modeloCategoria->buscarPorCodigo($codigo) : null;
        if (!$categoriaAtual) http_response_code(404);
        $busca = trim((string)($_GET['busca'] ?? ''));
        $ordem = (string)($_GET['ordem'] ?? 'destaques');
        $produtos = $categoriaAtual ? (new Produto())->listar($codigo, $busca, $ordem) : [];
        $tituloDocumento = ($categoriaAtual['nome'] ?? 'Categoria') . ' | WebShoes MVC';
        $pagina = 'categoria';
        require __DIR__ . '/../view/layout.php';
    }
}
