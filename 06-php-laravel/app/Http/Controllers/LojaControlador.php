<?php
namespace App\Http\Controllers;
use App\Models\Categoria;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\View\View;
class LojaControlador extends Controller {
    private function filtrar(Request $pedido,?int $categoria=null) {
        $consulta=Produto::query()->with('categoria')->where('ativo',1)->whereHas('categoria',fn($q)=>$q->where('ativo',1));
        if($categoria) $consulta->where('categoria_id',$categoria);
        $busca=trim((string)$pedido->query('busca',''));
        if($busca!=='') $consulta->where(function($q)use($busca){$q->where('nome','like','%'.$busca.'%')->orWhere('marca','like','%'.$busca.'%')->orWhere('descricao_curta','like','%'.$busca.'%');});
        switch($pedido->query('ordem','destaques')){
          case 'menor_preco':$consulta->orderBy('preco');break;
          case 'maior_preco':$consulta->orderByDesc('preco');break;
          case 'nome':$consulta->orderBy('nome');break;
          default:$consulta->orderByDesc('destaque')->orderBy('id');
        }
        return $consulta->get();
    }
    public function inicio(Request $pedido):View {
        return view('loja.inicio',['categorias'=>Categoria::where('ativo',1)->get(),'produtos'=>$this->filtrar($pedido)]);
    }
    public function categoria(Request $pedido,int $codigo):View {
        $categoria=Categoria::where('ativo',1)->findOrFail($codigo);
        return view('loja.inicio',['categorias'=>Categoria::where('ativo',1)->get(),'categoriaAtual'=>$categoria,'produtos'=>$this->filtrar($pedido,$codigo)]);
    }
    public function produto(int $codigo):View {
        $produto=Produto::with('categoria')->where('ativo',1)->whereHas('categoria',fn($q)=>$q->where('ativo',1))->findOrFail($codigo);
        return view('loja.produto',compact('produto'));
    }
    public function imagem(string $arquivo) {
        if(!preg_match('/^[a-f0-9]{32}\.(?:png|jpg|webp)$/D',$arquivo)) abort(404);
        $caminho=dirname(base_path()).'/imagens/uploads/'.$arquivo;
        if(!is_file($caminho)) abort(404);
        return response()->file($caminho,['X-Content-Type-Options'=>'nosniff']);
    }
}
