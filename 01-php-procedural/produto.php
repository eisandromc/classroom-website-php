<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/Consultas.php';
$codigo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$produto = null;
$categorias = [];
$erro = null;
try {
    $categorias = listarCategorias();
    if ($codigo > 0) $produto = buscarProduto($codigo);
    if (!$produto) http_response_code(404);
} catch (Throwable $excecao) { error_log($excecao->getMessage()); http_response_code(503); $erro = 'Não foi possível carregar os detalhes. Verifique a conexão.'; }
$linkCategoria = fn(int $id): string => 'categoria.php?id=' . $id;
$linkProduto = fn(int $id): string => 'produto.php?id=' . $id;
$inicio = 'index.php';
$tituloDocumento = ($produto['nome'] ?? 'Produto') . ' | WebShoes';
include __DIR__ . '/includes/cabecalho.php';
?>
<section class="conteiner pagina-produto">
    <div class="trilha"><a href="index.php">Início</a><span>/</span>
        <?php if ($produto): ?><a href="<?= escapar($linkCategoria((int)$produto['categoria_id'])) ?>"><?= escapar($produto['categoria_nome']) ?></a><span>/</span><span><?= escapar($produto['nome']) ?></span><?php else: ?><span>Produto</span><?php endif; ?></div>
    <?php if ($erro): ?><div class="aviso aviso-erro" role="alert"><?= escapar($erro) ?></div><?php endif; ?>
    <?php if (!$produto && !$erro): ?><div class="vazio"><h2>Produto não encontrado.</h2><a href="index.php" class="link-simples">Voltar à coleção</a></div><?php endif; ?>
    <?php if ($produto): ?>
    <div class="detalhe-grid">
        <div class="detalhe-arte"><img src="<?= escapar(enderecoImagem($produto['imagem'])) ?>" alt="Tênis <?= escapar($produto['nome']) ?>">
        <?php if ($produto['etiqueta']): ?><span class="tag detalhe-art-tag"><?= escapar($produto['etiqueta']) ?></span><?php endif; ?></div>
        <div class="detalhe-info">
            <span class="marca"><?= escapar($produto['marca']) ?> / <?= escapar($produto['categoria_nome']) ?></span>
            <h1><?= escapar($produto['nome']) ?></h1>
            <p class="descricao-curta"><?= escapar($produto['descricao_curta']) ?></p>
            <div class="preco-detalhe"><span class="preco"><?= precoReais($produto['preco']) ?></span>
            <?php if ($produto['preco_anterior'] !== null): ?><span class="preco-antigo"><?= precoReais($produto['preco_anterior']) ?></span><?php endif; ?></div>
            <div class="linha"></div><div class="rotulo-detalhe">Cor <span><?= escapar($produto['cor']) ?></span></div>
            <div class="rotulo-detalhe" style="margin-top:27px">Selecione o tamanho <span>Numeração BR</span></div>
            <div class="tamanhos" role="group" aria-label="Escolha o tamanho">
            <?php foreach (explode(',', $produto['tamanhos']) as $tamanho): ?><button class="tamanho" type="button" data-tamanho="<?= escapar(trim($tamanho)) ?>" aria-pressed="false"><?= escapar(trim($tamanho)) ?></button><?php endforeach; ?>
            </div>
            <div id="dados-produto" data-id="<?= (int)$produto['id'] ?>" data-nome="<?= escapar($produto['nome']) ?>" data-preco="<?= escapar($produto['preco']) ?>" data-imagem="<?= escapar(enderecoImagem($produto['imagem'])) ?>"></div>
            <button class="botao-primario comprar" type="button" id="adicionar-sacola">Adicionar à sacola <span aria-hidden="true">↗</span></button>
            <div class="linha"></div><p class="descr-longa"><?= escapar($produto['descricao']) ?></p>
            <div class="beneficios"><div><svg viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></svg> Produto fictício para demonstração</div><div><svg viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></svg> Sacola local, sem finalização de compra</div></div>
        </div>
    </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/rodape.php'; ?>
