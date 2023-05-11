<?php

namespace App\Http\Services;

use App\Models\Caixa;
use App\Models\Unidade;
use Illuminate\Support\Facades\DB;

class CaixaService {

    public function ultimaCaixa()
    {
        return Caixa::orderByDesc('caixas.id')->first();
    }

    public function espacoDisponivel($espaco_ocupado, $predio_id)
    {
        return Caixa::
                    with(['predio','documentos'])
                    ->leftjoin('documentos', function ($join) {
                        $join->on('documentos.caixa_id', '=', 'caixas.id');
                    })
                    ->selectRaw('IF(ISNULL(MAX(documentos.ordem) + 1), "1", (MAX(documentos.ordem) + 1)) as proxima_ordem')
                    ->espacoDisponivel($espaco_ocupado)
                    ->when($predio_id, function ($query) use ($predio_id) {
                        $query->where('caixas.predio_id', $predio_id);
                    })
                    ->whereNot('caixas.id', $this->ultimaCaixa()->id)
                    ->groupBy('caixas.id')
                    ->orderBy('caixas.id', 'desc')
                    ->paginate(8);
    }

    public function espacoDisponivelManual($espaco_ocupado, $predio_id, $andar_id)
    {
        return Caixa::
                    leftjoin('documentos', function ($join) {
                        $join->on('documentos.caixa_id', '=', 'caixas.id');
                    })
                    ->selectRaw('IF(ISNULL(MAX(documentos.ordem) + 1), "1", (MAX(documentos.ordem) + 1)) as proxima_ordem')
                    ->espacoDisponivel($espaco_ocupado)
                    ->when($predio_id, function ($query) use ($predio_id) {
                        $query->where('caixas.predio_id', $predio_id);
                    })
                    ->where('caixas.andar_id', $andar_id)
                    ->whereNot('caixas.id', $this->ultimaCaixa()->id)
                    ->groupBy('caixas.id')
                    ->orderBy('caixas.id', 'desc')
                    ->get();
    }

    public function alterar_espaco(
        int $caixaId,
        int $espaco_ocupado,
        int $predio_id,
        int $andar_id
    ): Caixa
    {
        //verifica se a caixa existe / se não cria uma caixa nova
        $caixa = Caixa::find($caixaId);


        if($caixa){
            //alterar caixa
            $caixa->update([
                'espaco_ocupado' => (int) $espaco_ocupado + (int) $caixa->espaco_ocupado,
                'espaco_disponivel' => (int) $caixa->espaco_disponivel - (int) $espaco_ocupado,
                'status' => ((int) $caixa->espaco_disponivel - (int) $espaco_ocupado) == 0 ? 'ocupado' : 'disponivel',
            ]);

        }else{
            //criar caixa
            $caixa = Caixa::create(
                [
                    'numero' => $caixaId,
                    'espaco_total' => 80,
                    'espaco_ocupado' => $espaco_ocupado,
                    'espaco_disponivel' => 80 - $espaco_ocupado,
                    'predio_id' => $predio_id,
                    'andar_id' => $andar_id,
                ]
            );
        }

        return $caixa;
    }
}
