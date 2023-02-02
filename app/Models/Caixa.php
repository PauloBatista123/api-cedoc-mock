<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    use HasFactory;

    protected $table = 'caixas';

    protected $fillable = [
        'status', 'numero', 'espaco_total', 'espaco_ocupado', 'espaco_disponivel', 'unidade_id', 'endereco_id'
    ];

    public function predio()
    {
        return $this->belongsTo(Unidade::class, 'predio_id', 'id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'caixa_id', 'id');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $espaco
     * @return \Illuminate\Database\Eloquent\Builder
     */

     public function scopeEspacoDisponivel($query, $espaco){
        return $query->where([['status', '=' ,'disponivel'], ['espaco_disponivel', '>=', $espaco]]);
     }

}
