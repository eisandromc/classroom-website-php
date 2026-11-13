<?php
require_once __DIR__.'/../aplicacao/ServicoCategoria.php';
class RepositorioFalso implements RepositorioCategoria {
  public function listar():array{return [['id'=>10,'nome'=>'Categoria em memória']];}
  public function listarAdministracao():array{return $this->listar();}
  public function buscarPorCodigo(int $codigo):?array{return $this->listar()[0];}
  public function buscarParaEditar(int $codigo):?array{return $this->listar()[0];}
  public function cadastrar(array $dados):int{return 11;}
  public function atualizar(int $codigo,array $dados):bool{return true;}
  public function excluir(int $codigo):bool{return true;}
}
$servico=new ServicoCategoria(new RepositorioFalso());
assert($servico->listar()[0]['nome']==='Categoria em memória');
assert($servico->cadastrar([])===11);
echo "OK: casos de uso testados sem MySQL\n";
