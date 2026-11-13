<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class VerificarAdministrador {
    public function handle(Request $pedido,Closure $proximo):Response {
        if(!$pedido->session()->has('administrador_id')) return redirect()->route('admin.login');
        return $proximo($pedido);
    }
}
