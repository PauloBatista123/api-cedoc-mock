<?php

namespace App\Http\Services;
use App\Models\Documento;
use App\Http\Services\CaixaService;
use App\Services\RastreabilidadeService;
use Illuminate\Support\Facades\Auth;

class RepactuacaoService {

    public function __construct(
        protected CaixaService $caixaService,
        protected RastreabilidadeService $rastreabilidadeService
    ) {}


    public function salvar_fila(
        Documento $documento
    )
    {

        try{
            //salvar rastreabilidade do documento
            $this->rastreabilidadeService->create(
                'alterar',
                $documento->id,
                Auth::user()->id,
                'Documento enviado para fila de repactuação'
            );

            //alterar status para fila de repactuação
            $documento->update([
                'status' => 'fila_repactuacao',
            ]);

        }catch(\Exception $e){
            throw new \Error($e->getMessage());
        }

    }



    public function remover_endereco(
        Documento $documento,
        string $status
    )
    {

        $documento->update([
            'caixa_id' => null,
            'predio_id' => null,
            'status' => $status,
            'ordem' => 0
        ]);
    }
}
