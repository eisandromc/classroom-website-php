<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  if(!Schema::hasTable('categorias')) Schema::create('categorias',function(Blueprint $t){
    $t->increments('id');$t->string('nome',80);$t->string('slug',100)->unique();$t->string('descricao',255);
    $t->string('cor',7)->default('#F1F1F1');$t->boolean('ativo')->default(true);
  });
  if(!Schema::hasTable('produtos')) Schema::create('produtos',function(Blueprint $t){
    $t->increments('id');$t->unsignedInteger('categoria_id');$t->string('nome',120);$t->string('slug',150)->unique();
    $t->string('marca',70);$t->string('descricao_curta',250);$t->text('descricao');
    $t->decimal('preco',10,2);$t->decimal('preco_anterior',10,2)->nullable();$t->string('imagem',100);
    $t->string('etiqueta',30)->default('');$t->string('cor',80);$t->string('tamanhos',80);
    $t->boolean('destaque')->default(false);$t->boolean('ativo')->default(true);
    $t->timestamp('criado_em')->useCurrent();
    $t->foreign('categoria_id')->references('id')->on('categorias')->restrictOnDelete()->cascadeOnUpdate();
    $t->index(['categoria_id','ativo']);
  });
  if(!Schema::hasTable('usuarios_administrativos')) Schema::create('usuarios_administrativos',function(Blueprint $t){
    $t->increments('id');$t->string('usuario',60)->unique();$t->string('senha_hash',255);
    $t->boolean('ativo')->default(true);$t->timestamp('criado_em')->useCurrent();
  });
 }
 public function down():void {
  // Didático e intencional: não apagar o banco compartilhado ao reverter uma migration.
  // Consulte o SQL original se quiser criar um banco de testes descartável.
 }
};
