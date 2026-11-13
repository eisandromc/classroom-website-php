<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UsuarioAdministrativo extends Model {
    protected $table='usuarios_administrativos';
    public $timestamps=false;
    protected $hidden=['senha_hash'];
    protected $fillable=['usuario','senha_hash','ativo'];
}
