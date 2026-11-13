<?php
require_once __DIR__ . '/../includes/ConexaoBanco.php';
/** Entidade de produto e operações de persistência via MySQLi. */
class Produto
{
    private mysqli $conexao;
    public function __construct()
    {
        $banco = new ConexaoBanco();
        $this->conexao = $banco->conectar();
    }
    public function listar(?int $codigoCategoria = null, string $busca = '', string $ordem = 'destaques'): array
    {
        $ordenacoes = [
            'destaques' => 'p.destaque DESC, p.id ASC',
            'menor_preco' => 'p.preco ASC, p.id ASC',
            'maior_preco' => 'p.preco DESC, p.id ASC',
            'nome' => 'p.nome ASC, p.id ASC'
        ];
        $sql = 'SELECT p.*, c.nome AS categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND c.ativo=1';
        $tipos = ''; $parametros = [];
        if ($codigoCategoria !== null) { $sql .= ' AND p.categoria_id=?'; $tipos .= 'i'; $parametros[] = $codigoCategoria; }
        if (trim($busca) !== '') {
            $sql .= ' AND (p.nome LIKE ? OR p.marca LIKE ? OR p.descricao_curta LIKE ?)';
            $tipos .= 'sss'; $texto = '%' . trim($busca) . '%';
            array_push($parametros, $texto, $texto, $texto);
        }
        $sql .= ' ORDER BY ' . ($ordenacoes[$ordem] ?? $ordenacoes['destaques']);
        $consulta = $this->conexao->prepare($sql);
        if ($tipos) $consulta->bind_param($tipos, ...$parametros);
        $consulta->execute();
        return $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function listarAdministracao(): array
    {
        return $this->conexao->query('SELECT p.*, c.nome AS categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id ORDER BY p.id DESC')->fetch_all(MYSQLI_ASSOC);
    }
    public function buscarPorCodigo(int $codigo): ?array
    {
        $consulta = $this->conexao->prepare('SELECT p.*, c.nome AS categoria_nome FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.ativo=1 AND c.ativo=1 LIMIT 1');
        $consulta->bind_param('i', $codigo); $consulta->execute();
        return $consulta->get_result()->fetch_assoc() ?: null;
    }
    public function buscarParaEditar(int $codigo): ?array
    {
        $consulta = $this->conexao->prepare('SELECT * FROM produtos WHERE id=? LIMIT 1');
        $consulta->bind_param('i', $codigo); $consulta->execute();
        return $consulta->get_result()->fetch_assoc() ?: null;
    }
    public function cadastrar(array $d): int
    {
        $sql = 'INSERT INTO produtos (categoria_id,nome,slug,marca,descricao_curta,descricao,preco,preco_anterior,imagem,etiqueta,cor,tamanhos,destaque,ativo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $c = $this->conexao->prepare($sql);
        $c->bind_param('isssssddssssii', $d['categoria_id'],$d['nome'],$d['slug'],$d['marca'],$d['descricao_curta'],$d['descricao'],$d['preco'],$d['preco_anterior'],$d['imagem'],$d['etiqueta'],$d['cor'],$d['tamanhos'],$d['destaque'],$d['ativo']);
        $c->execute();
        return (int)$this->conexao->insert_id;
    }
    public function atualizar(int $codigo, array $d): bool
    {
        $sql = 'UPDATE produtos SET categoria_id=?,nome=?,slug=?,marca=?,descricao_curta=?,descricao=?,preco=?,preco_anterior=?,imagem=?,etiqueta=?,cor=?,tamanhos=?,destaque=?,ativo=? WHERE id=?';
        $c = $this->conexao->prepare($sql);
        $c->bind_param('isssssddssssiii', $d['categoria_id'],$d['nome'],$d['slug'],$d['marca'],$d['descricao_curta'],$d['descricao'],$d['preco'],$d['preco_anterior'],$d['imagem'],$d['etiqueta'],$d['cor'],$d['tamanhos'],$d['destaque'],$d['ativo'],$codigo);
        $c->execute();
        return $this->buscarParaEditar($codigo) !== null;
    }
    public function excluir(int $codigo): bool
    {
        $c = $this->conexao->prepare('DELETE FROM produtos WHERE id=?');
        $c->bind_param('i', $codigo); $c->execute();
        return $c->affected_rows > 0;
    }
}
