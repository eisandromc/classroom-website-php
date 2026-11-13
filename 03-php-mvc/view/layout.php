<?php
// View/layout: as páginas são selecionadas pelo controlador, não pelo usuário diretamente.
$linkCategoria = fn(int $id): string => 'index.php?pagina=categoria&id=' . $id;
$linkProduto = fn(int $id): string => 'index.php?pagina=produto&id=' . $id;
$inicio = 'index.php';
require __DIR__ . '/partes/cabecalho.php';
require __DIR__ . '/' . $pagina . '.php';
require __DIR__ . '/partes/rodape.php';
