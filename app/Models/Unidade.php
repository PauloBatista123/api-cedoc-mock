<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    use HasFactory;

    protected $table = 'predios';

    protected $fillable = [
        'numero',
        'status',
        'observacao',
    ];

    public function andares()
    {
        return $this->hasMany(Andar::class, 'predio_id', 'id');
    }

    public static function localizacaoAndar($numero_caixas){
        if($numero_caixas >= 0 && $numero_caixas <= 7){
            return 1;
        }else if($numero_caixas >= 8 && $numero_caixas <= 14){
            return 2;
        }else if($numero_caixas >= 15 && $numero_caixas <= 21){
            return 3;
        }else if($numero_caixas >= 22 && $numero_caixas <= 28){
            return 4;
        }else if($numero_caixas >= 29 && $numero_caixas <= 35){
            return 5;
        }else if($numero_caixas >= 36 && $numero_caixas <= 42){
            return 6;
        }else if($numero_caixas >= 43 && $numero_caixas <= 49){
            return 7;
        }else if($numero_caixas >= 50 && $numero_caixas <= 56){
            return 8;
        }else{
            return 9;
        }
    }

}
