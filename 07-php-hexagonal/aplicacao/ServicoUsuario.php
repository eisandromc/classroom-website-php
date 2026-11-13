<?php
 require_once __DIR__.'/../dominio/Portas/RepositorioUsuario.php';
 /** Caso de uso depende somente do contrato (porta), nunca do MySQL. */
 class ServicoUsuario {
  public function __construct(private RepositorioUsuario $repositorio){}
  public function autenticar(string $usuario,string $senha):?int{return $this->repositorio->autenticar($usuario,$senha);}
 }
