<?php
interface RepositorioUsuario
{
 public function autenticar(string $usuario, string $senha): ?int;
}
