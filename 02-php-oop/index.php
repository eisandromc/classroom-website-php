<?php
require_once __DIR__ . '/includes/funcoes.php';
require_once __DIR__ . '/classes/Produto.php';
require_once __DIR__ . '/classes/Categoria.php';

$erro = null;
$produtos = [];
$categorias = [];
$busca = trim((string)($_GET['busca'] ?? ''));
$ordem = (string)($_GET['ordem'] ?? 'destaques');
try {
    $categorias = (new Categoria())->listar();
    $produtos = (new Produto())->listar(null, $busca, $ordem);
} catch (Throwable $excecao) {
    error_log($excecao->getMessage());
    $erro = 'Não foi possível carregar os produtos. Verifique MySQL e as configurações em includes/ConexaoBanco.php.';
}
$linkCategoria = fn(int $id): string => 'categoria.php?id=' . $id;
$linkProduto = fn(int $id): string => 'produto.php?id=' . $id;
$inicio = 'index.php';
$tituloDocumento = 'WebShoes — Seu passo, suas regras';
include __DIR__ . '/includes/cabecalho.php';
include __DIR__ . '/includes/hero.php';
?>
<section class="conteiner secao" id="colecao">
    <div class="secao-cabecalho"><div><span class="eyebrow">Seleção WebShoes</span><h2 class="titulo-secao">ENCONTRE SEU<br>PRÓXIMO PAR.</h2></div><p>Modelos para todos os estilos. Explore nossas categorias, compare e encontre seu favorito.</p></div>
    <div class="filtros"><a class="filtro ativo" href="index.php#colecao">Todos os modelos</a>
        <?php foreach ($categorias as $categoria): ?><a class="filtro" href="categoria.php?id=<?= (int)$categoria['id'] ?>"><?= escapar($categoria['nome']) ?></a><?php endforeach; ?>
    </div>
    <form class="barra-catalogo" action="index.php#colecao" method="get" role="search">
        <div class="busca"><svg viewBox="0 0 24 24"><circle cx="10.7" cy="10.7" r="6.7"/><path d="m16 16 5 5"/></svg><input name="busca" aria-label="Buscar tênis" placeholder="Busque por modelo ou marca..." value="<?= escapar($busca) ?>"></div>
        <select name="ordem" class="ordenacao" aria-label="Ordenar produtos" onchange="this.form.submit()">
        <?php foreach (parametrosOrdenacao() as $valor => $texto): ?><option value="<?= escapar($valor) ?>" <?= $ordem === $valor ? 'selected' : '' ?>><?= escapar($texto) ?></option><?php endforeach; ?>
        </select><button type="submit" class="botao-primario">Buscar</button>
    </form>
    <?php if ($erro): ?><div class="aviso aviso-erro" role="alert"><?= escapar($erro) ?></div><?php endif; ?>
    <div class="grade-produtos">
        <?php if (!$erro && !$produtos): ?><div class="vazio"><h3>Nenhum produto encontrado.</h3><p>Altere a busca para explorar outros modelos.</p></div><?php endif; ?>
        <?php foreach ($produtos as $produto): include __DIR__ . '/includes/card-produto.php'; endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/faixa.php'; include __DIR__ . '/includes/rodape.php'; ?>
