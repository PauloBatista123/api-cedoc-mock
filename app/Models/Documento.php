<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'documentos';

    protected $fillable = [
        'documento',
        'observacao',
        'tipo_documento_id',
        'caixa_id',
        'predio_id',
        'espaco_ocupado',
        'status',
        'nome_cooperado',
        'cpf_cooperado',
        'valor_operacao',
        'vencimento_operacao',
        'data_liquidacao',
        'data_expurgo',
        'ordem'
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

     /**
     * Scope a query to only include users of a given type.
     *
     * @param  mixed  $caixa_id
     * @return integer
     */

     public function scopeOrdem($query, $caixa_id){
        try {
            $ultima_ordem = $query->where('caixa_id', $caixa_id)->orderBy('ordem', 'desc')->first()->ordem;

            if($ultima_ordem > 0){
                return $ultima_ordem + 1;
            }else{
                return 1;
            }
        } catch (\Throwable $th) {
            return 1;
        }

     }


}
