<?php
// Variáveis esperadas: $inicio, $tituloDocumento, $categorias.
$inicio = $inicio ?? 'index.php';
$tituloDocumento = $tituloDocumento ?? 'WebShoes — Tênis com atitude';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f7f3">
    <meta name="description" content="WebShoes: loja demonstrativa de tênis lifestyle, corrida, basquete e skate.">
    <title><?= escapar($tituloDocumento) ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<div class="barra-topo">FRETE GRÁTIS FICTÍCIO A PARTIR DE R$ 399 · LOJA DEMONSTRATIVA SEM PAGAMENTO</div>
<header class="cabecalho">
    <div class="conteiner cabecalho-miolo">
        <a href="<?= escapar($inicio) ?>" class="logo" aria-label="WebShoes, página inicial"><span class="logo-marca">W</span><span>WEB<em>SHOES</em></span></a>
        <nav class="navegacao" id="navegacao" aria-label="Navegação principal">
            <a href="<?= escapar($inicio) ?>#colecao">Coleção</a>
            <?php foreach (array_slice($categorias, 0, 4) as $item): ?>
            <a href="<?= escapar($linkCategoria((int)$item['id'])) ?>"><?= escapar($item['nome']) ?></a>
            <?php endforeach; ?>
            <a href="admin/index.php">Admin</a>
        </nav>
        <div class="acoes-cabecalho">
            <a href="<?= escapar($inicio) ?>#colecao" class="botao-icone" title="Ir para a coleção" aria-label="Ir para a coleção">
                <svg viewBox="0 0 24 24"><circle cx="10.7" cy="10.7" r="6.7"/><path d="m16 16 5 5"/></svg>
            </a>
            <button class="botao-icone" type="button" id="abrir-sacola" aria-label="Abrir sacola">
                <svg viewBox="0 0 24 24"><path d="M4 8h16l-1.2 13H5.2L4 8Z"/><path d="M8 9V6a4 4 0 0 1 8 0v3"/></svg>
                <span class="contador-sacola" id="contador-sacola">0</span>
            </button>
            <button class="botao-icone icone-menu" type="button" id="menu-mobile" aria-label="Abrir menu" aria-expanded="false" aria-controls="navegacao">
                <svg viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>
</header>
<main id="conteudo">
