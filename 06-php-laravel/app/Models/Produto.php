<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Produto extends Model {
    protected $table='produtos';
    public $timestamps=false;
    protected $fillable=['categoria_id','nome','slug','marca','descricao_curta','descricao','preco','preco_anterior','imagem','etiqueta','cor','tamanhos','destaque','ativo'];
    protected $casts=['ativo'=>'boolean','destaque'=>'boolean','preco'=>'decimal:2','preco_anterior'=>'decimal:2'];
    public function categoria():BelongsTo { return $this->belongsTo(Categoria::class,'categoria_id'); }
}
