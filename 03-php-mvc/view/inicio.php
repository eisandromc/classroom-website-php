<?php include __DIR__ . '/partes/hero.php'; ?>
<section class="conteiner secao" id="colecao">
    <div class="secao-cabecalho"><div><span class="eyebrow">Seleção WebShoes</span><h2 class="titulo-secao">ENCONTRE SEU<br>PRÓXIMO PAR.</h2></div><p>Modelos para todos os estilos. Explore nossas categorias, compare e encontre seu favorito.</p></div>
    <div class="filtros"><a class="filtro ativo" href="index.php#colecao">Todos os modelos</a>
        <?php foreach ($categorias as $categoria): ?><a class="filtro" href="<?= escapar($linkCategoria((int)$categoria['id'])) ?>"><?= escapar($categoria['nome']) ?></a><?php endforeach; ?>
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
        <?php foreach ($produtos as $produto): include __DIR__ . '/partes/card-produto.php'; endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/partes/faixa.php'; ?>
