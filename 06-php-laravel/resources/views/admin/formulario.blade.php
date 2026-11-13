@extends('layouts.admin')
@section('conteudo')
<div class="painel-cabecalho"><h1>{{ $registro?'Editar':'Cadastrar' }} {{ $tipo==='produtos'?'produto':'categoria' }}</h1></div>
<section class="painel-caixa"><form method="post" enctype="multipart/form-data" action="{{ $registro ? route('admin.atualizar',[$tipo,$registro->id]) : route('admin.salvar',$tipo) }}">@csrf @if($registro) @method('PUT') @endif
<div class="painel-grade">
@foreach(['nome'=>'Nome','slug'=>'Slug (opcional)'] as $campo=>$rotulo)<div class="painel-campo"><label for="{{ $campo }}">{{ $rotulo }}</label><input id="{{ $campo }}" name="{{ $campo }}" value="{{ old($campo,$registro->$campo??'') }}" {{ $campo==='nome'?'required':'' }}></div>@endforeach
@if($tipo==='categorias')
<div class="painel-campo"><label>Descrição</label><input name="descricao" value="{{ old('descricao',$registro->descricao??'') }}"></div><div class="painel-campo"><label>Cor hexadecimal</label><input type="color" name="cor" value="{{ old('cor',$registro->cor??'#F1F1F1') }}"></div>
@else
<div class="painel-campo"><label>Categoria</label><select name="categoria_id" required>@foreach($categorias as $categoria)<option value="{{ $categoria->id }}" @selected(old('categoria_id',$registro->categoria_id??0)==$categoria->id)>{{ $categoria->nome }}</option>@endforeach</select></div>
@foreach(['marca'=>'Marca','descricao_curta'=>'Descrição curta','etiqueta'=>'Etiqueta','cor'=>'Cor','tamanhos'=>'Numerações (36,37,38)'] as $campo=>$rotulo)<div class="painel-campo"><label>{{ $rotulo }}</label><input name="{{ $campo }}" value="{{ old($campo,$registro->$campo??'') }}" {{ in_array($campo,['marca','descricao_curta','cor','tamanhos'])?'required':'' }}></div>@endforeach
@foreach(['preco'=>'Preço','preco_anterior'=>'Preço anterior'] as $campo=>$rotulo)<div class="painel-campo"><label>{{ $rotulo }}</label><input name="{{ $campo }}" type="number" min="0" step="0.01" value="{{ old($campo,$registro->$campo??'') }}" {{ $campo==='preco'?'required':'' }}></div>@endforeach
<div class="painel-campo largo"><label>Descrição completa</label><textarea name="descricao" required>{{ old('descricao',$registro->descricao??'') }}</textarea></div><div class="painel-campo largo"><label>Imagem JPG, PNG ou WebP (até 3 MB)</label><input type="file" accept="image/jpeg,image/png,image/webp" name="imagem"></div>
@endif
</div><div class="painel-opcoes"><label><input type="checkbox" name="ativo" value="1" @checked(old('ativo',$registro->ativo??true))> Ativo</label>@if($tipo==='produtos')<label><input type="checkbox" name="destaque" value="1" @checked(old('destaque',$registro->destaque??false))> Destaque</label>@endif</div>
<div class="painel-botoes"><button class="painel-botao">Salvar</button><a class="painel-botao secundario" href="{{ route('admin.listar',$tipo) }}">Cancelar</a></div></form></section>
@endsection
