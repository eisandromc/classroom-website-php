<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/includes/Dependencias.php';
$codigo = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($codigo < 1) { http_response_code(404); }
$erro = null;
$categoriaAtual = null;
$produtos = [];
$categorias = [];
$busca = trim((string)($_GET['busca'] ?? ''));
$ordem = (string)($_GET['ordem'] ?? 'destaques');
try {
    $modeloCategoria = categoriaServico();
    $categorias = $modeloCategoria->listar();
    if ($codigo > 0) $categoriaAtual = $modeloCategoria->buscarPorCodigo($codigo);
    if ($categoriaAtual) $produtos = produtoServico()->listar($codigo, $busca, $ordem);
    else http_response_code(404);
} catch (Throwable $excecao) { error_log($excecao->getMessage()); $erro = 'Erro de conexão ao consultar a categoria.'; http_response_code(503); }
$linkCategoria = fn(int $id): string => 'categoria.php?id=' . $id;
$linkProduto = fn(int $id): string => 'produto.php?id=' . $id;
$inicio = 'index.php';
$tituloDocumento = ($categoriaAtual['nome'] ?? 'Categoria') . ' | WebShoes';
include __DIR__ . '/includes/cabecalho.php';
?>
<section class="conteiner intro-interna"><div class="trilha"><a href="index.php">Início</a><span>/</span><span>Categoria</span></div>
    <h1><?= escapar($categoriaAtual['nome'] ?? 'Categoria não encontrada') ?></h1>
    <p><?= escapar($categoriaAtual['descricao'] ?? 'Confira as categorias disponíveis no nosso catálogo.') ?></p>
</section>
<section class="conteiner secao" id="colecao" style="padding-top:20px">
    <div class="filtros"><a class="filtro" href="index.php#colecao">Todos os modelos</a>
    <?php foreach ($categorias as $categoria): ?><a class="filtro <?= (int)$categoria['id']===$codigo ? 'ativo':'' ?>" href="categoria.php?id=<?= (int)$categoria['id'] ?>"><?= escapar($categoria['nome']) ?></a><?php endforeach; ?></div>
    <?php if ($categoriaAtual): ?>
    <form class="barra-catalogo" action="categoria.php#colecao" method="get" role="search">
        <input type="hidden" name="id" value="<?= $codigo ?>">
        <div class="busca"><input name="busca" aria-label="Buscar nesta categoria" placeholder="Buscar nesta categoria..." value="<?= escapar($busca) ?>"></div>
        <select name="ordem" class="ordenacao" onchange="this.form.submit()" aria-label="Ordenar produtos"><?php foreach (parametrosOrdenacao() as $valor=>$texto): ?><option value="<?= escapar($valor) ?>" <?= $ordem===$valor?'selected':'' ?>><?= escapar($texto) ?></option><?php endforeach; ?></select>
        <button type="submit" class="botao-primario">Buscar</button>
    </form><?php endif; ?>
    <?php if ($erro): ?><div class="aviso aviso-erro" role="alert"><?= escapar($erro) ?></div><?php endif; ?>
    <div class="grade-produtos">
    <?php if (!$erro && !$produtos): ?><div class="vazio"><h3><?= $categoriaAtual ? 'Nenhum modelo encontrado.' : 'Categoria indisponível.' ?></h3><a class="link-simples" href="index.php">Ver todo o catálogo</a></div><?php endif; ?>
    <?php foreach ($produtos as $produto): include __DIR__ . '/includes/card-produto.php'; endforeach; ?></div>
</section>
<?php include __DIR__ . '/includes/rodape.php'; ?>
