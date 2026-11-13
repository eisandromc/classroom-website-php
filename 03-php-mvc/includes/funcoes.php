<?php
/** Funções de apresentação: nunca construir HTML sem escapar valores do banco. */
function escapar(mixed $texto): string
{
    return htmlspecialchars((string)($texto ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function precoReais(mixed $valor): string
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}
function parametrosOrdenacao(): array
{
    return [
        'destaques' => 'Em destaque',
        'menor_preco' => 'Menor preço',
        'maior_preco' => 'Maior preço',
        'nome' => 'Nome A–Z'
    ];
}

/** O upload é compartilhado entre todas as arquiteturas; imagens iniciais são locais. */
function enderecoImagem(string $imagem, string $prefixo = ''): string
{
    $nome = rawurlencode(basename($imagem));
    return str_starts_with($imagem, 'uploads/')
        ? $prefixo . '../imagens/uploads/' . $nome
        : $prefixo . 'assets/img/' . $nome;
}
