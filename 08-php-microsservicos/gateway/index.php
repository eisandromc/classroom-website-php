<?php
/** API Gateway: sessão, segurança HTTP e chamadas aos dois microsserviços. NENHUM SQL neste processo. */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
require_once __DIR__.'/includes/Autenticacao.php';
require_once __DIR__.'/includes/Imagens.php';
$config=require __DIR__.'/../configuracao.php';
iniciarSessaoAdmin('webshoes_gateway_14');
function responderGateway(int $codigo,array $dados):never {http_response_code($codigo);echo json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function jsonGateway():array {
  if(!str_starts_with(strtolower($_SERVER['CONTENT_TYPE']??''),'application/json')) throw new InvalidArgumentException('Content-Type JSON obrigatório.');
  $texto=file_get_contents('php://input');
  if($texto===false || strlen($texto)>1024*1024) throw new InvalidArgumentException('Corpo grande demais.');
  $dados=json_decode($texto,true,32,JSON_THROW_ON_ERROR);
  if(!is_array($dados)||array_is_list($dados)) throw new InvalidArgumentException('Objeto JSON obrigatório.');
  return $dados;
}
/** HTTP entre processos: URLs fixas no arquivo de configuração, nenhuma URL enviada pelo usuário. */
function solicitarServico(string $servico,string $rota,string $metodo='GET',?array $dados=null,array $parametros=[]):array {
 global $config;
 $url=$config[$servico].'?'.http_build_query(array_merge(['rota'=>$rota],$parametros));
 $cabecalhos="Accept: application/json\r\nX-WebShoes-Interno: ".$config['chave']."\r\n";
 $opcoes=['method'=>$metodo,'timeout'=>4,'ignore_errors'=>true,'header'=>$cabecalhos];
 if($dados!==null) {$opcoes['content']=json_encode($dados,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);$opcoes['header'].="Content-Type: application/json\r\n";}
 $contexto=stream_context_create(['http'=>$opcoes]);
 $corpo=@file_get_contents($url,false,$contexto);
 if($corpo===false) throw new RuntimeException('Serviço '.$servico.' indisponível.');
 $status=503;
 foreach(($http_response_header??[]) as $cabecalho) if(preg_match('~^HTTP/\S+\s+(\d+)~',$cabecalho,$m)) $status=(int)$m[1];
 $resposta=json_decode($corpo,true);
 if(!is_array($resposta)) throw new RuntimeException('Serviço '.$servico.' retornou resposta inválida.');
 return [$status,$resposta];
}
$rota=trim((string)($_GET['rota']??'status'),'/');$metodo=$_SERVER['REQUEST_METHOD']??'GET';
try {
 if($rota==='status' && $metodo==='GET') responderGateway(200,['servico'=>'gateway','status'=>'online','banco'=>'nao_verificado']);
 if($rota==='saude' && $metodo==='GET') {
   [$s1,$c]=solicitarServico('catalogo','saude');[$s2,$i]=solicitarServico('identidade','saude');
   responderGateway(($s1===200&&$s2===200)?200:503,['catalogo'=>$c,'identidade'=>$i]);
 }
 if($rota==='sessao' && $metodo==='GET') responderGateway(200,['autenticado'=>administradorAutenticado(),'token'=>tokenFormulario()]);
 if($rota==='login' && $metodo==='POST') {
   verificarToken($_SERVER['HTTP_X_CSRF_TOKEN']??null);permitirTentativaLogin();$dados=jsonGateway();
   [$status,$resultado]=solicitarServico('identidade','entrar','POST',['usuario'=>(string)($dados['usuario']??''),'senha'=>(string)($dados['senha']??'')]);
   if($status!==200 || empty($resultado['id'])){
      registrarFalhaLogin();responderGateway($status===503?503:401,['erro'=>$status===503?'Serviço de identidade indisponível.':'Usuário ou senha inválidos.']);
   }
   entrarAdministrador((int)$resultado['id']);responderGateway(200,['autenticado'=>true,'token'=>tokenFormulario()]);
 }
 if($rota==='logout' && $metodo==='POST') {
   if(!administradorAutenticado()) responderGateway(401,['erro'=>'Autenticação necessária.']);
   verificarToken($_SERVER['HTTP_X_CSRF_TOKEN']??null);sairAdministrador();responderGateway(200,['autenticado'=>false]);
 }
 // As consultas públicas não requerem sessão nem acesso SQL no gateway.
 $publicas=($metodo==='GET') && (in_array($rota,['produtos','categorias'],true)
    || (bool)preg_match('~^(produtos/[1-9][0-9]*|categorias/[1-9][0-9]*/produtos)$~',$rota));
 if($publicas) {
   $params=[];foreach(['categoria','busca','ordem'] as $chave) if(isset($_GET[$chave]) && is_scalar($_GET[$chave])) $params[$chave]=(string)$_GET[$chave];
   [$codigo,$dados]=solicitarServico('catalogo',$rota,'GET',null,$params);responderGateway($codigo,$dados);
 }
 $administrativa=str_starts_with($rota,'administracao/') || in_array($rota,['produtos','categorias','imagens'],true)
   || (bool)preg_match('~^(produtos|categorias)/[1-9][0-9]*$~',$rota);
 if($administrativa) {
    if(!administradorAutenticado()) responderGateway(401,['erro'=>'Autentique-se para gerenciar cadastros.']);
    if($metodo!=='GET') verificarToken($_SERVER['HTTP_X_CSRF_TOKEN']??null);
    if($rota==='imagens' && $metodo==='POST') {
      $nome=salvarImagem($_FILES['imagem']??null);
      if(!$nome) throw new InvalidArgumentException('Selecione uma imagem.');
      responderGateway(201,['imagem'=>$nome]);
    }
    $dados=in_array($metodo,['POST','PUT','PATCH'],true)?jsonGateway():null;
    [$codigo,$resposta]=solicitarServico('catalogo',$rota,$metodo,$dados);
    responderGateway($codigo,$resposta);
 }
 responderGateway(404,['erro'=>'Rota inexistente.']);
} catch(JsonException|InvalidArgumentException|RuntimeException $e) {
  if(str_contains($e->getMessage(),'token de segurança inválido')) responderGateway(403,['erro'=>'Token CSRF inválido.']);
  if($e instanceof RuntimeException && str_starts_with($e->getMessage(),'Serviço')) responderGateway(503,['erro'=>'Microsserviço indisponível: '.$e->getMessage()]);
  responderGateway(422,['erro'=>$e->getMessage()]);
} catch(Throwable $e) {error_log($e->getMessage());responderGateway(503,['erro'=>'Gateway indisponível.']);}
