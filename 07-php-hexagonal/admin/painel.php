<?php
// Variáveis do controlador/página: $urlPainel, $urlProcessar, $urlLoja, $prefixoImagens,
// $autenticado, $tipo, $acao, $registro, $produtos, $categorias, $token, $mensagem, $erroMensagem.
function enderecoPainel(array $parametros = []): string {
    global $urlPainel;
    return $urlPainel . ($parametros ? (str_contains($urlPainel, '?') ? '&' : '?') . http_build_query($parametros) : '');
}
function campoAdmin(string $nome, string $rotulo, array $registro, string $tipoCampo = 'text', bool $obrigatorio = false, string $atributos = ''): void {
    $valor = htmlspecialchars((string)($registro[$nome] ?? ($tipoCampo === 'color' ? '#F1F1F1' : '')), ENT_QUOTES, 'UTF-8');
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $rotuloSeguro = htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8');
    echo '<div class="painel-campo"><label for="' . $nomeSeguro . '">' . $rotuloSeguro . '</label><input id="' . $nomeSeguro . '" name="' . $nomeSeguro . '" type="' . $tipoCampo . '" value="' . $valor . '" ' . ($obrigatorio ? 'required ' : '') . $atributos . '></div>';
}
?><!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Administração | WebShoes</title><link rel="stylesheet" href="<?= escapar($prefixoImagens) ?>assets/css/admin.css"></head>
<body class="admin"><header class="painel-topo"><a class="painel-logo" href="<?= escapar($urlLoja) ?>">WEB<em>SHOES</em> <small> / ADMIN</small></a>
<nav class="painel-nav" aria-label="Menu administrativo"><a href="<?= escapar($urlLoja) ?>">Voltar à loja</a>
<?php if ($autenticado): ?><a href="<?= escapar(enderecoPainel()) ?>">Painel</a><form action="<?= escapar($urlProcessar) ?>" method="post"><input type="hidden" name="token" value="<?= escapar($token) ?>"><input type="hidden" name="acao" value="sair"><button class="painel-botao secundario">Sair</button></form><?php endif; ?></nav></header>
<main class="painel-area">
<?php if ($mensagem): ?><div class="painel-sucesso" role="status"><?= escapar($mensagem) ?></div><?php endif; ?>
<?php if ($erroMensagem): ?><div class="painel-alerta" role="alert"><?= escapar($erroMensagem) ?></div><?php endif; ?>
<?php if (!$autenticado): ?>
<section class="painel-caixa painel-login"><p>ÁREA RESTRITA</p><h1>Acesse o painel</h1><p>Use as credenciais demonstrativas. Altere-as antes de publicar.</p>
<form method="post" action="<?= escapar($urlProcessar) ?>" class="painel-grade">
<input type="hidden" name="acao" value="entrar"><input type="hidden" name="token" value="<?= escapar($token) ?>">
<div class="painel-campo largo"><label for="usuario">Usuário</label><input type="text" id="usuario" name="usuario" required autocomplete="username"></div>
<div class="painel-campo largo"><label for="senha">Senha</label><input type="password" id="senha" name="senha" required autocomplete="current-password"></div>
<button class="painel-botao" type="submit">Entrar</button></form></section>
<?php else: ?>
<div class="painel-cabecalho"><div><p>WEB SHOES / PAINEL</p><h1>Gestão da loja</h1><p>Cadastre, edite e exclua categorias e tênis. Padrão de portas e adaptadores; banco compartilhado.</p></div>
<?php if ($acao !== 'formulario'): ?><a class="painel-botao" href="<?= escapar(enderecoPainel(['tipo'=>$tipo,'acao'=>'formulario'])) ?>">+ <?= $tipo==='produtos' ? 'Novo produto' : 'Nova categoria' ?></a><?php endif; ?></div>
<nav class="painel-abas" aria-label="Cadastros"><a href="<?= escapar(enderecoPainel(['tipo'=>'produtos'])) ?>" <?= $tipo==='produtos' ? 'aria-current="page"' : '' ?>>Produtos</a>
<a href="<?= escapar(enderecoPainel(['tipo'=>'categorias'])) ?>" <?= $tipo==='categorias' ? 'aria-current="page"' : '' ?>>Categorias</a></nav>
<?php if ($acao === 'formulario'): ?>
<section class="painel-caixa"><h2><?= $registro ? 'Editar' : 'Cadastrar' ?> <?= $tipo === 'produtos' ? 'produto' : 'categoria' ?></h2>
<form method="post" action="<?= escapar($urlProcessar) ?>" enctype="multipart/form-data">
<input type="hidden" name="acao" value="salvar"><input type="hidden" name="tipo" value="<?= escapar($tipo) ?>"><input type="hidden" name="codigo" value="<?= (int)($registro['id'] ?? 0) ?>"><input type="hidden" name="token" value="<?= escapar($token) ?>">
<div class="painel-grade">
<?php if ($tipo==='categorias'): ?>
<?php campoAdmin('nome','Nome da categoria',$registro,'text',true,'maxlength="80"'); ?>
<?php campoAdmin('slug','Slug (vazio: gerado a partir do nome)',$registro,'text',false,'maxlength="100"'); ?>
<?php campoAdmin('descricao','Descrição',$registro,'text',false,'maxlength="255"'); ?>
<?php campoAdmin('cor','Cor da categoria (hexadecimal)',$registro,'color',true); ?>
<?php else: ?>
<?php campoAdmin('nome','Nome do tênis',$registro,'text',true,'maxlength="120"'); ?>
<?php campoAdmin('slug','Slug (opcional)',$registro,'text',false,'maxlength="150"'); ?>
<?php campoAdmin('marca','Marca',$registro,'text',true,'maxlength="70"'); ?>
<div class="painel-campo"><label for="categoria_id">Categoria</label><select name="categoria_id" id="categoria_id" required>
<?php foreach ($categorias as $categoria): ?><option value="<?= (int)$categoria['id'] ?>" <?= (int)($registro['categoria_id'] ?? 0)===(int)$categoria['id'] ? 'selected' : '' ?>><?= escapar($categoria['nome']) ?></option><?php endforeach; ?>
</select></div>
<?php campoAdmin('preco','Preço (R$)',$registro,'number',true,'min="0" step="0.01"'); ?>
<?php campoAdmin('preco_anterior','Preço anterior (opcional)',$registro,'number',false,'min="0" step="0.01"'); ?>
<?php campoAdmin('descricao_curta','Descrição curta',$registro,'text',true,'maxlength="250"'); ?>
<?php campoAdmin('etiqueta','Etiqueta (NOVO, DROP...)',$registro,'text',false,'maxlength="30"'); ?>
<?php campoAdmin('cor','Cores do tênis',$registro,'text',true,'maxlength="80"'); ?>
<?php campoAdmin('tamanhos','Numerações (ex.: 36,37,38,39)',$registro,'text',true,'maxlength="80"'); ?>
<div class="painel-campo largo"><label for="descricao">Descrição completa</label><textarea id="descricao" name="descricao" required><?= escapar($registro['descricao'] ?? '') ?></textarea></div>
<div class="painel-campo largo"><label for="imagem">Imagem do tênis · JPG, PNG ou WEBP · até 3 MB</label><input type="file" id="imagem" name="imagem" accept="image/jpeg,image/png,image/webp">
<?php if (!empty($registro['imagem'])): ?><div><img class="painel-miniatura" src="<?= escapar(enderecoImagem($registro['imagem'],$prefixoImagens)) ?>" alt="Imagem atual do produto"></div><?php else: ?><p class="painel-dica">Sem upload, será usada a imagem ilustrativa inicial.</p><?php endif; ?></div>
<?php endif; ?></div>
<div class="painel-opcoes"><label><input type="checkbox" name="ativo" value="1" <?= (int)($registro['ativo'] ?? 1)===1 ? 'checked' : '' ?>> Ativo</label>
<?php if ($tipo==='produtos'): ?><label><input type="checkbox" name="destaque" value="1" <?= (int)($registro['destaque'] ?? 0)===1 ? 'checked' : '' ?>> Em destaque</label><?php endif; ?></div>
<div class="painel-botoes"><button type="submit" class="painel-botao">Salvar</button><a class="painel-botao secundario" href="<?= escapar(enderecoPainel(['tipo'=>$tipo])) ?>">Cancelar</a></div>
</form></section>
<?php else: ?>
<section class="painel-caixa"><h2><?= $tipo==='produtos' ? 'Produtos cadastrados' : 'Categorias cadastradas' ?></h2><div class="painel-tabela-rolagem"><table>
<thead><tr><th>Identificação</th><th>Nome</th><th><?= $tipo==='produtos' ? 'Preço' : 'Quantidade' ?></th><th>Situação</th><th>Ações</th></tr></thead><tbody>
<?php foreach (($tipo==='produtos' ? $produtos : $categorias) as $item): ?>
<tr><td><?php if($tipo==='produtos'): ?><img class="painel-miniatura" src="<?= escapar(enderecoImagem($item['imagem'],$prefixoImagens)) ?>" alt="Imagem de <?= escapar($item['nome']) ?>"><?php else: ?>#<?= (int)$item['id'] ?><?php endif; ?></td>
<td><strong><?= escapar($item['nome']) ?></strong><div class="painel-dica"><?= escapar($tipo==='produtos' ? $item['categoria_nome'] : $item['slug']) ?></div></td>
<td><?= $tipo==='produtos' ? escapar('R$ '.number_format((float)$item['preco'],2,',','.')) : (int)$item['quantidade_produtos'].' produto(s)' ?></td>
<td><?= (int)$item['ativo']===1 ? 'Ativo' : 'Inativo' ?></td>
<td><div class="painel-botoes"><a class="painel-botao secundario" href="<?= escapar(enderecoPainel(['tipo'=>$tipo,'acao'=>'formulario','id'=>(int)$item['id']])) ?>">Editar</a>
<form method="post" action="<?= escapar($urlProcessar) ?>" onsubmit="return confirm('Excluir definitivamente este registro?')"><input type="hidden" name="token" value="<?= escapar($token) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="tipo" value="<?= escapar($tipo) ?>"><input type="hidden" name="codigo" value="<?= (int)$item['id'] ?>"><button class="painel-botao perigo">Excluir</button></form></div></td></tr>
<?php endforeach; ?></tbody></table></div>
<?php if (!($tipo==='produtos' ? $produtos : $categorias)): ?><p class="painel-vazio">Nenhum cadastro encontrado.</p><?php endif; ?></section>
<?php endif; ?>
<?php endif; ?>
</main></body></html>
