<?php
/** Microsserviço catálogo: DONO das tabelas categorias/produtos. */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require_once __DIR__.'/classes/Produto.php';
require_once __DIR__.'/classes/Categoria.php';
require_once __DIR__.'/includes/Validacao.php';
$config=require __DIR__.'/../configuracao.php';
function resposta(int $codigo,array $dados):never {
    http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
}
function jsonEntrada():array {
    if(!str_starts_with(strtolower($_SERVER['CONTENT_TYPE']??''),'application/json')) throw new InvalidArgumentException('Content-Type JSON obrigatório.');
    $texto=file_get_contents('php://input');
    if($texto===false || strlen($texto)>1024*1024) throw new InvalidArgumentException('Dados muito grandes.');
    $dados=json_decode($texto,true,32,JSON_THROW_ON_ERROR);
    if(!is_array($dados) || array_is_list($dados)) throw new InvalidArgumentException('Objeto JSON obrigatório.');
    return $dados;
}
function interno():void {
    global $config;
    $chave=(string)($_SERVER['HTTP_X_WEBSHOES_INTERNO']??'');
    if(!$chave || !hash_equals($config['chave'],$chave)) resposta(403,['erro'=>'Requisição interna não autorizada.']);
}
$metodo=$_SERVER['REQUEST_METHOD']??'GET'; $rota=trim((string)($_GET['rota']??'status'),'/');
try {
 if($metodo==='GET' && $rota==='status') resposta(200,['servico'=>'catalogo','status'=>'online']);
 if($metodo==='GET' && $rota==='saude') { (new Categoria())->listar(); resposta(200,['servico'=>'catalogo','banco'=>'conectado']); }
 if($metodo==='GET' && $rota==='categorias') { $d=(new Categoria())->listar(); resposta(200,['dados'=>$d,'total'=>count($d)]); }
 if($metodo==='GET' && $rota==='produtos') {
   $categoria=isset($_GET['categoria']) ? filter_var($_GET['categoria'],FILTER_VALIDATE_INT) : null;
   if($categoria===false || ($categoria!==null && $categoria<1)) resposta(400,['erro'=>'Categoria inválida.']);
   $busca=trim((string)($_GET['busca']??'')); if(strlen($busca)>120) resposta(400,['erro'=>'Busca excede 120 caracteres.']);
   $d=(new Produto())->listar($categoria,$busca,(string)($_GET['ordem']??'destaques')); resposta(200,['dados'=>$d,'total'=>count($d)]);
 }
 if($metodo==='GET' && preg_match('~^produtos/([1-9][0-9]*)$~',$rota,$m)) {
   $d=(new Produto())->buscarPorCodigo((int)$m[1]);if(!$d)resposta(404,['erro'=>'Produto não encontrado.']);resposta(200,['dados'=>$d]);
 }
 if($metodo==='GET' && preg_match('~^categorias/([1-9][0-9]*)/produtos$~',$rota,$m)) {
   $categoria=(new Categoria())->buscarPorCodigo((int)$m[1]);if(!$categoria) resposta(404,['erro'=>'Categoria não encontrada.']);
   $d=(new Produto())->listar((int)$m[1]);resposta(200,['categoria'=>$categoria,'dados'=>$d,'total'=>count($d)]);
 }
 // Só o gateway chama as operações administrativas: chave de serviço obrigatória.
 interno();
 if($metodo==='GET' && $rota==='administracao/produtos') { $d=(new Produto())->listarAdministracao(); resposta(200,['dados'=>$d,'total'=>count($d)]); }
 if($metodo==='GET' && $rota==='administracao/categorias') { $d=(new Categoria())->listarAdministracao(); resposta(200,['dados'=>$d,'total'=>count($d)]); }
 if($metodo==='POST' && in_array($rota,['produtos','categorias'],true)) {
   $d=jsonEntrada();$id=$rota==='produtos' ? (new Produto())->cadastrar(validarProduto($d)) : (new Categoria())->cadastrar(validarCategoria($d));
   resposta(201,['id'=>$id,'mensagem'=>'Cadastro criado.']);
 }
 if(preg_match('~^(produtos|categorias)/([1-9][0-9]*)$~',$rota,$m)) {
   $modelo=$m[1]==='produtos'?new Produto():new Categoria();$id=(int)$m[2];
   $anterior=$modelo->buscarParaEditar($id);if(!$anterior) resposta(404,['erro'=>'Registro não encontrado.']);
   if(in_array($metodo,['PUT','PATCH'],true)) {
       $d=jsonEntrada();$dados=$m[1]==='produtos' ? validarProduto($d,$anterior['imagem']) : validarCategoria($d);
       $modelo->atualizar($id,$dados); resposta(200,['id'=>$id,'mensagem'=>'Cadastro atualizado.']);
   }
   if($metodo==='DELETE') { $modelo->excluir($id); resposta(200,['mensagem'=>'Cadastro excluído.']); }
 }
 resposta(404,['erro'=>'Rota não encontrada.']);
} catch(JsonException|InvalidArgumentException $e) { resposta(422,['erro'=>'Dados inválidos: '.$e->getMessage()]); }
catch(mysqli_sql_exception $e) {
   error_log($e->getMessage()); if(in_array((int)$e->getCode(),[1062,1451,1452],true)) resposta(409,['erro'=>'Conflito: chave repetida ou registros relacionados.']);
   resposta(503,['erro'=>'Falha ao consultar banco do catálogo.']);
} catch(Throwable $e) {error_log($e->getMessage());resposta(503,['erro'=>'Serviço de catálogo indisponível.']);}
