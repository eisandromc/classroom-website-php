<?php
namespace App\Http\Controllers;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
class AdminControlador extends Controller {
    private function classe(string $tipo):string {
        abort_unless(in_array($tipo,['produtos','categorias'],true),404);
        return $tipo==='produtos'?Produto::class:Categoria::class;
    }
    public function listar(string $tipo){
        $classe=$this->classe($tipo);
        $registros=$tipo==='produtos'?$classe::with('categoria')->orderByDesc('id')->get():$classe::withCount('produtos')->orderByDesc('id')->get();
        return view('admin.listar',compact('tipo','registros'));
    }
    public function formulario(string $tipo,?int $codigo=null){
        $classe=$this->classe($tipo);
        $registro=$codigo?$classe::findOrFail($codigo):null;
        $categorias=Categoria::orderBy('nome')->get();
        return view('admin.formulario',compact('tipo','registro','categorias'));
    }
    private function validar(Request $pedido,string $tipo,?int $codigo=null):array {
        $this->classe($tipo);
        $slug=['nullable','string','max:150','regex:/^[a-z0-9-]*$/',Rule::unique($tipo,'slug')->ignore($codigo)];
        if($tipo==='categorias') {
            $slug[2]='max:100';
            $dados=$pedido->validate(['nome'=>'required|string|max:80','slug'=>$slug,'descricao'=>'nullable|string|max:255','cor'=>['required','regex:/^#[0-9A-Fa-f]{6}$/']]);
            $dados['ativo']=$pedido->boolean('ativo');
        } else {
            $dados=$pedido->validate([
              'categoria_id'=>'required|integer|exists:categorias,id','nome'=>'required|string|max:120','slug'=>$slug,'marca'=>'required|string|max:70',
              'descricao_curta'=>'required|string|max:250','descricao'=>'required|string|max:65000','preco'=>'required|numeric|min:0|max:99999999.99',
              'preco_anterior'=>'nullable|numeric|min:0|max:99999999.99','etiqueta'=>'nullable|string|max:30',
              'cor'=>'required|string|max:80','tamanhos'=>'required|string|max:80',
              'imagem'=>'nullable|file|image|mimes:jpg,jpeg,png,webp|max:3072'
            ]);
            $dados['ativo']=$pedido->boolean('ativo');$dados['destaque']=$pedido->boolean('destaque');
            unset($dados['imagem']);
            if($pedido->hasFile('imagem')) {
                $arquivo=$pedido->file('imagem');
                $mime=$arquivo->getMimeType();
                $extensoes=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if(!isset($extensoes[$mime])) abort(422,'Formato de imagem inválido.');
                $dimensoes=@getimagesize($arquivo->getRealPath());
                if(!$dimensoes || $dimensoes[0]>4000 || $dimensoes[1]>4000) abort(422,'Dimensões inválidas.');
                $pasta=dirname(base_path()).'/imagens/uploads';
                if(!is_dir($pasta) || !is_writable($pasta)) abort(503,'Pasta de upload indisponível.');
                $nome=bin2hex(random_bytes(16)).'.'.$extensoes[$mime];
                $arquivo->move($pasta,$nome);
                $dados['imagem']='uploads/'.$nome;
            }
        }
        $dados['slug']=Str::slug($dados['slug']?:$dados['nome']);
        if($this->classe($tipo)::query()->where('slug',$dados['slug'])->when($codigo,fn($q)=>$q->where('id','!=',$codigo))->exists()) {
           throw ValidationException::withMessages(['slug'=>'Este slug já está cadastrado.']);
        }
        if($tipo==='categorias') $dados['descricao']=$dados['descricao']??'';
        else {
          $dados['etiqueta']=$dados['etiqueta']??'';
          if(!$codigo && !isset($dados['imagem'])) $dados['imagem']='tenis-01.svg';
          $dados['preco_anterior']=$dados['preco_anterior']??null;
        }
        return $dados;
    }
    public function salvar(Request $pedido,string $tipo,?int $codigo=null) {
        $classe=$this->classe($tipo);
        $dados=$this->validar($pedido,$tipo,$codigo);
        if($codigo) $classe::findOrFail($codigo)->update($dados);
        else $classe::create($dados);
        return redirect()->route('admin.listar',['tipo'=>$tipo])->with('mensagem','Cadastro salvo.');
    }
    public function excluir(string $tipo,int $codigo){
        $classe=$this->classe($tipo);
        try { $classe::findOrFail($codigo)->delete(); }
        catch(\Illuminate\Database\QueryException $erro) {
          Log::warning('Exclusão impedida pelo banco',['codigo'=>$codigo,'tipo'=>$tipo]);
          return back()->withErrors(['excluir'=>'Não foi possível excluir: existem registros relacionados ou houve conflito.']);
        }
        return redirect()->route('admin.listar',['tipo'=>$tipo])->with('mensagem','Cadastro excluído.');
    }
}
