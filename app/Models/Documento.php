<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'tipo_documento_id',
        'caixa_id',
        'espaco_ocupado',
        'status',
        'predio_id',
        'observacao',
        'nome_cooperado',
        'cpf_cooperado',
        'valor_operacao',
        'data_liquidacao',
        'data_expurgo',
    ];

    protected $with = ['tipoDocumento'];

    public function caixa()
    {
        return $this->belongsTo(Caixa::class, 'caixa_id', 'id');
    }

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id', 'id');
    }

    public function predio()
    {
        return $this->belongsTo(Unidade::class, 'predio_id', 'id');
    }


}
