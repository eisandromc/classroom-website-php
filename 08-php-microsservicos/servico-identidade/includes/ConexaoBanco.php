<?php
/**
 * Classe de conexão do WebShoes com MySQLi, sem PDO.
 * Arquivo em includes/, importado por cada classe de domínio.
 * Valores abaixo são apenas para XAMPP local; não use root sem senha em produção.
 */
class ConexaoBanco
{
    private string $servidor = 'localhost';
    private string $usuario = 'root';
    private string $senha = '';
    private string $banco = 'webshoes_identidade';
    private ?mysqli $conexao = null;

    public function conectar(): mysqli
    {
        if ($this->conexao === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->conexao = new mysqli(
                $this->servidor,
                $this->usuario,
                $this->senha,
                $this->banco
            );
            $this->conexao->set_charset('utf8mb4');
        }
        return $this->conexao;
    }

    public function obterConexao(): mysqli
    {
        return $this->conectar();
    }

    public function desconectar(): void
    {
        if ($this->conexao !== null) {
            $this->conexao->close();
            $this->conexao = null;
        }
    }
}
