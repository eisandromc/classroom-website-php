<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>WebShoes · Laravel Admin</title><link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}"></head><body class="admin">
<header class="painel-topo"><a class="painel-logo" href="{{ route('loja.inicio') }}">WEB<em>SHOES</em> / LARAVEL ADMIN</a><nav class="painel-nav"><a href="{{ route('loja.inicio') }}">Loja</a>@if(session('administrador_id'))<form method="post" action="{{ route('admin.sair') }}">@csrf <button class="painel-botao secundario">Sair</button></form>@endif</nav></header>
<main class="painel-area">@if(session('mensagem'))<div class="painel-sucesso" role="status">{{ session('mensagem') }}</div>@endif
@if($errors->any())<div class="painel-alerta" role="alert">{{ $errors->first() }}</div>@endif
@yield('conteudo')</main></body></html>
