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
                'status_anterior' => $documento->status
            ]);

        }catch(\Exception $e){
            throw new \Error($e->getMessage());
        }

    }

    /**
     * Função para remover o documento da fila
     *
     * @param Documento $documento
     * @param string $status
     */
    public function remover_fila(
        Documento $documento
    )
    {

        try{
            //salvar rastreabilidade do documento
            $this->rastreabilidadeService->create(
                'alterar',
                $documento->id,
                Auth::user()->id,
                'Documento removido da fila de repactuações'
            );

            //alterar status para fila de repactuação
            $documento->update([
                'status' => $documento->status_anterior,
                'status_anterior' => null,
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
     * {@internal O documento pai que está sendo alterado na repactuação irá repetir no relacionamento 'repactuacoes' no loop.
     *            Logo será necessário sair do loop de buscas dos filhos ('continue'), para evitar duplicidade de registros.}
     *
     * {@internal O documento pai ('aditivo_id') será duplicado na tabela repactuacoes, seguir as intruções acima}
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

            //variabel para calcular espaço dos documentos
            $calculaEspaco = 0;

            foreach ($documentos as $doc) {

                //buscar documento
                $documento = $this->documentoService->findById($doc['id']);

                //descrição documento
                if(!is_null($documento->caixa_id)){
                    $descricao = 'Documento arquivado anteriormente no Prédio '.$documento->predio_id.', Caixa '.$documento->caixa_id.' Ordem '.$documento->ordem;
                }elseif(!is_null($documento->caixa_id) && $documento->id == $documento_pai_id){
                    $descricao = 'Dossiê de Repactuação';
                }else{
                    $descricao = 'Documento não possuía endereçamento anterior, sendo seu primeiro endereço no dossiê de repactuação.';
                }

                // observação do documento
                if(!is_null($observacao) && !is_null($doc['observacao'])){
                    $concatObservacao = "Observação Repactuação:".$observacao." \n\n Observação Anterior:".$doc['observacao'];
                }else{
                    $concatObservacao = "Sem informações...";
                }

                //verificar se possui repactuações
                if($documento->repactuacoes_count > 0){

                    //percorrer os filhos e alterar o pai
                    foreach ($documento->repactuacoes as $repac_filho) {
                        $repac_filho->aditivo_id = $documento_pai_id;
                        $repac_filho->documento->caixa_id = $caixa_id;
                        $repac_filho->documento->predio_id = $predio_id;
                        $repac_filho->documento->ordem = $ordem;
                        $repac_filho->documento->status = 'repactuacao';
                        $calculaEspaco += $repac_filho->documento->espaco_ocupado;

                        $this->rastreabilidadeService->create(
                            'repactuar',
                            $repac_filho->documento->id,
                            Auth()->user()->id,
                            $descricao
                        );

                        if($repac_filho->documento->status_anterior !== 'alocar' && $repac_filho->documento->status_anterior !== null) {
                            //função para retirar os documentos da caixa de origem
                            $caixa = $this->caixaService->alterar_conteudo_caixa(
                                $repac_filho->documento->caixa->id,
                                $repac_filho->documento->espaco_ocupado,
                                $repac_filho->documento->caixa->predio_id,
                                $repac_filho->documento->caixa->andar_id,
                                'saida'
                            );
                        }

                        //atualizar o relacionamento com os filhos
                        $documento->push();
                    }

                    //proximo loop
                    continue;
                }

                $calculaEspaco += $documento->espaco_ocupado;

                // documentos sem repactuações
                $documento->update([
                    'espaco_ocupado' => $documento->espaco_ocupado,
                    'status' => 'repactuacao',
                    'caixa_id' => $caixa_id,
                    'predio_id' => $predio_id,
                    'observacao' => $concatObservacao,
                    'ordem' => $ordem,
                ]);

                // rastreabilidade do documento
                $this->rastreabilidadeService->create('repactuar',$documento->id,Auth()->user()->id,$descricao);

                // criar relações da repactuação
                $this->repactuacoes($documento, $documento_pai_id);

                if($documento->status_anterior !== 'alocar') {
                    //função para retirar os documentos da caixa de origem
                    $caixa = $this->caixaService->alterar_conteudo_caixa(
                        $documento->caixa->id,
                        $documento->espaco_ocupado,
                        $documento->caixa->predio_id,
                        $documento->caixa->andar_id,
                        'saida'
                    );
                }
            }

            //função para preencher a caixa destino dos documentos
            $caixa = $this->caixaService->alterar_conteudo_caixa(
                $caixa_id,
                $calculaEspaco,
                $predio_id,
                $andar_id,
                'entrada'
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
