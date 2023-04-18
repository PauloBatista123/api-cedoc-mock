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
}
