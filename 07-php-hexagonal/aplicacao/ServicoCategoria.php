<?php
 require_once __DIR__.'/../dominio/Portas/RepositorioCategoria.php';
 /** Caso de uso depende somente do contrato (porta), nunca do MySQL. */
 class ServicoCategoria {
  public function __construct(private RepositorioCategoria $repositorio){}
  public function listar():array{return $this->repositorio->listar();}
 public function listarAdministracao():array{return $this->repositorio->listarAdministracao();}
 public function buscarPorCodigo(int $id):?array{return $this->repositorio->buscarPorCodigo($id);}
 public function buscarParaEditar(int $id):?array{return $this->repositorio->buscarParaEditar($id);}
 public function cadastrar(array $dados):int{return $this->repositorio->cadastrar($dados);}
 public function atualizar(int $id,array $dados):bool{return $this->repositorio->atualizar($id,$dados);}
 public function excluir(int $id):bool{return $this->repositorio->excluir($id);}
 }
