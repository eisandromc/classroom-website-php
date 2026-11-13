<?php
/**
 * WebShoes REST: todas as chamadas HTTP e rotas ficam neste arquivo,
 * as classes em classes/ cuidam do acesso ao MySQL, sem PDO.
 * Consumida pelo site 03 HTML/CSS/JavaScript.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');
require_once __DIR__ . '/includes/Autenticacao.php';
require_once __DIR__ . '/includes/Validacao.php';
require_once __DIR__ . '/includes/Imagens.php';
require_once __DIR__ . '/classes/Produto.php';
require_once __DIR__ . '/classes/Categoria.php';
require_once __DIR__ . '/classes/Usuario.php';
iniciarSessaoAdmin('webshoes_admin_api');

function responder(int $codigo, array $conteudo): never
{
    http_response_code($codigo);
    echo json_encode($conteudo, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    exit;
}
function corpoJson(): array
{
    if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') {
        throw new InvalidArgumentException('Envie Content-Type: application/json.');
    }
    $corpo = file_get_contents('php://input');
    if ($corpo === false || strlen($corpo) > 1024*1024) throw new InvalidArgumentException('Corpo da requisição inválido ou muito grande.');
    try { $dados = json_decode($corpo, true, 32, JSON_THROW_ON_ERROR); }
    catch (JsonException $erro) { throw new InvalidArgumentException('JSON inválido.'); }
    if (!is_array($dados) || array_is_list($dados)) throw new InvalidArgumentException('Objeto JSON obrigatório.');
    return $dados;
}
function exigirAdministrador(): void
{
    if (!administradorAutenticado()) responder(401, ['erro'=>'Faça login para acessar o cadastro.']);
}
function exigirSeguranca(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!is_string($token) || !hash_equals(tokenFormulario(), $token)) responder(403, ['erro'=>'Token CSRF ausente ou inválido.']);
}
$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$rota = trim((string)($_GET['rota'] ?? ($_SERVER['PATH_INFO'] ?? '')), '/');
if (!$rota) $rota='status';

try {
    // Rotas públicas: mantém compatibilidade com a vitrine 03 original.
    if ($rota==='status' && $metodo==='GET') {
        responder(200,['servico'=>'WebShoes API','status'=>'online','banco'=>'nao_verificado','versao'=>'2.1']);
    }
    // Esta rota realmente consulta o MySQL através da classe de domínio,
    // ao contrário de status, que somente confirma que o PHP respondeu.
    if ($rota==='saude' && $metodo==='GET') {
        (new Categoria())->listar();
        responder(200,['servico'=>'WebShoes API','status'=>'operacional','banco'=>'conectado','versao'=>'2.1']);
    }
    if ($rota==='sessao' && $metodo==='GET') {
        responder(200,['autenticado'=>administradorAutenticado(),'token'=>tokenFormulario()]);
    }
    if ($rota==='login' && $metodo==='POST') {
        exigirSeguranca(); permitirTentativaLogin();
        $dados=corpoJson();
        $codigo=(new Usuario())->autenticar((string)($dados['usuario']??''),(string)($dados['senha']??''));
        if (!$codigo) { registrarFalhaLogin(); responder(401,['erro'=>'Usuário ou senha inválidos.']); }
        entrarAdministrador($codigo);
        responder(200,['autenticado'=>true,'token'=>tokenFormulario()]);
    }
    if ($rota==='logout' && $metodo==='POST') {
        exigirAdministrador(); exigirSeguranca(); sairAdministrador();
        responder(200,['autenticado'=>false]);
    }
    if ($rota==='categorias' && $metodo==='GET') {
        $dados=(new Categoria())->listar(); responder(200,['dados'=>$dados,'total'=>count($dados)]);
    }
    if ($rota==='produtos' && $metodo==='GET') {
        $categoria=null;
        if (isset($_GET['categoria'])) {
            $categoria=filter_var($_GET['categoria'],FILTER_VALIDATE_INT);
            if ($categoria===false || $categoria<1) responder(400,['erro'=>'Categoria inválida.']);
        }
        $busca=trim((string)($_GET['busca']??''));
        if (strlen($busca)>120) responder(400,['erro'=>'Busca muito longa.']);
        $dados=(new Produto())->listar($categoria,$busca,(string)($_GET['ordem']??'destaques'));
        responder(200,['dados'=>$dados,'total'=>count($dados)]);
    }
    if (preg_match('~^produtos/([1-9][0-9]*)$~',$rota,$grupos) && $metodo==='GET') {
        $dados=(new Produto())->buscarPorCodigo((int)$grupos[1]);
        if (!$dados) responder(404,['erro'=>'Produto não encontrado.']);
        responder(200,['dados'=>$dados]);
    }
    if (preg_match('~^categorias/([1-9][0-9]*)/produtos$~',$rota,$grupos) && $metodo==='GET') {
        $codigo=(int)$grupos[1];
        $categoria=(new Categoria())->buscarPorCodigo($codigo);
        if (!$categoria) responder(404,['erro'=>'Categoria não encontrada.']);
        $dados=(new Produto())->listar($codigo);
        responder(200,['categoria'=>$categoria,'dados'=>$dados,'total'=>count($dados)]);
    }

    // Todas as rotas de escrita e listagem administrativa exigem sessão.
    if (str_starts_with($rota,'administracao/') || in_array($rota,['imagens','produtos','categorias'],true)
        || preg_match('~^(produtos|categorias)/[1-9][0-9]*$~',$rota)) {
        exigirAdministrador();
        if ($metodo!=='GET') exigirSeguranca();

        if ($rota==='administracao/produtos' && $metodo==='GET') {
            $dados=(new Produto())->listarAdministracao();
            responder(200,['dados'=>$dados,'total'=>count($dados)]);
        }
        if ($rota==='administracao/categorias' && $metodo==='GET') {
            $dados=(new Categoria())->listarAdministracao();
            responder(200,['dados'=>$dados,'total'=>count($dados)]);
        }
        if ($rota==='imagens' && $metodo==='POST') {
            $nome=salvarImagem($_FILES['imagem']??null);
            if (!$nome) throw new InvalidArgumentException('Selecione uma imagem para enviar.');
            responder(201,['imagem'=>$nome]);
        }
        if ($rota==='categorias' && $metodo==='POST') {
            $codigo=(new Categoria())->cadastrar(validarCategoria(corpoJson()));
            responder(201,['id'=>$codigo,'mensagem'=>'Categoria cadastrada.']);
        }
        if ($rota==='produtos' && $metodo==='POST') {
            $codigo=(new Produto())->cadastrar(validarProduto(corpoJson()));
            responder(201,['id'=>$codigo,'mensagem'=>'Produto cadastrado.']);
        }
        if (preg_match('~^(produtos|categorias)/([1-9][0-9]*)$~',$rota,$grupos)) {
            $tipo=$grupos[1]; $codigo=(int)$grupos[2];
            $modelo=$tipo==='produtos' ? new Produto() : new Categoria();
            if (!$modelo->buscarParaEditar($codigo)) responder(404,['erro'=>'Registro não encontrado.']);
            if ($metodo==='PUT' || $metodo==='PATCH') {
                $entrada=corpoJson();
                $dados=$tipo==='produtos' ? validarProduto($entrada,$modelo->buscarParaEditar($codigo)['imagem']) : validarCategoria($entrada);
                $modelo->atualizar($codigo,$dados);
                responder(200,['id'=>$codigo,'mensagem'=>'Cadastro atualizado.']);
            }
            if ($metodo==='DELETE') {
                $modelo->excluir($codigo); responder(200,['mensagem'=>'Cadastro excluído.']);
            }
        }
    }
    // Método incorreto para uma rota conhecida não deve se transformar em operação implícita.
    $conhecida=in_array($rota,['login','logout','sessao','status','saude','produtos','categorias','imagens','administracao/produtos','administracao/categorias'],true)
      || (bool)preg_match('~^(produtos/[1-9][0-9]*|categorias/[1-9][0-9]*/produtos|categorias/[1-9][0-9]*)$~',$rota);
    if ($conhecida) { header('Allow: GET, POST, PUT, PATCH, DELETE'); responder(405,['erro'=>'Método HTTP não permitido nesta rota.']); }
    responder(404,['erro'=>'Rota não encontrada.']);
} catch (InvalidArgumentException $erro) {
    responder(422,['erro'=>$erro->getMessage()]);
} catch (mysqli_sql_exception $erro) {
    error_log('WebShoes API SQL: '.$erro->getMessage());
    if (in_array($erro->getCode(),[1062,1451,1452],true)) responder(409,['erro'=>'Registro duplicado ou relacionado a outros cadastros.']);
    responder(503,['erro'=>'Banco de dados indisponível ou falha de gravação.']);
} catch (Throwable $erro) {
    error_log('WebShoes API: '.$erro->getMessage());
    responder(503,['erro'=>'Serviço indisponível. Verifique o servidor e a instalação.']);
}
