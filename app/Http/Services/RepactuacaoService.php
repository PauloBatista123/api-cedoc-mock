<?php

namespace App\Http\Services;
use App\Models\Documento;
use App\Models\Repactuacao;
use App\Http\Services\CaixaService;
use App\Http\Services\DocumentoService;
use App\Services\RastreabilidadeService;
use Illuminate\Support\Facades\Auth;

class RepactuacaoService {

    public function __construct(
        protected CaixaService $caixaService,
        protected DocumentoService $documentoService,
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


    /**
     * Função para remover o endereço do documento
     *
     * @param Documento $documento
     * @param string $status
     */
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

    /**
     * Função para endereçar documentos repactuados
     *
     * @param Documento $documento
     *
     */

     public function enderecar(
        array $documentos,
        int $caixa_id,
        string $espaco_ocupado,
        string|null $observacao,
        string $ordem,
        int $predio_id,
        int $andar_id,
        int $documento_pai_id,
     )
     {

        try {

            foreach ($documentos as $doc) {
                $documento = $this->documentoService->findById($doc['id']);

                if(!is_null($documento->caixa_id)){
                    $descricao = 'Documento arquivado anteriormente no Prédio '.$documento->predio_id.', Caixa '.$documento->caixa_id.' Ordem '.$documento->ordem;
                }elseif(!is_null($documento->caixa_id) && $documento->id == $documento_pai_id){
                    $descricao = 'Dossiê de Repactuação';
                }else{
                    $descricao = 'Documento não possuía endereçamento anterior, sendo seu primeiro endereço no dossiê de repactuação.';
                }

                if(!is_null($observacao) && !is_null($doc['observacao'])){
                    $concatObservacao = "Observação Repactuação:".$observacao." \n\n Observação Anterior:".$doc['observacao'];
                }else{
                    $concatObservacao = "Sem informações...";
                }

                $documento->update([
                    'espaco_ocupado' => $doc['espaco_ocupado'],
                    'status' => 'repactuacao',
                    'caixa_id' => $caixa_id,
                    'predio_id' => $predio_id,
                    'observacao' => $concatObservacao,
                    'ordem' => $ordem,
                ]);

                $this->rastreabilidadeService->create(
                    'repactuar',
                    $documento->id,
                    Auth()->user()->id,
                    $descricao
                );

                $this->repactuacoes($documento, $documento_pai_id);
            }

            //verifica se a caixa existe / se não cria uma caixa nova
            $caixa = $this->caixaService->alterar_espaco(
                $caixa_id, $espaco_ocupado, $predio_id, $andar_id
            );

        } catch (\Throwable $th) {
            throw $th;
        }
     }

     public function repactuacoes(
        Documento $documento,
        int $documento_pai_id
     )
     {
        try {

            return Repactuacao::create([
                'user_id' => Auth()->user()->id,
                'documento_id' => $documento->id,
                'aditivo_id' => $documento_pai_id
            ]);

        }catch(\Throwable $th) {
            throw $th;
        }
     }
}
