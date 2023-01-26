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

    public function unidade()
    {
        return $this->belongsTo('App\Models\Unidade', 'unidade_id', 'id');
    }

    public function endereco()
    {
        return $this->belongsTo('App\Models\Endereco', 'endereco_id', 'id');
    }
}
