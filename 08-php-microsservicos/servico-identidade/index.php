<?php
/** Microsserviço identidade: banco separado; não conhece tabela produtos. */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require_once __DIR__.'/classes/Usuario.php';
$config=require __DIR__.'/../configuracao.php';
function responderIdentidade(int $codigo,array $dados):never {
 http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE);exit;
}
$chave=(string)($_SERVER['HTTP_X_WEBSHOES_INTERNO']??'');
if(!$chave || !hash_equals($config['chave'],$chave)) responderIdentidade(403,['erro'=>'Somente comunicação interna.']);
$rota=$_GET['rota']??''; $metodo=$_SERVER['REQUEST_METHOD']??'GET';
try {
 if($rota==='saude' && $metodo==='GET') {
   (new ConexaoBanco())->conectar()->query('SELECT 1');
   responderIdentidade(200,['servico'=>'identidade','banco'=>'conectado']);
 }
 if($rota==='entrar' && $metodo==='POST') {
   $corpo=file_get_contents('php://input');
   if($corpo===false || strlen($corpo)>8192) responderIdentidade(422,['erro'=>'Dados inválidos.']);
   $dados=json_decode($corpo,true,8,JSON_THROW_ON_ERROR);
   if(!is_array($dados)) responderIdentidade(422,['erro'=>'JSON inválido.']);
   $usuario=(string)($dados['usuario']??'');$senha=(string)($dados['senha']??'');
   if(strlen($usuario)>60 || strlen($senha)>256) responderIdentidade(401,['erro'=>'Credenciais inválidas.']);
   $id=(new Usuario())->autenticar($usuario,$senha);
   if(!$id) responderIdentidade(401,['erro'=>'Credenciais inválidas.']);
   responderIdentidade(200,['id'=>$id]);
 }
 responderIdentidade(404,['erro'=>'Rota inexistente.']);
} catch(Throwable $erro) {error_log($erro->getMessage());responderIdentidade(503,['erro'=>'Identidade indisponível.']);}
