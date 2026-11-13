<?php
/** Validações de entrada, independentes de SQL e HTML. */
function textoObrigatorio(mixed $valor, string $campo, int $limite): string
{
    if (!is_string($valor) && !is_numeric($valor)) throw new InvalidArgumentException("$campo inválido.");
    $texto = trim((string)$valor);
    if ($texto === '' || (function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto)) > $limite) {
        throw new InvalidArgumentException("Informe $campo com até $limite caracteres.");
    }
    return $texto;
}
function textoOpcional(mixed $valor, string $campo, int $limite): string
{
    $texto = trim((string)($valor ?? ''));
    if ((function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto)) > $limite) throw new InvalidArgumentException("$campo excedeu $limite caracteres.");
    return $texto;
}
function converterSlug(string $texto): string
{
    $semAcentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $slug = strtolower((string)$semAcentos);
    $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
    return $slug;
}
function numeroDecimal(mixed $valor, string $nome, bool $permitirNulo = false): ?float
{
    if ($permitirNulo && ($valor === '' || $valor === null)) return null;
    if (!is_numeric($valor) || !is_finite((float)$valor)) throw new InvalidArgumentException("$nome inválido.");
    $numero = (float)$valor;
    if ($numero < 0 || $numero > 99999999.99) throw new InvalidArgumentException("$nome fora do limite.");
    return $numero;
}
function flagFormulario(mixed $valor): int
{
    return ($valor === 1 || $valor === true || $valor === '1' || $valor === 'on') ? 1 : 0;
}
function validarCategoria(array $entrada): array
{
    $nome = textoObrigatorio($entrada['nome'] ?? '', 'o nome da categoria', 80);
    $slug = converterSlug(textoOpcional($entrada['slug'] ?? '', 'Slug', 100) ?: $nome);
    if ($slug === '' || strlen($slug) > 100) throw new InvalidArgumentException('Slug inválido.');
    $cor = (string)($entrada['cor'] ?? '#F1F1F1');
    if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $cor)) throw new InvalidArgumentException('Cor inválida: use #RRGGBB.');
    return [
        'nome' => $nome, 'slug' => $slug,
        'descricao' => textoOpcional($entrada['descricao'] ?? '', 'Descrição', 255),
        'cor' => strtoupper($cor), 'ativo' => flagFormulario($entrada['ativo'] ?? 0)
    ];
}
function validarProduto(array $entrada, string $imagemAnterior = 'tenis-01.svg'): array
{
    $nome = textoObrigatorio($entrada['nome'] ?? '', 'o nome do produto', 120);
    $slug = converterSlug(textoOpcional($entrada['slug'] ?? '', 'Slug', 150) ?: $nome);
    if ($slug === '' || strlen($slug) > 150) throw new InvalidArgumentException('Slug inválido.');
    $categoriaId = filter_var($entrada['categoria_id'] ?? null, FILTER_VALIDATE_INT);
    if ($categoriaId === false || $categoriaId === null || $categoriaId < 1) throw new InvalidArgumentException('Selecione uma categoria válida.');
    $imagem = (string)($entrada['imagem'] ?? $imagemAnterior);
    if (!preg_match('~^(?:tenis-(?:0[1-9]|1[0-2])\.svg|uploads/[a-f0-9]{32}\.(?:jpg|png|webp))$~D', $imagem)) {
        throw new InvalidArgumentException('Imagem inválida. Use uma imagem enviada pelo painel.');
    }
    return [
        'categoria_id' => $categoriaId, 'nome' => $nome, 'slug' => $slug,
        'marca' => textoObrigatorio($entrada['marca'] ?? '', 'a marca', 70),
        'descricao_curta' => textoObrigatorio($entrada['descricao_curta'] ?? '', 'a descrição curta', 250),
        'descricao' => textoObrigatorio($entrada['descricao'] ?? '', 'a descrição', 65000),
        'preco' => numeroDecimal($entrada['preco'] ?? null, 'Preço'),
        'preco_anterior' => numeroDecimal($entrada['preco_anterior'] ?? null, 'Preço anterior', true),
        'imagem' => $imagem,
        'etiqueta' => textoOpcional($entrada['etiqueta'] ?? '', 'Etiqueta', 30),
        'cor' => textoObrigatorio($entrada['cor'] ?? '', 'a cor do produto', 80),
        'tamanhos' => textoObrigatorio($entrada['tamanhos'] ?? '', 'os tamanhos', 80),
        'destaque' => flagFormulario($entrada['destaque'] ?? 0),
        'ativo' => flagFormulario($entrada['ativo'] ?? 0)
    ];
}
