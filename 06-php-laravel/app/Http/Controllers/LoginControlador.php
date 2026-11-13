<?php
namespace App\Http\Controllers;
use App\Models\UsuarioAdministrativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
class LoginControlador extends Controller {
    public function formulario(){return view('admin.login');}
    public function entrar(Request $pedido){
        $dados=$pedido->validate(['usuario'=>'required|string|max:60','senha'=>'required|string']);
        $chave='webshoes-login:'.$pedido->ip().':'.mb_strtolower($dados['usuario']);
        if(RateLimiter::tooManyAttempts($chave,6)) return back()->withErrors(['usuario'=>'Muitas tentativas. Aguarde alguns minutos.']);
        $usuario=UsuarioAdministrativo::where('usuario',$dados['usuario'])->where('ativo',1)->first();
        if(!$usuario || !password_verify($dados['senha'],$usuario->senha_hash)){
            RateLimiter::hit($chave,300);
            return back()->withErrors(['usuario'=>'Usuário ou senha inválidos.']);
        }
        RateLimiter::clear($chave);
        $pedido->session()->regenerate();
        $pedido->session()->put('administrador_id',$usuario->id);
        return redirect()->route('admin.listar',['tipo'=>'produtos']);
    }
    public function sair(Request $pedido){$pedido->session()->invalidate();$pedido->session()->regenerateToken();return redirect()->route('admin.login');}
}
