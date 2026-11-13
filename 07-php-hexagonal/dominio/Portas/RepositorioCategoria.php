<?php
interface RepositorioCategoria
{
 public function listar(): array;
 public function listarAdministracao(): array;
 public function buscarPorCodigo(int $codigo): ?array;
 public function buscarParaEditar(int $codigo): ?array;
 public function cadastrar(array $dados): int;
 public function atualizar(int $codigo, array $dados): bool;
 public function excluir(int $codigo): bool;
}
