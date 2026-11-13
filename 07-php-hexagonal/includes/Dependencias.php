<?php
/** Composition root: infraestrutura montada SOMENTE aqui. */
require_once __DIR__.'/ConexaoBanco.php';
require_once __DIR__.'/../aplicacao/ServicoProduto.php';
require_once __DIR__.'/../aplicacao/ServicoCategoria.php';
require_once __DIR__.'/../aplicacao/ServicoUsuario.php';
require_once __DIR__.'/../adaptadores/MySQL/RepositorioProdutoMySQL.php';
require_once __DIR__.'/../adaptadores/MySQL/RepositorioCategoriaMySQL.php';
require_once __DIR__.'/../adaptadores/MySQL/RepositorioUsuarioMySQL.php';
require_once __DIR__.'/../adaptadores/Arquivos/ArmazenamentoImagemLocal.php';
function conexaoHexagonal(): mysqli
{
    static $conexao = null;
    if (!$conexao) $conexao = (new ConexaoBanco())->conectar();
    return $conexao;
}
function produtoServico(): ServicoProduto
{
    return new ServicoProduto(new RepositorioProdutoMySQL(conexaoHexagonal()));
}
function categoriaServico(): ServicoCategoria
{
    return new ServicoCategoria(new RepositorioCategoriaMySQL(conexaoHexagonal()));
}
function usuarioServico(): ServicoUsuario
{
    return new ServicoUsuario(new RepositorioUsuarioMySQL(conexaoHexagonal()));
}
function imagemServico(): ArmazenamentoImagem
{
    return new ArmazenamentoImagemLocal();
}
