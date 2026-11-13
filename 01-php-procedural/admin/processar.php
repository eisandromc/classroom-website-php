<?php
require_once __DIR__ . '/../includes/Autenticacao.php';
require_once __DIR__ . '/../includes/Validacao.php';
require_once __DIR__ . '/../includes/Imagens.php';
iniciarSessaoAdmin('webshoes_admin_05');
$destino = 'index.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método não permitido.');
    verificarToken($_POST['token'] ?? null);
    $acao = (string)($_POST['acao'] ?? '');
    if ($acao === 'entrar') {
        permitirTentativaLogin();
        require_once __DIR__ . '/../includes/Consultas.php';
        $codigo = autenticarUsuario((string)($_POST['usuario'] ?? ''),(string)($_POST['senha'] ?? ''));
        if (!$codigo) { registrarFalhaLogin(); throw new RuntimeException('Usuário ou senha inválidos.'); }
        entrarAdministrador($codigo);
        $_SESSION['mensagem'] = 'Login efetuado.';
    } else {
        if (!administradorAutenticado()) throw new RuntimeException('Faça login para continuar.');
        if ($acao === 'sair') {
            sairAdministrador();
            header('Location: index.php'); exit;
        }
        $tipo = ($_POST['tipo'] ?? '') === 'categorias' ? 'categorias' : 'produtos';
        $destino = 'index.php?tipo=' . $tipo;
        require_once __DIR__ . '/../includes/Consultas.php';
        $codigo = filter_var($_POST['codigo'] ?? 0, FILTER_VALIDATE_INT);
        if ($codigo === false || $codigo < 0) throw new InvalidArgumentException('Código inválido.');
        if ($acao === 'salvar') {
            if ($codigo && !($tipo==='categorias' ? buscarCategoriaEditar($codigo) : buscarProdutoEditar($codigo))) throw new RuntimeException('Cadastro não encontrado.');
            if ($tipo === 'categorias') {
                $dados = validarCategoria($_POST);
            } else {
                $anterior = $codigo ? ($tipo==='categorias' ? buscarCategoriaEditar($codigo) : buscarProdutoEditar($codigo))['imagem'] : 'tenis-01.svg';
                $dados = validarProduto($_POST,$anterior);
                $imagem = salvarImagem($_FILES['imagem'] ?? null);
                if ($imagem) $dados['imagem'] = $imagem;
            }
            if ($codigo) ($tipo==='categorias' ? atualizarCategoria($codigo,$dados) : atualizarProduto($codigo,$dados));
            else ($tipo==='categorias' ? cadastrarCategoria($dados) : cadastrarProduto($dados));
            $_SESSION['mensagem'] = 'Cadastro salvo com sucesso.';
        } elseif ($acao === 'excluir') {
            if (!$codigo || !($tipo==='categorias' ? excluirCategoria($codigo) : excluirProduto($codigo))) throw new RuntimeException('Registro não encontrado.');
            $_SESSION['mensagem'] = 'Cadastro excluído com sucesso.';
        } else throw new RuntimeException('Ação não reconhecida.');
    }
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    $_SESSION['erro_mensagem'] = in_array($erro->getCode(),[1062,1451,1452],true) ? 'Não foi possível salvar ou excluir: chave duplicada ou registro relacionado a outro cadastro.' : 'Falha ao gravar no banco.';
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    $_SESSION['erro_mensagem'] = $erro instanceof InvalidArgumentException || $erro instanceof RuntimeException ? $erro->getMessage() : 'Ocorreu uma falha inesperada.';
}
header('Location: ' . $destino); exit;
