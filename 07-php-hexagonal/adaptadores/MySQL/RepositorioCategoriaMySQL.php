<?php
require_once __DIR__ . '/../../dominio/Portas/RepositorioCategoria.php';
/** Uma categoria possui vários produtos. SQL sempre parametrizado quando há entrada externa. */
class RepositorioCategoriaMySQL implements RepositorioCategoria
{
    private mysqli $conexao;
    public function __construct(mysqli $conexao)
    {
        $this->conexao = $conexao;
    }
    public function listar(): array
    {
        return $this->conexao->query('SELECT id, nome, slug, descricao, cor FROM categorias WHERE ativo = 1 ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    }
    public function listarAdministracao(): array
    {
        return $this->conexao->query('SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id = c.id) AS quantidade_produtos FROM categorias c ORDER BY c.id DESC')->fetch_all(MYSQLI_ASSOC);
    }
    public function buscarPorCodigo(int $codigo): ?array
    {
        $consulta = $this->conexao->prepare('SELECT id, nome, slug, descricao, cor FROM categorias WHERE id = ? AND ativo = 1 LIMIT 1');
        $consulta->bind_param('i', $codigo);
        $consulta->execute();
        return $consulta->get_result()->fetch_assoc() ?: null;
    }
    public function buscarParaEditar(int $codigo): ?array
    {
        $consulta = $this->conexao->prepare('SELECT * FROM categorias WHERE id = ? LIMIT 1');
        $consulta->bind_param('i', $codigo);
        $consulta->execute();
        return $consulta->get_result()->fetch_assoc() ?: null;
    }
    public function cadastrar(array $dados): int
    {
        $consulta = $this->conexao->prepare('INSERT INTO categorias (nome, slug, descricao, cor, ativo) VALUES (?, ?, ?, ?, ?)');
        $consulta->bind_param('ssssi', $dados['nome'], $dados['slug'], $dados['descricao'], $dados['cor'], $dados['ativo']);
        $consulta->execute();
        return (int)$this->conexao->insert_id;
    }
    public function atualizar(int $codigo, array $dados): bool
    {
        $consulta = $this->conexao->prepare('UPDATE categorias SET nome=?, slug=?, descricao=?, cor=?, ativo=? WHERE id=?');
        $consulta->bind_param('ssssii', $dados['nome'], $dados['slug'], $dados['descricao'], $dados['cor'], $dados['ativo'], $codigo);
        $consulta->execute();
        return $this->buscarParaEditar($codigo) !== null;
    }
    public function excluir(int $codigo): bool
    {
        $consulta = $this->conexao->prepare('DELETE FROM categorias WHERE id=?');
        $consulta->bind_param('i', $codigo);
        $consulta->execute();
        return $consulta->affected_rows > 0;
    }
}
