<section class="conteiner intro-interna"><div class="trilha"><a href="index.php">Início</a><span>/</span><span>Categoria</span></div>
    <h1><?= escapar($categoriaAtual['nome'] ?? 'Categoria não encontrada') ?></h1>
    <p><?= escapar($categoriaAtual['descricao'] ?? 'Confira as categorias disponíveis no nosso catálogo.') ?></p>
</section>
<section class="conteiner secao" id="colecao" style="padding-top:20px">
    <div class="filtros"><a class="filtro" href="index.php#colecao">Todos os modelos</a>
    <?php foreach ($categorias as $categoria): ?><a class="filtro <?= (int)$categoria['id']===$codigo ? 'ativo':'' ?>" href="<?= escapar($linkCategoria((int)$categoria['id'])) ?>"><?= escapar($categoria['nome']) ?></a><?php endforeach; ?></div>
    <?php if ($categoriaAtual): ?>
    <form class="barra-catalogo" action="index.php#colecao" method="get" role="search">
        <input type="hidden" name="pagina" value="categoria"><input type="hidden" name="id" value="<?= $codigo ?>">
        <div class="busca"><input name="busca" aria-label="Buscar nesta categoria" placeholder="Buscar nesta categoria..." value="<?= escapar($busca) ?>"></div>
        <select name="ordem" class="ordenacao" onchange="this.form.submit()" aria-label="Ordenar produtos"><?php foreach (parametrosOrdenacao() as $valor=>$texto): ?><option value="<?= escapar($valor) ?>" <?= $ordem===$valor?'selected':'' ?>><?= escapar($texto) ?></option><?php endforeach; ?></select>
        <button type="submit" class="botao-primario">Buscar</button>
    </form><?php endif; ?>
    <?php if ($erro): ?><div class="aviso aviso-erro" role="alert"><?= escapar($erro) ?></div><?php endif; ?>
    <div class="grade-produtos">
    <?php if (!$erro && !$produtos): ?><div class="vazio"><h3><?= $categoriaAtual ? 'Nenhum modelo encontrado.' : 'Categoria indisponível.' ?></h3><a class="link-simples" href="index.php">Ver todo o catálogo</a></div><?php endif; ?>
    <?php foreach ($produtos as $produto): include __DIR__ . '/partes/card-produto.php'; endforeach; ?></div>
</section>

