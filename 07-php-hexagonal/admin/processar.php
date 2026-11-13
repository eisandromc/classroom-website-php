<?php
require_once __DIR__ . '/../includes/Autenticacao.php';
require_once __DIR__ . '/../includes/Validacao.php';
require_once __DIR__ . '/../includes/Imagens.php';
iniciarSessaoAdmin('webshoes_admin_12');
$destino = 'index.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método não permitido.');
    verificarToken($_POST['token'] ?? null);
    $acao = (string)($_POST['acao'] ?? '');
    if ($acao === 'entrar') {
        permitirTentativaLogin();
        require_once __DIR__ . '/../includes/Dependencias.php';
        $codigo = usuarioServico()->autenticar((string)($_POST['usuario'] ?? ''),(string)($_POST['senha'] ?? ''));
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
        require_once __DIR__ . '/../includes/Dependencias.php';
        $modelo = $tipo === 'categorias' ? categoriaServico() : produtoServico();
        $codigo = filter_var($_POST['codigo'] ?? 0, FILTER_VALIDATE_INT);
        if ($codigo === false || $codigo < 0) throw new InvalidArgumentException('Código inválido.');
        if ($acao === 'salvar') {
            if ($codigo && !$modelo->buscarParaEditar($codigo)) throw new RuntimeException('Cadastro não encontrado.');
            if ($tipo === 'categorias') {
                $dados = validarCategoria($_POST);
            } else {
                $anterior = $codigo ? $modelo->buscarParaEditar($codigo)['imagem'] : 'tenis-01.svg';
                $dados = validarProduto($_POST,$anterior);
                $imagem = imagemServico()->salvar($_FILES['imagem'] ?? null);
                if ($imagem) $dados['imagem'] = $imagem;
            }
            if ($codigo) $modelo->atualizar($codigo, $dados);
            else $modelo->cadastrar($dados);
            $_SESSION['mensagem'] = 'Cadastro salvo com sucesso.';
        } elseif ($acao === 'excluir') {
            if (!$codigo || !$modelo->excluir($codigo)) throw new RuntimeException('Registro não encontrado.');
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
