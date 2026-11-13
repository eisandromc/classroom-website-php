<?php
require_once __DIR__.'/../../dominio/Portas/ArmazenamentoImagem.php';
require_once __DIR__.'/../../includes/Imagens.php';
class ArmazenamentoImagemLocal implements ArmazenamentoImagem
{
    public function salvar(?array $arquivo): ?string { return salvarImagem($arquivo); }
}
