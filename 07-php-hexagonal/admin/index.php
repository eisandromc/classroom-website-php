<?php
// Versão 01: processamento de consultas e HTML integrado no mesmo fluxo PHP.
require_once __DIR__ . '/../includes/funcoes.php';
require_once __DIR__ . '/../includes/Autenticacao.php';
iniciarSessaoAdmin('webshoes_admin_12');
$autenticado = administradorAutenticado();
$token = tokenFormulario();
$mensagem = (string)($_SESSION['mensagem'] ?? '');
$erroMensagem = (string)($_SESSION['erro_mensagem'] ?? '');
unset($_SESSION['mensagem'], $_SESSION['erro_mensagem']);
$tipo = (($_GET['tipo'] ?? '') === 'categorias') ? 'categorias' : 'produtos';
$acao = (($_GET['acao'] ?? '') === 'formulario') ? 'formulario' : 'listar';
$registro = [];
$produtos = []; $categorias = [];
$urlPainel = 'index.php'; $urlProcessar='processar.php'; $urlLoja='../index.php'; $prefixoImagens='../';
if ($autenticado) {
    require_once __DIR__ . '/../includes/Dependencias.php';
    try {
        $produtos = $tipo === 'produtos' && $acao !== 'formulario' ? produtoServico()->listarAdministracao() : [];
        $categorias = categoriaServico()->listarAdministracao();
        $codigo = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT) ?: 0;
        if ($acao === 'formulario' && $codigo > 0) {
            $registro = $tipo === 'produtos' ? produtoServico()->buscarParaEditar($codigo) : categoriaServico()->buscarParaEditar($codigo);
            if (!$registro) { $erroMensagem='Registro não encontrado.'; $acao='listar'; }
        }
    } catch (Throwable $erro) {
        error_log($erro->getMessage());
        $erroMensagem='Falha ao consultar o banco. Confira a instalação e conexão MySQL.';
    }
}
require __DIR__ . '/painel.php';
