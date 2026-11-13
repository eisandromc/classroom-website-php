<article class="card-produto">
    <a class="card-imagem" href="<?= escapar($linkProduto((int)$produto['id'])) ?>" aria-label="Ver detalhes de <?= escapar($produto['nome']) ?>">
        <?php if (!empty($produto['etiqueta'])): ?><span class="tag"><?= escapar($produto['etiqueta']) ?></span><?php endif; ?>
        <img src="<?= escapar(enderecoImagem($produto['imagem'])) ?>" alt="Tênis <?= escapar($produto['nome']) ?>" loading="lazy">
    </a>
    <div class="card-info">
        <div class="card-sobretitulo"><?= escapar($produto['marca']) ?> · <?= escapar($produto['categoria_nome']) ?></div>
        <h3 class="card-titulo"><a href="<?= escapar($linkProduto((int)$produto['id'])) ?>"><?= escapar($produto['nome']) ?></a></h3>
        <div class="card-rodape"><span class="preco"><?= precoReais($produto['preco']) ?></span>
            <?php if ($produto['preco_anterior'] !== null): ?><span class="preco-antigo"><?= precoReais($produto['preco_anterior']) ?></span><?php endif; ?>
        </div>
    </div>
</article>
