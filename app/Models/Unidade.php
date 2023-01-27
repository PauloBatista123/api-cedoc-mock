<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    use HasFactory;

    protected $table = 'unidades';

    protected $fillable = [
        'nome',
        'status'
    ];

    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'unidade_id', 'id');
    }
}
