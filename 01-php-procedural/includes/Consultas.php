<?php
/** Versão 05: todas as operações de persistência são FUNÇÕES. */
require_once __DIR__.'/ConexaoBanco.php';
function consultar(string $sql, string $tipos = '', array $valores = []): mysqli_result|bool
{
    $consulta = mysqli_prepare(conectarBanco(), $sql);
    if ($tipos !== '') mysqli_stmt_bind_param($consulta, $tipos, ...$valores);
    mysqli_stmt_execute($consulta);
    $resultado = mysqli_stmt_get_result($consulta);
    return $resultado ?: true;
}
function registros(string $sql, string $tipos = '', array $valores = []): array
{
    $resultado = consultar($sql, $tipos, $valores);
    return $resultado instanceof mysqli_result ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
}
function primeiro(string $sql, string $tipos = '', array $valores = []): ?array
{
    $resultado = consultar($sql, $tipos, $valores);
    return $resultado instanceof mysqli_result ? (mysqli_fetch_assoc($resultado) ?: null) : null;
}
function listarCategorias(): array
{
    return registros('SELECT id,nome,slug,descricao,cor FROM categorias WHERE ativo=1 ORDER BY id');
}
function listarCategoriasAdministracao(): array
{
    return registros('SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id=c.id) quantidade_produtos FROM categorias c ORDER BY c.id DESC');
}
function buscarCategoria(int $codigo): ?array
{
    return primeiro('SELECT id,nome,slug,descricao,cor FROM categorias WHERE id=? AND ativo=1 LIMIT 1','i',[$codigo]);
}
function buscarCategoriaEditar(int $codigo): ?array
{
    return primeiro('SELECT * FROM categorias WHERE id=? LIMIT 1','i',[$codigo]);
}
function listarProdutos(?int $categoria=null, string $busca='', string $ordem='destaques'): array
{
    $ordens=['destaques'=>'p.destaque DESC, p.id ASC','menor_preco'=>'p.preco ASC, p.id ASC','maior_preco'=>'p.preco DESC, p.id ASC','nome'=>'p.nome ASC, p.id ASC'];
    $sql='SELECT p.*,c.nome categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND c.ativo=1';
    $tipos=''; $valores=[];
    if ($categoria !== null) { $sql.=' AND p.categoria_id=?'; $tipos.='i'; $valores[]=$categoria; }
    if ($busca !== '') { $sql.=' AND (p.nome LIKE ? OR p.marca LIKE ? OR p.descricao_curta LIKE ?)'; $tipos.='sss'; $texto='%'.trim($busca).'%'; array_push($valores,$texto,$texto,$texto); }
    $sql.=' ORDER BY '.($ordens[$ordem]??$ordens['destaques']);
    return registros($sql,$tipos,$valores);
}
function listarProdutosAdministracao(): array
{
    return registros('SELECT p.*, c.nome categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC');
}
function buscarProduto(int $codigo): ?array
{
    return primeiro('SELECT p.*, c.nome categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.ativo=1 AND c.ativo=1 LIMIT 1','i',[$codigo]);
}
function buscarProdutoEditar(int $codigo): ?array
{
    return primeiro('SELECT * FROM produtos WHERE id=? LIMIT 1','i',[$codigo]);
}
function cadastrarCategoria(array $d): int
{
    consultar('INSERT INTO categorias (nome,slug,descricao,cor,ativo) VALUES (?,?,?,?,?)','ssssi',[$d['nome'],$d['slug'],$d['descricao'],$d['cor'],$d['ativo']]);
    return (int)mysqli_insert_id(conectarBanco());
}
function atualizarCategoria(int $id,array $d): bool
{
    consultar('UPDATE categorias SET nome=?,slug=?,descricao=?,cor=?,ativo=? WHERE id=?','ssssii',[$d['nome'],$d['slug'],$d['descricao'],$d['cor'],$d['ativo'],$id]);
    return mysqli_affected_rows(conectarBanco())>=0;
}
function excluirCategoria(int $id): bool
{
    consultar('DELETE FROM categorias WHERE id=?','i',[$id]);
    return mysqli_affected_rows(conectarBanco())>0;
}
function cadastrarProduto(array $d): int
{
    consultar('INSERT INTO produtos (categoria_id,nome,slug,marca,descricao_curta,descricao,preco,preco_anterior,imagem,etiqueta,cor,tamanhos,destaque,ativo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
      'isssssddssssii',[$d['categoria_id'],$d['nome'],$d['slug'],$d['marca'],$d['descricao_curta'],$d['descricao'],$d['preco'],$d['preco_anterior'],$d['imagem'],$d['etiqueta'],$d['cor'],$d['tamanhos'],$d['destaque'],$d['ativo']]);
    return (int)mysqli_insert_id(conectarBanco());
}
function atualizarProduto(int $id,array $d): bool
{
    consultar('UPDATE produtos SET categoria_id=?,nome=?,slug=?,marca=?,descricao_curta=?,descricao=?,preco=?,preco_anterior=?,imagem=?,etiqueta=?,cor=?,tamanhos=?,destaque=?,ativo=? WHERE id=?',
      'isssssddssssiii',[$d['categoria_id'],$d['nome'],$d['slug'],$d['marca'],$d['descricao_curta'],$d['descricao'],$d['preco'],$d['preco_anterior'],$d['imagem'],$d['etiqueta'],$d['cor'],$d['tamanhos'],$d['destaque'],$d['ativo'],$id]);
    return mysqli_affected_rows(conectarBanco())>=0;
}
function excluirProduto(int $id): bool
{
    consultar('DELETE FROM produtos WHERE id=?','i',[$id]);
    return mysqli_affected_rows(conectarBanco())>0;
}
function autenticarUsuario(string $usuario,string $senha): ?int
{
    $registro=primeiro('SELECT id,senha_hash FROM usuarios_administrativos WHERE usuario=? AND ativo=1 LIMIT 1','s',[$usuario]);
    return $registro && password_verify($senha,$registro['senha_hash']) ? (int)$registro['id'] : null;
}
