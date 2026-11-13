<?php
require_once __DIR__ . '/../../dominio/Portas/RepositorioUsuario.php';
class RepositorioUsuarioMySQL implements RepositorioUsuario
{
    private mysqli $conexao;
    public function __construct(mysqli $conexao)
    {
        $this->conexao = $conexao;
    }
    public function autenticar(string $usuario, string $senha): ?int
    {
        $consulta = $this->conexao->prepare('SELECT id, senha_hash FROM usuarios_administrativos WHERE usuario = ? AND ativo = 1 LIMIT 1');
        $consulta->bind_param('s', $usuario);
        $consulta->execute();
        $registro = $consulta->get_result()->fetch_assoc();
        return $registro && password_verify($senha, $registro['senha_hash']) ? (int)$registro['id'] : null;
    }
}
