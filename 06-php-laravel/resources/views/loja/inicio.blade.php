@extends('layouts.loja')
@section('titulo',isset($categoriaAtual)?$categoriaAtual->nome.' | WebShoes':'WebShoes — Laravel')
@section('conteudo')
<section class="conteiner intro-interna"><div class="trilha">WebShoes / Laravel / Eloquent</div><h1>{{ isset($categoriaAtual)?$categoriaAtual->nome:'ENCONTRE SEU PRÓXIMO PAR.' }}</h1><p>{{ $categoriaAtual->descricao ?? 'O mesmo catálogo nas diferentes arquiteturas WebShoes.' }}</p></section>
<section class="conteiner secao" style="padding-top:24px"><div class="filtros"><a class="filtro" href="{{ route('loja.inicio') }}">Todos</a>@foreach($categorias as $categoria)<a class="filtro" href="{{ route('loja.categoria',$categoria->id) }}">{{ $categoria->nome }}</a>@endforeach</div>
<form class="barra-catalogo" method="get"><div class="busca"><input name="busca" placeholder="Buscar tênis ou marca" value="{{ request('busca') }}" aria-label="Buscar"></div><select name="ordem" class="ordenacao" aria-label="Ordenar"><option value="destaques">Destaques</option><option value="menor_preco" @selected(request('ordem')==='menor_preco')>Menor preço</option><option value="maior_preco" @selected(request('ordem')==='maior_preco')>Maior preço</option><option value="nome" @selected(request('ordem')==='nome')>Nome</option></select><button class="botao-primario">Filtrar</button></form>
<div class="grade-produtos">@forelse($produtos as $produto)
<article class="card-produto"><a class="card-imagem" href="{{ route('loja.produto',$produto->id) }}">@if($produto->etiqueta)<span class="tag">{{ $produto->etiqueta }}</span>@endif
<img src="{{ str_starts_with($produto->imagem,'uploads/') ? route('loja.imagem',basename($produto->imagem)) : asset('assets/img/'.basename($produto->imagem)) }}" alt="Tênis {{ $produto->nome }}" loading="lazy"></a>
<div class="card-info"><div class="card-sobretitulo">{{ $produto->marca }} · {{ $produto->categoria->nome }}</div><h3 class="card-titulo"><a href="{{ route('loja.produto',$produto->id) }}">{{ $produto->nome }}</a></h3><span class="preco">R$ {{ number_format($produto->preco,2,',','.') }}</span></div></article>
@empty <p>Nenhum tênis encontrado.</p> @endforelse</div></section>
@endsection
