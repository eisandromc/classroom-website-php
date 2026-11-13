<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LojaControlador;
use App\Http\Controllers\LoginControlador;
use App\Http\Controllers\AdminControlador;
Route::get('/',[LojaControlador::class,'inicio'])->name('loja.inicio');
Route::get('/categoria/{codigo}',[LojaControlador::class,'categoria'])->whereNumber('codigo')->name('loja.categoria');
Route::get('/produto/{codigo}',[LojaControlador::class,'produto'])->whereNumber('codigo')->name('loja.produto');
Route::get('/imagens/{arquivo}',[LojaControlador::class,'imagem'])->name('loja.imagem');
Route::get('/admin/login',[LoginControlador::class,'formulario'])->name('admin.login');
Route::post('/admin/login',[LoginControlador::class,'entrar'])->name('admin.entrar');
Route::middleware('administrador')->prefix('admin')->group(function() {
  Route::post('/sair',[LoginControlador::class,'sair'])->name('admin.sair');
  Route::get('/{tipo}',[AdminControlador::class,'listar'])->whereIn('tipo',['produtos','categorias'])->name('admin.listar');
  Route::get('/{tipo}/novo',[AdminControlador::class,'formulario'])->whereIn('tipo',['produtos','categorias'])->name('admin.novo');
  Route::post('/{tipo}',[AdminControlador::class,'salvar'])->whereIn('tipo',['produtos','categorias'])->name('admin.salvar');
  Route::get('/{tipo}/{codigo}/editar',[AdminControlador::class,'formulario'])->whereNumber('codigo')->name('admin.editar');
  Route::put('/{tipo}/{codigo}',[AdminControlador::class,'salvar'])->whereNumber('codigo')->name('admin.atualizar');
  Route::delete('/{tipo}/{codigo}',[AdminControlador::class,'excluir'])->whereNumber('codigo')->name('admin.excluir');
});
