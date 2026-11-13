<?php
require_once __DIR__ . '/../includes/Autenticacao.php';
require_once __DIR__ . '/../includes/Validacao.php';
require_once __DIR__ . '/../includes/Imagens.php';
require_once __DIR__ . '/../model/Usuario.php';
require_once __DIR__ . '/../model/Produto.php';
require_once __DIR__ . '/../model/Categoria.php';
class AdministracaoControlador
{
    private function iniciar(): void { iniciarSessaoAdmin('webshoes_admin_02'); }
    public function exibir(): void
    {
        $this->iniciar();
        $autenticado = administradorAutenticado();
        $token = tokenFormulario();
        $mensagem = (string)($_SESSION['mensagem'] ?? '');
        $erroMensagem = (string)($_SESSION['erro_mensagem'] ?? '');
        unset($_SESSION['mensagem'], $_SESSION['erro_mensagem']);
        $tipo = ($_GET['tipo'] ?? '') === 'categorias' ? 'categorias' : 'produtos';
        $acao = ($_GET['acao'] ?? '') === 'formulario' ? 'formulario' : 'listar';
        $registro = []; $produtos=[]; $categorias=[];
        $urlPainel='index.php?pagina=admin'; $urlProcessar='index.php?pagina=admin-acao';
        $urlLoja='index.php'; $prefixoImagens='';
        if ($autenticado) {
            try {
                $produtos = $tipo==='produtos' && $acao !== 'formulario' ? (new Produto())->listarAdministracao() : [];
                $categorias = (new Categoria())->listarAdministracao();
                $codigo = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT) ?: 0;
                if ($acao==='formulario' && $codigo>0) {
                    $registro = $tipo==='produtos' ? (new Produto())->buscarParaEditar($codigo) : (new Categoria())->buscarParaEditar($codigo);
                    if (!$registro) { $erroMensagem='Registro não encontrado.'; $acao='listar'; }
                }
            } catch (Throwable $erro) { error_log($erro->getMessage()); $erroMensagem='Falha na conexão com o banco MySQL.'; }
        }
        require __DIR__ . '/../view/admin/painel.php';
    }
    public function processar(): void
    {
        $this->iniciar();
        $destino='index.php?pagina=admin';
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new RuntimeException('Método não permitido.');
            verificarToken($_POST['token'] ?? null);
            $acao = (string)($_POST['acao'] ?? '');
            if ($acao==='entrar') {
                permitirTentativaLogin();
                $codigo=(new Usuario())->autenticar((string)($_POST['usuario'] ?? ''),(string)($_POST['senha'] ?? ''));
                if (!$codigo) { registrarFalhaLogin(); throw new RuntimeException('Usuário ou senha inválidos.'); }
                entrarAdministrador($codigo);
                $_SESSION['mensagem']='Login efetuado.';
            } else {
                if (!administradorAutenticado()) throw new RuntimeException('Faça login para continuar.');
                if ($acao==='sair') {
                    sairAdministrador(); header('Location: index.php?pagina=admin'); exit;
                }
                $tipo=($_POST['tipo'] ?? '') === 'categorias' ? 'categorias' : 'produtos';
                $destino.='&tipo='.$tipo;
                $modelo=$tipo==='categorias' ? new Categoria() : new Produto();
                $codigo=filter_var($_POST['codigo'] ?? 0,FILTER_VALIDATE_INT);
                if ($codigo===false || $codigo<0) throw new InvalidArgumentException('Código inválido.');
                if ($acao==='salvar') {
                    if ($codigo && !$modelo->buscarParaEditar($codigo)) throw new RuntimeException('Cadastro não encontrado.');
                    if ($tipo==='categorias') $dados=validarCategoria($_POST);
                    else {
                        $anterior=$codigo ? $modelo->buscarParaEditar($codigo)['imagem'] : 'tenis-01.svg';
                        $dados=validarProduto($_POST,$anterior);
                        $imagem=salvarImagem($_FILES['imagem'] ?? null);
                        if ($imagem) $dados['imagem']=$imagem;
                    }
                    if ($codigo) $modelo->atualizar($codigo,$dados); else $modelo->cadastrar($dados);
                    $_SESSION['mensagem']='Cadastro salvo com sucesso.';
                } elseif ($acao==='excluir') {
                    if (!$codigo || !$modelo->excluir($codigo)) throw new RuntimeException('Cadastro não encontrado.');
                    $_SESSION['mensagem']='Cadastro excluído com sucesso.';
                } else throw new RuntimeException('Ação desconhecida.');
            }
        } catch (mysqli_sql_exception $erro) {
            error_log($erro->getMessage());
            $_SESSION['erro_mensagem']=in_array($erro->getCode(),[1062,1451,1452],true) ? 'Chave duplicada ou registro relacionado a outro cadastro.' : 'Falha de gravação no banco.';
        } catch (Throwable $erro) {
            error_log($erro->getMessage());
            $_SESSION['erro_mensagem']=$erro instanceof InvalidArgumentException || $erro instanceof RuntimeException ? $erro->getMessage() : 'Falha inesperada.';
        }
        header('Location: '.$destino); exit;
    }
}
